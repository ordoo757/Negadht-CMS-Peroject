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

namespace App\Modules\User\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('User::admin.index');
    }

    public function create()
    {
        return view('User::admin.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.user.index')->with('status', 'User created');
    }

    public function roles()
    {
        return view('User::admin.roles');
    }

    public function permissions()
    {
        return view('User::admin.permissions');
    }
}
