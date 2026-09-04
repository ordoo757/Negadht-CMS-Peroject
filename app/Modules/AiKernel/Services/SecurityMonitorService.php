<?php

namespace App\Modules\AiKernel\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SecurityMonitorService
{
    protected array $threatLevels = ['low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];

    public function analyzeLogin($user): array
    {
        $riskScore = 0;
        $factors = [];

        // Check IP reputation
        $ip = request()->ip();
        $ipReputation = $this->checkIpReputation($ip);
        if ($ipReputation['risk'] > 0.5) {
            $riskScore += 30;
            $factors[] = 'Suspicious IP address';
        }

        // Check login time pattern
        $hour = now()->hour;
        $usualHours = DB::table('ai_user_profiles')
            ->where('user_id', $user->id)
            ->value('preferred_hours');

        if ($usualHours) {
            $usualHours = json_decode($usualHours, true) ?? [];
            if (!in_array($hour, $usualHours)) {
                $riskScore += 20;
                $factors[] = 'Unusual login time';
            }
        }

        // Check failed attempts
        $failedAttempts = DB::table('activity_logs')
            ->where('action', 'login_failed')
            ->where('ip_address', $ip)
            ->where('created_at', '>=', now()->subHours(1))
            ->count();

        if ($failedAttempts > 3) {
            $riskScore += 25;
            $factors[] = 'Multiple failed attempts from this IP';
        }

        // Check device fingerprint
        $deviceFingerprint = $this->generateDeviceFingerprint();
        $knownDevices = DB::table('user_devices')
            ->where('user_id', $user->id)
            ->pluck('fingerprint')
            ->toArray();

        if (!empty($knownDevices) && !in_array($deviceFingerprint, $knownDevices)) {
            $riskScore += 15;
            $factors[] = 'New device detected';
        }

        $assessment = [
            'risk_score' => min(100, $riskScore),
            'risk_level' => $this->getRiskLevel($riskScore),
            'factors' => $factors,
            'requires_2fa' => $riskScore > 50,
            'requires_captcha' => $riskScore > 30,
            'should_block' => $riskScore > 80,
        ];

        // Log security event
        $this->logSecurityEvent($user->id, 'login', $assessment);

        return $assessment;
    }

    public function scanForThreats(): array
    {
        $threats = [];

        // Check for brute force attempts
        $bruteForceIps = DB::table('activity_logs')
            ->select('ip_address', DB::raw('COUNT(*) as attempts'))
            ->where('action', 'login_failed')
            ->where('created_at', '>=', now()->subHours(1))
            ->groupBy('ip_address')
            ->having('attempts', '>', 5)
            ->get();

        foreach ($bruteForceIps as $ip) {
            $threats[] = [
                'type' => 'brute_force',
                'ip' => $ip->ip_address,
                'severity' => 'high',
                'details' => "{$ip->attempts} failed login attempts",
            ];
        }

        // Check for SQL injection attempts
        $sqlInjectionPatterns = DB::table('activity_logs')
            ->where('created_at', '>=', now()->subHours(24))
            ->where(function ($query) {
                $query->where('url', 'like', '%union%select%')
                    ->orWhere('url', 'like', '%\'%20or%20\'1\'%3D\'1%')
                    ->orWhere('input', 'like', '%<script>%');
            })
            ->get();

        foreach ($sqlInjectionPatterns as $attempt) {
            $threats[] = [
                'type' => 'injection_attempt',
                'ip' => $attempt->ip_address,
                'severity' => 'critical',
                'details' => 'Potential injection attack detected',
            ];
        }

        // Check for privilege escalation
        $privilegeEscalation = DB::table('activity_logs')
            ->where('action', 'permission_change')
            ->where('created_at', '>=', now()->subHours(24))
            ->whereNotIn('user_id', function ($query) {
                $query->select('id')->from('users')->where('role', 'admin');
            })
            ->get();

        foreach ($privilegeEscalation as $event) {
            $threats[] = [
                'type' => 'privilege_escalation',
                'ip' => $event->ip_address,
                'severity' => 'critical',
                'details' => 'Unauthorized permission change',
            ];
        }

        return $threats;
    }

    public function blockIp(string $ip, string $reason = '', int $hours = 24): bool
    {
        Cache::put("blocked_ip:{$ip}", [
            'reason' => $reason,
            'blocked_at' => now(),
            'expires_at' => now()->addHours($hours),
        ], now()->addHours($hours));

        DB::table('blocked_ips')->insert([
            'ip_address' => $ip,
            'reason' => $reason,
            'blocked_until' => now()->addHours($hours),
            'created_at' => now(),
        ]);

        Log::warning("IP blocked: {$ip} - Reason: {$reason}");

        return true;
    }

    public function isIpBlocked(string $ip): bool
    {
        if (Cache::has("blocked_ip:{$ip}")) {
            return true;
        }

        return DB::table('blocked_ips')
            ->where('ip_address', $ip)
            ->where('blocked_until', '>', now())
            ->exists();
    }

    protected function checkIpReputation(string $ip): array
    {
        $cacheKey = "ip_reputation:{$ip}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Check internal database first
        $internalScore = DB::table('activity_logs')
            ->where('ip_address', $ip)
            ->where('risk_level', 'high')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $reputation = [
            'risk' => min(1.0, $internalScore / 10),
            'source' => 'internal',
        ];

        Cache::put($cacheKey, $reputation, now()->addHours(6));

        return $reputation;
    }

    protected function generateDeviceFingerprint(): string
    {
        $data = [
            request()->userAgent(),
            request()->header('Accept-Language'),
            request()->header('Accept-Encoding'),
        ];

        return hash('sha256', implode('|', $data));
    }

    protected function getRiskLevel(float $score): string
    {
        if ($score >= 80) return 'critical';
        if ($score >= 60) return 'high';
        if ($score >= 40) return 'medium';
        if ($score >= 20) return 'low';
        return 'minimal';
    }

    protected function logSecurityEvent(int $userId, string $event, array $assessment): void
    {
        DB::table('security_logs')->insert([
            'user_id' => $userId,
            'event' => $event,
            'ip_address' => request()->ip(),
            'risk_score' => $assessment['risk_score'],
            'risk_level' => $assessment['risk_level'],
            'factors' => json_encode($assessment['factors']),
            'created_at' => now(),
        ]);

        if ($assessment['risk_level'] === 'critical') {
            Log::critical("Critical security event for user {$userId}: " . json_encode($assessment));

            // Notify admins
            $this->notifyAdmins($userId, $assessment);
        }
    }

    protected function notifyAdmins(int $userId, array $assessment): void
    {
        $admins = DB::table('users')->where('role', 'admin')->get();

        foreach ($admins as $admin) {
            // In production, send actual notifications
            Log::info("Security alert sent to admin {$admin->email}: User {$userId} - Risk: {$assessment['risk_level']}");
        }
    }
}
