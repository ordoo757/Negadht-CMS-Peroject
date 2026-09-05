<?php

/**
 * NeuroCMS - Content Management System
 *
 * @author     Hooman Oliaei (هومان اولیائی)
 * @copyright  Copyright (c) 2026 Hooman Oliaei
 * @license    GNU General Public License v3.0
 * @link       https://github.com/ordoo757
 */

<?php

namespace App\Modules\AiKernel\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LearningService
{
    protected array $patterns = [];
    protected float $learningRate = 0.1;

    public function __construct()
    {
        // تنظیمات اولیه در صورت نیاز
    }

    // =============== متدهای اصلی ===============

    public function learnFromActivity(array $activity): void
    {
        $pattern = $this->extractPattern($activity);

        DB::table('ai_learning_patterns')->updateOrInsert(
            [
                'pattern_hash' => md5(serialize($pattern)),
                'user_id' => $activity['user_id'] ?? null,
            ],
            [
                'pattern' => json_encode($pattern),
                'activity_type' => $activity['type'] ?? 'unknown',
                'frequency' => DB::raw('frequency + 1'),
                'confidence' => DB::raw('LEAST(confidence + 0.01, 1.0)'),
                'last_seen' => now(),
                'updated_at' => now(),
            ]
        );

        $this->updateUserProfile($activity);
    }

    public function predictUserBehavior(int $userId, string $context): array
    {
        $patterns = DB::table('ai_learning_patterns')
            ->where('user_id', $userId)
            ->where('confidence', '>', 0.5)
            ->orderBy('frequency', 'desc')
            ->limit(10)
            ->get();

        $predictions = [];

        foreach ($patterns as $pattern) {
            $data = json_decode($pattern->pattern, true);
            $predictions[] = [
                'action' => $data['action'] ?? 'unknown',
                'probability' => $pattern->confidence,
                'confidence' => $pattern->confidence,
            ];
        }

        return $predictions;
    }

    public function detectAnomaly(array $activity): array
    {
        $userId = $activity['user_id'] ?? null;

        if (!$userId) {
            return ['is_anomaly' => false, 'score' => 0];
        }

        $userProfile = $this->getUserProfile($userId);
        $currentPattern = $this->extractPattern($activity);

        $anomalyScore = $this->calculateAnomalyScore($userProfile, $currentPattern);

        return [
            'is_anomaly' => $anomalyScore > 0.8,
            'score' => $anomalyScore,
            'reasons' => $this->getAnomalyReasons($userProfile, $currentPattern),
        ];
    }

    public function getRecommendations(int $userId, string $type = 'content'): array
    {
        $cacheKey = "recommendations_{$userId}_{$type}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $profile = $this->getUserProfile($userId);

        $recommendations = match($type) {
            'content' => $this->recommendContent($profile),
            'menu' => $this->recommendMenuItems($profile),
            'action' => $this->recommendActions($profile),
            default => [],
        };

        Cache::put($cacheKey, $recommendations, now()->addHours(2));

        return $recommendations;
    }

    public function trainModel(array $data): array
    {
        $startTime = microtime(true);

        // Simple linear regression training
        $weights = $this->initializeWeights(count($data[0]['features'] ?? []));

        for ($epoch = 0; $epoch < 100; $epoch++) {
            foreach ($data as $sample) {
                $features = $sample['features'] ?? [];
                $target = $sample['target'] ?? 0;

                $prediction = $this->predict($features, $weights);
                $error = $target - $prediction;

                // Update weights
                foreach ($weights as $i => $weight) {
                    $weights[$i] += $this->learningRate * $error * ($features[$i] ?? 0);
                }
            }
        }

        $trainingTime = microtime(true) - $startTime;

        // Save model
        DB::table('ai_models')->updateOrInsert(
            ['name' => 'default'],
            [
                'weights' => json_encode($weights),
                'training_time' => $trainingTime,
                'samples_count' => count($data),
                'updated_at' => now(),
            ]
        );

        return [
            'success' => true,
            'weights' => $weights,
            'training_time' => round($trainingTime, 4),
            'epochs' => 100,
        ];
    }

