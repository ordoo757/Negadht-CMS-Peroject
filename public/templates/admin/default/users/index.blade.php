@extends('admin.default.index')

@section('page-title', 'مدیریت کاربران')
@section('page-desc', 'مشاهده، ویرایش و مدیریت کاربران سیستم')

@section('breadcrumb')
    <span class="separator"><i class="fas fa-chevron-left"></i></span>
    <span>کاربران</span>
@endsection

@section('page-actions')
    <button class="btn btn-primary" onclick="openModal('addUserModal')">
        <i class="fas fa-plus"></i> کاربر جدید
    </button>
@endsection

@section('content')
<!-- Filters -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <form method="GET" action="{{ route('admin.user.index') }}" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="margin-bottom: 0; min-width: 200px;">
                <label class="form-label" style="font-size: 0.8rem;">جستجو</label>
                <input type="text" name="search" class="form-control" placeholder="نام، ایمیل..." value="{{ request('search') }}">
            </div>
            <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                <label class="form-label" style="font-size: 0.8rem;">نقش</label>
                <select name="role" class="form-select">
                    <option value="">همه</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>مدیر</option>
                    <option value="editor" {{ request('role') == 'editor' ? 'selected' : '' }}>ویرایشگر</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>کاربر</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                <label class="form-label" style="font-size: 0.8rem;">وضعیت</label>
                <select name="status" class="form-select">
                    <option value="">همه</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>فعال</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary" style="margin-bottom: 0;">
                <i class="fas fa-filter"></i> فیلتر
            </button>
            <a href="{{ route('admin.user.index') }}" class="btn btn-ghost" style="margin-bottom: 0;">
                <i class="fas fa-times"></i> پاک کردن
            </a>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>کاربر</th>
                        <th>ایمیل</th>
                        <th>نقش</th>
                        <th>وضعیت</th>
                        <th>تاریخ ثبت</th>
                        <th>آخرین ورود</th>
                        <th style="width: 120px;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <img src="{{ $user->avatar ?? asset('assets/admin/images/default-avatar.png') }}" 
                                     style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
                                <div>
                                    <strong style="font-size: 0.9rem;">{{ $user->name }}</strong>
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge badge-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'editor' ? 'warning' : 'secondary') }}">
                                {{ $user->role == 'admin' ? 'مدیر کل' : ($user->role == 'editor' ? 'ویرایشگر' : 'کاربر') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $user->is_active ? 'success' : 'secondary' }}">
                                {{ $user->is_active ? 'فعال' : 'غیرفعال' }}
                            </span>
                        </td>
                        <td>{{ $user->created_at ? $user->created_at->format('Y/m/d') : '-' }}</td>
                        <td>{{ $user->last_login ? $user->last_login->format('Y/m/d H:i') : '-' }}</td>
                        <td>
                            <div class="table-actions">
                                <button class="btn-icon" title="ویرایش" onclick="openModal('editUserModal{{ $user->id }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon" title="دسترسی‌ها">
                                    <i class="fas fa-key"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.user.destroy', $user->id) }}" style="display: inline;" id="deleteForm{{ $user->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn-icon danger" title="حذف" onclick="confirmDelete('آیا از حذف این کاربر اطمینان دارید؟', 'deleteForm{{ $user->id }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 3rem; color: var(--text-muted);">
                            <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                            هیچ کاربری یافت نشد
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(($users ?? collect())->hasPages())
    <div class="card-footer">
        {{ $users->links() }}
    </div>
    @endif
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> افزودن کاربر جدید</h3>
            <button class="modal-close" onclick="closeModal('addUserModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.user.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">نام <span class="required"></span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">ایمیل <span class="required"></span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">رمز عبور <span class="required"></span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">تأیید رمز عبور <span class="required"></span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">نقش</label>
                        <select name="role" class="form-select">
                            <option value="user">کاربر</option>
                            <option value="editor">ویرایشگر</option>
                            <option value="admin">مدیر</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">وضعیت</label>
                        <select name="is_active" class="form-select">
                            <option value="1">فعال</option>
                            <option value="0">غیرفعال</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">انصراف</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> ذخیره
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
