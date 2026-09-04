@extends('core::layouts.admin')

@section('title', 'مدیریت پلاگین‌ها')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">لیست پلاگین‌ها</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.plugin-maker.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> ایجاد پلاگین جدید
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- آمار --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-plug"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">کل پلاگین‌ها</span>
                                    <span class="info-box-number">{{ $stats['total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">فعال</span>
                                    <span class="info-box-number">{{ $stats['active'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-gem"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">نصب شده</span>
                                    <span class="info-box-number">{{ $stats['installed'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-dollar-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">پولی / رایگان</span>
                                    <span class="info-box-number">{{ $stats['paid'] }} / {{ $stats['free'] }}</span>
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
                                <th>دسته‌بندی</th>
                                <th>نوع</th>
                                <th>نسخه</th>
                                <th>قیمت</th>
                                <th>وضعیت</th>
                                <th>فعال</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plugins as $plugin)
                            <tr>
                                <td>{{ $plugin->id }}</td>
                                <td>
                                    <strong>{{ $plugin->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $plugin->slug }}</small>
                                </td>
                                <td>{{ $plugin->category ?? '-' }}</td>
                                <td>{{ $plugin->type }}</td>
                                <td>{{ $plugin->version }}</td>
                                <td>{{ $plugin->getPriceLabel() }}</td>
                                <td>
                                    <span class="badge badge-{{ $plugin->getStatusColor() }}">
                                        {{ $plugin->getStatusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    @if($plugin->is_active)
                                        <span class="badge badge-success">فعال</span>
                                    @else
                                        <span class="badge badge-danger">غیرفعال</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.plugin-maker.show', $plugin->slug) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.plugin-maker.edit', $plugin->slug) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.plugin-maker.destroy', $plugin->slug) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('آیا مطمئن هستید؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="text-center">هیچ پلاگینی یافت نشد.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">{{ $plugins->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
