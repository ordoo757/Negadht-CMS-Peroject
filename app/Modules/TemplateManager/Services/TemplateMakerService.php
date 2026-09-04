<?php

namespace App\Modules\TemplateManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use ZipArchive;

class TemplateMakerService
{
    protected string $cachePrefix = 'template_maker_';
    protected string $templatesPath;

    public function __construct()
    {
        $this->templatesPath = public_path('templates');
    }

    /**
     * ایجاد قالب جدید با ساختار کامل
     */
    public function createTemplate(array $data): array
    {
        $slug = $data['slug'] ?? Str::slug($data['name']);
        $type = $data['type'] ?? 'site';
        $path = "templates/{$type}/{$slug}";

        $fullPath = public_path($path);
        
        // ایجاد ساختار دایرکتوری
        $directories = [
            'css', 'js', 'images', 'fonts',
            'layouts', 'partials', 'components',
            'modules', 'widgets', 'assets'
        ];

        foreach ($directories as $dir) {
            File::makeDirectory("{$fullPath}/{$dir}", 0755, true, true);
        }

        // ایجاد فایل‌های پایه
        $this->generateLayoutFiles($fullPath, $data);
        $this->generateComponentFiles($fullPath, $data);
        $this->generateManifest($fullPath, $data, $slug, $type, $path);

        // ذخیره در دیتابیس
        $templateId = DB::table('templates')->insertGetId([
            'name' => $data['name'],
            'slug' => $slug,
            'type' => $type,
            'description' => $data['description'] ?? '',
            'author' => $data['author'] ?? auth()->user()->name ?? 'NeuroCMS',
            'version' => $data['version'] ?? '1.0.0',
            'is_active' => false,
            'is_default' => false,
            'path' => $path,
            'config' => json_encode($this->buildConfig($data)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ایجاد layout پیش‌فرض
        DB::table('template_layouts')->insert([
            'template_id' => $templateId,
            'name' => 'چیدمان پیش‌فرض',
            'slug' => 'default',
            'type' => $type,
            'structure' => json_encode($this->getDefaultStructure($type, $data)),
            'positions' => json_encode($this->getDefaultPositions($type)),
            'settings' => json_encode([
                'rtl' => $data['rtl'] ?? true,
                'responsive' => true,
                'dark_mode' => $data['dark_mode'] ?? false,
            ]),
            'is_active' => true,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->clearCache();

        return [
            'success' => true,
            'template_id' => $templateId,
            'slug' => $slug,
            'path' => $path,
        ];
    }

    /**
     * ساختار پیش‌فرض بر اساس نوع
     */
    protected function getDefaultStructure(string $type, array $data): array
    {
        $rtl = ($data['rtl'] ?? true) ? 'rtl' : 'ltr';
        
        if ($type === 'admin') {
            return [
                'doctype' => 'html5',
                'lang' => $data['language'] ?? 'fa',
                'dir' => $rtl,
                'viewport' => 'width=device-width, initial-scale=1.0',
                'body' => [
                    ['type' => 'header', 'id' => 'main-header', 'class' => 'admin-header'],
                    ['type' => 'wrapper', 'class' => 'admin-wrapper', 'children' => [
                        ['type' => 'sidebar', 'id' => 'main-sidebar', 'class' => 'admin-sidebar'],
                        ['type' => 'main', 'id' => 'main-content', 'class' => 'admin-content', 'children' => [
                            ['type' => 'breadcrumb', 'id' => 'breadcrumb'],
                            ['type' => 'content', 'id' => 'page-content'],
                        ]],
                    ]],
                    ['type' => 'footer', 'id' => 'main-footer', 'class' => 'admin-footer'],
                ],
            ];
        }

        if ($type === 'dashboard') {
            return [
                'doctype' => 'html5',
                'lang' => $data['language'] ?? 'fa',
                'dir' => $rtl,
                'body' => [
                    ['type' => 'header', 'id' => 'dash-header'],
                    ['type' => 'wrapper', 'children' => [
                        ['type' => 'sidebar', 'id' => 'dash-sidebar'],
                        ['type' => 'main', 'children' => [
                            ['type' => 'stats-row', 'id' => 'stats-widgets'],
                            ['type' => 'content-grid', 'children' => [
                                ['type' => 'widget-area', 'id' => 'widgets-left'],
                                ['type' => 'content', 'id' => 'main-content'],
                                ['type' => 'widget-area', 'id' => 'widgets-right'],
                            ]],
                        ]],
                    ]],
                    ['type' => 'footer', 'id' => 'dash-footer'],
                ],
            ];
        }

        return [
            'doctype' => 'html5',
            'lang' => $data['language'] ?? 'fa',
            'dir' => $rtl,
            'body' => [
                ['type' => 'header', 'id' => 'site-header', 'children' => [
                    ['type' => 'top-bar', 'id' => 'top-bar'],
                    ['type' => 'nav', 'id' => 'main-nav'],
                ]],
                ['type' => 'wrapper', 'children' => [
                    ['type' => 'sidebar', 'id' => 'sidebar-left', 'position' => 'left'],
                    ['type' => 'main', 'id' => 'main-content', 'children' => [
                        ['type' => 'slider', 'id' => 'hero-slider'],
                        ['type' => 'content', 'id' => 'page-content'],
                        ['type' => 'widgets', 'id' => 'bottom-widgets'],
                    ]],
                    ['type' => 'sidebar', 'id' => 'sidebar-right', 'position' => 'right'],
                ]],
                ['type' => 'footer', 'id' => 'site-footer', 'children' => [
                    ['type' => 'widgets', 'id' => 'footer-widgets'],
                    ['type' => 'copyright', 'id' => 'copyright-bar'],
                ]],
            ],
        ];
    }

    protected function getDefaultPositions(string $type): array
    {
        $common = ['header', 'footer', 'content-top', 'content-bottom'];
        
        $typePositions = [
            'admin' => ['sidebar', 'breadcrumb', 'toolbar', 'modal', 'notification'],
            'site' => ['sidebar-left', 'sidebar-right', 'top-bar', 'bottom-bar', 'hero'],
            'dashboard' => ['stats-row', 'widget-area', 'quick-actions', 'notifications'],
            'login' => ['login-form-top', 'login-form-bottom', 'social-login'],
        ];

        return array_merge($common, $typePositions[$type] ?? []);
    }

    protected function buildConfig(array $data): array
    {
        return [
            'rtl' => $data['rtl'] ?? true,
            'responsive' => true,
            'dark_mode' => $data['dark_mode'] ?? false,
            'header' => $data['has_header'] ?? true,
            'footer' => $data['has_footer'] ?? true,
            'sidebar' => $data['has_sidebar'] ?? true,
            'sticky_header' => $data['sticky_header'] ?? false,
            'sticky_footer' => $data['sticky_footer'] ?? false,
            'menu_type' => $data['menu_type'] ?? 'horizontal',
            'grid_system' => $data['grid_system'] ?? 'flexbox',
            'framework' => $data['framework'] ?? 'custom',
            'breakpoints' => [
                'mobile' => '576px',
                'tablet' => '768px',
                'desktop' => '1024px',
                'wide' => '1400px',
            ],
        ];
    }

    protected function generateLayoutFiles(string $path, array $data): void
    {
        $type = $data['type'] ?? 'site';
        $rtl = ($data['rtl'] ?? true) ? 'dir="rtl"' : '';
        $lang = $data['language'] ?? 'fa';

        // فایل اصلی index.php
        $indexContent = $this->renderLayoutTemplate($type, $lang, $rtl, $data);
        File::put("{$path}/index.php", $indexContent);

        // فایل‌های partial
        File::put("{$path}/partials/header.blade.php", $this->getHeaderPartial($data));
        File::put("{$path}/partials/footer.blade.php", $this->getFooterPartial($data));
        File::put("{$path}/partials/sidebar.blade.php", $this->getSidebarPartial($data));
        File::put("{$path}/partials/menu.blade.php", $this->getMenuPartial($data));
        
        // فایل layout master
        File::put("{$path}/layouts/master.blade.php", $this->getMasterLayout($type, $lang, $rtl));
    }

    protected function renderLayoutTemplate(string $type, string $lang, string $rtl, array $data): string
    {
        $menuType = $data['menu_type'] ?? 'horizontal';
        
        return <<<PHP
<?php
/** Template: {$data['name']} */
\$layout = app('template.maker')->getActiveLayout('{$type}');
\$components = app('template.maker')->getActiveComponents(\$layout->template_id ?? 0);
?>
<!DOCTYPE html>
<html lang="{$lang}" {$rtl}>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \$title ?? 'NeuroCMS' }}</title>
    <meta name="description" content="{{ \$description ?? '' }}">
    <link rel="stylesheet" href="{{ asset('templates/{$type}/{{ \$templateSlug }}/css/style.css') }}">
    @stack('styles')
</head>
<body class="template-{$type} {{ \$bodyClass ?? '' }}" data-theme="{{ \$theme ?? 'light' }}">
    
    @if(\$showHeader ?? true)
        @include('templates.{$type}.' . (\$templateSlug ?? 'default') . '.partials.header')
    @endif

    <div class="site-wrapper layout-{$menuType}">
        @if(\$showSidebar ?? true)
            @include('templates.{$type}.' . (\$templateSlug ?? 'default') . '.partials.sidebar')
        @endif

        <main class="main-content" role="main">
            @yield('content')
        </main>
    </div>

    @if(\$showFooter ?? true)
        @include('templates.{$type}.' . (\$templateSlug ?? 'default') . '.partials.footer')
    @endif

    <script src="{{ asset('templates/{$type}/{{ \$templateSlug }}/js/main.js') }}"></script>
    @stack('scripts')
</body>
</html>
PHP;
    }

    protected function getHeaderPartial(array $data): string
    {
        $sticky = ($data['sticky_header'] ?? false) ? 'sticky' : '';
        return <<<BLADE
<header class="site-header {{ \$headerClass ?? '' }} {$sticky}">
    <div class="container">
        <div class="header-inner">
            <div class="logo">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('templates/site/' . (\$templateSlug ?? 'default') . '/images/logo.png') }}" alt="{{ \$siteName }}">
                </a>
            </div>
            
            <button class="menu-toggle d-lg-none" aria-label="منو">
                <span></span><span></span><span></span>
            </button>

            <nav class="main-navigation" role="navigation">
                {!! app('menu.service')->render('main-menu', ['template' => \$templateSlug ?? null]) !!}
            </nav>

            <div class="header-actions">
                @if(auth()->check())
                    <div class="user-menu dropdown">
                        <button class="dropdown-toggle">
                            <img src="{{ auth()->user()->avatar ?? asset('assets/images/default-avatar.png') }}" class="avatar" alt="">
                            <span>{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="{{ route('user.dashboard') }}">داشبورد</a></li>
                            <li><a href="{{ route('user.profile') }}">پروفایل</a></li>
                            <li><hr></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit">خروج</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline">ورود</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">ثبت‌نام</a>
                @endif
            </div>
        </div>
    </div>
</header>
BLADE;
    }

    protected function getFooterPartial(array $data): string
    {
        return <<<BLADE
<footer class="site-footer {{ \$footerClass ?? '' }}">
    <div class="container">
        <div class="footer-widgets">
            @foreach(app('template.maker')->getWidgets('footer', \$templateId ?? null) as \$widget)
                <div class="footer-widget {{ \$widget['class'] ?? '' }}">
                    {!! \$widget['content'] !!}
                </div>
            @endforeach
        </div>
        
        <div class="footer-bottom">
            <p class="copyright">
                &copy; {{ date('Y') }} {{ \$siteName ?? 'NeuroCMS' }}. تمامی حقوق محفوظ است.
            </p>
            <div class="footer-links">
                <a href="{{ route('page.privacy') }}">حریم خصوصی</a>
                <a href="{{ route('page.terms') }}">قوانین</a>
                <a href="{{ route('page.contact') }}">تماس</a>
            </div>
        </div>
    </div>
</footer>
BLADE;
    }

    protected function getSidebarPartial(array $data): string
    {
        $position = $data['sidebar_position'] ?? 'right';
        return <<<BLADE
<aside class="sidebar sidebar-{$position} {{ \$sidebarClass ?? '' }}">
    <div class="sidebar-inner">
        @foreach(app('template.maker')->getWidgets('sidebar', \$templateId ?? null) as \$widget)
            <div class="widget {{ \$widget['class'] ?? '' }}">
                @if(\$widget['title'])
                    <h4 class="widget-title">{{ \$widget['title'] }}</h4>
                @endif
                <div class="widget-content">
                    {!! \$widget['content'] !!}
                </div>
            </div>
        @endforeach
        
        {{ \$sidebarContent ?? '' }}
    </div>
</aside>
BLADE;
    }

    protected function getMenuPartial(array $data): string
    {
        $menuType = $data['menu_type'] ?? 'horizontal';
        return <<<BLADE
@if(\$menuType === 'mega')
    <div class="mega-menu {{ \$menuClass ?? '' }}">
        @foreach(\$menuItems as \$item)
            <div class="mega-menu-item">
                <a href="{{ \$item['url'] }}" class="mega-menu-link">{{ \$item['title'] }}</a>
                @if(!empty(\$item['children']))
                    <div class="mega-menu-dropdown">
                        @foreach(\$item['children'] as \$child)
                            <div class="mega-menu-column">
                                <h5>{{ \$child['title'] }}</h5>
                                @if(!empty(\$child['children']))
                                    <ul>
                                        @foreach(\$child['children'] as \$sub)
                                            <li><a href="{{ \$sub['url'] }}">{{ \$sub['title'] }}</a></li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@elseif(\$menuType === 'vertical')
    <ul class="vertical-menu {{ \$menuClass ?? '' }}">
        @foreach(\$menuItems as \$item)
            <li class="{{ !empty(\$item['children']) ? 'has-children' : '' }} {{ \$item['active'] ? 'active' : '' }}">
                <a href="{{ \$item['url'] }}">
                    @if(\$item['icon'])<i class="{{ \$item['icon'] }}"></i>@endif
                    <span>{{ \$item['title'] }}</span>
                </a>
                @if(!empty(\$item['children']))
                    <ul class="sub-menu">
                        @foreach(\$item['children'] as \$child)
                            <li><a href="{{ \$child['url'] }}">{{ \$child['title'] }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>
@else
    <ul class="horizontal-menu {{ \$menuClass ?? '' }}">
        @foreach(\$menuItems as \$item)
            <li class="{{ !empty(\$item['children']) ? 'has-dropdown' : '' }} {{ \$item['active'] ? 'active' : '' }}">
                <a href="{{ \$item['url'] }}">
                    @if(\$item['icon'])<i class="{{ \$item['icon'] }}"></i>@endif
                    {{ \$item['title'] }}
                </a>
                @if(!empty(\$item['children']))
                    <ul class="dropdown-menu">
                        @foreach(\$item['children'] as \$child)
                            <li class="{{ !empty(\$child['children']) ? 'has-submenu' : '' }}">
                                <a href="{{ \$child['url'] }}">{{ \$child['title'] }}</a>
                                @if(!empty(\$child['children']))
                                    <ul class="submenu">
                                        @foreach(\$child['children'] as \$sub)
                                            <li><a href="{{ \$sub['url'] }}">{{ \$sub['title'] }}</a></li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>
@endif
BLADE;
    }

    protected function getMasterLayout(string $type, string $lang, string $rtl): string
    {
        return <<<BLADE
<!DOCTYPE html>
<html lang="{$lang}" {$rtl}>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>@yield('title', 'NeuroCMS')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    @stack('head')
    <link rel="stylesheet" href="{{ asset('templates/{$type}/' . (\$templateSlug ?? 'default') . '/css/style.css') }}">
    @stack('styles')
</head>
<body class="template-{{ \$templateSlug ?? 'default' }} layout-{{ \$layoutSlug ?? 'default' }} {{ \$bodyClass ?? '' }}" data-dir="{{ app()->getLocale() === 'fa' || app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    
    @section('body')
        @includeWhen(\$showHeader ?? true, 'templates.{$type}.' . (\$templateSlug ?? 'default') . '.partials.header')
        
        <div id="app-wrapper" class="wrapper {{ \$wrapperClass ?? '' }}">
            @yield('before_content')
            
            <main id="main-content" class="main {{ \$mainClass ?? '' }}" role="main">
                @yield('content')
            </main>
            
            @yield('after_content')
        </div>
        
        @includeWhen(\$showFooter ?? true, 'templates.{$type}.' . (\$templateSlug ?? 'default') . '.partials.footer')
    @show

    <div id="modal-container"></div>
    <div id="notification-container"></div>
    
    <script src="{{ asset('templates/{$type}/' . (\$templateSlug ?? 'default') . '/js/main.js') }}"></script>
    @stack('scripts')
</body>
</html>
BLADE;
    }

    protected function generateComponentFiles(string $path, array $data): void
    {
        // CSS اصلی با متغیرها و گرید
        File::put("{$path}/css/style.css", $this->generateAdvancedCss($data));
        
        // JS اصلی با قابلیت‌های تعاملی
        File::put("{$path}/js/main.js", $this->generateAdvancedJs($data));
        
        // فایل کامپوننت‌ها
        File::put("{$path}/components/hero.blade.php", $this->getHeroComponent());
        File::put("{$path}/components/cards.blade.php", $this->getCardsComponent());
        File::put("{$path}/components/stats.blade.php", $this->getStatsComponent());
    }

    protected function generateAdvancedCss(array $data): string
    {
        $primary = $data['primary_color'] ?? '#6366f1';
        $secondary = $data['secondary_color'] ?? '#8b5cf6';
        $rtl = $data['rtl'] ?? true;
        $dir = $rtl ? 'rtl' : 'ltr';

        return <<<CSS
/* ============================================
   NeuroCMS Advanced Template System
   Direction: {$dir}
   ============================================ */

:root {
    /* Colors */
    --primary: {$primary};
    --primary-light: color-mix(in srgb, {$primary} 80%, white);
    --primary-dark: color-mix(in srgb, {$primary} 80%, black);
    --secondary: {$secondary};
    --accent: #f59e0b;
    
    /* Neutral */
    --white: #ffffff;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    
    /* Semantic */
    --text: var(--gray-800);
    --text-muted: var(--gray-500);
    --bg: var(--gray-50);
    --border: var(--gray-200);
    
    /* Layout */
    --header-height: 70px;
    --header-height-mobile: 60px;
    --footer-height: auto;
    --sidebar-width: 280px;
    --sidebar-width-collapsed: 70px;
    --content-max-width: 1400px;
    --container-padding: clamp(1rem, 3vw, 2rem);
    
    /* Spacing Scale */
    --space-xs: 0.25rem;
    --space-sm: 0.5rem;
    --space-md: 1rem;
    --space-lg: 1.5rem;
    --space-xl: 2rem;
    --space-2xl: 3rem;
    --space-3xl: 4rem;
    
    /* Typography */
    --font-sans: 'Vazirmatn', 'Inter', system-ui, -apple-system, sans-serif;
    --font-mono: 'JetBrains Mono', monospace;
    --text-xs: clamp(0.75rem, 0.7rem + 0.25vw, 0.875rem);
    --text-sm: clamp(0.875rem, 0.8rem + 0.35vw, 1rem);
    --text-base: clamp(1rem, 0.9rem + 0.5vw, 1.125rem);
    --text-lg: clamp(1.125rem, 1rem + 0.65vw, 1.25rem);
    --text-xl: clamp(1.25rem, 1.1rem + 0.75vw, 1.5rem);
    --text-2xl: clamp(1.5rem, 1.3rem + 1vw, 2rem);
    --text-3xl: clamp(1.875rem, 1.6rem + 1.4vw, 2.5rem);
    
    /* Effects */
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    
    --radius-sm: 0.375rem;
    --radius: 0.5rem;
    --radius-md: 0.75rem;
    --radius-lg: 1rem;
    --radius-xl: 1.5rem;
    --radius-full: 9999px;
    
    /* Transitions */
    --transition-fast: 150ms ease;
    --transition: 250ms ease;
    --transition-slow: 350ms ease;
    
    /* Z-index */
    --z-dropdown: 100;
    --z-sticky: 200;
    --z-fixed: 300;
    --z-modal-backdrop: 400;
    --z-modal: 500;
    --z-popover: 600;
    --z-tooltip: 700;
}

/* Dark Mode */
[data-theme="dark"] {
    --text: var(--gray-100);
    --text-muted: var(--gray-400);
    --bg: var(--gray-900);
    --border: var(--gray-700);
}

/* Reset & Base */
*, *::before, *::after {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
    -webkit-text-size-adjust: 100%;
}

body {
    font-family: var(--font-sans);
    font-size: var(--text-base);
    line-height: 1.6;
    color: var(--text);
    background: var(--bg);
    min-height: 100vh;
    overflow-x: hidden;
}

/* Typography */
h1, h2, h3, h4, h5, h6 {
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: var(--space-md);
}

h1 { font-size: var(--text-3xl); }
h2 { font-size: var(--text-2xl); }
h3 { font-size: var(--text-xl); }
h4 { font-size: var(--text-lg); }

a {
    color: var(--primary);
    text-decoration: none;
    transition: color var(--transition-fast);
}

a:hover { color: var(--primary-dark); }

img, video, svg {
    max-width: 100%;
    height: auto;
    display: block;
}

/* Layout System */
.container {
    width: 100%;
    max-width: var(--content-max-width);
    margin-inline: auto;
    padding-inline: var(--container-padding);
}

.site-wrapper {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.main-content {
    flex: 1;
    width: 100%;
}

/* Grid System */
.grid {
    display: grid;
    gap: var(--space-lg);
}

.grid-cols-1 { grid-template-columns: repeat(1, 1fr); }
.grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
.grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
.grid-cols-4 { grid-template-columns: repeat(4, 1fr); }

@media (min-width: 768px) {
    .md\\:grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
    .md\\:grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
    .md\\:grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
}

@media (min-width: 1024px) {
    .lg\\:grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
    .lg\\:grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
    .lg\\:grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
}

/* Flex utilities */
.flex { display: flex; }
.flex-col { flex-direction: column; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.gap-sm { gap: var(--space-sm); }
.gap-md { gap: var(--space-md); }
.gap-lg { gap: var(--space-lg); }

/* Header */
.site-header {
    position: relative;
    z-index: var(--z-sticky);
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    height: var(--header-height);
}

.site-header.sticky {
    position: sticky;
    top: 0;
}

.header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: var(--header-height);
    gap: var(--space-lg);
}

.logo img {
    height: 40px;
    width: auto;
}

/* Navigation */
.main-navigation {
    flex: 1;
}

.horizontal-menu {
    display: flex;
    list-style: none;
    gap: var(--space-xs);
}

.horizontal-menu a {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    padding: var(--space-sm) var(--space-md);
    color: white;
    border-radius: var(--radius);
    transition: background var(--transition-fast);
}

.horizontal-menu a:hover,
.horizontal-menu .active a {
    background: rgba(255,255,255,0.15);
}

.has-dropdown {
    position: relative;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    {$dir}: 0;
    background: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-lg);
    min-width: 200px;
    padding: var(--space-sm);
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all var(--transition);
}

.has-dropdown:hover > .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-menu a {
    color: var(--text);
    padding: var(--space-sm) var(--space-md);
    border-radius: var(--radius);
}

.dropdown-menu a:hover {
    background: var(--gray-50);
    color: var(--primary);
}

/* Mobile Menu */
.menu-toggle {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: var(--space-sm);
}

.menu-toggle span {
    display: block;
    width: 24px;
    height: 2px;
    background: white;
    transition: all var(--transition);
}

/* Sidebar */
.sidebar {
    width: var(--sidebar-width);
    background: white;
    border-{$dir}: 1px solid var(--border);
    padding: var(--space-lg);
}

.sidebar-inner {
    position: sticky;
    top: calc(var(--header-height) + var(--space-lg));
}

.widget {
    margin-bottom: var(--space-xl);
    padding: var(--space-lg);
    background: var(--gray-50);
    border-radius: var(--radius-lg);
}

.widget-title {
    font-size: var(--text-lg);
    margin-bottom: var(--space-md);
    padding-bottom: var(--space-sm);
    border-bottom: 2px solid var(--primary);
}

/* Footer */
.site-footer {
    background: var(--gray-800);
    color: var(--gray-300);
    padding: var(--space-3xl) 0 var(--space-xl);
}

.footer-widgets {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--space-xl);
    margin-bottom: var(--space-2xl);
}

.footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: var(--space-xl);
    border-top: 1px solid var(--gray-700);
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-sm);
    padding: var(--space-sm) var(--space-lg);
    font-size: var(--text-sm);
    font-weight: 600;
    border-radius: var(--radius);
    border: 1px solid transparent;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    color: white;
}

.btn-outline {
    background: transparent;
    color: white;
    border-color: rgba(255,255,255,0.3);
}

.btn-outline:hover {
    background: rgba(255,255,255,0.1);
}

/* Cards */
.card {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: box-shadow var(--transition), transform var(--transition);
}

.card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.card-body {
    padding: var(--space-lg);
}

/* Stats Component */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-lg);
}

