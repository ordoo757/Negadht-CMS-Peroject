<?php

namespace App\Modules\MenuMaker;

use App\Core\Foundation\Component;
use App\Modules\MenuMaker\Services\MenuService;
use App\Modules\MenuMaker\Services\AiMenuBuilder;

class Module extends Component
{
    protected string $name = 'منو ساز هوشمند';
    protected string $slug = 'menu';
    protected string $version = '2.0.0';
    protected string $author = 'NeuroCMS';
    protected string $description = 'ساخت منوهای هوشمند با هدایت AI و امکانات پیشرفته';
    protected string $icon = 'menu';
    protected string $adminRoute = 'admin.menu.index';
    protected array $dependencies = ['User', 'AiKernel'];
    protected bool $hasFrontend = true;
    protected bool $hasAdminPanel = true;

    public function registerModule(): void
    {
        $this->app->singleton('menu.service', MenuService::class);
        $this->app->singleton('menu.ai', AiMenuBuilder::class);
    }

    public function bootModule(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadTranslations();
        $this->loadMigrations();
        $this->loadConfig();
    }

    public function install(): bool
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Seed default menus
        \Illuminate\Support\Facades\DB::table('menus')->insertOrIgnore([
            [
                'id' => 1,
                'name' => 'منوی اصلی سایت',
                'slug' => 'main-menu',
                'position' => 'header',
                'language' => 'fa',
                'is_active' => true,
                'created_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'منوی مدیریت',
                'slug' => 'admin-menu',
                'position' => 'sidebar',
                'language' => 'fa',
                'is_active' => true,
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
                'title' => 'منو ساز',
                'icon' => 'menu',
                'route' => 'admin.menu.index',
                'children' => [
                    ['title' => 'لیست منوها', 'route' => 'admin.menu.index'],
                    ['title' => 'ساخت منوی جدید', 'route' => 'admin.menu.create'],
                    ['title' => 'منو ساز AI', 'route' => 'admin.menu.ai-builder'],
                    ['title' => 'تنظیمات منو', 'route' => 'admin.menu.settings'],
                ],
            ],
        ];
    }
}
