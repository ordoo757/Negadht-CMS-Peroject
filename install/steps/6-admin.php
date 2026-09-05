<?php
$defaults = [
    'username' => $_SESSION['admin_config']['username'] ?? 'admin',
    'email' => $_SESSION['admin_config']['email'] ?? 'admin@neurocms.ir',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'proceed') {
    $username = $_POST['admin_username'] ?? '';
    $email = $_POST['admin_email'] ?? '';
    $password = $_POST['admin_password'] ?? '';
    $confirm = $_POST['admin_password_confirm'] ?? '';

    $errors = [];

    if (empty($username)) {
        $errors[] = $lang->get('required');
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $lang->get('email');
    }
    if (strlen($password) < 8) {
        $errors[] = str_replace(':min', '8', $lang->get('min'));
    }
    if ($password !== $confirm) {
        $errors[] = $lang->get('admin_password_mismatch');
    }

    if (empty($errors)) {
        $_SESSION['admin_config'] = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ];
        // ادامه به مرحله بعد
    } else {
        $_SESSION['admin_errors'] = $errors;
    }
}

$errors = $_SESSION['admin_errors'] ?? [];
unset($_SESSION['admin_errors']);
?>

<div class="admin-step">
    <div class="alert alert-info">
        <i class="fas fa-user-shield"></i>
        <strong>ایجاد حساب کاربری مدیر</strong>
        <p class="mt-2 mb-0">اطلاعات حساب کاربری مدیر سیستم را وارد کنید.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <ul class="mb-0 mt-2">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form id="installer-form" method="POST" action="?step=6">
        <input type="hidden" name="step" value="6">
        <input type="hidden" name="action" value="proceed">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="admin_username"><?php echo $lang->get('admin_username'); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="admin_username" name="admin_username" 
                           value="<?php echo htmlspecialchars($defaults['username']); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="admin_email"><?php echo $lang->get('admin_email'); ?> <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="admin_email" name="admin_email" 
                           value="<?php echo htmlspecialchars($defaults['email']); ?>" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="admin_password"><?php echo $lang->get('admin_password'); ?> <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="admin_password" name="admin_password" 
                           minlength="8" required>
                    <small class="text-muted"><?php echo $lang->get('admin_password_weak'); ?></small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="admin_password_confirm"><?php echo $lang->get('admin_password_confirm'); ?> <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="admin_password_confirm" name="admin_password_confirm" 
                           minlength="8" required>
                </div>
            </div>
        </div>

        <div class="password-strength mt-2">
            <div class="progress" style="height: 5px;">
                <div id="passwordStrengthBar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
            </div>
            <small id="passwordStrengthText" class="text-muted">قدرت رمز عبور: ضعیف</small>
        </div>
    </form>
</div>

<script>
document.getElementById('admin_password').addEventListener('input', function() {
    const password = this.value;
    const bar = document.getElementById('passwordStrengthBar');
    const text = document.getElementById('passwordStrengthText');

    let score = 0;
    if (password.length >= 8) score += 25;
    if (password.match(/[a-z]/)) score += 25;
    if (password.match(/[A-Z]/)) score += 25;
    if (password.match(/[0-9]/) || password.match(/[^a-zA-Z0-9]/)) score += 25;

    bar.style.width = score + '%';

    if (score <= 25) {
        bar.className = 'progress-bar bg-danger';
        text.textContent = 'قدرت رمز عبور: ضعیف';
    } else if (score <= 50) {
        bar.className = 'progress-bar bg-warning';
        text.textContent = 'قدرت رمز عبور: متوسط';
    } else if (score <= 75) {
        bar.className = 'progress-bar bg-info';
        text.textContent = 'قدرت رمز عبور: خوب';
    } else {
        bar.className = 'progress-bar bg-success';
        text.textContent = 'قدرت رمز عبور: عالی';
    }
});

document.querySelector('.btn-next').addEventListener('click', function(e) {
    const password = document.getElementById('admin_password').value;
    const confirm = document.getElementById('admin_password_confirm').value;

    if (password !== confirm) {
        e.preventDefault();
        alert('<?php echo $lang->get('admin_password_mismatch'); ?>');
        return false;
    }

    if (password.length < 8) {
        e.preventDefault();
        alert('رمز عبور حداقل باید ۸ کاراکتر باشد.');
        return false;
    }
});
</script>