.stat-card {
    background: white;
    padding: var(--space-xl);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    text-align: center;
}

.stat-value {
    font-size: var(--text-3xl);
    font-weight: 800;
    color: var(--primary);
}

.stat-label {
    color: var(--text-muted);
    margin-top: var(--space-sm);
}

/* Hero Section */
.hero {
    position: relative;
    padding: var(--space-3xl) 0;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    overflow: hidden;
}

.hero-content {
    position: relative;
    z-index: 1;
    max-width: 600px;
}

.hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    margin-bottom: var(--space-lg);
}

.hero-description {
    font-size: var(--text-lg);
    opacity: 0.9;
    margin-bottom: var(--space-xl);
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.6s ease forwards;
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: var(--gray-100);
}

::-webkit-scrollbar-thumb {
    background: var(--gray-300);
    border-radius: var(--radius-full);
}

::-webkit-scrollbar-thumb:hover {
    background: var(--gray-400);
}

/* ============================================
   RESPONSIVE DESIGN
   ============================================ */

@media (max-width: 1024px) {
    .sidebar {
        position: fixed;
        top: 0;
        {$dir}: calc(-1 * var(--sidebar-width));
        height: 100vh;
        z-index: var(--z-fixed);
        transition: {$dir} var(--transition);
        box-shadow: var(--shadow-xl);
    }
    
    .sidebar.open {
        {$dir}: 0;
    }
    
    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: calc(var(--z-fixed) - 1);
    }
    
    .sidebar-overlay.active {
        display: block;
    }
}

