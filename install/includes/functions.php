<?php

/**
 * دریافت عنوان مرحله
 */
function getStepTitle($step, $lang) {
    $titles = [
        1 => $lang->get('welcome_title'),
        2 => $lang->get('license_title'),
        3 => $lang->get('requirements_title'),
        4 => $lang->get('database_title'),
        5 => $lang->get('site_title'),
        6 => $lang->get('admin_title'),
        7 => $lang->get('modules_title'),
        8 => $lang->get('finalize_title'),
    ];

    return $titles[$step] ?? 'Step ' . $step;
}

/**
 * دریافت برچسب مرحله
 */
function getStepLabel($step, $lang) {
    $labels = [
        1 => $lang->get('welcome'),
        2 => $lang->get('license'),
        3 => $lang->get('requirements'),
        4 => $lang->get('database'),
        5 => $lang->get('site'),
        6 => $lang->get('admin'),
        7 => $lang->get('modules'),
        8 => $lang->get('finalize'),
    ];

    return $labels[$step] ?? 'Step ' . $step;
}

/**
 * دریافت توضیحات مرحله
 */
function getStepDescription($step, $lang) {
    $descriptions = [
        1 => $lang->get('welcome_desc'),
        2 => $lang->get('license_desc'),
        3 => $lang->get('requirements_desc'),
        4 => $lang->get('database_desc'),
        5 => $lang->get('site_desc'),
        6 => $lang->get('admin_desc'),
        7 => $lang->get('modules_desc'),
        8 => $lang->get('finalize_desc'),
    ];

    return $descriptions[$step] ?? '';
}

/**
 * دریافت نام فایل مرحله
 */
function getStepFileName($step) {
    $names = [
        1 => 'welcome',
        2 => 'license',
        3 => 'requirements',
        4 => 'database',
        5 => 'site',
        6 => 'admin',
        7 => 'modules',
        8 => 'finalize',
    ];

    return $names[$step] ?? 'welcome';
}

/**
 * بررسی وجود فایل قفل نصب
 */
function isInstalled() {
    return file_exists(__DIR__ . '/../storage/.installed');
}

/**
 * ثبت لاگ نصب
 */
function logInstall($message, $type = 'info') {
    $logFile = __DIR__ . '/../storage/logs/install.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$type}] {$message}\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * نمایش پیام موفقیت/خطا
 */
function showMessage($type, $message) {
    $classes = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
    ];

    $icons = [
        'success' => 'fa-check-circle',
        'error' => 'fa-exclamation-circle',
        'warning' => 'fa-exclamation-triangle',
        'info' => 'fa-info-circle',
    ];

    return '<div class="alert ' . $classes[$type] . ' alert-dismissible fade show" role="alert">
        <i class="fas ' . $icons[$type] . '"></i>
        ' . $message . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}
