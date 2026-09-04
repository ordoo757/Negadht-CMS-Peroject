@extends('admin.default.index')

@section('page-title', 'تنظیمات سیستم')
@section('page-desc', 'مدیریت نام سایت، وضعیت و تنظیمات پایه')

@section('breadcrumb')
    <span class="separator"><i class="fas fa-chevron-left"></i></span>
    <a href="{{ route('admin.system.settings') }}">سیستم</a>
    <span class="separator"><i class="fas fa-chevron-left"></i></span>
    <span>تنظیمات سایت</span>
@endsection

@section('page-actions')
    <button type="submit" form="settingsForm" class="btn btn-primary">
        <i class="fas fa-save"></i> ذخیره تغییرات
    </button>
@endsection

@section('content')
<form id="settingsForm" method="POST" action="{{ route('admin.system.settings.save') }}">
    @csrf
    
    <div class="grid-2">
        <!-- General Settings -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-cog"></i> تنظیمات عمومی</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">نام سایت <span class="required"></span></label>
                    <input type="text" name="site_name" class="form-control" value="{{ $settings['site_name'] ?? 'NeuroCMS' }}" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">توضیحات سایت</label>
                    <textarea name="site_description" class="form-control" rows="3">{{ $settings['site_description'] ?? '' }}</textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">کلمات کلیدی (با کاما جدا کنید)</label>
                    <input type="text" name="site_keywords" class="form-control" value="{{ $settings['site_keywords'] ?? '' }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">ایمیل مدیر</label>
                    <input type="email" name="admin_email" class="form-control" value="{{ $settings['admin_email'] ?? '' }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">لوگوی سایت</label>
                    <input type="file" name="site_logo" class="form-control" accept="image/*">
                    @if($settings['site_logo'] ?? false)
                        <img src="{{ asset($settings['site_logo']) }}" style="max-height: 60px; margin-top: 0.5rem; border-radius: 8px;">
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Site Status -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-power-off"></i> وضعیت سایت</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="checkbox" name="site_active" value="1" {{ ($settings['site_active'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                        <span>سایت فعال باشد</span>
                    </label>
                    <p class="form-hint">با غیرفعال کردن، سایت برای کاربران غیرمدیر غیرقابل دسترس می‌شود</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">پیام غیرفعال بودن سایت</label>
                    <textarea name="maintenance_message" class="form-control" rows="3">{{ $settings['maintenance_message'] ?? 'سایت در حال بروزرسانی است. لطفاً بعداً مراجعه کنید.' }}</textarea>
                </div>
                
                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="checkbox" name="registration_enabled" value="1" {{ ($settings['registration_enabled'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                        <span>ثبت‌نام کاربران فعال باشد</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="checkbox" name="email_verification" value="1" {{ ($settings['email_verification'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                        <span>تأیید ایمیل الزامی باشد</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid-2">
        <!-- Regional Settings -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-globe"></i> تنظیمات منطقه‌ای</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">زبان پیش‌فرض</label>
                        <select name="default_language" class="form-select">
                            <option value="fa" {{ ($settings['default_language'] ?? 'fa') == 'fa' ? 'selected' : '' }}>🇮🇷 فارسی</option>
                            <option value="ar" {{ ($settings['default_language'] ?? 'fa') == 'ar' ? 'selected' : '' }}>🇸🇦 العربية</option>
                            <option value="en" {{ ($settings['default_language'] ?? 'fa') == 'en' ? 'selected' : '' }}>🇬🇧 English</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">منطقه زمانی</label>
                        <select name="timezone" class="form-select">
                            <option value="Asia/Tehran" {{ ($settings['timezone'] ?? 'Asia/Tehran') == 'Asia/Tehran' ? 'selected' : '' }}>تهران (Asia/Tehran)</option>
                            <option value="Asia/Dubai" {{ ($settings['timezone'] ?? '') == 'Asia/Dubai' ? 'selected' : '' }}>دبی (Asia/Dubai)</option>
                            <option value="Asia/Riyadh" {{ ($settings['timezone'] ?? '') == 'Asia/Riyadh' ? 'selected' : '' }}>ریاض (Asia/Riyadh)</option>
                            <option value="UTC" {{ ($settings['timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">فرمت تاریخ</label>
                        <select name="date_format" class="form-select">
                            <option value="Y/m/d" {{ ($settings['date_format'] ?? 'Y/m/d') == 'Y/m/d' ? 'selected' : '' }}>۱۴۰۳/۰۱/۰۱</option>
                            <option value="d/m/Y" {{ ($settings['date_format'] ?? '') == 'd/m/Y' ? 'selected' : '' }}>۰۱/۰۱/۱۴۰۳</option>
                            <option value="Y-m-d" {{ ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' }}>۱۴۰۳-۰۱-۰۱</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">فرمت زمان</label>
                        <select name="time_format" class="form-select">
                            <option value="H:i" {{ ($settings['time_format'] ?? 'H:i') == 'H:i' ? 'selected' : '' }}>۲۴ ساعته (۱۴:۳۰)</option>
                            <option value="h:i A" {{ ($settings['time_format'] ?? '') == 'h:i A' ? 'selected' : '' }}>۱۲ ساعته (۰۲:۳۰ ب.ظ)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Advanced Settings -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-sliders-h"></i> تنظیمات پیشرفته</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">تعداد آیتم در هر صفحه</label>
                    <input type="number" name="per_page" class="form-control" value="{{ $settings['per_page'] ?? 20 }}" min="5" max="100">
                </div>
                
                <div class="form-group">
                    <label class="form-label">حداکثر حجم آپلود (MB)</label>
                    <input type="number" name="max_upload_size" class="form-control" value="{{ $settings['max_upload_size'] ?? 10 }}" min="1" max="100">
                </div>
                
                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="checkbox" name="cache_enabled" value="1" {{ ($settings['cache_enabled'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                        <span>کش سیستم فعال باشد</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="checkbox" name="debug_mode" value="1" {{ ($settings['debug_mode'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                        <span>حالت Debug (فقط توسعه)</span>
                    </label>
                    <p class="form-hint" style="color: var(--danger);">⚠️ در محیط تولید غیرفعال کنید</p>
                </div>
                
                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="checkbox" name="force_https" value="1" {{ ($settings['force_https'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                        <span>اجبار به HTTPS</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
