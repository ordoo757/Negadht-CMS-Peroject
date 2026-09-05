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

namespace App\Modules\AdvancedPowerPoint\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Presentation extends Model
{
    protected $table = 'presentations';

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'is_public',
        'user_id',
        'theme',
        'settings',
        'view_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'settings' => 'json',
        'view_count' => 'integer',
    ];

    /**
     * روابط
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function slides()
    {
        return $this->hasMany(Slide::class)->orderBy('order');
    }

    /**
     * متدهای کمکی
     */
    public function getShareUrlAttribute(): string
    {
        return route('powerpoint.embed', $this->id);
    }

    public function getEditUrlAttribute(): string
    {
        return route('admin.advanced-powerpoint.edit', $this->id);
    }

    public function getThumbnailAttribute(): string
    {
        $firstSlide = $this->slides()->first();
        if ($firstSlide) {
            return asset('assets/powerpoint/thumbnails/' . $firstSlide->id . '.png');
        }
        return asset('assets/powerpoint/default-thumbnail.png');
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    /**
     * کش
     */
    public function clearCache(): void
    {
        Cache::forget('presentation_' . $this->id);
        Cache::forget('presentations_list');
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($presentation) {
            $presentation->clearCache();
        });

        static::deleted(function ($presentation) {
            $presentation->clearCache();
        });
    }
}
