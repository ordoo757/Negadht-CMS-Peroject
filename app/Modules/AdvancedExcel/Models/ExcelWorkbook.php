<?php

namespace App\Modules\AdvancedExcel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ExcelWorkbook extends Model
{
    protected $table = 'excel_workbooks';

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'is_public',
        'user_id',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'settings' => 'json',
    ];

    /**
     * روابط
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function worksheets()
    {
        return $this->hasMany(ExcelWorksheet::class)->orderBy('order');
    }

    /**
     * متدهای کمکی
     */
    public function getShareUrlAttribute(): string
    {
        return route('excel.embed', $this->id);
    }

    public function getEditUrlAttribute(): string
    {
        return route('admin.advanced-excel.edit', $this->id);
    }

    public function isOwner(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    public function canAccess(int $userId): bool
    {
        return $this->is_public || $this->isOwner($userId);
    }

    /**
     * کش
     */
    public function clearCache(): void
    {
        Cache::forget('excel_workbook_' . $this->id);
        Cache::forget('excel_workbooks_list');
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
