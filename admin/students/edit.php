<?php
// admin/students/edit.php — Edit student details
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo  = getDB();
$id   = intval($_GET['id'] ?? 0);

// Fetch student
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    redirectWithMessage('index.php', 'Student not found.', 'danger');
}

$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']     ?? '');
    $gender   = $_POST['gender']       ?? '';
    $dob      = $_POST['date_of_birth'] ?? '';
    $phone    = trim($_POST['phone_number']  ?? '');
    $classId  = $_POST['class_id']     ?? '';
    $admNo    = trim($_POST['admission_number'] ?? '');

    if (empty($fullName)) $errors[] = 'Full name is required.';
    if (empty($gender))   $errors[] = 'Gender is required.';
    if (empty($admNo))    $errors[] = 'Admission number is required.';

    // Check duplicate (excluding current)
    if ($admNo) {
        $check = $pdo->prepare("SELECT id FROM students WHERE admission_number = ? AND id != ?");
        $check->execute([$admNo, $id]);
        if ($check->fetch()) $errors[] = "Admission number '$admNo' is taken by another student.";
    }

    if (empty($errors)) {
        $upd = $pdo->prepare(
            "UPDATE students SET admission_number=?, full_name=?, gender=?, date_of_birth=?, phone_number=?, class_id=?
             WHERE id=?"
        );
        $upd->execute([
            $admNo, $fullName, $gender,
            $dob   ?: null,
            $phone ?: null,
            $classId ?: null,
            $id
        ]);
        redirectWithMessage('index.php', "Student '$fullName' updated successfully.", 'success');
    }

    // Re-populate with POST data on error
    $student = array_merge($student, $_POST);
}

$pageTitle   = 'Edit Student';
$breadcrumbs = [['label' => 'Students', 'url' => 'index.php'], ['label' => 'Edit']];
include __DIR__ . '/../../includes/page_start.php';
?>

<div class="card form-card">
    <div class="card-header">
        <h2>✏️ Edit Student</h2>
        <a href="index.php" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="card-body">

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" id="flashMsg">
                <?php foreach ($errors as $err): ?>
                    <div>⚠️ <?php echo e($err); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit.php?id=<?php echo $id; ?>" data-validate>

            <div class="form-row">
                <div class="form-group">
                    <label for="admission_number">Admission Number *</label>
                    <input type="text" id="admission_number" name="admission_number"
                           value="<?php echo e($student['admission_number']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name"
                           value="<?php echo e($student['full_name']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select id="gender" name="gender" required>
                        <option value="">-- Select --</option>
                        <option value="Male"   <?php echo $student['gender'] === 'Male'   ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $student['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth"
                           value="<?php echo e($student['date_of_birth']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number"
                           value="<?php echo e($student['phone_number']); ?>">
                </div>
                <div class="form-group">
                    <label for="class_id">Class</label>
                    <select id="class_id" name="class_id">
                        <option value="">-- No Class --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"
                                <?php echo ($student['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                                <?php echo e($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:flex; gap:12px; margin-top:8px;">
                <button type="submit" class="btn btn-warning">💾 Save Changes</button>
                <a href="index.php" class="btn btn-outline">Cancel</a>
            </div>

        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
