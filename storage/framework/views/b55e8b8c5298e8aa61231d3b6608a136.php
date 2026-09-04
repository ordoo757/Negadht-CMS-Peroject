<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>" dir="<?php echo e(app()->getLocale() == 'en' ? 'ltr' : 'rtl'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? 'NeuroCMS Admin'); ?></title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('templates/admin/default/css/admin.css')); ?>">
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="admin-panel">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>🧠 NeuroCMS</h2>
            <p>v2.0.0</p>
        </div>
        <nav class="sidebar-nav">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                <span class="icon">📊</span>
                <span>داشبورد</span>
            </a>
            <div class="nav-group">
                <div class="nav-group-title">مدیریت</div>
                <a href="<?php echo e(route('admin.modules.index')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.modules.*') ? 'active' : ''); ?>">
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
                <a href="<?php echo e(route('lang.switch', 'fa')); ?>" class="lang-link <?php echo e(app()->getLocale() == 'fa' ? 'active' : ''); ?>">FA</a>
                <a href="<?php echo e(route('lang.switch', 'ar')); ?>" class="lang-link <?php echo e(app()->getLocale() == 'ar' ? 'active' : ''); ?>">AR</a>
                <a href="<?php echo e(route('lang.switch', 'en')); ?>" class="lang-link <?php echo e(app()->getLocale() == 'en' ? 'active' : ''); ?>">EN</a>
                <div class="user-info">
                    <span><?php echo e(auth()->user()->name ?? 'Admin'); ?></span>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" style="display:inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="logout-btn">🚪</button>
                    </form>
                </div>
            </div>
        </header>

        <div class="content-wrapper">
            <?php if(session('status')): ?>
                <div class="alert alert-success"><?php echo e(session('status')); ?></div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>

    <script src="<?php echo e(asset('templates/admin/default/js/admin.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /data/data/com.termux/files/home/neurocms/resources/views/admin/default/index.blade.php ENDPATH**/ ?>