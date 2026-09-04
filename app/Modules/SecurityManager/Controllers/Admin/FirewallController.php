<?php

namespace App\Modules\SecurityManager\Controllers\Admin;

use App\Core\Controllers\AdminController;
use App\Modules\SecurityManager\Services\SecurityService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FirewallController extends AdminController
{
    protected SecurityService $service;

    public function __construct(SecurityService $service)
    {
        $this->service = $service;
    }

    /**
     * نمایش صفحه فایروال
     */
    public function index()
    {
        $blockedIps = $this->service->getBlockedIps();
        $whitelistIps = $this->service->getWhitelistIps();
        $settings = $this->service->getAllSettings();

        return view('security-manager::admin.firewall', compact('blockedIps', 'whitelistIps', 'settings'));
    }

    /**
     * مسدود کردن آی‌پی
     */
    public function blockIp(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string|max:500',
            'minutes' => 'required|integer|min:1|max:1440',
            'is_permanent' => 'nullable|boolean',
        ]);

        $result = $this->service->blockIp(
            $validated['ip_address'],
            $validated['reason'] ?? '',
            $validated['minutes'],
            $validated['is_permanent'] ?? false
        );

        if ($result) {
            return redirect()->back()->with('success', "آی‌پی {$validated['ip_address']} با موفقیت مسدود شد.");
        }

        return redirect()->back()->with('error', 'مسدود کردن آی‌پی ناموفق بود.');
    }

    /**
     * رفع مسدودیت آی‌پی
     */
    public function unblockIp(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip',
        ]);

        $result = $this->service->unblockIp($validated['ip_address']);

        if ($result) {
            return redirect()->back()->with('success', "آی‌پی {$validated['ip_address']} با موفقیت رفع مسدودیت شد.");
        }

        return redirect()->back()->with('error', 'رفع مسدودیت آی‌پی ناموفق بود.');
    }
}
