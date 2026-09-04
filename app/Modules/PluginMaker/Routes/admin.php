<?php

use App\Modules\PluginMaker\Controllers\Admin\PluginController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['web', 'auth', 'admin'])
    ->name('admin.plugin-maker.')
    ->group(function () {
        Route::get('/plugins', [PluginController::class, 'index'])->name('index');
        Route::get('/plugins/create', [PluginController::class, 'create'])->name('create');
        Route::post('/plugins', [PluginController::class, 'store'])->name('store');
        Route::get('/plugins/{slug}', [PluginController::class, 'show'])->name('show');
        Route::get('/plugins/{slug}/edit', [PluginController::class, 'edit'])->name('edit');
        Route::put('/plugins/{slug}', [PluginController::class, 'update'])->name('update');
        Route::delete('/plugins/{slug}', [PluginController::class, 'destroy'])->name('destroy');
        Route::delete('/plugins/{slug}/force', [PluginController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/plugins/{slug}/restore', [PluginController::class, 'restore'])->name('restore');
        Route::post('/plugins/{slug}/install', [PluginController::class, 'install'])->name('install');
        Route::post('/plugins/{slug}/uninstall', [PluginController::class, 'uninstall'])->name('uninstall');
        Route::post('/plugins/{slug}/activate', [PluginController::class, 'activate'])->name('activate');
        Route::post('/plugins/{slug}/deactivate', [PluginController::class, 'deactivate'])->name('deactivate');
    });
