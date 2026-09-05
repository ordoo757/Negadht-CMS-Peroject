<?php

class Installer
{
    public function run()
    {
        try {
            logInstall('Starting installation...');

            // 1. دریافت تنظیمات از سشن
            $siteConfig = $_SESSION['site_config'] ?? [];
            $dbConfig = $_SESSION['db_config'] ?? [];
            $adminConfig = $_SESSION['admin_config'] ?? [];
            $modules = $_SESSION['modules_selected'] ?? [];

            // 2. ایجاد فایل .env
            $this->createEnvFile($siteConfig, $dbConfig);

            // 3. اجرای میگریشن‌ها
            $db = new Database();
            $result = $db->runMigrations($dbConfig);
            if (!$result['success']) {
                throw new Exception($result['message']);
            }

            // 4. ایجاد کاربر ادمین
            $this->createAdminUser($adminConfig);

            // 5. نصب ماژول‌های انتخاب شده
            $this->installModules($modules);

            // 6. ایجاد فایل قفل نصب
            $this->createInstallLock();

            logInstall('Installation completed successfully!');

            return [
                'success' => true,
                'message' => 'نصب با موفقیت انجام شد.',
            ];

        } catch (Exception $e) {
            logInstall('Installation failed: ' . $e->getMessage(), 'error');
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function createEnvFile($siteConfig, $dbConfig)
    {
        $envPath = __DIR__ . '/../../.env';
        $envExample = __DIR__ . '/../../.env.example';

        if (!file_exists($envExample)) {
            throw new Exception('.env.example file not found!');
        }

        $content = file_get_contents($envExample);

        // جایگزینی مقادیر
        $replacements = [
            'APP_NAME' => $siteConfig['name'] ?? 'NeuroCMS',
            'APP_URL' => $siteConfig['url'] ?? 'http://localhost',
            'APP_TIMEZONE' => $siteConfig['timezone'] ?? 'Asia/Tehran',
            'APP_LOCALE' => $siteConfig['language'] ?? 'fa',
            'DB_HOST' => $dbConfig['host'] ?? 'localhost',
            'DB_PORT' => $dbConfig['port'] ?? '3306',
            'DB_DATABASE' => $dbConfig['name'] ?? 'neurocms',
            'DB_USERNAME' => $dbConfig['user'] ?? 'root',
            'DB_PASSWORD' => $dbConfig['pass'] ?? '',
        ];

        foreach ($replacements as $key => $value) {
            $content = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                $content
            );
        }

        file_put_contents($envPath, $content);
        logInstall('.env file created successfully.');
    }

    protected function createAdminUser($adminConfig)
    {
        // اتصال به دیتابیس
        $dbConfig = $_SESSION['db_config'] ?? [];
        $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // هش کردن رمز عبور
        $hashedPassword = password_hash($adminConfig['password'], PASSWORD_BCRYPT);

        // ایجاد کاربر ادمین
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role, created_at, updated_at)
            VALUES (?, ?, ?, 'admin', NOW(), NOW())
        ");
        $stmt->execute([$adminConfig['username'], $adminConfig['email'], $hashedPassword]);

        logInstall("Admin user created: {$adminConfig['username']}");
    }

    protected function installModules($modules)
    {
        $installed = [];

        foreach ($modules as $module) {
            $moduleClass = "App\\Modules\\" . ucfirst($module) . "\\Module";

            if (class_exists($moduleClass)) {
                $instance = new $moduleClass();
                if (method_exists($instance, 'install')) {
                    $instance->install();
                    $installed[] = $module;
                    logInstall("Module installed: {$module}");
                }
            }
        }

        return $installed;
    }

    protected function createInstallLock()
    {
        $lockFile = __DIR__ . '/../storage/.installed';
        file_put_contents($lockFile, date('Y-m-d H:i:s') . "\n");
        logInstall('Install lock file created.');
    }

    public static function isStepCompleted($step)
    {
        // بررسی مراحل تکمیل شده
        $completed = $_SESSION['completed_steps'] ?? [];
        return in_array($step, $completed);
    }
}
