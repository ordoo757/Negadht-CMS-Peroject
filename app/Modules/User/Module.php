<?php

namespace App\Modules\User;

use App\Core\Foundation\Module as BaseModule;

class Module extends BaseModule
{
    protected string $name = 'مدیریت کاربران';
    protected string $slug = 'user';
    protected string $version = '2.0.0';
    protected string $author = 'NeuroCMS';
    protected string $description = 'مدیریت کاربران، نقش‌ها و مجوزها';
    protected bool $isCore = true;
    protected array $dependencies = [];

    public function registerModule(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadTranslations();
        $this->loadMigrations();
        $this->loadConfig();
    }

    public function bootModule(): void
    {
        $this->publishAssets();
    }

    public function install(): bool
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
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
                'title' => 'کاربران',
                'icon' => 'users',
                'route' => 'admin.user.index',
                'children' => [
                    ['title' => 'لیست کاربران', 'route' => 'admin.user.index'],
                    ['title' => 'نقش‌ها', 'route' => 'admin.user.roles'],
                    ['title' => 'مجوزها', 'route' => 'admin.user.permissions'],
                ],
            ],
        ];
    }
}
