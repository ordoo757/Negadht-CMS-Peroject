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

@section('title', 'پروفایل سایت')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">پروفایل سایت</h3>
                </div>

                <form method="POST" action="{{ route('admin.site-manager.profile.update') }}">
                    @csrf

                    <div class="card-body">
                        {{-- وضعیت سایت --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>وضعیت سایت</label>
                                    <select name="site_status" class="form-control">
                                        <option value="active" {{ ($settings['site_status'] ?? 'active') == 'active' ? 'selected' : '' }}>
                                            فعال
                                        </option>
                                        <option value="inactive" {{ ($settings['site_status'] ?? '') == 'inactive' ? 'selected' : '' }}>
                                            غیرفعال
                                        </option>
                                        <option value="maintenance" {{ ($settings['site_status'] ?? '') == 'maintenance' ? 'selected' : '' }}>
                                            در حال بروزرسانی
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>حالت نگهداری</label>
                                    <select name="maintenance_mode" class="form-control">
                                        <option value="0" {{ !($settings['maintenance_mode'] ?? false) ? 'selected' : '' }}>
                                            خاموش
                                        </option>
                                        <option value="1" {{ ($settings['maintenance_mode'] ?? false) ? 'selected' : '' }}>
                                            روشن
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- اطلاعات اصلی --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>نام سایت <span class="text-danger">*</span></label>
                                    <input type="text" name="site_name" class="form-control" 
                                        value="{{ old('site_name', $settings['site_name'] ?? 'NeuroCMS') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>شعار سایت</label>
                                    <input type="text" name="site_slogan" class="form-control"
                                        value="{{ old('site_slogan', $settings['site_slogan'] ?? '') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>توضیحات سایت</label>
                                    <textarea name="site_description" class="form-control" rows="3">{{ old('site_description', $settings['site_description'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>کلمات کلیدی (SEO)</label>
                                    <input type="text" name="site_keywords" class="form-control"
                                        value="{{ old('site_keywords', $settings['site_keywords'] ?? '') }}">
                                    <small class="text-muted">کلمات کلیدی را با کاما جدا کنید.</small>
                                </div>
                            </div>
                        </div>

                        {{-- ارتباط --}}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>ایمیل سایت <span class="text-danger">*</span></label>
                                    <input type="email" name="site_email" class="form-control"
                                        value="{{ old('site_email', $settings['site_email'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>شماره تماس</label>
                                    <input type="text" name="site_phone" class="form-control"
                                        value="{{ old('site_phone', $settings['site_phone'] ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>آدرس</label>
                                    <input type="text" name="site_address" class="form-control"
                                        value="{{ old('site_address', $settings['site_address'] ?? '') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>پیام حالت نگهداری</label>
                                    <textarea name="maintenance_message" class="form-control" rows="2">{{ old('maintenance_message', $settings['maintenance_message'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> ذخیره تنظیمات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
