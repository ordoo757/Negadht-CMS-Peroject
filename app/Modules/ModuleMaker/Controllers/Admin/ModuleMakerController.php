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

namespace App\Modules\ModuleMaker\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ModuleMakerController extends Controller
{
    protected $makerService;

    public function __construct()
    {
        $this->makerService = app('module.maker');
    }

    public function index()
    {
        return view('ModuleMaker::admin.index');
    }

    public function generateModule(Request $request)
    {
        $result = $this->makerService->generateModule($request->all());
        return back()->with($result['success'] ? 'status' : 'error', $result['message'] ?? '');
    }

    public function generateComponent(Request $request)
    {
        $result = $this->makerService->generateComponent($request->all());
        return back()->with($result['success'] ? 'status' : 'error', $result['message'] ?? '');
    }

    public function generatePlugin(Request $request)
    {
        $result = $this->makerService->generatePlugin($request->all());
        return back()->with($result['success'] ? 'status' : 'error', $result['message'] ?? '');
    }
}
