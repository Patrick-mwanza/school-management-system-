<?php
// admin/students/index.php — List all students
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo = getDB();

$students = $pdo->query(
    "SELECT s.*, c.class_name
     FROM students s
     LEFT JOIN classes c ON s.class_id = c.id
     ORDER BY s.created_at DESC"
)->fetchAll();

$pageTitle   = 'Students';
$breadcrumbs = [['label' => 'Students']];
include __DIR__ . '/../../includes/page_start.php';
?>

<div class="page-actions">
    <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input type="text" id="tableSearch" placeholder="Search students...">
    </div>
    <a href="add.php" class="btn btn-primary">➕ Add Student</a>
</div>

<div class="card">
    <div class="card-header">
        <h2>All Students <span class="badge badge-primary"><?php echo count($students); ?></span></h2>
    </div>
    <div class="table-wrapper">
        <table id="dataTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Admission No.</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Phone</th>
                    <th>Class</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-light);">No students found. <a href="add.php">Add one now</a>.</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $i => $s): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><span class="badge badge-primary"><?php echo e($s['admission_number']); ?></span></td>
                            <td><?php echo e($s['full_name']); ?></td>
                            <td><?php echo e($s['gender']); ?></td>
                            <td><?php echo formatDate($s['date_of_birth']); ?></td>
                            <td><?php echo e($s['phone_number'] ?: '—'); ?></td>
                            <td><?php echo e($s['class_name'] ?? '—'); ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="edit.php?id=<?php echo $s['id']; ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                                    <a href="delete.php?id=<?php echo $s['id']; ?>"
                                       class="btn btn-danger btn-sm"
                                       data-confirm="Delete student <?php echo e($s['full_name']); ?>? This cannot be undone.">
                                        🗑️ Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr id="noResultsRow" style="display:none;">
                    <td colspan="8" style="text-align:center;padding:30px;color:var(--text-light);">No students match your search.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
