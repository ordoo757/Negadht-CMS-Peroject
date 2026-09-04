<?php

namespace App\Modules\PluginMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Plugin extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'plugins';

    protected $fillable = [
        'name', 'slug', 'description', 'category', 'type', 'version', 'status',
        'author', 'author_email', 'website', 'license', 'price', 'is_free',
        'config', 'settings', 'tags', 'dependencies', 'screenshots',
        'preview_image', 'view_path', 'style_path', 'script_path',
        'is_active', 'is_core', 'is_system', 'is_public',
        'view_count', 'download_count', 'last_used_at', 'installed_at',
        'activated_at', 'expires_at',
    ];

    protected $casts = [
        'config' => 'json',
        'settings' => 'json',
        'tags' => 'json',
        'dependencies' => 'json',
        'screenshots' => 'json',
        'is_active' => 'boolean',
        'is_core' => 'boolean',
        'is_system' => 'boolean',
        'is_public' => 'boolean',
        'is_free' => 'boolean',
        'view_count' => 'integer',
        'download_count' => 'integer',
        'price' => 'float',
        'last_used_at' => 'datetime',
        'installed_at' => 'datetime',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'draft',
        'type' => 'general',
        'version' => '1.0.0',
        'is_active' => true,
        'is_core' => false,
        'is_system' => false,
        'is_public' => true,
        'is_free' => true,
        'price' => 0,
        'view_count' => 0,
        'download_count' => 0,
    ];

    public function getFullPath(): string
    {
        return storage_path("app/plugins/{$this->slug}");
    }

    public function getPublicPath(): string
    {
        return public_path("plugins/{$this->slug}");
    }

    public function getUrl(): string
    {
        return url("plugins/{$this->slug}");
    }

    public function isInstalled(): bool
    {
        return file_exists($this->getFullPath());
    }

    public function isActivated(): bool
    {
        return $this->activated_at !== null && $this->activated_at <= now();
    }

    public function getPriceLabel(): string
    {
        if ($this->is_free || $this->price == 0) {
            return 'رایگان';
        }
        return number_format($this->price) . ' تومان';
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'draft' => 'پیش‌نویس',
            'stable' => 'پایدار',
            'beta' => 'بتا',
            'alpha' => 'آلفا',
            'deprecated' => 'منسوخ',
            'archived' => 'بایگانی',
            default => $this->status,
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'stable' => 'green',
            'beta' => 'blue',
            'alpha' => 'orange',
            'deprecated' => 'red',
            'archived' => 'gray',
            default => 'gray',
        };
    }

    public function clearCache(): void
    {
        Cache::forget("plugin_{$this->slug}");
        Cache::forget('plugins_list');
        Cache::forget('plugins_categories');
        Cache::forget('plugins_stats');
    }

    public function incrementView(): void
    {
        $this->increment('view_count');
        $this->clearCache();
    }

    public function incrementDownload(): void
    {
        $this->increment('download_count');
        $this->clearCache();
    }

    public function activate(): bool
    {
        $this->activated_at = now();
        $this->is_active = true;
        $this->save();
        $this->clearCache();
        return true;
    }

    public function deactivate(): bool
    {
        $this->is_active = false;
        $this->save();
        $this->clearCache();
        return true;
    }

    public function install(): bool
    {
        if ($this->isInstalled()) {
            return true;
        }
        $this->installed_at = now();
        $this->is_active = true;
        $this->save();
        return true;
    }

    public function uninstall(): bool
    {
        if (!$this->isInstalled()) {
            return true;
        }
        $this->is_active = false;
        $this->save();
        return true;
    }

    public static function getStats(): array
    {
        return Cache::remember('plugins_stats', 3600, function () {
            return [
                'total' => self::count(),
                'active' => self::where('is_active', true)->count(),
                'free' => self::where('is_free', true)->count(),
                'paid' => self::where('is_free', false)->count(),
                'installed' => self::whereNotNull('installed_at')->count(),
            ];
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plugin) {
            if (empty($plugin->slug)) {
                $plugin->slug = Str::slug($plugin->name);
            }
        });

        static::updating(function ($plugin) {
            $plugin->clearCache();
        });

        static::deleted(function ($plugin) {
            $plugin->clearCache();
        });
    }
}
