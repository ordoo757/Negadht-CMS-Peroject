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
use App\Modules\ModuleMaker\Controllers\Admin\ModuleMakerController;

Route::get('/', [ModuleMakerController::class, 'index'])->name('admin.module-maker.index');
Route::post('/generate-module', [ModuleMakerController::class, 'generateModule'])->name('admin.module-maker.generate');
Route::post('/generate-component', [ModuleMakerController::class, 'generateComponent'])->name('admin.component-maker.generate');
Route::post('/generate-plugin', [ModuleMakerController::class, 'generatePlugin'])->name('admin.plugin-maker.generate');
