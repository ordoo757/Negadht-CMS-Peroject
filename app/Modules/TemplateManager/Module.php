<?php

namespace App\Modules\TemplateManager;

use App\Core\Foundation\Component;
use App\Modules\TemplateManager\Services\TemplateService;
use App\Modules\TemplateManager\Services\AiTemplateBuilder;

class Module extends Component
{
    protected string $name = 'مدیریت قالب‌ها';
    protected string $slug = 'template';
    protected string $version = '2.0.0';
    protected string $author = 'NeuroCMS';
    protected string $description = 'سیستم مدیریت قالب‌ها با قابلیت ساخت هوشمند';
    protected string $icon = 'layout';
    protected string $adminRoute = 'admin.template.index';
    protected array $dependencies = ['User'];
    protected bool $hasFrontend = true;
    protected bool $hasAdminPanel = true;

    public function registerModule(): void
    {
        $this->app->singleton('template.service', TemplateService::class);
        $this->app->singleton('template.ai', AiTemplateBuilder::class);

        // Register template engine
        $this->app->bind('template.engine', function() {
            return new \App\Modules\TemplateManager\Services\TemplateEngine();
        });
    }

    public function bootModule(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadTranslations();
        $this->loadMigrations();
        $this->loadConfig();

        // Register template positions
        $this->app['events']->listen('template.positions', function() {
            return $this->getTemplatePositions();
        });
    }

    public function install(): bool
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Create default templates
        \Illuminate\Support\Facades\DB::table('templates')->insertOrIgnore([
            [
                'id' => 1,
                'name' => 'قالب پیش‌فرض مدیریت',
                'slug' => 'admin-default',
                'type' => 'admin',
                'is_active' => true,
                'is_default' => true,
                'path' => 'templates/admin/default',
                'config' => json_encode([
                    'header' => true,
                    'sidebar' => true,
                    'footer' => true,
                    'rtl' => true,
                ]),
                'created_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'قالب پیش‌فرض سایت',
                'slug' => 'site-default',
                'type' => 'site',
                'is_active' => true,
                'is_default' => true,
                'path' => 'templates/site/default',
                'config' => json_encode([
                    'header' => true,
                    'sidebar' => true,
                    'footer' => true,
                    'rtl' => true,
                    'responsive' => true,
                ]),
                'created_at' => now(),
            ],
        ]);

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

    public function registerAdminMenu(): array
    {
        return [
            [
                'title' => 'قالب‌ها',
                'icon' => 'layout',
                'route' => 'admin.template.index',
                'children' => [
                    ['title' => 'لیست قالب‌ها', 'route' => 'admin.template.index'],
                    ['title' => 'ساخت قالب جدید', 'route' => 'admin.template.create'],
                    ['title' => 'سازنده قالب AI', 'route' => 'admin.template.ai-builder'],
                    ['title' => 'پوزیشن‌ها', 'route' => 'admin.template.positions'],
                    ['title' => 'تنظیمات قالب', 'route' => 'admin.template.settings'],
                ],
            ],
        ];
    }

    protected function getTemplatePositions(): array
    {
        return [
            'header' => 'هدر',
            'top-bar' => 'نوار بالا',
            'sidebar-left' => 'سایدبار چپ',
            'sidebar-right' => 'سایدبار راست',
            'content-top' => 'بالای محتوا',
            'content-bottom' => 'پایین محتوا',
            'footer' => 'فوتر',
            'bottom-bar' => 'نوار پایین',
            'mobile-header' => 'هدر موبایل',
            'mobile-footer' => 'فوتر موبایل',
        ];
    }
}
