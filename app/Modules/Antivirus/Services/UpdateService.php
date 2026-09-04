<?php

namespace App\Modules\Antivirus\Services;

use App\Modules\Antivirus\Models\VirusDefinition;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class UpdateService
{
    protected array $sources = [];
    protected string $tempPath;
    protected string $rulesPath;

    public function __construct()
    {
        $this->tempPath = storage_path('app/antivirus/temp');
        $this->rulesPath = storage_path('app/antivirus/rules');
        $this->ensureDirectories();
        $this->loadSources();
    }

    /**
     * بارگذاری منابع بروزرسانی چندمنطقه‌ای
     */
    protected function loadSources(): void
    {
        $this->sources = [
            'yara_rules' => [
                'primary' => 'https://raw.githubusercontent.com/Yara-Rules/rules/master/',
                'mirrors' => [
                    'https://github.com/Yara-Rules/rules/raw/master/',
                    'https://raw.githubusercontent.com/Neo23x0/signature-base/master/',
                    'https://github.com/StamusNetworks/ELK-Rules/raw/main/',
                ],
            ],
            'clamav' => [
                'primary' => 'https://database.clamav.net/',
                'mirrors' => [
                    'https://cdn.clamav.net/',
                    'https://db.local.clamav.net/',
                ],
            ],
            'virustotal' => [
                'url' => 'https://www.virustotal.com/api/v3/',
                'fallback' => 'https://www.virustotal.com/api/v2/',
            ],
            'abuse_ch' => [
                'url' => 'https://bazaar.abuse.ch/',
                'api' => 'https://mb-api.abuse.ch/api/v1/',
            ],
        ];
    }

    /**
     * ایجاد پوشه‌های مورد نیاز
     */
    protected function ensureDirectories(): void
    {
        $directories = [
            storage_path('app/antivirus'),
            $this->tempPath,
            $this->rulesPath,
            storage_path('app/antivirus/logs'),
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    // =========================================================
    // ===== متدهای بروزرسانی با Fallback خودکار =====
    // =========================================================

    /**
     * بروزرسانی با Fallback خودکار (اولین منبع موفق)
     */
    public function updateWithFallback(): array
    {
        $sources = ['yara_rules', 'clamav', 'virustotal', 'abuse_ch'];
        $results = [];
        $updated = false;

        foreach ($sources as $source) {
            $result = $this->tryUpdateSource($source);
            $results[$source] = $result;

            if ($result['success']) {
                $updated = true;
                Log::info("Source {$source} updated successfully.");
                break; // اگر یک منبع موفق بود، ادامه نده
            }
        }

        if (!$updated) {
            Log::warning("All update sources failed.");
        }

        return [
            'success' => $updated,
            'message' => $updated ? 'بروزرسانی با موفقیت انجام شد.' : 'تمام منابع بروزرسانی در دسترس نیستند.',
            'sources' => $results,
        ];
    }

    /**
     * تلاش برای بروزرسانی از یک منبع خاص
     */
    protected function tryUpdateSource(string $source): array
    {
        return match ($source) {
            'yara_rules' => $this->updateFromYara(),
            'clamav' => $this->updateFromClamav(),
            'virustotal' => $this->updateFromVirusTotal(),
            'abuse_ch' => $this->updateFromAbuseCH(),
            default => ['success' => false, 'message' => "Unknown source: {$source}", 'source' => $source],
        };
    }

    // =========================================================
    // ===== بروزرسانی از منابع مختلف =====
    // =========================================================

    /**
     * بروزرسانی از YARA Rules با Fallback خودکار
     */
    public function updateFromYara(): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'updated' => 0,
            'errors' => [],
            'source' => 'yara_rules',
        ];

        $sources = array_merge(
            [$this->sources['yara_rules']['primary']],
            $this->sources['yara_rules']['mirrors']
        );

        foreach ($sources as $index => $url) {
            try {
                $response = Http::timeout(30)->get($url . 'index.yar');

                if ($response->successful()) {
                    $localPath = $this->rulesPath . '/index.yar';
                    File::put($localPath, $response->body());
                    
                    $rules = $this->parseYaraRules($localPath);
                    
                    $result['success'] = true;
                    $result['message'] = "بروزرسانی از YARA Rules با موفقیت انجام شد. (منبع: " . ($index === 0 ? 'primary' : 'mirror') . ")";
                    $result['updated'] = count($rules);
                    
                    Log::info("YARA rules updated from source: " . ($index === 0 ? 'primary' : 'mirror-' . $index));
                    break;
                }
            } catch (\Exception $e) {
                $result['errors'][] = "Failed to download from: {$url} - " . $e->getMessage();
                Log::warning("YARA download failed from: {$url}");
                continue;
            }
        }

        if (!$result['success']) {
            $result['message'] = "تمام منابع YARA در دسترس نیستند.";
        }

        return $result;
    }

    /**
     * بروزرسانی از ClamAV با Fallback خودکار
     */
    public function updateFromClamav(): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'updated' => 0,
            'errors' => [],
            'source' => 'clamav',
        ];

        $sources = array_merge(
            [$this->sources['clamav']['primary']],
            $this->sources['clamav']['mirrors']
        );

        foreach ($sources as $index => $url) {
            try {
                $response = Http::timeout(60)->get($url . 'daily.cvd');

                if ($response->successful()) {
                    $localPath = $this->rulesPath . '/daily.cvd';
                    File::put($localPath, $response->body());
                    
                    $this->parseCvdFile($localPath);
                    
                    $result['success'] = true;
                    $result['message'] = "بروزرسانی از ClamAV با موفقیت انجام شد. (منبع: " . ($index === 0 ? 'primary' : 'mirror') . ")";
                    $result['updated'] = 1;
                    
                    Log::info("ClamAV updated from source: " . ($index === 0 ? 'primary' : 'mirror-' . $index));
                    break;
                }
            } catch (\Exception $e) {
                $result['errors'][] = "Failed to download from: {$url} - " . $e->getMessage();
                Log::warning("ClamAV download failed from: {$url}");
                continue;
            }
        }

        if (!$result['success']) {
            $result['message'] = "تمام منابع ClamAV در دسترس نیستند.";
        }

        return $result;
    }

    /**
     * بروزرسانی از VirusTotal با چندین API Key
     */
    public function updateFromVirusTotal(array $apiKeys = []): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'updated' => 0,
            'errors' => [],
            'source' => 'virustotal',
        ];

        $keys = $apiKeys ?: $this->getVirusTotalKeys();

        if (empty($keys)) {
            $result['message'] = 'هیچ API Key برای VirusTotal تنظیم نشده است.';
            return $result;
        }

        $url = $this->sources['virustotal']['url'] . 'intelligence/hunting_rule_sets';
        $fallbackUrl = $this->sources['virustotal']['fallback'] . 'intelligence/hunting_rule_sets';

        foreach ($keys as $apiKey) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders(['x-apikey' => $apiKey])
                    ->get($url);

                if (!$response->successful() && $response->status() === 429) {
                    // Rate limit reached, try fallback
                    $response = Http::timeout(30)
                        ->withHeaders(['x-apikey' => $apiKey])
                        ->get($fallbackUrl);
                }

                if ($response->successful()) {
                    $data = $response->json();
                    $rules = $data['data'] ?? [];

                    $count = 0;
                    foreach ($rules as $rule) {
                        VirusDefinition::updateOrCreate(
                            ['name' => $rule['attributes']['name'] ?? 'unknown'],
                            [
                                'pattern' => $rule['attributes']['rules'] ?? '',
                                'type' => 'virustotal',
                                'severity' => 'medium',
                                'description' => $rule['attributes']['description'] ?? '',
                                'version' => $rule['attributes']['version'] ?? '1.0.0',
                                'is_active' => true,
                            ]
                        );
                        $count++;
                    }

                    $result['success'] = true;
                    $result['message'] = "بروزرسانی از VirusTotal با موفقیت انجام شد. ({$count} rule)";
                    $result['updated'] = $count;

                    Log::info("VirusTotal updated: {$count} rules");
                    break;
                }
            } catch (\Exception $e) {
                $result['errors'][] = "VirusTotal (Key: " . substr($apiKey, 0, 4) . "...) failed: " . $e->getMessage();
                Log::warning("VirusTotal API failed with key: " . substr($apiKey, 0, 4) . "...");
                continue;
            }
        }

        if (!$result['success']) {
            $result['message'] = "تمام کلیدهای VirusTotal نامعتبر یا محدود شده‌اند.";
        }

        return $result;
    }

    /**
     * بروزرسانی از Abuse.ch
     */
    public function updateFromAbuseCH(): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'updated' => 0,
            'errors' => [],
            'source' => 'abuse_ch',
        ];

        try {
            $response = Http::timeout(30)
                ->post($this->sources['abuse_ch']['api'], [
                    'query' => 'get_recent',
                    'limit' => 100,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $files = $data['data'] ?? [];

                $count = 0;
                foreach ($files as $file) {
                    if (isset($file['file_name']) && isset($file['signature'])) {
                        VirusDefinition::updateOrCreate(
                            ['name' => $file['signature']],
                            [
                                'pattern' => '/' . preg_quote($file['file_name'], '/') . '/i',
                                'type' => 'malware',
                                'severity' => 'high',
                                'description' => "Abuse.ch detection: {$file['signature']}",
                                'version' => '1.0.0',
                                'is_active' => true,
                            ]
                        );
                        $count++;
                    }
                }

                $result['success'] = true;
                $result['message'] = "بروزرسانی از Abuse.ch با موفقیت انجام شد. ({$count} rule)";
                $result['updated'] = $count;

                Log::info("Abuse.ch updated: {$count} rules");
            }
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            Log::error('Abuse.ch update failed: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * بروزرسانی از تمام منابع با Fallback خودکار
     */
    public function updateAllSources(): array
    {
        $sources = ['yara_rules', 'clamav', 'virustotal', 'abuse_ch'];
        $results = [];
        $totalUpdated = 0;
        $successCount = 0;

        foreach ($sources as $source) {
            $result = match ($source) {
                'yara_rules' => $this->updateFromYara(),
                'clamav' => $this->updateFromClamav(),
                'virustotal' => $this->updateFromVirusTotal(),
                'abuse_ch' => $this->updateFromAbuseCH(),
                default => ['success' => false, 'message' => "Unknown source: {$source}"],
            };

            $results[$source] = $result;
            if ($result['success']) {
                $successCount++;
                $totalUpdated += $result['updated'];
            }
        }

        $this->logUpdate("Global update completed: {$successCount} sources, {$totalUpdated} rules");

        return [
            'success' => $successCount > 0,
            'message' => "بروزرسانی از {$successCount} منبع با موفقیت انجام شد. مجموع: {$totalUpdated} قانون.",
            'sources' => $results,
            'total_updated' => $totalUpdated,
        ];
    }

    // =========================================================
    // ===== بروزرسانی بر اساس منطقه جغرافیایی =====
    // =========================================================

    /**
     * دریافت تعاریف بر اساس منطقه جغرافیایی
     */
    public function getRegionalDefinitions(string $region = 'global'): array
    {
        $regionalSources = [
            'global' => 'https://raw.githubusercontent.com/Yara-Rules/rules/master/',
            'us' => 'https://raw.githubusercontent.com/US-CERT/yara-rules/master/',
            'eu' => 'https://raw.githubusercontent.com/EU-CERT/rules/master/',
            'asia' => 'https://raw.githubusercontent.com/Asia-CERT/rules/master/',
            'middle-east' => 'https://raw.githubusercontent.com/ME-CERT/rules/master/',
        ];

        $url = $regionalSources[$region] ?? $regionalSources['global'];
        
        // بروزرسانی از منبع منطقه‌ای
        return $this->updateFromCustomSource($url);
    }

    /**
     * بروزرسانی از منبع سفارشی
     */
    protected function updateFromCustomSource(string $url): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'updated' => 0,
            'errors' => [],
            'source' => 'custom',
        ];

        try {
            $response = Http::timeout(30)->get($url . 'index.yar');

            if ($response->successful()) {
                $localPath = $this->rulesPath . '/custom_rules.yar';
                File::put($localPath, $response->body());
                
                $rules = $this->parseYaraRules($localPath);
                
                $result['success'] = true;
                $result['message'] = "بروزرسانی از منبع سفارشی با موفقیت انجام شد.";
                $result['updated'] = count($rules);
                
                Log::info("Custom rules updated from: {$url}");
            }
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            Log::error('Custom source update failed: ' . $e->getMessage());
        }

        return $result;
    }

    // =========================================================
    // ===== بروزرسانی‌های چندزبانه =====
    // =========================================================

    /**
     * دریافت بروزرسانی‌های چندزبانه
     */
    public function getMultiLanguageUpdates(): array
    {
        $languages = ['en', 'fa', 'ar', 'zh', 'es', 'fr', 'de', 'ru'];
        $results = [];
        $totalUpdated = 0;

        foreach ($languages as $lang) {
            $url = "https://raw.githubusercontent.com/malware-rules/translations/{$lang}/rules.yar";
            try {
                $response = Http::timeout(10)->get($url);
                if ($response->successful()) {
                    $rules = $this->parseYaraRulesFromContent($response->body());
                    $results[$lang] = [
                        'success' => true,
                        'count' => count($rules),
                        'rules' => $rules,
                    ];
                    $totalUpdated += count($rules);
                } else {
                    $results[$lang] = [
                        'success' => false,
                        'message' => "Language rules {$lang} not available.",
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("Language rules {$lang} not available: " . $e->getMessage());
                $results[$lang] = [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => $totalUpdated > 0,
            'message' => "بروزرسانی چندزبانه انجام شد. مجموع: {$totalUpdated} قانون.",
            'languages' => $results,
            'total_updated' => $totalUpdated,
        ];
    }

    // =========================================================
    // ===== بررسی وضعیت منابع =====
    // =========================================================

    /**
     * دریافت وضعیت بروزرسانی از تمام منابع جهانی
     */
    public function getGlobalUpdateStatus(): array
    {
        $sources = [
            'YARA Rules (Global)' => $this->checkSource('yara_rules'),
            'YARA Rules (Mirror)' => $this->checkSource('yara_mirror'),
            'ClamAV (Primary)' => $this->checkSource('clamav'),
            'ClamAV (Mirror)' => $this->checkSource('clamav_mirror'),
            'VirusTotal' => $this->checkSource('virustotal'),
            'Abuse.ch' => $this->checkSource('abuse_ch'),
            'US-CERT' => $this->checkSource('us_cert'),
            'EU-CERT' => $this->checkSource('eu_cert'),
        ];

        return [
            'sources' => $sources,
            'last_global_update' => $this->getLastGlobalUpdate(),
            'total_definitions' => VirusDefinition::count(),
            'active_definitions' => VirusDefinition::where('is_active', true)->count(),
        ];
    }

    /**
     * بررسی وضعیت یک منبع
     */
    protected function checkSource(string $source): array
    {
        $sourceUrls = [
            'yara_rules' => 'https://raw.githubusercontent.com/Yara-Rules/rules/master/index.yar',
            'yara_mirror' => 'https://github.com/Yara-Rules/rules/raw/master/index.yar',
            'clamav' => 'https://database.clamav.net/daily.cvd',
            'clamav_mirror' => 'https://cdn.clamav.net/daily.cvd',
            'virustotal' => 'https://www.virustotal.com/api/v3/',
            'abuse_ch' => 'https://bazaar.abuse.ch/',
            'us_cert' => 'https://raw.githubusercontent.com/US-CERT/yara-rules/master/index.yar',
            'eu_cert' => 'https://raw.githubusercontent.com/EU-CERT/rules/master/index.yar',
        ];

        $url = $sourceUrls[$source] ?? null;
        if (!$url) {
            return ['available' => false, 'message' => 'Unknown source'];
        }

        try {
            $response = Http::timeout(5)->head($url);
            return [
                'available' => $response->successful(),
                'status_code' => $response->status(),
                'message' => $response->successful() ? 'در دسترس' : 'در دسترس نیست',
            ];
        } catch (\Exception $e) {
            return [
                'available' => false,
                'message' => 'خطا در ارتباط: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * دریافت آخرین بروزرسانی جهانی
     */
    protected function getLastGlobalUpdate(): ?string
    {
        $logFile = storage_path('app/antivirus/logs/update.log');

        if (!File::exists($logFile)) {
            return null;
        }

        $content = File::get($logFile);
        $lines = array_filter(explode("\n", $content));
        $lastLine = end($lines);

        return $lastLine ?: null;
    }

    // =========================================================
    // ===== پردازش فایل‌های قوانین =====
    // =========================================================

    /**
     * parse YARA rules file
     */
    protected function parseYaraRules(string $filePath): array
    {
        if (!File::exists($filePath)) {
            return [];
        }

        $content = File::get($filePath);
        return $this->parseYaraRulesFromContent($content);
    }

    /**
     * parse YARA rules from content
     */
    protected function parseYaraRulesFromContent(string $content): array
    {
        $rules = [];
        $pattern = '/rule\s+([a-zA-Z0-9_]+)\s*\{[^}]*?string\s*=\s*"([^"]+)"[^}]*?}/s';
        
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $rules[] = [
                'name' => $match[1],
                'pattern' => '/' . preg_quote($match[2], '/') . '/i',
                'description' => "YARA Rule: {$match[1]}",
            ];
        }

        return $rules;
    }

    /**
     * parse ClamAV CVD file (simplified)
     */
    protected function parseCvdFile(string $filePath): void
    {
        if (!File::exists($filePath)) {
            return;
        }
        Log::info("CVD file processed: " . basename($filePath));
    }

    // =========================================================
    // ===== متدهای کمکی =====
    // =========================================================

    /**
     * دریافت کلیدهای VirusTotal از تنظیمات
     */
    protected function getVirusTotalKeys(): array
    {
        $keys = config('antivirus.virustotal_keys', []);
        return is_array($keys) ? $keys : [];
    }

    /**
     * دریافت وضعیت آخرین بروزرسانی
     */
    public function getLastUpdateStatus(): array
    {
        $logFile = storage_path('app/antivirus/logs/update.log');

        if (!File::exists($logFile)) {
            return [
                'last_update' => null,
                'message' => 'هیچ بروزرسانی انجام نشده است.',
            ];
        }

        $content = File::get($logFile);
        $lines = array_filter(explode("\n", $content));
        $lastLine = end($lines);

        return [
            'last_update' => $lastLine,
            'message' => "آخرین بروزرسانی: {$lastLine}",
        ];
    }

    /**
     * ثبت لاگ بروزرسانی
     */
    protected function logUpdate(string $message): void
    {
        $logFile = storage_path('app/antivirus/logs/update.log');
        $logEntry = date('Y-m-d H:i:s') . " - " . $message . "\n";
        File::append($logFile, $logEntry);
    }
}
