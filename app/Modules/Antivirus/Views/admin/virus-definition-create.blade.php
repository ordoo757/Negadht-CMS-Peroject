@extends('core::layouts.admin')
@section('title', 'ایجاد تعریف ویروس جدید')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ایجاد تعریف ویروس جدید</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.antivirus.virus-definitions') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.antivirus.virus-definitions.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>نام <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>نوع <span class="text-danger">*</span></label>
                                    <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                                        <option value="">انتخاب کنید...</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>{{ strtoupper($type) }}</option>
                                        @endforeach
                                    </select>
                                    @error('type') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>الگوی Regex <span class="text-danger">*</span></label>
                                    <input type="text" name="pattern" class="form-control @error('pattern') is-invalid @enderror" value="{{ old('pattern') }}" placeholder="مثال: /eval\s*\(/i" required>
                                    @error('pattern') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    <small class="text-muted">الگوی عبارت منظم برای تشخیص ویروس</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>شدت <span class="text-danger">*</span></label>
                                    <select name="severity" class="form-control @error('severity') is-invalid @enderror" required>
                                        <option value="">انتخاب کنید...</option>
                                        @foreach($severities as $severity)
                                            <option value="{{ $severity }}" {{ old('severity') == $severity ? 'selected' : '' }}>
                                                {{ ucfirst($severity) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('severity') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>فعال</label>
                                    <select name="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                        <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>بله</option>
                                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>خیر</option>
                                    </select>
                                    @error('is_active') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>توضیحات</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                                    @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> ذخیره</button>
                        <a href="{{ route('admin.antivirus.virus-definitions') }}" class="btn btn-secondary">انصراف</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
