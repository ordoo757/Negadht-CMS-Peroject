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

@section('title', 'ایجاد پلاگین جدید')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ایجاد پلاگین جدید</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.plugin-maker.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.plugin-maker.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>نام پلاگین <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>دسته‌بندی</label>
                                    <input type="text" name="category" class="form-control" value="{{ old('category') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>توضیحات</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>نوع</label>
                                    <select name="type" class="form-control">
                                        <option value="general">عمومی</option>
                                        <option value="seo">سئو</option>
                                        <option value="security">امنیتی</option>
                                        <option value="analytics">تحلیلی</option>
                                        <option value="social">شبکه اجتماعی</option>
                                        <option value="marketing">مارکتینگ</option>
                                        <option value="payment">پرداخت</option>
                                        <option value="crm">مدیریت مشتری</option>
                                        <option value="ecommerce">فروشگاهی</option>
                                        <option value="custom">سفارشی</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>نسخه</label>
                                    <input type="text" name="version" class="form-control" value="{{ old('version', '1.0.0') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>وضعیت</label>
                                    <select name="status" class="form-control">
                                        <option value="draft">پیش‌نویس</option>
                                        <option value="stable">پایدار</option>
                                        <option value="beta">بتا</option>
                                        <option value="alpha">آلفا</option>
                                        <option value="deprecated">منسوخ</option>
                                        <option value="archived">بایگانی</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>لایسنس</label>
                                    <input type="text" name="license" class="form-control" value="{{ old('license', 'MIT') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>نویسنده</label>
                                    <input type="text" name="author" class="form-control" value="{{ old('author', auth()->user()->name ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>ایمیل نویسنده</label>
                                    <input type="email" name="author_email" class="form-control" value="{{ old('author_email', auth()->user()->email ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>وبسایت</label>
                                    <input type="url" name="website" class="form-control" value="{{ old('website') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>قیمت (تومان)</label>
                                    <input type="number" name="price" class="form-control" value="{{ old('price', 0) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>رایگان</label>
                                    <select name="is_free" class="form-control">
                                        <option value="1">بله</option>
                                        <option value="0">خیر</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>فعال</label>
                                    <select name="is_active" class="form-control">
                                        <option value="1">بله</option>
                                        <option value="0">خیر</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>عمومی</label>
                                    <select name="is_public" class="form-control">
                                        <option value="1">بله</option>
                                        <option value="0">خیر</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">ایجاد پلاگین</button>
                        <a href="{{ route('admin.plugin-maker.index') }}" class="btn btn-secondary">انصراف</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
