@extends('admin.default.index')

@section('page-title', 'مرکز امنیت')
@section('page-desc', 'نمایش و مدیریت وضعیت امنیتی سیستم')

@section('breadcrumb')
    <span class="separator"><i class="fas fa-chevron-left"></i></span>
    <span>امنیت</span>
@endsection

@section('content')
<!-- Security Overview -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div class="stat-info">
            <h3>امن</h3>
            <p>وضعیت کلی</p>
            <div class="stat-trend up">
                <i class="fas fa-check"></i>
                <span>بدون تهدید</span>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $failedLogins ?? 0 }}</h3>
            <p>ورود ناموفق ۲۴ ساعت</p>
            <div class="stat-trend {{ ($failedLogins ?? 0) > 10 ? 'down' : 'up' }}">
                <i class="fas fa-{{ ($failedLogins ?? 0) > 10 ? 'exclamation' : 'check' }}"></i>
                <span>{{ ($failedLogins ?? 0) > 10 ? 'نیاز به بررسی' : 'عادی' }}</span>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-ban"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $blockedIps ?? 0 }}</h3>
            <p>IP مسدود شده</p>
            <div class="stat-trend up">
                <i class="fas fa-shield-alt"></i>
                <span>محافظت فعال</span>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-user-clock"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $activeSessions ?? 1 }}</h3>
            <p>نشست‌های فعال</p>
            <div class="stat-trend up">
                <i class="fas fa-check"></i>
                <span>عادی</span>
            </div>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Security Settings -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-cog"></i> تنظیمات امنیتی</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.security.settings') }}">
                @csrf
                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="checkbox" name="two_factor" value="1" {{ ($security['two_factor'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                        <span>احراز هویت دو مرحله‌ای (2FA)</span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="checkbox" name="login_captcha" value="1" {{ ($security['login_captcha'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                        <span>کپچای ورود</span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="checkbox" name="ip_restriction" value="1" {{ ($security['ip_restriction'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                        <span>محدودیت IP</span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label">حداکثر تلاش ورود</label>
                    <input type="number" name="max_attempts" class="form-control" value="{{ $security['max_attempts'] ?? 5 }}" min="1" max="20">
                </div>
                <div class="form-group">
                    <label class="form-label">مدت زمان مسدودی (دقیقه)</label>
                    <input type="number" name="lockout_duration" class="form-control" value="{{ $security['lockout_duration'] ?? 30 }}" min="5" max="1440">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> ذخیره تنظیمات
                </button>
            </form>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-history"></i> فعالیت‌های اخیر</h3>
        </div>
        <div class="card-body">
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>عملیات</th>
                            <th>کاربر</th>
                            <th>IP</th>
                            <th>زمان</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities ?? [] as $activity)
                        <tr>
                            <td>
                                <span class="badge badge-{{ $activity['type'] == 'login' ? 'success' : ($activity['type'] == 'failed' ? 'danger' : 'info') }}">
                                    {{ $activity['action'] }}
                                </span>
                            </td>
                            <td>{{ $activity['user'] ?? 'ناشناس' }}</td>
                            <td><code>{{ $activity['ip'] }}</code></td>
                            <td>{{ $activity['time'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center" style="padding: 2rem; color: var(--text-muted);">
                                <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                                فعالیتی ثبت نشده
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Blocked IPs -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-ban"></i> IP‌های مسدود شده</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('blockIpModal')">
            <i class="fas fa-plus"></i> مسدود کردن IP
        </button>
    </div>
    <div class="card-body">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>IP</th>
                        <th>دلیل</th>
                        <th>مسدود شده توسط</th>
                        <th>تاریخ مسدودی</th>
                        <th>انقضا</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($blockedIpsList ?? [] as $ip)
                    <tr>
                        <td><code>{{ $ip['ip'] }}</code></td>
                        <td>{{ $ip['reason'] }}</td>
                        <td>{{ $ip['blocked_by'] }}</td>
                        <td>{{ $ip['created_at'] }}</td>
                        <td>{{ $ip['expires_at'] ?? 'دائمی' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.security.unblock', $ip['id']) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('آیا از رفع مسدودیت اطمینان دارید؟')">
                                    <i class="fas fa-unlock"></i> رفع مسدودیت
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 2rem; color: var(--text-muted);">
                            <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; color: var(--success);"></i>
                            هیچ IP مسدودی وجود ندارد
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Block IP Modal -->
<div class="modal-overlay" id="blockIpModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-ban"></i> مسدود کردن IP</h3>
            <button class="modal-close" onclick="closeModal('blockIpModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.security.block') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">آدرس IP</label>
                    <input type="text" name="ip" class="form-control" placeholder="192.168.1.1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">دلیل</label>
                    <input type="text" name="reason" class="form-control" placeholder="حمله brute force">
                </div>
                <div class="form-group">
                    <label class="form-label">مدت زمان (دقیقه، 0 = دائمی)</label>
                    <input type="number" name="duration" class="form-control" value="1440" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('blockIpModal')">انصراف</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-ban"></i> مسدود کردن
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
