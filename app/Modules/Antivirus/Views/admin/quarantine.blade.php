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

@section('title', 'قرنطینه فایل‌ها')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">فایل‌های قرنطینه</h3>
                    <div class="card-tools">
                        <span class="badge badge-info">{{ $quarantineFiles->total() ?? 0 }} فایل</span>
                    </div>
                </div>

                <div class="card-body">
                    {{-- آمار --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-lock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">کل فایل‌ها</span>
                                    <span class="info-box-number">{{ $stats['quarantined_files'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-bug"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">بحرانی</span>
                                    <span class="info-box-number">{{ $quarantineFiles->where('severity', 'critical')->count() ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-undo"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">بازیابی شده</span>
                                    <span class="info-box-number">{{ $quarantineFiles->where('is_restored', true)->count() ?? 0 }}</span>
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
                                <select name="severity" class="form-control">
                                    <option value="">همه شدت‌ها</option>
                                    <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>کم</option>
                                    <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>متوسط</option>
                                    <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>بالا</option>
                                    <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>بحرانی</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="is_restored" class="form-control">
                                    <option value="">همه</option>
                                    <option value="1" {{ request('is_restored') == '1' ? 'selected' : '' }}>بازیابی شده</option>
                                    <option value="0" {{ request('is_restored') == '0' ? 'selected' : '' }}>در قرنطینه</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">فیلتر</button>
                                <a href="{{ route('admin.antivirus.quarantine') }}" class="btn btn-secondary">پاک کردن</a>
                            </div>
                        </div>
                    </form>

                    {{-- جدول --}}
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نام فایل</th>
                                <th>ویروس</th>
                                <th>شدت</th>
                                <th>حجم</th>
                                <th>وضعیت</th>
                                <th>تاریخ</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quarantineFiles ?? [] as $file)
                            <tr>
                                <td>{{ $file->id }}</td>
                                <td>
                                    <strong>{{ $file->filename }}</strong>
                                    <br>
                                    <small class="text-muted">{{ basename($file->original_path) }}</small>
                                </td>
                                <td>{{ $file->virus_name ?? 'نامشخص' }}</td>
                                <td>
                                    <span class="badge badge-{{ $file->severity_color }}">
                                        {{ $file->severity_label }}
                                    </span>
                                </td>
                                <td>{{ $file->size_label }}</td>
                                <td>
                                    @if($file->is_restored)
                                        <span class="badge badge-success">بازیابی شده</span>
                                    @else
                                        <span class="badge badge-danger">قرنطینه</span>
                                    @endif
                                </td>
                                <td>{{ $file->created_at->diffForHumans() }}</td>
                                <td>
                                    <div class="btn-group">
                                        @if(!$file->is_restored)
                                            <form action="{{ route('admin.antivirus.quarantine-restore', $file->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('آیا از بازیابی فایل مطمئن هستید؟')">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.antivirus.quarantine-delete', $file->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('آیا از حذف دائمی فایل مطمئن هستید؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">هیچ فایلی در قرنطینه یافت نشد.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- صفحه‌بندی --}}
                    <div class="mt-3">
                        {{ $quarantineFiles->links() ?? '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
