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

namespace App\Modules\PluginMaker;

use App\Core\Foundation\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Module extends Component
{
    protected string $name = 'سازنده پلاگین';
    protected string $slug = 'plugin-maker';
    protected string $version = '2.0.0';
    protected string $icon = 'plug';
    protected string $adminRoute = 'admin.plugin-maker.index';
    protected array $dependencies = ['User', 'ModuleMaker'];

    /**
     * ثبت ماژول در Container
     */
    public function registerModule(): void
    {
        try {
            $this->app->singleton('plugin.maker', function ($app) {
                return new \App\Modules\PluginMaker\Services\PluginMakerService();
            });
        } catch (\Exception $e) {
            Log::error('Failed to register PluginMaker service: ' . $e->getMessage());
        }
    }

    /**
     * بوت کردن ماژول
     */
    public function bootModule(): void
    {
        try {
            $this->loadRoutes();
            $this->loadViews();
            $this->loadTranslations();
            $this->registerPluginPaths();
        } catch (\Exception $e) {
            Log::error('Failed to boot PluginMaker module: ' . $e->getMessage());
        }
    }

    /**
     * نصب ماژول
     */
    public function install(): bool
    {
        try {
            // اجرای میگریشن‌ها
            $this->runMigrations();

            // ایجاد پوشه‌های مورد نیاز
            $this->createRequiredDirectories();

            // ثبت تنظیمات اولیه
            $this->registerDefaultConfig();

            // ایجاد پلاگین پیش‌فرض (اختیاری)
            $this->createDefaultPlugin();

            Log::info('PluginMaker module installed successfully.');
            return true;

        } catch (\Exception $e) {
            Log::error('PluginMaker installation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * حذف نصب ماژول
     */
    public function uninstall(): bool
    {
        try {
            // حذف پوشه‌های ایجاد شده
            $this->removeDirectories();

            // پاک کردن کش
            $this->clearPluginCache();

            Log::info('PluginMaker module uninstalled successfully.');
            return true;

        } catch (\Exception $e) {
            Log::error('PluginMaker uninstallation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * بروزرسانی ماژول
     */
    public function update(string $oldVersion): bool
    {
        try {
            // بروزرسانی میگریشن‌ها
            if (version_compare($oldVersion, '2.0.0', '<')) {
                $this->runMigrations();
            }

            // بروزرسانی تنظیمات
            $this->updateConfig();

            Log::info("PluginMaker module updated from {$oldVersion} to {$this->version}");
            return true;

        } catch (\Exception $e) {
            Log::error('PluginMaker update failed: ' . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // متدهای کمکی با مدیریت خطا
    // =========================================================

    /**
     * اجرای میگریشن‌های ماژول
     */
    protected function runMigrations(): void
    {
        try {
            $migrationPath = __DIR__ . '/Migrations';
            if (is_dir($migrationPath)) {
                $this->loadMigrationsFrom($migrationPath);
                Log::info('PluginMaker migrations loaded from: ' . $migrationPath);
            } else {
                Log::warning('PluginMaker migration path not found: ' . $migrationPath);
            }
        } catch (\Exception $e) {
            Log::error('Failed to run PluginMaker migrations: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ایجاد پوشه‌های مورد نیاز
     */
    protected function createRequiredDirectories(): void
    {
        $directories = [
            storage_path('app/plugins'),
            storage_path('app/plugins/temp'),
            public_path('plugins'),
        ];

        foreach ($directories as $dir) {
            try {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                    Log::info('Directory created: ' . $dir);
                }
            } catch (\Exception $e) {
                Log::error('Failed to create directory: ' . $dir . ' - ' . $e->getMessage());
            }
        }
    }

    /**
     * حذف پوشه‌های ایجاد شده
     */
    protected function removeDirectories(): void
    {
        $directories = [
            storage_path('app/plugins'),
            public_path('plugins'),
        ];

        foreach ($directories as $dir) {
            try {
                if (is_dir($dir)) {
                    $this->deleteDirectory($dir);
                    Log::info('Directory removed: ' . $dir);
                }
            } catch (\Exception $e) {
                Log::error('Failed to remove directory: ' . $dir . ' - ' . $e->getMessage());
            }
        }
    }

    /**
     * حذف یک پوشه به صورت بازگشتی
     */
    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    /**
     * ثبت تنظیمات پیش‌فرض
     */
    protected function registerDefaultConfig(): void
    {
        try {
            $config = [
                'plugins' => [
                    'path' => storage_path('app/plugins'),
                    'public_path' => public_path('plugins'),
                    'temp_path' => storage_path('app/plugins/temp'),
                    'default_status' => 'draft',
                    'default_type' => 'general',
                    'max_plugins' => 100,
                    'cache_ttl' => 3600,
                ],
            ];
            
            config()->set('plugins', $config);
            Log::info('PluginMaker default config registered.');
        } catch (\Exception $e) {
            Log::error('Failed to register default config: ' . $e->getMessage());
        }
    }

    /**
     * بروزرسانی تنظیمات
     */
    protected function updateConfig(): void
    {
        try {
            $currentConfig = config('plugins', []);
            $newConfig = array_merge($currentConfig, [
                'updated_at' => now()->toDateTimeString(),
                'version' => $this->version,
            ]);
            config()->set('plugins', $newConfig);
            Log::info('PluginMaker config updated.');
        } catch (\Exception $e) {
            Log::error('Failed to update config: ' . $e->getMessage());
        }
    }

    /**
     * ایجاد پلاگین پیش‌فرض (اختیاری)
     */
    protected function createDefaultPlugin(): void
    {
        try {
            // بررسی وجود جدول plugins
            if (!$this->isTableExists('plugins')) {
                Log::info('Plugins table does not exist yet, skipping default plugin creation.');
                return;
            }

            $pluginModel = new \App\Modules\PluginMaker\Models\Plugin();
            
            // بررسی وجود پلاگین پیش‌فرض
            $existing = $pluginModel->where('slug', 'default-plugin')->first();
            if ($existing) {
                return;
            }

            $pluginModel->create([
                'name' => 'پلاگین پیش‌فرض',
                'slug' => 'default-plugin',
                'description' => 'پلاگین پیش‌فرض سیستم',
                'category' => 'system',
                'type' => 'general',
                'version' => '1.0.0',
                'status' => 'stable',
                'author' => 'System',
                'is_active' => true,
                'is_core' => true,
                'is_system' => true,
                'is_public' => false,
                'is_free' => true,
            ]);

            Log::info('Default plugin created successfully.');
        } catch (\Exception $e) {
            Log::warning('Could not create default plugin: ' . $e->getMessage());
        }
    }

    /**
     * پاک کردن کش پلاگین‌ها
     */
    protected function clearPluginCache(): void
    {
        try {
            \Illuminate\Support\Facades\Cache::forget('plugins_list');
            \Illuminate\Support\Facades\Cache::forget('plugins_categories');
            \Illuminate\Support\Facades\Cache::forget('plugins_stats');
            Log::info('Plugin cache cleared.');
        } catch (\Exception $e) {
            Log::error('Failed to clear plugin cache: ' . $e->getMessage());
        }
    }

    // =========================================================
    // متدهای ثبت مسیرها با مدیریت خطا
    // =========================================================

    /**
     * ثبت مسیرهای پلاگین‌ها
     */
    protected function registerPluginPaths(): void
    {
        try {
            // 1. ثبت Namespace در ویو
            view()->addNamespace('plugins', public_path('plugins'));

            // 2. ثبت مسیر در اتولودر
            $this->registerComposerAutoload();

            // 3. ثبت در کانفیگ
            $this->registerPluginConfig();

            // 4. بارگذاری Helpers
            $this->loadPluginHelpers();

            // 5. ثبت رویدادها
            $this->registerPluginEvents();

            // 6. ثبت در سیستم فایل
            $this->registerPluginStorage();

            // 7. بارگذاری پلاگین‌های فعال
            $this->loadActivePlugins();

        } catch (\Exception $e) {
            Log::error('Failed to register plugin paths: ' . $e->getMessage());
        }
    }

    /**
     * ثبت مسیر پلاگین‌ها در اتولودر کامپوزر
     */
    protected function registerComposerAutoload(): void
    {
        try {
            $composer = require base_path('vendor/autoload.php');
            $pluginPaths = $this->getInstalledPluginPaths();
            
            foreach ($pluginPaths as $pluginPath) {
                if (is_dir($pluginPath)) {
                    $composer->add('Plugins\\', $pluginPath);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to register composer autoload: ' . $e->getMessage());
        }
    }

    /**
     * دریافت مسیرهای پلاگین‌های نصب شده
     */
    protected function getInstalledPluginPaths(): array
    {
        $paths = [];
        $pluginBaseDir = storage_path('app/plugins');
        
        try {
            if (!is_dir($pluginBaseDir)) {
                return $paths;
            }

            $directories = glob($pluginBaseDir . '/*', GLOB_ONLYDIR);
            
            foreach ($directories as $dir) {
                $srcPath = $dir . '/src';
                if (is_dir($srcPath)) {
                    $paths[] = $srcPath;
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to get installed plugin paths: ' . $e->getMessage());
        }
        
        return $paths;
    }

    /**
     * ثبت مسیرهای پلاگین‌ها در کانفیگ
     */
    protected function registerPluginConfig(): void
    {
        try {
            $config = [
                'plugins' => [
                    'path' => storage_path('app/plugins'),
                    'public_path' => public_path('plugins'),
                    'temp_path' => storage_path('app/plugins/temp'),
                ],
            ];
            
            config()->set('plugins', $config);
        } catch (\Exception $e) {
            Log::error('Failed to register plugin config: ' . $e->getMessage());
        }
    }

    /**
     * بارگذاری فایل‌های کمکی (Helpers) پلاگین‌ها
     */
    protected function loadPluginHelpers(): void
    {
        $pluginBaseDir = storage_path('app/plugins');
        
        try {
            if (!is_dir($pluginBaseDir)) {
                return;
            }

            $directories = glob($pluginBaseDir . '/*', GLOB_ONLYDIR);
            
            foreach ($directories as $dir) {
                $helperFile = $dir . '/helpers.php';
                if (file_exists($helperFile)) {
                    require_once $helperFile;
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to load plugin helpers: ' . $e->getMessage());
        }
    }

    /**
     * ثبت رویدادهای (Events) پلاگین‌ها
     */
    protected function registerPluginEvents(): void
    {
        $pluginBaseDir = storage_path('app/plugins');
        
        try {
            if (!is_dir($pluginBaseDir)) {
                return;
            }

            $directories = glob($pluginBaseDir . '/*', GLOB_ONLYDIR);
            
            foreach ($directories as $dir) {
                $eventsFile = $dir . '/events.php';
                if (file_exists($eventsFile)) {
                    $events = require_once $eventsFile;
                    if (is_array($events)) {
                        foreach ($events as $event => $listeners) {
                            foreach ($listeners as $listener) {
                                \Illuminate\Support\Facades\Event::listen($event, $listener);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to register plugin events: ' . $e->getMessage());
        }
    }

    /**
     * ثبت مسیرهای پلاگین‌ها در سیستم فایل (برای ذخیره‌سازی)
     */
    protected function registerPluginStorage(): void
    {
        try {
            \Illuminate\Support\Facades\Storage::extend('plugins', function ($app, $config) {
                return $app['filesystem']->createLocalDriver([
                    'root' => storage_path('app/plugins'),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to register plugin storage: ' . $e->getMessage());
        }
    }

    /**
     * بارگذاری پلاگین‌های فعال
     */
    protected function loadActivePlugins(): void
    {
        // بررسی وجود جدول plugins قبل از کوئری
        if (!$this->isTableExists('plugins')) {
            Log::info('Plugins table does not exist yet, skipping active plugins loading.');
            return;
        }

        try {
            $activePlugins = \App\Modules\PluginMaker\Models\Plugin::where('is_active', true)
                ->whereNotNull('installed_at')
                ->get();
            
            foreach ($activePlugins as $plugin) {
                $this->loadSinglePlugin($plugin);
            }
        } catch (\Exception $e) {
            Log::error('Failed to load active plugins: ' . $e->getMessage());
        }
    }

    /**
     * بارگذاری یک پلاگین خاص
     */
    protected function loadSinglePlugin($plugin): void
    {
        try {
            $pluginPath = $plugin->getFullPath();
            
            if (!is_dir($pluginPath)) {
                Log::warning('Plugin path not found: ' . $pluginPath);
                return;
            }
            
            // بارگذاری فایل bootstrap.php
            $bootstrapFile = $pluginPath . '/bootstrap.php';
            if (file_exists($bootstrapFile)) {
                require_once $bootstrapFile;
                Log::info('Plugin bootstrap loaded: ' . $plugin->slug);
            }
            
            // بارگذاری سرویس‌ها
            $servicesFile = $pluginPath . '/services.php';
            if (file_exists($servicesFile)) {
                $services = require_once $servicesFile;
                if (is_array($services)) {
                    foreach ($services as $service) {
                        if (isset($service['abstract']) && isset($service['concrete'])) {
                            app()->bind($service['abstract'], function ($app) use ($service) {
                                return new $service['concrete']($app);
                            });
                        }
                    }
                }
            }
            
            // بارگذاری مسیرها
            $routesFile = $pluginPath . '/routes.php';
            if (file_exists($routesFile)) {
                $this->loadRoutesFrom($routesFile);
                Log::info('Plugin routes loaded: ' . $plugin->slug);
            }
            
            // بارگذاری ترجمه‌ها
            $langPath = $pluginPath . '/lang';
            if (is_dir($langPath)) {
                $this->loadTranslationsFrom($langPath, $plugin->slug);
                Log::info('Plugin translations loaded: ' . $plugin->slug);
            }
        } catch (\Exception $e) {
            Log::error('Failed to load plugin: ' . $plugin->slug . ' - ' . $e->getMessage());
        }
    }

    // =========================================================
    // متدهای بررسی وضعیت دیتابیس
    // =========================================================

    /**
     * بررسی وجود یک جدول در دیتابیس
     */
    protected function isTableExists(string $tableName): bool
    {
        try {
            // بررسی اتصال به دیتابیس
            if (!$this->isDatabaseConnected()) {
                return false;
            }

            $connection = DB::connection();
            $databaseName = $connection->getDatabaseName();
            
            if (empty($databaseName)) {
                return false;
            }

            // روش استاندارد Laravel
            if (Schema::hasTable($tableName)) {
                return true;
            }

            // روش جایگزین برای MySQL
            $result = $connection->select("SHOW TABLES LIKE '{$tableName}'");
            return !empty($result);

        } catch (\Exception $e) {
            Log::warning('Table check failed for ' . $tableName . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * بررسی اتصال به دیتابیس
     */
    protected function isDatabaseConnected(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::warning('Database connection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * بررسی اینکه آیا ماژول در حال نصب است
     */
    protected function isInstalling(): bool
    {
        // بررسی وجود فایل .installing یا پارامتر خاص
        return file_exists(base_path('.installing'));
    }

    /**
     * دریافت وضعیت کامل ماژول
     */
    public function getStatus(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'version' => $this->version,
            'is_installed' => $this->isInstalled(),
            'database_connected' => $this->isDatabaseConnected(),
            'table_exists' => $this->isTableExists('plugins'),
            'directories_created' => is_dir(storage_path('app/plugins')),
            'config_registered' => config()->has('plugins'),
        ];
    }
}
