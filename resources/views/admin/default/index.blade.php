<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'NeuroCMS Admin' }}</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('templates/admin/default/css/admin.css') }}">
    @stack('styles')
</head>
<body class="admin-panel">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>🧠 NeuroCMS</h2>
            <p>v2.0.0</p>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="icon">📊</span>
                <span>داشبورد</span>
            </a>
            <div class="nav-group">
                <div class="nav-group-title">مدیریت</div>
                <a href="{{ route('admin.modules.index') }}" class="nav-item {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}">
                    <span class="icon">📦</span>
                    <span>ماژول‌ها</span>
                </a>
                <a href="#" class="nav-item">
                    <span class="icon">👤</span>
                    <span>کاربران</span>
                </a>
                <a href="#" class="nav-item">
                    <span class="icon">🌐</span>
                    <span>زبان‌ها</span>
                </a>
            </div>
            <div class="nav-group">
                <div class="nav-group-title">محتوا</div>
                <a href="#" class="nav-item">
                    <span class="icon">📝</span>
                    <span>فرم‌ها</span>
                </a>
                <a href="#" class="nav-item">
                    <span class="icon">📋</span>
                    <span>جداول</span>
                </a>
                <a href="#" class="nav-item">
                    <span class="icon">📈</span>
                    <span>گزارش‌ها</span>
                </a>
            </div>
            <div class="nav-group">
                <div class="nav-group-title">ظاهر</div>
                <a href="#" class="nav-item">
                    <span class="icon">🎨</span>
                    <span>قالب‌ها</span>
                </a>
                <a href="#" class="nav-item">
                    <span class="icon">☰</span>
                    <span>منوها</span>
                </a>
            </div>
            <div class="nav-group">
                <div class="nav-group-title">هوش مصنوعی</div>
                <a href="#" class="nav-item">
                    <span class="icon">🧠</span>
                    <span>هسته AI</span>
                </a>
                <a href="#" class="nav-item">
                    <span class="icon">🤖</span>
                    <span>دستیار هوشمند</span>
                </a>
            </div>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <button class="menu-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>
            <div class="search-box">
                <input type="text" placeholder="جستجو...">
            </div>
            <div class="user-menu">
                <a href="{{ route('lang.switch', 'fa') }}" class="lang-link {{ app()->getLocale() == 'fa' ? 'active' : '' }}">FA</a>
                <a href="{{ route('lang.switch', 'ar') }}" class="lang-link {{ app()->getLocale() == 'ar' ? 'active' : '' }}">AR</a>
                <a href="{{ route('lang.switch', 'en') }}" class="lang-link {{ app()->getLocale() == 'en' ? 'active' : '' }}">EN</a>
                <div class="user-info">
                    <span>{{ auth()->user()->name ?? 'Admin' }}</span>
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="logout-btn">🚪</button>
                    </form>
                </div>
            </div>
        </header>

        <div class="content-wrapper">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </div>
    </main>

    <script src="{{ asset('templates/admin/default/js/admin.js') }}"></script>
    @stack('scripts')
</body>
</html>
