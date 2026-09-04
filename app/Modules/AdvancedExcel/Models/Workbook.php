<?php

namespace App\Modules\AdvancedExcel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Workbook extends Model
{
    protected $table = 'excel_workbooks';

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'is_public',
        'user_id',
        'settings',
        'view_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'settings' => 'json',
        'view_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function worksheets()
    {
        return $this->hasMany(Worksheet::class)->orderBy('order');
    }

    public function getShareUrlAttribute(): string
    {
        return route('excel.embed', $this->id);
    }

    public function getEditUrlAttribute(): string
    {
        return route('admin.advanced-excel.edit', $this->id);
    }

    public function getThumbnailAttribute(): string
    {
        return asset('assets/excel/default-thumbnail.png');
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    public function clearCache(): void
    {
        Cache::forget('workbook_' . $this->id);
        Cache::forget('workbooks_list');
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($workbook) {
            $workbook->clearCache();
        });

        static::deleted(function ($workbook) {
            $workbook->clearCache();
        });
    }
}
