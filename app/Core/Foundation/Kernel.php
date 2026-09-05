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

namespace App\Core\Foundation;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class Kernel
{
    protected ModuleRegistry $registry;

    public function __construct(ModuleRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function monitor(): array
    {
        $startTime = microtime(true);

        $status = [
            'system' => $this->getSystemStatus(),
            'security' => $this->getSecurityStatus(),
            'modules' => $this->getModulesStatus(),
            'performance' => $this->getPerformanceMetrics(),
            'ai_status' => $this->getAiStatus(),
        ];

        $status['response_time'] = round((microtime(true) - $startTime) * 1000, 2) . 'ms';

        return $status;
    }

    protected function getSystemStatus(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'locale' => app()->getLocale(),
        ];
    }

    protected function getSecurityStatus(): array
    {
        $failedLogins = DB::table('activity_logs')
            ->where('action', 'login_failed')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        $blockedIps = DB::table('blocked_ips')
            ->where('blocked_until', '>', now())
            ->count();

        return [
            'failed_logins_24h' => $failedLogins,
            'blocked_ips' => $blockedIps,
            'ssl_enabled' => request()->secure(),
            'maintenance_mode' => app()->isDownForMaintenance(),
        ];
    }

    protected function getModulesStatus(): array
    {
        $modules = $this->registry->getAllModules();
        $components = $this->registry->getAllComponents();

        return [
            'total_modules' => count($modules),
            'active_modules' => count(array_filter($modules, fn($m) => $m->isActive())),
            'total_components' => count($components),
            'active_components' => count(array_filter($components, fn($c) => $c->isActive())),
        ];
    }

    protected function getPerformanceMetrics(): array
    {
        return [
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
        ];
    }

    protected function getAiStatus(): array
    {
        return [
            'ai_available' => app()->bound('ai.service'),
            'default_provider' => 'configured',
            'learning_active' => true,
            'monitoring_active' => true,
        ];
    }
}
