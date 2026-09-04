@extends('core::layouts.admin')

@section('title', 'گزارش‌های اسکن')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">گزارش‌های اسکن</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.antivirus.scan') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> اسکن جدید
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- آمار --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">کل گزارش‌ها</span>
                                    <span class="info-box-number">{{ $stats['total_scans'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">تکمیل شده</span>
                                    <span class="info-box-number">{{ $stats['completed_scans'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-bug"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">فایل‌های آلوده</span>
                                    <span class="info-box-number">{{ $stats['infected_files'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">در حال اجرا</span>
                                    <span class="info-box-number">{{ $reports->where('status', 'running')->count() ?? 0 }}</span>
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
                                <select name="type" class="form-control">
                                    <option value="">همه انواع</option>
                                    <option value="full" {{ request('type') == 'full' ? 'selected' : '' }}>کامل</option>
                                    <option value="quick" {{ request('type') == 'quick' ? 'selected' : '' }}>سریع</option>
                                    <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>دلخواه</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">همه وضعیت‌ها</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>در انتظار</option>
                                    <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>در حال اجرا</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>تکمیل شده</option>
                                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>ناموفق</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">فیلتر</button>
                                <a href="{{ route('admin.antivirus.reports') }}" class="btn btn-secondary">پاک کردن</a>
                            </div>
                        </div>
                    </form>

                    {{-- جدول --}}
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نوع</th>
                                <th>وضعیت</th>
                                <th>فایل‌ها</th>
                                <th>آلوده</th>
                                <th>زمان</th>
                                <th>مدت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports ?? [] as $report)
                            <tr>
                                <td>{{ $report->id }}</td>
                                <td>{{ $report->type_label }}</td>
                                <td>
                                    <span class="badge badge-{{ $report->status_color }}">
                                        {{ $report->status_label }}
                                    </span>
                                </td>
                                <td>{{ $report->scanned_count }}</td>
                                <td>
                                    @if($report->infected_count > 0)
                                        <span class="badge badge-danger">{{ $report->infected_count }}</span>
                                    @else
                                        <span class="badge badge-success">۰</span>
                                    @endif
                                </td>
                                <td>{{ $report->created_at->diffForHumans() }}</td>
                                <td>{{ $report->duration ? round($report->duration) . 's' : '-' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.antivirus.report-show', $report->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($report->status === 'completed' && $report->infected_count > 0)
                                            <a href="{{ route('admin.antivirus.report-export', $report->id) }}" class="btn btn-success btn-sm">
                                                <i class="fas fa-file-export"></i>
                                            </a>
                                        @endif
                                        @if($report->status === 'running')
                                            <form action="{{ route('admin.antivirus.cancel-scan', $report->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('آیا از لغو اسکن مطمئن هستید؟')">
                                                    <i class="fas fa-stop"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.antivirus.delete-report', $report->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('آیا از حذف گزارش مطمئن هستید؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">هیچ گزارش اسکنی یافت نشد.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- صفحه‌بندی --}}
                    <div class="mt-3">
                        {{ $reports->links() ?? '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
