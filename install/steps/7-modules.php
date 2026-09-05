<?php
$modules = [
    'core' => [
        'name' => $lang->get('module_core'),
        'required' => true,
        'selected' => true,
    ],
    'ai' => [
        'name' => $lang->get('module_ai'),
        'required' => false,
        'selected' => true,
    ],
    'content' => [
        'name' => $lang->get('module_content'),
        'required' => false,
        'selected' => true,
    ],
    'security' => [
        'name' => $lang->get('module_security'),
        'required' => false,
        'selected' => true,
    ],
    'excel' => [
        'name' => $lang->get('module_excel'),
        'required' => false,
        'selected' => true,
    ],
    'ppt' => [
        'name' => $lang->get('module_ppt'),
        'required' => false,
        'selected' => true,
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'proceed') {
    $selected = [];
    foreach ($modules as $key => $module) {
        if ($module['required'] || isset($_POST['module_' . $key])) {
            $selected[] = $key;
        }
    }
    $_SESSION['modules_selected'] = $selected;
}
?>

<div class="modules-step">
    <div class="alert alert-info">
        <i class="fas fa-puzzle-piece"></i>
        <strong>انتخاب ماژول‌ها</strong>
        <p class="mt-2 mb-0"><?php echo $lang->get('modules_select'); ?></p>
    </div>

    <form id="installer-form" method="POST" action="?step=8">
        <input type="hidden" name="step" value="7">
        <input type="hidden" name="action" value="proceed">

        <div class="modules-grid">
            <?php foreach ($modules as $key => $module): ?>
                <div class="module-item">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="module_<?php echo $key; ?>" 
                               name="module_<?php echo $key; ?>" value="1" 
                               <?php echo $module['selected'] ? 'checked' : ''; ?>
                               <?php echo $module['required'] ? 'disabled' : ''; ?>>
                        <label class="form-check-label" for="module_<?php echo $key; ?>">
                            <?php echo $module['name']; ?>
                            <?php if ($module['required']): ?>
                                <span class="badge bg-danger">اجباری</span>
                            <?php endif; ?>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </form>
</div>

<style>
.modules-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}
.module-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}
.module-item:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}
</style>
