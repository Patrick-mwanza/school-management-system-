<?php
// admin/reports/attendance.php — Attendance summary report
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pdo      = getDB();
$classId  = intval($_GET['class_id'] ?? 0);
$fromDate = $_GET['from_date'] ?? date('Y-m-01');
$toDate   = $_GET['to_date']   ?? date('Y-m-d');
$classes  = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();

$summaries = [];
$className = '';

if ($classId) {
    $cs = $pdo->prepare("SELECT class_name FROM classes WHERE id = ?");
    $cs->execute([$classId]);
    $className = $cs->fetchColumn();

    // Per-student attendance summary
    $stmt = $pdo->prepare(
        "SELECT s.full_name, s.admission_number,
                SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS present_days,
                SUM(CASE WHEN a.status = 'Absent'  THEN 1 ELSE 0 END) AS absent_days,
                COUNT(a.id) AS total_days
         FROM students s
         LEFT JOIN attendance a ON a.student_id = s.id
              AND a.class_id = ?
              AND a.date BETWEEN ? AND ?
         WHERE s.class_id = ?
         GROUP BY s.id, s.full_name, s.admission_number
         ORDER BY s.full_name"
    );
    $stmt->execute([$classId, $fromDate, $toDate, $classId]);
    $summaries = $stmt->fetchAll();
}

$pageTitle   = 'Attendance Report';
$breadcrumbs = [['label' => 'Reports'], ['label' => 'Attendance']];
include __DIR__ . '/../../includes/page_start.php';
?>

<!-- Filters -->
<div class="card" style="max-width:640px;margin-bottom:20px;">
    <div class="card-header"><h2>🔍 Filter Report</h2></div>
    <div class="card-body">
        <form method="GET" action="attendance.php">
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
                    <label for="from_date">From Date</label>
                    <input type="date" id="from_date" name="from_date" value="<?php echo e($fromDate); ?>">
                </div>
                <div class="form-group">
                    <label for="to_date">To Date</label>
                    <input type="date" id="to_date" name="to_date" value="<?php echo e($toDate); ?>">
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary">📊 Generate</button>
                <?php if (!empty($summaries)): ?>
                    <button onclick="window.print()" type="button" class="btn btn-outline no-print">🖨️ Print</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Report -->
<?php if ($classId): ?>
<div class="card">
    <div class="card-header">
        <h2>
            Attendance Summary — <?php echo e($className); ?>
            <?php if (!empty($summaries)): ?>
                <span class="badge badge-primary"><?php echo count($summaries); ?> students</span>
            <?php endif; ?>
        </h2>
        <p style="font-size:0.8rem;color:var(--text-mid);margin-top:4px;">
            Period: <?php echo formatDate($fromDate); ?> → <?php echo formatDate($toDate); ?>
        </p>
    </div>
    <div class="table-wrapper">
        <table id="dataTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Admission No.</th>
                    <th>Student Name</th>
                    <th>Present Days</th>
                    <th>Absent Days</th>
                    <th>Total Days</th>
                    <th>Attendance %</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($summaries)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-light);">No attendance records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($summaries as $i => $row):
                        $pct    = $row['total_days'] > 0 ? round(($row['present_days'] / $row['total_days']) * 100, 1) : 0;
                        $remark = $pct >= 75 ? 'Good' : ($pct >= 50 ? 'Fair' : 'Poor');
                        $remarkClass = $pct >= 75 ? 'badge-success' : ($pct >= 50 ? 'badge-warning' : 'badge-danger');
                    ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><span class="badge badge-primary"><?php echo e($row['admission_number']); ?></span></td>
                        <td><?php echo e($row['full_name']); ?></td>
                        <td><span class="badge badge-success"><?php echo $row['present_days']; ?></span></td>
                        <td><span class="badge badge-danger"><?php echo $row['absent_days']; ?></span></td>
                        <td><?php echo $row['total_days']; ?></td>
                        <td><strong><?php echo $pct; ?>%</strong></td>
                        <td><span class="badge <?php echo $remarkClass; ?>"><?php echo $remark; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
