<?php

/**
 * NeuroCMS - Content Management System
 *
 * @author     Hooman Oliaei (هومان اولیائی)
 * @copyright  Copyright (c) 2026 Hooman Oliaei
 * @license    GNU General Public License v3.0
 * @link       https://github.com/ordoo757
 */
<?php

namespace App\Modules\Antivirus\Controllers\Admin;

use App\Core\Controllers\AdminController;
use App\Modules\Antivirus\Services\AntivirusService;
use Illuminate\Http\Request;

class ReportController extends AdminController
{
    protected AntivirusService $service;

    public function __construct(AntivirusService $service)
    {
        $this->service = $service;
    }

    /**
     * لیست گزارش‌ها
     */
    public function index(Request $request)
    {
        $filters = $request->only(['type', 'status', 'search']);
        $reports = $this->service->getReports($filters);
        $stats = $this->service->getStats();

        return view('antivirus::admin.reports', compact('reports', 'stats', 'filters'));
    }

    /**
     * نمایش جزئیات گزارش
     */
    public function show(int $id)
    {
        $report = $this->service->getReport($id);

        if (!$report) {
            abort(404, 'گزارش یافت نشد.');
        }

        return view('antivirus::admin.report-detail', compact('report'));
    }

    /**
     * خروجی CSV
     */
    public function export(int $id)
    {
        $report = $this->service->getReport($id);

        if (!$report) {
            abort(404, 'گزارش یافت نشد.');
        }

        $filename = "scan_report_{$report->id}_{$report->type}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $result = $report->result ?? [];
        $infectedFiles = $result['infected_files'] ?? [];

        $callback = function() use ($infectedFiles) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['#', 'مسیر فایل', 'ویروس', 'شدت']);

            foreach ($infectedFiles as $index => $file) {
                fputcsv($handle, [
                    $index + 1,
                    $file['path'] ?? 'نامشخص',
                    $file['matches'][0]['name'] ?? 'نامشخص',
                    $file['matches'][0]['severity'] ?? 'نامشخص',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
