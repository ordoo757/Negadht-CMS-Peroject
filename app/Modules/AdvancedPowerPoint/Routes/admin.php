<?php

use App\Modules\AdvancedPowerPoint\Controllers\Admin\PowerPointController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['web', 'auth', 'admin'])
    ->name('admin.advanced-powerpoint.')
    ->group(function () {

        // ===== مدیریت ارائه‌ها =====
        Route::get('/powerpoint', [PowerPointController::class, 'index'])->name('index');
        Route::get('/powerpoint/create', [PowerPointController::class, 'create'])->name('create');
        Route::post('/powerpoint', [PowerPointController::class, 'store'])->name('store');
        Route::get('/powerpoint/{id}/edit', [PowerPointController::class, 'edit'])->name('edit');
        Route::put('/powerpoint/{id}', [PowerPointController::class, 'update'])->name('update');
        Route::delete('/powerpoint/{id}', [PowerPointController::class, 'destroy'])->name('destroy');

        // ===== مدیریت اسلایدها (AJAX) =====
        Route::post('/powerpoint/{presentationId}/slides', [PowerPointController::class, 'createSlide'])->name('create-slide');
        Route::put('/powerpoint/slides/{slideId}', [PowerPointController::class, 'updateSlide'])->name('update-slide');
        Route::delete('/powerpoint/slides/{slideId}', [PowerPointController::class, 'deleteSlide'])->name('delete-slide');

        // ===== مدیریت عناصر (AJAX) =====
        Route::post('/powerpoint/slides/{slideId}/elements', [PowerPointController::class, 'addElement'])->name('add-element');
        Route::put('/powerpoint/elements/{elementId}', [PowerPointController::class, 'updateElement'])->name('update-element');
        Route::delete('/powerpoint/elements/{elementId}', [PowerPointController::class, 'deleteElement'])->name('delete-element');

        // ===== دسترسی‌ها =====
        Route::get('/powerpoint/{id}/permissions', [PowerPointController::class, 'permissions'])->name('permissions');
        Route::post('/powerpoint/{id}/permissions', [PowerPointController::class, 'updatePermissions'])->name('update-permissions');

        // ===== جاسازی =====
        Route::get('/powerpoint/{id}/embed', [PowerPointController::class, 'embed'])->name('embed');
    });

// مسیر عمومی برای جاسازی
Route::get('/powerpoint/embed/{id}', [PowerPointController::class, 'embed'])->name('powerpoint.embed');
