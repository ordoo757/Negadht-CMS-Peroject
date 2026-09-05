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

namespace App\Core\Http\Controllers\Admin;

use App\Core\Controllers\AdminController;
use App\Core\Foundation\ModuleRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ModuleController extends AdminController
{
    protected ModuleRegistry $moduleRegistry;

    public function __construct(ModuleRegistry $moduleRegistry)
    {
        $this->moduleRegistry = $moduleRegistry;
    }

    /**
     * لیست تمام ماژول‌ها
     */
    public function index()
    {
        $modules = $this->moduleRegistry->getAllModules();
        $stats = $this->getStats($modules);

        return view('core::admin.modules.index', compact('modules', 'stats'));
    }

    /**
     * فعال کردن ماژول
     */
    public function activate(string $slug)
    {
        try {
            $this->moduleRegistry->activateModule($slug);
            
            // به‌روزرسانی در دیتابیس
            $this->updateModuleStatus($slug, true);
            
            // پاک کردن کش
            $this->clearCache();

            return redirect()->back()->with('success', "ماژول '{$slug}' با موفقیت فعال شد.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * غیرفعال کردن ماژول
     */
    public function deactivate(string $slug)
    {
        try {
            // بررسی وابستگی‌ها
            $dependents = $this->moduleRegistry->getModulesDependingOn($slug);
            
            if (!empty($dependents)) {
                $names = implode(', ', array_column($dependents, 'name'));
                throw new \Exception("ماژول '{$slug}' توسط ماژول‌های زیر استفاده می‌شود: {$names}");
            }

            $this->moduleRegistry->deactivateModule($slug);
            $this->updateModuleStatus($slug, false);
            $this->clearCache();

            return redirect()->back()->with('success', "ماژول '{$slug}' با موفقیت غیرفعال شد.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * نصب ماژول
     */
    public function install(string $slug)
    {
        try {
            $module = $this->moduleRegistry->getModule($slug);
            
            if (!$module) {
                throw new \Exception("ماژول '{$slug}' یافت نشد.");
            }

            // اجرای متد نصب ماژول
            if (method_exists($module['class'], 'install')) {
                $result = $module['class']->install();
                if ($result === false) {
                    throw new \Exception("نصب ماژول '{$slug}' ناموفق بود.");
                }
            }

            // به‌روزرسانی وضعیت در دیتابیس
            $this->updateModuleInstallStatus($slug, true);
            
            // پاک کردن کش
            $this->clearCache();

            return redirect()->back()->with('success', "ماژول '{$slug}' با موفقیت نصب شد.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * حذف نصب ماژول
     */
    public function uninstall(string $slug)
    {
        try {
            $module = $this->moduleRegistry->getModule($slug);
            
            if (!$module) {
                throw new \Exception("ماژول '{$slug}' یافت نشد.");
            }

            // بررسی وابستگی‌ها
            $dependents = $this->moduleRegistry->getModulesDependingOn($slug);
            if (!empty($dependents)) {
                $names = implode(', ', array_column($dependents, 'name'));
                throw new \Exception("ماژول '{$slug}' توسط ماژول‌های زیر استفاده می‌شود: {$names}");
            }

            // اجرای متد حذف نصب ماژول
            if (method_exists($module['class'], 'uninstall')) {
                $result = $module['class']->uninstall();
                if ($result === false) {
                    throw new \Exception("حذف نصب ماژول '{$slug}' ناموفق بود.");
                }
            }

            // به‌روزرسانی وضعیت در دیتابیس
            $this->updateModuleInstallStatus($slug, false);
            
            // پاک کردن کش
            $this->clearCache();

            return redirect()->back()->with('success', "ماژول '{$slug}' با موفقیت حذف شد.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * دریافت وضعیت ماژول (API)
     */
    public function status(string $slug)
    {
        $module = $this->moduleRegistry->getModule($slug);
        
        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        return response()->json([
            'slug' => $slug,
            'name' => $module['name'] ?? $slug,
            'is_active' => $module['is_active'] ?? false,
            'is_installed' => $module['is_installed'] ?? false,
            'version' => $module['version'] ?? '1.0.0',
            'dependencies' => $module['dependencies'] ?? [],
            'dependents' => $this->moduleRegistry->getModulesDependingOn($slug),
        ]);
    }

    /**
     * محاسبه آمار ماژول‌ها
     */
    protected function getStats(array $modules): array
    {
        $total = count($modules);
        $active = 0;
        $installed = 0;
        $withDependencies = 0;

        foreach ($modules as $module) {
            if ($module['is_active'] ?? false) $active++;
            if ($module['is_installed'] ?? false) $installed++;
            if (!empty($module['dependencies'])) $withDependencies++;
        }

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'installed' => $installed,
            'not_installed' => $total - $installed,
            'with_dependencies' => $withDependencies,
        ];
    }

    /**
     * به‌روزرسانی وضعیت ماژول در دیتابیس
     */
    protected function updateModuleStatus(string $slug, bool $isActive): void
    {
        try {
            if (class_exists(\App\Models\Module::class)) {
                \App\Models\Module::where('slug', $slug)->update([
                    'is_active' => $isActive,
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // جدول یا مدل وجود ندارد
        }
    }

    /**
     * به‌روزرسانی وضعیت نصب ماژول در دیتابیس
     */
    protected function updateModuleInstallStatus(string $slug, bool $isInstalled): void
    {
        try {
            if (class_exists(\App\Models\Module::class)) {
                \App\Models\Module::where('slug', $slug)->update([
                    'is_installed' => $isInstalled,
                    'installed_at' => $isInstalled ? now() : null,
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // جدول یا مدل وجود ندارد
        }
    }

    /**
     * پاک کردن کش ماژول‌ها
     */
    protected function clearCache(): void
    {
        Cache::forget('modules_list');
        Cache::forget('modules_active');
        Cache::forget('module_registry');
    }
}
