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

namespace App\Modules\ContentManager;

use App\Core\Foundation\Component;

class Module extends Component
{
    protected string $name = 'مدیریت محتوا';
    protected string $slug = 'content-manager';
    protected string $version = '1.0.0';
    protected string $icon = 'newspaper';
    protected string $adminRoute = 'admin.content-manager.pages.index';
    protected array $dependencies = ['User', 'AiKernel'];

    public function registerModule(): void
    {
        $this->app->singleton('content.manager', Services\ContentService::class);
    }

    public function bootModule(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadTranslations();

        // ثبت کامپوننت‌های ویرایشگر
        $this->registerEditorComponents();

        // بارگذاری تنظیمات محتوا
        $this->loadContentSettings();
    }

    public function install(): bool
    {
        $this->runMigrations();
        $this->createDefaultCategories();
        $this->createDefaultPages();
        return true;
    }

    public function uninstall(): bool
    {
        return true;
    }

    public function update(string $oldVersion): bool
    {
        return true;
    }

    protected function runMigrations(): void
    {
        $migrationPath = __DIR__ . '/Migrations';
        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    protected function createDefaultCategories(): void
    {
        $defaults = [
            ['name' => 'اخبار', 'slug' => 'news', 'description' => 'اخبار و رویدادها'],
            ['name' => 'مقالات', 'slug' => 'articles', 'description' => 'مقالات آموزشی و تخصصی'],
            ['name' => 'گزارش‌ها', 'slug' => 'reports', 'description' => 'گزارش‌های تحلیلی'],
            ['name' => 'وبلاگ', 'slug' => 'blog', 'description' => 'وبلاگ شخصی و عمومی'],
        ];

        foreach ($defaults as $category) {
            \App\Modules\ContentManager\Models\Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }

    protected function createDefaultPages(): void
    {
        $defaults = [
            [
                'title' => 'صفحه اصلی',
                'slug' => 'home',
                'content' => '<h1>به NeuroCMS خوش آمدید</h1><p>سیستم مدیریت محتوای هوشمند</p>',
                'status' => 'published',
                'is_home' => true,
            ],
            [
                'title' => 'درباره ما',
                'slug' => 'about',
                'content' => '<h1>درباره NeuroCMS</h1><p>NeuroCMS یک سیستم مدیریت محتوای قدرتمند و هوشمند است.</p>',
                'status' => 'published',
                'is_home' => false,
            ],
            [
                'title' => 'تماس با ما',
                'slug' => 'contact',
                'content' => '<h1>تماس با ما</h1><p>برای ارتباط با ما از فرم زیر استفاده کنید.</p>',
                'status' => 'published',
                'is_home' => false,
            ],
        ];

        foreach ($defaults as $page) {
            \App\Modules\ContentManager\Models\Page::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }

    protected function registerEditorComponents(): void
    {
        // ثبت کامپوننت‌های ویرایشگر در ویو
        view()->share('editor_config', $this->getEditorConfig());
    }

    protected function getEditorConfig(): array
    {
        return [
            'toolbar' => [
                'undo', 'redo', '|',
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'alignleft', 'aligncenter', 'alignright', 'alignjustify', '|',
                'bullist', 'numlist', '|',
                'link', 'unlink', '|',
                'image', 'media', '|',
                'code', 'fullscreen',
            ],
            'plugins' => ['link', 'image', 'media', 'code', 'fullscreen', 'table'],
            'height' => 400,
            'menubar' => true,
        ];
    }

    protected function loadContentSettings(): void
    {
        try {
            $settings = \App\Modules\ContentManager\Models\ContentSetting::all();
            foreach ($settings as $setting) {
                config()->set('content.' . $setting->key, $setting->value);
            }
        } catch (\Exception $e) {
            // جدول هنوز وجود ندارد
        }
    }

    public function registerAdminMenu(): array
    {
        return [
            [
                'title' => 'مدیریت محتوا',
                'icon' => 'newspaper',
                'route' => 'admin.content-manager.pages.index',
                'children' => [
                    ['title' => 'صفحات', 'route' => 'admin.content-manager.pages.index'],
                    ['title' => 'صفحه جدید', 'route' => 'admin.content-manager.pages.create'],
                    ['title' => 'دسته‌بندی‌ها', 'route' => 'admin.content-manager.categories.index'],
                    ['title' => 'رسانه‌ها', 'route' => 'admin.content-manager.media.index'],
                    ['title' => 'نظرات', 'route' => 'admin.content-manager.comments.index'],
                ],
            ],
        ];
    }
}
