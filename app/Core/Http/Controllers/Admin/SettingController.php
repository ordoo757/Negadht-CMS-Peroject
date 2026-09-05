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

namespace App\Core\Http\Controllers\Admin;

use App\Core\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SettingController extends AdminController
{
    /**
     * نمایش صفحه تنظیمات
     */
    public function index()
    {
        $settings = [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'app_url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
        ];

        return view('core::admin.settings.index', compact('settings'));
    }

    /**
     * بروزرسانی تنظیمات
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_env' => 'required|string|in:local,production,staging',
            'app_debug' => 'nullable|boolean',
            'app_url' => 'required|url',
            'timezone' => 'required|string|timezone',
            'locale' => 'required|string|in:fa,en,ar',
        ]);

        try {
            // بروزرسانی فایل .env (در محیط واقعی باید با احتیاط انجام شود)
            $this->updateEnvFile($validated);

            // بروزرسانی کش
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            return redirect()->back()->with('success', 'تنظیمات با موفقیت بروزرسانی شد.');
        } catch (\Exception $e) {
            Log::error('Failed to update settings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'بروزرسانی تنظیمات ناموفق بود: ' . $e->getMessage());
        }
    }

    /**
     * پاک کردن کش
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            
            Cache::flush();

            return redirect()->back()->with('success', 'کش با موفقیت پاک شد.');
        } catch (\Exception $e) {
            Log::error('Failed to clear cache: ' . $e->getMessage());
            return redirect()->back()->with('error', 'پاک کردن کش ناموفق بود: ' . $e->getMessage());
        }
    }

    /**
     * نمایش لاگ‌ها
     */
    public function logs()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!File::exists($logFile)) {
            return view('core::admin.settings.logs', ['logs' => 'هیچ لاگی یافت نشد.']);
        }

        $logs = File::get($logFile);
        $logs = $this->parseLogs($logs);

        return view('core::admin.settings.logs', compact('logs'));
    }

    /**
     * بروزرسانی فایل .env
     */
    protected function updateEnvFile(array $data): void
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            throw new \Exception('فایل .env یافت نشد.');
        }

        $content = File::get($envPath);
        
        $replacements = [
            'APP_NAME' => $data['app_name'],
            'APP_ENV' => $data['app_env'],
            'APP_DEBUG' => $data['app_debug'] ? 'true' : 'false',
            'APP_URL' => $data['app_url'],
            'APP_TIMEZONE' => $data['timezone'],
            'APP_LOCALE' => $data['locale'],
        ];

        foreach ($replacements as $key => $value) {
            $content = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                $content
            );
        }

        File::put($envPath, $content);
    }

    /**
     * پارس کردن لاگ‌ها
     */
    protected function parseLogs(string $logContent): array
    {
        $lines = explode("\n", $logContent);
        $logs = [];
        $currentLog = [];

        foreach ($lines as $line) {
            if (preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $line)) {
                if (!empty($currentLog)) {
                    $logs[] = $currentLog;
                }
                $currentLog = ['message' => $line];
            } else {
                $currentLog['message'] .= "\n" . $line;
            }
        }

        if (!empty($currentLog)) {
            $logs[] = $currentLog;
        }

        return array_slice($logs, -50);
    }
}
