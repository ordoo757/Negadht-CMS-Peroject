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

    public function __construct()
    {
        $this->tempPath = storage_path('app/antivirus/temp');
        $this->ensureDirectories();
        $this->loadSources();
    }

    /**
     * بارگذاری منابع بروزرسانی
     */
    protected function loadSources(): void
    {
        $this->sources = [
            'yara_rules' => [
                'url' => 'https://raw.githubusercontent.com/Yara-Rules/rules/master/',
                'files' => [
                    'index.yar',
                    'malware_index.yar',
                    'exploit_index.yar',
                    'trojan_index.yar',
                    'ransomware_index.yar',
                ],
            ],
            'clamav' => [
                'url' => 'https://database.clamav.net/',
                'files' => [
                    'main.cvd',
                    'daily.cvd',
                    'bytecode.cvd',
                ],
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
            storage_path('app/antivirus/temp'),
            storage_path('app/antivirus/rules'),
            storage_path('app/antivirus/logs'),
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * بروزرسانی از YARA Rules
     */
    public function updateFromYara(): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'updated' => 0,
            'errors' => [],
        ];

        try {
            $rulesPath = storage_path('app/antivirus/rules');
            $downloaded = 0;

            foreach ($this->sources['yara_rules']['files'] as $file) {
                $url = $this->sources['yara_rules']['url'] . $file;
                $localPath = $rulesPath . '/' . $file;

                $response = Http::timeout(30)->get($url);

                if ($response->successful()) {
                    File::put($localPath, $response->body());
                    $downloaded++;
                    
                    // استخراج قوانین از فایل YARA
                    $this->parseYaraRules($localPath);
                } else {
                    $result['errors'][] = "Failed to download: {$file}";
                }
            }

            $result['success'] = true;
            $result['message'] = "بروزرسانی از YARA Rules با موفقیت انجام شد.";
            $result['updated'] = $downloaded;

            Log::info("YARA rules updated: {$downloaded} files");

        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            Log::error('YARA update failed: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * بروزرسانی از ClamAV
     */
    public function updateFromClamav(): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'updated' => 0,
            'errors' => [],
        ];

        try {
            $rulesPath = storage_path('app/antivirus/rules');
            $downloaded = 0;

            foreach ($this->sources['clamav']['files'] as $file) {
                $url = $this->sources['clamav']['url'] . $file;
                $localPath = $rulesPath . '/' . $file;

                $response = Http::timeout(60)->get($url);

                if ($response->successful()) {
                    File::put($localPath, $response->body());
                    $downloaded++;
                    
                    // پردازش فایل CVD
                    $this->parseCvdFile($localPath);
                } else {
                    $result['errors'][] = "Failed to download: {$file}";
                }
            }

            $result['success'] = true;
            $result['message'] = "بروزرسانی از ClamAV با موفقیت انجام شد.";
            $result['updated'] = $downloaded;

            Log::info("ClamAV definitions updated: {$downloaded} files");

        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            Log::error('ClamAV update failed: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * بروزرسانی از VirusTotal (با محدودیت)
     */
    public function updateFromVirusTotal(string $apiKey): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'updated' => 0,
            'errors' => [],
        ];

        try {
            // دریافت لیست آخرین ویروس‌ها
            $response = Http::timeout(30)
                ->withHeaders(['x-apikey' => $apiKey])
                ->get('https://www.virustotal.com/api/v3/intelligence/hunting_rule_sets');

            if (!$response->successful()) {
                throw new \Exception('VirusTotal API error: ' . $response->body());
            }

            $data = $response->json();
            $rules = $data['data'] ?? [];

            foreach ($rules as $rule) {
                // ذخیره در دیتابیس
                VirusDefinition::updateOrCreate(
                    ['name' => $rule['attributes']['name'] ?? 'unknown'],
                    [
                        'pattern' => $rule['attributes']['rules'] ?? '',
                        'type' => 'generic',
                        'severity' => 'medium',
                        'description' => $rule['attributes']['description'] ?? '',
                        'version' => '1.0.0',
                        'is_active' => true,
                    ]
                );
                $result['updated']++;
            }

            $result['success'] = true;
            $result['message'] = "بروزرسانی از VirusTotal با موفقیت انجام شد.";

            Log::info("VirusTotal updated: {$result['updated']} rules");

        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            Log::error('VirusTotal update failed: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * parse YARA rules file
     */
    protected function parseYaraRules(string $filePath): void
    {
        if (!File::exists($filePath)) {
            return;
        }

        $content = File::get($filePath);
        $rules = $this->extractYaraRules($content);

        foreach ($rules as $rule) {
            if (isset($rule['name']) && isset($rule['pattern'])) {
                VirusDefinition::updateOrCreate(
                    ['name' => $rule['name']],
                    [
                        'pattern' => $rule['pattern'],
                        'type' => 'yara',
                        'severity' => $this->detectSeverity($rule['name']),
                        'description' => $rule['description'] ?? '',
                        'version' => '1.0.0',
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    /**
     * parse ClamAV CVD file (simplified)
     */
    protected function parseCvdFile(string $filePath): void
    {
        // ClamAV CVD files are binary, we need to extract signatures
        // This is a simplified version - in production use clamav library
        if (!File::exists($filePath)) {
            return;
        }

        // Placeholder for actual CVD parsing
        Log::info("CVD file processed: " . basename($filePath));
    }

    /**
     * استخراج قوانین YARA از متن
     */
    protected function extractYaraRules(string $content): array
    {
        $rules = [];
        $pattern = '/rule\s+([a-zA-Z0-9_]+)\s*\{[^}]*?string\s*=\s*"([^"]+)"[^}]*?}/s';
        
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $rules[] = [
                'name' => $match[1],
                'pattern' => '/' . preg_quote($match[2], '/') . '/i',
                'description' => "Detected by YARA rule: {$match[1]}",
            ];
        }

        return $rules;
    }

    /**
     * تشخیص شدت بر اساس نام ویروس
     */
    protected function detectSeverity(string $name): string
    {
        $nameLower = strtolower($name);

        if (str_contains($nameLower, 'ransomware') || str_contains($nameLower, 'critical')) {
            return 'critical';
        }
        if (str_contains($nameLower, 'trojan') || str_contains($nameLower, 'backdoor')) {
            return 'high';
        }
        if (str_contains($nameLower, 'malware') || str_contains($nameLower, 'virus')) {
            return 'medium';
        }

        return 'low';
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
        $lines = explode("\n", $content);
        $lastLine = array_filter($lines)[count($lines) - 1] ?? '';

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
