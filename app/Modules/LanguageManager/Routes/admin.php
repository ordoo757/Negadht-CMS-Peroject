<?php

use Illuminate\Support\Facades\Route;
use App\Modules\LanguageManager\Controllers\Admin\LanguageController;

Route::get('/', [LanguageController::class, 'index'])->name('admin.language.index');
Route::get('/create', [LanguageController::class, 'create'])->name('admin.language.create');
Route::post('/store', [LanguageController::class, 'store'])->name('admin.language.store');
Route::get('/translations', [LanguageController::class, 'translations'])->name('admin.language.translations');
