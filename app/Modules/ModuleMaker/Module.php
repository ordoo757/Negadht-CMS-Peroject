<?php

namespace App\Modules\ModuleMaker;

use App\Core\Foundation\Component;

class Module extends Component
{
    protected string $name = 'سازنده ماژول';
    protected string $slug = 'module-maker';
    protected string $version = '2.0.0';
    protected string $icon = 'puzzle-piece';
    protected string $adminRoute = 'admin.module-maker.index';
    protected array $dependencies = ['User'];

    public function registerModule(): void
    {
        $this->app->singleton('module.maker', Services\ModuleMakerService::class);
    }

    public function bootModule(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadTranslations();
    }

    public function install(): bool
    {
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
                'title' => 'سازنده‌ها',
                'icon' => 'tools',
                'route' => 'admin.module-maker.index',
                'children' => [
                    ['title' => 'سازنده ماژول', 'route' => 'admin.module-maker.index'],
                    ['title' => 'سازنده کامپوننت', 'route' => 'admin.component-maker.index'],
                    ['title' => 'سازنده پلاگین', 'route' => 'admin.plugin-maker.index'],
                ],
            ],
        ];
    }
}
