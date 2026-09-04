<?php

use Illuminate\Support\Facades\Route;

// ============================================
// Installer routes
// ============================================
if (!file_exists(base_path('.installed'))) {
    Route::get('/install', [\App\Installer\Controllers\InstallController::class, 'index'])
        ->name('installer.welcome');
    Route::get('/install/requirements', [\App\Installer\Controllers\InstallController::class, 'requirements'])
        ->name('installer.requirements');
    Route::get('/install/database', [\App\Installer\Controllers\InstallController::class, 'database'])
        ->name('installer.database');
    Route::post('/install/database', [\App\Installer\Controllers\InstallController::class, 'saveDatabase']);
    Route::get('/install/account', [\App\Installer\Controllers\InstallController::class, 'account'])
        ->name('installer.account');
    Route::post('/install/finish', [\App\Installer\Controllers\InstallController::class, 'finish'])
        ->name('installer.finish');
}

// ============================================
// Language switcher
// ============================================
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['fa', 'ar', 'en'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return back();
})->name('lang.switch');

// ============================================
// Home
// ============================================
Route::get('/', function () {
    return view('site.default.index');
})->name('home');

// ============================================
// Auth routes
// ============================================
Route::middleware(['web'])->group(function () {

    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (\Illuminate\Http\Request $request) {
        $credentials = $request->only('email', 'password');
        if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
            return redirect()->intended('/admin');
        }
        return back()->withErrors(['email' => 'Invalid credentials']);
    })->name('login.post');

    Route::post('/logout', function () {
        \Illuminate\Support\Facades\Auth::logout();
        return redirect('/');
    })->name('logout');
});

