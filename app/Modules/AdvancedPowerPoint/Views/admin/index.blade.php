@extends('core::layouts.admin')

@section('title', 'مدیریت ارائه‌های پاورپوینت')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">مدیریت ارائه‌های پاورپوینت</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.advanced-powerpoint.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> ارائه جدید
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- آمار --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-file-powerpoint"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">کل ارائه‌ها</span>
                                    <span class="info-box-number">{{ $stats['total_presentations'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">فعال</span>
                                    <span class="info-box-number">{{ $stats['active_presentations'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-layer-group"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">اسلایدها</span>
                                    <span class="info-box-number">{{ $stats['total_slides'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-cubes"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">عناصر</span>
                                    <span class="info-box-number">{{ $stats['total_elements'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- فیلترها --}}
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="جستجو..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="is_active" class="form-control">
                                    <option value="">همه وضعیت‌ها</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>فعال</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غیرفعال</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="is_public" class="form-control">
                                    <option value="">همه</option>
                                    <option value="1" {{ request('is_public') == '1' ? 'selected' : '' }}>عمومی</option>
                                    <option value="0" {{ request('is_public') == '0' ? 'selected' : '' }}>خصوصی</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">فیلتر</button>
                                <a href="{{ route('admin.advanced-powerpoint.index') }}" class="btn btn-secondary">پاک کردن</a>
                            </div>
                        </div>
                    </form>

                    {{-- جدول --}}
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نام</th>
                                <th>اسلایدها</th>
                                <th>عناصر</th>
                                <th>وضعیت</th>
                                <th>عمومی</th>
                                <th>بازدید</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($presentations ?? [] as $presentation)
                            <tr>
                                <td>{{ $presentation->id }}</td>
                                <td>
                                    <strong>{{ $presentation->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ Str::limit($presentation->description, 30) }}</small>
                                </td>
                                <td>{{ $presentation->slides->count() }}</td>
                                <td>{{ $presentation->slides->sum(function($s) { return $s->elements->count(); }) }}</td>
                                <td>
                                    <span class="badge badge-{{ $presentation->is_active ? 'success' : 'danger' }}">
                                        {{ $presentation->is_active ? 'فعال' : 'غیرفعال' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $presentation->is_public ? 'info' : 'secondary' }}">
                                        {{ $presentation->is_public ? 'عمومی' : 'خصوصی' }}
                                    </span>
                                </td>
                                <td>{{ $presentation->view_count }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.advanced-powerpoint.show', $presentation->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.advanced-powerpoint.edit', $presentation->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.advanced-powerpoint.embed', $presentation->id) }}" class="btn btn-success btn-sm" target="_blank">
                                            <i class="fas fa-code"></i>
                                        </a>
                                        <form action="{{ route('admin.advanced-powerpoint.destroy', $presentation->id) }}" method="POST" class="d-inline">
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
                                <td colspan="8" class="text-center">هیچ ارائه‌ای یافت نشد.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- صفحه‌بندی --}}
                    <div class="mt-3">
                        {{ $presentations->links() ?? '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
