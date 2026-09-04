<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'NeuroCMS' }}</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('templates/site/default/css/style.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <h1>🧠 {{ $siteName ?? 'NeuroCMS' }}</h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="/">{{ trans('messages.home') }}</a></li>
                    <li><a href="#about">{{ trans('messages.about') }}</a></li>
                    <li><a href="#contact">{{ trans('messages.contact') }}</a></li>
                    <li><a href="/admin">{{ trans('messages.dashboard') }}</a></li>
                </ul>
            </nav>
            <div class="lang-switcher">
                <a href="{{ route('lang.switch', 'fa') }}">FA</a>
                <a href="{{ route('lang.switch', 'ar') }}">AR</a>
                <a href="{{ route('lang.switch', 'en') }}">EN</a>
            </div>
        </div>
    </header>

    <div class="hero-section">
        <div class="container">
            <h1>{{ trans('messages.welcome') }}</h1>
            <p>سیستم مدیریت محتوای هوشمند و ماژولار با قابلیت‌های AI</p>
            <a href="/install" class="btn btn-primary">شروع نصب</a>
        </div>
    </div>

    <div class="site-wrapper">
        <aside class="sidebar">
            <div class="widget">
                <h3>ویژگی‌ها</h3>
                <ul>
                    <li>✅ هسته هوش مصنوعی</li>
                    <li>✅ معماری ماژولار</li>
                    <li>✅ چندزبانه</li>
                    <li>✅ امنیت پیشرفته</li>
                </ul>
            </div>
        </aside>

        <main class="content">
            {!! $content ?? '' !!}
        </main>
    </div>

    <footer class="site-footer">
        <div class="container">
            <p>{{ trans('messages.copyright', ['year' => date('Y')]) }}</p>
        </div>
    </footer>

    <script src="{{ asset('templates/site/default/js/main.js') }}"></script>
</body>
</html>
