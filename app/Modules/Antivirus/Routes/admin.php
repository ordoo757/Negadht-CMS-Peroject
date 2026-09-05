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

use App\Modules\Antivirus\Controllers\Admin\ScanController;
use App\Modules\Antivirus\Controllers\Admin\ReportController;
use App\Modules\Antivirus\Controllers\Admin\QuarantineController;
use App\Modules\Antivirus\Controllers\Admin\VirusDefinitionController;
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

        // =========================================================
        // ===== Virus Definitions =====
        // =========================================================
        Route::get('/antivirus/virus-definitions', [VirusDefinitionController::class, 'index'])->name('virus-definitions');
        Route::get('/antivirus/virus-definitions/create', [VirusDefinitionController::class, 'create'])->name('virus-definitions.create');
        Route::post('/antivirus/virus-definitions', [VirusDefinitionController::class, 'store'])->name('virus-definitions.store');
        Route::get('/antivirus/virus-definitions/{id}/edit', [VirusDefinitionController::class, 'edit'])->name('virus-definitions.edit');
        Route::put('/antivirus/virus-definitions/{id}', [VirusDefinitionController::class, 'update'])->name('virus-definitions.update');
        Route::delete('/antivirus/virus-definitions/{id}', [VirusDefinitionController::class, 'destroy'])->name('virus-definitions.destroy');
        Route::post('/antivirus/virus-definitions/{id}/toggle', [VirusDefinitionController::class, 'toggle'])->name('virus-definitions.toggle');

        // Import / Export
        Route::post('/antivirus/virus-definitions/import', [VirusDefinitionController::class, 'import'])->name('virus-definitions.import');
        Route::get('/antivirus/virus-definitions/export', [VirusDefinitionController::class, 'export'])->name('virus-definitions.export');
    });

        // ===== Update =====
        Route::post('/antivirus/update/yara', [UpdateController::class, 'updateFromYara'])->name('update-yara');
        Route::post('/antivirus/update/clamav', [UpdateController::class, 'updateFromClamav'])->name('update-clamav');
        Route::post('/antivirus/update/virustotal', [UpdateController::class, 'updateFromVirusTotal'])->name('update-virustotal');
        Route::get('/antivirus/update/status', [UpdateController::class, 'status'])->name('update-status');
