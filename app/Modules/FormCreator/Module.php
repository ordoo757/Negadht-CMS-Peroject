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

namespace App\Modules\FormCreator;

use App\Core\Foundation\Component;
use App\Modules\FormCreator\Services\FormService;
use App\Modules\FormCreator\Services\AiFormBuilder;

class Module extends Component
{
    protected string $name = 'سازنده فرم‌ها';
    protected string $slug = 'form';
    protected string $version = '2.0.0';
    protected string $author = 'NeuroCMS';
    protected string $description = 'ساخت انواع فرم‌ها با قابلیت‌های پیشرفته و AI';
    protected string $icon = 'form';
    protected string $adminRoute = 'admin.form.index';
    protected array $dependencies = ['User'];

    public function registerModule(): void
    {
        $this->app->singleton('form.service', FormService::class);
        $this->app->singleton('form.ai', AiFormBuilder::class);
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
                'title' => 'فرم‌ها',
                'icon' => 'form',
                'route' => 'admin.form.index',
                'children' => [
                    ['title' => 'لیست فرم‌ها', 'route' => 'admin.form.index'],
                    ['title' => 'ساخت فرم جدید', 'route' => 'admin.form.create'],
                    ['title' => 'سازنده AI', 'route' => 'admin.form.ai-builder'],
                    ['title' => 'پاسخ‌ها', 'route' => 'admin.form.responses'],
                ],
            ],
        ];
    }
}
