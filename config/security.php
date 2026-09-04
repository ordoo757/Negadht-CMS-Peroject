<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Content Security Policy (CSP)
    |--------------------------------------------------------------------------
    */
    'csp' => [
        'report_only' => env('CSP_REPORT_ONLY', false),

        'default_src' => ["'self'"],
        'script_src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
        'style_src' => ["'self'", "'unsafe-inline'"],
        'img_src' => ["'self'", "data:", "https:"],
        'font_src' => ["'self'", "https:"],
        'connect_src' => ["'self'"],
        'frame_src' => ["'self'"],
        'object_src' => ["'none'"],
        'base_uri' => ["'self'"],
        'form_action' => ["'self'"],
        'frame_ancestors' => ["'none'"],
        'upgrade_insecure_requests' => true,
        'block_all_mixed_content' => true,
        'report_uri' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Strict Transport Security (HSTS)
    |--------------------------------------------------------------------------
    */
    'hsts' => [
        'enabled' => env('HSTS_ENABLED', true),
        'max_age' => env('HSTS_MAX_AGE', 31536000), // 1 year
        'include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
        'preload' => env('HSTS_PRELOAD', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'enabled' => env('RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('RATE_LIMIT_MAX_ATTEMPTS', 60),
        'decay_minutes' => env('RATE_LIMIT_DECAY_MINUTES', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'x-frame-options' => 'DENY',
        'x-content-type-options' => 'nosniff',
        'x-xss-protection' => '1; mode=block',
        'referrer-policy' => 'strict-origin-when-cross-origin',
        'permissions-policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication (2FA)
    |--------------------------------------------------------------------------
    */
    '2fa' => [
        'enabled' => env('2FA_ENABLED', false),
        'required_roles' => ['admin'],
        'otp_length' => 6,
        'otp_expiry_minutes' => 10,
        'backup_codes_count' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
        'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
        'require_special' => env('PASSWORD_REQUIRE_SPECIAL', true),
        'expiry_days' => env('PASSWORD_EXPIRY_DAYS', 90),
    ],
];
