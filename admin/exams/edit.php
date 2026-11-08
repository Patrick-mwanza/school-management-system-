<?php
// admin/exams/edit.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo = getDB();
$id  = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM examinations WHERE id = ?");
$stmt->execute([$id]);
$exam = $stmt->fetch();
if (!$exam) redirectWithMessage('index.php', 'Exam not found.', 'danger');

$classes  = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();
$subjects = $pdo->query("SELECT id, subject_name FROM subjects ORDER BY subject_name")->fetchAll();
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $examName  = trim($_POST['exam_name']  ?? '');
    $classId   = intval($_POST['class_id']   ?? 0);
    $subjectId = intval($_POST['subject_id'] ?? 0);
    $examDate  = $_POST['exam_date']  ?? '';
    $maxMarks  = intval($_POST['max_marks']  ?? 100);

    if (empty($examName)) $errors[] = 'Exam name is required.';
    if (!$classId)        $errors[] = 'Class is required.';
    if (!$subjectId)      $errors[] = 'Subject is required.';
    if (empty($examDate)) $errors[] = 'Exam date is required.';

    if (empty($errors)) {
        $pdo->prepare(
            "UPDATE examinations SET exam_name=?, class_id=?, subject_id=?, exam_date=?, max_marks=? WHERE id=?"
        )->execute([$examName, $classId, $subjectId, $examDate, $maxMarks, $id]);
        redirectWithMessage('index.php', "Examination '$examName' updated.", 'success');
    }
    $exam = array_merge($exam, $_POST);
}

$pageTitle   = 'Edit Examination';
$breadcrumbs = [['label' => 'Examinations', 'url' => 'index.php'], ['label' => 'Edit']];
include __DIR__ . '/../../includes/page_start.php';
?>

<div class="card form-card">
    <div class="card-header">
        <h2>✏️ Edit Examination</h2>
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
                <label for="exam_name">Examination Name *</label>
                <input type="text" id="exam_name" name="exam_name" value="<?php echo e($exam['exam_name']); ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="class_id">Class *</label>
                    <select id="class_id" name="class_id" required>
                        <option value="">-- Select Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($exam['class_id'] == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo e($c['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subject_id">Subject *</label>
                    <select id="subject_id" name="subject_id" required>
                        <option value="">-- Select Subject --</option>
                        <?php foreach ($subjects as $sub): ?>
                            <option value="<?php echo $sub['id']; ?>" <?php echo ($exam['subject_id'] == $sub['id']) ? 'selected' : ''; ?>>
                                <?php echo e($sub['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="exam_date">Exam Date *</label>
                    <input type="date" id="exam_date" name="exam_date" value="<?php echo e($exam['exam_date']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="max_marks">Maximum Marks</label>
                    <input type="number" id="max_marks" name="max_marks" min="1" value="<?php echo e($exam['max_marks']); ?>">
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
