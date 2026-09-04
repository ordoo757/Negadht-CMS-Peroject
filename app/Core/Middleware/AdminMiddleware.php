<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // 🔥 هوشمند: اگر هیچ admin در سیستم نیست، کاربر فعلی را خودکار admin کن
        if (!User::where('role', 'admin')->exists()) {
            $user->update(['role' => 'admin']);
            // لاگ برای اطلاع‌رسانی
            \Illuminate\Support\Facades\Log::info('First admin auto-created: ' . $user->email);
        }

        if (!$user->hasRole('admin')) {
            abort(403, 'Access denied');
        }

        if (app()->bound('ai.security')) {
            $assessment = app('ai.security')->analyzeLogin($user);

            if ($assessment['should_block'] ?? false) {
                auth()->logout();
                abort(403, 'Access blocked due to security concerns');
            }

            if ($assessment['requires_2fa'] ?? false) {
                if (!session()->has('2fa_verified')) {
                    return redirect()->route('2fa.verify');
                }
            }
        }

        return $next($request);
    }
}
