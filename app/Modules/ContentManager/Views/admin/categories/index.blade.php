@extends('core::layouts.admin')

@section('title', 'مدیریت دسته‌بندی‌ها')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">مدیریت دسته‌بندی‌ها</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.content-manager.categories.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> دسته‌بندی جدید
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- آمار --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-tags"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">کل دسته‌بندی‌ها</span>
                                    <span class="info-box-number">{{ $stats['total'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">فعال</span>
                                    <span class="info-box-number">{{ $stats['active'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">غیرفعال</span>
                                    <span class="info-box-number">{{ $stats['inactive'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- جدول --}}
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نام</th>
                                <th>شناسه</th>
                                <th>توضیحات</th>
                                <th>زیردسته</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories ?? [] as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td><strong>{{ $category->name }}</strong></td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td>{{ Str::limit($category->description, 50) }}</td>
                                <td>{{ $category->parent->name ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $category->is_active ? 'success' : 'danger' }}">
                                        {{ $category->is_active ? 'فعال' : 'غیرفعال' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.content-manager.categories.edit', $category->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.content-manager.categories.toggle', $category->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-{{ $category->is_active ? 'warning' : 'success' }} btn-sm">
                                                <i class="fas fa-{{ $category->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.content-manager.categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('آیا مطمئن هستید؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">هیچ دسته‌بندی‌ای یافت نشد.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
