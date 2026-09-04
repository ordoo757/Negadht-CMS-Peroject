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

    // =============== بخش جدید AI ===============
    'ai' => [
        'default_provider' => env('AI_DEFAULT_PROVIDER', 'ollama'), // اولویت اول: محلی و رایگان
        
        'openai' => [
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 2000),
        ],
        
        'claude' => [
            'model' => env('CLAUDE_MODEL', 'claude-3-opus-20240229'),
            'max_tokens' => (int) env('CLAUDE_MAX_TOKENS', 2000),
        ],
        
        'ollama' => [
            'url' => env('OLLAMA_URL', 'http://localhost:11434'),
            'model' => env('OLLAMA_MODEL', 'llama2'),
            'max_tokens' => (int) env('OLLAMA_MAX_TOKENS', 2000),
        ],
    ],
];
