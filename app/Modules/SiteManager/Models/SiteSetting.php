<?php

namespace App\Modules\SiteManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $table = 'site_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * دریافت مقدار یک تنظیم
     */
    public static function get(string $key, $default = null)
    {
        $setting = Cache::remember("site_setting_{$key}", 3600, function () use ($key) {
            return self::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return $setting->value;
    }

    /**
     * تنظیم مقدار
     */
    public static function set(string $key, $value): void
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        
        Cache::put("site_setting_{$key}", $setting, 3600);
        Cache::forget('site_settings_all');
    }

    /**
     * دریافت تمام تنظیمات
     */
    public static function getAll(): array
    {
        return Cache::remember('site_settings_all', 3600, function () {
            return self::all()->pluck('value', 'key')->toArray();
        });
    }
}
