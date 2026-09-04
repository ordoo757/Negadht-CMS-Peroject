<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $presentation->name }} - NeuroCMS PowerPoint</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Vazir', 'Segoe UI', Tahoma, sans-serif;
            background: #1a1a2e;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .pp-embed {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            max-width: 1000px;
            width: 100%;
            overflow: hidden;
        }
        .pp-embed-header {
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        .pp-embed-header h1 {
            font-size: 24px;
            color: #333;
        }
        .pp-embed-header .description {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .pp-embed-slide {
            padding: 40px;
            min-height: 500px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: #ffffff;
            transition: background 0.5s ease;
        }
        .pp-embed-slide[data-theme="dark"] {
            background: #1a1a2e;
            color: #fff;
        }
        .pp-embed-slide[data-theme="light"] {
            background: #ffffff;
            color: #333;
        }
        .pp-embed-slide[data-theme="modern"] {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        .pp-embed-slide[data-theme="elegant"] {
            background: #f5f0eb;
            color: #333;
        }
        .pp-embed-slide h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }
        .pp-embed-slide p {
            font-size: 18px;
            max-width: 600px;
            line-height: 1.8;
        }
        .pp-embed-slide .slide-number {
            position: absolute;
            bottom: 20px;
            right: 30px;
            font-size: 14px;
            color: rgba(0,0,0,0.3);
        }
        .pp-embed-slide[data-theme="dark"] .slide-number {
            color: rgba(255,255,255,0.3);
        }
        .pp-embed-footer {
            padding: 15px 30px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pp-embed-footer .nav-buttons {
            display: flex;
            gap: 10px;
        }
        .pp-embed-footer .nav-buttons button {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }
        .pp-embed-footer .nav-buttons button:hover {
            transform: scale(1.05);
        }
        .pp-embed-footer .nav-buttons .prev {
            background: #e9ecef;
            color: #333;
        }
        .pp-embed-footer .nav-buttons .next {
            background: #007bff;
            color: #fff;
        }
        .pp-embed-footer .slide-indicator {
            font-size: 14px;
            color: #666;
        }
        .pp-embed-footer .brand {
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="pp-embed">
        <div class="pp-embed-header">
            <h1>{{ $presentation->name }}</h1>
            @if($presentation->description)
                <div class="description">{{ $presentation->description }}</div>
            @endif
        </div>

        <div class="pp-embed-slide" id="slideContainer" data-theme="{{ $presentation->theme ?? 'default' }}">
            @php
                $slides = $presentation->slides->sortBy('order');
                $firstSlide = $slides->first();
            @endphp

            @if($firstSlide)
                @php
                    $elements = $firstSlide->elements->sortBy('order');
                    $texts = $elements->where('type', 'text');
                    $title = $texts->first()->content ?? $firstSlide->title ?? 'اسلاید ۱';
                    $description = $texts->skip(1)->first()->content ?? '';
                @endphp
                <h2>{{ $title }}</h2>
                @if($description)
                    <p>{{ $description }}</p>
                @endif
                <div class="slide-number">{{ $loop->iteration }} / {{ $slides->count() }}</div>
            @else
                <h2>هیچ اسلایدی وجود ندارد</h2>
            @endif
        </div>

        <div class="pp-embed-footer">
            <div class="brand">NeuroCMS &bull; PowerPoint</div>
            <div class="slide-indicator" id="slideIndicator">اسلاید 1 از {{ $slides->count() }}</div>
            <div class="nav-buttons">
                <button class="prev" onclick="prevSlide()">قبلی</button>
                <button class="next" onclick="nextSlide()">بعدی</button>
            </div>
        </div>
    </div>

    <script>
        let currentIndex = 0;
        const slides = @json($slides->map(function($slide) {
            return [
                'id' => $slide->id,
                'title' => $slide->title,
                'background' => $slide->background,
                'theme' => $slide->theme ?? 'default',
                'elements' => $slide->elements->map(function($element) {
                    return [
                        'type' => $element->type,
                        'content' => $element->content,
                        'style' => $element->style,
                    ];
                }),
            ];
        }));

        function renderSlide(index) {
            const container = document.getElementById('slideContainer');
            const slide = slides[index];
            if (!slide) return;

            container.dataset.theme = slide.theme || 'default';
            container.style.background = slide.background || '';

            // یافتن عناصر متنی
            const texts = slide.elements.filter(el => el.type === 'text');
            const title = texts.length > 0 ? texts[0].content : slide.title || 'اسلاید ' + (index + 1);
            const description = texts.length > 1 ? texts[1].content : '';

            container.innerHTML = `
                <h2>${title}</h2>
                ${description ? `<p>${description}</p>` : ''}
                <div class="slide-number">${index + 1} / ${slides.length}</div>
            `;

            // به‌روزرسانی اندیکاتور
            document.getElementById('slideIndicator').textContent = `اسلاید ${index + 1} از ${slides.length}`;
        }

        function nextSlide() {
            if (currentIndex < slides.length - 1) {
                currentIndex++;
                renderSlide(currentIndex);
            }
        }

        function prevSlide() {
            if (currentIndex > 0) {
                currentIndex--;
                renderSlide(currentIndex);
            }
        }

        // صفحه‌کلید
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                e.preventDefault();
                nextSlide();
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                e.preventDefault();
                prevSlide();
            }
        });

        // تاچ برای موبایل
        let touchStartX = 0;
        let touchEndX = 0;
        document.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });
        document.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            if (touchStartX - touchEndX > 50) nextSlide();
            if (touchEndX - touchStartX > 50) prevSlide();
        });
    </script>
</body>
</html>