    // =============== متدهای داخلی ===============

    protected function extractPattern(array $activity): array
    {
        return [
            'action' => $activity['action'] ?? 'unknown',
            'hour' => date('G', strtotime($activity['timestamp'] ?? now())),
            'day_of_week' => date('w', strtotime($activity['timestamp'] ?? now())),
            'ip_prefix' => substr($activity['ip_address'] ?? '0.0.0.0', 0, 7),
            'user_agent_type' => $this->classifyUserAgent($activity['user_agent'] ?? ''),
        ];
    }

    protected function updateUserProfile(array $activity): void
    {
        $userId = $activity['user_id'] ?? null;
        if (!$userId) return;

        $profile = DB::table('ai_user_profiles')->where('user_id', $userId)->first();

        if (!$profile) {
            DB::table('ai_user_profiles')->insert([
                'user_id' => $userId,
                'activity_count' => 1,
                'preferred_hours' => json_encode([date('G')]),
                'common_actions' => json_encode([$activity['action'] ?? 'unknown']),
                'risk_score' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $hours = json_decode($profile->preferred_hours, true) ?? [];
            $hours[] = date('G');
            $hours = array_slice(array_count_values($hours), 0, 5, true);

            $actions = json_decode($profile->common_actions, true) ?? [];
            $actions[] = $activity['action'] ?? 'unknown';
            $actions = array_slice(array_count_values($actions), 0, 10, true);

            DB::table('ai_user_profiles')
                ->where('user_id', $userId)
                ->update([
                    'activity_count' => DB::raw('activity_count + 1'),
                    'preferred_hours' => json_encode(array_keys($hours)),
                    'common_actions' => json_encode(array_keys($actions)),
                    'updated_at' => now(),
                ]);
        }
    }

    protected function getUserProfile(int $userId): array
    {
        $profile = DB::table('ai_user_profiles')->where('user_id', $userId)->first();

        if (!$profile) {
            return [];
        }

        return [
            'activity_count' => $profile->activity_count,
            'preferred_hours' => json_decode($profile->preferred_hours, true) ?? [],
            'common_actions' => json_decode($profile->common_actions, true) ?? [],
            'risk_score' => $profile->risk_score,
        ];
    }

    protected function calculateAnomalyScore(array $profile, array $currentPattern): float
    {
        if (empty($profile)) {
            return 0.3; // New user, moderate risk
        }

        $score = 0;

        // Check if action is common
        if (!in_array($currentPattern['action'], $profile['common_actions'] ?? [])) {
            $score += 0.3;
        }

        // Check if hour is preferred
        if (!in_array($currentPattern['hour'], $profile['preferred_hours'] ?? [])) {
            $score += 0.2;
        }

        // Check activity frequency
        if ($profile['activity_count'] < 10) {
            $score += 0.2;
        }

        return min(1.0, $score);
    }

    protected function getAnomalyReasons(array $profile, array $currentPattern): array
    {
        $reasons = [];

        if (!in_array($currentPattern['action'], $profile['common_actions'] ?? [])) {
            $reasons[] = 'Unusual activity pattern detected';
        }

        if (!in_array($currentPattern['hour'], $profile['preferred_hours'] ?? [])) {
            $reasons[] = 'Activity outside normal hours';
        }

        return $reasons;
    }

    protected function recommendContent(array $profile): array
    {
        $recommendations = [];

        foreach ($profile['common_actions'] ?? [] as $action) {
            $recommendations[] = [
                'type' => 'content',
                'action' => $action,
                'reason' => 'Based on your activity history',
            ];
        }

        return $recommendations;
    }

    protected function recommendMenuItems(array $profile): array
    {
        $commonActions = $profile['common_actions'] ?? [];

        $menuMapping = [
            'create_post' => ['admin.content.create', 'admin.posts.create'],
            'edit_post' => ['admin.content.index', 'admin.posts.index'],
            'view_users' => ['admin.users.index'],
            'settings' => ['admin.settings.index'],
        ];

        $recommendations = [];
        foreach ($commonActions as $action) {
            if (isset($menuMapping[$action])) {
                foreach ($menuMapping[$action] as $route) {
                    $recommendations[] = [
                        'route' => $route,
                        'priority' => 'high',
                    ];
                }
            }
        }

        return $recommendations;
    }

    protected function recommendActions(array $profile): array
    {
        return [
            ['action' => 'review_security', 'priority' => 'medium'],
            ['action' => 'update_profile', 'priority' => 'low'],
        ];
    }

    protected function initializeWeights(int $count): array
    {
        $weights = [];
        for ($i = 0; $i < $count; $i++) {
            $weights[] = mt_rand() / mt_getrandmax() * 2 - 1;
        }
        return $weights;
    }

    protected function predict(array $features, array $weights): float
    {
        $sum = 0;
        foreach ($features as $i => $value) {
            $sum += ($weights[$i] ?? 0) * $value;
        }
        return 1 / (1 + exp(-$sum));
    }

    protected function classifyUserAgent(string $userAgent): string
    {
        if (str_contains($userAgent, 'Mobile')) return 'mobile';
        if (str_contains($userAgent, 'Tablet')) return 'tablet';
        if (str_contains($userAgent, 'Bot')) return 'bot';
        return 'desktop';
    }

    // =============== متدهای جدید برای داشبورد ===============

    /**
     * دریافت آمار کلی کاربران برای داشبورد
     */
    public function getUserInsights(): array
    {
        $totalUsers = DB::table('users')->count();
        $activeUsers = DB::table('ai_user_profiles')
            ->where('updated_at', '>=', now()->subDays(7))
            ->count();
        $totalPatterns = DB::table('ai_learning_patterns')->count();
        $commonActions = DB::table('ai_learning_patterns')
            ->select('activity_type', DB::raw('COUNT(*) as count'))
            ->groupBy('activity_type')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $anomalies = DB::table('ai_learning_patterns')
            ->where('confidence', '<', 0.3)
            ->where('frequency', '>', 5)
            ->count();

        return [
            'total_users' => $totalUsers,
            'active_users_7d' => $activeUsers,
            'total_patterns' => $totalPatterns,
            'common_actions' => $commonActions->toArray(),
            'anomalies_detected' => $anomalies,
            'learning_confidence' => $totalPatterns > 0 ? min(1.0, $totalPatterns / 1000) : 0,
        ];
    }

    /**
     * پاک کردن داده‌های قدیمی (بهینه‌سازی)
     */
    public function cleanOldData(int $days = 90): int
    {
        $deleted = DB::table('ai_learning_patterns')
            ->where('last_seen', '<', now()->subDays($days))
            ->delete();

        Log::info("Cleaned {$deleted} old learning patterns");

        return $deleted;
    }

    /**
     * دریافت پیشنهادات هوشمند برای یک کاربر خاص
     */
    public function getSmartSuggestions(int $userId): array
    {
        $profile = $this->getUserProfile($userId);
        $recommendations = $this->getRecommendations($userId, 'content');

        // اولویت‌بندی بر اساس زمان
        $hour = (int) date('G');
        $preferredHours = $profile['preferred_hours'] ?? [];

        usort($recommendations, function ($a, $b) use ($hour, $preferredHours) {
            $aScore = in_array($hour, $preferredHours) ? 2 : 1;
            $bScore = in_array($hour, $preferredHours) ? 2 : 1;
            return $bScore <=> $aScore;
        });

        return array_slice($recommendations, 0, 5);
    }
}
