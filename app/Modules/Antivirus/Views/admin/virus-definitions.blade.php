<?php

/**
 * NeuroCMS - Content Management System
 *
 * @author     Hooman Oliaei (هومان اولیائی)
 * @copyright  Copyright (c) 2026 Hooman Oliaei
 * @license    GNU General Public License v3.0
 * @link       https://github.com/ordoo757
 */
@extends('core::layouts.admin')

@section('title', 'مدیریت تعاریف ویروس‌ها')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">مدیریت تعاریف ویروس‌ها</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.antivirus.virus-definitions.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> تعریف جدید
                        </a>
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#importModal">
                            <i class="fas fa-file-import"></i> واردات
                        </button>
                        <a href="{{ route('admin.antivirus.virus-definitions.export') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-file-export"></i> خروجی
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- آمار --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-virus"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">کل تعاریف</span>
                                    <span class="info-box-number">{{ $stats['total'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">فعال</span>
                                    <span class="info-box-number">{{ $stats['active'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">غیرفعال</span>
                                    <span class="info-box-number">{{ $stats['inactive'] ?? 0 }}</span>
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
                                    <option value="php" {{ request('type') == 'php' ? 'selected' : '' }}>PHP</option>
                                    <option value="javascript" {{ request('type') == 'javascript' ? 'selected' : '' }}>JavaScript</option>
                                    <option value="html" {{ request('type') == 'html' ? 'selected' : '' }}>HTML</option>
                                    <option value="sql" {{ request('type') == 'sql' ? 'selected' : '' }}>SQL</option>
                                    <option value="python" {{ request('type') == 'python' ? 'selected' : '' }}>Python</option>
                                    <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>سایر</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="severity" class="form-control">
                                    <option value="">همه شدت‌ها</option>
                                    <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>کم</option>
                                    <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>متوسط</option>
                                    <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>بالا</option>
                                    <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>بحرانی</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="is_active" class="form-control">
                                    <option value="">همه وضعیت‌ها</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>فعال</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غیرفعال</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">فیلتر</button>
                                <a href="{{ route('admin.antivirus.virus-definitions') }}" class="btn btn-secondary">پاک کردن</a>
                            </div>
                        </div>
                    </form>

                    {{-- جدول --}}
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نام</th>
                                <th>نوع</th>
                                <th>شدت</th>
                                <th>وضعیت</th>
                                <th>تاریخ</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($definitions ?? [] as $definition)
                            <tr>
                                <td>{{ $definition->id }}</td>
                                <td>
                                    <strong>{{ $definition->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ Str::limit($definition->description, 30) }}</small>
                                </td>
                                <td><span class="badge badge-secondary">{{ $definition->type }}</span></td>
                                <td>
                                    <span class="badge badge-{{ $definition->severity_color }}">
                                        {{ $definition->severity_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $definition->is_active ? 'success' : 'danger' }}">
                                        {{ $definition->is_active ? 'فعال' : 'غیرفعال' }}
                                    </span>
                                </td>
                                <td>{{ $definition->created_at->diffForHumans() }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.antivirus.virus-definitions.edit', $definition->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.antivirus.virus-definitions.toggle', $definition->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-{{ $definition->is_active ? 'warning' : 'success' }} btn-sm">
                                                <i class="fas fa-{{ $definition->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.antivirus.virus-definitions.destroy', $definition->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('آیا از حذف تعریف مطمئن هستید؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">هیچ تعریف ویروسی یافت نشد.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Import --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.antivirus.virus-definitions.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">واردات تعاریف ویروس</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>فایل JSON</label>
                        <input type="file" name="file" class="form-control" accept=".json" required>
                        <small class="text-muted">فایل باید شامل کلید definitions باشد.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">واردات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
