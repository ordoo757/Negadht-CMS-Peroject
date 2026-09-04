<?php

use App\Core\Http\Controllers\Admin\DashboardController;
use App\Core\Http\Controllers\Admin\ModuleController;
use App\Core\Http\Controllers\Admin\ComponentController;
use App\Core\Http\Controllers\Admin\PluginController;
use App\Core\Http\Controllers\Admin\UserController;
use App\Core\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['web', 'auth', 'admin'])
    ->name('admin.')
    ->group(function () {

        // =============== Dashboard ===============
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // =============== Modules Management ===============
        Route::prefix('modules')->name('modules.')->group(function () {
            Route::get('/', [ModuleController::class, 'index'])->name('index');
            Route::post('/{slug}/activate', [ModuleController::class, 'activate'])->name('activate');
            Route::post('/{slug}/deactivate', [ModuleController::class, 'deactivate'])->name('deactivate');
            Route::post('/{slug}/install', [ModuleController::class, 'install'])->name('install');
            Route::post('/{slug}/uninstall', [ModuleController::class, 'uninstall'])->name('uninstall');
            Route::get('/{slug}/status', [ModuleController::class, 'status'])->name('status');
        });

        // =============== Components Management ===============
        Route::prefix('components')->name('components.')->group(function () {
            Route::get('/', [ComponentController::class, 'index'])->name('index');
            Route::post('/{slug}/activate', [ComponentController::class, 'activate'])->name('activate');
            Route::post('/{slug}/deactivate', [ComponentController::class, 'deactivate'])->name('deactivate');
            Route::post('/{slug}/install', [ComponentController::class, 'install'])->name('install');
            Route::post('/{slug}/uninstall', [ComponentController::class, 'uninstall'])->name('uninstall');
        });

        // =============== Plugins Management ===============
        Route::prefix('plugins')->name('plugins.')->group(function () {
            Route::get('/', [PluginController::class, 'index'])->name('index');
            Route::post('/{slug}/activate', [PluginController::class, 'activate'])->name('activate');
            Route::post('/{slug}/deactivate', [PluginController::class, 'deactivate'])->name('deactivate');
            Route::post('/{slug}/install', [PluginController::class, 'install'])->name('install');
            Route::post('/{slug}/uninstall', [PluginController::class, 'uninstall'])->name('uninstall');
        });

        // =============== Users Management ===============
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{id}', [UserController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{id}', [UserController::class, 'update'])->name('update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
        });

        // =============== Settings ===============
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/', [SettingController::class, 'update'])->name('update');
            Route::get('/cache/clear', [SettingController::class, 'clearCache'])->name('cache.clear');
            Route::get('/logs', [SettingController::class, 'logs'])->name('logs');
        });

        // =============== System Info ===============
        Route::get('/system/info', [DashboardController::class, 'systemInfo'])->name('system.info');
    });

        // ===== Security Tests =====
        Route::get('/security-test', [App\Http\Controllers\Admin\SecurityTestController::class, 'index'])->name('security-test.index');
        Route::post('/security-test/run', [App\Http\Controllers\Admin\SecurityTestController::class, 'run'])->name('security-test.run');
        Route::get('/security-test/results', [App\Http\Controllers\Admin\SecurityTestController::class, 'results'])->name('security-test.results');
        Route::get('/security-test/export', [App\Http\Controllers\Admin\SecurityTestController::class, 'export'])->name('security-test.export');
