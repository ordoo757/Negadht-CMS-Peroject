<?php

namespace App\Modules\SiteManager;

use App\Core\Foundation\Component;

class Module extends Component
{
    protected string $name = 'مدیریت سایت';
    protected string $slug = 'site-manager';
    protected string $version = '1.0.0';
    protected string $icon = 'globe';
    protected string $adminRoute = 'admin.site-manager.profile';
    protected array $dependencies = ['User'];

    public function registerModule(): void
    {
        $this->app->singleton('site.manager', Services\SiteService::class);
    }

    public function bootModule(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadTranslations();
        
        // بارگذاری تنظیمات سایت
        $this->loadSiteSettings();
    }

    public function install(): bool
    {
        $this->runMigrations();
        $this->createDefaultSettings();
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
            'site_name' => 'NeuroCMS',
            'site_slogan' => 'سیستم مدیریت محتوای هوشمند',
            'site_description' => 'NeuroCMS یک سیستم مدیریت محتوای قدرتمند و هوشمند است.',
            'site_keywords' => 'CMS, NeuroCMS, مدیریت محتوا, هوش مصنوعی',
            'site_email' => 'info@neurocms.ir',
            'site_phone' => '',
            'site_address' => '',
            'site_status' => 'active',
            'maintenance_mode' => false,
            'maintenance_message' => 'سایت در حال بروزرسانی است. لطفاً بعداً مراجعه کنید.',
            'logo' => '',
            'favicon' => '',
            'social_facebook' => '',
            'social_twitter' => '',
            'social_instagram' => '',
            'social_linkedin' => '',
        ];

        foreach ($defaults as $key => $value) {
            \App\Modules\SiteManager\Models\SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }

    protected function loadSiteSettings(): void
    {
        try {
            $settings = \App\Modules\SiteManager\Models\SiteSetting::all();
            foreach ($settings as $setting) {
                config()->set('site.' . $setting->key, $setting->value);
            }
        } catch (\Exception $e) {
            // جدول هنوز وجود ندارد
        }
    }

    public function registerAdminMenu(): array
    {
        return [
            [
                'title' => 'مدیریت سایت',
                'icon' => 'globe',
                'route' => 'admin.site-manager.profile',
                'children' => [
                    ['title' => 'پروفایل سایت', 'route' => 'admin.site-manager.profile'],
                    ['title' => 'تنظیمات عمومی', 'route' => 'admin.site-manager.settings'],
                    ['title' => 'شبکه‌های اجتماعی', 'route' => 'admin.site-manager.social'],
                ],
            ],
        ];
    }
}
