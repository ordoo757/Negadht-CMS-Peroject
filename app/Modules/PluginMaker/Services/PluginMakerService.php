<?php

namespace App\Modules\PluginMaker\Services;

use App\Modules\PluginMaker\Models\Plugin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PluginMakerService
{
    protected array $supportedTypes = ['general', 'seo', 'security', 'analytics', 'social', 'marketing', 'payment', 'crm', 'ecommerce', 'custom'];
    protected array $allowedStatuses = ['draft', 'stable', 'beta', 'alpha', 'deprecated', 'archived'];

    public function create(array $data): Plugin
    {
        $validated = $this->validateCreate($data);
        $slug = $this->generateUniqueSlug($validated['name']);

        $plugin = Plugin::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? '',
            'category' => $validated['category'] ?? 'general',
            'type' => $validated['type'] ?? 'general',
            'version' => $validated['version'] ?? '1.0.0',
            'status' => $validated['status'] ?? 'draft',
            'author' => $validated['author'] ?? auth()->user()->email ?? 'system',
            'author_email' => $validated['author_email'] ?? auth()->user()->email ?? null,
            'website' => $validated['website'] ?? null,
            'license' => $validated['license'] ?? 'MIT',
            'price' => $validated['price'] ?? 0,
            'is_free' => $validated['is_free'] ?? true,
            'config' => $validated['config'] ?? [],
            'settings' => $validated['settings'] ?? [],
            'tags' => $validated['tags'] ?? [],
            'dependencies' => $validated['dependencies'] ?? [],
            'screenshots' => $validated['screenshots'] ?? [],
            'preview_image' => $validated['preview_image'] ?? null,
            'view_path' => $validated['view_path'] ?? null,
            'style_path' => $validated['style_path'] ?? null,
            'script_path' => $validated['script_path'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'is_core' => $validated['is_core'] ?? false,
            'is_system' => $validated['is_system'] ?? false,
            'is_public' => $validated['is_public'] ?? true,
        ]);

        $this->createPluginFolders($plugin);
        $this->createBaseFiles($plugin);

        Log::info("Plugin created: {$plugin->name} ({$plugin->slug})");

        return $plugin;
    }

    public function update(Plugin $plugin, array $data): Plugin
    {
        $validated = $this->validateUpdate($data);

        $plugin->update([
            'name' => $validated['name'] ?? $plugin->name,
            'description' => $validated['description'] ?? $plugin->description,
            'category' => $validated['category'] ?? $plugin->category,
            'type' => $validated['type'] ?? $plugin->type,
            'version' => $validated['version'] ?? $plugin->version,
            'status' => $validated['status'] ?? $plugin->status,
            'author' => $validated['author'] ?? $plugin->author,
            'author_email' => $validated['author_email'] ?? $plugin->author_email,
            'website' => $validated['website'] ?? $plugin->website,
            'license' => $validated['license'] ?? $plugin->license,
            'price' => $validated['price'] ?? $plugin->price,
            'is_free' => $validated['is_free'] ?? $plugin->is_free,
            'config' => $validated['config'] ?? $plugin->config,
            'settings' => $validated['settings'] ?? $plugin->settings,
            'tags' => $validated['tags'] ?? $plugin->tags,
            'dependencies' => $validated['dependencies'] ?? $plugin->dependencies,
            'screenshots' => $validated['screenshots'] ?? $plugin->screenshots,
            'preview_image' => $validated['preview_image'] ?? $plugin->preview_image,
            'view_path' => $validated['view_path'] ?? $plugin->view_path,
            'style_path' => $validated['style_path'] ?? $plugin->style_path,
            'script_path' => $validated['script_path'] ?? $plugin->script_path,
            'is_active' => $validated['is_active'] ?? $plugin->is_active,
            'is_core' => $validated['is_core'] ?? $plugin->is_core,
            'is_system' => $validated['is_system'] ?? $plugin->is_system,
            'is_public' => $validated['is_public'] ?? $plugin->is_public,
        ]);

        $plugin->clearCache();
        Log::info("Plugin updated: {$plugin->name} ({$plugin->slug})");

        return $plugin;
    }

    public function delete(Plugin $plugin): bool
    {
        $plugin->delete();
        $plugin->clearCache();
        Log::info("Plugin deleted: {$plugin->name} ({$plugin->slug})");
        return true;
    }

    public function forceDelete(Plugin $plugin): bool
    {
        $this->deletePluginFolders($plugin);
        $plugin->forceDelete();
        $plugin->clearCache();
        Log::info("Plugin permanently deleted: {$plugin->name} ({$plugin->slug})");
        return true;
    }

    public function restore(string $slug): ?Plugin
    {
        $plugin = Plugin::withTrashed()->where('slug', $slug)->first();
        if (!$plugin) return null;
        $plugin->restore();
        Log::info("Plugin restored: {$plugin->name} ({$plugin->slug})");
        return $plugin;
    }

    public function getList(array $filters = [], int $perPage = 20)
    {
        $query = Plugin::query();

        if (!empty($filters['category'])) $query->where('category', $filters['category']);
        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (!empty($filters['type'])) $query->where('type', $filters['type']);
        if (isset($filters['is_free'])) $query->where('is_free', $filters['is_free']);
        if (isset($filters['is_active'])) $query->where('is_active', $filters['is_active']);
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%")
                  ->orWhere('category', 'like', "%{$filters['search']}%")
                  ->orWhere('author', 'like', "%{$filters['search']}%");
            });
        }

        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortField, $sortDir);

        return $query->paginate($perPage);
    }

    public function getDetails(string $slug): ?Plugin
    {
        $plugin = Plugin::where('slug', $slug)->first();
        if (!$plugin) return null;
        $plugin->incrementView();
        return $plugin;
    }

    public function getStats(): array
    {
        return Plugin::getStats();
    }

    public function install(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();
        if (!$plugin) {
            throw ValidationException::withMessages(['slug' => "Plugin '{$slug}' not found."]);
        }
        if ($plugin->isInstalled()) {
            throw ValidationException::withMessages(['slug' => "Plugin '{$slug}' is already installed."]);
        }
        return $plugin->install();
    }

    public function uninstall(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();
        if (!$plugin) {
            throw ValidationException::withMessages(['slug' => "Plugin '{$slug}' not found."]);
        }
        return $plugin->uninstall();
    }

    public function activate(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();
        if (!$plugin) {
            throw ValidationException::withMessages(['slug' => "Plugin '{$slug}' not found."]);
        }
        return $plugin->activate();
    }

    public function deactivate(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();
        if (!$plugin) {
            throw ValidationException::withMessages(['slug' => "Plugin '{$slug}' not found."]);
        }
        return $plugin->deactivate();
    }

    protected function validateCreate(array $data): array
    {
        $rules = [
            'name' => 'required|string|max:255|unique:plugins,name',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'type' => 'nullable|in:' . implode(',', $this->supportedTypes),
            'version' => 'nullable|string|max:20',
            'status' => 'nullable|in:' . implode(',', $this->allowedStatuses),
            'author' => 'nullable|string|max:255',
            'author_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'license' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'nullable|boolean',
            'config' => 'nullable|array',
            'settings' => 'nullable|array',
            'tags' => 'nullable|array',
            'dependencies' => 'nullable|array',
            'screenshots' => 'nullable|array',
            'preview_image' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'is_core' => 'nullable|boolean',
            'is_system' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
        ];
        return validator($data, $rules)->validate();
    }

    protected function validateUpdate(array $data): array
    {
        $rules = [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'type' => 'nullable|in:' . implode(',', $this->supportedTypes),
            'version' => 'nullable|string|max:20',
            'status' => 'nullable|in:' . implode(',', $this->allowedStatuses),
            'author' => 'nullable|string|max:255',
            'author_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'license' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'nullable|boolean',
            'config' => 'nullable|array',
            'settings' => 'nullable|array',
            'tags' => 'nullable|array',
            'dependencies' => 'nullable|array',
            'screenshots' => 'nullable|array',
            'preview_image' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'is_core' => 'nullable|boolean',
            'is_system' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
        ];
        return validator($data, $rules)->validate();
    }

    protected function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;
        while (Plugin::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }
        return $slug;
    }

    protected function createPluginFolders(Plugin $plugin): void
    {
        $paths = [
            $plugin->getFullPath(),
            $plugin->getFullPath() . '/views',
            $plugin->getFullPath() . '/assets',
            $plugin->getFullPath() . '/assets/css',
            $plugin->getFullPath() . '/assets/js',
            $plugin->getFullPath() . '/assets/images',
            $plugin->getFullPath() . '/config',
            $plugin->getFullPath() . '/src',
        ];

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    protected function createBaseFiles(Plugin $plugin): void
    {
        $path = $plugin->getFullPath();

        file_put_contents($path . '/config/config.php', "<?php\n\nreturn [\n    'name' => '{$plugin->name}',\n    'version' => '{$plugin->version}',\n    'author' => '{$plugin->author}',\n];\n");

        file_put_contents($path . '/README.md', "# {$plugin->name}\n\n**Version:** {$plugin->version}\n**Author:** {$plugin->author}\n**Price:** {$plugin->getPriceLabel()}\n\n## Description\n\n{$plugin->description}\n");

        file_put_contents($path . '/src/index.php', "<?php\n\n/**\n * {$plugin->name}\n * Version: {$plugin->version}\n * Author: {$plugin->author}\n */\n\n// Your plugin logic here\n");
    }

    protected function deletePluginFolders(Plugin $plugin): void
    {
        $path = $plugin->getFullPath();
        if (is_dir($path)) {
            $this->deleteDirectory($path);
        }
    }

    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
