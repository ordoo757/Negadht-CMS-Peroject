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
use App\Modules\Antivirus\Services\ScannerService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ScanController extends AdminController
{
    protected AntivirusService $service;
    protected ScannerService $scanner;

    public function __construct(AntivirusService $service, ScannerService $scanner)
    {
        $this->service = $service;
        $this->scanner = $scanner;
    }

    /**
     * داشبورد ویروس‌یابی
     */
    public function index()
    {
        $stats = $this->service->getStats();
        $recentReports = $this->service->getReports([], 5);

        return view('antivirus::admin.index', compact('stats', 'recentReports'));
    }

    /**
     * صفحه اسکن جدید
     */
    public function scan()
    {
        return view('antivirus::admin.scan');
    }

    /**
     * اجرای اسکن
     */
    public function startScan(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:quick,full,custom',
                'path' => 'nullable|string',
                'quarantine' => 'nullable|boolean',
            ]);

            $options = [
                'quarantine' => $validated['quarantine'] ?? true,
            ];

            if ($validated['type'] === 'custom' && !empty($validated['path'])) {
                $options['path'] = $validated['path'];
            }

            $report = $this->service->startScan($validated['type'], $options);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'report' => $report,
                    'message' => 'اسکن با موفقیت شروع شد.',
                ]);
            }

            return redirect()->route('admin.antivirus.reports')
                ->with('success', "اسکن '{$report->type_label}' با موفقیت شروع شد.");

        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'خطا در شروع اسکن: ' . $e->getMessage());
        }
    }

    /**
     * اسکن فایل آپلود شده
     */
    public function scanUpload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:20480', // 20MB
            ]);

            $result = $this->scanner->scanUploadedFile($request->file('file'));

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'safe' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * دریافت وضعیت اسکن (AJAX)
     */
    public function status(int $id)
    {
        $report = $this->service->getReport($id);

        if (!$report) {
            return response()->json(['error' => 'Report not found'], 404);
        }

        return response()->json([
            'id' => $report->id,
            'status' => $report->status,
            'status_label' => $report->status_label,
            'file_count' => $report->file_count,
            'infected_count' => $report->infected_count,
            'scanned_count' => $report->scanned_count,
            'progress' => $report->file_count > 0 
                ? round(($report->scanned_count / $report->file_count) * 100, 2)
                : 0,
        ]);
    }

    /**
     * لغو اسکن
     */
    public function cancelScan(int $id)
    {
        $report = $this->service->getReport($id);

        if (!$report) {
            return redirect()->back()->with('error', 'گزارش یافت نشد.');
        }

        if ($report->status !== 'running') {
            return redirect()->back()->with('error', 'اسکن در حال اجرا نیست.');
        }

        $report->status = 'cancelled';
        $report->completed_at = now();
        $report->save();

        return redirect()->route('admin.antivirus.reports')
            ->with('success', "اسکن '{$report->type_label}' با موفقیت لغو شد.");
    }

    /**
     * حذف گزارش
     */
    public function deleteReport(int $id)
    {
        $report = $this->service->getReport($id);

        if (!$report) {
            return redirect()->back()->with('error', 'گزارش یافت نشد.');
        }

        $report->delete();

        return redirect()->route('admin.antivirus.reports')
            ->with('success', 'گزارش با موفقیت حذف شد.');
    }
}
