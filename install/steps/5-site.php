<?php
$timezones = [
    'Asia/Tehran' => 'Asia/Tehran (UTC+3:30)',
    'Asia/Dubai' => 'Asia/Dubai (UTC+4)',
    'UTC' => 'UTC',
    'America/New_York' => 'America/New_York (UTC-5)',
    'Europe/London' => 'Europe/London (UTC+0)',
    'Europe/Paris' => 'Europe/Paris (UTC+1)',
    'Asia/Tokyo' => 'Asia/Tokyo (UTC+9)',
    'Asia/Shanghai' => 'Asia/Shanghai (UTC+8)',
];

$defaults = [
    'name' => $_SESSION['site_config']['name'] ?? 'NeuroCMS',
    'url' => $_SESSION['site_config']['url'] ?? 'http://localhost',
    'timezone' => $_SESSION['site_config']['timezone'] ?? 'Asia/Tehran',
    'language' => $_SESSION['site_config']['language'] ?? $currentLang,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'proceed') {
    $_SESSION['site_config'] = [
        'name' => $_POST['site_name'] ?? 'NeuroCMS',
        'url' => $_POST['site_url'] ?? 'http://localhost',
        'timezone' => $_POST['site_timezone'] ?? 'Asia/Tehran',
        'language' => $_POST['site_language'] ?? $currentLang,
    ];
}
?>

<div class="site-step">
    <div class="alert alert-info">
        <i class="fas fa-globe"></i>
        <strong>تنظیمات سایت</strong>
        <p class="mt-2 mb-0">تنظیمات اصلی سایت را پیکربندی کنید.</p>
    </div>

    <form id="installer-form" method="POST" action="?step=6">
        <input type="hidden" name="step" value="5">
        <input type="hidden" name="action" value="proceed">

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="site_name"><?php echo $lang->get('site_name'); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="site_name" name="site_name" 
                           value="<?php echo htmlspecialchars($defaults['name']); ?>" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="site_url"><?php echo $lang->get('site_url'); ?> <span class="text-danger">*</span></label>
                    <input type="url" class="form-control" id="site_url" name="site_url" 
                           value="<?php echo htmlspecialchars($defaults['url']); ?>" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="site_timezone"><?php echo $lang->get('site_timezone'); ?> <span class="text-danger">*</span></label>
                    <select class="form-control" id="site_timezone" name="site_timezone" required>
                        <?php foreach ($timezones as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo $defaults['timezone'] == $value ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="site_language"><?php echo $lang->get('site_language'); ?> <span class="text-danger">*</span></label>
                    <select class="form-control" id="site_language" name="site_language" required>
                        <?php foreach ($lang->getAvailableLanguages() as $code => $name): ?>
                            <option value="<?php echo $code; ?>" <?php echo $defaults['language'] == $code ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted"><?php echo $lang->get('site_language_desc'); ?></small>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.querySelector('.btn-next').addEventListener('click', function(e) {
    const name = document.getElementById('site_name').value;
    const url = document.getElementById('site_url').value;

    if (!name || !url) {
        e.preventDefault();
        alert('لطفاً همه فیلدهای ضروری را پر کنید.');
        return false;
    }
});
</script>
