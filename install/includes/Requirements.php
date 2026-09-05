<?php

class Requirements
{
    protected $results = [];
    protected $passed = true;

    public function checkAll()
    {
        $this->checkPhpVersion();
        $this->checkExtensions();
        $this->checkPermissions();
        $this->checkEnvFile();
        $this->checkMemoryLimit();

        return $this->results;
    }

    public function isPassed()
    {
        return $this->passed;
    }

    protected function checkPhpVersion()
    {
        $version = phpversion();
        $required = '8.3';
        $passed = version_compare($version, $required, '>=');

        $this->results['php'] = [
            'name' => 'PHP Version',
            'details' => "نسخه فعلی: {$version} | نسخه مورد نیاز: >= {$required}",
            'passed' => $passed,
            'version' => $version,
            'solution' => $passed ? '' : 'لطفاً PHP نسخه 8.3 یا بالاتر را نصب کنید.',
        ];

        if (!$passed) {
            $this->passed = false;
        }
    }

    protected function checkExtensions()
    {
        $required = [
            'pdo' => 'PDO',
            'mbstring' => 'Mbstring',
            'openssl' => 'OpenSSL',
            'json' => 'JSON',
            'xml' => 'XML',
            'ctype' => 'Ctype',
            'tokenizer' => 'Tokenizer',
            'fileinfo' => 'Fileinfo',
            'bcmath' => 'BCMath',
            'zip' => 'Zip',
            'curl' => 'cURL',
        ];

        $missing = [];
        foreach ($required as $ext => $name) {
            if (!extension_loaded($ext)) {
                $missing[] = $name;
            }
        }

        $passed = empty($missing);
        $details = $passed ? 'تمام اکستنشن‌های مورد نیاز نصب هستند.' : 'اکستنشن‌های زیر نصب نیستند: ' . implode(', ', $missing);

        $this->results['extensions'] = [
            'name' => 'Required Extensions',
            'details' => $details,
            'passed' => $passed,
            'solution' => $passed ? '' : 'لطفاً اکستنشن‌های زیر را نصب کنید: ' . implode(', ', $missing),
        ];

        if (!$passed) {
            $this->passed = false;
        }
    }

    protected function checkPermissions()
    {
        $paths = [
            'storage' => [
                'path' => __DIR__ . '/../../storage',
                'name' => 'storage',
            ],
            'bootstrap/cache' => [
                'path' => __DIR__ . '/../../bootstrap/cache',
                'name' => 'bootstrap/cache',
            ],
            'public/uploads' => [
                'path' => __DIR__ . '/../../public/uploads',
                'name' => 'public/uploads',
            ],
        ];

        $failed = [];
        foreach ($paths as $key => $path) {
            $dir = $path['path'];
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            if (!is_writable($dir)) {
                $failed[] = $path['name'];
            }
        }

        $passed = empty($failed);
        $details = $passed ? 'تمام پوشه‌ها دارای دسترسی نوشتن هستند.' : 'پوشه‌های زیر دسترسی نوشتن ندارند: ' . implode(', ', $failed);

        $this->results['permissions'] = [
            'name' => 'Folder Permissions',
            'details' => $details,
            'passed' => $passed,
            'solution' => $passed ? '' : 'لطفاً دسترسی نوشتن را برای پوشه‌های زیر تنظیم کنید: ' . implode(', ', $failed),
        ];

        if (!$passed) {
            $this->passed = false;
        }
    }

    protected function checkEnvFile()
    {
        $envFile = __DIR__ . '/../../.env';
        $envExample = __DIR__ . '/../../.env.example';

        $passed = file_exists($envFile) || file_exists($envExample);
        $details = '';

        if (file_exists($envFile)) {
            $details = 'فایل .env وجود دارد.';
        } elseif (file_exists($envExample)) {
            $details = 'فایل .env.example وجود دارد. (نیاز به کپی به .env)';
            $passed = true;
        } else {
            $details = 'فایل .env یا .env.example وجود ندارد.';
        }

        $this->results['env'] = [
            'name' => '.env File',
            'details' => $details,
            'passed' => $passed,
            'solution' => $passed ? '' : 'لطفاً فایل .env را از .env.example کپی کنید یا ایجاد کنید.',
        ];
    }

    protected function checkMemoryLimit()
    {
        $memoryLimit = ini_get('memory_limit');
        $limitBytes = $this->convertToBytes($memoryLimit);
        $requiredBytes = 128 * 1024 * 1024; // 128 MB

        $passed = $limitBytes >= $requiredBytes;
        $details = "حافظه اختصاص‌یافته: {$memoryLimit} | حداقل مورد نیاز: 128M";

        $this->results['memory'] = [
            'name' => 'Memory Limit',
            'details' => $details,
            'passed' => $passed,
            'solution' => $passed ? '' : 'لطفاً memory_limit را در php.ini به 128M یا بیشتر افزایش دهید.',
        ];

        if (!$passed) {
            $this->passed = false;
        }
    }

    protected function convertToBytes($value)
    {
        $unit = strtolower(substr($value, -1));
        $number = (int) substr($value, 0, -1);

        switch ($unit) {
            case 'g': return $number * 1024 * 1024 * 1024;
            case 'm': return $number * 1024 * 1024;
            case 'k': return $number * 1024;
            default: return (int) $value;
        }
    }
}
