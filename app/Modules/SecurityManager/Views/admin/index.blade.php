@extends('core::layouts.admin')

@section('title', 'داشبورد امنیت')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h4 class="mb-4">داشبورد امنیت</h4>
        </div>
    </div>

    {{-- آمار --}}
    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">کل لاگ‌ها</span>
                    <span class="info-box-number">{{ $stats['total_logs'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">لاگ‌های پرخطر</span>
                    <span class="info-box-number">{{ $stats['high_risk_logs'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">لاگ‌های حل نشده</span>
                    <span class="info-box-number">{{ $stats['unresolved_logs'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-dark"><i class="fas fa-ban"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">آی‌پی‌های مسدود</span>
                    <span class="info-box-number">{{ $stats['blocked_ips'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- لاگ‌های اخیر --}}
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">لاگ‌های اخیر</h5>
                    <a href="{{ route('admin.security-manager.logs') }}" class="btn btn-sm btn-primary float-left">
                        مشاهده همه
                    </a>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>رویداد</th>
                                <th>نوع</th>
                                <th>سطح ریسک</th>
                                <th>زمان</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLogs as $log)
                            <tr>
                                <td>{{ $log->event }}</td>
                                <td>{{ $log->type }}</td>
                                <td>
                                    <span class="badge badge-{{ $log->risk_level == 'high' ? 'danger' : ($log->risk_level == 'medium' ? 'warning' : 'info') }}">
                                        {{ $log->risk_level }}
                                    </span>
                                </td>
                                <td>{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center">هیچ لاگی یافت نشد.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">تنظیمات امنیتی</h5>
                    <a href="{{ route('admin.security-manager.settings') }}" class="btn btn-sm btn-primary float-left">
                        ویرایش
                    </a>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><strong>سطح امنیت:</strong> {{ $settings['security_level'] ?? 'medium' }}</li>
                        <li><strong>تلاش‌های مجاز ورود:</strong> {{ $settings['max_login_attempts'] ?? 5 }}</li>
                        <li><strong>فایروال:</strong> {{ ($settings['enable_firewall'] ?? true) ? 'فعال' : 'غیرفعال' }}</li>
                        <li><strong>2FA:</strong> {{ ($settings['enable_2fa'] ?? false) ? 'فعال' : 'غیرفعال' }}</li>
                        <li><strong>SSL:</strong> {{ ($settings['enable_ssl'] ?? true) ? 'فعال' : 'غیرفعال' }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
