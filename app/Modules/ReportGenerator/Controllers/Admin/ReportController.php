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

namespace App\Modules\ReportGenerator\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct()
    {
        $this->reportService = app('report.service');
    }

    public function index()
    {
        return view('ReportGenerator::admin.index');
    }

    public function create()
    {
        return view('ReportGenerator::admin.create');
    }

    public function store(Request $request)
    {
        $id = $this->reportService->createReport($request->all());
        return redirect()->route('admin.report.index')->with('status', 'Report created');
    }

    public function system()
    {
        return view('ReportGenerator::admin.system', [
            'reports' => $this->reportService->getSystemReports(),
        ]);
    }
}
