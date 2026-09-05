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

namespace App\Modules\SecurityManager\Services;

use App\Modules\SecurityManager\Models\SecurityLog;
use App\Modules\SecurityManager\Models\BlockedIp;
use App\Modules\SecurityManager\Models\SecuritySetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SecurityService
{
    /**
     * دریافت تمام تنظیمات امنیتی
     */
    public function getAllSettings(): array
    {
        return SecuritySetting::getAll();
    }

    /**
     * دریافت یک تنظیم خاص
     */
    public function getSetting(string $key, $default = null)
    {
        return SecuritySetting::get($key, $default);
    }

    /**
     * بروزرسانی تنظیمات امنیتی
     */
    public function updateSettings(array $data): bool
    {
        try {
            foreach ($data as $key => $value) {
                SecuritySetting::set($key, $value);
            }
            
            Cache::forget('security_settings_all');
            Log::info('Security settings updated successfully.');
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update security settings: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * دریافت لاگ‌های امنیتی با فیلتر
     */
    public function getLogs(array $filters = [], int $perPage = 20)
    {
        $query = SecurityLog::query();

        if (!empty($filters['event'])) {
            $query->where('event', 'like', "%{$filters['event']}%");
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['risk_level'])) {
            $query->where('risk_level', $filters['risk_level']);
        }

        if (!empty($filters['is_resolved'])) {
            $query->where('is_resolved', $filters['is_resolved']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * دریافت آی‌پی‌های مسدود شده
     */
    public function getBlockedIps()
    {
        return BlockedIp::orderBy('created_at', 'desc')->get();
    }

    /**
     * دریافت آی‌پی‌های مجاز
     */
    public function getWhitelistIps()
    {
        return \App\Modules\SecurityManager\Models\WhitelistIp::orderBy('created_at', 'desc')->get();
    }

    /**
     * مسدود کردن آی‌پی
     */
    public function blockIp(string $ip, string $reason = '', int $minutes = 30, bool $permanent = false): bool
    {
        try {
            BlockedIp::block($ip, $reason, $minutes, $permanent);
            
            SecurityLog::log(
                'ip_blocked',
                'security',
                ['ip' => $ip, 'reason' => $reason, 'permanent' => $permanent],
                'medium'
            );
            
            Log::info("IP {$ip} blocked successfully.");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to block IP {$ip}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * رفع مسدودیت آی‌پی
     */
    public function unblockIp(string $ip): bool
    {
        try {
            BlockedIp::unblock($ip);
            
            SecurityLog::log(
                'ip_unblocked',
                'security',
                ['ip' => $ip],
                'low'
            );
            
            Log::info("IP {$ip} unblocked successfully.");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to unblock IP {$ip}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * دریافت آمار امنیتی
     */
    public function getStats(): array
    {
        $totalLogs = SecurityLog::count();
        $highRiskLogs = SecurityLog::highRisk()->count();
        $unresolvedLogs = SecurityLog::unresolved()->count();
        $blockedIps = BlockedIp::count();

        return [
            'total_logs' => $totalLogs,
            'high_risk_logs' => $highRiskLogs,
            'unresolved_logs' => $unresolvedLogs,
            'blocked_ips' => $blockedIps,
            'today_logs' => SecurityLog::whereDate('created_at', today())->count(),
            'this_week_logs' => SecurityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }

    /**
     * پاک کردن لاگ‌های قدیمی
     */
    public function cleanOldLogs(int $days = 30): int
    {
        $deleted = SecurityLog::where('created_at', '<', now()->subDays($days))->delete();
        Log::info("Cleaned {$deleted} old security logs.");
        return $deleted;
    }
}
