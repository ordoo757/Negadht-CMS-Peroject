@extends('core::layouts.admin')

@section('title', $presentation->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $presentation->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.advanced-powerpoint.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                        <a href="{{ route('admin.advanced-powerpoint.edit', $presentation->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> ویرایش
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- اطلاعات --}}
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>نام</th>
                                    <td>{{ $presentation->name }}</td>
                                </tr>
                                <tr>
                                    <th>توضیحات</th>
                                    <td>{{ $presentation->description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>تم</th>
                                    <td>{{ $presentation->theme }}</td>
                                </tr>
                                <tr>
                                    <th>وضعیت</th>
                                    <td>
                                        <span class="badge badge-{{ $presentation->is_active ? 'success' : 'danger' }}">
                                            {{ $presentation->is_active ? 'فعال' : 'غیرفعال' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>تعداد اسلایدها</th>
                                    <td>{{ $presentation->slides->count() }}</td>
                                </tr>
                                <tr>
                                    <th>تعداد عناصر</th>
                                    <td>{{ $presentation->slides->sum(function($s) { return $s->elements->count(); }) }}</td>
                                </tr>
                                <tr>
                                    <th>دسترسی</th>
                                    <td>
                                        <span class="badge badge-{{ $presentation->is_public ? 'info' : 'secondary' }}">
                                            {{ $presentation->is_public ? 'عمومی' : 'خصوصی' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>بازدید</th>
                                    <td>{{ $presentation->view_count }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- اسلایدها --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>اسلایدها</h5>
                            <div class="row">
                                @forelse($presentation->slides as $slide)
                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <div class="slide-preview" style="background: {{ $slide->background ?? '#f5f5f5' }}; min-height: 150px; border-radius: 4px; padding: 10px;">
                                                <div class="slide-number">اسلاید {{ $loop->iteration }}</div>
                                                <div class="slide-title">{{ $slide->title ?? 'بدون عنوان' }}</div>
                                                <small class="text-muted">{{ $slide->elements->count() }} عنصر</small>
                                            </div>
                                            <div class="mt-2">
                                                <span class="badge badge-info">{{ $slide->layout }}</span>
                                                @if($slide->transition)
                                                    <span class="badge badge-secondary">{{ $slide->transition }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12">
                                    <p class="text-center text-muted">هیچ اسلایدی وجود ندارد.</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.slide-preview {
    position: relative;
    overflow: hidden;
}
.slide-number {
    font-size: 12px;
    color: #999;
}
.slide-title {
    font-weight: bold;
    margin-top: 10px;
}
</style>
@endpush
