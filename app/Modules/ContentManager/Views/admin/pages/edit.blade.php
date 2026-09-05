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

@section('title', 'ویرایش صفحه: ' . $page->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ویرایش صفحه: {{ $page->title }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.content-manager.pages.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.content-manager.pages.update', $page->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>عنوان صفحه <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $page->title) }}" required>
                                    @error('title')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>وضعیت</label>
                                    <select name="status" class="form-control @error('status') is-invalid @enderror">
                                        <option value="draft" {{ old('status', $page->status) == 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                                        <option value="published" {{ old('status', $page->status) == 'published' ? 'selected' : '' }}>منتشر شده</option>
                                        <option value="pending" {{ old('status', $page->status) == 'pending' ? 'selected' : '' }}>در انتظار</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>محتوای صفحه</label>
                                    <textarea name="content" id="editor" class="form-control @error('content') is-invalid @enderror" rows="10">{{ old('content', $page->content) }}</textarea>
                                    @error('content')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>دسته‌بندی</label>
                                    <select name="category_id" class="form-control @error('category_id') is-invalid @enderror">
                                        <option value="">بدون دسته‌بندی</option>
                                        @foreach($categories ?? [] as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $page->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>تصویر شاخص</label>
                                    <input type="text" name="featured_image" class="form-control @error('featured_image') is-invalid @enderror" value="{{ old('featured_image', $page->featured_image) }}" placeholder="مسیر تصویر">
                                    @error('featured_image')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- SEO --}}
                        <div class="card card-secondary mt-3">
                            <div class="card-header">
                                <h5 class="card-title">بهینه‌سازی موتور جستجو (SEO)</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>عنوان متا</label>
                                            <input type="text" name="meta_title" class="form-control @error('meta_title') is-invalid @enderror" value="{{ old('meta_title', $page->meta_title) }}">
                                            @error('meta_title')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>توضیحات متا</label>
                                            <textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror" rows="2">{{ old('meta_description', $page->meta_description) }}</textarea>
                                            @error('meta_description')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>کلمات کلیدی متا</label>
                                            <input type="text" name="meta_keywords" class="form-control @error('meta_keywords') is-invalid @enderror" value="{{ old('meta_keywords', $page->meta_keywords) }}">
                                            @error('meta_keywords')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>صفحه اصلی</label>
                                    <select name="is_home" class="form-control @error('is_home') is-invalid @enderror">
                                        <option value="0">خیر</option>
                                        <option value="1" {{ old('is_home', $page->is_home) == '1' ? 'selected' : '' }}>بله</option>
                                    </select>
                                    @error('is_home')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>تاریخ انتشار</label>
                                    <input type="datetime-local" name="published_at" class="form-control @error('published_at') is-invalid @enderror" value="{{ old('published_at', $page->published_at ? $page->published_at->format('Y-m-d\TH:i') : '') }}">
                                    @error('published_at')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> بروزرسانی صفحه
                        </button>
                        <a href="{{ route('admin.content-manager.pages.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> انصراف
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
