@extends('core::layouts.admin')

@section('title', 'داشبورد ویروس‌یابی')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h4 class="mb-4">داشبورد ویروس‌یابی و اسکن امنیتی</h4>
        </div>
    </div>

    {{-- آمار --}}
    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-shield-virus"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">کل اسکن‌ها</span>
                    <span class="info-box-number">{{ $stats['total_scans'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">اسکن‌های تکمیل شده</span>
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
                <span class="info-box-icon bg-warning"><i class="fas fa-lock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">فایل‌های قرنطینه</span>
                    <span class="info-box-number">{{ $stats['quarantined_files'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- تعاریف ویروس و آخرین اسکن --}}
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">تعاریف ویروس‌ها</h5>
                    <span class="badge badge-primary float-left">{{ $stats['virus_definitions'] ?? 0 }} تعریف</span>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check-circle text-success"></i> تعاریف فعال: {{ $stats['virus_definitions'] ?? 0 }}</li>
                        <li><i class="fas fa-clock text-warning"></i> آخرین بروزرسانی: {{ now()->diffForHumans() }}</li>
                    </ul>
                    <a href="{{ route('admin.antivirus.scan') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-play"></i> اسکن جدید
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">آخرین اسکن</h5>
                </div>
                <div class="card-body">
                    @if(isset($stats['last_scan']) && $stats['last_scan'])
                        <ul class="list-unstyled">
                            <li><strong>نوع:</strong> {{ $stats['last_scan']->type_label }}</li>
                            <li><strong>وضعیت:</strong> <span class="badge badge-{{ $stats['last_scan']->status_color }}">{{ $stats['last_scan']->status_label }}</span></li>
                            <li><strong>فایل‌های اسکن شده:</strong> {{ $stats['last_scan']->scanned_count }}</li>
                            <li><strong>فایل‌های آلوده:</strong> {{ $stats['last_scan']->infected_count }}</li>
                            <li><strong>زمان:</strong> {{ $stats['last_scan']->created_at->diffForHumans() }}</li>
                        </ul>
                        <a href="{{ route('admin.antivirus.report-show', $stats['last_scan']->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> مشاهده گزارش
                        </a>
                    @else
                        <p class="text-muted">هیچ اسکنی انجام نشده است.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- اسکن‌های اخیر --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">اسکن‌های اخیر</h5>
                    <a href="{{ route('admin.antivirus.reports') }}" class="btn btn-sm btn-primary float-left">مشاهده همه</a>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>نوع</th>
                                <th>وضعیت</th>
                                <th>فایل‌ها</th>
                                <th>آلوده</th>
                                <th>زمان</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentReports ?? [] as $report)
                            <tr>
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
                                <td>
                                    <a href="{{ route('admin.antivirus.report-show', $report->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center">هیچ اسکنی یافت نشد.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
