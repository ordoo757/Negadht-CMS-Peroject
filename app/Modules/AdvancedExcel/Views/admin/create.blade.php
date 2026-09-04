@extends('core::layouts.admin')

@section('title', 'ایجاد کتاب کار جدید')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ایجاد کتاب کار جدید</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.advanced-excel.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.advanced-excel.store') }}">
                    @csrf

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>نام کتاب کار <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>وضعیت</label>
                                    <select name="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                        <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>فعال</option>
                                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>غیرفعال</option>
                                    </select>
                                    @error('is_active')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>توضیحات</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>دسترسی</label>
                                    <select name="is_public" class="form-control @error('is_public') is-invalid @enderror">
                                        <option value="1" {{ old('is_public') == '1' ? 'selected' : '' }}>عمومی (همه می‌توانند ببینند)</option>
                                        <option value="0" {{ old('is_public') == '0' ? 'selected' : '' }}>خصوصی (فقط من و کاربران مجاز)</option>
                                    </select>
                                    @error('is_public')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>تنظیمات (JSON)</label>
                                    <textarea name="settings" class="form-control @error('settings') is-invalid @enderror" rows="3">{{ old('settings', '{"default_font":"Vazir","default_font_size":12}') }}</textarea>
                                    @error('settings')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> ایجاد کتاب کار
                        </button>
                        <a href="{{ route('admin.advanced-excel.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> انصراف
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
