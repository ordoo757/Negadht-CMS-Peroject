<?php

namespace App\Modules\AiKernel\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AiService
{
    protected array $config;
    protected string $defaultProvider;
    protected string $activeProvider;
    protected array $providers = ['openai', 'claude', 'gemini', 'ollama', 'local'];
    protected array $providerStatus = [];

    public function __construct()
    {
        $this->config = config('modules.ai', []);
        $this->defaultProvider = $this->config['default_provider'] ?? 'ollama';
        $this->activeProvider = $this->determineActiveProvider();
    }

    /**
     * تعیین ارائه‌دهنده فعال بر اساس اولویت و دسترسی
     * اولویت: OpenAI -> Claude -> Gemini -> Ollama -> Local
     */
    protected function determineActiveProvider(): string
    {
        $preferredProviders = ['openai', 'claude', 'gemini', 'ollama', 'local'];
        
        foreach ($preferredProviders as $provider) {
            if ($this->isProviderAvailable($provider)) {
                Log::info("AI Provider selected: {$provider}");
                return $provider;
            }
        }

        return 'local';
    }

    /**
     * بررسی در دسترس بودن هر ارائه‌دهنده
     */
    protected function isProviderAvailable(string $provider): bool
    {
        $cacheKey = "ai_provider_status_{$provider}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $available = false;

        switch ($provider) {
            case 'openai':
                $available = $this->checkOpenAIAvailability();
                break;
            case 'claude':
                $available = $this->checkClaudeAvailability();
                break;
            case 'gemini':
                $available = $this->checkGeminiAvailability();
                break;
            case 'ollama':
                $available = $this->checkOllamaAvailability();
                break;
            case 'local':
                $available = true;
                break;
        }

        Cache::put($cacheKey, $available, now()->addMinutes(5));
        
        return $available;
    }

