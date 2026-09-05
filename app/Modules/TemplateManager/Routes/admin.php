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
use App\Modules\TemplateManager\Controllers\Admin\TemplateController;

Route::get('/', [TemplateController::class, 'index'])->name('admin.template.index');
Route::get('/create', [TemplateController::class, 'create'])->name('admin.template.create');
Route::post('/store', [TemplateController::class, 'store'])->name('admin.template.store');
Route::get('/ai-builder', [TemplateController::class, 'aiBuilder'])->name('admin.template.ai-builder');
Route::get('/positions', [TemplateController::class, 'positions'])->name('admin.template.positions');
Route::get('/settings', [TemplateController::class, 'settings'])->name('admin.template.settings');
