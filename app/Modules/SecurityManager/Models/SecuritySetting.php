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

namespace App\Modules\SecurityManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SecuritySetting extends Model
{
    protected $table = 'security_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function get(string $key, $default = null)
    {
        $setting = Cache::remember("security_setting_{$key}", 3600, function () use ($key) {
            return self::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return $setting->value;
    }

    public static function set(string $key, $value): void
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        
        Cache::put("security_setting_{$key}", $setting, 3600);
        Cache::forget('security_settings_all');
    }

    public static function getAll(): array
    {
        return Cache::remember('security_settings_all', 3600, function () {
            return self::all()->pluck('value', 'key')->toArray();
        });
    }
}
