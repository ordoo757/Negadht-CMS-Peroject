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

if (!function_exists('neuro_modules')) {
    function neuro_modules(): array
    {
        return app('module.registry')->getAllModules();
    }
}

if (!function_exists('neuro_components')) {
    function neuro_components(): array
    {
        return app('module.registry')->getAllComponents();
    }
}

if (!function_exists('neuro_plugins')) {
    function neuro_plugins(): array
    {
        return app('module.registry')->getAllPlugins();
    }
}

if (!function_exists('neuro_active_modules')) {
    function neuro_active_modules(): array
    {
        return app('module.registry')->getActiveModules();
    }
}

if (!function_exists('neuro_is_installed')) {
    function neuro_is_installed(string $slug): bool
    {
        return app('module.registry')->isInstalled($slug);
    }
}

if (!function_exists('neuro_kernel')) {
    function neuro_kernel(): \App\Core\Foundation\Kernel
    {
        return app('neuro.kernel');
    }
}

if (!function_exists('neuro_ai')) {
    function neuro_ai(): \App\Modules\AiKernel\Services\AiService
    {
        return app('ai.service');
    }
}

if (!function_exists('neuro_menu')) {
    function neuro_menu(string $slug): array
    {
        return app('menu.service')->getMenu($slug);
    }
}

if (!function_exists('neuro_template')) {
    function neuro_template(string $type = 'site'): ?object
    {
        return app('template.service')->getActiveTemplate($type);
    }
}

if (!function_exists('neuro_trans')) {
    function neuro_trans(string $key, array $replace = [], ?string $locale = null): string
    {
        return trans('messages.' . $key, $replace, $locale);
    }
}

if (!function_exists('neuro_format_bytes')) {
    function neuro_format_bytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
