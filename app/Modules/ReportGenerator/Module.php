<?php

namespace App\Modules\ReportGenerator;

use App\Core\Foundation\Component;
use App\Modules\ReportGenerator\Services\ReportService;

class Module extends Component
{
    protected string $name = 'گزارش‌ساز';
    protected string $slug = 'report';
    protected string $version = '2.0.0';
    protected string $author = 'NeuroCMS';
    protected string $description = 'ساخت گزارش‌های حرفه‌ای با نمودار و جدول';
    protected string $icon  = 'chart';
    protected string $adminRoute = 'admin.report.index';
    protected array $dependencies = ['User'];

    public function registerModule(): void
    {
        $this->app->singleton('report.service', ReportService::class);
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
                'title' => 'گزارش‌ها',
                'icon' => 'chart',
                'route' => 'admin.report.index',
                'children' => [
                    ['title' => 'لیست گزارش‌ها', 'route' => 'admin.report.index'],
                    ['title' => 'ساخت گزارش', 'route' => 'admin.report.create'],
                    ['title' => 'گزارش سیستم', 'route' => 'admin.report.system'],
                ],
            ],
        ];
    }
}
