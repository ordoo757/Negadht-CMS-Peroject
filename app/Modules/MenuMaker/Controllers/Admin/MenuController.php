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

namespace App\Modules\MenuMaker\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    protected $menuService;
    protected $aiBuilder;

    public function __construct()
    {
        $this->menuService = app('menu.service');
        $this->aiBuilder = app('menu.ai');
    }

    public function index()
    {
        return view('MenuMaker::admin.index', [
            'menus' => \Illuminate\Support\Facades\DB::table('menus')->get(),
        ]);
    }

    public function create()
    {
        return view('MenuMaker::admin.create', [
            'positions' => $this->menuService->getPositions(),
            'types' => $this->menuService->getMenuTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $id = $this->menuService->createMenu($request->all());
        return redirect()->route('admin.menu.index')->with('status', 'Menu created');
    }

    public function aiBuilder()
    {
        return view('MenuMaker::admin.ai-builder');
    }

    public function settings()
    {
        return view('MenuMaker::admin.settings');
    }
}
