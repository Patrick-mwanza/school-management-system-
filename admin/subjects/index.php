<?php
// admin/subjects/index.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo      = getDB();
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_name")->fetchAll();

$pageTitle   = 'Subjects';
$breadcrumbs = [['label' => 'Subjects']];
include __DIR__ . '/../../includes/page_start.php';
?>

<div class="page-actions">
    <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input type="text" id="tableSearch" placeholder="Search subjects...">
    </div>
    <a href="add.php" class="btn btn-primary">➕ Add Subject</a>
</div>

<div class="card">
    <div class="card-header">
        <h2>All Subjects <span class="badge badge-primary"><?php echo count($subjects); ?></span></h2>
    </div>
    <div class="table-wrapper">
        <table id="dataTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject Code</th>
                    <th>Subject Name</th>
                    <th>Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subjects)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-light);">No subjects yet. <a href="add.php">Add one</a>.</td></tr>
                <?php else: ?>
                    <?php foreach ($subjects as $i => $sub): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><span class="badge badge-primary"><?php echo e($sub['subject_code']); ?></span></td>
                        <td><?php echo e($sub['subject_name']); ?></td>
                        <td><?php echo formatDate($sub['created_at']); ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="edit.php?id=<?php echo $sub['id']; ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                                <a href="delete.php?id=<?php echo $sub['id']; ?>"
                                   class="btn btn-danger btn-sm"
                                   data-confirm="Delete subject '<?php echo e($sub['subject_name']); ?>'?">🗑️ Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr id="noResultsRow" style="display:none;">
                    <td colspan="5" style="text-align:center;padding:30px;color:var(--text-light);">No subjects match your search.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
