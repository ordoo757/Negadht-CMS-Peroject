<?php

use Illuminate\Support\Facades\Route;
use App\Modules\AiKernel\Controllers\Admin\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('admin.ai.dashboard');
Route::get('/security', [DashboardController::class, 'security'])->name('admin.ai.security');
Route::get('/learning', [DashboardController::class, 'learning'])->name('admin.ai.learning');
Route::get('/settings', [DashboardController::class, 'settings'])->name('admin.ai.settings');
