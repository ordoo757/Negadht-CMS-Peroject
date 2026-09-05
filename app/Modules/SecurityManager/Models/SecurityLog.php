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

namespace App\Modules\SecurityManager\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityLog extends Model
{
    protected $table = 'security_logs';

    protected $fillable = [
        'user_id',
        'event',
        'type',
        'ip_address',
        'user_agent',
        'details',
        'risk_level',
        'is_resolved',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'details' => 'json',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /**
     * دریافت کاربر مرتبط
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * دریافت کننده حل کننده
     */
    public function resolver()
    {
        return $this->belongsTo(\App\Models\User::class, 'resolved_by');
    }

    /**
     * اسکوپ لاگ‌های با ریسک بالا
     */
    public function scopeHighRisk($query)
    {
        return $query->where('risk_level', 'high');
    }

    /**
     * اسکوپ لاگ‌های حل نشده
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * ایجاد لاگ جدید
     */
    public static function log(string $event, string $type, array $details = [], string $riskLevel = 'low'): self
    {
        return self::create([
            'user_id' => auth()->id() ?? null,
            'event' => $event,
            'type' => $type,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => $details,
            'risk_level' => $riskLevel,
            'is_resolved' => false,
        ]);
    }

    /**
     * حل کردن لاگ
     */
    public function resolve(): void
    {
        $this->is_resolved = true;
        $this->resolved_at = now();
        $this->resolved_by = auth()->id();
        $this->save();
    }
}
