<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LanguageMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale'));

        if ($request->has('lang')) {
            $locale = $request->get('lang');
            session(['locale' => $locale]);
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
