<?php

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

    public function __construct()
    {
        $this->modulesPath = app_path('Modules');
        $this->discover();
    }

    public function discover(): void
    {
        if (Cache::has($this->cacheKey)) {
            $data = Cache::get($this->cacheKey);
            $this->modules = $data['modules'] ?? [];
            $this->components = $data['components'] ?? [];
            $this->plugins = $data['plugins'] ?? [];
            return;
        }

        $this->scanModules();
        $this->cacheRegistry();
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

        // Extract namespace
        $namespace = null;
        if (preg_match('/namespace\s+([^;]+);/', $contents, $matches)) {
            $namespace = $matches[1];
        }

        // Extract class name
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
        $data = [
            'modules' => array_map(fn($m) => $m->toArray(), $this->modules),
            'components' => array_map(fn($c) => $c->toArray(), $this->components),
            'plugins' => array_map(fn($p) => $p->toArray(), $this->plugins),
        ];

        Cache::put($this->cacheKey, $data, now()->addHours(24));
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

        // Check dependencies
        $missing = $module->checkDependencies();
        if (!empty($missing)) {
            throw new \Exception("Missing dependencies: " . implode(', ', $missing));
        }

        // Run installation
        $result = $module->install();

        if ($result) {
            // Update database
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
        $extractPath = storage_path('app/temp/' . uniqid());

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return false;
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Detect type and validate
        $manifestFile = $extractPath . '/manifest.json';
        if (!File::exists($manifestFile)) {
            File::deleteDirectory($extractPath);
            return false;
        }

        $manifest = json_decode(File::get($manifestFile), true);
        $type = $manifest['type'] ?? 'module';
        $slug = $manifest['slug'] ?? '';

        if (!$slug) {
            File::deleteDirectory($extractPath);
            return false;
        }

        // Move to appropriate directory
        if ($type === 'component') {
            $targetPath = app_path("Modules/{$slug}");
        } elseif ($type === 'plugin') {
            $targetPath = app_path("Plugins/{$slug}");
        } else {
            $targetPath = app_path("Modules/{$slug}");
        }

        if (File::isDirectory($targetPath)) {
            File::deleteDirectory($targetPath);
        }

        File::moveDirectory($extractPath, $targetPath);
        File::deleteDirectory($extractPath);

        $this->clearCache();

        return $this->install($slug);
    }

    public function exportToZip(string $slug): ?string
    {
        $module = $this->getModule($slug) ?? $this->getComponent($slug);

        if (!$module) {
            return null;
        }

        $sourcePath = $module->toArray()['path'];
        $zipName = "{$slug}-{$module->getVersion()}.zip";
        $zipPath = storage_path("app/exports/{$zipName}");

        if (!File::isDirectory(dirname($zipPath))) {
            File::makeDirectory(dirname($zipPath), 0755, true);
        }

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = File::allFiles($sourcePath);
        foreach ($files as $file) {
            $relativePath = str_replace($sourcePath . '/', '', $file->getPathname());
            $zip->addFile($file->getPathname(), $relativePath);
        }

        $zip->close();

        return $zipPath;
    }
}
