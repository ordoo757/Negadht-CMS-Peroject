@extends('core::layouts.admin')

@section('title', 'مدیریت کامپوننت‌ها')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">لیست کامپوننت‌ها</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.component-maker.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> ایجاد کامپوننت جدید
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- فیلترها --}}
                    <form method="GET" action="{{ route('admin.component-maker.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="جستجو..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="category" class="form-control">
                                    <option value="">همه دسته‌بندی‌ها</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">همه وضعیت‌ها</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                                    <option value="stable" {{ request('status') == 'stable' ? 'selected' : '' }}>پایدار</option>
                                    <option value="beta" {{ request('status') == 'beta' ? 'selected' : '' }}>بتا</option>
                                    <option value="alpha" {{ request('status') == 'alpha' ? 'selected' : '' }}>آلفا</option>
                                    <option value="deprecated" {{ request('status') == 'deprecated' ? 'selected' : '' }}>منسوخ</option>
                                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>بایگانی</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="type" class="form-control">
                                    <option value="">همه انواع</option>
                                    <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>سفارشی</option>
                                    <option value="layout" {{ request('type') == 'layout' ? 'selected' : '' }}>لایه</option>
                                    <option value="widget" {{ request('type') == 'widget' ? 'selected' : '' }}>ویجت</option>
                                    <option value="module" {{ request('type') == 'module' ? 'selected' : '' }}>ماژول</option>
                                    <option value="plugin" {{ request('type') == 'plugin' ? 'selected' : '' }}>پلاگین</option>
                                    <option value="theme" {{ request('type') == 'theme' ? 'selected' : '' }}>قالب</option>
                                    <option value="template" {{ request('type') == 'template' ? 'selected' : '' }}>تمپلیت</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary">فیلتر</button>
                            </div>
                            <div class="col-md-1">
                                <a href="{{ route('admin.component-maker.index') }}" class="btn btn-secondary">پاک کردن</a>
                            </div>
                        </div>
                    </form>

                    {{-- آمار --}}
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-cubes"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">کل</span>
                                    <span class="info-box-number">{{ $stats['total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">فعال</span>
                                    <span class="info-box-number">{{ $stats['active'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-star"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">سیستمی</span>
                                    <span class="info-box-number">{{ $stats['system'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-globe"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">عمومی</span>
                                    <span class="info-box-number">{{ $stats['public'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-trash"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">دسته‌بندی</span>
                                    <span class="info-box-number">{{ count($stats['categories']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- جدول کامپوننت‌ها --}}
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نام</th>
                                <th>دسته‌بندی</th>
                                <th>نوع</th>
                                <th>نسخه</th>
                                <th>وضعیت</th>
                                <th>فعال</th>
                                <th>بازدید</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($components as $component)
                            <tr>
                                <td>{{ $component->id }}</td>
                                <td>
                                    <strong>{{ $component->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $component->slug }}</small>
                                </td>
                                <td>{{ $component->category ?? '-' }}</td>
                                <td>{{ $component->type }}</td>
                                <td>{{ $component->version }}</td>
                                <td>
                                    <span class="badge badge-{{ $component->getStatusColor() }}">
                                        {{ $component->getStatusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    @if($component->is_active)
                                        <span class="badge badge-success">فعال</span>
                                    @else
                                        <span class="badge badge-danger">غیرفعال</span>
                                    @endif
                                </td>
                                <td>{{ $component->view_count }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.component-maker.show', $component->slug) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.component-maker.edit', $component->slug) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.component-maker.destroy', $component->slug) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('آیا مطمئن هستید؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @if($component->isInstalled())
                                            <form action="{{ route('admin.component-maker.uninstall', $component->slug) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('آیا از حذف نصب مطمئن هستید؟')">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.component-maker.install', $component->slug) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.component-maker.export', $component->slug) }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-file-export"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">هیچ کامپوننتی یافت نشد.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- صفحه‌بندی --}}
                    <div class="mt-3">
                        {{ $components->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

