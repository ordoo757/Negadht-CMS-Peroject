<?php

namespace App\Modules\PluginMaker;

use App\Core\Foundation\Component;

class Module extends Component
{
    protected string $name = 'سازنده پلاگین';
    protected string $slug = 'plugin-maker';
    protected string $version = '2.0.0';
    protected string $icon = 'plug';
    protected string $adminRoute = 'admin.plugin-maker.index';
    protected array $dependencies = ['User', 'ModuleMaker'];

    public function registerModule(): void
    {
        //
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
}