@media (max-width: 768px) {
    :root {
        --header-height: var(--header-height-mobile);
    }
    
    .menu-toggle {
        display: flex;
    }
    
    .main-navigation {
        position: fixed;
        top: var(--header-height);
        {$dir}: 0;
        width: 100%;
        height: calc(100vh - var(--header-height));
        background: white;
        transform: translateX({$dir === 'rtl' ? '100%' : '-100%'});
        transition: transform var(--transition);
        overflow-y: auto;
    }
    
    .main-navigation.open {
        transform: translateX(0);
    }
    
    .horizontal-menu {
        flex-direction: column;
        padding: var(--space-lg);
    }
    
    .horizontal-menu a {
        color: var(--text);
        padding: var(--space-md);
    }
    
    .dropdown-menu {
        position: static;
        opacity: 1;
        visibility: visible;
        transform: none;
        box-shadow: none;
        padding-{$dir}: var(--space-lg);
    }
    
    .grid-cols-2,
    .grid-cols-3,
    .grid-cols-4 {
        grid-template-columns: 1fr;
    }
    
    .footer-bottom {
        flex-direction: column;
        gap: var(--space-md);
        text-align: center;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .header-inner {
        gap: var(--space-sm);
    }
    
    .logo img {
        height: 32px;
    }
}

/* Utility Classes */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}

