<?php

class Database
{
    protected $connection;

    public function testConnection($host, $port, $name, $user, $pass)
    {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $this->connection = new PDO($dsn, $user, $pass);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return [
                'success' => true,
                'message' => 'اتصال با موفقیت برقرار شد!',
            ];
        } catch (PDOException $e) {
            // اگر دیتابیس وجود نداشته باشد، سعی در اتصال بدون دیتابیس
            if (strpos($e->getMessage(), 'Unknown database') !== false) {
                try {
                    $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
                    $this->connection = new PDO($dsn, $user, $pass);
                    $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // ایجاد دیتابیس
                    $this->connection->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                    return [
                        'success' => true,
                        'message' => "دیتابیس '{$name}' با موفقیت ایجاد و اتصال برقرار شد!",
                    ];
                } catch (PDOException $e2) {
                    return [
                        'success' => false,
                        'message' => "خطا: " . $e2->getMessage(),
                    ];
                }
            }

            return [
                'success' => false,
                'message' => "خطا: " . $e->getMessage(),
            ];
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function runMigrations($config)
    {
        try {
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";
            $this->connection = new PDO($dsn, $config['user'], $config['pass']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // دریافت لیست فایل‌های میگریشن
            $migrationPath = __DIR__ . '/../../database/migrations';
            $files = glob($migrationPath . '/*.php');
            sort($files);

            // ایجاد جدول migrations اگر وجود ندارد
            $this->connection->exec("
                CREATE TABLE IF NOT EXISTS `migrations` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `migration` varchar(255) NOT NULL,
                    `batch` int(11) NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $batch = 1;
            $executed = [];

            // بررسی میگریشن‌های اجرا شده
            $stmt = $this->connection->query("SELECT migration FROM migrations");
            $executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($files as $file) {
                $migrationName = basename($file, '.php');

                // اگر قبلاً اجرا شده، رد کن
                if (in_array($migrationName, $executed)) {
                    continue;
                }

                // اجرای میگریشن
                try {
                    require_once $file;
                    $className = $this->getClassNameFromFile($file);
                    if (class_exists($className)) {
                        $instance = new $className();
                        if (method_exists($instance, 'up')) {
                            // اجرای متد up
                            $instance->up();

                            // ثبت در جدول migrations
                            $stmt = $this->connection->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                            $stmt->execute([$migrationName, $batch]);

                            logInstall("Migration executed: {$migrationName}");
                        }
                    }
                } catch (Exception $e) {
                    logInstall("Migration failed: {$migrationName} - " . $e->getMessage(), 'error');
                    throw $e;
                }
            }

            return [
                'success' => true,
                'message' => 'میگریشن‌ها با موفقیت اجرا شدند.',
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطا در اجرای میگریشن‌ها: ' . $e->getMessage(),
            ];
        }
    }

    protected function getClassNameFromFile($file)
    {
        $content = file_get_contents($file);
        if (preg_match('/class\s+([a-zA-Z0-9_]+)/', $content, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
