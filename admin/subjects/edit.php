<?php
// admin/subjects/edit.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo = getDB();
$id  = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
$stmt->execute([$id]);
$subject = $stmt->fetch();
if (!$subject) redirectWithMessage('index.php', 'Subject not found.', 'danger');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['subject_name'] ?? '');
    $code = strtoupper(trim($_POST['subject_code'] ?? ''));

    if (empty($name)) $errors[] = 'Subject name is required.';
    if (empty($code)) $errors[] = 'Subject code is required.';

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM subjects WHERE subject_code = ? AND id != ?");
        $check->execute([$code, $id]);
        if ($check->fetch()) $errors[] = "Subject code '$code' is already used.";
    }

    if (empty($errors)) {
        $pdo->prepare("UPDATE subjects SET subject_name=?, subject_code=? WHERE id=?")->execute([$name, $code, $id]);
        redirectWithMessage('index.php', "Subject updated to '$name' successfully.", 'success');
    }
    $subject['subject_name'] = $name;
    $subject['subject_code'] = $code;
}

$pageTitle   = 'Edit Subject';
$breadcrumbs = [['label' => 'Subjects', 'url' => 'index.php'], ['label' => 'Edit']];
include __DIR__ . '/../../includes/page_start.php';
?>
<div class="card form-card">
    <div class="card-header">
        <h2>✏️ Edit Subject</h2>
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
                    <label for="subject_name">Subject Name *</label>
                    <input type="text" id="subject_name" name="subject_name"
                           value="<?php echo e($subject['subject_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="subject_code">Subject Code *</label>
                    <input type="text" id="subject_code" name="subject_code"
                           value="<?php echo e($subject['subject_code']); ?>"
                           style="text-transform:uppercase;" required>
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
