<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class Localization
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // زبان از session یا cookie
        $locale = Session::get('locale', config('app.locale'));
        
        // پشتیبانی از زبان‌ها
        $supportedLocales = ['en', 'fa', 'ar', 'zh', 'es', 'fr', 'de', 'ru'];
        
        if (!in_array($locale, $supportedLocales)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
