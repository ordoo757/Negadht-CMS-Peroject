<?php

namespace App\Modules\Antivirus\Models;

use Illuminate\Database\Eloquent\Model;

class ScanReport extends Model
{
    protected $table = 'scan_reports';

    protected $fillable = [
        'user_id',
        'type',
        'path',
        'file_count',
        'infected_count',
        'scanned_count',
        'status',
        'result',
        'details',
        'started_at',
        'completed_at',
        'duration',
    ];

    protected $casts = [
        'details' => 'json',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration' => 'float',
    ];

    const TYPES = ['full', 'custom', 'upload', 'scheduled'];
    const STATUSES = ['pending', 'running', 'completed', 'failed', 'cancelled'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'در انتظار',
            'running' => 'در حال اجرا',
            'completed' => 'تکمیل شده',
            'failed' => 'ناموفق',
            'cancelled' => 'لغو شده',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            'pending' => 'warning',
            'running' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'full' => 'کامل',
            'custom' => 'دلخواه',
            'upload' => 'آپلود',
            'scheduled' => 'زمان‌بندی‌شده',
        ];

        return $labels[$this->type] ?? $this->type;
    }

    public function getSummaryAttribute(): array
    {
        return [
            'total' => $this->file_count,
            'scanned' => $this->scanned_count,
            'infected' => $this->infected_count,
            'safe' => $this->scanned_count - $this->infected_count,
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (empty($report->started_at)) {
                $report->started_at = now();
            }
            if (empty($report->status)) {
                $report->status = 'pending';
            }
        });

        static::updating(function ($report) {
            if ($report->status === 'completed' && empty($report->completed_at)) {
                $report->completed_at = now();
                $report->duration = $report->started_at->diffInSeconds($report->completed_at);
            }
        });
    }
}
