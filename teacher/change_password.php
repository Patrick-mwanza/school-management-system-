<?php
// teacher/change_password.php — Teacher changes their own password
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';
requireLogin();

$pdo    = getDB();
$userId = $_SESSION['user_id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPass = $_POST['current_password'] ?? '';
    $newPassword  = $_POST['new_password']    ?? '';
    $confirmPass  = $_POST['confirm_password'] ?? '';

    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPass, $user['password'])) {
        $errors[] = 'Your current password is incorrect.';
    }
    if (strlen($newPassword) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    }
    if ($newPassword !== $confirmPass) {
        $errors[] = 'New passwords do not match.';
    }

    if (empty($errors)) {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
            ->execute([$hashed, $userId]);
        redirectWithMessage('dashboard.php', 'Your password has been changed successfully.', 'success');
    }
}

$pageTitle   = 'Change My Password';
$breadcrumbs = [['label' => 'Change Password']];
include __DIR__ . '/../includes/page_start.php';
?>

<div class="card form-card">
    <div class="card-header">
        <h2>🔑 Change My Password</h2>
        <a href="dashboard.php" class="btn btn-outline btn-sm">← Dashboard</a>
    </div>
    <div class="card-body">

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" id="flashMsg">
                <?php foreach ($errors as $err): ?><div>⚠️ <?php echo e($err); ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="change_password.php" data-validate>
            <div class="form-group">
                <label for="current_password">Current Password *</label>
                <div class="password-wrapper">
                    <input type="password" id="current_password" name="current_password"
                           placeholder="Enter your current password" required>
                    <button type="button" class="eye-toggle" onclick="togglePassword('current_password', this)" title="Show/hide password">👁️</button>
                </div>
            </div>
            <div class="form-group">
                <label for="new_password">New Password *</label>
                <div class="password-wrapper">
                    <input type="password" id="new_password" name="new_password"
                           placeholder="Enter new password (min. 6 characters)"
                           required minlength="6">
                    <button type="button" class="eye-toggle" onclick="togglePassword('new_password', this)" title="Show/hide password">👁️</button>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password *</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Re-enter new password"
                           required minlength="6">
                    <button type="button" class="eye-toggle" onclick="togglePassword('confirm_password', this)" title="Show/hide password">👁️</button>
                </div>
            </div>

            <div style="display:flex; gap:12px; margin-top:8px;">
                <button type="submit" class="btn btn-primary">🔑 Change Password</button>
                <a href="dashboard.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword(fieldId, btn) {
    var input = document.getElementById(fieldId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
        btn.title = 'Hide password';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
        btn.title = 'Show password';
    }
}

document.querySelector('form').addEventListener('submit', function(e) {
    var p1  = document.getElementById('new_password').value;
    var p2  = document.getElementById('confirm_password');
    var old = p2.parentNode.querySelector('.field-error');
    if (old) old.remove();
    p2.classList.remove('input-error');
    if (p1 !== p2.value) {
        e.preventDefault();
        p2.classList.add('input-error');
        var err = document.createElement('span');
        err.className   = 'field-error';
        err.textContent = 'Passwords do not match.';
        p2.parentNode.appendChild(err);
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
