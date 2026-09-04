@extends('admin.default.index')

@section('page-title', 'مدیریت نقش‌ها')
@section('page-desc', 'تعریف و مدیریت نقش‌های کاربری')

@section('breadcrumb')
    <span class="separator"><i class="fas fa-chevron-left"></i></span>
    <a href="{{ route('admin.user.index') }}">کاربران</a>
    <span class="separator"><i class="fas fa-chevron-left"></i></span>
    <span>نقش‌ها</span>
@endsection

@section('page-actions')
    <button class="btn btn-primary" onclick="openModal('addRoleModal')">
        <i class="fas fa-plus"></i> نقش جدید
    </button>
@endsection

@section('content')
<div class="grid-3">
    @forelse($roles ?? [] as $role)
    <div class="card">
        <div class="card-header" style="justify-content: flex-start; gap: 0.75rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                <i class="fas fa-user-tag"></i>
            </div>
            <div>
                <h3 style="margin: 0;">{{ $role->name }}</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">{{ $role->slug }}</span>
            </div>
        </div>
        <div class="card-body">
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">{{ $role->description ?? 'بدون توضیحات' }}</p>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
                <span class="badge badge-secondary">{{ $role->users_count ?? 0 }} کاربر</span>
                <span class="badge badge-info">{{ $role->permissions_count ?? 0 }} مجوز</span>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-secondary btn-sm" style="flex: 1;" onclick="openModal('editRoleModal{{ $role->id }}')">
                    <i class="fas fa-edit"></i> ویرایش
                </button>
                <button class="btn btn-primary btn-sm" style="flex: 1;">
                    <i class="fas fa-key"></i> مجوزها
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="card" style="grid-column: span 3;">
        <div class="card-body text-center" style="padding: 3rem;">
            <i class="fas fa-user-tag" style="font-size: 3rem; color: var(--gray-300); margin-bottom: 1rem; display: block;"></i>
            <p style="color: var(--text-muted);">هیچ نقشی تعریف نشده</p>
        </div>
    </div>
    @endforelse
</div>

<!-- Default Roles Info -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-info-circle"></i> نقش‌های پیش‌فرض سیستم</h3>
    </div>
    <div class="card-body">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>نقش</th>
                        <th>slug</th>
                        <th>دسترسی‌ها</th>
                        <th>توضیحات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="badge badge-danger">مدیر کل</span></td>
                        <td><code>super-admin</code></td>
                        <td>تمام دسترسی‌ها</td>
                        <td>کنترل کامل سیستم</td>
                    </tr>
                    <tr>
                        <td><span class="badge badge-warning">مدیر</span></td>
                        <td><code>admin</code></td>
                        <td>مدیریت محتوا و کاربران</td>
                        <td>دسترسی به بخش مدیریت</td>
                    </tr>
                    <tr>
                        <td><span class="badge badge-info">ویرایشگر</span></td>
                        <td><code>editor</code></td>
                        <td>مدیریت محتوا</td>
                        <td>ویرایش و انتشار محتوا</td>
                    </tr>
                    <tr>
                        <td><span class="badge badge-secondary">کاربر</span></td>
                        <td><code>user</code></td>
                        <td>مشاهده پروفایل</td>
                        <td>دسترسی محدود به سایت</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Role Modal -->
<div class="modal-overlay" id="addRoleModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> افزودن نقش</h3>
            <button class="modal-close" onclick="closeModal('addRoleModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('admin.user.roles.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">نام نقش</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">شناسه (slug)</label>
                    <input type="text" name="slug" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">توضیحات</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addRoleModal')">انصراف</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> ذخیره</button>
            </div>
        </form>
    </div>
</div>
@endsection
