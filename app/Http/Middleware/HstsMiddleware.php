<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HstsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $config = config('security.hsts', []);
        
        if (!$config['enabled'] ?? false) {
            return $response;
        }

        $maxAge = $config['max_age'] ?? 31536000;
        $includeSubdomains = $config['include_subdomains'] ?? true;
        $preload = $config['preload'] ?? false;

        $header = "max-age={$maxAge}";
        
        if ($includeSubdomains) {
            $header .= "; includeSubDomains";
        }
        
        if ($preload) {
            $header .= "; preload";
        }

        $response->headers->set('Strict-Transport-Security', $header);

        return $response;
    }
}
