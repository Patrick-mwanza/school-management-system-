<?php
// admin/teachers/change_password.php — Admin resets a teacher's password
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo = getDB();
$id  = intval($_GET['id'] ?? 0);

// Get teacher + their user account
$stmt = $pdo->prepare(
    "SELECT t.*, u.id as user_id, u.username
     FROM teachers t
     LEFT JOIN users u ON u.teacher_id = t.id
     WHERE t.id = ?"
);
$stmt->execute([$id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    redirectWithMessage('index.php', 'Teacher not found.', 'danger');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword  = $_POST['new_password']     ?? '';
    $confirmPass  = $_POST['confirm_password'] ?? '';

    if (strlen($newPassword) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    if ($newPassword !== $confirmPass) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        if ($teacher['user_id']) {
            // Update existing user account
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
                ->execute([$hashed, $teacher['user_id']]);
        } else {
            // Create user account if it doesn't exist yet
            $nameParts    = explode(' ', strtolower($teacher['full_name']));
            $baseUsername = implode('.', array_filter($nameParts));
            $username     = $baseUsername;
            $suffix       = 1;
            while (true) {
                $uc = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $uc->execute([$username]);
                if (!$uc->fetch()) break;
                $username = $baseUsername . $suffix++;
            }
            $pdo->prepare(
                "INSERT INTO users (username, password, role, teacher_id) VALUES (?, ?, 'teacher', ?)"
            )->execute([$username, $hashed, $id]);
        }

        redirectWithMessage(
            'index.php',
            "Password for {$teacher['full_name']} has been updated successfully.",
            'success'
        );
    }
}

$pageTitle   = 'Change Teacher Password';
$breadcrumbs = [['label' => 'Teachers', 'url' => 'index.php'], ['label' => 'Change Password']];
include __DIR__ . '/../../includes/page_start.php';
?>

<div class="card form-card">
    <div class="card-header">
        <h2>🔑 Reset Password — <?php echo e($teacher['full_name']); ?></h2>
        <a href="index.php" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="card-body">

        <!-- Teacher info -->
        <div class="alert alert-info" style="margin-bottom:24px;">
            <div>👩‍🏫 <strong><?php echo e($teacher['full_name']); ?></strong> &nbsp;|&nbsp;
            Staff No: <strong><?php echo e($teacher['staff_number']); ?></strong></div>
            <?php if ($teacher['username']): ?>
                <div style="margin-top:4px;">Login username: <strong><?php echo e($teacher['username']); ?></strong></div>
            <?php endif; ?>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" id="flashMsg">
                <?php foreach ($errors as $err): ?><div>⚠️ <?php echo e($err); ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="change_password.php?id=<?php echo $id; ?>" data-validate>
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
                <label for="confirm_password">Confirm Password *</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Re-enter new password"
                           required minlength="6">
                    <button type="button" class="eye-toggle" onclick="togglePassword('confirm_password', this)" title="Show/hide password">👁️</button>
                </div>
            </div>

            <div style="display:flex; gap:12px; margin-top:8px;">
                <button type="submit" class="btn btn-primary">🔑 Update Password</button>
                <a href="index.php" class="btn btn-outline">Cancel</a>
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

// Client-side password match check
document.querySelector('form').addEventListener('submit', function(e) {
    var p1 = document.getElementById('new_password').value;
    var p2 = document.getElementById('confirm_password').value;
    var c  = document.getElementById('confirm_password');
    // remove old error
    var old = c.parentNode.querySelector('.field-error');
    if (old) old.remove();
    c.classList.remove('input-error');
    if (p1 !== p2) {
        e.preventDefault();
        c.classList.add('input-error');
        var err = document.createElement('span');
        err.className   = 'field-error';
        err.textContent = 'Passwords do not match.';
        c.parentNode.appendChild(err);
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
