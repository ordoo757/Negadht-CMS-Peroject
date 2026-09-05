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

namespace App\Modules\LanguageManager;

use App\Core\Foundation\Module as BaseModule;

class Module extends BaseModule
{
    protected string $name = 'مدیریت زبان‌ها';
    protected string $slug = 'language';
    protected string $version = '2.0.0';
    protected bool $isCore = true;
    protected array $dependencies = [];

    public function registerModule(): void
    {
        $this->app->singleton('language.manager', Services\LanguageService::class);
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

        \Illuminate\Support\Facades\DB::table('languages')->insertOrIgnore([
            ['code' => 'fa', 'name' => 'فارسی', 'native_name' => 'فارسی', 'rtl' => true, 'is_active' => true, 'is_default' => true],
            ['code' => 'ar', 'name' => 'العربية', 'native_name' => 'العربية', 'rtl' => true, 'is_active' => true, 'is_default' => false],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'rtl' => false, 'is_active' => true, 'is_default' => false],
        ]);

        return true;
    }

    public function uninstall(): bool
    {
        return false;
    }

    public function update(string $oldVersion): bool
    {
        return true;
    }

    public function registerAdminMenu(): array
    {
        return [
            [
                'title' => 'زبان‌ها',
                'icon' => 'language',
                'route' => 'admin.language.index',
                'children' => [
                    ['title' => 'لیست زبان‌ها', 'route' => 'admin.language.index'],
                    ['title' => 'افزودن زبان', 'route' => 'admin.language.create'],
                    ['title' => 'ترجمه‌ها', 'route' => 'admin.language.translations'],
                ],
            ],
        ];
    }
}
