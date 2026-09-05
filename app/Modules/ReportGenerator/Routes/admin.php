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
use App\Modules\ReportGenerator\Controllers\Admin\ReportController;

Route::get('/', [ReportController::class, 'index'])->name('admin.report.index');
Route::get('/create', [ReportController::class, 'create'])->name('admin.report.create');
Route::post('/store', [ReportController::class, 'store'])->name('admin.report.store');
Route::get('/system', [ReportController::class, 'system'])->name('admin.report.system');
