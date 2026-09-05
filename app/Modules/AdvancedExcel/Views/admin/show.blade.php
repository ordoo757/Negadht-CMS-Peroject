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

@section('title', $workbook->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $workbook->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.advanced-excel.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                        <a href="{{ route('admin.advanced-excel.edit', $workbook->id) }}" class="btn btn-primary btn-sm">
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
                                    <td>{{ $workbook->name }}</td>
                                </tr>
                                <tr>
                                    <th>توضیحات</th>
                                    <td>{{ $workbook->description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>وضعیت</th>
                                    <td>
                                        <span class="badge badge-{{ $workbook->is_active ? 'success' : 'danger' }}">
                                            {{ $workbook->is_active ? 'فعال' : 'غیرفعال' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>دسترسی</th>
                                    <td>
                                        <span class="badge badge-{{ $workbook->is_public ? 'info' : 'secondary' }}">
                                            {{ $workbook->is_public ? 'عمومی' : 'خصوصی' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>تعداد برگه‌ها</th>
                                    <td>{{ $stats['total_worksheets'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>تعداد سلول‌ها</th>
                                    <td>{{ $stats['total_cells'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>تعداد نمودارها</th>
                                    <td>{{ $stats['total_charts'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>بازدید</th>
                                    <td>{{ $stats['views'] ?? 0 }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- برگه‌ها --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>برگه‌ها</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>نام برگه</th>
                                            <th>سلول‌ها</th>
                                            <th>نمودارها</th>
                                            <th>وضعیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($workbook->worksheets as $worksheet)
                                        <tr>
                                            <td>{{ $worksheet->id }}</td>
                                            <td>{{ $worksheet->name }}</td>
                                            <td>{{ $worksheet->cells->count() }}</td>
                                            <td>{{ $worksheet->charts->count() }}</td>
                                            <td>
                                                <span class="badge badge-{{ $worksheet->is_active ? 'success' : 'danger' }}">
                                                    {{ $worksheet->is_active ? 'فعال' : 'غیرفعال' }}
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">هیچ برگه‌ای وجود ندارد.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
