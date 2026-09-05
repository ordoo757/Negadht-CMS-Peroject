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

@section('title', $page->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $page->title }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.content-manager.pages.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                        <a href="{{ route('admin.content-manager.pages.edit', $page->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> ویرایش
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>عنوان</th>
                                    <td>{{ $page->title }}</td>
                                </tr>
                                <tr>
                                    <th>شناسه</th>
                                    <td><code>{{ $page->slug }}</code></td>
                                </tr>
                                <tr>
                                    <th>دسته‌بندی</th>
                                    <td>{{ $page->category->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>وضعیت</th>
                                    <td>
                                        <span class="badge badge-{{ $page->status_color }}">
                                            {{ $page->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>صفحه اصلی</th>
                                    <td>{{ $page->is_home ? 'بله' : 'خیر' }}</td>
                                </tr>
                                <tr>
                                    <th>بازدید</th>
                                    <td>{{ $page->views }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>نویسنده</th>
                                    <td>{{ $page->user->name ?? 'سیستم' }}</td>
                                </tr>
                                <tr>
                                    <th>تاریخ انتشار</th>
                                    <td>{{ $page->published_at ? $page->published_at->format('Y/m/d H:i') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ایجاد</th>
                                    <td>{{ $page->created_at->format('Y/m/d H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>آخرین بروزرسانی</th>
                                    <td>{{ $page->updated_at->format('Y/m/d H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>تصویر شاخص</th>
                                    <td>
                                        @if($page->featured_image)
                                            <img src="{{ $page->featured_image }}" alt="{{ $page->title }}" style="max-height: 100px;">
                                        @else
                                            <span class="text-muted">ندارد</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>محتوای صفحه</h5>
                            <div class="p-3 border rounded">
                                {!! $page->content !!}
                            </div>
                        </div>
                    </div>

                    @if($page->meta_title || $page->meta_description || $page->meta_keywords)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>اطلاعات SEO</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>عنوان متا</th>
                                    <td>{{ $page->meta_title ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>توضیحات متا</th>
                                    <td>{{ $page->meta_description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>کلمات کلیدی</th>
                                    <td>{{ $page->meta_keywords ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
