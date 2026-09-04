<?php

namespace App\Modules\SiteManager\Controllers\Admin;

use App\Core\Controllers\AdminController;
use App\Modules\SiteManager\Services\SiteService;
use Illuminate\Http\Request;

class ProfileController extends AdminController
{
    protected SiteService $service;

    public function __construct(SiteService $service)
    {
        $this->service = $service;
    }

    /**
     * نمایش پروفایل سایت
     */
    public function index()
    {
        $settings = $this->service->getAllSettings();
        $status = $this->service->getSiteStatus();

        return view('site-manager::admin.profile', compact('settings', 'status'));
    }

    /**
     * بروزرسانی پروفایل سایت
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_slogan' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'site_keywords' => 'nullable|string|max:500',
            'site_email' => 'required|email|max:255',
            'site_phone' => 'nullable|string|max:50',
            'site_address' => 'nullable|string|max:500',
            'site_status' => 'required|in:active,inactive,maintenance',
            'maintenance_mode' => 'nullable|boolean',
            'maintenance_message' => 'nullable|string|max:500',
        ]);

        $result = $this->service->updateSettings($validated);

        if ($result) {
            return redirect()->route('admin.site-manager.profile')
                ->with('success', 'پروفایل سایت با موفقیت بروزرسانی شد.');
        }

        return redirect()->back()->with('error', 'بروزرسانی پروفایل سایت ناموفق بود.');
    }
}
