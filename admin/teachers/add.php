<?php
// admin/teachers/add.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo      = getDB();
$subjects = $pdo->query("SELECT id, subject_name FROM subjects ORDER BY subject_name")->fetchAll();
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staffNo  = strtoupper(trim($_POST['staff_number'] ?? ''));
    $fullName = trim($_POST['full_name']    ?? '');
    $gender   = $_POST['gender']      ?? '';
    $phone    = trim($_POST['phone_number'] ?? '');
    $email    = trim($_POST['email']        ?? '');
    $subjId   = $_POST['subject_id']   ?? '';

    if (empty($staffNo))  $errors[] = 'Staff number is required.';
    if (empty($fullName)) $errors[] = 'Full name is required.';
    if (empty($gender))   $errors[] = 'Gender is required.';

    if (empty($errors) && $staffNo) {
        $check = $pdo->prepare("SELECT id FROM teachers WHERE staff_number = ?");
        $check->execute([$staffNo]);
        if ($check->fetch()) $errors[] = "Staff number '$staffNo' already exists.";
    }

    if (empty($errors)) {
        // Insert teacher
        $stmt = $pdo->prepare(
            "INSERT INTO teachers (staff_number, full_name, gender, phone_number, email, subject_id)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$staffNo, $fullName, $gender, $phone ?: null, $email ?: null, $subjId ?: null]);
        $teacherId = $pdo->lastInsertId();

        // Auto-create login account
        // username: firstname.lastname (lowercase, dots)
        $nameParts = explode(' ', strtolower($fullName));
        $baseUsername = implode('.', array_filter($nameParts));
        $username = $baseUsername;
        $suffix   = 1;
        // Ensure username is unique
        while (true) {
            $uc = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $uc->execute([$username]);
            if (!$uc->fetch()) break;
            $username = $baseUsername . $suffix;
            $suffix++;
        }

        $hashedPass = password_hash('teacher123', PASSWORD_DEFAULT);
        $userStmt   = $pdo->prepare(
            "INSERT INTO users (username, password, role, teacher_id) VALUES (?, ?, 'teacher', ?)"
        );
        $userStmt->execute([$username, $hashedPass, $teacherId]);

        redirectWithMessage(
            'index.php',
            "Teacher '$fullName' added. Login: username='$username', password='teacher123'.",
            'success'
        );
    }
}

$suggestedStaff = generateStaffNumber($pdo);

$pageTitle   = 'Add Teacher';
$breadcrumbs = [['label' => 'Teachers', 'url' => 'index.php'], ['label' => 'Add']];
include __DIR__ . '/../../includes/page_start.php';
?>

<div class="card form-card">
    <div class="card-header">
        <h2>👩‍🏫 Register New Teacher</h2>
        <a href="index.php" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="card-body">

        <div class="alert alert-info" style="margin-bottom:20px;">
            ℹ️ A login account will be created automatically. Default password: <strong>teacher123</strong>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" id="flashMsg">
                <?php foreach ($errors as $err): ?><div>⚠️ <?php echo e($err); ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="add.php" data-validate>
            <div class="form-row">
                <div class="form-group">
                    <label for="staff_number">Staff Number *</label>
                    <input type="text" id="staff_number" name="staff_number"
                           value="<?php echo e($_POST['staff_number'] ?? $suggestedStaff); ?>"
                           placeholder="e.g. TCH002" required style="text-transform:uppercase;">
                </div>
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name"
                           value="<?php echo e($_POST['full_name'] ?? ''); ?>"
                           placeholder="Enter teacher full name" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select id="gender" name="gender" required>
                        <option value="">-- Select Gender --</option>
                        <option value="Male"   <?php echo (($_POST['gender'] ?? '') === 'Male')   ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (($_POST['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subject_id">Subject Taught</label>
                    <select id="subject_id" name="subject_id">
                        <option value="">-- Select Subject --</option>
                        <?php foreach ($subjects as $sub): ?>
                            <option value="<?php echo $sub['id']; ?>"
                                <?php echo (($_POST['subject_id'] ?? '') == $sub['id']) ? 'selected' : ''; ?>>
                                <?php echo e($sub['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number"
                           value="<?php echo e($_POST['phone_number'] ?? ''); ?>"
                           placeholder="e.g. 0712345678">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo e($_POST['email'] ?? ''); ?>"
                           placeholder="teacher@school.ac.ke">
                </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="submit" class="btn btn-primary">✅ Register Teacher</button>
                <a href="index.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
