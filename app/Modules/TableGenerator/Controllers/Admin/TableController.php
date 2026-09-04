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
