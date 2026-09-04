<?php

namespace App\Modules\AdvancedExcel\Models;

use Illuminate\Database\Eloquent\Model;

class Cell extends Model
{
    protected $table = 'excel_cells';

    protected $fillable = [
        'worksheet_id',
        'cell_id',
        'value',
        'data_type',
        'format',
        'style',
        'formula',
    ];

    protected $casts = [
        'style' => 'json',
    ];

    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class);
    }

    public function getDisplayValueAttribute(): string
    {
        if ($this->formula) {
            return '=' . $this->formula;
        }
        return $this->value ?? '';
    }

    public function getParsedValueAttribute()
    {
        if ($this->data_type === 'number') {
            return (float) $this->value;
        }
        if ($this->data_type === 'boolean') {
            return (bool) $this->value;
        }
        if ($this->data_type === 'date') {
            return \Carbon\Carbon::parse($this->value);
        }
        return $this->value;
    }

    public function applyStyle(array $style): void
    {
        $currentStyle = $this->style ?? [];
        $this->style = array_merge($currentStyle, $style);
        $this->save();
    }

    public function getStyleAttribute($value): array
    {
        return json_decode($value, true) ?? [];
    }

    public function calculateFormula(): ?string
    {
        if (!$this->formula) {
            return $this->value;
        }

        $worksheet = $this->worksheet;
        $formula = $this->formula;

        // SUM
        if (preg_match('/SUM\(([^)]+)\)/', $formula, $matches)) {
            $range = $matches[1];
            $sum = $this->calculateSum($range, $worksheet);
            return (string) $sum;
        }

        // AVERAGE
        if (preg_match('/AVERAGE\(([^)]+)\)/', $formula, $matches)) {
            $range = $matches[1];
            $avg = $this->calculateAverage($range, $worksheet);
            return (string) $avg;
        }

        // COUNT
        if (preg_match('/COUNT\(([^)]+)\)/', $formula, $matches)) {
            $range = $matches[1];
            $count = $this->calculateCount($range, $worksheet);
            return (string) $count;
        }

        // MAX
        if (preg_match('/MAX\(([^)]+)\)/', $formula, $matches)) {
            $range = $matches[1];
            $max = $this->calculateMax($range, $worksheet);
            return (string) $max;
        }

        // MIN
        if (preg_match('/MIN\(([^)]+)\)/', $formula, $matches)) {
            $range = $matches[1];
            $min = $this->calculateMin($range, $worksheet);
            return (string) $min;
        }

        return $this->value;
    }

    protected function getRangeCells(string $range, Worksheet $worksheet): array
    {
        $cells = [];
        $parts = explode(':', $range);

        if (count($parts) === 1) {
            $cell = $worksheet->cells()->where('cell_id', $parts[0])->first();
            return $cell ? [$cell] : [];
        }

        $start = $parts[0];
        $end = $parts[1];

        // parse cell coordinates
        preg_match('/([A-Z]+)(\d+)/', $start, $startMatch);
        preg_match('/([A-Z]+)(\d+)/', $end, $endMatch);

        if (!$startMatch || !$endMatch) {
            return [];
        }

        $startCol = $this->letterToNumber($startMatch[1]);
        $endCol = $this->letterToNumber($endMatch[1]);
        $startRow = (int) $startMatch[2];
        $endRow = (int) $endMatch[2];

        for ($row = $startRow; $row <= $endRow; $row++) {
            for ($col = $startCol; $col <= $endCol; $col++) {
                $colLetter = $this->numberToLetter($col);
                $cellId = $colLetter . $row;
                $cell = $worksheet->cells()->where('cell_id', $cellId)->first();
                if ($cell) {
                    $cells[] = $cell;
                }
            }
        }

        return $cells;
    }

    protected function calculateSum(string $range, Worksheet $worksheet): float
    {
        $cells = $this->getRangeCells($range, $worksheet);
        $sum = 0;

        foreach ($cells as $cell) {
            if (is_numeric($cell->value)) {
                $sum += (float) $cell->value;
            }
        }

        return $sum;
    }

    protected function calculateAverage(string $range, Worksheet $worksheet): float
    {
        $cells = $this->getRangeCells($range, $worksheet);
        $sum = 0;
        $count = 0;

        foreach ($cells as $cell) {
            if (is_numeric($cell->value)) {
                $sum += (float) $cell->value;
                $count++;
            }
        }

        return $count > 0 ? $sum / $count : 0;
    }

    protected function calculateCount(string $range, Worksheet $worksheet): int
    {
        $cells = $this->getRangeCells($range, $worksheet);
        return count($cells);
    }

    protected function calculateMax(string $range, Worksheet $worksheet): float
    {
        $cells = $this->getRangeCells($range, $worksheet);
        $max = null;

        foreach ($cells as $cell) {
            if (is_numeric($cell->value)) {
                if ($max === null || (float) $cell->value > $max) {
                    $max = (float) $cell->value;
                }
            }
        }

        return $max ?? 0;
    }

    protected function calculateMin(string $range, Worksheet $worksheet): float
    {
        $cells = $this->getRangeCells($range, $worksheet);
        $min = null;

        foreach ($cells as $cell) {
            if (is_numeric($cell->value)) {
                if ($min === null || (float) $cell->value < $min) {
                    $min = (float) $cell->value;
                }
            }
        }

        return $min ?? 0;
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
}
