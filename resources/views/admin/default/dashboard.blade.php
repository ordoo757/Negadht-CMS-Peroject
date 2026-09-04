@extends('admin.default.index')

@section('page-title', 'داشبورد مدیریت')
@section('page-desc', 'نمای کلی وضعیت سیستم، آمار و اطلاعات مهم')

@section('breadcrumb')
    <span class="separator"><i class="fas fa-chevron-left"></i></span>
    <span>داشبورد</span>
@endsection

@section('page-actions')
    <button class="btn btn-secondary btn-sm" onclick="window.location.reload()">
        <i class="fas fa-sync-alt"></i> بروزرسانی
    </button>
    <button class="btn btn-primary btn-sm" onclick="neuroAdmin.showToast('دستیار AI', 'در حال تحلیل داده‌ها...', 'info')">
        <i class="fas fa-magic"></i> تحلیل هوشمند
    </button>
@endsection

@section('content')
<!-- Stats Row -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-cube"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $status['modules']['active_modules'] ?? 0 }}</h3>
            <p>ماژول‌های فعال</p>
            <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i>
                <span>۱۲٪ رشد</span>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-puzzle-piece"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $status['modules']['active_components'] ?? 0 }}</h3>
            <p>کامپوننت‌های فعال</p>
            <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i>
                <span>۵٪ رشد</span>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $totalUsers ?? 1 }}</h3>
            <p>کاربران ثبت‌شده</p>
            <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i>
                <span>۸٪ رشد</span>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $status['security']['failed_logins_24h'] ?? 0 }}</h3>
            <p>ورود ناموفق ۲۴ ساعت</p>
            <div class="stat-trend {{ ($status['security']['failed_logins_24h'] ?? 0) > 10 ? 'down' : 'up' }}">
                <i class="fas fa-{{ ($status['security']['failed_logins_24h'] ?? 0) > 10 ? 'arrow-up' : 'arrow-down' }}"></i>
                <span>{{ ($status['security']['failed_logins_24h'] ?? 0) > 10 ? 'نیاز به بررسی' : 'عادی' }}</span>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-bolt"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $status['response_time'] ?? '0ms' }}</h3>
            <p>زمان پاسخ‌دهی</p>
            <div class="stat-trend up">
                <i class="fas fa-check"></i>
                <span>بهینه</span>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon pink">
            <i class="fas fa-brain"></i>
        </div>
        <div class="stat-info">
            <h3>{{ ($status['ai_status']['ai_available'] ?? false) ? 'فعال' : 'غیرفعال' }}</h3>
            <p>وضعیت هوش مصنوعی</p>
            <div class="stat-trend up">
                <i class="fas fa-{{ ($status['ai_status']['learning_active'] ?? false) ? 'check' : 'times' }}"></i>
                <span>یادگیری {{ ($status['ai_status']['learning_active'] ?? false) ? 'فعال' : 'غیرفعال' }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Main Grid -->
<div class="grid-2">
    <!-- System Status -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-server"></i> وضعیت سیستم</h3>
            <span class="badge badge-success"><i class="fas fa-circle" style="font-size: 6px;"></i> آنلاین</span>
        </div>
        <div class="card-body">
            <div class="data-table-wrapper">
                <table class="data-table">
                    <tbody>
                        <tr>
                            <td style="color: var(--text-muted);"><i class="fab fa-php" style="margin-left: 0.5rem;"></i> نسخه PHP</td>
                            <td style="font-weight: 600;">{{ $status['system']['php_version'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted);"><i class="fab fa-laravel" style="margin-left: 0.5rem;"></i> نسخه Laravel</td>
                            <td style="font-weight: 600;">{{ $status['system']['laravel_version'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted);"><i class="fas fa-code-branch" style="margin-left: 0.5rem;"></i> محیط</td>
                            <td>
                                <span class="badge {{ ($status['system']['environment'] ?? '') === 'production' ? 'badge-success' : 'badge-warning' }}">
                                    {{ $status['system']['environment'] ?? '-' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted);"><i class="fas fa-bug" style="margin-left: 0.5rem;"></i> حالت Debug</td>
                            <td>
                                <span class="badge {{ ($status['system']['debug_mode'] ?? false) ? 'badge-warning' : 'badge-success' }}">
                                    {{ ($status['system']['debug_mode'] ?? false) ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted);"><i class="fas fa-clock" style="margin-left: 0.5rem;"></i> منطقه زمانی</td>
                            <td style="font-weight: 600;">{{ $status['system']['timezone'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted);"><i class="fas fa-language" style="margin-left: 0.5rem;"></i> زبان پیش‌فرض</td>
                            <td style="font-weight: 600;">{{ $status['system']['locale'] ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Security Status -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-shield-alt"></i> وضعیت امنیتی</h3>
            <a href="{{ route('admin.security.index') }}" class="btn btn-ghost btn-sm">
                <i class="fas fa-external-link-alt"></i> مدیریت
            </a>
        </div>
        <div class="card-body">
            <div class="data-table-wrapper">
                <table class="data-table">
                    <tbody>
                        <tr>
                            <td style="color: var(--text-muted);"><i class="fas fa-times-circle" style="margin-left: 0.5rem;"></i> ورود ناموفق ۲۴ ساعت</td>
                            <td>
                                <span class="badge {{ ($status['security']['failed_logins_24h'] ?? 0) > 10 ? 'badge-danger' : 'badge-success' }}">
                                    {{ $status['security']['failed_logins_24h'] ?? 0 }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted);"><i class="fas fa-ban" style="margin-left: 0.5rem;"></i> IP‌های مسدود</td>
                            <td>
                                <span class="badge {{ ($status['security']['blocked_ips'] ?? 0) > 0 ? 'badge-warning' : 'badge-success' }}">
                                    {{ $status['security']['blocked_ips'] ?? 0 }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted);"><i class="fas fa-lock" style="margin-left: 0.5rem;"></i> SSL/TLS</td>
                            <td>
                                <span class="badge {{ ($status['security']['ssl_enabled'] ?? false) ? 'badge-success' : 'badge-warning' }}">
                                    {{ ($status['security']['ssl_enabled'] ?? false) ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted);"><i class="fas fa-wrench" style="margin-left: 0.5rem;"></i> حالت تعمیر</td>
                            <td>
                                <span class="badge {{ ($status['security']['maintenance_mode'] ?? false) ? 'badge-danger' : 'badge-success' }}">
                                    {{ ($status['security']['maintenance_mode'] ?? false) ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- AI Status & Modules -->
<div class="grid-2">
    <!-- AI Status -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-robot"></i> هوش مصنوعی</h3>
            <a href="{{ route('admin.ai.assistant') }}" class="btn btn-ghost btn-sm">
                <i class="fas fa-external-link-alt"></i> دستیار
            </a>
        </div>
        <div class="card-body">
            <div class="data-table-wrapper">
                <table class="data-table">
                    <tbody>
                        <tr>
                            <td style="color: var(--text-muted);"><i class="fas fa-power-off" style="margin-left: 0.5rem;"></i> وضعیت AI</td>
                            <td>
                                <span class="badge {{ ($status['ai_status']['ai_available'] ?? false) ? 'badge-success' : 'badge-secondary' }}">
                                    {{ ($status['ai_status']['ai_available'] ?? false) ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted);"><i class="fas fa-graduation-cap" style="margin-left: 0.5rem;"></i> یادگیری ماشین</td>
                            <td>
                                <span class="badge {{ ($status['ai_status']['learning_active'] ?? false) ? 'badge-success' : 'badge-secondary' }}">
                                    {{ ($status['ai_status']['learning_active'] ?? false) ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted);"><i class="fas fa-eye" style="margin-left: 0.5rem;"></i> مانیتورینگ امنیتی</td>
                            <td>
                                <span class="badge {{ ($status['ai_status']['monitoring_active'] ?? false) ? 'badge-success' : 'badge-secondary' }}">
                                    {{ ($status['ai_status']['monitoring_active'] ?? false) ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-bolt"></i> دسترسی سریع</h3>
        </div>
        <div class="card-body">
            <div class="grid-2" style="gap: 0.75rem;">
                <a href="{{ route('admin.system.settings') }}" class="btn btn-secondary" style="justify-content: flex-start;">
                    <i class="fas fa-sliders-h"></i> تنظیمات سایت
                </a>
                <a href="{{ route('admin.user.index') }}" class="btn btn-secondary" style="justify-content: flex-start;">
                    <i class="fas fa-users"></i> مدیریت کاربران
                </a>
                <a href="{{ route('admin.template.index') }}" class="btn btn-secondary" style="justify-content: flex-start;">
                    <i class="fas fa-layer-group"></i> قالب‌ها
                </a>
                <a href="{{ route('admin.modules.index') }}" class="btn btn-secondary" style="justify-content: flex-start;">
                    <i class="fas fa-box-open"></i> ماژول‌ها
                </a>
                <a href="{{ route('admin.security.index') }}" class="btn btn-secondary" style="justify-content: flex-start;">
                    <i class="fas fa-shield-alt"></i> امنیت
                </a>
                <a href="{{ route('admin.system.backup') }}" class="btn btn-secondary" style="justify-content: flex-start;">
                    <i class="fas fa-database"></i> پشتیبان‌گیری
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Performance -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-tachometer-alt"></i> عملکرد سیستم</h3>
    </div>
    <div class="card-body">
        <div class="grid-4">
            <div class="text-center">
                <div style="font-size: 2rem; font-weight: 700; color: var(--primary); margin-bottom: 0.5rem;">
                    {{ $status['performance']['memory_usage'] ?? '0 MB' }}
                </div>
                <p style="color: var(--text-muted); font-size: 0.875rem;">مصرف حافظه</p>
            </div>
            <div class="text-center">
                <div style="font-size: 2rem; font-weight: 700; color: var(--secondary); margin-bottom: 0.5rem;">
                    {{ $status['performance']['peak_memory'] ?? '0 MB' }}
                </div>
                <p style="color: var(--text-muted); font-size: 0.875rem;">پیک حافظه</p>
            </div>
            <div class="text-center">
                <div style="font-size: 2rem; font-weight: 700; color: var(--success); margin-bottom: 0.5rem;">
                    {{ ucfirst($status['performance']['cache_driver'] ?? 'file') }}
                </div>
                <p style="color: var(--text-muted); font-size: 0.875rem;">درایور کش</p>
            </div>
            <div class="text-center">
                <div style="font-size: 2rem; font-weight: 700; color: var(--info); margin-bottom: 0.5rem;">
                    {{ ucfirst($status['performance']['session_driver'] ?? 'file') }}
                </div>
                <p style="color: var(--text-muted); font-size: 0.875rem;">درایور سشن</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.stat-info h3').forEach(el => {
        const text = el.textContent;
        if (!isNaN(parseInt(text))) {
            const target = parseInt(text);
            let current = 0;
            const increment = target / 30;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    el.textContent = target.toLocaleString('fa-IR');
                    clearInterval(timer);
                } else {
                    el.textContent = Math.floor(current).toLocaleString('fa-IR');
                }
            }, 30);
        }
    });
});
</script>
@endpush

