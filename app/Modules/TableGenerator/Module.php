<?php

namespace App\Modules\TableGenerator;

use App\Core\Foundation\Component;
use App\Modules\TableGenerator\Services\TableService;

class Module extends Component
{
    protected string $name = 'سازنده جداول';
    protected string $slug  = 'table';
    protected string $version = '2.0.0';
    protected string $author = 'NeuroCMS';
    protected string $description = 'ساخت انواع جداول و لیست‌ها با امکانات پیشرفته';
    protected string $icon = 'table';
    protected string $adminRoute = 'admin.table.index';
    protected array $dependencies = ['User'];

    public function registerModule(): void
    {
        $this->app->singleton('table.service', TableService::class);
    }

    public function bootModule(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadTranslations();
        $this->loadMigrations();
    }

    public function install(): bool
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
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
                'title' => 'جداول',
                'icon' => 'table',
                'route' => 'admin.table.index',
                'children' => [
                    ['title' => 'لیست جداول', 'route' => 'admin.table.index'],
                    ['title' => 'ساخت جدول', 'route' => 'admin.table.create'],
                ],
            ],
        ];
    }
}
