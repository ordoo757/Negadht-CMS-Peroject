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
use Illuminate\Support\Facades\Storage;

class QuarantineFile extends Model
{
    protected $table = 'quarantine_files';

    protected $fillable = [
        'original_path',
        'quarantine_path',
        'filename',
        'size',
        'mime_type',
        'reason',
        'virus_name',
        'severity',
        'user_id',
        'is_restored',
        'restored_at',
        'restored_by',
    ];

    protected $casts = [
        'is_restored' => 'boolean',
        'restored_at' => 'datetime',
        'size' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
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

    public function restoreFile(): bool
    {
        if ($this->is_restored) {
            return false;
        }

        if (Storage::exists($this->quarantine_path)) {
            Storage::move($this->quarantine_path, $this->original_path);
        }

        $this->is_restored = true;
        $this->restored_at = now();
        $this->restored_by = auth()->id();
        $this->save();

        return true;
    }

    public function deletePermanently(): bool
    {
        if (Storage::exists($this->quarantine_path)) {
            Storage::delete($this->quarantine_path);
        }

        return $this->delete() ? true : false;
    }

    public function getSizeLabelAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($file) {
            if (empty($file->quarantine_path)) {
                $file->quarantine_path = 'antivirus/quarantine/' . basename($file->original_path);
            }
        });

        static::deleted(function ($file) {
            // حذف فایل فیزیکی در صورت نیاز
        });
    }
}
