<?php

namespace App\Modules\ComponentMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Component extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * نام جدول در دیتابیس
     *
     * @var string
     */
    protected $table = 'components';

    /**
     * فیلدهای قابل پر کردن (Mass Assignment)
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'type',
        'version',
        'status',
        'author',
        'author_email',
        'website',
        'license',
        'config',
        'settings',
        'tags',
        'dependencies',
        'screenshots',
        'preview_image',
        'view_path',
        'style_path',
        'script_path',
        'is_active',
        'is_core',
        'is_system',
        'is_public',
        'view_count',
        'download_count',
        'last_used_at',
        'installed_at',
    ];

    /**
     * فیلدهایی که باید به صورت تاریخ/زمان Cast شوند
     *
     * @var array<string, string>
     */
    protected $casts = [
        'config' => 'array',
        'settings' => 'array',
        'tags' => 'array',
        'dependencies' => 'array',
        'screenshots' => 'array',
        'is_active' => 'boolean',
        'is_core' => 'boolean',
        'is_system' => 'boolean',
        'is_public' => 'boolean',
        'view_count' => 'integer',
        'download_count' => 'integer',
        'last_used_at' => 'datetime',
        'installed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * مقادیر پیش‌فرض برای فیلدها
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
        'type' => 'custom',
        'version' => '1.0.0',
        'is_active' => true,
        'is_core' => false,
        'is_system' => false,
        'is_public' => true,
        'view_count' => 0,
        'download_count' => 0,
    ];

    /**
     * فیلدهای قابل جستجو (برای استفاده در متد search)
     *
     * @var array<int, string>
     */
    protected array $searchable = [
        'name',
        'slug',
        'description',
        'category',
        'type',
        'author',
        'tags',
    ];

    // =========================================================
    // روابط (Relationships)
    // =========================================================

    /**
     * کاربری که این کامپوننت را ایجاد کرده است.
     */
    public function creator()
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class), 'author', 'email');
    }

    /**
     * کامپوننت‌هایی که به این کامپوننت وابسته هستند.
     */
    public function dependents()
    {
        return $this->hasMany(static::class, 'dependencies', 'slug');
    }

    /**
     * کامپوننت‌هایی که این کامپوننت به آنها وابسته است (Many-to-Many).
     */
    public function dependencies()
    {
        return $this->belongsToMany(static::class, 'component_dependencies', 'component_id', 'dependency_id');
    }

    // =========================================================
    // اسکوپ‌ها (Scopes)
    // =========================================================

    /**
     * اسکوپ برای فیلتر کردن کامپوننت‌های فعال.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * اسکوپ برای فیلتر کردن کامپوننت‌های عمومی.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * اسکوپ برای فیلتر کردن کامپوننت‌های سیستمی.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * اسکوپ برای فیلتر بر اساس دسته‌بندی.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * اسکوپ برای فیلتر بر اساس وضعیت.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * اسکوپ برای جستجوی پیشرفته.
     */
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
              ->orWhere('description', 'like', "%{$keyword}%")
              ->orWhere('category', 'like', "%{$keyword}%")
              ->orWhere('type', 'like', "%{$keyword}%")
              ->orWhere('author', 'like', "%{$keyword}%")
              ->orWhereJsonContains('tags', $keyword);
        });
    }

    // =========================================================
    // متدهای اصلی و کمکی
    // =========================================================

    /**
     * دریافت مسیر کامل ذخیره‌سازی کامپوننت در storage.
     */
    public function getFullPath(): string
    {
        return storage_path("app/components/{$this->slug}");
    }

    /**
     * دریافت مسیر عمومی کامپوننت در public.
     */
    public function getPublicPath(): string
    {
        return public_path("components/{$this->slug}");
    }

    /**
     * دریافت URL قابل دسترسی کامپوننت.
     */
    public function getUrl(): string
    {
        return url("components/{$this->slug}");
    }

    /**
     * دریافت آیکون کامپوننت از تنظیمات.
     */
    public function getIcon(): string
    {
        return $this->config['icon'] ?? 'box';
    }

    /**
     * بررسی نصب بودن کامپوننت.
     */
    public function isInstalled(): bool
    {
        return file_exists($this->getFullPath());
    }

    /**
     * دریافت نسخه قابل نمایش.
     */
    public function getVersion(): string
    {
        return $this->version ?? '1.0.0';
    }

    /**
     * بررسی پایدار بودن (Stable).
     */
    public function isStable(): bool
    {
        return $this->status === 'stable';
    }

    /**
     * دریافت برچسب وضعیت به صورت فارسی/انگلیسی.
     */
    public function getStatusLabel(): string
    {
        $labels = [
            'draft'      => 'پیش‌نویس',
            'stable'     => 'پایدار',
            'beta'       => 'بتا',
            'alpha'      => 'آلفا',
            'deprecated' => 'منسوخ',
            'archived'   => 'بایگانی',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * دریافت رنگ وضعیت (برای استفاده در UI).
     */
    public function getStatusColor(): string
    {
        $colors = [
            'draft'      => 'gray',
            'stable'     => 'green',
            'beta'       => 'blue',
            'alpha'      => 'orange',
            'deprecated' => 'red',
            'archived'   => 'gray',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    // =========================================================
    // متدهای کش (Cache) و بهینه‌سازی
    // =========================================================

    /**
     * دریافت کامپوننت از کش با استفاده از slug.
     */
    public static function getFromCache(string $slug): ?self
    {
        $cacheKey = "component_{$slug}";
        return Cache::remember($cacheKey, 3600, function () use ($slug) {
            return self::where('slug', $slug)->first();
        });
    }

    /**
     * پاک کردن کش مرتبط با این کامپوننت.
     */
    public function clearCache(): void
    {
        Cache::forget("component_{$this->slug}");
        Cache::forget('components_list');
        Cache::forget('components_categories');
        Cache::forget('components_stats');
    }

    /**
     * دریافت لیست کامپوننت‌ها با قابلیت فیلتر و کش.
     */
    public static function getList(array $filters = []): array
    {
        $cacheKey = 'components_list_' . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, 1800, function () use ($filters) {
            $query = self::query()->active();
            
            if (!empty($filters['category'])) {
                $query->category($filters['category']);
            }
            
            if (!empty($filters['status'])) {
                $query->status($filters['status']);
            }
            
            if (!empty($filters['type'])) {
                $query->where('type', $filters['type']);
            }
            
            if (!empty($filters['search'])) {
                $query->search($filters['search']);
            }
            
            return $query->orderBy('name')->get()->toArray();
        });
    }

    /**
     * دریافت لیست دسته‌بندی‌های موجود.
     */
    public static function getCategories(): array
    {
        return Cache::remember('components_categories', 86400, function () {
            return self::select('category')
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category')
                ->toArray();
        });
    }

    // =========================================================
    // متدهای آماری و مدیریت بازدید/دانلود
    // =========================================================

    /**
     * افزایش شمارنده بازدید.
     */
    public function incrementView(): void
    {
        $this->increment('view_count');
        $this->clearCache();
    }

    /**
     * افزایش شمارنده دانلود.
     */
    public function incrementDownload(): void
    {
        $this->increment('download_count');
        $this->clearCache();
    }

    /**
     * دریافت آمار کلی کامپوننت‌ها.
     */
    public static function getStats(): array
    {
        return Cache::remember('components_stats', 3600, function () {
            return [
                'total'      => self::count(),
                'active'     => self::active()->count(),
                'public'     => self::public()->count(),
                'system'     => self::system()->count(),
                'categories' => self::getCategories(),
                'recent'     => self::orderBy('created_at', 'desc')->limit(10)->get(),
                'popular'    => self::orderBy('view_count', 'desc')->limit(10)->get(),
            ];
        });
    }

    /**
     * دریافت کامپوننت‌های مشابه بر اساس دسته‌بندی.
     */
    public function getSimilar(int $limit = 5): array
    {
        return self::active()
            ->where('category', $this->category)
            ->where('id', '!=', $this->id)
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * دریافت محبوب‌ترین کامپوننت‌ها بر اساس دانلود.
     */
    public static function getPopular(int $limit = 10): array
    {
        return self::active()
            ->public()
            ->orderBy('download_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    // =========================================================
    // متدهای پیشرفته و آینده‌نگر
    // =========================================================

    /**
     * ایجاد کامپوننت جدید از روی قالب.
     */
    public static function createFromTemplate(string $template, array $data): self
    {
        $component = new self();
        $component->name       = $data['name'] ?? 'New Component';
        $component->category   = $data['category'] ?? 'general';
        $component->type       = $data['type'] ?? 'custom';
        $component->config     = $data['config'] ?? [];
        $component->settings   = $data['settings'] ?? [];
        $component->tags       = $data['tags'] ?? [];
        $component->dependencies = $data['dependencies'] ?? [];
        $component->author     = $data['author'] ?? auth()->user()->email ?? 'system';
        $component->save();

        return $component;
    }

    /**
     * صادر کردن کامپوننت به صورت فایل ZIP.
     */
    public function export(): string
    {
        $zipFileName = "component_{$this->slug}_v{$this->version}.zip";
        $zipPath = storage_path("app/exports/{$zipFileName}");

        // ... منطق ساخت ZIP در آینده

        return $zipPath;
    }

    /**
     * وارد کردن کامپوننت از فایل ZIP.
     */
    public static function import(string $zipPath): self
    {
        // ... منطق وارد کردن از ZIP در آینده
        return new self();
    }

    /**
     * نصب کامپوننت در سیستم.
     */
    public function install(): bool
    {
        if ($this->isInstalled()) {
            return true;
        }

        // ... منطق نصب در آینده

        $this->installed_at = now();
        $this->is_active = true;
        $this->save();

        return true;
    }

    /**
     * حذف کامپوننت از سیستم.
     */
    public function uninstall(): bool
    {
        if (!$this->isInstalled()) {
            return true;
        }

        // ... منطق حذف در آینده

        $this->is_active = false;
        $this->save();

        return true;
    }

    // =========================================================
    // Boot Model Events
    // =========================================================

    /**
     * بوت کردن مدل و تنظیم رویدادها.
     */
    protected static function boot()
    {
        parent::boot();

        // ایجاد خودکار slug هنگام ساخت
        static::creating(function ($component) {
            if (empty($component->slug)) {
                $component->slug = Str::slug($component->name);
            }
        });

        // پاک کردن کش هنگام بروزرسانی
        static::updating(function ($component) {
            $component->clearCache();
        });

        // پاک کردن کش هنگام حذف
        static::deleted(function ($component) {
            $component->clearCache();
        });
    }
}
