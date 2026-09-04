<?php

use App\Modules\Antivirus\Controllers\Admin\ScanController;
use App\Modules\Antivirus\Controllers\Admin\ReportController;
use App\Modules\Antivirus\Controllers\Admin\QuarantineController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['web', 'auth', 'admin'])
    ->name('admin.antivirus.')
    ->group(function () {

        // =========================================================
        // ===== Dashboard =====
        // =========================================================
        Route::get('/antivirus', [ScanController::class, 'index'])->name('index');

        // =========================================================
        // ===== Scan =====
        // =========================================================
        Route::get('/antivirus/scan', [ScanController::class, 'scan'])->name('scan');
        Route::post('/antivirus/scan', [ScanController::class, 'startScan'])->name('start-scan');
        Route::post('/antivirus/scan/upload', [ScanController::class, 'scanUpload'])->name('scan-upload');
        Route::get('/antivirus/scan/{id}/status', [ScanController::class, 'status'])->name('scan-status');
        Route::post('/antivirus/scan/{id}/cancel', [ScanController::class, 'cancelScan'])->name('cancel-scan');
        Route::delete('/antivirus/scan/{id}', [ScanController::class, 'deleteReport'])->name('delete-report');

        // =========================================================
        // ===== Reports =====
        // =========================================================
        Route::get('/antivirus/reports', [ReportController::class, 'index'])->name('reports');
        Route::get('/antivirus/reports/{id}', [ReportController::class, 'show'])->name('report-show');
        Route::get('/antivirus/reports/{id}/export', [ReportController::class, 'export'])->name('report-export');

        // =========================================================
        // ===== Quarantine =====
        // =========================================================
        Route::get('/antivirus/quarantine', [QuarantineController::class, 'index'])->name('quarantine');
        Route::post('/antivirus/quarantine/{id}/restore', [QuarantineController::class, 'restore'])->name('quarantine-restore');
        Route::delete('/antivirus/quarantine/{id}', [QuarantineController::class, 'destroy'])->name('quarantine-delete');
    });
