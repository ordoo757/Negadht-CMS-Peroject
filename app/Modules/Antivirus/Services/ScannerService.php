<?php

namespace App\Modules\Antivirus\Services;

use App\Modules\Antivirus\Models\VirusDefinition;
use App\Modules\Antivirus\Models\ScanReport;
use App\Modules\Antivirus\Models\QuarantineFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ScannerService
{
    protected array $definitions = [];
    protected array $infectedFiles = [];
    protected int $scannedCount = 0;
    protected int $infectedCount = 0;

    public function __construct()
    {
        $this->loadDefinitions();
    }

    /**
     * بارگذاری تعاریف ویروس‌ها
     */
    protected function loadDefinitions(): void
    {
        $this->definitions = VirusDefinition::active()->get()->toArray();
    }

    /**
     * اسکن یک فایل
     */
    public function scanFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return ['infected' => false, 'error' => 'File not found'];
        }

        $content = file_get_contents($filePath);
        $result = ['infected' => false, 'matches' => []];

        foreach ($this->definitions as $definition) {
            if (preg_match($definition['pattern'], $content)) {
                $result['infected'] = true;
                $result['matches'][] = [
                    'name' => $definition['name'],
                    'severity' => $definition['severity'],
                    'description' => $definition['description'],
                ];
            }
        }

        return $result;
    }

    /**
     * اسکن یک پوشه
     */
    public function scanDirectory(string $directory, array $options = []): ScanReport
    {
        $this->infectedFiles = [];
        $this->scannedCount = 0;
        $this->infectedCount = 0;

        $report = ScanReport::create([
            'user_id' => auth()->id(),
            'type' => $options['type'] ?? 'custom',
            'path' => $directory,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $this->scanRecursive($directory, $options);

            $report->file_count = $this->scannedCount;
            $report->infected_count = $this->infectedCount;
            $report->scanned_count = $this->scannedCount;
            $report->result = [
                'infected_files' => $this->infectedFiles,
                'scan_summary' => [
                    'total' => $this->scannedCount,
                    'infected' => $this->infectedCount,
                    'safe' => $this->scannedCount - $this->infectedCount,
                ],
            ];
            $report->status = 'completed';
            $report->completed_at = now();
            $report->duration = $report->started_at->diffInSeconds($report->completed_at);
            $report->save();

            // ذخیره فایل‌های آلوده در قرنطینه
            if ($options['quarantine'] ?? false) {
                $this->quarantineInfectedFiles();
            }

        } catch (\Exception $e) {
            $report->status = 'failed';
            $report->result = ['error' => $e->getMessage()];
            $report->save();
            Log::error('Scan failed: ' . $e->getMessage());
        }

        return $report;
    }

    /**
     * اسکن بازگشتی پوشه
     */
    protected function scanRecursive(string $directory, array $options = []): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = scandir($directory);
        $extensions = $options['extensions'] ?? ['php', 'js', 'html', 'txt', 'sql', 'css', 'json'];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $directory . '/' . $file;

            if (is_dir($path)) {
                $this->scanRecursive($path, $options);
            } else {
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                if (!in_array($extension, $extensions)) {
                    continue;
                }

                $this->scannedCount++;
                $result = $this->scanFile($path);

                if ($result['infected']) {
                    $this->infectedCount++;
                    $this->infectedFiles[] = [
                        'path' => $path,
                        'matches' => $result['matches'],
                    ];
                }
            }
        }
    }

    /**
     * قرنطینه کردن فایل‌های آلوده
     */
    protected function quarantineInfectedFiles(): void
    {
        foreach ($this->infectedFiles as $infected) {
            $path = $infected['path'];
            $filename = basename($path);

            // کپی به قرنطینه
            $quarantinePath = storage_path('app/antivirus/quarantine/' . $filename);

            if (File::copy($path, $quarantinePath)) {
                QuarantineFile::create([
                    'original_path' => $path,
                    'quarantine_path' => 'antivirus/quarantine/' . $filename,
                    'filename' => $filename,
                    'size' => File::size($path),
                    'mime_type' => File::mimeType($path),
                    'reason' => $infected['matches'][0]['description'] ?? 'تشخیص ویروس',
                    'virus_name' => $infected['matches'][0]['name'] ?? 'Unknown',
                    'severity' => $infected['matches'][0]['severity'] ?? 'medium',
                    'user_id' => auth()->id(),
                ]);
            }
        }
    }

    /**
     * اسکن سریع (فقط فایل‌های مهم)
     */
    public function quickScan(): ScanReport
    {
        $paths = [
            app_path(),
            base_path('config'),
            base_path('routes'),
            public_path(),
        ];

        $options = [
            'type' => 'quick',
            'extensions' => ['php', 'js', 'html', 'sql'],
        ];

        $report = null;
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $report = $this->scanDirectory($path, $options);
            }
        }

        return $report;
    }

    /**
     * اسکن کامل (همه فایل‌ها)
     */
    public function fullScan(): ScanReport
    {
        $options = [
            'type' => 'full',
            'extensions' => ['php', 'js', 'html', 'txt', 'sql', 'css', 'json', 'xml', 'yaml'],
            'quarantine' => true,
        ];

        return $this->scanDirectory(base_path(), $options);
    }

    /**
     * اسکن فایل آپلود شده
     */
    public function scanUploadedFile($file): array
    {
        $path = $file->getRealPath();
        $result = $this->scanFile($path);

        if ($result['infected']) {
            // قرنطینه خودکار
            $quarantinePath = storage_path('app/antivirus/quarantine/' . $file->getClientOriginalName());

            if (File::copy($path, $quarantinePath)) {
                QuarantineFile::create([
                    'original_path' => $file->getRealPath(),
                    'quarantine_path' => 'antivirus/quarantine/' . $file->getClientOriginalName(),
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'reason' => $result['matches'][0]['description'] ?? 'تشخیص ویروس در آپلود',
                    'virus_name' => $result['matches'][0]['name'] ?? 'Unknown',
                    'severity' => $result['matches'][0]['severity'] ?? 'medium',
                    'user_id' => auth()->id(),
                ]);
            }

            return [
                'safe' => false,
                'message' => 'فایل آلوده شناسایی شد و به قرنطینه منتقل گردید.',
                'details' => $result['matches'],
            ];
        }

        return [
            'safe' => true,
            'message' => 'فایل سالم است.',
        ];
    }

    /**
     * دریافت آمار اسکن
     */
    public function getStats(): array
    {
        return [
            'total_scans' => ScanReport::count(),
            'completed_scans' => ScanReport::where('status', 'completed')->count(),
            'infected_files' => ScanReport::sum('infected_count'),
            'quarantined_files' => QuarantineFile::count(),
            'virus_definitions' => VirusDefinition::active()->count(),
            'last_scan' => ScanReport::where('status', 'completed')->latest()->first(),
        ];
    }
}