    /**
     * بررسی دسترسی به OpenAI
     */
    protected function checkOpenAIAvailability(): bool
    {
        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            return false;
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->get('https://api.openai.com/v1/models');

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('OpenAI unavailable: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * بررسی دسترسی به Claude (Anthropic)
     */
    protected function checkClaudeAvailability(): bool
    {
        $apiKey = env('CLAUDE_API_KEY');
        if (empty($apiKey)) {
            return false;
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                ])
                ->get('https://api.anthropic.com/v1/messages');

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Claude unavailable: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * بررسی دسترسی به Gemini (Google AI - رایگان با محدودیت)
     */
    protected function checkGeminiAvailability(): bool
    {
        $apiKey = env('GEMINI_API_KEY');
        if (empty($apiKey)) {
            return false;
        }

        try {
            $response = Http::timeout(5)
                ->get("https://generativelanguage.googleapis.com/v1/models?key={$apiKey}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Gemini unavailable: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * بررسی دسترسی به Ollama (محلی و رایگان)
     */
    protected function checkOllamaAvailability(): bool
    {
        $ollamaUrl = $this->config['ollama']['url'] ?? 'http://localhost:11434';
        
        try {
            $response = Http::timeout(3)->get($ollamaUrl . '/api/tags');

            if ($response->successful()) {
                $models = $response->json();
                if (!empty($models['models'])) {
                    Log::info('Ollama is available with ' . count($models['models']) . ' models');
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            Log::warning('Ollama unavailable: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * دریافت ارائه‌دهنده فعال
     */
    public function getActiveProvider(): string
    {
        return $this->activeProvider;
    }

    /**
     * دریافت وضعیت تمام ارائه‌دهنده‌ها
     */
    public function getProviderStatus(): array
    {
        $status = [];
        foreach ($this->providers as $provider) {
            $status[$provider] = $this->isProviderAvailable($provider);
        }
        return $status;
    }

    // =============== متدهای اصلی ===============

    public function analyze(string $content, string $type = 'general'): array
    {
        $cacheKey = 'ai_analysis_' . md5($content . $type);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = match($type) {
            'security' => $this->analyzeSecurity($content),
            'content' => $this->analyzeContent($content),
            'sentiment' => $this->analyzeSentiment($content),
            'translation' => $this->translate($content),
            'code' => $this->analyzeCode($content),
            default => $this->generalAnalysis($content),
        };

        Cache::put($cacheKey, $result, now()->addHours(1));
        $this->logActivity('analyze', $type, strlen($content));

        return $result;
    }

    public function generate(string $prompt, string $type = 'text'): array
    {
        $provider = $this->getActiveProvider();

        try {
            $response = match($provider) {
                'openai' => $this->callOpenAI($prompt, $type),
                'claude' => $this->callClaude($prompt, $type),
                'gemini' => $this->callGemini($prompt, $type),
                'ollama' => $this->callOllama($prompt, $type),
                default => $this->callLocalAI($prompt, $type),
            };

            $this->logActivity('generate', $type, strlen($prompt));

            return [
                'success' => true,
                'data' => $response,
                'provider' => $provider,
                'fallback_used' => ($provider !== $this->defaultProvider),
            ];
        } catch (\Exception $e) {
            Log::error('AI Generation failed: ' . $e->getMessage());
            
            if ($provider !== 'local') {
                Log::info('Falling back to local AI');
                return $this->generate($prompt, $type);
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $provider,
            ];
        }
    }

    // =============== متدهای فراخوانی API ===============

    /**
     * فراخوانی OpenAI
     */
    protected function callOpenAI(string $prompt, string $type): string
    {
        $apiKey = env('OPENAI_API_KEY');

        if (!$apiKey) {
            throw new \Exception('OpenAI API key not configured');
        }

        $response = Http::timeout(60)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->config['openai']['model'] ?? 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant for a CMS system.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => $this->config['openai']['max_tokens'] ?? 2000,
                'temperature' => 0.7,
            ]);

        if (!$response->successful()) {
            throw new \Exception('OpenAI API error: ' . $response->body());
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    /**
     * فراخوانی Claude (Anthropic)
     */
    protected function callClaude(string $prompt, string $type): string
    {
        $apiKey = env('CLAUDE_API_KEY');

        if (!$apiKey) {
            throw new \Exception('Claude API key not configured');
        }

        $response = Http::timeout(60)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->config['claude']['model'] ?? 'claude-3-opus-20240229',
                'max_tokens' => $this->config['claude']['max_tokens'] ?? 2000,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception('Claude API error: ' . $response->body());
        }

        return $response->json('content.0.text') ?? '';
    }

    /**
     * فراخوانی Gemini (Google AI - رایگان)
     */
    protected function callGemini(string $prompt, string $type): string
    {
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            throw new \Exception('Gemini API key not configured');
        }

        $model = $this->config['gemini']['model'] ?? 'gemini-pro';

        $response = Http::timeout(60)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => $this->config['gemini']['max_tokens'] ?? 2000,
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception('Gemini API error: ' . $response->body());
        }

        $data = $response->json();
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    /**
     * فراخوانی Ollama (مدل محلی و رایگان)
     */
    protected function callOllama(string $prompt, string $type): string
    {
        $ollamaUrl = $this->config['ollama']['url'] ?? 'http://localhost:11434';
        $model = $this->config['ollama']['model'] ?? 'llama2';

        $response = Http::timeout(120)
            ->post($ollamaUrl . '/api/generate', [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature' => 0.7,
                    'num_predict' => $this->config['ollama']['max_tokens'] ?? 2000,
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception('Ollama API error: ' . $response->body());
        }

        $data = $response->json();
        return $data['response'] ?? '';
    }

    /**
     * Fallback محلی (بدون نیاز به API)
     */
    protected function callLocalAI(string $prompt, string $type): string
    {
        if (class_exists(NlpService::class)) {
            $nlp = new NlpService();
            $summary = $nlp->summarize($prompt, 3);
            return "Local AI Analysis:\n" . $summary . "\n\n[Processed locally without external API]";
        }

        $responses = [
            'greeting' => 'سلام! من دستیار هوشمند شما هستم.',
            'help' => 'می‌توانید از من برای تحلیل، تولید محتوا و مدیریت سیستم استفاده کنید.',
            'default' => 'در حال حاضر به اینترنت دسترسی ندارم. لطفاً سوال خود را ساده‌تر بپرسید یا بعداً دوباره تلاش کنید.',
        ];

        $lowerPrompt = strtolower($prompt);
        
        if (str_contains($lowerPrompt, 'سلام') || str_contains($lowerPrompt, 'hello')) {
            return $responses['greeting'];
        }
        
        if (str_contains($lowerPrompt, 'کمک') || str_contains($lowerPrompt, 'help')) {
            return $responses['help'];
        }

        return $responses['default'];
    }

    // =============== بقیه متدها ===============

    public function monitorSystem(): array
    {
        $metrics = [
            'cpu_usage' => $this->getCpuUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'active_users' => $this->getActiveUsers(),
            'failed_logins' => $this->getFailedLogins(),
            'suspicious_activities' => $this->getSuspiciousActivities(),
            'active_provider' => $this->getActiveProvider(),
            'provider_status' => $this->getProviderStatus(),
        ];

        $analysis = $this->analyzeSecurityMetrics($metrics);

        return [
            'metrics' => $metrics,
            'analysis' => $analysis,
            'recommendations' => $this->generateRecommendations($metrics, $analysis),
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    public function smartMenuBuilder(array $context): array
    {
        $prompt = "Based on the following context, generate an optimal menu structure:\n";
        $prompt .= json_encode($context, JSON_PRETTY_PRINT);

        $result = $this->generate($prompt, 'json');

        if ($result['success']) {
            return json_decode($result['data'], true) ?? [];
        }

        return [];
    }

    public function smartFormBuilder(array $requirements): array
    {
        $prompt = "Generate a form structure based on these requirements:\n";
        $prompt .= json_encode($requirements, JSON_PRETTY_PRINT);

        $result = $this->generate($prompt, 'json');

        if ($result['success']) {
            return json_decode($result['data'], true) ?? [];
        }

        return [];
    }

    public function smartTemplateBuilder(array $preferences): array
    {
        $prompt = "Generate a template structure based on these preferences:\n";
        $prompt .= json_encode($preferences, JSON_PRETTY_PRINT);

        $result = $this->generate($prompt, 'json');

        if ($result['success']) {
            return json_decode($result['data'], true) ?? [];
        }

        return [];
    }

    public function translate(string $text, string $targetLang = 'en', string $sourceLang = 'auto'): string
    {
        $cacheKey = "translation_{$sourceLang}_{$targetLang}_" . md5($text);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $prompt = "Translate the following text from {$sourceLang} to {$targetLang}. ";
        $prompt .= "Maintain the tone, context, and any technical terms accurately.\n\n";
        $prompt .= "Text: {$text}\n\nTranslation:";

        $result = $this->generate($prompt, 'text');

        if ($result['success']) {
            $translation = trim($result['data']);
            Cache::put($cacheKey, $translation, now()->addDays(7));
            return $translation;
        }

        return $text;
    }

    // =============== متدهای تحلیلی ===============

    protected function analyzeSecurity(string $content): array
    {
        $threats = [];
        $score = 100;

        $sqlPatterns = [
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|ALTER|CREATE)\b.*\b(FROM|INTO|TABLE|DATABASE)\b)/i',
            '/(--|#|\/\*|\*\/)/',
            '/(\bOR\b|\bAND\b)\s+\d+\s*=\s*\d+/i',
        ];

        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $threats[] = 'Potential SQL Injection detected';
                $score -= 25;
            }
        }

        $xssPatterns = [
            '/<script\b[^>]*>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $threats[] = 'Potential XSS attack detected';
                $score -= 25;
            }
        }

        return [
            'type' => 'security',
            'score' => max(0, $score),
            'threats' => $threats,
            'is_safe' => empty($threats),
            'recommendations' => $this->getSecurityRecommendations($threats),
        ];
    }

    protected function analyzeContent(string $content): array
    {
        $wordCount = str_word_count(strip_tags($content));
        $readability = $this->calculateReadability($content);

        return [
            'type' => 'content',
            'word_count' => $wordCount,
            'readability_score' => $readability,
            'language' => $this->detectLanguage($content),
            'sentiment' => $this->analyzeSentiment($content),
            'keywords' => $this->extractKeywords($content),
            'suggestions' => $this->getContentSuggestions($wordCount, $readability),
        ];
    }

    protected function analyzeSentiment(string $content): array
    {
        $positiveWords = ['good', 'great', 'excellent', 'amazing', 'love', 'best', 'perfect', 'happy', 'joy'];
        $negativeWords = ['bad', 'terrible', 'awful', 'hate', 'worst', 'horrible', 'sad', 'angry', 'disappointing'];

        $words = explode(' ', strtolower(strip_tags($content)));
        $positive = 0;
        $negative = 0;

        foreach ($words as $word) {
            if (in_array($word, $positiveWords)) $positive++;
            if (in_array($word, $negativeWords)) $negative++;
        }

        $total = $positive + $negative;
        $score = $total > 0 ? ($positive - $negative) / $total : 0;

        return [
            'score' => $score,
            'label' => $score > 0.2 ? 'positive' : ($score < -0.2 ? 'negative' : 'neutral'),
            'confidence' => $total > 0 ? min($total / 10, 1.0) : 0,
        ];
    }

    protected function analyzeCode(string $code): array
    {
        $lines = explode("\n", $code);
        $issues = [];

        foreach ($lines as $lineNum => $line) {
            if (preg_match('/eval\s*\(/', $line)) {
                $issues[] = ['line' => $lineNum + 1, 'issue' => 'Dangerous eval() usage', 'severity' => 'critical'];
            }
            if (preg_match('/mysql_/', $line)) {
                $issues[] = ['line' => $lineNum + 1, 'issue' => 'Deprecated mysql_* functions', 'severity' => 'warning'];
            }
            if (preg_match('/@\$[_a-zA-Z]/', $line)) {
                $issues[] = ['line' => $lineNum + 1, 'issue' => 'Error suppression used', 'severity' => 'notice'];
            }
        }

        return [
            'type' => 'code',
            'total_lines' => count($lines),
            'issues' => $issues,
            'quality_score' => max(0, 100 - count($issues) * 10),
        ];
    }

    protected function generalAnalysis(string $content): array
    {
        return [
            'type' => 'general',
            'length' => strlen($content),
            'word_count' => str_word_count($content),
            'language' => $this->detectLanguage($content),
            'summary' => substr($content, 0, 200) . '...',
        ];
    }

    // =============== متدهای کمکی ===============

    protected function getCpuUsage(): float
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return round($load[0] * 100 / intval(shell_exec('nproc') ?: 1), 2);
        }
        return 0;
    }

    protected function getMemoryUsage(): array
    {
        $memory = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);

        return [
            'current' => $this->formatBytes($memory),
            'peak' => $this->formatBytes($peak),
            'limit' => ini_get('memory_limit'),
        ];
    }

    protected function getDiskUsage(): array
    {
        $total = disk_total_space(base_path());
        $free = disk_free_space(base_path());

        return [
            'total' => $this->formatBytes($total),
            'free' => $this->formatBytes($free),
            'used' => $this->formatBytes($total - $free),
            'percentage' => round((($total - $free) / $total) * 100, 2),
        ];
    }

    protected function getActiveUsers(): int
    {
        return DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
            ->count();
    }

    protected function getFailedLogins(): int
    {
        return DB::table('activity_logs')
            ->where('action', 'login_failed')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();
    }

    protected function getSuspiciousActivities(): array
    {
        return DB::table('activity_logs')
            ->where('risk_level', 'high')
            ->where('created_at', '>=', now()->subHours(24))
            ->select('action', 'ip_address', 'created_at')
            ->limit(10)
            ->get()
            ->toArray();
    }

    protected function analyzeSecurityMetrics(array $metrics): array
    {
        $risks = [];

        if ($metrics['cpu_usage'] > 80) {
            $risks[] = 'High CPU usage detected';
        }

        if ($metrics['failed_logins'] > 10) {
            $risks[] = 'Multiple failed login attempts';
        }

        if (!empty($metrics['suspicious_activities'])) {
            $risks[] = 'Suspicious activities detected in last 24 hours';
        }

        return [
            'risk_level' => empty($risks) ? 'low' : (count($risks) > 2 ? 'high' : 'medium'),
            'risks' => $risks,
        ];
    }

    protected function generateRecommendations(array $metrics, array $analysis): array
    {
        $recommendations = [];

        if ($metrics['cpu_usage'] > 80) {
            $recommendations[] = 'Consider optimizing database queries or enabling caching';
        }

        if ($metrics['failed_logins'] > 10) {
            $recommendations[] = 'Enable rate limiting and consider IP blocking';
        }

        if ($metrics['disk_usage']['percentage'] > 80) {
            $recommendations[] = 'Clean up old logs and temporary files';
        }

        return $recommendations;
    }

    protected function calculateReadability(string $content): float
    {
        $text = strip_tags($content);
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $words = str_word_count($text);
        $syllables = $this->countSyllables($text);

        if (count($sentences) === 0 || $words === 0) {
            return 0;
        }

        $score = 206.835 - (1.015 * ($words / count($sentences))) - (84.6 * ($syllables / $words));

        return round(max(0, min(100, $score)), 2);
    }

    protected function countSyllables(string $text): int
    {
        $words = str_word_count(strtolower($text), 1);
        $syllables = 0;

        foreach ($words as $word) {
            $syllables += max(1, preg_match_all('/[aeiouy]{1,2}/', $word));
        }

        return $syllables;
    }

    protected function detectLanguage(string $text): string
    {
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return 'fa';
        }
        if (preg_match('/[\x{0750}-\x{077F}]/u', $text)) {
            return 'ar';
        }
        return 'en';
    }

    protected function extractKeywords(string $content): array
    {
        $text = strtolower(strip_tags($content));
        $words = str_word_count($text, 1);
        $stopWords = ['the', 'a', 'an', 'in', 'on', 'at', 'to', 'for', 'of', 'and', 'or', 'but'];

        $filtered = array_diff($words, $stopWords);
        $freq = array_count_values($filtered);
        arsort($freq);

        return array_slice(array_keys($freq), 0, 10);
    }

    protected function getSecurityRecommendations(array $threats): array
    {
        $recommendations = [];

        foreach ($threats as $threat) {
            if (str_contains($threat, 'SQL Injection')) {
                $recommendations[] = 'Use prepared statements and parameterized queries';
                $recommendations[] = 'Implement input validation and sanitization';
            }
            if (str_contains($threat, 'XSS')) {
                $recommendations[] = 'Escape all output using htmlspecialchars()';
                $recommendations[] = 'Implement Content Security Policy (CSP)';
            }
        }

        return array_unique($recommendations);
    }

    protected function getContentSuggestions(int $wordCount, float $readability): array
    {
        $suggestions = [];

        if ($wordCount < 300) {
            $suggestions[] = 'Consider adding more content for better SEO';
        }

        if ($readability < 50) {
            $suggestions[] = 'Content may be difficult to read. Consider simplifying language.';
        }

        return $suggestions;
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    protected function logActivity(string $action, string $type, int $size): void
    {
        DB::table('ai_activity_logs')->insert([
            'action' => $action,
            'type' => $type,
            'input_size' => $size,
            'provider' => $this->getActiveProvider(),
            'created_at' => now(),
        ]);
    }

    /**
     * دریافت وضعیت کامل سیستم AI
     */
    public function getSystemStatus(): array
    {
        return [
            'active_provider' => $this->getActiveProvider(),
            'default_provider' => $this->defaultProvider,
            'providers' => $this->getProviderStatus(),
            'config' => [
                'openai' => isset($this->config['openai']) ? 'configured' : 'not configured',
                'claude' => isset($this->config['claude']) ? 'configured' : 'not configured',
                'gemini' => isset($this->config['gemini']) ? 'configured' : 'not configured',
                'ollama' => isset($this->config['ollama']) ? 'configured' : 'not configured',
            ],
            'api_keys' => [
                'openai' => env('OPENAI_API_KEY') ? 'set' : 'not set',
                'claude' => env('CLAUDE_API_KEY') ? 'set' : 'not set',
                'gemini' => env('GEMINI_API_KEY') ? 'set' : 'not set',
            ],
            'ollama' => [
                'url' => $this->config['ollama']['url'] ?? 'http://localhost:11434',
                'model' => $this->config['ollama']['model'] ?? 'llama2',
                'available' => $this->isProviderAvailable('ollama'),
            ],
        ];
    }
}
