<?php

namespace App\Modules\SecurityManager;

use App\Core\Foundation\Component;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class Module extends Component
{
    protected string $name = 'مدیریت امنیت';
    protected string $slug = 'security-manager';
    protected string $version = '1.0.0';
    protected string $icon = 'shield-alt';
    protected string $adminRoute = 'admin.security-manager.index';
    protected array $dependencies = ['User'];

    public function registerModule(): void
    {
        $this->app->singleton('security.manager', Services\SecurityService::class);
    }

    public function bootModule(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadTranslations();

        // ثبت میان‌افزارهای امنیتی
        $this->registerSecurityMiddlewares();

        // بارگذاری تنظیمات امنیتی
        $this->loadSecuritySettings();
    }

    public function install(): bool
    {
        $this->runMigrations();
        $this->createDefaultSettings();
        $this->createDefaultBlockedIps();
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

    protected function createDefaultSettings(): void
    {
        $defaults = [
            'max_login_attempts' => 5,
            'block_duration_minutes' => 30,
            'enable_2fa' => false,
            'enable_captcha' => true,
            'enable_firewall' => true,
            'enable_ip_blacklist' => true,
            'enable_ip_whitelist' => false,
            'enable_ssl' => true,
            'security_level' => 'medium',
            'session_timeout' => 60,
            'password_min_length' => 8,
            'enable_activity_log' => true,
            'log_retention_days' => 30,
            'enable_email_notifications' => true,
            'admin_email' => 'admin@neurocms.ir',
        ];

        foreach ($defaults as $key => $value) {
            \App\Modules\SecurityManager\Models\SecuritySetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }

    protected function createDefaultBlockedIps(): void
    {
        // لیست پیش‌فرض آی‌پی‌های مسدود شده
        $defaultIps = [
            // '192.168.1.100',
            // '10.0.0.1',
        ];

        foreach ($defaultIps as $ip) {
            \App\Modules\SecurityManager\Models\BlockedIp::updateOrCreate(
                ['ip_address' => $ip],
                ['reason' => 'مسدود شده توسط سیستم', 'is_permanent' => false]
            );
        }
    }

    protected function registerSecurityMiddlewares(): void
    {
        // ثبت میان‌افزارهای امنیتی
        $this->app['router']->aliasMiddleware('security.firewall', \App\Modules\SecurityManager\Middleware\FirewallMiddleware::class);
        $this->app['router']->aliasMiddleware('security.rate.limit', \App\Modules\SecurityManager\Middleware\RateLimitMiddleware::class);
    }

    protected function loadSecuritySettings(): void
    {
        try {
            $settings = \App\Modules\SecurityManager\Models\SecuritySetting::all();
            foreach ($settings as $setting) {
                config()->set('security.' . $setting->key, $setting->value);
            }
        } catch (\Exception $e) {
            // جدول هنوز وجود ندارد
        }
    }

    public function registerAdminMenu(): array
    {
        return [
            [
                'title' => 'امنیت',
                'icon' => 'shield-alt',
                'route' => 'admin.security-manager.index',
                'children' => [
                    ['title' => 'داشبورد امنیت', 'route' => 'admin.security-manager.index'],
                    ['title' => 'فایروال', 'route' => 'admin.security-manager.firewall'],
                    ['title' => 'لاگ‌های امنیتی', 'route' => 'admin.security-manager.logs'],
                    ['title' => 'تنظیمات امنیتی', 'route' => 'admin.security-manager.settings'],
                ],
            ],
        ];
    }
}
