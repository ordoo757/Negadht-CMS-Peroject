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

@section('title', 'مدیریت صفحات')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">مدیریت صفحات</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.content-manager.pages.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> صفحه جدید
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- آمار --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-file-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">کل صفحات</span>
                                    <span class="info-box-number">{{ $stats['total_pages'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">منتشر شده</span>
                                    <span class="info-box-number">{{ $stats['published_pages'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-pen"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">پیش‌نویس</span>
                                    <span class="info-box-number">{{ $stats['draft_pages'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-eye"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">کل بازدیدها</span>
                                    <span class="info-box-number">{{ $stats['total_views'] ?? 0 }}</span>
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
                                <select name="status" class="form-control">
                                    <option value="">همه وضعیت‌ها</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>منتشر شده</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>در انتظار</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="category" class="form-control">
                                    <option value="">همه دسته‌بندی‌ها</option>
                                    @foreach($categories ?? [] as $category)
                                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">فیلتر</button>
                                <a href="{{ route('admin.content-manager.pages.index') }}" class="btn btn-secondary">پاک کردن</a>
                            </div>
                        </div>
                    </form>

                    {{-- جدول --}}
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>عنوان</th>
                                <th>دسته‌بندی</th>
                                <th>وضعیت</th>
                                <th>بازدید</th>
                                <th>تاریخ انتشار</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pages ?? [] as $page)
                            <tr>
                                <td>{{ $page->id }}</td>
                                <td>
                                    <strong>{{ $page->title }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $page->slug }}</small>
                                </td>
                                <td>{{ $page->category->name ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $page->status_color }}">
                                        {{ $page->status_label }}
                                    </span>
                                </td>
                                <td>{{ $page->views }}</td>
                                <td>{{ $page->published_at ? $page->published_at->diffForHumans() : '-' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.content-manager.pages.show', $page->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.content-manager.pages.edit', $page->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.content-manager.pages.destroy', $page->id) }}" method="POST" class="d-inline">
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
                                <td colspan="7" class="text-center">هیچ صفحه‌ای یافت نشد.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- صفحه‌بندی --}}
                    <div class="mt-3">
                        {{ $pages->links() ?? '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
