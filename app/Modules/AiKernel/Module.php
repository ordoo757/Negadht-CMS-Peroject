<?php

namespace App\Modules\AiKernel;

use App\Core\Foundation\Module as BaseModule;
use App\Modules\AiKernel\Services\AiService;
use App\Modules\AiKernel\Services\NlpService;
use App\Modules\AiKernel\Services\LearningService;
use App\Modules\AiKernel\Services\SecurityMonitorService;

class Module extends BaseModule
{
    protected string $name = 'هسته هوش مصنوعی';
    protected string $slug = 'ai';
    protected string $version = '2.0.0';
    protected string $author = 'NeuroCMS AI Team';
    protected string $description = 'هسته هوشمند سیستم با قابلیت‌های AI، NLP، یادگیری و مانیتورینگ امنیتی';
    protected array $dependencies = ['User'];
    protected bool $isCore = true;

    public function registerModule(): void
    {
        $this->app->singleton('ai.service', AiService::class);
        $this->app->singleton('ai.nlp', NlpService::class);
        $this->app->singleton('ai.learning', LearningService::class);
        $this->app->singleton('ai.security', SecurityMonitorService::class);

        // Register facades
        $this->app->alias('ai.service', 'AiKernel');

        // Register AI middleware
        $this->app['router']->aliasMiddleware('ai.monitor', \App\Modules\AiKernel\Middleware\AiMonitorMiddleware::class);
    }

    public function bootModule(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadTranslations();
        $this->loadMigrations();
        $this->loadConfig();

        // Register event listeners
        $this->app['events']->listen('user.login', function($user) {
            app('ai.security')->analyzeLogin($user);
        });

        $this->app['events']->listen('user.activity', function($activity) {
            app('ai.learning')->learnFromActivity($activity);
        });
    }

    public function install(): bool
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Seed default AI configurations
        \Illuminate\Support\Facades\DB::table('ai_configs')->insertOrIgnore([
            ['key' => 'default_provider', 'value' => 'openai', 'group' => 'general'],
            ['key' => 'security_level', 'value' => 'high', 'group' => 'security'],
            ['key' => 'learning_mode', 'value' => 'active', 'group' => 'learning'],
            ['key' => 'monitor_interval', 'value' => '60', 'group' => 'monitoring'],
        ]);

        return true;
    }

    public function uninstall(): bool
    {
        // Don't allow uninstalling core module
        return false;
    }

    public function update(string $oldVersion): bool
    {
        if (version_compare($oldVersion, '2.0.0', '<')) {
            // Run update migrations
        }
        return true;
    }

    public function registerAdminMenu(): array
    {
        return [
            [
                'title' => 'هوش مصنوعی',
                'icon' => 'brain',
                'route' => 'admin.ai.dashboard',
                'children' => [
                    ['title' => 'داشبورد AI', 'route' => 'admin.ai.dashboard'],
                    ['title' => 'تحلیل‌های امنیتی', 'route' => 'admin.ai.security'],
                    ['title' => 'یادگیری سیستم', 'route' => 'admin.ai.learning'],
                    ['title' => 'تنظیمات AI', 'route' => 'admin.ai.settings'],
                ],
            ],
        ];
    }
}
