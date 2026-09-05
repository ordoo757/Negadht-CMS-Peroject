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
use App\Modules\LanguageManager\Controllers\Admin\LanguageController;

Route::get('/', [LanguageController::class, 'index'])->name('admin.language.index');
Route::get('/create', [LanguageController::class, 'create'])->name('admin.language.create');
Route::post('/store', [LanguageController::class, 'store'])->name('admin.language.store');
Route::get('/translations', [LanguageController::class, 'translations'])->name('admin.language.translations');
