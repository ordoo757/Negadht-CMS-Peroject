@extends('core::layouts.admin')

@section('title', 'ویرایش کامپوننت: ' . $component->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ویرایش کامپوننت: {{ $component->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.component-maker.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.component-maker.update', $component->slug) }}">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">نام کامپوننت <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $component->name) }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category">دسته‌بندی</label>
                                    <input type="text" name="category" id="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category', $component->category) }}">
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
                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $component->description) }}</textarea>
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
                                        <option value="custom" {{ old('type', $component->type) == 'custom' ? 'selected' : '' }}>سفارشی</option>
                                        <option value="layout" {{ old('type', $component->type) == 'layout' ? 'selected' : '' }}>لایه</option>
                                        <option value="widget" {{ old('type', $component->type) == 'widget' ? 'selected' : '' }}>ویجت</option>
                                        <option value="module" {{ old('type', $component->type) == 'module' ? 'selected' : '' }}>ماژول</option>
                                        <option value="plugin" {{ old('type', $component->type) == 'plugin' ? 'selected' : '' }}>پلاگین</option>
                                        <option value="theme" {{ old('type', $component->type) == 'theme' ? 'selected' : '' }}>قالب</option>
                                        <option value="template" {{ old('type', $component->type) == 'template' ? 'selected' : '' }}>تمپلیت</option>
                                    </select>
                                    @error('type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="version">نسخه</label>
                                    <input type="text" name="version" id="version" class="form-control @error('version') is-invalid @enderror" value="{{ old('version', $component->version) }}">
                                    @error('version')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">وضعیت</label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                                        <option value="draft" {{ old('status', $component->status) == 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                                        <option value="stable" {{ old('status', $component->status) == 'stable' ? 'selected' : '' }}>پایدار</option>
                                        <option value="beta" {{ old('status', $component->status) == 'beta' ? 'selected' : '' }}>بتا</option>
                                        <option value="alpha" {{ old('status', $component->status) == 'alpha' ? 'selected' : '' }}>آلفا</option>
                                        <option value="deprecated" {{ old('status', $component->status) == 'deprecated' ? 'selected' : '' }}>منسوخ</option>
                                        <option value="archived" {{ old('status', $component->status) == 'archived' ? 'selected' : '' }}>بایگانی</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="license">لایسنس</label>
                                    <input type="text" name="license" id="license" class="form-control @error('license') is-invalid @enderror" value="{{ old('license', $component->license) }}">
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
                                    <input type="text" name="author" id="author" class="form-control @error('author') is-invalid @enderror" value="{{ old('author', $component->author) }}">
                                    @error('author')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="author_email">ایمیل نویسنده</label>
                                    <input type="email" name="author_email" id="author_email" class="form-control @error('author_email') is-invalid @enderror" value="{{ old('author_email', $component->author_email) }}">
                                    @error('author_email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="website">وبسایت</label>
                                    <input type="url" name="website" id="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $component->website) }}">
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
                                    <textarea name="tags" id="tags" class="form-control @error('tags') is-invalid @enderror" rows="2">{{ old('tags', json_encode($component->tags, JSON_PRETTY_PRINT)) }}</textarea>
                                    @error('tags')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dependencies">وابستگی‌ها (JSON)</label>
                                    <textarea name="dependencies" id="dependencies" class="form-control @error('dependencies') is-invalid @enderror" rows="2">{{ old('dependencies', json_encode($component->dependencies, JSON_PRETTY_PRINT)) }}</textarea>
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
                                        <option value="1" {{ old('is_active', $component->is_active) == '1' ? 'selected' : '' }}>بله</option>
                                        <option value="0" {{ old('is_active', $component->is_active) == '0' ? 'selected' : '' }}>خیر</option>
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
                                        <option value="1" {{ old('is_public', $component->is_public) == '1' ? 'selected' : '' }}>بله</option>
                                        <option value="0" {{ old('is_public', $component->is_public) == '0' ? 'selected' : '' }}>خیر</option>
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
                                        <option value="1" {{ old('is_core', $component->is_core) == '1' ? 'selected' : '' }}>بله</option>
                                        <option value="0" {{ old('is_core', $component->is_core) == '0' ? 'selected' : '' }}>خیر</option>
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
                                        <option value="1" {{ old('is_system', $component->is_system) == '1' ? 'selected' : '' }}>بله</option>
                                        <option value="0" {{ old('is_system', $component->is_system) == '0' ? 'selected' : '' }}>خیر</option>
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
                            <i class="fas fa-save"></i> بروزرسانی کامپوننت
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
