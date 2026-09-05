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

@section('title', 'اسکن جدید')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">اسکن جدید</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.antivirus.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- فرم اسکن --}}
                    <form method="POST" action="{{ route('admin.antivirus.start-scan') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>نوع اسکن <span class="text-danger">*</span></label>
                                    <select name="type" class="form-control @error('type') is-invalid @enderror" id="scanType" onchange="toggleCustomPath()">
                                        <option value="quick" {{ old('type') == 'quick' ? 'selected' : '' }}>سریع (فایل‌های مهم)</option>
                                        <option value="full" {{ old('type') == 'full' ? 'selected' : '' }}>کامل (همه فایل‌ها)</option>
                                        <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>دلخواه</option>
                                    </select>
                                    @error('type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>قرنطینه خودکار</label>
                                    <select name="quarantine" class="form-control @error('quarantine') is-invalid @enderror">
                                        <option value="1" {{ old('quarantine') == '1' ? 'selected' : '' }}>فعال</option>
                                        <option value="0" {{ old('quarantine') == '0' ? 'selected' : '' }}>غیرفعال</option>
                                    </select>
                                    @error('quarantine')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row" id="customPathRow" style="display: none;">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>مسیر اسکن</label>
                                    <input type="text" name="path" class="form-control @error('path') is-invalid @enderror" value="{{ old('path', base_path()) }}" placeholder="مسیر کامل پوشه را وارد کنید">
                                    @error('path')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="text-muted">مثال: {{ base_path() }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-play"></i> شروع اسکن
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- اطلاعات --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>نکات مهم:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>اسکن سریع فقط فایل‌های مهم (PHP, JS, HTML, SQL) را بررسی می‌کند.</li>
                                    <li>اسکن کامل همه فایل‌های پروژه را اسکن می‌کند و ممکن است زمان‌بر باشد.</li>
                                    <li>فایل‌های آلوده به صورت خودکار به قرنطینه منتقل می‌شوند.</li>
                                    <li>تعاریف ویروس‌ها شامل eval, base64_decode, system, SQL Injection و XSS می‌باشند.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- اسکن فایل آپلودی --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-secondary">
                                <div class="card-header">
                                    <h5 class="card-title">اسکن فایل آپلودی</h5>
                                </div>
                                <div class="card-body">
                                    <form id="uploadScanForm" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <input type="file" name="file" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="fas fa-search"></i> اسکن فایل
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <div id="uploadResult" class="mt-2" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleCustomPath() {
        const type = document.getElementById('scanType').value;
        const row = document.getElementById('customPathRow');
        row.style.display = type === 'custom' ? 'block' : 'none';
    }

    // اسکن فایل آپلودی
    document.getElementById('uploadScanForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const resultDiv = document.getElementById('uploadResult');

        resultDiv.innerHTML = '<div class="alert alert-info">در حال اسکن...</div>';
        resultDiv.style.display = 'block';

        fetch('{{ route("admin.antivirus.scan-upload") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.safe) {
                resultDiv.innerHTML = '<div class="alert alert-success">✅ ' + data.message + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger">❌ ' + data.message + 
                    (data.details ? '<br><small>' + JSON.stringify(data.details) + '</small>' : '') + '</div>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<div class="alert alert-danger">خطا در اسکن: ' + error.message + '</div>';
        });
    });

    // فعال کردن نمایش مسیر دلخواه در صورت انتخاب
    toggleCustomPath();
</script>
@endpush
