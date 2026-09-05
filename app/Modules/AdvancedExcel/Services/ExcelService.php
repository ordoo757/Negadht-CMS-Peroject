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

namespace App\Modules\AdvancedExcel\Services;

use App\Modules\AdvancedExcel\Models\Workbook;
use App\Modules\AdvancedExcel\Models\Worksheet;
use App\Modules\AdvancedExcel\Models\Cell;
use App\Modules\AdvancedExcel\Models\Chart;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExcelService
{
    // ===== Workbook Management =====

    public function getWorkbooks(array $filters = [], int $perPage = 20)
    {
        $query = Workbook::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_public'])) {
            $query->where('is_public', $filters['is_public']);
        }

        $query->orderBy('created_at', 'desc');
        return $query->paginate($perPage);
    }

    public function getWorkbook(int $id): ?Workbook
    {
        return Cache::remember("workbook_{$id}", 3600, function () use ($id) {
            return Workbook::with('worksheets.cells')->find($id);
        });
    }

    public function createWorkbook(array $data): Workbook
    {
        $workbook = Workbook::create($data);
        $workbook->clearCache();

        // Create default worksheet
        $this->createDefaultWorksheet($workbook);

        Log::info("Workbook created: {$workbook->name} (ID: {$workbook->id})");
        return $workbook;
    }

    protected function createDefaultWorksheet(Workbook $workbook): void
    {
        $worksheet = Worksheet::create([
            'workbook_id' => $workbook->id,
            'name' => 'برگه ۱',
            'order' => 0,
            'is_active' => true,
        ]);

        // Add sample header row
        $headers = ['A1' => 'ستون ۱', 'B1' => 'ستون ۲', 'C1' => 'ستون ۳'];
        foreach ($headers as $cellId => $value) {
            Cell::create([
                'worksheet_id' => $worksheet->id,
                'cell_id' => $cellId,
                'value' => $value,
                'data_type' => 'text',
            ]);
        }
    }

    public function updateWorkbook(Workbook $workbook, array $data): Workbook
    {
        $workbook->update($data);
        $workbook->clearCache();

        Log::info("Workbook updated: {$workbook->name} (ID: {$workbook->id})");
        return $workbook;
    }

    public function deleteWorkbook(Workbook $workbook): bool
    {
        $workbook->delete();
        $workbook->clearCache();

        Log::info("Workbook deleted: {$workbook->name} (ID: {$workbook->id})");
        return true;
    }

    // ===== Worksheet Management =====

    public function createWorksheet(Workbook $workbook, string $name): Worksheet
    {
        $order = $workbook->worksheets()->count();

        $worksheet = Worksheet::create([
            'workbook_id' => $workbook->id,
            'name' => $name,
            'order' => $order,
            'is_active' => true,
        ]);

        $workbook->clearCache();

        Log::info("Worksheet created: {$name} in workbook {$workbook->id}");
        return $worksheet;
    }

    public function updateWorksheet(Worksheet $worksheet, array $data): Worksheet
    {
        $worksheet->update($data);
        $worksheet->workbook->clearCache();

        Log::info("Worksheet updated: {$worksheet->name} (ID: {$worksheet->id})");
        return $worksheet;
    }

    public function deleteWorksheet(Worksheet $worksheet): bool
    {
        $worksheet->delete();
        $worksheet->workbook->clearCache();

        Log::info("Worksheet deleted: {$worksheet->name} (ID: {$worksheet->id})");
        return true;
    }

    // ===== Cell Management =====

    public function updateCell(Worksheet $worksheet, string $cellId, $value, string $formula = null): Cell
    {
        $cell = $worksheet->cells()->updateOrCreate(
            ['cell_id' => $cellId],
            [
                'value' => $value,
                'data_type' => $this->detectDataType($value),
                'formula' => $formula,
            ]
        );

        // Calculate formula if exists
        if ($formula) {
            $calculatedValue = $cell->calculateFormula();
            if ($calculatedValue !== null) {
                $cell->value = $calculatedValue;
                $cell->save();
            }
        }

        return $cell;
    }

    public function getCellValue(Worksheet $worksheet, string $cellId): ?Cell
    {
        return $worksheet->cells()->where('cell_id', $cellId)->first();
    }

    public function deleteCell(Worksheet $worksheet, string $cellId): bool
    {
        $deleted = $worksheet->cells()->where('cell_id', $cellId)->delete();
        $worksheet->workbook->clearCache();
        return $deleted > 0;
    }

    protected function detectDataType($value): string
    {
        if (is_numeric($value)) return 'number';
        if (is_bool($value)) return 'boolean';
        if (strtotime($value) !== false) return 'date';
        return 'text';
    }

    // ===== Chart Management =====

    public function createChart(Worksheet $worksheet, array $data): Chart
    {
        $chart = Chart::create(array_merge($data, ['worksheet_id' => $worksheet->id]));
        $worksheet->workbook->clearCache();

        Log::info("Chart created: {$chart->name} in worksheet {$worksheet->id}");
        return $chart;
    }

    public function updateChart(Chart $chart, array $data): Chart
    {
        $chart->update($data);
        $chart->worksheet->workbook->clearCache();

        Log::info("Chart updated: {$chart->name} (ID: {$chart->id})");
        return $chart;
    }

    public function deleteChart(Chart $chart): bool
    {
        $chart->delete();
        $chart->worksheet->workbook->clearCache();

        Log::info("Chart deleted: {$chart->name} (ID: {$chart->id})");
        return true;
    }

    // ===== Export & Import =====

    public function exportWorkbook(Workbook $workbook): array
    {
        $data = [
            'name' => $workbook->name,
            'description' => $workbook->description,
            'settings' => $workbook->settings,
            'worksheets' => [],
        ];

        foreach ($workbook->worksheets as $worksheet) {
            $sheetData = [
                'name' => $worksheet->name,
                'cells' => [],
            ];

            foreach ($worksheet->cells as $cell) {
                $sheetData['cells'][$cell->cell_id] = [
                    'value' => $cell->value,
                    'data_type' => $cell->data_type,
                    'formula' => $cell->formula,
                    'style' => $cell->style,
                ];
            }

            $data['worksheets'][] = $sheetData;
        }

        return $data;
    }

    // ===== Stats =====

    public function getStats(): array
    {
        return [
            'total_workbooks' => Workbook::count(),
            'active_workbooks' => Workbook::where('is_active', true)->count(),
            'total_worksheets' => Worksheet::count(),
            'total_cells' => Cell::count(),
            'total_charts' => Chart::count(),
        ];
    }

    public function getWorkbookStats(Workbook $workbook): array
    {
        $worksheets = $workbook->worksheets;
        $totalCells = 0;
        $totalCharts = 0;

        foreach ($worksheets as $worksheet) {
            $totalCells += $worksheet->cells()->count();
            $totalCharts += $worksheet->charts()->count();
        }

        return [
            'total_worksheets' => $worksheets->count(),
            'total_cells' => $totalCells,
            'total_charts' => $totalCharts,
            'views' => $workbook->view_count,
        ];
    }
}
