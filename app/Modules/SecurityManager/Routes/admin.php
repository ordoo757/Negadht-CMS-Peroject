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

use App\Modules\SecurityManager\Controllers\Admin\SecurityController;
use App\Modules\SecurityManager\Controllers\Admin\FirewallController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['web', 'auth', 'admin'])
    ->name('admin.security-manager.')
    ->group(function () {
        // داشبورد امنیت
        Route::get('/security', [SecurityController::class, 'index'])->name('index');

        // لاگ‌های امنیتی
        Route::get('/security/logs', [SecurityController::class, 'logs'])->name('logs');
        Route::post('/security/logs/{id}/resolve', [SecurityController::class, 'resolveLog'])->name('logs.resolve');

        // تنظیمات امنیتی
        Route::get('/security/settings', [SecurityController::class, 'settings'])->name('settings');
        Route::post('/security/settings', [SecurityController::class, 'updateSettings'])->name('settings.update');

        // فایروال
        Route::get('/security/firewall', [FirewallController::class, 'index'])->name('firewall');
        Route::post('/security/firewall/block', [FirewallController::class, 'blockIp'])->name('firewall.block');
        Route::post('/security/firewall/unblock', [FirewallController::class, 'unblockIp'])->name('firewall.unblock');
    });
