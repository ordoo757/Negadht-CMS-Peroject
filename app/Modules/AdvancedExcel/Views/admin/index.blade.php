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

@section('title', 'مدیریت کتاب‌های کار اکسل')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">مدیریت کتاب‌های کار اکسل</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.advanced-excel.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> کتاب کار جدید
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- آمار --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-table"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">کل کتاب‌ها</span>
                                    <span class="info-box-number">{{ $stats['total_workbooks'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">فعال</span>
                                    <span class="info-box-number">{{ $stats['active_workbooks'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-layer-group"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">برگه‌ها</span>
                                    <span class="info-box-number">{{ $stats['total_worksheets'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-chart-bar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">نمودارها</span>
                                    <span class="info-box-number">{{ $stats['total_charts'] ?? 0 }}</span>
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
                                <select name="is_active" class="form-control">
                                    <option value="">همه وضعیت‌ها</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>فعال</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غیرفعال</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="is_public" class="form-control">
                                    <option value="">همه</option>
                                    <option value="1" {{ request('is_public') == '1' ? 'selected' : '' }}>عمومی</option>
                                    <option value="0" {{ request('is_public') == '0' ? 'selected' : '' }}>خصوصی</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">فیلتر</button>
                                <a href="{{ route('admin.advanced-excel.index') }}" class="btn btn-secondary">پاک کردن</a>
                            </div>
                        </div>
                    </form>

                    {{-- جدول --}}
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نام</th>
                                <th>برگه‌ها</th>
                                <th>سلول‌ها</th>
                                <th>نمودارها</th>
                                <th>وضعیت</th>
                                <th>عمومی</th>
                                <th>بازدید</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($workbooks ?? [] as $workbook)
                            <tr>
                                <td>{{ $workbook->id }}</td>
                                <td>
                                    <strong>{{ $workbook->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ Str::limit($workbook->description, 30) }}</small>
                                </td>
                                <td>{{ $workbook->worksheets->count() }}</td>
                                <td>{{ $workbook->worksheets->sum(function($w) { return $w->cells->count(); }) }}</td>
                                <td>{{ $workbook->worksheets->sum(function($w) { return $w->charts->count(); }) }}</td>
                                <td>
                                    <span class="badge badge-{{ $workbook->is_active ? 'success' : 'danger' }}">
                                        {{ $workbook->is_active ? 'فعال' : 'غیرفعال' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $workbook->is_public ? 'info' : 'secondary' }}">
                                        {{ $workbook->is_public ? 'عمومی' : 'خصوصی' }}
                                    </span>
                                </td>
                                <td>{{ $workbook->view_count }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.advanced-excel.show', $workbook->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.advanced-excel.edit', $workbook->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.advanced-excel.embed', $workbook->id) }}" class="btn btn-success btn-sm" target="_blank">
                                            <i class="fas fa-code"></i>
                                        </a>
                                        <form action="{{ route('admin.advanced-excel.destroy', $workbook->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('آیا مطمئن هستید؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">هیچ کتاب کاری یافت نشد.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- صفحه‌بندی --}}
                    <div class="mt-3">
                        {{ $workbooks->links() ?? '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
