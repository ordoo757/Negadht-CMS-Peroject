<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ModuleAccessMiddleware
{
    public function handle(Request $request, Closure $next, string $module)
    {
        if (!auth()->user()->canAccessModule($module)) {
            abort(403, 'Module access denied');
        }
        
        return $next($request);
    }
}
