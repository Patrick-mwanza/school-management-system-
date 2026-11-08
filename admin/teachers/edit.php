<?php
// admin/teachers/edit.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo  = getDB();
$id   = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->execute([$id]);
$teacher = $stmt->fetch();
if (!$teacher) redirectWithMessage('index.php', 'Teacher not found.', 'danger');

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
        $check = $pdo->prepare("SELECT id FROM teachers WHERE staff_number = ? AND id != ?");
        $check->execute([$staffNo, $id]);
        if ($check->fetch()) $errors[] = "Staff number '$staffNo' is already taken.";
    }

    if (empty($errors)) {
        $pdo->prepare(
            "UPDATE teachers SET staff_number=?, full_name=?, gender=?, phone_number=?, email=?, subject_id=? WHERE id=?"
        )->execute([$staffNo, $fullName, $gender, $phone ?: null, $email ?: null, $subjId ?: null, $id]);
        redirectWithMessage('index.php', "Teacher '$fullName' updated successfully.", 'success');
    }
    $teacher = array_merge($teacher, $_POST);
}

$pageTitle   = 'Edit Teacher';
$breadcrumbs = [['label' => 'Teachers', 'url' => 'index.php'], ['label' => 'Edit']];
include __DIR__ . '/../../includes/page_start.php';
?>

<div class="card form-card">
    <div class="card-header">
        <h2>✏️ Edit Teacher</h2>
        <a href="index.php" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" id="flashMsg">
                <?php foreach ($errors as $err): ?><div>⚠️ <?php echo e($err); ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="edit.php?id=<?php echo $id; ?>" data-validate>
            <div class="form-row">
                <div class="form-group">
                    <label for="staff_number">Staff Number *</label>
                    <input type="text" id="staff_number" name="staff_number"
                           value="<?php echo e($teacher['staff_number']); ?>"
                           style="text-transform:uppercase;" required>
                </div>
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name"
                           value="<?php echo e($teacher['full_name']); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select id="gender" name="gender" required>
                        <option value="">-- Select --</option>
                        <option value="Male"   <?php echo $teacher['gender'] === 'Male'   ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $teacher['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subject_id">Subject Taught</label>
                    <select id="subject_id" name="subject_id">
                        <option value="">-- None --</option>
                        <?php foreach ($subjects as $sub): ?>
                            <option value="<?php echo $sub['id']; ?>"
                                <?php echo ($teacher['subject_id'] == $sub['id']) ? 'selected' : ''; ?>>
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
                           value="<?php echo e($teacher['phone_number']); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo e($teacher['email']); ?>">
                </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="submit" class="btn btn-warning">💾 Save Changes</button>
                <a href="index.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
