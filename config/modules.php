<?php

return [
    'core_modules' => [
        'User',
        'LanguageManager',
        'AiKernel',
    ],

    'auto_discover' => true,

    'cache' => [
        'enabled' => true,
        'ttl' => 86400,
    ],

    'paths' => [
        'modules' => app_path('Modules'),
        'plugins' => app_path('Plugins'),
        'templates' => public_path('templates'),
    ],
];
