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
use App\Modules\FormCreator\Controllers\Admin\FormController;

Route::get('/', [FormController::class, 'index'])->name('admin.form.index');
Route::get('/create', [FormController::class, 'create'])->name('admin.form.create');
Route::post('/store', [FormController::class, 'store'])->name('admin.form.store');
Route::get('/ai-builder', [FormController::class, 'aiBuilder'])->name('admin.form.ai-builder');
Route::get('/responses', [FormController::class, 'responses'])->name('admin.form.responses');
