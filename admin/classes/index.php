<?php
// admin/classes/index.php — List all classes
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo = getDB();
$classes = $pdo->query(
    "SELECT c.*, COUNT(s.id) AS student_count
     FROM classes c
     LEFT JOIN students s ON s.class_id = c.id
     GROUP BY c.id
     ORDER BY c.class_name"
)->fetchAll();

$pageTitle   = 'Classes';
$breadcrumbs = [['label' => 'Classes']];
include __DIR__ . '/../../includes/page_start.php';
?>

<div class="page-actions">
    <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input type="text" id="tableSearch" placeholder="Search classes...">
    </div>
    <a href="add.php" class="btn btn-primary">➕ Add Class</a>
</div>

<div class="card">
    <div class="card-header">
        <h2>All Classes <span class="badge badge-primary"><?php echo count($classes); ?></span></h2>
    </div>
    <div class="table-wrapper">
        <table id="dataTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Class Name</th>
                    <th>No. of Students</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($classes)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-light);">No classes yet. <a href="add.php">Add one</a>.</td></tr>
                <?php else: ?>
                    <?php foreach ($classes as $i => $c): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><strong><?php echo e($c['class_name']); ?></strong></td>
                        <td><span class="badge badge-info"><?php echo $c['student_count']; ?> students</span></td>
                        <td><?php echo formatDate($c['created_at']); ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="edit.php?id=<?php echo $c['id']; ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                                <a href="delete.php?id=<?php echo $c['id']; ?>"
                                   class="btn btn-danger btn-sm"
                                   data-confirm="Delete class '<?php echo e($c['class_name']); ?>'?">🗑️ Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr id="noResultsRow" style="display:none;">
                    <td colspan="5" style="text-align:center;padding:30px;color:var(--text-light);">No classes match your search.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
