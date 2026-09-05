<?php
/**
 * NeuroCMS - Installer
 *
 * @author     Hooman Oliaei (هومان اولیائی)
 * @copyright  Copyright (c) 2026 Hooman Oliaei
 * @license    GNU General Public License v3.0
 * @link       https://github.com/ordoo757
 */

// ============================================================
// ===== تنظیمات اولیه =====
// ============================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

// بررسی وجود فایل قفل نصب
if (file_exists(__DIR__ . '/storage/.installed')) {
    header('Location: ../');
    exit;
}

// ============================================================
// ===== بارگذاری فایل‌های مورد نیاز =====
// ============================================================

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Language.php';
require_once __DIR__ . '/includes/Requirements.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Installer.php';
require_once __DIR__ . '/includes/License.php';

// ============================================================
// ===== مدیریت زبان =====
// ============================================================

$lang = new Language();
$currentLang = $lang->getCurrentLanguage();

// ============================================================
// ===== مدیریت مراحل =====
// ============================================================

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$maxSteps = 8;

// جلوگیری از دسترسی به مراحل بعدی بدون تکمیل قبلی
if ($step > 1 && !Installer::isStepCompleted($step - 1)) {
    $step = 1;
}

// ============================================================
// ===== نمایش مرحله =====
// ============================================================

$stepFile = __DIR__ . '/steps/' . $step . '-' . getStepFileName($step) . '.php';

if (!file_exists($stepFile)) {
    die('Step not found!');
}

$stepTitle = getStepTitle($step, $lang);

?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>" dir="<?php echo $lang->isRTL() ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeuroCMS - نصب | <?php echo $stepTitle; ?></title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/installer.css">
</head>
<body>
    <div class="installer-container">
        <!-- Header -->
        <div class="installer-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="installer-title">
                            <i class="fas fa-cube"></i>
                            NeuroCMS
                            <small>نسخه 2.0.0</small>
                        </h1>
                    </div>
                    <div class="col-md-6 text-end">
                        <?php echo $lang->renderLanguageSwitcher(); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="installer-progress">
            <div class="container">
                <div class="progress-steps">
                    <?php for ($i = 1; $i <= $maxSteps; $i++): ?>
                        <div class="step-item <?php echo $i < $step ? 'completed' : ''; ?> <?php echo $i == $step ? 'active' : ''; ?>">
                            <div class="step-circle">
                                <?php if ($i < $step): ?>
                                    <i class="fas fa-check"></i>
                                <?php else: ?>
                                    <?php echo $i; ?>
                                <?php endif; ?>
                            </div>
                            <div class="step-label"><?php echo getStepLabel($i, $lang); ?></div>
                        </div>
                        <?php if ($i < $maxSteps): ?>
                            <div class="step-line <?php echo $i < $step ? 'completed' : ''; ?>"></div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="installer-content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="step-container">
                            <div class="step-header">
                                <h2><?php echo $stepTitle; ?></h2>
                                <p class="text-muted"><?php echo getStepDescription($step, $lang); ?></p>
                            </div>

                            <div class="step-body">
                                <?php include $stepFile; ?>
                            </div>

                            <div class="step-footer">
                                <div class="d-flex justify-content-between">
                                    <?php if ($step > 1): ?>
                                        <a href="?step=<?php echo $step - 1; ?>" class="btn btn-secondary">
                                            <i class="fas fa-arrow-<?php echo $lang->isRTL() ? 'right' : 'left'; ?>"></i>
                                            <?php echo $lang->get('previous'); ?>
                                        </a>
                                    <?php else: ?>
                                        <div></div>
                                    <?php endif; ?>

                                    <?php if ($step < $maxSteps): ?>
                                        <button type="submit" form="installer-form" class="btn btn-primary btn-next">
                                            <?php echo $lang->get('next'); ?>
                                            <i class="fas fa-arrow-<?php echo $lang->isRTL() ? 'left' : 'right'; ?>"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" form="installer-form" class="btn btn-success btn-install">
                                            <i class="fas fa-rocket"></i>
                                            <?php echo $lang->get('install'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="installer-footer">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center">
                        <p>
                            &copy; 2026 <a href="https://github.com/ordoo757" target="_blank">Hooman Oliaei (هومان اولیائی)</a>
                            &mdash; NeuroCMS
                            <br>
                            <small>
                                <?php echo $lang->get('powered_by'); ?>
                                <a href="https://laravel.com" target="_blank">Laravel</a>
                                &amp;
                                <a href="https://deepseek.com" target="_blank">DeepSeek AI</a>
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/installer.js"></script>
</body>
</html>
