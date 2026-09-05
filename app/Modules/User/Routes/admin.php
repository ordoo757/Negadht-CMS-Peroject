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

use Illuminate\Support\Facades\Route;
use App\Modules\User\Controllers\Admin\UserController;

Route::get('/', [UserController::class, 'index'])->name('admin.user.index');
Route::get('/create', [UserController::class, 'create'])->name('admin.user.create');
Route::post('/store', [UserController::class, 'store'])->name('admin.user.store');
Route::get('/roles', [UserController::class, 'roles'])->name('admin.user.roles');
Route::get('/permissions', [UserController::class, 'permissions'])->name('admin.user.permissions');
