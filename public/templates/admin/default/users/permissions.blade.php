@extends('admin.default.index')

@section('page-title', 'مدیریت مجوزها')
@section('page-desc', 'تعریف و تخصیص مجوزهای دسترسی')

@section('breadcrumb')
    <span class="separator"><i class="fas fa-chevron-left"></i></span>
    <a href="{{ route('admin.user.index') }}">کاربران</a>
    <span class="separator"><i class="fas fa-chevron-left"></i></span>
    <span>مجوزها</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-key"></i> لیست مجوزها</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addPermissionModal')">
            <i class="fas fa-plus"></i> مجوز جدید
        </button>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>مجوز</th>
                        <th>شناسه</th>
                        <th>ماژول</th>
                        <th>عملیات</th>
                        <th>توضیحات</th>
                        <th style="width: 100px;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $modules = [
                        'template' => ['create','read','update','delete','publish','export','import','settings'],
                        'user' => ['create','read','update','delete','roles','permissions'],
                        'menu' => ['create','read','update','delete','publish'],
                        'component' => ['create','read','update','delete','export','import'],
                        'module' => ['install','uninstall','activate','deactivate','export'],
                        'plugin' => ['install','uninstall','activate','deactivate'],
                        'form' => ['create','read','update','delete','responses'],
                        'table' => ['create','read','update','delete','export'],
                        'report' => ['create','read','update','delete','export'],
                        'language' => ['create','read','update','delete','set_default'],
                        'ai' => ['use','configure','train'],
                        'system' => ['settings','backup','logs','maintenance'],
                    ];
                    @endphp
                    
                    @foreach($modules as $module => $actions)
                        @foreach($actions as $action)
                        <tr>
                            <td>
                                <span class="badge badge-secondary">{{ __("permissions.$module.$action") }}</span>
                            </td>
                            <td><code>{{ $module }}.{{ $action }}</code></td>
                            <td>{{ __("modules.$module") }}</td>
                            <td>
                                <span class="badge badge-{{ $action == 'delete' ? 'danger' : ($action == 'create' ? 'success' : 'info') }}">
                                    {{ $action }}
                                </span>
                            </td>
                            <td style="color: var(--text-muted); font-size: 0.85rem;">
                                دسترسی {{ $action }} در بخش {{ __("modules.$module") }}
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn-icon" title="ویرایش"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon danger" title="حذف"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
