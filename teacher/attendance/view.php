<?php
// teacher/attendance/view.php — Teacher views attendance records
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pdo      = getDB();
$classId  = intval($_GET['class_id'] ?? 0);
$fromDate = $_GET['from_date'] ?? date('Y-m-01');
$toDate   = $_GET['to_date']   ?? date('Y-m-d');
$classes  = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();

$records   = [];
$className = '';

if ($classId) {
    $cs = $pdo->prepare("SELECT class_name FROM classes WHERE id = ?");
    $cs->execute([$classId]);
    $className = $cs->fetchColumn();

    $stmt = $pdo->prepare(
        "SELECT a.date, a.status, s.full_name, s.admission_number
         FROM attendance a
         JOIN students s ON a.student_id = s.id
         WHERE a.class_id = ? AND a.date BETWEEN ? AND ?
         ORDER BY a.date DESC, s.full_name"
    );
    $stmt->execute([$classId, $fromDate, $toDate]);
    $records = $stmt->fetchAll();
}

$presentCount = count(array_filter($records, fn($r) => $r['status'] === 'Present'));
$absentCount  = count(array_filter($records, fn($r) => $r['status'] === 'Absent'));

$pageTitle   = 'View Attendance';
$breadcrumbs = [['label' => 'Attendance']];
include __DIR__ . '/../../includes/page_start.php';
?>

<div class="card" style="max-width:640px;margin-bottom:24px;">
    <div class="card-header"><h2>🔍 Filter Attendance</h2></div>
    <div class="card-body">
        <form method="GET" action="view.php" data-validate>
            <div class="form-group">
                <label for="class_id">Class *</label>
                <select id="class_id" name="class_id" required>
                    <option value="">-- Select Class --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $classId == $c['id'] ? 'selected' : ''; ?>>
                            <?php echo e($c['class_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="from_date">From</label>
                    <input type="date" id="from_date" name="from_date" value="<?php echo e($fromDate); ?>">
                </div>
                <div class="form-group">
                    <label for="to_date">To</label>
                    <input type="date" id="to_date" name="to_date" value="<?php echo e($toDate); ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">📊 View</button>
        </form>
    </div>
</div>

<?php if ($classId && !empty($records)): ?>
<div class="stats-grid" style="max-width:400px;margin-bottom:20px;">
    <div class="stat-card"><div class="stat-icon icon-green">✅</div><div class="stat-info"><div class="stat-label">Present</div><div class="stat-value"><?php echo $presentCount; ?></div></div></div>
    <div class="stat-card"><div class="stat-icon icon-red">❌</div><div class="stat-info"><div class="stat-label">Absent</div><div class="stat-value"><?php echo $absentCount; ?></div></div></div>
</div>
<?php endif; ?>

<?php if ($classId): ?>
<div class="card">
    <div class="card-header">
        <h2>Attendance — <?php echo e($className); ?> <span class="badge badge-primary"><?php echo count($records); ?></span></h2>
        <a href="mark.php?class_id=<?php echo $classId; ?>&date=<?php echo date('Y-m-d'); ?>" class="btn btn-primary btn-sm">✅ Mark Today</a>
    </div>
    <div class="table-wrapper">
        <table id="dataTable">
            <thead>
                <tr><th>#</th><th>Date</th><th>Admission No.</th><th>Student Name</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-light);">No records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($records as $i => $r): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo formatDate($r['date']); ?></td>
                        <td><span class="badge badge-primary"><?php echo e($r['admission_number']); ?></span></td>
                        <td><?php echo e($r['full_name']); ?></td>
                        <td>
                            <?php if ($r['status'] === 'Present'): ?>
                                <span class="badge badge-success">✅ Present</span>
                            <?php else: ?>
                                <span class="badge badge-danger">❌ Absent</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
