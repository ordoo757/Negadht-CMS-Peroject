<?php

namespace App\Modules\SecurityManager\Controllers\Admin;

use App\Core\Controllers\AdminController;
use App\Modules\SecurityManager\Services\SecurityService;
use Illuminate\Http\Request;

class SecurityController extends AdminController
{
    protected SecurityService $service;

    public function __construct(SecurityService $service)
    {
        $this->service = $service;
    }

    /**
     * نمایش داشبورد امنیت
     */
    public function index()
    {
        $stats = $this->service->getStats();
        $recentLogs = $this->service->getLogs([], 10);
        $blockedIps = $this->service->getBlockedIps();
        $settings = $this->service->getAllSettings();

        return view('security-manager::admin.index', compact('stats', 'recentLogs', 'blockedIps', 'settings'));
    }

    /**
     * نمایش لاگ‌های امنیتی
     */
    public function logs(Request $request)
    {
        $filters = $request->only(['event', 'type', 'risk_level', 'is_resolved', 'date_from', 'date_to']);
        $logs = $this->service->getLogs($filters);

        return view('security-manager::admin.logs', compact('logs', 'filters'));
    }

    /**
     * نمایش تنظیمات امنیتی
     */
    public function settings()
    {
        $settings = $this->service->getAllSettings();
        return view('security-manager::admin.settings', compact('settings'));
    }

    /**
     * بروزرسانی تنظیمات امنیتی
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'max_login_attempts' => 'required|integer|min:1|max:100',
            'block_duration_minutes' => 'required|integer|min:1|max:1440',
            'enable_2fa' => 'nullable|boolean',
            'enable_captcha' => 'nullable|boolean',
            'enable_firewall' => 'nullable|boolean',
            'enable_ip_blacklist' => 'nullable|boolean',
            'enable_ip_whitelist' => 'nullable|boolean',
            'enable_ssl' => 'nullable|boolean',
            'security_level' => 'required|in:low,medium,high',
            'session_timeout' => 'required|integer|min:5|max:1440',
            'password_min_length' => 'required|integer|min:6|max:50',
            'enable_activity_log' => 'nullable|boolean',
            'log_retention_days' => 'required|integer|min:1|max:365',
            'enable_email_notifications' => 'nullable|boolean',
            'admin_email' => 'required|email|max:255',
        ]);

        $result = $this->service->updateSettings($validated);

        if ($result) {
            return redirect()->route('admin.security-manager.settings')
                ->with('success', 'تنظیمات امنیتی با موفقیت بروزرسانی شد.');
        }

        return redirect()->back()->with('error', 'بروزرسانی تنظیمات امنیتی ناموفق بود.');
    }

    /**
     * حل کردن یک لاگ امنیتی
     */
    public function resolveLog(int $id)
    {
        $log = \App\Modules\SecurityManager\Models\SecurityLog::findOrFail($id);
        $log->resolve();

        return redirect()->back()->with('success', 'لاگ با موفقیت حل شد.');
    }
}
