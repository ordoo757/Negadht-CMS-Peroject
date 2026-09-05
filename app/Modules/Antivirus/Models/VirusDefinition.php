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

namespace App\Modules\Antivirus\Models;

use Illuminate\Database\Eloquent\Model;

class VirusDefinition extends Model
{
    protected $table = 'virus_definitions';

    protected $fillable = [
        'name',
        'pattern',
        'type',
        'severity',
        'description',
        'is_active',
        'version',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const SEVERITIES = ['low', 'medium', 'high', 'critical'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function getSeverityLabelAttribute(): string
    {
        $labels = [
            'low' => 'کم',
            'medium' => 'متوسط',
            'high' => 'بالا',
            'critical' => 'بحرانی',
        ];

        return $labels[$this->severity] ?? $this->severity;
    }

    public function getSeverityColorAttribute(): string
    {
        $colors = [
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'dark',
        ];

        return $colors[$this->severity] ?? 'secondary';
    }
}