.text-center { text-center; }
.text-right { text-align: {$dir === 'rtl' ? 'left' : 'right'}; }
.text-left { text-align: {$dir === 'rtl' ? 'right' : 'left'}; }

.d-none { display: none; }
.d-block { display: block; }
.d-flex { display: flex; }
.d-grid { display: grid; }

@media (min-width: 768px) {
    .d-md-none { display: none; }
    .d-md-block { display: block; }
    .d-md-flex { display: flex; }
}

@media (min-width: 1024px) {
    .d-lg-none { display: none; }
    .d-lg-block { display: block; }
    .d-lg-flex { display: flex; }
}
CSS;
    }

    protected function generateAdvancedJs(array $data): string
    {
        return <<<JS
/**
 * NeuroCMS Template Engine
 * Responsive & Interactive Components
 */

class NeuroTemplate {
    constructor() {
        this.breakpoints = {
            mobile: 576,
            tablet: 768,
            desktop: 1024,
            wide: 1400
        };
        this.init();
    }

    init() {
        this.initMobileMenu();
        this.initSidebar();
        this.initDropdowns();
        this.initStickyHeader();
        this.initDarkMode();
        this.initLazyLoad();
        this.initAnimations();
        this.initAccessibility();
    }

    initMobileMenu() {
        const toggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('.main-navigation');
        
        if (!toggle || !nav) return;

        toggle.addEventListener('click', () => {
            const isOpen = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', isOpen);
            document.body.style.overflow = isOpen ? 'hidden' : '';
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!nav.contains(e.target) && !toggle.contains(e.target)) {
                nav.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Close on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && nav.classList.contains('open')) {
                nav.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    initSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (!sidebar) return;

        // Collapsible sidebar for admin
        const collapseBtn = document.querySelector('.sidebar-collapse');
        if (collapseBtn) {
            collapseBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
            });

            // Restore state
            if (localStorage.getItem('sidebar-collapsed') === 'true') {
                sidebar.classList.add('collapsed');
            }
        }
    }

    initDropdowns() {
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const dropdown = toggle.closest('.dropdown');
                const menu = dropdown.querySelector('.dropdown-menu');
                
                // Close others
                document.querySelectorAll('.dropdown.open').forEach(d => {
                    if (d !== dropdown) d.classList.remove('open');
                });

                dropdown.classList.toggle('open');
            });
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown.open').forEach(d => d.classList.remove('open'));
            }
        });
    }

    initStickyHeader() {
        const header = document.querySelector('.site-header.sticky');
        if (!header) return;

        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                header.classList.add('scrolled');
                if (currentScroll > lastScroll) {
                    header.classList.add('hidden');
                } else {
                    header.classList.remove('hidden');
                }
            } else {
                header.classList.remove('scrolled', 'hidden');
            }
            
            lastScroll = currentScroll;
        });
    }

    initDarkMode() {
        const toggle = document.querySelector('[data-theme-toggle]');
        if (!toggle) return;

        // Check system preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const savedTheme = localStorage.getItem('theme');

        if (savedTheme) {
            document.body.setAttribute('data-theme', savedTheme);
        } else if (prefersDark) {
            document.body.setAttribute('data-theme', 'dark');
        }

        toggle.addEventListener('click', () => {
            const current = document.body.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.body.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        });
    }

    initLazyLoad() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img.lazy').forEach(img => imageObserver.observe(img));
        }
    }

    initAnimations() {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('[data-animate]').forEach(el => observer.observe(el));
        }
    }

    initAccessibility() {
        // Skip link
        const skipLink = document.createElement('a');
        skipLink.href = '#main-content';
        skipLink.className = 'sr-only skip-link';
        skipLink.textContent = 'پرش به محتوای اصلی';
        document.body.prepend(skipLink);

        // Focus trap for modals
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('keydown', (e) => {
                if (e.key === 'Tab') {
                    const focusable = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                    const first = focusable[0];
                    const last = focusable[focusable.length - 1];

                    if (e.shiftKey && document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    } else if (!e.shiftKey && document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            });
        });
    }

    // Utility: Debounce
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Utility: AJAX helper
    async fetch(url, options = {}) {
        const defaultOptions = {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        return fetch(url, { ...defaultOptions, ...options });
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    window.neuroTemplate = new NeuroTemplate();
});

