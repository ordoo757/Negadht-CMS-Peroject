<?php

namespace App\Modules\SecurityManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BlockedIp extends Model
{
    protected $table = 'blocked_ips';

    protected $fillable = [
        'ip_address',
        'reason',
        'is_permanent',
        'blocked_until',
        'blocked_by',
    ];

    protected $casts = [
        'is_permanent' => 'boolean',
        'blocked_until' => 'datetime',
    ];

    /**
     * بررسی مسدود بودن یک آی‌پی
     */
    public static function isBlocked(string $ip): bool
    {
        $cacheKey = "blocked_ip_{$ip}";
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $blocked = self::where('ip_address', $ip)
            ->where(function ($query) {
                $query->where('is_permanent', true)
                    ->orWhere('blocked_until', '>', now());
            })
            ->exists();

        Cache::put($cacheKey, $blocked, 60);
        
        return $blocked;
    }

    /**
     * مسدود کردن یک آی‌پی
     */
    public static function block(string $ip, string $reason = '', int $minutes = 30, bool $permanent = false): self
    {
        $blockedUntil = $permanent ? null : now()->addMinutes($minutes);

        $blocked = self::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason' => $reason,
                'is_permanent' => $permanent,
                'blocked_until' => $blockedUntil,
                'blocked_by' => auth()->id(),
            ]
        );

        Cache::put("blocked_ip_{$ip}", true, $permanent ? 86400 * 365 : $minutes * 60);

        return $blocked;
    }

    /**
     * رفع مسدودیت یک آی‌پی
     */
    public static function unblock(string $ip): bool
    {
        $deleted = self::where('ip_address', $ip)->delete();
        Cache::forget("blocked_ip_{$ip}");
        return $deleted > 0;
    }
}
