<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Cache;

class RateLimitMiddleware
{
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next)
    {
        $config = config('security.rate_limit', []);
        
        if (!$config['enabled'] ?? true) {
            return $next($request);
        }

        $key = $this->resolveRequestSignature($request);
        $maxAttempts = $config['max_attempts'] ?? 60;
        $decayMinutes = $config['decay_minutes'] ?? 1;

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $this->limiter->availableIn($key),
            ], 429);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        $remaining = $maxAttempts - $this->limiter->attempts($key) + 1;
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remaining);

        return $response;
    }

    protected function resolveRequestSignature(Request $request): string
    {
        return sha1(
            $request->method() .
            '|' . $request->url() .
            '|' . $request->ip()
        );
    }
}
