<?php

namespace App\Modules\TemplateManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class TemplateService
{
    protected string $cachePrefix = 'template_';
    protected string $templatesPath;

    public function __construct()
    {
        $this->templatesPath = public_path('templates');
    }

    public function getActiveTemplate(string $type = 'site'): ?object
    {
        $cacheKey = "{$this->cachePrefix}active_{$type}";

        if (Cache::has($cacheKey)) {
            return (object) Cache::get($cacheKey);
        }

        $template = DB::table('templates')
            ->where('type', $type)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();

        if ($template) {
            Cache::put($cacheKey, (array) $template, now()->addHours(24));
        }

        return $template;
    }

    public function getTemplate(string $slug): ?object
    {
        return DB::table('templates')->where('slug', $slug)->first();
    }

    public function getAllTemplates(string $type = null): array
    {
        $query = DB::table('templates');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('name')->get()->toArray();
    }

    public function activateTemplate(string $slug): bool
    {
        $template = $this->getTemplate($slug);
        if (!$template) return false;

        // Deactivate other templates of same type
        DB::table('templates')
            ->where('type', $template->type)
            ->update(['is_default' => false, 'updated_at' => now()]);

        // Activate selected template
        DB::table('templates')
            ->where('slug', $slug)
            ->update(['is_active' => true, 'is_default' => true, 'updated_at' => now()]);

        $this->clearCache();

        return true;
    }

    public function deactivateTemplate(string $slug): bool
    {
        $template = $this->getTemplate($slug);
        if (!$template || $template->is_default) return false;

        DB::table('templates')
            ->where('slug', $slug)
            ->update(['is_active' => false, 'updated_at' => now()]);

        $this->clearCache();

        return true;
    }