// Livewire-like updates (if needed)
window.addEventListener('template-updated', (e) => {
    const { position, html } = e.detail;
    const container = document.querySelector(`[data-position="${position}"]`);
    if (container) container.innerHTML = html;
});
JS;
    }

    protected function getHeroComponent(): string
    {
        return <<<BLADE
<section class="hero {{ \$class ?? '' }}">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">{{ \$title ?? '' }}</h1>
            <p class="hero-description">{{ \$description ?? '' }}</p>
            @if(\$buttons ?? false)
                <div class="hero-buttons">
                    @foreach(\$buttons as \$btn)
                        <a href="{{ \$btn['url'] }}" class="btn {{ \$btn['class'] ?? 'btn-primary' }}">
                            {{ \$btn['text'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
        @if(\$image ?? false)
            <div class="hero-image">
                <img src="{{ \$image }}" alt="{{ \$title ?? '' }}" loading="lazy">
            </div>
        @endif
    </div>
</section>
BLADE;
    }

    protected function getCardsComponent(): string
    {
        return <<<BLADE
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 {{ \$class ?? '' }}">
    @foreach(\$cards as \$card)
        <div class="card" data-animate>
            @if(\$card['image'])
                <div class="card-image">
                    <img src="{{ \$card['image'] }}" alt="{{ \$card['title'] }}" loading="lazy">
                </div>
            @endif
            <div class="card-body">
                <h3>{{ \$card['title'] }}</h3>
                <p>{{ \$card['description'] }}</p>
                @if(\$card['link'])
                    <a href="{{ \$card['link'] }}" class="btn btn-primary btn-sm">
                        {{ \$card['link_text'] ?? 'بیشتر' }}
                    </a>
                @endif
            </div>
        </div>
    @endforeach
</div>
BLADE;
    }

    protected function getStatsComponent(): string
    {
        return <<<BLADE
<div class="stats-grid {{ \$class ?? '' }}">
    @foreach(\$stats as \$stat)
        <div class="stat-card" data-animate>
            <div class="stat-value" data-count="{{ \$stat['value'] }}">0</div>
            <div class="stat-label">{{ \$stat['label'] }}</div>
        </div>
    @endforeach
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-count]').forEach(el => {
        const target = parseInt(el.dataset.count);
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const timer = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            el.textContent = target.toLocaleString('fa-IR');
                            clearInterval(timer);
                        } else {
                            el.textContent = Math.floor(current).toLocaleString('fa-IR');
                        }
                    }, 16);
                    observer.unobserve(el);
                }
            });
        });
        observer.observe(el);
    });
});
</script>
@endpush
BLADE;
    }

    protected function generateManifest(string $path, array $data, string $slug, string $type, string $relativePath): void
    {
        $manifest = [
            'name' => $data['name'],
            'slug' => $slug,
            'type' => $type,
            'version' => $data['version'] ?? '1.0.0',
            'author' => $data['author'] ?? 'NeuroCMS',
            'description' => $data['description'] ?? '',
            'license' => $data['license'] ?? 'MIT',
            'requires' => [
                'neurocms' => '>=2.0.0',
                'php' => '>=8.2',
            ],
            'config' => $this->buildConfig($data),
            'positions' => $this->getDefaultPositions($type),
            'components' => ['header', 'footer', 'sidebar', 'menu', 'hero', 'cards', 'stats'],
            'languages' => ['fa', 'en', 'ar'],
            'responsive' => true,
            'rtl' => $data['rtl'] ?? true,
        ];

        File::put("{$path}/template.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * خروجی ZIP از قالب
     */
    public function exportToZip(int $templateId): ?string
    {
        $template = DB::table('templates')->find($templateId);
        if (!$template) return null;

        $sourcePath = public_path($template->path);
        if (!File::isDirectory($sourcePath)) return null;

        $zipName = "{$template->slug}-v{$template->version}.zip";
        $zipPath = storage_path("app/exports/templates/{$zipName}");

        if (!File::isDirectory(dirname($zipPath))) {
            File::makeDirectory(dirname($zipPath), 0755, true);
        }

        // به‌روزرسانی manifest
        $manifest = json_decode(File::get("{$sourcePath}/template.json"), true);
        $manifest['exported_at'] = now()->toIso8601String();
        $manifest['export_by'] = auth()->user()->name ?? 'system';
        File::put("{$sourcePath}/template.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $files = File::allFiles($sourcePath);
        foreach ($files as $file) {
            $relativePath = str_replace($sourcePath . '/', '', $file->getPathname());
            $zip->addFile($file->getPathname(), $relativePath);
        }

        $zip->close();

        return $zipPath;
    }

    /**
     * نصب از ZIP
     */
    public function installFromZip(string $zipPath): array
    {
        $extractPath = storage_path('app/temp/templates/' . uniqid());
        File::makeDirectory($extractPath, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            File::deleteDirectory($extractPath);
            return ['success' => false, 'error' => 'Cannot open ZIP file'];
        }

        $zip->extractTo($extractPath);
        $zip->close();

        $manifestFile = $extractPath . '/template.json';
        if (!File::exists($manifestFile)) {
            File::deleteDirectory($extractPath);
            return ['success' => false, 'error' => 'Template manifest not found'];
        }

        $manifest = json_decode(File::get($manifestFile), true);
        if (!$manifest || !isset($manifest['name'], $manifest['slug'], $manifest['type'])) {
            File::deleteDirectory($extractPath);
            return ['success' => false, 'error' => 'Invalid manifest format'];
        }

        // بررسی نسخه
        if (isset($manifest['requires']['neurocms'])) {
            $required = $manifest['requires']['neurocms'];
            // Version check logic here
        }

        $targetPath = public_path("templates/{$manifest['type']}/{$manifest['slug']}");
        
        // پشتیبان‌گیری از قالب قبلی
        if (File::isDirectory($targetPath)) {
            $backupPath = $targetPath . '_backup_' . now()->format('Ymd_His');
            File::copyDirectory($targetPath, $backupPath);
        }

        File::copyDirectory($extractPath, $targetPath);
        File::deleteDirectory($extractPath);

        // ثبت در دیتابیس
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

        return ['success' => true, 'message' => 'Template installed successfully'];
    }

    /**
     * دریافت Layout فعال
     */
    public function getActiveLayout(string $type = 'site'): ?object
    {
        return DB::table('template_layouts')
            ->join('templates', 'template_layouts.template_id', '=', 'templates.id')
            ->where('templates.type', $type)
            ->where('templates.is_active', true)
            ->where('template_layouts.is_default', true)
            ->select('template_layouts.*', 'templates.slug as template_slug', 'templates.name as template_name')
            ->first();
    }

    /**
     * دریافت کامپوننت‌های فعال
     */
    public function getActiveComponents(int $templateId): array
    {
        return DB::table('template_components')
            ->where('template_id', $templateId)
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('order')
            ->get()
            ->groupBy('position')
            ->toArray();
    }

    /**
     * دریافت ویجت‌ها
     */
    public function getWidgets(string $position, ?int $templateId = null): array
    {
        if (!$templateId) {
            $layout = $this->getActiveLayout();
            $templateId = $layout->template_id ?? 0;
        }

        return DB::table('template_components')
            ->where('template_id', $templateId)
            ->where('position', $position)
            ->where('type', 'widget')
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(fn($w) => [
                'id' => $w->id,
                'name' => $w->name,
                'title' => json_decode($w->config, true)['title'] ?? null,
                'content' => $w->html_content,
                'class' => json_decode($w->config, true)['class'] ?? '',
            ])
            ->toArray();
    }

    /**
     * ذخیره چیدمان (Layout Builder)
     */
    public function saveLayout(int $templateId, array $structure, array $settings = []): bool
    {
        DB::table('template_layouts')
            ->where('template_id', $templateId)
            ->where('is_default', true)
            ->update([
                'structure' => json_encode($structure),
                'settings' => json_encode($settings),
                'updated_at' => now(),
            ]);

        $this->clearCache();
        return true;
    }

    /**
     * کامپایل قالب برای خروجی نهایی
     */
    public function compileTemplate(int $templateId): array
    {
        $template = DB::table('templates')->find($templateId);
        $components = $this->getActiveComponents($templateId);
        $layout = DB::table('template_layouts')
            ->where('template_id', $templateId)
            ->where('is_default', true)
            ->first();

        $path = public_path($template->path);
        $compiledPath = "{$path}/compiled";

        if (!File::isDirectory($compiledPath)) {
            File::makeDirectory($compiledPath, 0755, true);
        }

        // کامپایل CSS
        $css = File::get("{$path}/css/style.css");
        // Minify CSS (simple)
        $cssMin = preg_replace(['/\/\*.*?\*\//s', '/\s+/', '/;\s*}/'], ['', ' ', '}'], $css);
        File::put("{$compiledPath}/style.min.css", $cssMin);

        // کامپایل JS
        $js = File::get("{$path}/js/main.js");
        // Basic minification
        $jsMin = preg_replace(['/\/\*.*?\*\//s', '/\/\/.*$/m', '/\s+/'], ['', '', ' '], $js);
        File::put("{$compiledPath}/main.min.js", $jsMin);

        return [
            'success' => true,
            'path' => $compiledPath,
            'css_size' => strlen($cssMin),
            'js_size' => strlen($jsMin),
        ];
    }

    public function clearCache(): void
    {
        Cache::forget("{$this->cachePrefix}active_site");
        Cache::forget("{$this->cachePrefix}active_admin");
        Cache::forget("{$this->cachePrefix}layouts");
        Cache::forget("{$this->cachePrefix}components");
    }
}
