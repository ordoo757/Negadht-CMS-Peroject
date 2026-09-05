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

namespace App\Modules\Antivirus\Controllers\Admin;

use App\Core\Controllers\AdminController;
use App\Modules\Antivirus\Services\AntivirusService;
use Illuminate\Http\Request;

class UpdateController extends AdminController
{
    protected AntivirusService $service;

    public function __construct(AntivirusService $service)
    {
        $this->service = $service;
    }

    public function updateFromYara()
    {
        $result = $this->service->updateFromYara();

        return redirect()->route('admin.antivirus.index')
            ->with('success', $result['message'] ?? 'بروزرسانی انجام شد.')
            ->withErrors($result['errors'] ?? []);
    }

    public function updateFromClamav()
    {
        $result = $this->service->updateFromClamav();

        return redirect()->route('admin.antivirus.index')
            ->with('success', $result['message'] ?? 'بروزرسانی انجام شد.')
            ->withErrors($result['errors'] ?? []);
    }

    public function updateFromVirusTotal(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string|min:32',
        ]);

        $result = $this->service->updateFromVirusTotal($request->api_key);

        return redirect()->route('admin.antivirus.index')
            ->with('success', $result['message'] ?? 'بروزرسانی انجام شد.')
            ->withErrors($result['errors'] ?? []);
    }

    public function status()
    {
        $status = $this->service->getUpdateStatus();

        return response()->json($status);
    }
}
