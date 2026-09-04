<?php

use App\Modules\ContentManager\Controllers\Admin\PageController;
use App\Modules\ContentManager\Controllers\Admin\CategoryController;
use App\Modules\ContentManager\Controllers\Admin\MediaController;
use App\Modules\ContentManager\Controllers\Admin\CommentController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['web', 'auth', 'admin'])
    ->name('admin.content-manager.')
    ->group(function () {

        // =========================================================
        // ===== Pages =====
        // =========================================================
        Route::prefix('pages')->name('pages.')->group(function () {
            Route::get('/', [PageController::class, 'index'])->name('index');
            Route::get('/create', [PageController::class, 'create'])->name('create');
            Route::post('/', [PageController::class, 'store'])->name('store');
            Route::get('/{id}', [PageController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [PageController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PageController::class, 'update'])->name('update');
            Route::delete('/{id}', [PageController::class, 'destroy'])->name('destroy');
            Route::delete('/{id}/force', [PageController::class, 'forceDestroy'])->name('force-destroy');
            Route::post('/{slug}/restore', [PageController::class, 'restore'])->name('restore');

            // AI Features
            Route::post('/generate-ai', [PageController::class, 'generateWithAI'])->name('generate-ai');
        });

        // =========================================================
        // ===== Categories =====
        // =========================================================
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::get('/create', [CategoryController::class, 'create'])->name('create');
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [CategoryController::class, 'edit'])->name('edit');
            Route::put('/{id}', [CategoryController::class, 'update'])->name('update');
            Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle', [CategoryController::class, 'toggle'])->name('toggle');
        });

        // =========================================================
        // ===== Media =====
        // =========================================================
        Route::prefix('media')->name('media.')->group(function () {
            Route::get('/', [MediaController::class, 'index'])->name('index');
            Route::post('/upload', [MediaController::class, 'upload'])->name('upload');
            Route::delete('/{id}', [MediaController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-destroy', [MediaController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::get('/info/{id}', [MediaController::class, 'info'])->name('info');
            Route::get('/search', [MediaController::class, 'search'])->name('search');
        });

        // =========================================================
        // ===== Comments =====
        // =========================================================
        Route::prefix('comments')->name('comments.')->group(function () {
            Route::get('/', [CommentController::class, 'index'])->name('index');
            Route::post('/{id}/approve', [CommentController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [CommentController::class, 'reject'])->name('reject');
            Route::delete('/{id}', [CommentController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-approve', [CommentController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/bulk-reject', [CommentController::class, 'bulkReject'])->name('bulk-reject');
            Route::post('/bulk-destroy', [CommentController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::get('/page/{pageId}', [CommentController::class, 'getPageComments'])->name('page-comments');
            Route::get('/stats', [CommentController::class, 'stats'])->name('stats');
        });
    });
