<?php

namespace App\Modules\AdvancedExcel\Models;

use Illuminate\Database\Eloquent\Model;

class Worksheet extends Model
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

    public function workbook()
    {
        return $this->belongsTo(Workbook::class);
    }

    public function cells()
    {
        return $this->hasMany(Cell::class);
    }

    public function charts()
    {
        return $this->hasMany(Chart::class);
    }

    public function getCellValue(string $cellId): ?string
    {
        $cell = $this->cells()->where('cell_id', $cellId)->first();
        return $cell ? $cell->value : null;
    }

    public function setCellValue(string $cellId, $value): void
    {
        $dataType = $this->detectDataType($value);
        $this->cells()->updateOrCreate(
            ['cell_id' => $cellId],
            [
                'value' => $value,
                'data_type' => $dataType,
            ]
        );
    }

    public function getRowData(int $rowNumber): array
    {
        $rowData = [];
        $cells = $this->cells()->where('cell_id', 'regexp', "^[A-Z]+{$rowNumber}$")->get();

        foreach ($cells as $cell) {
            $rowData[$cell->cell_id] = $cell->value;
        }

        return $rowData;
    }

    public function getColumnData(string $columnLetter): array
    {
        $columnData = [];
        $cells = $this->cells()->where('cell_id', 'like', "{$columnLetter}%")->get();

        foreach ($cells as $cell) {
            $columnData[$cell->cell_id] = $cell->value;
        }

        return $columnData;
    }

    protected function detectDataType($value): string
    {
        if (is_numeric($value)) return 'number';
        if (is_bool($value)) return 'boolean';
        if (strtotime($value) !== false) return 'date';
        return 'text';
    }

    public function clearCells(): void
    {
        $this->cells()->delete();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($worksheet) {
            $worksheet->cells()->delete();
            $worksheet->charts()->delete();
        });
    }
}
