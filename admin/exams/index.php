<?php
// admin/exams/index.php — List all examinations
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pdo  = getDB();
$exams = $pdo->query(
    "SELECT e.*, c.class_name, s.subject_name
     FROM examinations e
     LEFT JOIN classes c ON e.class_id = c.id
     LEFT JOIN subjects s ON e.subject_id = s.id
     ORDER BY e.exam_date DESC"
)->fetchAll();

$pageTitle   = 'Examinations';
$breadcrumbs = [['label' => 'Examinations']];
include __DIR__ . '/../../includes/page_start.php';
?>

<div class="page-actions">
    <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input type="text" id="tableSearch" placeholder="Search exams...">
    </div>
    <?php if (isAdmin()): ?>
        <a href="add.php" class="btn btn-primary">➕ Add Examination</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h2>All Examinations <span class="badge badge-primary"><?php echo count($exams); ?></span></h2>
    </div>
    <div class="table-wrapper">
        <table id="dataTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Exam Name</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Max Marks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($exams)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-light);">No exams yet. <?php if(isAdmin()): ?><a href="add.php">Add one</a>.<?php endif; ?></td></tr>
                <?php else: ?>
                    <?php foreach ($exams as $i => $exam): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><strong><?php echo e($exam['exam_name']); ?></strong></td>
                        <td><?php echo e($exam['class_name']); ?></td>
                        <td><span class="badge badge-info"><?php echo e($exam['subject_name']); ?></span></td>
                        <td><?php echo formatDate($exam['exam_date']); ?></td>
                        <td><?php echo e($exam['max_marks']); ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="marks.php?exam_id=<?php echo $exam['id']; ?>" class="btn btn-primary btn-sm">📝 Marks</a>
                                <a href="results.php?exam_id=<?php echo $exam['id']; ?>" class="btn btn-success btn-sm">🏆 Results</a>
                                <?php if (isAdmin()): ?>
                                    <a href="edit.php?id=<?php echo $exam['id']; ?>" class="btn btn-warning btn-sm">✏️</a>
                                    <a href="delete.php?id=<?php echo $exam['id']; ?>"
                                       class="btn btn-danger btn-sm"
                                       data-confirm="Delete exam '<?php echo e($exam['exam_name']); ?>'?">🗑️</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr id="noResultsRow" style="display:none;">
                    <td colspan="7" style="text-align:center;padding:30px;color:var(--text-light);">No exams match your search.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
