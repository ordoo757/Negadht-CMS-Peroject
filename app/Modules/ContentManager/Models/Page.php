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

namespace App\Modules\ContentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Page extends Model
{
    use SoftDeletes;

    protected $table = 'pages';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'is_home',
        'views',
        'user_id',
        'category_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'featured_image',
        'published_at',
    ];

    protected $casts = [
        'is_home' => 'boolean',
        'views' => 'integer',
        'published_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * روابط
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * اسکوپ‌ها
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * متدهای کمکی
     */
    public function getUrlAttribute(): string
    {
        return url('/page/' . $this->slug);
    }

    public function getEditUrlAttribute(): string
    {
        return route('admin.content-manager.pages.edit', $this->id);
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'draft' => 'پیش‌نویس',
            'published' => 'منتشر شده',
            'pending' => 'در انتظار بررسی',
            'trash' => 'زباله دان',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            'draft' => 'secondary',
            'published' => 'success',
            'pending' => 'warning',
            'trash' => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }

    /**
     * کش
     */
    public static function getPublished($limit = 10)
    {
        return Cache::remember('published_pages', 3600, function () use ($limit) {
            return self::published()->orderBy('published_at', 'desc')->limit($limit)->get();
        });
    }

    public function clearCache(): void
    {
        Cache::forget('published_pages');
        Cache::forget('page_' . $this->id);
        Cache::forget('page_slug_' . $this->slug);
    }

    /**
     * Boot
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
            if (empty($page->published_at) && $page->status === 'published') {
                $page->published_at = now();
            }
        });

        static::updating(function ($page) {
            if ($page->isDirty('status') && $page->status === 'published') {
                $page->published_at = now();
            }
            $page->clearCache();
        });

        static::deleted(function ($page) {
            $page->clearCache();
        });
    }
}
