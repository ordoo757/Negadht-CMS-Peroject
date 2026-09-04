<?php

namespace App\Core\Foundation;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

abstract class Module extends ServiceProvider
{
    protected string $name = '';
    protected string $slug = '';
    protected string $version = '1.0.0';
    protected string $author = 'NeuroCMS';
    protected string $description = '';
    protected array $dependencies = [];
    protected bool $isCore = false;
    protected bool $isActive = true;

    protected string $modulePath = '';
    protected string $namespace = '';

    public function __construct($app)
    {
        parent::__construct($app);
        $this->modulePath = $this->getModulePath();
        $this->namespace = $this->getNamespace();
    }

    abstract public function registerModule(): void;
    abstract public function bootModule(): void;
    abstract public function install(): bool;
    abstract public function uninstall(): bool;
    abstract public function update(string $oldVersion): bool;

    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getVersion(): string { return $this->version; }
    public function getAuthor(): string { return $this->author; }
    public function getDescription(): string { return $this->description; }
    public function getDependencies(): array { return $this->dependencies; }
    public function isCore(): bool { return $this->isCore; }
    public function isActive(): bool { return $this->isActive; }

    protected function getModulePath(): string
    {
        $reflector = new \ReflectionClass($this);
        return dirname($reflector->getFileName());
    }

    protected function getNamespace(): string
    {
        $reflector = new \ReflectionClass($this);
        return $reflector->getNamespaceName();
    }

    protected function loadRoutes(): void
    {
        $routesPath = $this->modulePath . '/Routes';

        if (File::exists($routesPath . '/web.php')) {
            Route::middleware('web')
                ->namespace($this->namespace . '\Controllers')
                ->group($routesPath . '/web.php');
        }

        if (File::exists($routesPath . '/api.php')) {
            Route::middleware('api')
                ->prefix('api')
                ->namespace($this->namespace . '\Controllers')
                ->group($routesPath . '/api.php');
        }

        if (File::exists($routesPath . '/admin.php')) {
            Route::middleware(['web', 'auth', 'admin'])
                ->prefix('admin')
                ->name('admin.')
                ->namespace($this->namespace . '\Controllers\Admin')
                ->group($routesPath . '/admin.php');
        }
    }

    protected function loadViews(): void
    {
        $viewsPath = $this->modulePath . '/Resources/views';
        if (File::isDirectory($viewsPath)) {
            View::addLocation($viewsPath);
            View::addNamespace($this->slug, $viewsPath);
        }
    }

    protected function loadTranslations(): void
    {
        $langPath = $this->modulePath . '/Resources/lang';
        if (File::isDirectory($langPath)) {
            Lang::addNamespace($this->slug, $langPath);
        }
    }

    protected function loadMigrations(): void
    {
        $migrationsPath = $this->modulePath . '/Database/Migrations';
        if (File::isDirectory($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }

    protected function loadConfig(): void
    {
        $configPath = $this->modulePath . '/Config/config.php';
        if (File::exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'modules.' . $this->slug);
        }
    }

    protected function publishAssets(): void
    {
        $assetsPath = $this->modulePath . '/Resources/assets';
        if (File::isDirectory($assetsPath)) {
            $this->publishes([
                $assetsPath => public_path('modules/' . $this->slug),
            ], 'modules.' . $this->slug . '.assets');
        }
    }

    public function checkDependencies(): array
    {
        $missing = [];
        $moduleRegistry = app('module.registry');

        foreach ($this->dependencies as $dependency) {
            if (!$moduleRegistry->isInstalled($dependency)) {
                $missing[] = $dependency;
            }
        }

        return $missing;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'version' => $this->version,
            'author' => $this->author,
            'description' => $this->description,
            'dependencies' => $this->dependencies,
            'is_core' => $this->isCore,
            'is_active' => $this->isActive,
            'path' => $this->modulePath,
        ];
    }
}

