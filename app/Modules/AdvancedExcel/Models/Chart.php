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

class Chart extends Model
{
    protected $table = 'excel_charts';

    protected $fillable = [
        'worksheet_id',
        'name',
        'type',
        'data_range',
        'config',
        'position',
        'size',
    ];

    protected $casts = [
        'config' => 'json',
        'position' => 'json',
        'size' => 'json',
    ];

    const TYPES = [
        'column' => 'نمودار ستونی',
        'bar' => 'نمودار میله‌ای',
        'line' => 'نمودار خطی',
        'pie' => 'نمودار دایره‌ای',
        'area' => 'نمودار مساحتی',
        'scatter' => 'نمودار پراکندگی',
        'bubble' => 'نمودار حبابی',
        'radar' => 'نمودار راداری',
    ];

    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class);
    }

    public function getData(array $worksheetData): array
    {
        $data = [];
        $range = $this->data_range;

        if (!$range) {
            return $data;
        }

        $cells = $this->worksheet->cells()->where('cell_id', 'regexp', "^[A-Z]+\\d+$")->get();
        $cellMap = [];

        foreach ($cells as $cell) {
            $cellMap[$cell->cell_id] = $cell->value;
        }

        $parts = explode(':', $range);
        if (count($parts) !== 2) {
            return $data;
        }

        preg_match('/([A-Z]+)(\d+)/', $parts[0], $start);
        preg_match('/([A-Z]+)(\d+)/', $parts[1], $end);

        if (!$start || !$end) {
            return $data;
        }

        $startCol = $this->letterToNumber($start[1]);
        $endCol = $this->letterToNumber($end[1]);
        $startRow = (int) $start[2];
        $endRow = (int) $end[2];

        for ($row = $startRow; $row <= $endRow; $row++) {
            $rowData = [];
            for ($col = $startCol; $col <= $endCol; $col++) {
                $cellId = $this->numberToLetter($col) . $row;
                $rowData[] = $cellMap[$cellId] ?? null;
            }
            $data[] = $rowData;
        }

        return $data;
    }

    protected function letterToNumber(string $letter): int
    {
        $number = 0;
        $letter = strtoupper($letter);
        $len = strlen($letter);

        for ($i = 0; $i < $len; $i++) {
            $number = $number * 26 + (ord($letter[$i]) - 64);
        }

        return $number;
    }

    protected function numberToLetter(int $number): string
    {
        $letter = '';

        while ($number > 0) {
            $number--;
            $letter = chr($number % 26 + 65) . $letter;
            $number = intval($number / 26);
        }

        return $letter;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($chart) {
            // حذف وابسته‌ها در صورت نیاز
        });
    }
}
