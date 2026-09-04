<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بررسی نیازمندی‌ها - NeuroCMS</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .box {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
        }
        h1 { margin-bottom: 1.5rem; color: #1f2937; }
        .check-group { margin-bottom: 1.5rem; }
        .check-group h3 { margin-bottom: 0.75rem; color: #4b5563; font-size: 1rem; }
        .check-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        .check-item.pass { border-right: 4px solid #10b981; }
        .check-item.fail { border-right: 4px solid #ef4444; }
        .status { font-weight: bold; }
        .pass .status { color: #10b981; }
        .fail .status { color: #ef4444; }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 1rem;
            margin-top: 1rem;
        }
        .btn:disabled { background: #9ca3af; cursor: not-allowed; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🔍 بررسی نیازمندی‌های سیستم</h1>

        @if($checks['all_passed'])
            <div class="alert alert-success">✅ تمام نیازمندی‌ها برآورده شده‌اند!</div>
        @else
            <div class="alert alert-danger">❌ برخی از نیازمندی‌ها برآورده نشده‌اند.</div>
        @endif

        <div class="check-group">
            <h3>نسخه PHP</h3>
            <div class="check-item {{ $checks['php_version'] ? 'pass' : 'fail' }}">
                <span>PHP >= 8.3</span>
                <span class="status">{{ $checks['php_version'] ? '✅ ' . $checks['php_version_current'] : '❌ ' . $checks['php_version_current'] }}</span>
            </div>
        </div>

        <div class="check-group">
            <h3>افزونه‌های PHP</h3>
            @foreach($checks['extensions'] as $ext => $loaded)
                <div class="check-item {{ $loaded ? 'pass' : 'fail' }}">
                    <span>{{ $ext }}</span>
                    <span class="status">{{ $loaded ? '✅ نصب شده' : '❌ نصب نشده' }}</span>
                </div>
            @endforeach
        </div>

        <div class="check-group">
            <h3>دسترسی نوشتن</h3>
            @foreach($checks['writable'] as $path => $writable)
                <div class="check-item {{ $writable ? 'pass' : 'fail' }}">
                    <span>{{ $path }}</span>
                    <span class="status">{{ $writable ? '✅ قابل نوشتن' : '❌ غیرقابل نوشتن' }}</span>
                </div>
            @endforeach
        </div>

        <div style="text-align: center;">
            @if($checks['all_passed'])
                <a href="{{ route('installer.database') }}" class="btn">ادامه نصب</a>
            @else
                <button class="btn" disabled>لطفاً نیازمندی‌ها را برطرف کنید</button>
            @endif
        </div>
    </div>
</body>
</html>
