/**
 * NeuroCMS Installer JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // ============================================================
    // ===== Auto-submit for checkboxes =====
    // ============================================================
    document.querySelectorAll('input[type="checkbox"][data-auto-submit]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });

    // ============================================================
    // ===== Password visibility toggle =====
    // ============================================================
    document.querySelectorAll('.toggle-password').forEach(function(button) {
        button.addEventListener('click', function() {
            const input = document.getElementById(this.dataset.target);
            if (input) {
                input.type = input.type === 'password' ? 'text' : 'password';
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            }
        });
    });

    // ============================================================
    // ===== Copy to clipboard =====
    // ============================================================
    document.querySelectorAll('.copy-to-clipboard').forEach(function(button) {
        button.addEventListener('click', function() {
            const text = this.dataset.text;
            if (text) {
                navigator.clipboard.writeText(text).then(function() {
                    const original = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-check"></i> کپی شد!';
                    setTimeout(function() {
                        button.innerHTML = original;
                    }, 2000);
                });
            }
        });
    });

    // ============================================================
    // ===== Progress bar animation =====
    // ============================================================
    const progressSteps = document.querySelectorAll('.step-item');
    if (progressSteps.length > 0) {
        progressSteps.forEach(function(step, index) {
            setTimeout(function() {
                step.classList.add('fade-in');
            }, index * 100);
        });
    }

    console.log('NeuroCMS Installer loaded successfully!');
});
