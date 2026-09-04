<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Controllers\AdminController;
use App\Core\Foundation\ModuleRegistry;
use App\Modules\ComponentMaker\Models\Component;
use App\Modules\PluginMaker\Models\Plugin;
use Illuminate\Support\Facades\DB;

class DashboardController extends AdminController
{
    protected ModuleRegistry $moduleRegistry;

    public function __construct(ModuleRegistry $moduleRegistry)
    {
        $this->moduleRegistry = $moduleRegistry;
    }

    /**
     * نمایش داشبورد اصلی
     */
    public function index()
    {
        // آمار ماژول‌ها
        $modules = $this->moduleRegistry->getAllModules();
        $totalModules = count($modules);
        $activeModules = 0;
        $installedModules = 0;

        foreach ($modules as $module) {
            if ($module['is_active'] ?? false) $activeModules++;
            if ($module['is_installed'] ?? false) $installedModules++;
        }

        // آمار کامپوننت‌ها
        $totalComponents = 0;
        $activeComponents = 0;
        try {
            $totalComponents = Component::count();
            $activeComponents = Component::where('is_active', true)->count();
        } catch (\Exception $e) {
            // جدول هنوز وجود ندارد
        }

        // آمار پلاگین‌ها
        $totalPlugins = 0;
        $activePlugins = 0;
        try {
            $totalPlugins = Plugin::count();
            $activePlugins = Plugin::where('is_active', true)->count();
        } catch (\Exception $e) {
            // جدول هنوز وجود ندارد
        }

        // آمار کاربران
        $totalUsers = DB::table('users')->count() ?? 0;

        // فعالیت‌های اخیر
        $recentActivities = $this->getRecentActivities();

        return view('core::admin.dashboard.index', compact(
            'totalModules',
            'activeModules',
            'installedModules',
            'totalComponents',
            'activeComponents',
            'totalPlugins',
            'activePlugins',
            'totalUsers',
            'recentActivities'
        ));
    }

    /**
     * دریافت فعالیت‌های اخیر
     */
    protected function getRecentActivities(): array
    {
        try {
            return DB::table('activity_logs')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * اطلاعات سیستم
     */
    public function systemInfo()
    {
        return response()->json([
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ]);
    }
}
