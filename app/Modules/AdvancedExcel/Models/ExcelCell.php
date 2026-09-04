<?php

namespace App\Modules\AdvancedExcel\Models;

use Illuminate\Database\Eloquent\Model;

class ExcelCell extends Model
{
    protected $table = 'excel_cells';

    protected $fillable = [
        'worksheet_id',
        'cell_id',
        'value',
        'data_type',
        'style',
        'formula',
    ];

    protected $casts = [
        'style' => 'json',
    ];

    /**
     * روابط
     */
    public function worksheet()
    {
        return $this->belongsTo(ExcelWorksheet::class);
    }

    /**
     * متدهای کمکی
     */
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
}
