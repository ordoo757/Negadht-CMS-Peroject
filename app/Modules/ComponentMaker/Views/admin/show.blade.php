@extends('core::layouts.admin')

@section('title', 'جزئیات کامپوننت: ' . $component->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">جزئیات کامپوننت: {{ $component->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.component-maker.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                        <a href="{{ route('admin.component-maker.edit', $component->slug) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> ویرایش
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>نام</th>
                                    <td>{{ $component->name }}</td>
                                </tr>
                                <tr>
                                    <th>شناسه (Slug)</th>
                                    <td><code>{{ $component->slug }}</code></td>
                                </tr>
                                <tr>
                                    <th>دسته‌بندی</th>
                                    <td>{{ $component->category ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>نوع</th>
                                    <td>{{ $component->type }}</td>
                                </tr>
                                <tr>
                                    <th>نسخه</th>
                                    <td>{{ $component->version }}</td>
                                </tr>
                                <tr>
                                    <th>وضعیت</th>
                                    <td>
                                        <span class="badge badge-{{ $component->getStatusColor() }}">
                                            {{ $component->getStatusLabel() }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>لایسنس</th>
                                    <td>{{ $component->license ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>نویسنده</th>
                                    <td>{{ $component->author ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ایمیل نویسنده</th>
                                    <td>{{ $component->author_email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>وبسایت</th>
                                    <td>{{ $component->website ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>فعال</th>
                                    <td>
                                        @if($component->is_active)
                                            <span class="badge badge-success">بله</span>
                                        @else
                                            <span class="badge badge-danger">خیر</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>عمومی</th>
                                    <td>
                                        @if($component->is_public)
                                            <span class="badge badge-success">بله</span>
                                        @else
                                            <span class="badge badge-danger">خیر</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>نصب شده</th>
                                    <td>
                                        @if($component->isInstalled())
                                            <span class="badge badge-success">بله</span>
                                        @else
                                            <span class="badge badge-danger">خیر</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>توضیحات</h5>
                            <p>{{ $component->description ?? 'توضیحاتی وارد نشده است.' }}</p>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h5>تگ‌ها</h5>
                            <div>
                                @if($component->tags)
                                    @foreach($component->tags as $tag)
                                        <span class="badge badge-info">{{ $tag }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">هیچ تگی تعریف نشده است.</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>وابستگی‌ها</h5>
                            <div>
                                @if($component->dependencies)
                                    @foreach($component->dependencies as $dependency)
                                        <span class="badge badge-warning">{{ $dependency }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">هیچ وابستگی‌ای ندارد.</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-eye"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">بازدید</span>
                                    <span class="info-box-number">{{ $component->view_count }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-download"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">دانلود</span>
                                    <span class="info-box-number">{{ $component->download_count }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-calendar-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">ایجاد</span>
                                    <span class="info-box-number">{{ $component->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">آخرین بروزرسانی</span>
                                    <span class="info-box-number">{{ $component->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!empty($similar))
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>کامپوننت‌های مشابه</h5>
                            <div class="row">
                                @foreach($similar as $item)
                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>{{ $item['name'] }}</h6>
                                            <small class="text-muted">{{ $item['category'] ?? 'بدون دسته‌بندی' }}</small>
                                            <br>
                                            <a href="{{ route('admin.component-maker.show', $item['slug']) }}" class="btn btn-sm btn-primary mt-2">
                                                مشاهده
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
