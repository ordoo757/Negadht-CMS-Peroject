<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    $installer = new Installer();
    $result = $installer->run();

    if ($result['success']) {
        $_SESSION['install_complete'] = true;
        $_SESSION['install_result'] = $result;
    } else {
        $_SESSION['install_error'] = $result['message'];
    }
}

$isComplete = $_SESSION['install_complete'] ?? false;
$result = $_SESSION['install_result'] ?? null;
$error = $_SESSION['install_error'] ?? null;
?>

<div class="finalize-step">
    <?php if (!$isComplete): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <strong><?php echo $lang->get('finalize_summary'); ?></strong>
            <p class="mt-2 mb-0"><?php echo $lang->get('finalize_confirm'); ?></p>
        </div>

        <div class="summary-box">
            <h5>خلاصه تنظیمات</h5>
            <table class="table table-bordered">
                <tr>
                    <th>نام سایت</th>
                    <td><?php echo htmlspecialchars($_SESSION['site_config']['name'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>آدرس سایت</th>
                    <td><?php echo htmlspecialchars($_SESSION['site_config']['url'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>منطقه زمانی</th>
                    <td><?php echo htmlspecialchars($_SESSION['site_config']['timezone'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>زبان پیش‌فرض</th>
                    <td><?php echo htmlspecialchars($_SESSION['site_config']['language'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>کاربر مدیر</th>
                    <td><?php echo htmlspecialchars($_SESSION['admin_config']['username'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>ماژول‌های انتخاب شده</th>
                    <td><?php echo implode(', ', $_SESSION['modules_selected'] ?? []); ?></td>
                </tr>
            </table>
        </div>

        <form id="installer-form" method="POST" action="?step=8">
            <input type="hidden" name="step" value="8">
            <input type="hidden" name="action" value="install">

            <div class="text-center mt-3">
                <button type="submit" class="btn btn-success btn-lg btn-install">
                    <i class="fas fa-rocket"></i>
                    <?php echo $lang->get('install'); ?>
                </button>
            </div>
        </form>

    <?php elseif ($isComplete && $result && $result['success']): ?>
        <div class="alert alert-success text-center">
            <i class="fas fa-check-circle fa-3x mb-3"></i>
            <h3><?php echo $lang->get('success_title'); ?></h3>
            <p><?php echo $lang->get('success_message'); ?></p>
            <p><?php echo $lang->get('success_delete'); ?></p>
            <a href="<?php echo $_SESSION['site_config']['url'] ?? '/'; ?>/admin" class="btn btn-primary btn-lg mt-3">
                <i class="fas fa-sign-in-alt"></i>
                <?php echo $lang->get('success_login'); ?>
            </a>
        </div>

    <?php elseif ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <h4><?php echo $lang->get('error_title'); ?></h4>
            <p><?php echo $error; ?></p>
            <p><?php echo $lang->get('error_log'); ?></p>
        </div>
    <?php endif; ?>
</div>

<style>
.summary-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 15px 0;
}
.summary-box table {
    margin-bottom: 0;
}
</style>
