<?php

namespace App\Modules\User;

use App\Core\Foundation\Module;

class UserModule extends Module
{
    protected string $name = 'User Management';
    protected string $slug = 'user';
    protected string $version = '1.0.0';
    protected string $author = 'NeuroCMS';
    protected string $description = 'User management system';
    protected bool $isCore = true;

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
}
