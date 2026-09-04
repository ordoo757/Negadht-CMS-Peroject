@extends('core::layouts.admin')

@section('title', 'جزئیات گزارش اسکن')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">جزئیات گزارش اسکن</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.antivirus.reports') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- اطلاعات اصلی --}}
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>نوع اسکن</th>
                                    <td>{{ $report->type_label }}</td>
                                </tr>
                                <tr>
                                    <th>وضعیت</th>
                                    <td>
                                        <span class="badge badge-{{ $report->status_color }}">
                                            {{ $report->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>مسیر</th>
                                    <td><code>{{ $report->path ?? 'نامشخص' }}</code></td>
                                </tr>
                                <tr>
                                    <th>تاریخ شروع</th>
                                    <td>{{ $report->started_at ? $report->started_at->format('Y/m/d H:i:s') : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>تاریخ پایان</th>
                                    <td>{{ $report->completed_at ? $report->completed_at->format('Y/m/d H:i:s') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>مدت زمان</th>
                                    <td>{{ $report->duration ? round($report->duration) . ' ثانیه' : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>کل فایل‌ها</th>
                                    <td>{{ $report->file_count }}</td>
                                </tr>
                                <tr>
                                    <th>فایل‌های اسکن شده</th>
                                    <td>{{ $report->scanned_count }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- خلاصه --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-{{ $report->infected_count > 0 ? 'danger' : 'success' }}">
                                <i class="fas fa-{{ $report->infected_count > 0 ? 'exclamation-triangle' : 'check-circle' }}"></i>
                                <strong>نتیجه اسکن:</strong>
                                {{ $report->infected_count > 0 
                                    ? "{$report->infected_count} فایل آلوده شناسایی شد." 
                                    : "همه فایل‌ها سالم هستند. ✓" }}
                            </div>
                        </div>
                    </div>

                    {{-- فایل‌های آلوده --}}
                    @if($report->infected_count > 0 && isset($report->result['infected_files']))
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>فایل‌های آلوده</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>مسیر فایل</th>
                                            <th>ویروس</th>
                                            <th>شدت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report->result['infected_files'] as $index => $file)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><code>{{ $file['path'] ?? 'نامشخص' }}</code></td>
                                            <td>{{ $file['matches'][0]['name'] ?? 'نامشخص' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $file['matches'][0]['severity'] === 'critical' ? 'dark' : ($file['matches'][0]['severity'] === 'high' ? 'danger' : ($file['matches'][0]['severity'] === 'medium' ? 'warning' : 'info')) }}">
                                                    {{ $file['matches'][0]['severity'] ?? 'نامشخص' }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- عملیات --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            @if($report->status === 'completed' && $report->infected_count > 0)
                                <a href="{{ route('admin.antivirus.report-export', $report->id) }}" class="btn btn-success">
                                    <i class="fas fa-file-export"></i> خروجی CSV
                                </a>
                            @endif
                            @if($report->status === 'running')
                                <form action="{{ route('admin.antivirus.cancel-scan', $report->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-stop"></i> لغو اسکن
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('admin.antivirus.delete-report', $report->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('آیا از حذف گزارش مطمئن هستید؟')">
                                    <i class="fas fa-trash"></i> حذف گزارش
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
