<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نصب NeuroCMS</title>
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
        }
        .installer-box {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .logo { font-size: 4rem; margin-bottom: 1rem; }
        h1 { color: #1f2937; margin-bottom: 1rem; }
        p { color: #6b7280; margin-bottom: 2rem; line-height: 1.8; }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 1rem 3rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            transition: transform 0.3s;
            border: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 1rem;
        }
        .btn:hover { transform: translateY(-2px); }
        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin: 2rem 0;
            text-align: right;
        }
        .feature {
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 10px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="installer-box">
        <div class="logo">🧠</div>
        <h1>NeuroCMS</h1>
        <p>سیستم مدیریت محتوای هوشمند و ماژولار</p>
        <div class="features">
            <div class="feature">✅ هسته هوش مصنوعی</div>
            <div class="feature">✅ معماری ماژولار</div>
            <div class="feature">✅ چندزبانه</div>
            <div class="feature">✅ امنیت پیشرفته</div>
        </div>
        <a href="{{ route('installer.requirements') }}" class="btn">شروع نصب</a>
    </div>
</body>
</html>
