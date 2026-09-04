
        // ===== Security Middlewares =====
        \App\Http\Middleware\ContentSecurityPolicy::class,
        \App\Http\Middleware\SecurityHeaders::class,
        \App\Http\Middleware\HstsMiddleware::class,

        // ===== Localization =====
        \App\Http\Middleware\Localization::class,
