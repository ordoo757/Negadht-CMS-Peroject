<?php

namespace App\Installer\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class InstallController
{
    protected array $requirements = [
        'php' => '8.3.0',
        'extensions' => ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json', 'tokenizer', 'xml', 'ctype', 'fileinfo', 'bcmath', 'zip'],
    ];

    public function index()
    {
        if (File::exists(base_path('.installed'))) {
            return redirect('/');
        }
        return view('installer::welcome');
    }

    public function requirements()
    {
        if (File::exists(base_path('.installed'))) {
            return redirect('/');
        }

        $checks = [
            'php_version' => version_compare(PHP_VERSION, $this->requirements['php'], '>='),
            'php_version_current' => PHP_VERSION,
            'extensions' => [],
            'writable' => [],
        ];

        foreach ($this->requirements['extensions'] as $ext) {
            $checks['extensions'][$ext] = extension_loaded($ext);
        }

        $checks['writable']['storage'] = is_writable(storage_path());
        $checks['writable']['bootstrap/cache'] = is_writable(base_path('bootstrap/cache'));
        $checks['writable']['public/uploads'] = is_writable(public_path('uploads'));

        $checks['all_passed'] = $checks['php_version'] && 
            !in_array(false, $checks['extensions'], true) &&
            !in_array(false, $checks['writable'], true);

        return view('installer::requirements', compact('checks'));
    }

    public function database()
    {
        if (File::exists(base_path('.installed'))) {
            return redirect('/');
        }
        return view('installer::database');
    }

    public function saveDatabase(Request $request)
    {
        $request->validate([
            'db_host' => 'required',
            'db_name' => 'required',
            'db_user' => 'required',
            'db_password' => 'nullable',
        ]);

        try {
            $pdo = new \PDO(
                "mysql:host={$request->db_host};dbname={$request->db_name}",
                $request->db_user,
                $request->db_password
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            return back()->withErrors(['connection' => 'Connection failed: ' . $e->getMessage()]);
        }

        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            File::copy(base_path('.env.example'), $envPath);
        }

        $envContent = File::get($envPath);
        $envContent = str_replace([
            'DB_HOST=127.0.0.1',
            'DB_DATABASE=laravel',
            'DB_USERNAME=root',
            'DB_PASSWORD=',
        ], [
            "DB_HOST={$request->db_host}",
            "DB_DATABASE={$request->db_name}",
            "DB_USERNAME={$request->db_user}",
            "DB_PASSWORD={$request->db_password}",
        ], $envContent);

        File::put($envPath, $envContent);

        return redirect()->route('installer.account');
    }

    public function account()
    {
        if (File::exists(base_path('.installed'))) {
            return redirect('/');
        }
        return view('installer::account');
    }

    public function finish(Request $request)
    {
        $request->validate([
            'site_name' => 'required',
            'admin_email' => 'required|email',
            'admin_password' => 'required|min:8|confirmed',
        ]);

        try {
            Artisan::call('migrate', ['--force' => true]);

            $registry = app('module.registry');
            foreach ($registry->getAllModules() as $module) {
                $module->install();
            }
            foreach ($registry->getAllComponents() as $component) {
                $component->install();
            }

            DB::table('users')->insert([
                'name' => 'مدیر سیستم',
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'admin',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            File::put(base_path('.installed'), date('Y-m-d H:i:s'));

            Artisan::call('key:generate');
            Artisan::call('config:cache');

            return view('installer::finish');
        } catch (\Exception $e) {
            return back()->withErrors(['install' => 'Installation failed: ' . $e->getMessage()]);
        }
    }
}
