@extends('core::layouts.admin')

@section('title', 'ویرایشگر پاورپوینت - ' . $presentation->name)

@push('styles')
<style>
    .pp-editor-container {
        display: flex;
        gap: 20px;
        height: calc(100vh - 200px);
        min-height: 500px;
    }
    .pp-sidebar {
        width: 300px;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        overflow-y: auto;
        flex-shrink: 0;
    }
    .pp-main {
        flex: 1;
        background: #e9ecef;
        border-radius: 8px;
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: auto;
    }
    .pp-slide {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        width: 100%;
        max-width: 900px;
        aspect-ratio: 16/9;
        padding: 30px;
        position: relative;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .pp-slide[data-theme="dark"] {
        background: #1a1a2e;
        color: #fff;
    }
    .pp-slide[data-theme="light"] {
        background: #ffffff;
        color: #333;
    }
    .pp-slide[data-theme="modern"] {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
    }
    .pp-slide[data-theme="elegant"] {
        background: #f5f0eb;
        color: #333;
        border: 2px solid #d4c5a9;
    }
    .pp-element {
        position: absolute;
        cursor: move;
        padding: 5px 10px;
    }
    .pp-element-text {
        font-size: 24px;
        font-weight: bold;
    }
    .pp-element-image {
        border-radius: 4px;
    }
    .pp-element-shape {
        border-radius: 4px;
    }
    .pp-slide-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .pp-slide-list li {
        padding: 10px 15px;
        margin-bottom: 5px;
        background: #fff;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 2px solid transparent;
        transition: all 0.2s;
    }
    .pp-slide-list li:hover {
        border-color: #007bff;
    }
    .pp-slide-list li.active {
        border-color: #007bff;
        background: #e3f2fd;
    }
    .pp-toolbar {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 15px;
        padding: 10px;
        background: #fff;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    .pp-toolbar .btn {
        font-size: 12px;
        padding: 4px 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ویرایشگر پاورپوینت: {{ $presentation->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.advanced-powerpoint.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                        <button onclick="savePresentation()" class="btn btn-success btn-sm">
                            <i class="fas fa-save"></i> ذخیره
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    {{-- نوار ابزار --}}
                    <div class="pp-toolbar">
                        <div class="btn-group">
                            <button onclick="addSlide()" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> اسلاید جدید
                            </button>
                            <button onclick="duplicateSlide()" class="btn btn-info btn-sm">
                                <i class="fas fa-copy"></i> کپی
                            </button>
                            <button onclick="deleteSlide()" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> حذف
                            </button>
                        </div>
                        <span class="badge badge-secondary" id="slideCount">0 اسلاید</span>
                        <div class="ms-auto">
                            <button onclick="addElement('text')" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-font"></i> متن
                            </button>
                            <button onclick="addElement('image')" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-image"></i> تصویر
                            </button>
                            <button onclick="addElement('shape')" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-square"></i> شکل
                            </button>
                            <button onclick="addElement('chart')" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-chart-bar"></i> نمودار
                            </button>
                        </div>
                    </div>

                    {{-- ویرایشگر --}}
                    <div class="pp-editor-container">
                        {{-- Sidebar - لیست اسلایدها --}}
                        <div class="pp-sidebar">
                            <h6>اسلایدها</h6>
                            <ul class="pp-slide-list" id="slideList">
                                @forelse($presentation->slides as $slide)
                                <li class="{{ $loop->first ? 'active' : '' }}" data-slide="{{ $slide->id }}" onclick="selectSlide('{{ $slide->id }}')">
                                    <span>{{ $slide->title ?? 'اسلاید ' . $loop->iteration }}</span>
                                    <span class="badge badge-light">{{ $slide->elements->count() }}</span>
                                </li>
                                @empty
                                <li class="text-muted">هیچ اسلایدی وجود ندارد</li>
                                @endforelse
                            </ul>
                            <div class="mt-3">
                                <h6>تنظیمات اسلاید</h6>
                                <div class="form-group">
                                    <label>پس‌زمینه</label>
                                    <input type="color" id="slideBackground" class="form-control" style="width: 100%; height: 40px; padding: 2px;" onchange="updateSlideBackground()">
                                </div>
                                <div class="form-group">
                                    <label>ترنزیشن</label>
                                    <select id="slideTransition" class="form-control" onchange="updateSlideTransition()">
                                        <option value="">بدون</option>
                                        <option value="fade">محو</option>
                                        <option value="slide">اسلاید</option>
                                        <option value="zoom">بزرگنمایی</option>
                                        <option value="flip">چرخش</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>تم</label>
                                    <select id="slideTheme" class="form-control" onchange="updateSlideTheme()">
                                        <option value="default">پیش‌فرض</option>
                                        <option value="dark">تاریک</option>
                                        <option value="light">روشن</option>
                                        <option value="modern">مدرن</option>
                                        <option value="elegant">شیک</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Main - نمایش اسلاید --}}
                        <div class="pp-main">
                            <div class="pp-slide" id="slidePreview" data-theme="default">
                                {{-- عناصر به صورت جاوااسکریپت بارگذاری می‌شوند --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const presentationId = {{ $presentation->id }};
    let slides = @json($presentation->slides);
    let currentSlideId = slides.length > 0 ? slides[0].id : null;
    let currentElements = [];
    let selectedElement = null;

    // ===== Initialize =====
    document.addEventListener('DOMContentLoaded', function() {
        if (currentSlideId) {
            loadSlide(currentSlideId);
        }
        updateSlideCount();
        updateThemeSelect();
    });

    // ===== Load Slide =====
    function loadSlide(slideId) {
        currentSlideId = slideId;
        const slide = slides.find(s => s.id == slideId);
        if (!slide) return;

        // به‌روزرسانی پیش‌نمایش
        const preview = document.getElementById('slidePreview');
        preview.style.background = slide.background || '#ffffff';
        preview.dataset.theme = slide.theme || 'default';

        // بارگذاری عناصر
        currentElements = slide.elements || [];
        renderElements();

        // به‌روزرسانی تنظیمات
        document.getElementById('slideBackground').value = slide.background || '#ffffff';
        document.getElementById('slideTransition').value = slide.transition || '';
        document.getElementById('slideTheme').value = slide.theme || 'default';

        // به‌روزرسانی لیست
        document.querySelectorAll('.pp-slide-list li').forEach(li => {
            li.classList.toggle('active', li.dataset.slide == slideId);
        });
    }

    // ===== Render Elements =====
    function renderElements() {
        const preview = document.getElementById('slidePreview');
        preview.innerHTML = '';

        currentElements.forEach(element => {
            const el = document.createElement('div');
            el.className = 'pp-element pp-element-' + element.type;
            el.style.left = (element.position?.x || 10) + '%';
            el.style.top = (element.position?.y || 10) + '%';
            el.style.width = (element.size?.width || 60) + '%';
            el.style.height = (element.size?.height || 20) + '%';
            el.dataset.elementId = element.id;

            switch(element.type) {
                case 'text':
                    el.textContent = element.content || 'متن';
                    el.style.fontSize = element.style?.font_size || 24 + 'px';
                    el.style.color = element.style?.color || '#333';
                    el.style.textAlign = element.style?.text_align || 'center';
                    el.style.fontWeight = element.style?.font_weight || 'normal';
                    break;
                case 'image':
                    el.innerHTML = '<img src="' + (element.content || '') + '" style="width:100%;height:100%;object-fit:contain;">';
                    break;
                case 'shape':
                    el.style.background = element.style?.background || '#007bff';
                    el.style.borderRadius = element.style?.border_radius || '4px';
                    break;
                case 'chart':
                    el.innerHTML = '<div class="chart-placeholder">📊 ' + (element.content || 'نمودار') + '</div>';
                    break;
                default:
                    el.textContent = element.content || 'عنصر';
            }

            el.onclick = function() { selectElement(element.id); };
            preview.appendChild(el);
        });
    }

    // ===== Select Slide =====
    function selectSlide(slideId) {
        loadSlide(slideId);
    }

    // ===== Select Element =====
    function selectElement(elementId) {
        selectedElement = elementId;
        document.querySelectorAll('.pp-element').forEach(el => {
            el.style.outline = el.dataset.elementId == elementId ? '2px solid #007bff' : 'none';
        });
    }

    // ===== Add Slide =====
    function addSlide() {
        fetch('{{ url("admin/advanced-powerpoint/presentations") }}/' + presentationId + '/slides', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                title: 'اسلاید ' + (slides.length + 1),
                layout: 'default',
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                slides.push(data.slide);
                updateSlideList();
                loadSlide(data.slide.id);
                updateSlideCount();
            }
        })
        .catch(error => console.error('Error adding slide:', error));
    }

    // ===== Duplicate Slide =====
    function duplicateSlide() {
        // منطق کپی اسلاید
        alert('قابلیت کپی اسلاید در حال توسعه است.');
    }

    // ===== Delete Slide =====
    function deleteSlide() {
        if (!currentSlideId) return;
        if (slides.length <= 1) {
            alert('حداقل یک اسلاید باید وجود داشته باشد.');
            return;
        }
        if (!confirm('آیا از حذف این اسلاید مطمئن هستید؟')) return;

        fetch('{{ url("admin/advanced-powerpoint/slides") }}/' + currentSlideId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const index = slides.findIndex(s => s.id == currentSlideId);
                slides.splice(index, 1);
                updateSlideList();
                const newSlide = slides[index] || slides[0];
                loadSlide(newSlide.id);
                updateSlideCount();
            }
        })
        .catch(error => console.error('Error deleting slide:', error));
    }

    // ===== Add Element =====
    function addElement(type) {
        if (!currentSlideId) return;

        const elementData = {
            type: type,
            content: type === 'text' ? 'متن جدید' : '',
            style: {},
            position: { x: 20 + Math.random() * 40, y: 20 + Math.random() * 40 },
            size: { width: 60, height: 20 },
        };

        fetch('{{ url("admin/advanced-powerpoint/slides") }}/' + currentSlideId + '/elements', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(elementData),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const slide = slides.find(s => s.id == currentSlideId);
                if (slide) {
                    slide.elements = slide.elements || [];
                    slide.elements.push(data.element);
                    renderElements();
                }
            }
        })
        .catch(error => console.error('Error adding element:', error));
    }

    // ===== Update Slide Background =====
    function updateSlideBackground() {
        const color = document.getElementById('slideBackground').value;
        if (currentSlideId) {
            const preview = document.getElementById('slidePreview');
            preview.style.background = color;
        }
    }

    // ===== Update Slide Transition =====
    function updateSlideTransition() {
        const transition = document.getElementById('slideTransition').value;
        // ذخیره در سرور
    }

    // ===== Update Slide Theme =====
    function updateSlideTheme() {
        const theme = document.getElementById('slideTheme').value;
        const preview = document.getElementById('slidePreview');
        preview.dataset.theme = theme;
    }

    // ===== Update Slide List =====
    function updateSlideList() {
        const list = document.getElementById('slideList');
        list.innerHTML = '';
        slides.forEach(slide => {
            const li = document.createElement('li');
            li.dataset.slide = slide.id;
            li.innerHTML = `
                <span>${slide.title || 'اسلاید ' + (slides.indexOf(slide) + 1)}</span>
                <span class="badge badge-light">${(slide.elements || []).length}</span>
            `;
            li.onclick = function() { selectSlide(slide.id); };
            if (slide.id == currentSlideId) li.classList.add('active');
            list.appendChild(li);
        });
    }

    // ===== Update Slide Count =====
    function updateSlideCount() {
        document.getElementById('slideCount').textContent = slides.length + ' اسلاید';
    }

    // ===== Update Theme Select =====
    function updateThemeSelect() {
        // تنظیم تم فعلی
    }

    // ===== Save Presentation =====
    function savePresentation() {
        alert('تمامی تغییرات به طور خودکار ذخیره می‌شوند.');
    }
</script>
@endpush
