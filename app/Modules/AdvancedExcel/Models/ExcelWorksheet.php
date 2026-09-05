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

namespace App\Modules\AdvancedExcel\Models;

use Illuminate\Database\Eloquent\Model;

class ExcelWorksheet extends Model
{
    protected $table = 'excel_worksheets';

    protected $fillable = [
        'workbook_id',
        'name',
        'order',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'json',
        'order' => 'integer',
    ];

    /**
     * روابط
     */
    public function workbook()
    {
        return $this->belongsTo(ExcelWorkbook::class);
    }

    public function cells()
    {
        return $this->hasMany(ExcelCell::class);
    }

    /**
     * متدهای کمکی
     */
    public function getCellValue(string $cellId): ?string
    {
        $cell = $this->cells()->where('cell_id', $cellId)->first();
        return $cell ? $cell->value : null;
    }

    public function setCellValue(string $cellId, $value): void
    {
        $this->cells()->updateOrCreate(
            ['cell_id' => $cellId],
            ['value' => $value, 'data_type' => $this->detectDataType($value)]
        );
    }

    public function getRowData(int $rowNumber): array
    {
        $rowData = [];
        $cells = $this->cells()->where('cell_id', 'like', $rowNumber . '%')->get();

        foreach ($cells as $cell) {
            $rowData[$cell->cell_id] = $cell->value;
        }

        return $rowData;
    }

    protected function detectDataType($value): string
    {
        if (is_numeric($value)) {
            return 'number';
        }
        if (is_bool($value)) {
            return 'boolean';
        }
        if (strtotime($value) !== false) {
            return 'date';
        }
        return 'text';
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($worksheet) {
            $worksheet->cells()->delete();
        });
    }
}
