<?php

namespace App\Modules\SiteManager\Services;

use App\Modules\SiteManager\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SiteService
{
    /**
     * دریافت تمام تنظیمات سایت
     */
    public function getAllSettings(): array
    {
        return SiteSetting::getAll();
    }

    /**
     * دریافت یک تنظیم خاص
     */
    public function getSetting(string $key, $default = null)
    {
        return SiteSetting::get($key, $default);
    }

    /**
     * بروزرسانی تنظیمات
     */
    public function updateSettings(array $data): bool
    {
        try {
            foreach ($data as $key => $value) {
                SiteSetting::set($key, $value);
            }
            
            Cache::forget('site_settings_all');
            Log::info('Site settings updated successfully.');
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update site settings: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * دریافت وضعیت سایت
     */
    public function getSiteStatus(): array
    {
        return [
            'status' => SiteSetting::get('site_status', 'active'),
            'maintenance_mode' => SiteSetting::get('maintenance_mode', false),
            'maintenance_message' => SiteSetting::get('maintenance_message', ''),
        ];
    }

    /**
     * تغییر وضعیت سایت
     */
    public function setSiteStatus(string $status): bool
    {
        return SiteSetting::set('site_status', $status);
    }

    /**
     * تغییر حالت نگهداری
     */
    public function setMaintenanceMode(bool $mode, string $message = ''): bool
    {
        SiteSetting::set('maintenance_mode', $mode);
        if ($message) {
            SiteSetting::set('maintenance_message', $message);
        }
        return true;
    }
}
