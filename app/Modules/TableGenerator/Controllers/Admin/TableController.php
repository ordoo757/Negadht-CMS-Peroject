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

namespace App\Modules\TableGenerator\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TableController extends Controller
{
    protected $tableService;

    public function __construct()
    {
        $this->tableService = app('table.service');
    }

    public function index()
    {
        return view('TableGenerator::admin.index');
    }

    public function create()
    {
        return view('TableGenerator::admin.create');
    }

    public function store(Request $request)
    {
        $id = $this->tableService->createTable($request->all());
        return redirect()->route('admin.table.index')->with('status', 'Table created');
    }
}
