<?php

use App\Modules\SiteManager\Controllers\Admin\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['web', 'auth', 'admin'])
    ->name('admin.site-manager.')
    ->group(function () {
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });
