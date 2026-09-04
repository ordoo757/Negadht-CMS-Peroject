<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ایجاد حساب مدیر - NeuroCMS</title>
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
            max-width: 500px;
            width: 100%;
        }
        h1 { margin-bottom: 1.5rem; color: #1f2937; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; margin-bottom: 0.5rem; color: #4b5563; font-weight: 500; }
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
        .alert-danger { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .alert-danger ul { margin-right: 1.5rem; }
    </style>
</head>
<body>
    <div class="box">
        <h1>👤 ایجاد حساب مدیر</h1>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('installer.finish') }}">
            @csrf
            <div class="form-group">
                <label>نام سایت</label>
                <input type="text" name="site_name" value="NeuroCMS" required>
            </div>
            <div class="form-group">
                <label>ایمیل مدیر</label>
                <input type="email" name="admin_email" placeholder="admin@example.com" required>
            </div>
            <div class="form-group">
                <label>رمز عبور</label>
                <input type="password" name="admin_password" required>
            </div>
            <div class="form-group">
                <label>تکرار رمز عبور</label>
                <input type="password" name="admin_password_confirmation" required>
            </div>
            <button type="submit" class="btn">تکمیل نصب</button>
        </form>
    </div>
</body>
</html>
