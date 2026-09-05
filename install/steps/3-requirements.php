<?php
$requirements = new Requirements();
$results = $requirements->checkAll();
$passed = $requirements->isPassed();
?>

<div class="requirements-step">
    <div class="alert alert-<?php echo $passed ? 'success' : 'danger'; ?>">
        <i class="fas fa-<?php echo $passed ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo $passed ? $lang->get('requirements_passed') : $lang->get('requirements_failed'); ?>
    </div>

    <div class="requirements-list">
        <?php foreach ($results as $item): ?>
            <div class="requirement-item <?php echo $item['passed'] ? 'passed' : 'failed'; ?>">
                <div class="d-flex align-items-center">
                    <div class="requirement-status">
                        <i class="fas fa-<?php echo $item['passed'] ? 'check-circle text-success' : 'times-circle text-danger'; ?>"></i>
                    </div>
                    <div class="requirement-info flex-grow-1">
                        <div class="requirement-name">
                            <?php echo $item['name']; ?>
                            <?php if (isset($item['version'])): ?>
                                <span class="badge bg-secondary"><?php echo $item['version']; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="requirement-details">
                            <?php echo $item['details']; ?>
                        </div>
                        <?php if (!$item['passed'] && isset($item['solution'])): ?>
                            <div class="requirement-solution text-danger">
                                <i class="fas fa-lightbulb"></i>
                                <?php echo $item['solution']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="requirement-status-text">
                        <span class="badge bg-<?php echo $item['passed'] ? 'success' : 'danger'; ?>">
                            <?php echo $item['passed'] ? '✓' : '✗'; ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!$passed): ?>
        <div class="alert alert-warning mt-3">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>توجه:</strong>
            لطفاً مشکلات فوق را برطرف کنید و سپس صفحه را refresh کنید.
        </div>
    <?php endif; ?>

    <form id="installer-form" method="POST" action="?step=4">
        <input type="hidden" name="step" value="3">
        <input type="hidden" name="action" value="proceed">
        <input type="hidden" name="requirements_passed" value="<?php echo $passed ? '1' : '0'; ?>">
    </form>
</div>

<style>
.requirement-item {
    padding: 12px 15px;
    margin-bottom: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #6c757d;
    transition: all 0.3s ease;
}
.requirement-item.passed {
    border-left-color: #28a745;
}
.requirement-item.failed {
    border-left-color: #dc3545;
}
.requirement-status {
    width: 30px;
    font-size: 20px;
    margin-right: 15px;
}
.requirement-info {
    margin: 0 10px;
}
.requirement-name {
    font-weight: 600;
}
.requirement-details {
    color: #6c757d;
    font-size: 14px;
}
.requirement-solution {
    font-size: 13px;
    margin-top: 5px;
    padding: 5px 10px;
    background: #fff3cd;
    border-radius: 4px;
}
</style>

<script>
document.querySelector('.btn-next').addEventListener('click', function(e) {
    if (document.querySelector('input[name="requirements_passed"]').value !== '1') {
        e.preventDefault();
        alert('لطفاً ابتدا مشکلات نیازمندی‌ها را برطرف کنید.');
        return false;
    }
});
</script>
