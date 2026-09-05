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

use App\Modules\ComponentMaker\Controllers\Admin\ComponentController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['web', 'auth', 'admin'])
    ->name('admin.component-maker.')
    ->group(function () {
        Route::get('/components', [ComponentController::class, 'index'])
            ->name('index');

        Route::get('/components/create', [ComponentController::class, 'create'])
            ->name('create');

        Route::post('/components', [ComponentController::class, 'store'])
            ->name('store');

        Route::get('/components/{slug}', [ComponentController::class, 'show'])
            ->name('show');

        Route::get('/components/{slug}/edit', [ComponentController::class, 'edit'])
            ->name('edit');

        Route::put('/components/{slug}', [ComponentController::class, 'update'])
            ->name('update');

        Route::delete('/components/{slug}', [ComponentController::class, 'destroy'])
            ->name('destroy');

        Route::delete('/components/{slug}/force', [ComponentController::class, 'forceDestroy'])
            ->name('force-destroy');

        Route::post('/components/{slug}/restore', [ComponentController::class, 'restore'])
            ->name('restore');

        Route::post('/components/{slug}/install', [ComponentController::class, 'install'])
            ->name('install');

        Route::post('/components/{slug}/uninstall', [ComponentController::class, 'uninstall'])
            ->name('uninstall');

        Route::get('/components/{slug}/export', [ComponentController::class, 'export'])
            ->name('export');
    });
