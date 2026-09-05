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

@section('title', 'مدیریت رسانه‌ها')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">مدیریت رسانه‌ها</h3>
                    <div class="card-tools">
                        <form action="{{ route('admin.content-manager.media.upload') }}" method="POST" enctype="multipart/form-data" class="d-inline">
                            @csrf
                            <div class="input-group input-group-sm" style="width: 300px;">
                                <input type="file" name="file" class="form-control" required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> آپلود
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    {{-- آمار --}}
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-files"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">کل فایل‌ها</span>
                                    <span class="info-box-number">{{ $stats['total'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-image"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">تصاویر</span>
                                    <span class="info-box-number">{{ $stats['images'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-video"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">ویدئوها</span>
                                    <span class="info-box-number">{{ $stats['videos'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-file-pdf"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">مدارک</span>
                                    <span class="info-box-number">{{ $stats['documents'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary"><i class="fas fa-file"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">سایر</span>
                                    <span class="info-box-number">{{ $stats['others'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-dark"><i class="fas fa-database"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">حجم کل</span>
                                    <span class="info-box-number">{{ isset($stats['total_size']) ? round($stats['total_size'] / 1024 / 1024, 2) : 0 }} MB</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- جستجو --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <form method="GET" class="d-flex">
                                <input type="text" name="search" class="form-control" placeholder="جستجوی فایل‌ها..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary ml-2">جستجو</button>
                                <a href="{{ route('admin.content-manager.media.index') }}" class="btn btn-secondary ml-2">پاک کردن</a>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <select name="mime_type" class="form-control" onchange="window.location.href=this.value ? '?mime_type='+this.value : window.location.pathname">
                                <option value="">همه انواع</option>
                                <option value="image" {{ request('mime_type') == 'image' ? 'selected' : '' }}>تصاویر</option>
                                <option value="video" {{ request('mime_type') == 'video' ? 'selected' : '' }}>ویدئوها</option>
                                <option value="audio" {{ request('mime_type') == 'audio' ? 'selected' : '' }}>صوت‌ها</option>
                                <option value="application" {{ request('mime_type') == 'application' ? 'selected' : '' }}>مدارک</option>
                            </select>
                        </div>
                    </div>

                    {{-- گالری --}}
                    <div class="row">
                        @forelse($media ?? [] as $item)
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    @if(str_starts_with($item->mime_type, 'image/'))
                                        <img src="{{ $item->url }}" alt="{{ $item->alt ?? $item->original_name }}" class="img-fluid" style="max-height: 150px;">
                                    @else
                                        <i class="fas fa-file fa-4x text-secondary"></i>
                                    @endif
                                    <p class="mt-2 mb-0">
                                        <strong>{{ Str::limit($item->original_name, 20) }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $item->size_label }}</small>
                                        <br>
                                        <small class="text-muted">{{ $item->mime_type }}</small>
                                    </p>
                                    <div class="mt-2">
                                        <form action="{{ route('admin.content-manager.media.destroy', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('آیا مطمئن هستید؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <button class="btn btn-info btn-sm" onclick="copyToClipboard('{{ $item->url }}')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <p class="text-center text-muted">هیچ فایلی یافت نشد.</p>
                        </div>
                        @endforelse
                    </div>

                    {{-- صفحه‌بندی --}}
                    <div class="mt-3">
                        {{ $media->links() ?? '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('لینک کپی شد: ' + text);
    });
}
</script>
@endsection
