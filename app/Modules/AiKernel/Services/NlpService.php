<?php

namespace App\Modules\AiKernel\Services;

use Illuminate\Support\Facades\Cache;

class NlpService
{
    protected array $stopWords;
    protected array $stemmer;

    public function __construct()
    {
        $this->stopWords = $this->loadStopWords();
    }

    public function tokenize(string $text): array
    {
        // Support for Persian, Arabic, and English
        preg_match_all('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\w]+/u', $text, $matches);
        return $matches[0] ?? [];
    }

    public function stem(string $word): string
    {
        // Persian/Arabic stemming
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $word)) {
            return $this->persianStem($word);
        }

        // English Porter Stemmer (simplified)
        return $this->englishStem($word);
    }

    public function extractEntities(string $text): array
    {
        $entities = [
            'persons' => [],
            'locations' => [],
            'organizations' => [],
            'dates' => [],
            'emails' => [],
            'urls' => [],
        ];

        // Extract emails
        preg_match_all('/[\w.-]+@[\w.-]+\.[\w]{2,}/', $text, $emails);
        $entities['emails'] = $emails[0];

        // Extract URLs
        preg_match_all('/https?:\/\/[^\s]+/', $text, $urls);
        $entities['urls'] = $urls[0];

        // Extract dates (simple patterns)
        preg_match_all('/\b\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4}\b/', $text, $dates);
        $entities['dates'] = $dates[0];

        return $entities;
    }

    public function summarize(string $text, int $sentencesCount = 3): string
    {
        $sentences = preg_split('/[.!?؟!]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        if (count($sentences) <= $sentencesCount) {
            return $text;
        }

        $wordFreq = $this->getWordFrequency($text);
        $sentenceScores = [];

        foreach ($sentences as $index => $sentence) {
            $words = $this->tokenize($sentence);
            $score = 0;

            foreach ($words as $word) {
                $stemmed = $this->stem(strtolower($word));
                $score += $wordFreq[$stemmed] ?? 0;
            }

            $sentenceScores[$index] = $score / max(count($words), 1);
        }

        arsort($sentenceScores);
        $topIndices = array_slice(array_keys($sentenceScores), 0, $sentencesCount, true);
        ksort($topIndices);

        $summary = [];
        foreach ($topIndices as $index) {
            $summary[] = trim($sentences[$index]);
        }

        return implode('. ', $summary) . '.';
    }

    public function classify(string $text, array $categories): array
    {
        $scores = [];
        $words = array_count_values($this->tokenize(strtolower($text)));

        foreach ($categories as $category => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                $score += $words[$keyword] ?? 0;
            }
            $scores[$category] = $score;
        }

        arsort($scores);
        return $scores;
    }

    public function similarity(string $text1, string $text2): float
    {
        $words1 = array_count_values($this->tokenize(strtolower($text1)));
        $words2 = array_count_values($this->tokenize(strtolower($text2)));

        $allWords = array_unique(array_merge(array_keys($words1), array_keys($words2)));

        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        foreach ($allWords as $word) {
            $a = $words1[$word] ?? 0;
            $b = $words2[$word] ?? 0;

            $dotProduct += $a * $b;
            $magnitude1 += $a * $a;
            $magnitude2 += $b * $b;
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }

        return round($dotProduct / ($magnitude1 * $magnitude2), 4);
    }

    protected function persianStem(string $word): string
    {
        // Remove common Persian suffixes
        $suffixes = ['\u0647\u0627', '\u0647\u0627\u06CC', '\u0627\u062A', '\u0627\u0646', '\u0648\u0646', '\u06CC\u0646', '\u0645\u06CC'];

        foreach ($suffixes as $suffix) {
            $word = preg_replace('/' . $suffix . '$/u', '', $word);
        }

        return $word;
    }

    protected function englishStem(string $word): string
    {
        $rules = [
            '/ies$/' => 'y',
            '/ied$/' => 'y',
            '/ying$/' => 'y',
            '/ying$/' => 'y',
            '/s$/' => '',
            '/ed$/' => '',
            '/ing$/' => '',
            '/ly$/' => '',
            '/ment$/' => '',
        ];

        foreach ($rules as $pattern => $replacement) {
            $word = preg_replace($pattern, $replacement, $word);
        }

        return $word;
    }

    protected function getWordFrequency(string $text): array
    {
        $cacheKey = 'word_freq_' . md5($text);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $words = $this->tokenize(strtolower($text));
        $freq = [];

        foreach ($words as $word) {
            $stemmed = $this->stem($word);
            if (!in_array($stemmed, $this->stopWords)) {
                $freq[$stemmed] = ($freq[$stemmed] ?? 0) + 1;
            }
        }

        Cache::put($cacheKey, $freq, now()->addHours(1));

        return $freq;
    }

    protected function loadStopWords(): array
    {
        return [
            // Persian
            'و', 'در', 'به', 'از', 'که', 'این', 'را', 'با', 'است', 'برای', 'آن', 'یک', 'خود', 'تا', 'کرد',
            'بر', 'هم', 'نیز', 'ما', 'اما', 'یا', 'هر', 'او', 'شده', 'باید', 'شود', 'شد', 'بود', 'من', 'دیگر',
            // Arabic
            'في', 'من', 'إلى', 'على', 'هذا', 'هذه', 'التي', 'الذي', 'و', 'أن', 'كان', 'لم', 'قد', 'ما', 'لا',
            // English
            'the', 'a', 'an', 'in', 'on', 'at', 'to', 'for', 'of', 'and', 'or', 'but', 'is', 'are', 'was', 'were',
            'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should',
        ];
    }
}
