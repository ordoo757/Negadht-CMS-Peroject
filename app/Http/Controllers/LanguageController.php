<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    /**
     * تغییر زبان
     */
    public function switch(Request $request)
    {
        $locale = $request->input('locale', 'en');
        
        // زبان‌های پشتیبانی شده
        $supportedLocales = ['en', 'fa', 'ar', 'zh', 'es', 'fr', 'de', 'ru'];
        
        if (!in_array($locale, $supportedLocales)) {
            $locale = 'en';
        }

        Session::put('locale', $locale);
        App::setLocale($locale);

        // ذخیره در کوکی
        cookie()->queue('locale', $locale, 60 * 24 * 365);

        return redirect()->back()->with('success', "Language changed to " . $locale);
    }

    /**
     * دریافت زبان فعلی
     */
    public function current()
    {
        return response()->json([
            'locale' => App::getLocale(),
            'supported' => ['en', 'fa', 'ar', 'zh', 'es', 'fr', 'de', 'ru'],
        ]);
    }
}
