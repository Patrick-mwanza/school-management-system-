<?php
// admin/classes/add.php — Add a new class
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo    = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $className = trim($_POST['class_name'] ?? '');
    if (empty($className)) $errors[] = 'Class name is required.';

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM classes WHERE class_name = ?");
        $check->execute([$className]);
        if ($check->fetch()) $errors[] = "Class '$className' already exists.";
    }

    if (empty($errors)) {
        $pdo->prepare("INSERT INTO classes (class_name) VALUES (?)")->execute([$className]);
        redirectWithMessage('index.php', "Class '$className' added successfully.", 'success');
    }
}

$pageTitle   = 'Add Class';
$breadcrumbs = [['label' => 'Classes', 'url' => 'index.php'], ['label' => 'Add']];
include __DIR__ . '/../../includes/page_start.php';
?>
<div class="card form-card">
    <div class="card-header">
        <h2>🏛️ Add New Class</h2>
        <a href="index.php" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" id="flashMsg">
                <?php foreach ($errors as $err): ?><div>⚠️ <?php echo e($err); ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="add.php" data-validate>
            <div class="form-group">
                <label for="class_name">Class Name *</label>
                <input type="text" id="class_name" name="class_name"
                       value="<?php echo e($_POST['class_name'] ?? ''); ?>"
                       placeholder="e.g. Form 1A" required>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="submit" class="btn btn-primary">✅ Add Class</button>
                <a href="index.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
