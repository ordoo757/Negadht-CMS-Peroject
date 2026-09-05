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

class QuarantineController extends AdminController
{
    protected AntivirusService $service;

    public function __construct(AntivirusService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'severity', 'is_restored']);
        $quarantineFiles = $this->service->getQuarantineFiles($filters);
        $stats = $this->service->getStats();

        return view('antivirus::admin.quarantine', compact('quarantineFiles', 'stats', 'filters'));
    }

    public function restore(int $id)
    {
        try {
            $this->service->restoreQuarantineFile($id);

            return redirect()->route('admin.antivirus.quarantine')
                ->with('success', 'فایل با موفقیت بازیابی شد.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطا در بازیابی فایل: ' . $e->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->service->deleteQuarantineFile($id);

            return redirect()->route('admin.antivirus.quarantine')
                ->with('success', 'فایل با موفقیت حذف شد.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطا در حذف فایل: ' . $e->getMessage());
        }
    }
}
