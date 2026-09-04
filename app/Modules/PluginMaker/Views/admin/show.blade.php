@extends('core::layouts.admin')

@section('title', 'جزئیات پلاگین: ' . $plugin->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">جزئیات پلاگین: {{ $plugin->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.plugin-maker.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                        <a href="{{ route('admin.plugin-maker.edit', $plugin->slug) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> ویرایش
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr><th>نام</th><td>{{ $plugin->name }}</td></tr>
                                <tr><th>شناسه</th><td><code>{{ $plugin->slug }}</code></td></tr>
                                <tr><th>دسته‌بندی</th><td>{{ $plugin->category ?? '-' }}</td></tr>
                                <tr><th>نوع</th><td>{{ $plugin->type }}</td></tr>
                                <tr><th>نسخه</th><td>{{ $plugin->version }}</td></tr>
                                <tr><th>وضعیت</th><td><span class="badge badge-{{ $plugin->getStatusColor() }}">{{ $plugin->getStatusLabel() }}</span></td></tr>
                                <tr><th>لایسنس</th><td>{{ $plugin->license ?? '-' }}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr><th>قیمت</th><td>{{ $plugin->getPriceLabel() }}</td></tr>
                                <tr><th>نویسنده</th><td>{{ $plugin->author ?? '-' }}</td></tr>
                                <tr><th>ایمیل</th><td>{{ $plugin->author_email ?? '-' }}</td></tr>
                                <tr><th>فعال</th><td>@if($plugin->is_active) <span class="badge badge-success">بله</span> @else <span class="badge badge-danger">خیر</span> @endif</td></tr>
                                <tr><th>نصب شده</th><td>@if($plugin->isInstalled()) <span class="badge badge-success">بله</span> @else <span class="badge badge-danger">خیر</span> @endif</td></tr>
                                <tr><th>بازدید</th><td>{{ $plugin->view_count }}</td></tr>
                                <tr><th>دانلود</th><td>{{ $plugin->download_count }}</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-3"><div class="col-12"><h5>توضیحات</h5><p>{{ $plugin->description ?? 'توضیحاتی وارد نشده است.' }}</p></div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
