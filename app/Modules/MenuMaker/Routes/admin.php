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
use App\Modules\MenuMaker\Controllers\Admin\MenuController;

Route::get('/', [MenuController::class, 'index'])->name('admin.menu.index');
Route::get('/create', [MenuController::class, 'create'])->name('admin.menu.create');
Route::post('/store', [MenuController::class, 'store'])->name('admin.menu.store');
Route::get('/ai-builder', [MenuController::class, 'aiBuilder'])->name('admin.menu.ai-builder');
Route::get('/settings', [MenuController::class, 'settings'])->name('admin.menu.settings');
