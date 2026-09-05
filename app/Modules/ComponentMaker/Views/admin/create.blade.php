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

@section('title', 'ایجاد کامپوننت جدید')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ایجاد کامپوننت جدید</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.component-maker.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.component-maker.store') }}">
                    @csrf

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">نام کامپوننت <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category">دسته‌بندی</label>
                                    <input type="text" name="category" id="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category') }}">
                                    @error('category')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">توضیحات</label>
                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="type">نوع</label>
                                    <select name="type" id="type" class="form-control @error('type') is-invalid @enderror">
                                        <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>سفارشی</option>
                                        <option value="layout" {{ old('type') == 'layout' ? 'selected' : '' }}>لایه</option>
                                        <option value="widget" {{ old('type') == 'widget' ? 'selected' : '' }}>ویجت</option>
                                        <option value="module" {{ old('type') == 'module' ? 'selected' : '' }}>ماژول</option>
                                        <option value="plugin" {{ old('type') == 'plugin' ? 'selected' : '' }}>پلاگین</option>
                                        <option value="theme" {{ old('type') == 'theme' ? 'selected' : '' }}>قالب</option>
                                        <option value="template" {{ old('type') == 'template' ? 'selected' : '' }}>تمپلیت</option>
                                    </select>
                                    @error('type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="version">نسخه</label>
                                    <input type="text" name="version" id="version" class="form-control @error('version') is-invalid @enderror" value="{{ old('version', '1.0.0') }}">
                                    @error('version')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">وضعیت</label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                                        <option value="stable" {{ old('status') == 'stable' ? 'selected' : '' }}>پایدار</option>
                                        <option value="beta" {{ old('status') == 'beta' ? 'selected' : '' }}>بتا</option>
                                        <option value="alpha" {{ old('status') == 'alpha' ? 'selected' : '' }}>آلفا</option>
                                        <option value="deprecated" {{ old('status') == 'deprecated' ? 'selected' : '' }}>منسوخ</option>
                                        <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>بایگانی</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="license">لایسنس</label>
                                    <input type="text" name="license" id="license" class="form-control @error('license') is-invalid @enderror" value="{{ old('license', 'MIT') }}">
                                    @error('license')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="author">نویسنده</label>
                                    <input type="text" name="author" id="author" class="form-control @error('author') is-invalid @enderror" value="{{ old('author', auth()->user()->name ?? '') }}">
                                    @error('author')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="author_email">ایمیل نویسنده</label>
                                    <input type="email" name="author_email" id="author_email" class="form-control @error('author_email') is-invalid @enderror" value="{{ old('author_email', auth()->user()->email ?? '') }}">
                                    @error('author_email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="website">وبسایت</label>
                                    <input type="url" name="website" id="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website') }}">
                                    @error('website')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tags">تگ‌ها (JSON)</label>
                                    <textarea name="tags" id="tags" class="form-control @error('tags') is-invalid @enderror" rows="2">{{ old('tags', '["general"]') }}</textarea>
                                    @error('tags')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dependencies">وابستگی‌ها (JSON)</label>
                                    <textarea name="dependencies" id="dependencies" class="form-control @error('dependencies') is-invalid @enderror" rows="2">{{ old('dependencies', '[]') }}</textarea>
                                    @error('dependencies')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="is_active">فعال</label>
                                    <select name="is_active" id="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>بله</option>
                                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>خیر</option>
                                    </select>
                                    @error('is_active')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="is_public">عمومی</label>
                                    <select name="is_public" id="is_public" class="form-control @error('is_public') is-invalid @enderror">
                                        <option value="1" {{ old('is_public', '1') == '1' ? 'selected' : '' }}>بله</option>
                                        <option value="0" {{ old('is_public') == '0' ? 'selected' : '' }}>خیر</option>
                                    </select>
                                    @error('is_public')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="is_core">هسته</label>
                                    <select name="is_core" id="is_core" class="form-control @error('is_core') is-invalid @enderror">
                                        <option value="1" {{ old('is_core') == '1' ? 'selected' : '' }}>بله</option>
                                        <option value="0" {{ old('is_core', '0') == '0' ? 'selected' : '' }}>خیر</option>
                                    </select>
                                    @error('is_core')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="is_system">سیستمی</label>
                                    <select name="is_system" id="is_system" class="form-control @error('is_system') is-invalid @enderror">
                                        <option value="1" {{ old('is_system') == '1' ? 'selected' : '' }}>بله</option>
                                        <option value="0" {{ old('is_system', '0') == '0' ? 'selected' : '' }}>خیر</option>
                                    </select>
                                    @error('is_system')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> ایجاد کامپوننت
                        </button>
                        <a href="{{ route('admin.component-maker.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> انصراف
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
