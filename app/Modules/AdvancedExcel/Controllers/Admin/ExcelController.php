<?php

namespace App\Modules\AdvancedExcel\Controllers\Admin;

use App\Core\Controllers\AdminController;
use App\Modules\AdvancedExcel\Models\Workbook;
use App\Modules\AdvancedExcel\Models\Worksheet;
use App\Modules\AdvancedExcel\Services\ExcelService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ExcelController extends AdminController
{
    protected ExcelService $service;

    public function __construct(ExcelService $service)
    {
        $this->service = $service;
    }

    // ===== Workbook =====

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'is_active', 'is_public']);
        $workbooks = $this->service->getWorkbooks($filters);
        $stats = $this->service->getStats();

        return view('advanced-excel::admin.index', compact('workbooks', 'stats', 'filters'));
    }

    public function create()
    {
        return view('advanced-excel::admin.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'nullable|boolean',
                'is_public' => 'nullable|boolean',
                'settings' => 'nullable|array',
            ]);

            $workbook = $this->service->createWorkbook($validated);

            return redirect()->route('admin.advanced-excel.index')
                ->with('success', "کتاب کار '{$workbook->name}' با موفقیت ایجاد شد.");

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در ایجاد کتاب کار: ' . $e->getMessage())->withInput();
        }
    }

    public function show(int $id)
    {
        $workbook = $this->service->getWorkbook($id);

        if (!$workbook) {
            abort(404, 'کتاب کار یافت نشد.');
        }

        $workbook->incrementViews();
        $stats = $this->service->getWorkbookStats($workbook);

        return view('advanced-excel::admin.show', compact('workbook', 'stats'));
    }

    public function edit(int $id)
    {
        $workbook = $this->service->getWorkbook($id);

        if (!$workbook) {
            abort(404, 'کتاب کار یافت نشد.');
        }

        return view('advanced-excel::admin.editor', compact('workbook'));
    }

    public function update(Request $request, int $id)
    {
        try {
            $workbook = Workbook::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'nullable|boolean',
                'is_public' => 'nullable|boolean',
                'settings' => 'nullable|array',
            ]);

            $this->service->updateWorkbook($workbook, $validated);

            return redirect()->route('admin.advanced-excel.index')
                ->with('success', "کتاب کار '{$workbook->name}' با موفقیت بروزرسانی شد.");

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در بروزرسانی کتاب کار: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(int $id)
    {
        try {
            $workbook = Workbook::findOrFail($id);
            $this->service->deleteWorkbook($workbook);

            return redirect()->route('admin.advanced-excel.index')
                ->with('success', "کتاب کار '{$workbook->name}' با موفقیت حذف شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در حذف کتاب کار: ' . $e->getMessage());
        }
    }

    // ===== Worksheet =====

    public function createWorksheet(Request $request, int $workbookId)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $workbook = Workbook::findOrFail($workbookId);
            $worksheet = $this->service->createWorksheet($workbook, $validated['name']);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'worksheet' => $worksheet,
                ]);
            }

            return redirect()->back()->with('success', "برگه '{$worksheet->name}' با موفقیت ایجاد شد.");

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'خطا در ایجاد برگه: ' . $e->getMessage());
        }
    }

    public function deleteWorksheet(int $worksheetId)
    {
        try {
            $worksheet = Worksheet::findOrFail($worksheetId);
            $this->service->deleteWorksheet($worksheet);

            return redirect()->back()->with('success', "برگه با موفقیت حذف شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در حذف برگه: ' . $e->getMessage());
        }
    }

    // ===== Cell =====

    public function updateCell(Request $request, int $worksheetId)
    {
        try {
            $validated = $request->validate([
                'cell_id' => 'required|string',
                'value' => 'nullable',
                'formula' => 'nullable|string',
            ]);

            $worksheet = Worksheet::findOrFail($worksheetId);

            $cell = $this->service->updateCell(
                $worksheet,
                $validated['cell_id'],
                $validated['value'] ?? null,
                $validated['formula'] ?? null
            );

            return response()->json([
                'success' => true,
                'cell' => $cell,
                'value' => $cell->value,
                'formula' => $cell->formula,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getWorksheetData(int $worksheetId)
    {
        try {
            $worksheet = Worksheet::with('cells')->findOrFail($worksheetId);

            $data = [];
            foreach ($worksheet->cells as $cell) {
                $data[$cell->cell_id] = [
                    'value' => $cell->value,
                    'data_type' => $cell->data_type,
                    'formula' => $cell->formula,
                    'style' => $cell->style,
                ];
            }

            return response()->json([
                'success' => true,
                'worksheet' => $worksheet,
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ===== Chart =====

    public function createChart(Request $request, int $worksheetId)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:column,bar,line,pie,area,scatter,bubble,radar',
                'data_range' => 'required|string',
                'config' => 'nullable|array',
                'position' => 'nullable|array',
                'size' => 'nullable|array',
            ]);

            $worksheet = Worksheet::findOrFail($worksheetId);
            $chart = $this->service->createChart($worksheet, $validated);

            return response()->json([
                'success' => true,
                'chart' => $chart,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteChart(int $chartId)
    {
        try {
            $chart = \App\Modules\AdvancedExcel\Models\Chart::findOrFail($chartId);
            $this->service->deleteChart($chart);

            return response()->json([
                'success' => true,
                'message' => 'نمودار با موفقیت حذف شد.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ===== Embed =====

    public function embed(int $id)
    {
        $workbook = $this->service->getWorkbook($id);

        if (!$workbook || !$workbook->is_public) {
            abort(404, 'کتاب کار یافت نشد یا عمومی نیست.');
        }

        $workbook->incrementViews();

        return view('advanced-excel::admin.embed', compact('workbook'));
    }
}
