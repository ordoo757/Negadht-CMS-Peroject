<?php

namespace App\Modules\AdvancedExcel;

use App\Core\Foundation\Component;

class Module extends Component
{
    protected string $name = 'اکسل پیشرفته';
    protected string $slug = 'advanced-excel';
    protected string $version = '1.0.0';
    protected string $icon = 'table';
    protected string $adminRoute = 'admin.advanced-excel.index';
    protected array $dependencies = ['User'];

    public function registerModule(): void
    {
        $this->app->singleton('advanced.excel', Services\ExcelService::class);
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
        $this->createDefaultWorkbook();
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

    protected function createDefaultWorkbook(): void
    {
        // ایجاد کتاب کار پیش‌فرض
        $workbook = \App\Modules\AdvancedExcel\Models\Workbook::create([
            'name' => 'کتاب کار پیش‌فرض',
            'description' => 'کتاب کار پیش‌فرض سیستم',
            'is_active' => true,
            'is_public' => true,
            'user_id' => 1,
            'settings' => [
                'default_font' => 'Vazir',
                'default_font_size' => 12,
                'default_cell_width' => 100,
                'default_cell_height' => 25,
            ],
        ]);

        // ایجاد برگه پیش‌فرض
        $worksheet = \App\Modules\AdvancedExcel\Models\Worksheet::create([
            'workbook_id' => $workbook->id,
            'name' => 'برگه ۱',
            'order' => 0,
            'is_active' => true,
        ]);

        // ایجاد سلول‌های نمونه
        $sampleData = [
            ['A1' => 'نام', 'B1' => 'ایمیل', 'C1' => 'نقش', 'D1' => 'تاریخ ثبت'],
            ['A2' => 'مدیر', 'B2' => 'admin@site.com', 'C2' => 'مدیر', 'D2' => '2024-01-01'],
            ['A3' => 'ویرایشگر', 'B3' => 'editor@site.com', 'C3' => 'ویرایشگر', 'D3' => '2024-01-02'],
            ['A4' => 'نویسنده', 'B4' => 'writer@site.com', 'C4' => 'نویسنده', 'D4' => '2024-01-03'],
        ];

        foreach ($sampleData as $row) {
            foreach ($row as $cellId => $value) {
                \App\Modules\AdvancedExcel\Models\Cell::create([
                    'worksheet_id' => $worksheet->id,
                    'cell_id' => $cellId,
                    'value' => $value,
                    'data_type' => is_numeric($value) ? 'number' : (strtotime($value) ? 'date' : 'text'),
                ]);
            }
        }
    }

    protected function loadAssets(): void
    {
        $this->publishes([
            __DIR__ . '/Assets/css' => public_path('assets/excel/css'),
            __DIR__ . '/Assets/js' => public_path('assets/excel/js'),
        ], 'excel-assets');
    }

    public function registerAdminMenu(): array
    {
        return [
            [
                'title' => 'اکسل پیشرفته',
                'icon' => 'table',
                'route' => 'admin.advanced-excel.index',
                'children' => [
                    ['title' => 'کتاب‌های کار', 'route' => 'admin.advanced-excel.index'],
                    ['title' => 'ایجاد کتاب جدید', 'route' => 'admin.advanced-excel.create'],
                    ['title' => 'تنظیمات', 'route' => 'admin.advanced-excel.settings'],
                ],
            ],
        ];
    }
}
