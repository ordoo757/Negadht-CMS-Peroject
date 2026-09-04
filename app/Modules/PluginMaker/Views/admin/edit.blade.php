@extends('core::layouts.admin')

@section('title', 'ویرایش پلاگین: ' . $plugin->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ویرایش پلاگین: {{ $plugin->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.plugin-maker.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.plugin-maker.update', $plugin->slug) }}">
                    @csrf @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>نام پلاگین</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $plugin->name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>دسته‌بندی</label>
                                    <input type="text" name="category" class="form-control" value="{{ old('category', $plugin->category) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>توضیحات</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description', $plugin->description) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>نوع</label>
                                    <select name="type" class="form-control">
                                        <option value="general" {{ $plugin->type == 'general' ? 'selected' : '' }}>عمومی</option>
                                        <option value="seo" {{ $plugin->type == 'seo' ? 'selected' : '' }}>سئو</option>
                                        <option value="security" {{ $plugin->type == 'security' ? 'selected' : '' }}>امنیتی</option>
                                        <option value="analytics" {{ $plugin->type == 'analytics' ? 'selected' : '' }}>تحلیلی</option>
                                        <option value="social" {{ $plugin->type == 'social' ? 'selected' : '' }}>شبکه اجتماعی</option>
                                        <option value="marketing" {{ $plugin->type == 'marketing' ? 'selected' : '' }}>مارکتینگ</option>
                                        <option value="payment" {{ $plugin->type == 'payment' ? 'selected' : '' }}>پرداخت</option>
                                        <option value="crm" {{ $plugin->type == 'crm' ? 'selected' : '' }}>مدیریت مشتری</option>
                                        <option value="ecommerce" {{ $plugin->type == 'ecommerce' ? 'selected' : '' }}>فروشگاهی</option>
                                        <option value="custom" {{ $plugin->type == 'custom' ? 'selected' : '' }}>سفارشی</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>نسخه</label>
                                    <input type="text" name="version" class="form-control" value="{{ old('version', $plugin->version) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>وضعیت</label>
                                    <select name="status" class="form-control">
                                        <option value="draft" {{ $plugin->status == 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                                        <option value="stable" {{ $plugin->status == 'stable' ? 'selected' : '' }}>پایدار</option>
                                        <option value="beta" {{ $plugin->status == 'beta' ? 'selected' : '' }}>بتا</option>
                                        <option value="alpha" {{ $plugin->status == 'alpha' ? 'selected' : '' }}>آلفا</option>
                                        <option value="deprecated" {{ $plugin->status == 'deprecated' ? 'selected' : '' }}>منسوخ</option>
                                        <option value="archived" {{ $plugin->status == 'archived' ? 'selected' : '' }}>بایگانی</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>لایسنس</label>
                                    <input type="text" name="license" class="form-control" value="{{ old('license', $plugin->license) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>نویسنده</label>
                                    <input type="text" name="author" class="form-control" value="{{ old('author', $plugin->author) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>ایمیل نویسنده</label>
                                    <input type="email" name="author_email" class="form-control" value="{{ old('author_email', $plugin->author_email) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>وبسایت</label>
                                    <input type="url" name="website" class="form-control" value="{{ old('website', $plugin->website) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>قیمت (تومان)</label>
                                    <input type="number" name="price" class="form-control" value="{{ old('price', $plugin->price) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>رایگان</label>
                                    <select name="is_free" class="form-control">
                                        <option value="1" {{ $plugin->is_free ? 'selected' : '' }}>بله</option>
                                        <option value="0" {{ !$plugin->is_free ? 'selected' : '' }}>خیر</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>فعال</label>
                                    <select name="is_active" class="form-control">
                                        <option value="1" {{ $plugin->is_active ? 'selected' : '' }}>بله</option>
                                        <option value="0" {{ !$plugin->is_active ? 'selected' : '' }}>خیر</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>عمومی</label>
                                    <select name="is_public" class="form-control">
                                        <option value="1" {{ $plugin->is_public ? 'selected' : '' }}>بله</option>
                                        <option value="0" {{ !$plugin->is_public ? 'selected' : '' }}>خیر</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">بروزرسانی پلاگین</button>
                        <a href="{{ route('admin.plugin-maker.index') }}" class="btn btn-secondary">انصراف</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
