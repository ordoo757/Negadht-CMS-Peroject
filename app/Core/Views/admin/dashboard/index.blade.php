@extends('core::admin.layouts.admin')

@section('title', 'داشبورد مدیریت')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">داشبورد مدیریت</h1>
        </div>
    </div>

    {{-- آمار --}}
    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-cubes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">ماژول‌ها</span>
                    <span class="info-box-number">{{ $totalModules }}</span>
                    <span class="info-box-text">فعال: {{ $activeModules }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-boxes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">کامپوننت‌ها</span>
                    <span class="info-box-number">{{ $totalComponents }}</span>
                    <span class="info-box-text">فعال: {{ $activeComponents }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-plug"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">پلاگین‌ها</span>
                    <span class="info-box-number">{{ $totalPlugins }}</span>
                    <span class="info-box-text">فعال: {{ $activePlugins }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">کاربران</span>
                    <span class="info-box-number">{{ $totalUsers }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- فعالیت‌های اخیر --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">فعالیت‌های اخیر</h3>
                </div>
                <div class="card-body">
                    @if(empty($recentActivities))
                        <p class="text-muted">هیچ فعالیتی ثبت نشده است.</p>
                    @else
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>کاربر</th>
                                    <th>عمل</th>
                                    <th>زمان</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivities as $activity)
                                <tr>
                                    <td>{{ $activity->user_id ?? 'سیستم' }}</td>
                                    <td>{{ $activity->action ?? 'نامشخص' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
