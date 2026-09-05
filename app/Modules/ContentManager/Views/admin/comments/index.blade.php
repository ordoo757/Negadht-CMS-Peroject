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

@section('title', 'مدیریت نظرات')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">مدیریت نظرات</h3>
                </div>

                <div class="card-body">
                    {{-- آمار --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-comments"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">کل نظرات</span>
                                    <span class="info-box-number">{{ $stats['total'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">تأیید شده</span>
                                    <span class="info-box-number">{{ $stats['approved'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">در انتظار</span>
                                    <span class="info-box-number">{{ $stats['pending'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-trash"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">رد شده</span>
                                    <span class="info-box-number">{{ ($stats['total'] ?? 0) - ($stats['approved'] ?? 0) - ($stats['pending'] ?? 0) }}</span>
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
                            <div class="col-md-3">
                                <select name="is_approved" class="form-control">
                                    <option value="">همه وضعیت‌ها</option>
                                    <option value="1" {{ request('is_approved') == '1' ? 'selected' : '' }}>تأیید شده</option>
                                    <option value="0" {{ request('is_approved') == '0' ? 'selected' : '' }}>در انتظار</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="page_id" class="form-control">
                                    <option value="">همه صفحات</option>
                                    @foreach($pages ?? [] as $id => $title)
                                        <option value="{{ $id }}" {{ request('page_id') == $id ? 'selected' : '' }}>
                                            {{ $title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">فیلتر</button>
                                <a href="{{ route('admin.content-manager.comments.index') }}" class="btn btn-secondary">پاک کردن</a>
                            </div>
                        </div>
                    </form>

                    {{-- جدول --}}
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نظر</th>
                                <th>کاربر</th>
                                <th>صفحه</th>
                                <th>وضعیت</th>
                                <th>تاریخ</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($comments ?? [] as $comment)
                            <tr>
                                <td>{{ $comment->id }}</td>
                                <td>{{ Str::limit($comment->content, 50) }}</td>
                                <td>{{ $comment->user->name ?? 'کاربر ناشناس' }}</td>
                                <td><a href="{{ route('admin.content-manager.pages.show', $comment->page_id) }}" target="_blank">{{ $comment->page->title ?? 'صفحه حذف شده' }}</a></td>
                                <td>
                                    <span class="badge badge-{{ $comment->is_approved ? 'success' : 'warning' }}">
                                        {{ $comment->is_approved ? 'تأیید شده' : 'در انتظار' }}
                                    </span>
                                </td>
                                <td>{{ $comment->created_at->diffForHumans() }}</td>
                                <td>
                                    <div class="btn-group">
                                        @if(!$comment->is_approved)
                                            <form action="{{ route('admin.content-manager.comments.approve', $comment->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.content-manager.comments.reject', $comment->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.content-manager.comments.destroy', $comment->id) }}" method="POST" class="d-inline">
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
                                <td colspan="7" class="text-center">هیچ نظری یافت نشد.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- صفحه‌بندی --}}
                    <div class="mt-3">
                        {{ $comments->links() ?? '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
