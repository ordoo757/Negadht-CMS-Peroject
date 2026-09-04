<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود - NeuroCMS</title>
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
        .login-box {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .logo { text-align: center; font-size: 3rem; margin-bottom: 0.5rem; }
        h1 { text-align: center; color: #1f2937; margin-bottom: 0.25rem; font-size: 1.5rem; }
        p.subtitle { text-align: center; color: #6b7280; margin-bottom: 2rem; font-size: 0.9rem; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; margin-bottom: 0.5rem; color: #4b5563; font-weight: 500; font-size: 0.9rem; }
        input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
        }
        input:focus { outline: none; border-color: #6366f1; }
        .btn {
            width: 100%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 1rem;
            font-weight: bold;
        }
        .alert-danger { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; }
        .alert-danger ul { margin-right: 1.5rem; }
        .back-link { text-align: center; margin-top: 1.5rem; }
        .back-link a { color: #6366f1; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo">🧠</div>
        <h1>NeuroCMS</h1>
        <p class="subtitle">ورود به پنل مدیریت</p>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="form-group">
                <label>ایمیل</label>
                <input type="email" name="email" required autofocus>
            </div>
            <div class="form-group">
                <label>رمز عبور</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">ورود</button>
        </form>

        <div class="back-link">
            <a href="/">← بازگشت به سایت</a>
        </div>
    </div>
</body>
</html>
