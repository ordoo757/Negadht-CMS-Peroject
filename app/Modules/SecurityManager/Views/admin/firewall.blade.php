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

@section('title', 'فایروال')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">مدیریت فایروال</h5>
                </div>
                <div class="card-body">
                    {{-- فرم مسدود کردن آی‌پی --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h6>مسدود کردن آی‌پی جدید</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('admin.security-manager.firewall.block') }}">
                                        @csrf
                                        <div class="form-group">
                                            <label>آی‌پی آدرس</label>
                                            <input type="text" name="ip_address" class="form-control" placeholder="مثال: 192.168.1.100" required>
                                        </div>
                                        <div class="form-group">
                                            <label>دلیل</label>
                                            <input type="text" name="reason" class="form-control" placeholder="دلیل مسدود کردن">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>مدت زمان (دقیقه)</label>
                                                    <input type="number" name="minutes" class="form-control" value="30" min="1" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>دائمی</label>
                                                    <select name="is_permanent" class="form-control">
                                                        <option value="0">خیر</option>
                                                        <option value="1">بله</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-ban"></i> مسدود کردن
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h6>رفع مسدودیت آی‌پی</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('admin.security-manager.firewall.unblock') }}">
                                        @csrf
                                        <div class="form-group">
                                            <label>آی‌پی آدرس</label>
                                            <input type="text" name="ip_address" class="form-control" placeholder="مثال: 192.168.1.100" required>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check"></i> رفع مسدودیت
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- لیست آی‌پی‌های مسدود شده --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>آی‌پی‌های مسدود شده</h6>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>آی‌پی</th>
                                        <th>دلیل</th>
                                        <th>دائمی</th>
                                        <th>تاریخ انقضا</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($blockedIps as $ip)
                                    <tr>
                                        <td><code>{{ $ip->ip_address }}</code></td>
                                        <td>{{ $ip->reason ?? '-' }}</td>
                                        <td>{{ $ip->is_permanent ? 'بله' : 'خیر' }}</td>
                                        <td>{{ $ip->blocked_until ? $ip->blocked_until->diffForHumans() : 'نامحدود' }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.security-manager.firewall.unblock') }}">
                                                @csrf
                                                <input type="hidden" name="ip_address" value="{{ $ip->ip_address }}">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> رفع
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center">هیچ آی‌پی مسدود شده‌ای وجود ندارد.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