    public function createTemplate(array $data): int
    {
        $slug = $data['slug'] ?? \Illuminate\Support\Str::slug($data['name']);
        $path = "templates/{$data['type']}/{$slug}";

        // Create template directory structure
        $fullPath = public_path($path);
        File::makeDirectory($fullPath . '/css', 0755, true);
        File::makeDirectory($fullPath . '/js', 0755, true);
        File::makeDirectory($fullPath . '/images', 0755, true);
        File::makeDirectory($fullPath . '/layouts', 0755, true);
        File::makeDirectory($fullPath . '/partials', 0755, true);

        // Create default files
        File::put($fullPath . '/index.php', $this->getDefaultTemplateHtml($data));
        File::put($fullPath . '/css/style.css', $this->getDefaultCss());
        File::put($fullPath . '/js/main.js', $this->getDefaultJs());

        $id = DB::table('templates')->insertGetId([
            'name' => $data['name'],
            'slug' => $slug,
            'type' => $data['type'],
            'description' => $data['description'] ?? '',
            'author' => $data['author'] ?? 'NeuroCMS',
            'version' => $data['version'] ?? '1.0.0',
            'is_active' => false,
            'is_default' => false,
            'path' => $path,
            'config' => json_encode($data['config'] ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    public function deleteTemplate(string $slug): bool
    {
        $template = $this->getTemplate($slug);
        if (!$template || $template->is_default) return false;

        // Delete files
        $path = public_path($template->path);
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }

        DB::table('templates')->where('slug', $slug)->delete();

        $this->clearCache();

        return true;
    }

    public function installFromZip(string $zipPath): array
    {
        $extractPath = storage_path('app/temp/templates/' . uniqid());

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return ['success' => false, 'error' => 'Cannot open zip file'];
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Validate template
        $manifestFile = $extractPath . '/template.json';
        if (!File::exists($manifestFile)) {
            File::deleteDirectory($extractPath);
            return ['success' => false, 'error' => 'Template manifest not found'];
        }

        $manifest = json_decode(File::get($manifestFile), true);

        if (!$manifest || !isset($manifest['name'], $manifest['slug'])) {
            File::deleteDirectory($extractPath);
            return ['success' => false, 'error' => 'Invalid template manifest'];
        }

        // Move to templates directory
        $targetPath = public_path("templates/{$manifest['type']}/{$manifest['slug']}");
        if (File::isDirectory($targetPath)) {
            File::deleteDirectory($targetPath);
        }

        File::moveDirectory($extractPath, $targetPath);

        // Register in database
        DB::table('templates')->updateOrInsert(
            ['slug' => $manifest['slug']],
            [
                'name' => $manifest['name'],
                'type' => $manifest['type'],
                'description' => $manifest['description'] ?? '',
                'author' => $manifest['author'] ?? 'Unknown',
                'version' => $manifest['version'] ?? '1.0.0',
                'path' => "templates/{$manifest['type']}/{$manifest['slug']}",
                'config' => json_encode($manifest['config'] ?? []),
                'is_active' => false,
                'updated_at' => now(),
            ]
        );

        File::deleteDirectory($extractPath);

        return ['success' => true, 'message' => 'Template installed successfully'];
    }

    public function exportTemplate(string $slug): ?string
    {
        $template = $this->getTemplate($slug);
        if (!$template) return null;

        $sourcePath = public_path($template->path);
        if (!File::isDirectory($sourcePath)) return null;

        $zipName = "{$slug}-{$template->version}.zip";
        $zipPath = storage_path("app/exports/templates/{$zipName}");

        if (!File::isDirectory(dirname($zipPath))) {
            File::makeDirectory(dirname($zipPath), 0755, true);
        }

        // Create manifest
        $manifest = [
            'name' => $template->name,
            'slug' => $template->slug,
            'type' => $template->type,
            'version' => $template->version,
            'author' => $template->author,
            'description' => $template->description,
            'config' => json_decode($template->config, true) ?? [],
        ];

        File::put($sourcePath . '/template.json', json_encode($manifest, JSON_PRETTY_PRINT));

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

    public function getTemplatePositions(string $slug): array
    {
        $template = $this->getTemplate($slug);
        if (!$template) return [];

        $config = json_decode($template->config, true) ?? [];

        $positions = [];
        foreach ($config as $key => $enabled) {
            if ($enabled && !in_array($key, ['rtl', 'responsive'])) {
                $positions[] = $key;
            }
        }

        return $positions;
    }

    public function renderPosition(string $position, array $params = []): string
    {
        $template = $this->getActiveTemplate();
        if (!$template) return '';

        // Get modules assigned to this position
        $modules = DB::table('module_positions')
            ->where('template_id', $template->id)
            ->where('position', $position)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $html = '';
        foreach ($modules as $module) {
            $html .= $this->renderModule($module, $params);
        }

        return $html;
    }

    protected function renderModule($module, array $params): string
    {
        // Render module based on type
        return "<!-- Module: {$module->name} -->";
    }

    protected function clearCache(): void
    {
        Cache::forget("{$this->cachePrefix}active_site");
        Cache::forget("{$this->cachePrefix}active_admin");
    }

    protected function getDefaultTemplateHtml(array $data): string
    {
        $rtl = ($data['config']['rtl'] ?? true) ? 'dir="rtl"' : '';
        $lang = $data['language'] ?? 'fa';

        return <<<HTML
<!DOCTYPE html>
<html lang="{$lang}" {$rtl}>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \$title ?? 'NeuroCMS' }}</title>
    <link rel="stylesheet" href="{{ asset('templates/{$data['type']}/{$data['slug']}/css/style.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <h1>{{ \$siteName ?? 'NeuroCMS' }}</h1>
            </div>
            <nav class="main-nav">
                {!! \$menu ?? '' !!}
            </nav>
        </div>
    </header>

    <div class="site-wrapper">
        @if(\$showSidebar ?? true)
        <aside class="sidebar">
            {!! \$sidebar ?? '' !!}
        </aside>
        @endif

        <main class="content">
            {!! \$content ?? '' !!}
        </main>
    </div>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ \$siteName ?? 'NeuroCMS' }}</p>
        </div>
    </footer>

    <script src="{{ asset('templates/{$data['type']}/{$data['slug']}/js/main.js') }}"></script>
</body>
</html>
HTML;
    }

    protected function getDefaultCss(): string
    {
        return <<<CSS
/* NeuroCMS Default Template */
:root {
    --primary-color: #6366f1;
    --secondary-color: #8b5cf6;
    --text-color: #1f2937;
    --bg-color: #f9fafb;
    --sidebar-width: 280px;
    --header-height: 70px;
    --footer-height: 60px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Vazirmatn', 'Segoe UI', Tahoma, sans-serif;
    background: var(--bg-color);
    color: var(--text-color);
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.site-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 1rem 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.site-header .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.main-nav ul {
    display: flex;
    list-style: none;
    gap: 2rem;
}

.main-nav a {
    color: white;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    transition: background 0.3s;
}

.main-nav a:hover {
    background: rgba(255,255,255,0.2);
}

.site-wrapper {
    display: flex;
    min-height: calc(100vh - var(--header-height) - var(--footer-height));
}

.sidebar {
    width: var(--sidebar-width);
    background: white;
    padding: 2rem;
    box-shadow: 2px 0 10px rgba(0,0,0,0.05);
}

.content {
    flex: 1;
    padding: 2rem;
}

.site-footer {
    background: var(--text-color);
    color: white;
    text-align: center;
    padding: 1rem 0;
    height: var(--footer-height);
}

@media (max-width: 768px) {
    .site-wrapper {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        order: 2;
    }

    .main-nav ul {
        flex-direction: column;
        gap: 0.5rem;
    }
}
CSS;
    }

    protected function getDefaultJs(): string
    {
        return <<<JS
// NeuroCMS Template JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
        });
    }

    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});
JS;
    }
}
