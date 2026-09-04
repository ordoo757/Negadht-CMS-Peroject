<?php

namespace App\Modules\Antivirus\Services;

use App\Modules\Antivirus\Models\ScanReport;
use App\Modules\Antivirus\Models\QuarantineFile;
use App\Modules\Antivirus\Models\VirusDefinition;
use Illuminate\Support\Facades\Log;

class AntivirusService
{
    protected ScannerService $scanner;

    public function __construct(ScannerService $scanner)
    {
        $this->scanner = $scanner;
    }

    /**
     * شروع اسکن جدید
     */
    public function startScan(string $type, array $options = []): ScanReport
    {
        return match ($type) {
            'quick' => $this->scanner->quickScan(),
            'full' => $this->scanner->fullScan(),
            'custom' => $this->scanner->scanDirectory($options['path'] ?? base_path(), $options),
            default => throw new \Exception('نوع اسکن نامعتبر است.'),
        };
    }

    /**
     * دریافت گزارش‌های اسکن
     */
    public function getReports(array $filters = [], int $perPage = 20)
    {
        $query = ScanReport::query();

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('path', 'like', "%{$search}%");
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * دریافت یک گزارش
     */
    public function getReport(int $id): ?ScanReport
    {
        return ScanReport::find($id);
    }

    /**
     * دریافت فایل‌های قرنطینه
     */
    public function getQuarantineFiles(array $filters = [], int $perPage = 20)
    {
        $query = QuarantineFile::query();

        if (!empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (!empty($filters['is_restored'])) {
            $query->where('is_restored', $filters['is_restored']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('filename', 'like', "%{$search}%")
                  ->orWhere('virus_name', 'like', "%{$search}%");
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * بازیابی فایل از قرنطینه
     */
    public function restoreQuarantineFile(int $id): bool
    {
        $file = QuarantineFile::find($id);

        if (!$file) {
            throw new \Exception('فایل یافت نشد.');
        }

        if ($file->is_restored) {
            throw new \Exception('فایل قبلاً بازیابی شده است.');
        }

        return $file->restoreFile();
    }

    /**
     * حذف دائمی فایل از قرنطینه
     */
    public function deleteQuarantineFile(int $id): bool
    {
        $file = QuarantineFile::find($id);

        if (!$file) {
            throw new \Exception('فایل یافت نشد.');
        }

        return $file->deletePermanently();
    }

    /**
     * دریافت تعاریف ویروس‌ها
     */
    public function getVirusDefinitions(array $filters = [])
    {
        $query = VirusDefinition::query();

        if (!empty($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (!empty($filters['severity'])) {
            $query->bySeverity($filters['severity']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->get();
    }

    /**
     * ایجاد تعریف ویروس جدید
     */
    public function createVirusDefinition(array $data): VirusDefinition
    {
        return VirusDefinition::create($data);
    }

    /**
     * بروزرسانی تعریف ویروس
     */
    public function updateVirusDefinition(VirusDefinition $definition, array $data): VirusDefinition
    {
        $definition->update($data);
        return $definition;
    }

    /**
     * حذف تعریف ویروس
     */
    public function deleteVirusDefinition(VirusDefinition $definition): bool
    {
        return $definition->delete();
    }

    /**
     * دریافت آمار
     */
    public function getStats(): array
    {
        return $this->scanner->getStats();
    }
}
