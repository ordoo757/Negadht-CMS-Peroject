<?php

use Illuminate\Support\Facades\Route;
use App\Modules\FormCreator\Controllers\Admin\FormController;

Route::get('/', [FormController::class, 'index'])->name('admin.form.index');
Route::get('/create', [FormController::class, 'create'])->name('admin.form.create');
Route::post('/store', [FormController::class, 'store'])->name('admin.form.store');
Route::get('/ai-builder', [FormController::class, 'aiBuilder'])->name('admin.form.ai-builder');
Route::get('/responses', [FormController::class, 'responses'])->name('admin.form.responses');
