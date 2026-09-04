<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Foundation\ModuleRegistry;
use App\Core\Foundation\Kernel;

class NeuroServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('module.registry', ModuleRegistry::class);
        $this->app->singleton('neuro.kernel', Kernel::class);
    }

    public function boot(): void
    {
        $registry = app('module.registry');

        foreach ($registry->getActiveModules() as $module) {
            $module->registerModule();
        }

        foreach ($registry->getActiveComponents() as $component) {
            $component->registerModule();
        }

        foreach ($registry->getActivePlugins() as $plugin) {
            $plugin->registerPlugin();
        }

        foreach ($registry->getActiveModules() as $module) {
            $module->bootModule();
        }

        foreach ($registry->getActiveComponents() as $component) {
            $component->bootModule();
        }

        foreach ($registry->getActivePlugins() as $plugin) {
            $plugin->bootPlugin();
        }
    }
}
