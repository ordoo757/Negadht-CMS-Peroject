<?php
$db = new Database();
$connectionTested = false;
$connectionSuccess = false;
$connectionMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'test_connection') {
        $host = $_POST['db_host'] ?? 'localhost';
        $port = $_POST['db_port'] ?? '3306';
        $name = $_POST['db_name'] ?? '';
        $user = $_POST['db_user'] ?? '';
        $pass = $_POST['db_pass'] ?? '';

        $result = $db->testConnection($host, $port, $name, $user, $pass);
        $connectionTested = true;
        $connectionSuccess = $result['success'];
        $connectionMessage = $result['message'];

        if ($connectionSuccess) {
            $_SESSION['db_config'] = [
                'host' => $host,
                'port' => $port,
                'name' => $name,
                'user' => $user,
                'pass' => $pass,
            ];
        }

        // Return JSON for AJAX
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $connectionSuccess,
                'message' => $connectionMessage,
            ]);
            exit;
        }
    }

    if ($action === 'proceed' && isset($_POST['db_host'])) {
        $_SESSION['db_config'] = [
            'host' => $_POST['db_host'],
            'port' => $_POST['db_port'],
            'name' => $_POST['db_name'],
            'user' => $_POST['db_user'],
            'pass' => $_POST['db_pass'],
        ];
    }
}

$defaults = [
    'host' => $_SESSION['db_config']['host'] ?? 'localhost',
    'port' => $_SESSION['db_config']['port'] ?? '3306',
    'name' => $_SESSION['db_config']['name'] ?? 'neurocms',
    'user' => $_SESSION['db_config']['user'] ?? 'root',
    'pass' => $_SESSION['db_config']['pass'] ?? '',
];
?>

<div class="database-step">
    <div class="alert alert-info">
        <i class="fas fa-database"></i>
        <strong>تنظیمات دیتابیس</strong>
        <p class="mt-2 mb-0">لطفاً اطلاعات اتصال به دیتابیس MySQL/MariaDB را وارد کنید.</p>
    </div>

    <?php if ($connectionTested): ?>
        <div class="alert alert-<?php echo $connectionSuccess ? 'success' : 'danger'; ?>">
            <i class="fas fa-<?php echo $connectionSuccess ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $connectionMessage; ?>
        </div>
    <?php endif; ?>

    <form id="installer-form" method="POST" action="?step=5">
        <input type="hidden" name="step" value="4">
        <input type="hidden" name="action" value="proceed">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="db_host"><?php echo $lang->get('db_host'); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="db_host" name="db_host" 
                           value="<?php echo htmlspecialchars($defaults['host']); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="db_port"><?php echo $lang->get('db_port'); ?></label>
                    <input type="number" class="form-control" id="db_port" name="db_port" 
                           value="<?php echo htmlspecialchars($defaults['port']); ?>">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="db_name"><?php echo $lang->get('db_name'); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="db_name" name="db_name" 
                           value="<?php echo htmlspecialchars($defaults['name']); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="db_user"><?php echo $lang->get('db_user'); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="db_user" name="db_user" 
                           value="<?php echo htmlspecialchars($defaults['user']); ?>" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="db_pass"><?php echo $lang->get('db_pass'); ?></label>
                    <input type="password" class="form-control" id="db_pass" name="db_pass" 
                           value="<?php echo htmlspecialchars($defaults['pass']); ?>">
                </div>
            </div>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="db_create" name="db_create" value="1">
            <label class="form-check-label" for="db_create">
                <?php echo $lang->get('db_create'); ?>
            </label>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-info" id="testConnection">
                <i class="fas fa-plug"></i> <?php echo $lang->get('db_test'); ?>
            </button>
            <div id="testResult" class="mt-2"></div>
        </div>
    </form>
</div>

<script>
document.getElementById('testConnection').addEventListener('click', function() {
    const form = document.getElementById('installer-form');
    const formData = new FormData(form);
    formData.append('action', 'test_connection');
    formData.append('ajax', '1');

    const resultDiv = document.getElementById('testResult');
    resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm text-info" role="status"></div> در حال تست...';

    fetch('?step=4', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        resultDiv.innerHTML = `<div class="alert alert-${data.success ? 'success' : 'danger'} mt-2">
            <i class="fas fa-${data.success ? 'check-circle' : 'exclamation-circle'}"></i>
            ${data.message}
        </div>`;
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class="alert alert-danger mt-2">خطا در تست اتصال</div>';
    });
});

document.querySelector('.btn-next').addEventListener('click', function(e) {
    const host = document.getElementById('db_host').value;
    const name = document.getElementById('db_name').value;
    const user = document.getElementById('db_user').value;

    if (!host || !name || !user) {
        e.preventDefault();
        alert('لطفاً همه فیلدهای ضروری را پر کنید.');
        return false;
    }
});
</script>
