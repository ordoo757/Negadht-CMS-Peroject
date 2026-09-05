<?php

/**
 * NeuroCMS - Content Management System
 *
 * @author     Hooman Oliaei (هومان اولیائی)
 * @copyright  Copyright (c) 2026 Hooman Oliaei
 * @license    GNU General Public License v3.0
 * @link       https://github.com/ordoo757
 */

namespace App\Core\Foundation;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModuleRegistry
{
    protected array $modules = [];
    protected array $components = [];
    protected array $plugins = [];
    protected string $modulesPath;
    protected string $cacheKey = 'neurocms_modules_registry';
    protected bool $useCache = true;

    public function __construct()
    {
        $this->modulesPath = app_path('Modules');
        $this->discover();
    }

    public function discover(): void
    {
        // اگر محیط تست است، کش را غیرفعال کن
        if (app()->environment('testing')) {
            $this->useCache = false;
        }

        if ($this->useCache) {
            try {
                if (Cache::has($this->cacheKey)) {
                    $data = Cache::get($this->cacheKey);
                    $this->modules = $data['modules'] ?? [];
                    $this->components = $data['components'] ?? [];
                    $this->plugins = $data['plugins'] ?? [];
                    return;
                }
            } catch (\Throwable $e) {
                // در صورت بروز هر خطایی در کش، آن را غیرفعال کن
                $this->useCache = false;
                Log::warning('Cache disabled for ModuleRegistry: ' . $e->getMessage());
            }
        }

        $this->scanModules();

        if ($this->useCache) {
            try {
                $this->cacheRegistry();
            } catch (\Throwable $e) {
                Log::warning('Failed to cache ModuleRegistry: ' . $e->getMessage());
            }
        }
    }

    protected function scanModules(): void
    {
        if (!File::isDirectory($this->modulesPath)) {
            return;
        }

        $directories = File::directories($this->modulesPath);

        foreach ($directories as $dir) {
            $moduleFile = $dir . '/Module.php';

            if (File::exists($moduleFile)) {
                try {
                    $className = $this->getClassFromFile($moduleFile);
                    if ($className && class_exists($className)) {
                        $instance = app($className);

                        if ($instance instanceof Component) {
                            $this->components[$instance->getSlug()] = $instance;
                        } elseif ($instance instanceof Module) {
                            $this->modules[$instance->getSlug()] = $instance;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to load module from {$dir}: " . $e->getMessage());
                }
            }
        }

        // Scan plugins
        $pluginsPath = app_path('Plugins');
        if (File::isDirectory($pluginsPath)) {
            $pluginDirs = File::directories($pluginsPath);
            foreach ($pluginDirs as $dir) {
                $pluginFile = $dir . '/Plugin.php';
                if (File::exists($pluginFile)) {
                    try {
                        $className = $this->getClassFromFile($pluginFile);
                        if ($className && class_exists($className)) {
                            $instance = app($className);
                            if ($instance instanceof Plugin) {
                                $this->plugins[$instance->getSlug()] = $instance;
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("Failed to load plugin from {$dir}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    protected function getClassFromFile(string $file): ?string
    {
        $contents = File::get($file);

        $namespace = null;
        if (preg_match('/namespace\s+([^;]+);/', $contents, $matches)) {
            $namespace = $matches[1];
        }

        $class = null;
        if (preg_match('/class\s+(\w+)/', $contents, $matches)) {
            $class = $matches[1];
        }

        if ($namespace && $class) {
            return $namespace . '\\' . $class;
        }

        return null;
    }

    protected function cacheRegistry(): void
    {
        if (!$this->useCache) {
            return;
        }

        try {
            $data = [
                'modules' => array_map(fn($m) => $m->toArray(), $this->modules),
                'components' => array_map(fn($c) => $c->toArray(), $this->components),
                'plugins' => array_map(fn($p) => $p->toArray(), $this->plugins),
            ];

            Cache::put($this->cacheKey, $data, now()->addHours(24));
        } catch (\Throwable $e) {
            Log::warning('Failed to cache ModuleRegistry: ' . $e->getMessage());
        }
    }

    public function getModule(string $slug): ?Module
    {
        return $this->modules[$slug] ?? null;
    }

    public function getComponent(string $slug): ?Component
    {
        return $this->components[$slug] ?? null;
    }

    public function getPlugin(string $slug): ?Plugin
    {
        return $this->plugins[$slug] ?? null;
    }

    public function getAllModules(): array
    {
        return $this->modules;
    }

    public function getAllComponents(): array
    {
        return $this->components;
    }

    public function getAllPlugins(): array
    {
        return $this->plugins;
    }

    public function getActiveModules(): array
    {
        return array_filter($this->modules, fn($m) => $m->isActive());
    }

    public function getActiveComponents(): array
    {
        return array_filter($this->components, fn($c) => $c->isActive());
    }

    public function getActivePlugins(): array
    {
        return array_filter($this->plugins, fn($p) => $p->isActive());
    }

    public function isInstalled(string $slug): bool
    {
        return isset($this->modules[$slug]) || isset($this->components[$slug]);
    }

    public function isActive(string $slug): bool
    {
        $module = $this->getModule($slug) ?? $this->getComponent($slug);
        return $module ? $module->isActive() : false;
    }

    public function install(string $slug): bool
    {
        $module = $this->getModule($slug) ?? $this->getComponent($slug);

        if (!$module) {
            return false;
        }

        $missing = $module->checkDependencies();
        if (!empty($missing)) {
            throw new \Exception("Missing dependencies: " . implode(', ', $missing));
        }

        $result = $module->install();

        if ($result) {
            DB::table('modules')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $module->getName(),
                    'version' => $module->getVersion(),
                    'type' => $module instanceof Component ? 'component' : 'module',
                    'is_active' => true,
                    'installed_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->cacheRegistry();
        }

        return $result;
    }

    public function uninstall(string $slug): bool
    {
        $module = $this->getModule($slug) ?? $this->getComponent($slug);

        if (!$module) {
            return false;
        }

        $result = $module->uninstall();

        if ($result) {
            DB::table('modules')->where('slug', $slug)->delete();
            $this->cacheRegistry();
        }

        return $result;
    }

    public function activate(string $slug): bool
    {
        $module = $this->getModule($slug) ?? $this->getComponent($slug);

        if (!$module) {
            return false;
        }

        DB::table('modules')
            ->where('slug', $slug)
            ->update(['is_active' => true, 'updated_at' => now()]);

        $this->cacheRegistry();
        return true;
    }

    public function deactivate(string $slug): bool
    {
        $module = $this->getModule($slug) ?? $this->getComponent($slug);

        if (!$module || $module->isCore()) {
            return false;
        }

        DB::table('modules')
            ->where('slug', $slug)
            ->update(['is_active' => false, 'updated_at' => now()]);

        $this->cacheRegistry();
        return true;
    }

    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
        $this->discover();
    }

    public function installFromZip(string $zipPath): bool
    {
        // ...
        return true;
    }

    public function exportToZip(string $slug): ?string
    {
        // ...
        return null;
    }
}
