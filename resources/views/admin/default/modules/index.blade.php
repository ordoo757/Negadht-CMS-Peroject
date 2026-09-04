@extends('admin.default.index')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>📦 مدیریت ماژول‌ها و کامپوننت‌ها</h2>
        <a href="{{ route('admin.modules.index') }}" class="btn btn-primary">🔄 بازنشانی</a>
    </div>

    <h3 style="margin-bottom:1rem;">ماژول‌ها</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>نام</th>
                <th>نامک</th>
                <th>نسخه</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($modules as $slug => $module)
            <tr>
                <td>{{ $module->getName() }}</td>
                <td>{{ $slug }}</td>
                <td>{{ $module->getVersion() }}</td>
                <td>
                    @if($module->isActive())
                        <span class="badge badge-success">فعال</span>
                    @else
                        <span class="badge badge-danger">غیرفعال</span>
                    @endif
                </td>
                <td>
                    @if($module->isActive())
                        <form method="POST" action="{{ route('admin.modules.deactivate', $slug) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning" data-confirm="غیرفعال شود؟">غیرفعال</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.modules.activate', $slug) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">فعال</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.modules.export', $slug) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">📥 خروجی</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3 style="margin:2rem 0 1rem;">کامپوننت‌ها</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>نام</th>
                <th>نامک</th>
                <th>نسخه</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($components as $slug => $component)
            <tr>
                <td>{{ $component->getName() }}</td>
                <td>{{ $slug }}</td>
                <td>{{ $component->getVersion() }}</td>
                <td>
                    @if($component->isActive())
                        <span class="badge badge-success">فعال</span>
                    @else
                        <span class="badge badge-danger">غیرفعال</span>
                    @endif
                </td>
                <td>
                    @if($component->isActive())
                        <form method="POST" action="{{ route('admin.modules.deactivate', $slug) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning" data-confirm="غیرفعال شود؟">غیرفعال</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.modules.activate', $slug) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">فعال</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