// ============================================
// Admin Panel Routes
// ============================================
Route::middleware(['web', 'auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // Dashboard
    Route::get('/', function () {
        $kernel = app('neuro.kernel');
        return view('admin.default.dashboard', [
            'status' => $kernel->monitor(),
            'totalUsers' => \Illuminate\Support\Facades\DB::table('users')->count(),
        ]);
    })->name('dashboard');

    // System Settings
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/settings', function () {
            return view('admin.default.system.settings', [
                'settings' => cache('site_settings', []),
            ]);
        })->name('settings');

        Route::post('/settings', function (\Illuminate\Http\Request $request) {
            cache()->forever('site_settings', $request->except('_token'));
            return back()->with('status', 'تنظیمات با موفقیت ذخیره شد');
        })->name('settings.save');

        Route::get('/maintenance', function () {
            return view('admin.default.system.maintenance');
        })->name('maintenance');

        Route::get('/backup', function () {
            return view('admin.default.system.backup');
        })->name('backup');

        Route::get('/logs', function () {
            return view('admin.default.system.logs');
        })->name('logs');
    });

    // Security
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/', function () {
            return view('admin.default.security.index', [
                'failedLogins' => \Illuminate\Support\Facades\DB::table('activity_logs')
                    ->where('action', 'login_failed')
                    ->where('created_at', '>=', now()->subDay())
                    ->count(),
                'blockedIps' => \Illuminate\Support\Facades\DB::table('blocked_ips')
                    ->where('blocked_until', '>', now())
                    ->count(),
                'activeSessions' => \Illuminate\Support\Facades\DB::table('sessions')->count(),
            ]);
        })->name('index');

        Route::post('/settings', function () {
            return back()->with('status', 'تنظیمات امنیتی ذخیره شد');
        })->name('settings');

        Route::post('/block', function () {
            return back()->with('status', 'IP مسدود شد');
        })->name('block');

        Route::delete('/unblock/{id}', function ($id) {
            return back()->with('status', 'مسدودیت برداشته شد');
        })->name('unblock');

        Route::get('/firewall', function () {
            return view('admin.default.security.firewall');
        })->name('firewall');

        Route::get('/ips', function () {
            return view('admin.default.security.ips');
        })->name('ips');

        Route::get('/sessions', function () {
            return view('admin.default.security.sessions');
        })->name('sessions');
    });

    // AI Assistant
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/assistant', function () {
            return view('admin.default.ai.assistant');
        })->name('assistant');

        Route::get('/analytics', function () {
            return view('admin.default.ai.analytics');
        })->name('analytics');

        Route::get('/config', function () {
            return view('admin.default.ai.config');
        })->name('config');
    });

    // Users Management
    Route::prefix('users')->name('user.')->group(function () {
        Route::get('/', function () {
            return view('admin.default.users.index', [
                'users' => \Illuminate\Support\Facades\DB::table('users')->paginate(20),
            ]);
        })->name('index');

        Route::post('/store', function (\Illuminate\Http\Request $request) {
            return redirect()->route('admin.user.index')->with('status', 'کاربر ایجاد شد');
        })->name('store');

        Route::delete('/{id}', function ($id) {
            return back()->with('status', 'کاربر حذف شد');
        })->name('destroy');

        Route::get('/roles', function () {
            return view('admin.default.users.roles', [
                'roles' => \Illuminate\Support\Facades\DB::table('roles')->get(),
            ]);
        })->name('roles');

        Route::post('/roles', function () {
            return back()->with('status', 'نقش ایجاد شد');
        })->name('roles.store');

        Route::get('/permissions', function () {
            return view('admin.default.users.permissions');
        })->name('permissions');

        Route::get('/profile', function () {
            return view('admin.default.users.profile');
        })->name('profile');

        Route::get('/settings', function () {
            return view('admin.default.users.settings');
        })->name('settings');
    });

    // Template Manager
    Route::prefix('templates')->name('template.')->group(function () {
        Route::get('/', function () {
            return view('admin.default.template.index');
        })->name('index');

        Route::get('/create', function () {
            return view('admin.default.template.create');
        })->name('create');

        Route::post('/store', function () {
            return back()->with('status', 'قالب ذخیره شد');
        })->name('store');

        Route::get('/{id}/edit', function ($id) {
            return view('admin.default.template.edit');
        })->name('edit');

        Route::put('/{id}', function ($id) {
            return back()->with('status', 'قالب بروزرسانی شد');
        })->name('update');

        Route::delete('/{id}', function ($id) {
            return back()->with('status', 'قالب حذف شد');
        })->name('destroy');

        Route::post('/{id}/activate', function ($id) {
            return back()->with('status', 'قالب فعال شد');
        })->name('activate');

        Route::post('/{id}/deactivate', function ($id) {
            return back()->with('status', 'قالب غیرفعال شد');
        })->name('deactivate');

        Route::post('/{id}/export', function ($id) {
            return back()->with('status', 'قالب export شد');
        })->name('export');
    });

    // Language Manager
    Route::prefix('languages')->name('language.')->group(function () {
        Route::get('/', function () {
            return view('admin.default.language.index');
        })->name('index');

        Route::get('/create', function () {
            return view('admin.default.language.create');
        })->name('create');

        Route::post('/store', function () {
            return back()->with('status', 'زبان اضافه شد');
        })->name('store');

        Route::get('/translations', function () {
            return view('admin.default.language.translations');
        })->name('translations');
    });

    // Component Maker
    Route::prefix('components')->name('component-maker.')->group(function () {
        Route::get('/', function () {
            return view('admin.default.component.index');
        })->name('index');
    });

    // Form Creator
    Route::prefix('forms')->name('form-creator.')->group(function () {
        Route::get('/', function () {
            return view('admin.default.form.index');
        })->name('index');
    });

    // Table Generator
    Route::prefix('tables')->name('table-generator.')->group(function () {
        Route::get('/', function () {
            return view('admin.default.table.index');
        })->name('index');
    });

    // Menu Maker
    Route::prefix('menus')->name('menu-maker.')->group(function () {
        Route::get('/', function () {
            return view('admin.default.menu.index');
        })->name('index');
    });

    // Report Generator
    Route::prefix('reports')->name('report-generator.')->group(function () {
        Route::get('/', function () {
            return view('admin.default.report.index');
        })->name('index');
    });

    // Module Maker
    Route::prefix('module-maker')->name('module-maker.')->group(function () {
        Route::get('/', function () {
            return view('admin.default.module-maker.index');
        })->name('index');
    });

    // Plugin Maker
    Route::prefix('plugin-maker')->name('plugin-maker.')->group(function () {
        Route::get('/', function () {
            return view('admin.default.plugin-maker.index');
        })->name('index');
    });

    // Modules Management
    Route::get('/modules', function () {
        $registry = app('module.registry');
        return view('admin.modules.index', [
            'modules' => $registry->getAllModules(),
            'components' => $registry->getAllComponents(),
            'plugins' => $registry->getAllPlugins(),
        ]);
    })->name('modules.index');

    Route::post('/modules/{slug}/install', function ($slug) {
        $result = app('module.registry')->install($slug);
        return back()->with('status', $result ? 'ماژول نصب شد' : 'خطا');
    })->name('modules.install');

    Route::post('/modules/{slug}/uninstall', function ($slug) {
        $result = app('module.registry')->uninstall($slug);
        return back()->with('status', $result ? 'ماژول حذف شد' : 'خطا');
    })->name('modules.uninstall');

    Route::post('/modules/{slug}/activate', function ($slug) {
        app('module.registry')->activate($slug);
        return back()->with('status', 'ماژول فعال شد');
    })->name('modules.activate');

    Route::post('/modules/{slug}/deactivate', function ($slug) {
        app('module.registry')->deactivate($slug);
        return back()->with('status', 'ماژول غیرفعال شد');
    })->name('modules.deactivate');

    Route::post('/modules/{slug}/export', function ($slug) {
        $path = app('module.registry')->exportToZip($slug);
        return $path ? response()->download($path) : back()->withErrors(['export' => 'خطا']);
    })->name('modules.export');

    // Module Routes (auto-loaded from modules)
    // These will be loaded by Module classes
});
