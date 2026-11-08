<?php
// admin/classes/edit.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo = getDB();
$id  = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->execute([$id]);
$class = $stmt->fetch();
if (!$class) redirectWithMessage('index.php', 'Class not found.', 'danger');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $className = trim($_POST['class_name'] ?? '');
    if (empty($className)) $errors[] = 'Class name is required.';

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM classes WHERE class_name = ? AND id != ?");
        $check->execute([$className, $id]);
        if ($check->fetch()) $errors[] = "Class name '$className' already exists.";
    }

    if (empty($errors)) {
        $pdo->prepare("UPDATE classes SET class_name = ? WHERE id = ?")->execute([$className, $id]);
        redirectWithMessage('index.php', "Class updated to '$className' successfully.", 'success');
    }
    $class['class_name'] = $className;
}

$pageTitle   = 'Edit Class';
$breadcrumbs = [['label' => 'Classes', 'url' => 'index.php'], ['label' => 'Edit']];
include __DIR__ . '/../../includes/page_start.php';
?>
<div class="card form-card">
    <div class="card-header">
        <h2>✏️ Edit Class</h2>
        <a href="index.php" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" id="flashMsg">
                <?php foreach ($errors as $err): ?><div>⚠️ <?php echo e($err); ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="edit.php?id=<?php echo $id; ?>" data-validate>
            <div class="form-group">
                <label for="class_name">Class Name *</label>
                <input type="text" id="class_name" name="class_name"
                       value="<?php echo e($class['class_name']); ?>"
                       placeholder="e.g. Form 2A" required>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="submit" class="btn btn-warning">💾 Save Changes</button>
                <a href="index.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
