<?php

/**
 * NeuroCMS - Content Management System
 *
 * @author     Hooman Oliaei (هومان اولیائی)
 * @copyright  Copyright (c) 2026 Hooman Oliaei
 * @license    GNU General Public License v3.0
 * @link       https://github.com/ordoo757
 */
<?php

namespace App\Modules\ComponentMaker\Services;

use App\Modules\ComponentMaker\Models\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ComponentMakerService
{
    /**
     * لیست انواع کامپوننت‌های پشتیبانی شده
     */
    protected array $supportedTypes = [
        'custom',
        'layout',
        'widget',
        'module',
        'plugin',
        'theme',
        'template',
    ];

    /**
     * لیست وضعیت‌های مجاز
     */
    protected array $allowedStatuses = [
        'draft',
        'stable',
        'beta',
        'alpha',
        'deprecated',
        'archived',
    ];

    /**
     * ایجاد کامپوننت جدید
     */
    public function create(array $data): Component
    {
        // اعتبارسنجی داده‌ها
        $validated = $this->validateCreate($data);

        // ایجاد slug
        $slug = $this->generateUniqueSlug($validated['name']);

        // ساخت کامپوننت
        $component = Component::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? '',
            'category' => $validated['category'] ?? 'general',
            'type' => $validated['type'] ?? 'custom',
            'version' => $validated['version'] ?? '1.0.0',
            'status' => $validated['status'] ?? 'draft',
            'author' => $validated['author'] ?? auth()->user()->email ?? 'system',
            'author_email' => $validated['author_email'] ?? auth()->user()->email ?? null,
            'website' => $validated['website'] ?? null,
            'license' => $validated['license'] ?? 'MIT',
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

        // ساخت پوشه‌های مورد نیاز
        $this->createComponentFolders($component);

        // ایجاد فایل‌های پایه
        $this->createBaseFiles($component);

        // لاگ
        Log::info("Component created: {$component->name} ({$component->slug})");

        return $component;
    }

    /**
     * بروزرسانی کامپوننت
     */
    public function update(Component $component, array $data): Component
    {
        $validated = $this->validateUpdate($data);

        $component->update([
            'name' => $validated['name'] ?? $component->name,
            'description' => $validated['description'] ?? $component->description,
            'category' => $validated['category'] ?? $component->category,
            'type' => $validated['type'] ?? $component->type,
            'version' => $validated['version'] ?? $component->version,
            'status' => $validated['status'] ?? $component->status,
            'author' => $validated['author'] ?? $component->author,
            'author_email' => $validated['author_email'] ?? $component->author_email,
            'website' => $validated['website'] ?? $component->website,
            'license' => $validated['license'] ?? $component->license,
            'config' => $validated['config'] ?? $component->config,
            'settings' => $validated['settings'] ?? $component->settings,
            'tags' => $validated['tags'] ?? $component->tags,
            'dependencies' => $validated['dependencies'] ?? $component->dependencies,
            'screenshots' => $validated['screenshots'] ?? $component->screenshots,
            'preview_image' => $validated['preview_image'] ?? $component->preview_image,
            'view_path' => $validated['view_path'] ?? $component->view_path,
            'style_path' => $validated['style_path'] ?? $component->style_path,
            'script_path' => $validated['script_path'] ?? $component->script_path,
            'is_active' => $validated['is_active'] ?? $component->is_active,
            'is_core' => $validated['is_core'] ?? $component->is_core,
            'is_system' => $validated['is_system'] ?? $component->is_system,
            'is_public' => $validated['is_public'] ?? $component->is_public,
        ]);

        // پاک کردن کش
        $component->clearCache();

        Log::info("Component updated: {$component->name} ({$component->slug})");

        return $component;
    }

    /**
     * حذف کامپوننت (با قابلیت بازیابی)
     */
    public function delete(Component $component): bool
    {
        $component->delete();

        // پاک کردن کش
        $component->clearCache();

        Log::info("Component deleted: {$component->name} ({$component->slug})");

        return true;
    }

    /**
     * حذف دائمی کامپوننت
     */
    public function forceDelete(Component $component): bool
    {
        // حذف پوشه‌های فیزیکی
        $this->deleteComponentFolders($component);

        // حذف از دیتابیس
        $component->forceDelete();

        // پاک کردن کش
        $component->clearCache();

        Log::info("Component permanently deleted: {$component->name} ({$component->slug})");

        return true;
    }

    /**
     * بازیابی کامپوننت حذف شده
     */
    public function restore(string $slug): ?Component
    {
        $component = Component::withTrashed()->where('slug', $slug)->first();

        if (!$component) {
            return null;
        }

        $component->restore();

        Log::info("Component restored: {$component->name} ({$component->slug})");

        return $component;
    }

    /**
     * دریافت لیست کامپوننت‌ها با فیلتر
     */
    public function getList(array $filters = [], int $perPage = 20)
    {
        $query = Component::query();

        // فیلتر بر اساس دسته‌بندی
        if (!empty($filters['category'])) {
            $query->category($filters['category']);
        }

        // فیلتر بر اساس وضعیت
        if (!empty($filters['status'])) {
            $query->status($filters['status']);
        }

        // فیلتر بر اساس نوع
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // فیلتر بر اساس فعال/غیرفعال
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // جستجو
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // مرتب‌سازی
        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortField, $sortDir);

        return $query->paginate($perPage);
    }

    /**
     * دریافت جزئیات کامل کامپوننت (با روابط)
     */
    public function getDetails(string $slug): ?Component
    {
        $component = Component::getFromCache($slug);

        if (!$component) {
            return null;
        }

        // بارگذاری روابط
        $component->load(['creator', 'dependencies']);

        // افزایش بازدید
        $component->incrementView();

        return $component;
    }

    /**
     * دریافت کامپوننت‌های مشابه
     */
    public function getSimilar(Component $component, int $limit = 5): array
    {
        return $component->getSimilar($limit);
    }

    /**
     * دریافت کامپوننت‌های محبوب
     */
    public function getPopular(int $limit = 10): array
    {
        return Component::getPopular($limit);
    }

    /**
     * دریافت آمار کامپوننت‌ها
     */
    public function getStats(): array
    {
        return Component::getStats();
    }

    /**
     * دریافت دسته‌بندی‌های موجود
     */
    public function getCategories(): array
    {
        return Component::getCategories();
    }

    /**
     * نصب کامپوننت
     */
    public function install(string $slug): bool
    {
        $component = Component::where('slug', $slug)->first();

        if (!$component) {
            throw ValidationException::withMessages([
                'slug' => "Component '{$slug}' not found.",
            ]);
        }

        if ($component->isInstalled()) {
            throw ValidationException::withMessages([
                'slug' => "Component '{$slug}' is already installed.",
            ]);
        }

        return $component->install();
    }

    /**
     * حذف نصب کامپوننت
     */
    public function uninstall(string $slug): bool
    {
        $component = Component::where('slug', $slug)->first();

        if (!$component) {
            throw ValidationException::withMessages([
                'slug' => "Component '{$slug}' not found.",
            ]);
        }

        return $component->uninstall();
    }

    /**
     * صادر کردن کامپوننت به فایل ZIP
     */
    public function export(string $slug): string
    {
        $component = Component::where('slug', $slug)->first();

        if (!$component) {
            throw ValidationException::withMessages([
                'slug' => "Component '{$slug}' not found.",
            ]);
        }

        return $component->export();
    }

    /**
     * وارد کردن کامپوننت از فایل ZIP
     */
    public function import(string $zipPath): Component
    {
        if (!file_exists($zipPath)) {
            throw ValidationException::withMessages([
                'file' => 'Zip file not found.',
            ]);
        }

        return Component::import($zipPath);
    }

    // =========================================================
    // متدهای کمکی
    // =========================================================

    /**
     * اعتبارسنجی داده‌های ایجاد
     */
    protected function validateCreate(array $data): array
    {
        $rules = [
            'name' => 'required|string|max:255|unique:components,name',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'type' => 'nullable|in:' . implode(',', $this->supportedTypes),
            'version' => 'nullable|string|max:20',
            'status' => 'nullable|in:' . implode(',', $this->allowedStatuses),
            'author' => 'nullable|string|max:255',
            'author_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'license' => 'nullable|string|max:100',
            'config' => 'nullable|array',
            'settings' => 'nullable|array',
            'tags' => 'nullable|array',
            'dependencies' => 'nullable|array',
            'screenshots' => 'nullable|array',
            'preview_image' => 'nullable|string|max:255',
            'view_path' => 'nullable|string|max:255',
            'style_path' => 'nullable|string|max:255',
            'script_path' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'is_core' => 'nullable|boolean',
            'is_system' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
        ];

        return validator($data, $rules)->validate();
    }

    /**
     * اعتبارسنجی داده‌های بروزرسانی
     */
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
            'config' => 'nullable|array',
            'settings' => 'nullable|array',
            'tags' => 'nullable|array',
            'dependencies' => 'nullable|array',
            'screenshots' => 'nullable|array',
            'preview_image' => 'nullable|string|max:255',
            'view_path' => 'nullable|string|max:255',
            'style_path' => 'nullable|string|max:255',
            'script_path' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'is_core' => 'nullable|boolean',
            'is_system' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
        ];

        return validator($data, $rules)->validate();
    }

    /**
     * ایجاد slug یکتا
     */
    protected function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Component::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }

    /**
     * ایجاد پوشه‌های فیزیکی کامپوننت
     */
    protected function createComponentFolders(Component $component): void
    {
        $paths = [
            $component->getFullPath(),
            $component->getFullPath() . '/views',
            $component->getFullPath() . '/assets',
            $component->getFullPath() . '/assets/css',
            $component->getFullPath() . '/assets/js',
            $component->getFullPath() . '/assets/images',
        ];

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /**
     * ایجاد فایل‌های پایه کامپوننت
     */
    protected function createBaseFiles(Component $component): void
    {
        $path = $component->getFullPath();

        // فایل تنظیمات
        $configContent = "<?php\n\nreturn [\n    'name' => '{$component->name}',\n    'version' => '{$component->version}',\n    'author' => '{$component->author}',\n    'description' => '{$component->description}',\n];\n";
        file_put_contents($path . '/config.php', $configContent);

        // فایل README
        $readmeContent = "# {$component->name}\n\n" .
                         "**Version:** {$component->version}\n" .
                         "**Author:** {$component->author}\n" .
                         "**Category:** {$component->category}\n\n" .
                         "## Description\n\n{$component->description}\n\n" .
                         "## Installation\n\n" .
                         "```bash\ncomposer require component/{$component->slug}\n```\n";
        file_put_contents($path . '/README.md', $readmeContent);

        // فایل index.php
        $indexContent = "<?php\n\n/**\n * {$component->name}\n * Version: {$component->version}\n * Author: {$component->author}\n */\n\n// Your component logic here\n";
        file_put_contents($path . '/index.php', $indexContent);
    }

    /**
     * حذف پوشه‌های فیزیکی کامپوننت
     */
    protected function deleteComponentFolders(Component $component): void
    {
        $path = $component->getFullPath();

        if (is_dir($path)) {
            $this->deleteDirectory($path);
        }
    }

    /**
     * حذف یک پوشه به صورت بازگشتی
     */
    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

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
