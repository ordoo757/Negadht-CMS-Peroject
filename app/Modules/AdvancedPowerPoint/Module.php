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

namespace App\Modules\AdvancedPowerPoint;

use App\Core\Foundation\Component;

class Module extends Component
{
    protected string $name = 'پاورپوینت پیشرفته';
    protected string $slug = 'advanced-powerpoint';
    protected string $version = '1.0.0';
    protected string $icon = 'file-powerpoint';
    protected string $adminRoute = 'admin.advanced-powerpoint.index';
    protected array $dependencies = ['User'];

    public function registerModule(): void
    {
        $this->app->singleton('advanced.powerpoint', Services\PowerPointService::class);
    }

    public function bootModule(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadTranslations();
        $this->loadAssets();
    }

    public function install(): bool
    {
        $this->runMigrations();
        $this->createDefaultPresentations();
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

    protected function createDefaultPresentations(): void
    {
        // ایجاد یک ارائه پیش‌فرض
        $presentation = \App\Modules\AdvancedPowerPoint\Models\Presentation::create([
            'name' => 'ارائه پیش‌فرض',
            'description' => 'ارائه پیش‌فرض سیستم',
            'is_active' => true,
            'is_public' => true,
            'user_id' => 1,
            'theme' => 'default',
            'settings' => [
                'width' => 1280,
                'height' => 720,
                'background' => '#ffffff',
                'font_family' => 'Vazir',
            ],
        ]);

        // ایجاد اسلایدهای پیش‌فرض
        $slide1 = \App\Modules\AdvancedPowerPoint\Models\Slide::create([
            'presentation_id' => $presentation->id,
            'title' => 'اسلاید اول',
            'order' => 0,
            'layout' => 'title',
            'background' => '#1a1a2e',
        ]);

        \App\Modules\AdvancedPowerPoint\Models\SlideElement::create([
            'slide_id' => $slide1->id,
            'type' => 'text',
            'content' => 'به NeuroCMS خوش آمدید',
            'style' => [
                'font_size' => 48,
                'color' => '#ffffff',
                'text_align' => 'center',
                'font_weight' => 'bold',
            ],
            'position' => ['x' => 50, 'y' => 40],
            'size' => ['width' => 80, 'height' => 20],
        ]);

        $slide2 = \App\Modules\AdvancedPowerPoint\Models\Slide::create([
            'presentation_id' => $presentation->id,
            'title' => 'اسلاید دوم',
            'order' => 1,
            'layout' => 'content',
            'background' => '#16213e',
        ]);

        \App\Modules\AdvancedPowerPoint\Models\SlideElement::create([
            'slide_id' => $slide2->id,
            'type' => 'text',
            'content' => 'سیستم مدیریت محتوای هوشمند',
            'style' => [
                'font_size' => 36,
                'color' => '#ffffff',
                'text_align' => 'center',
                'font_weight' => 'bold',
            ],
            'position' => ['x' => 50, 'y' => 30],
            'size' => ['width' => 80, 'height' => 15],
        ]);

        \App\Modules\AdvancedPowerPoint\Models\SlideElement::create([
            'slide_id' => $slide2->id,
            'type' => 'text',
            'content' => 'NeuroCMS یک سیستم مدیریت محتوای قدرتمند و هوشمند است که با استفاده از هوش مصنوعی، تجربه کاربری بی‌نظیری را ارائه می‌دهد.',
            'style' => [
                'font_size' => 20,
                'color' => '#e0e0e0',
                'text_align' => 'center',
            ],
            'position' => ['x' => 50, 'y' => 50],
            'size' => ['width' => 70, 'height' => 25],
        ]);
    }

    protected function loadAssets(): void
    {
        $this->publishes([
            __DIR__ . '/Assets/css' => public_path('assets/powerpoint/css'),
            __DIR__ . '/Assets/js' => public_path('assets/powerpoint/js'),
        ], 'powerpoint-assets');
    }

    public function registerAdminMenu(): array
    {
        return [
            [
                'title' => 'پاورپوینت پیشرفته',
                'icon' => 'file-powerpoint',
                'route' => 'admin.advanced-powerpoint.index',
                'children' => [
                    ['title' => 'ارائه‌ها', 'route' => 'admin.advanced-powerpoint.index'],
                    ['title' => 'ایجاد ارائه جدید', 'route' => 'admin.advanced-powerpoint.create'],
                    ['title' => 'تنظیمات', 'route' => 'admin.advanced-powerpoint.settings'],
                ],
            ],
        ];
    }
}
