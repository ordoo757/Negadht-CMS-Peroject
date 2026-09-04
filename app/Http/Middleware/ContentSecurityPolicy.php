<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // دریافت تنظیمات CSP از کانفیگ
        $cspConfig = config('security.csp', []);
        $reportOnly = $cspConfig['report_only'] ?? false;
        $reportUri = $cspConfig['report_uri'] ?? null;

        // ساخت هدر CSP
        $policy = $this->buildPolicy($cspConfig);

        if ($reportOnly) {
            $response->headers->set('Content-Security-Policy-Report-Only', $policy);
        } else {
            $response->headers->set('Content-Security-Policy', $policy);
        }

        return $response;
    }

    /**
     * ساخت سیاست CSP
     */
    protected function buildPolicy(array $config): string
    {
        $directives = [];

        // ===== Default Sources =====
        $defaultSrc = $config['default_src'] ?? ["'self'"];
        $directives[] = "default-src " . implode(' ', $defaultSrc);

        // ===== Script Sources =====
        $scriptSrc = $config['script_src'] ?? ["'self'", "'unsafe-inline'", "'unsafe-eval'"];
        $directives[] = "script-src " . implode(' ', $scriptSrc);

        // ===== Style Sources =====
        $styleSrc = $config['style_src'] ?? ["'self'", "'unsafe-inline'"];
        $directives[] = "style-src " . implode(' ', $styleSrc);

        // ===== Image Sources =====
        $imgSrc = $config['img_src'] ?? ["'self'", "data:", "https:"];
        $directives[] = "img-src " . implode(' ', $imgSrc);

        // ===== Font Sources =====
        $fontSrc = $config['font_src'] ?? ["'self'", "https:"];
        $directives[] = "font-src " . implode(' ', $fontSrc);

        // ===== Connect Sources =====
        $connectSrc = $config['connect_src'] ?? ["'self'"];
        $directives[] = "connect-src " . implode(' ', $connectSrc);

        // ===== Frame Sources =====
        $frameSrc = $config['frame_src'] ?? ["'self'"];
        $directives[] = "frame-src " . implode(' ', $frameSrc);

        // ===== Object Sources =====
        $objectSrc = $config['object_src'] ?? ["'none'"];
        $directives[] = "object-src " . implode(' ', $objectSrc);

        // ===== Base URI =====
        $baseUri = $config['base_uri'] ?? ["'self'"];
        $directives[] = "base-uri " . implode(' ', $baseUri);

        // ===== Form Action =====
        $formAction = $config['form_action'] ?? ["'self'"];
        $directives[] = "form-action " . implode(' ', $formAction);

        // ===== Frame Ancestors =====
        $frameAncestors = $config['frame_ancestors'] ?? ["'none'"];
        $directives[] = "frame-ancestors " . implode(' ', $frameAncestors);

        // ===== Upgrade Insecure Requests =====
        if ($config['upgrade_insecure_requests'] ?? false) {
            $directives[] = "upgrade-insecure-requests";
        }

        // ===== Block All Mixed Content =====
        if ($config['block_all_mixed_content'] ?? false) {
            $directives[] = "block-all-mixed-content";
        }

        // ===== Report URI =====
        $reportUri = $config['report_uri'] ?? null;
        if ($reportUri) {
            $directives[] = "report-uri {$reportUri}";
        }

        return implode('; ', $directives);
    }
}
