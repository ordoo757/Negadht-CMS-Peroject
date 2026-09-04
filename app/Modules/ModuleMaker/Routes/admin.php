<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ModuleMaker\Controllers\Admin\ModuleMakerController;

Route::get('/', [ModuleMakerController::class, 'index'])->name('admin.module-maker.index');
Route::post('/generate-module', [ModuleMakerController::class, 'generateModule'])->name('admin.module-maker.generate');
Route::post('/generate-component', [ModuleMakerController::class, 'generateComponent'])->name('admin.component-maker.generate');
Route::post('/generate-plugin', [ModuleMakerController::class, 'generatePlugin'])->name('admin.plugin-maker.generate');
