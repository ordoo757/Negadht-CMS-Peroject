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

namespace App\Modules\Antivirus;

use App\Core\Foundation\Component;

class Module extends Component
{
    protected string $name = 'ویروس‌یابی و اسکن امنیتی';
    protected string $slug = 'antivirus';
    protected string $version = '1.0.0';
    protected string $icon = 'shield-virus';
    protected string $adminRoute = 'admin.antivirus.index';
    protected array $dependencies = ['User', 'SecurityManager'];

    public function registerModule(): void
    {
        $this->app->singleton('antivirus.service', Services\AntivirusService::class);
        $this->app->singleton('scanner.service', Services\ScannerService::class);
    }

    public function bootModule(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadTranslations();
        $this->loadAssets();

        // ثبت میان‌افزارهای اسکن خودکار
        $this->registerScanMiddleware();
    }

    public function install(): bool
    {
        $this->runMigrations();
        $this->createDefaultVirusDefinitions();
        $this->createRequiredDirectories();
        return true;
    }

    public function uninstall(): bool
    {
        return true;
    }

    public function update(string $oldVersion): bool
    {
        return true;
    }

    protected function runMigrations(): void
    {
        $migrationPath = __DIR__ . '/Migrations';
        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    protected function createDefaultVirusDefinitions(): void
    {
        // تعاریف ویروس‌های پیش‌فرض (الگوهای ساده)
        $definitions = [
            [
                'name' => 'Eval Injection',
                'pattern' => '/eval\s*\(/i',
                'type' => 'php',
                'severity' => 'critical',
                'description' => 'کد حاوی تابع eval() مخرب',
            ],
            [
                'name' => 'Base64 Decode',
                'pattern' => '/base64_decode\s*\(/i',
                'type' => 'php',
                'severity' => 'high',
                'description' => 'استفاده از base64_decode برای اجرای کد',
            ],
            [
                'name' => 'System Command',
                'pattern' => '/(system|exec|shell_exec|passthru)\s*\(/i',
                'type' => 'php',
                'severity' => 'critical',
                'description' => 'فراخوانی دستورات سیستمی',
            ],
            [
                'name' => 'File Write',
                'pattern' => '/(file_put_contents|fopen|fwrite)\s*\(/i',
                'type' => 'php',
                'severity' => 'medium',
                'description' => 'عملیات نوشتن روی فایل‌ها',
            ],
            [
                'name' => 'SQL Injection',
                'pattern' => '/(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|ALTER)\s+.*\s+(FROM|INTO|TABLE|DATABASE)/i',
                'type' => 'sql',
                'severity' => 'high',
                'description' => 'الگوی احتمالی تزریق SQL',
            ],
            [
                'name' => 'JavaScript XSS',
                'pattern' => '/<script\b[^>]*>/i',
                'type' => 'javascript',
                'severity' => 'high',
                'description' => 'کد مخرب XSS در جاوااسکریپت',
            ],
        ];

        foreach ($definitions as $definition) {
            \App\Modules\Antivirus\Models\VirusDefinition::updateOrCreate(
                ['name' => $definition['name']],
                $definition
            );
        }
    }

    protected function createRequiredDirectories(): void
    {
        $directories = [
            storage_path('app/antivirus'),
            storage_path('app/antivirus/quarantine'),
            storage_path('app/antivirus/reports'),
            storage_path('app/antivirus/logs'),
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    protected function loadAssets(): void
    {
        $this->publishes([
            __DIR__ . '/Assets/css' => public_path('assets/antivirus/css'),
            __DIR__ . '/Assets/js' => public_path('assets/antivirus/js'),
        ], 'antivirus-assets');
    }

    protected function registerScanMiddleware(): void
    {
        // ثبت میان‌افزار اسکن خودکار برای آپلود فایل‌ها
        $this->app['router']->aliasMiddleware('scan.upload', \App\Modules\Antivirus\Middleware\ScanUploadMiddleware::class);
    }

    public function registerAdminMenu(): array
    {
        return [
            [
                'title' => 'ویروس‌یابی',
                'icon' => 'shield-virus',
                'route' => 'admin.antivirus.index',
                'children' => [
                    ['title' => 'داشبورد', 'route' => 'admin.antivirus.index'],
                    ['title' => 'اسکن جدید', 'route' => 'admin.antivirus.scan'],
                    ['title' => 'گزارش‌ها', 'route' => 'admin.antivirus.reports'],
                    ['title' => 'قرنطینه', 'route' => 'admin.antivirus.quarantine'],
                ],
            ],
        ];
    }
}
