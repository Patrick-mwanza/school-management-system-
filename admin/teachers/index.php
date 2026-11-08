<?php
// admin/teachers/index.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo = getDB();
$teachers = $pdo->query(
    "SELECT t.*, s.subject_name
     FROM teachers t
     LEFT JOIN subjects s ON t.subject_id = s.id
     ORDER BY t.full_name"
)->fetchAll();

$pageTitle   = 'Teachers';
$breadcrumbs = [['label' => 'Teachers']];
include __DIR__ . '/../../includes/page_start.php';
?>

<div class="page-actions">
    <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input type="text" id="tableSearch" placeholder="Search teachers...">
    </div>
    <a href="add.php" class="btn btn-primary">➕ Add Teacher</a>
</div>

<div class="card">
    <div class="card-header">
        <h2>All Teachers <span class="badge badge-primary"><?php echo count($teachers); ?></span></h2>
    </div>
    <div class="table-wrapper">
        <table id="dataTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Staff No.</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($teachers)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-light);">No teachers found. <a href="add.php">Add one</a>.</td></tr>
                <?php else: ?>
                    <?php foreach ($teachers as $i => $t): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><span class="badge badge-info"><?php echo e($t['staff_number']); ?></span></td>
                        <td><?php echo e($t['full_name']); ?></td>
                        <td><?php echo e($t['gender']); ?></td>
                        <td><?php echo e($t['phone_number'] ?: '—'); ?></td>
                        <td><?php echo e($t['email'] ?: '—'); ?></td>
                        <td><?php echo e($t['subject_name'] ?? '—'); ?></td>
                        <td>
                            <div class="table-actions">
                                    <a href="edit.php?id=<?php echo $t['id']; ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                                    <a href="change_password.php?id=<?php echo $t['id']; ?>" class="btn btn-primary btn-sm">🔑 Password</a>
                                    <a href="delete.php?id=<?php echo $t['id']; ?>"
                                       class="btn btn-danger btn-sm"
                                       data-confirm="Delete teacher '<?php echo e($t['full_name']); ?>'?">🗑️ Delete</a>
                                </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr id="noResultsRow" style="display:none;">
                    <td colspan="8" style="text-align:center;padding:30px;color:var(--text-light);">No teachers match your search.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
