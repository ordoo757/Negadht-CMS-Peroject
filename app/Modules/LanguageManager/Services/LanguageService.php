<?php

namespace App\Modules\LanguageManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class LanguageService
{
    protected string $cacheKey = 'languages';

    public function getActiveLanguages(): array
    {
        if (Cache::has($this->cacheKey)) {
            return Cache::get($this->cacheKey);
        }

        $languages = DB::table('languages')
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->get()
            ->toArray();

        Cache::put($this->cacheKey, $languages, now()->addHours(24));

        return $languages;
    }

    public function getDefaultLanguage(): ?object
    {
        return DB::table('languages')->where('is_default', true)->first();
    }

    public function addLanguage(array $data): int
    {
        $id = DB::table('languages')->insertGetId([
            'code' => $data['code'],
            'name' => $data['name'],
            'native_name' => $data['native_name'] ?? $data['name'],
            'rtl' => $data['rtl'] ?? false,
            'is_active' => $data['is_active'] ?? true,
            'is_default' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::forget($this->cacheKey);

        return $id;
    }

    public function translateWithAI(string $text, string $targetLang, string $sourceLang = 'auto'): string
    {
        if (app()->bound('ai.service')) {
            return app('ai.service')->translate($text, $targetLang, $sourceLang);
        }

        return $text;
    }

    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
