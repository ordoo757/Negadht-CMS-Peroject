<?php

namespace App\Providers;

use App\Core\AI\AIService;
use App\Core\Services\HookManager;
use App\Core\Services\ModuleManager;
use App\Core\Services\ThemeManager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class CmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('hook.manager', fn() => new HookManager());
        $this->app->singleton('module.manager', fn() => new ModuleManager());
        $this->app->singleton('theme.manager', fn() => new ThemeManager());
        $this->app->singleton('ai.service', fn() => new AIService());
        $this->app->singleton('translation.service', fn() => new \App\Modules\Language\Services\TranslationService());
    }

    public function boot(): void
    {
        $this->loadModules();
        $this->registerBladeDirectives();
    }
    
    protected function loadModules(): void
    {
        $manager = app('module.manager');
        foreach ($manager->getInstalledModules() as $slug => $version) {
            $info = $manager->getModuleInfo($slug);
            if ($info && $info['active']) {
                $class = "App\\Modules\\{$info['name']}\\Module";
                if (class_exists($class)) {
                    $module = app($class);
                    $module->registerRoutes();
                    $module->registerViews();
                    $module->registerTranslations();
                }
            }
        }
    }
    
    protected function registerBladeDirectives(): void
    {
        Blade::directive('themePosition', function ($expression) {
            return "<?php echo app('theme.manager')->renderPosition({$expression}); ?>";
        });
        
        Blade::directive('trans', function ($expression) {
            return "<?php echo app('translation.service')->translate({$expression}); ?>";
        });
    }
}
