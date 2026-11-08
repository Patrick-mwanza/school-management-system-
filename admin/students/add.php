<?php
// admin/students/add.php — Register a new student
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo     = getDB();
$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName    = trim($_POST['full_name']    ?? '');
    $gender      = $_POST['gender']      ?? '';
    $dob         = $_POST['date_of_birth'] ?? '';
    $phone       = trim($_POST['phone_number'] ?? '');
    $classId     = $_POST['class_id']     ?? '';
    $admNo       = trim($_POST['admission_number'] ?? '');

    // Validation
    if (empty($fullName))  $errors[] = 'Full name is required.';
    if (empty($gender))    $errors[] = 'Gender is required.';
    if (empty($admNo))     $errors[] = 'Admission number is required.';

    // Check duplicate admission number
    if ($admNo) {
        $check = $pdo->prepare("SELECT id FROM students WHERE admission_number = ?");
        $check->execute([$admNo]);
        if ($check->fetch()) $errors[] = "Admission number '$admNo' already exists.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO students (admission_number, full_name, gender, date_of_birth, phone_number, class_id)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $admNo,
            $fullName,
            $gender,
            $dob   ?: null,
            $phone ?: null,
            $classId ?: null,
        ]);
        redirectWithMessage('index.php', "Student '$fullName' registered successfully!", 'success');
    }
}

// Auto-suggest admission number
$suggestedAdm = generateAdmissionNumber($pdo);

$pageTitle   = 'Add Student';
$breadcrumbs = [['label' => 'Students', 'url' => 'index.php'], ['label' => 'Add']];
include __DIR__ . '/../../includes/page_start.php';
?>

<div class="card form-card">
    <div class="card-header">
        <h2>🎓 Register New Student</h2>
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

        <form method="POST" action="add.php" data-validate id="addStudentForm">

            <div class="form-row">
                <div class="form-group">
                    <label for="admission_number">Admission Number *</label>
                    <input type="text" id="admission_number" name="admission_number"
                           value="<?php echo e($_POST['admission_number'] ?? $suggestedAdm); ?>"
                           placeholder="e.g. ADM2025009" required>
                </div>
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name"
                           value="<?php echo e($_POST['full_name'] ?? ''); ?>"
                           placeholder="Enter student full name" required>
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
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth"
                           value="<?php echo e($_POST['date_of_birth'] ?? ''); ?>">
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
                    <label for="class_id">Assign to Class</label>
                    <select id="class_id" name="class_id">
                        <option value="">-- Select Class --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"
                                <?php echo (($_POST['class_id'] ?? '') == $class['id']) ? 'selected' : ''; ?>>
                                <?php echo e($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:flex; gap:12px; margin-top:8px;">
                <button type="submit" class="btn btn-primary">✅ Register Student</button>
                <a href="index.php" class="btn btn-outline">Cancel</a>
            </div>

        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
