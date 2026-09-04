<?php

use App\Modules\AdvancedExcel\Controllers\Admin\ExcelController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['web', 'auth', 'admin'])
    ->name('admin.advanced-excel.')
    ->group(function () {

        // ===== Workbook =====
        Route::get('/excel', [ExcelController::class, 'index'])->name('index');
        Route::get('/excel/create', [ExcelController::class, 'create'])->name('create');
        Route::post('/excel', [ExcelController::class, 'store'])->name('store');
        Route::get('/excel/{id}', [ExcelController::class, 'show'])->name('show');
        Route::get('/excel/{id}/edit', [ExcelController::class, 'edit'])->name('edit');
        Route::put('/excel/{id}', [ExcelController::class, 'update'])->name('update');
        Route::delete('/excel/{id}', [ExcelController::class, 'destroy'])->name('destroy');

        // ===== Worksheet =====
        Route::post('/excel/{workbookId}/worksheets', [ExcelController::class, 'createWorksheet'])->name('create-worksheet');
        Route::delete('/excel/worksheets/{worksheetId}', [ExcelController::class, 'deleteWorksheet'])->name('delete-worksheet');

        // ===== Cell =====
        Route::post('/excel/worksheets/{worksheetId}/cell', [ExcelController::class, 'updateCell'])->name('update-cell');
        Route::get('/excel/worksheets/{worksheetId}/data', [ExcelController::class, 'getWorksheetData'])->name('worksheet-data');

        // ===== Chart =====
        Route::post('/excel/worksheets/{worksheetId}/charts', [ExcelController::class, 'createChart'])->name('create-chart');
        Route::delete('/excel/charts/{chartId}', [ExcelController::class, 'deleteChart'])->name('delete-chart');

        // ===== Embed =====
        Route::get('/excel/{id}/embed', [ExcelController::class, 'embed'])->name('embed');
    });

// Public route for embedding
Route::get('/excel/embed/{id}', [ExcelController::class, 'embed'])->name('excel.embed');
