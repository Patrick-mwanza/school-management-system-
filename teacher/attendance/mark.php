<?php
// teacher/attendance/mark.php — Teacher marks attendance
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pdo     = getDB();
$classId = intval($_GET['class_id'] ?? $_POST['class_id'] ?? 0);
$date    = $_GET['date'] ?? $_POST['date'] ?? date('Y-m-d');
$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();

// If class selected, show mark form
$class    = null;
$students = [];
$existing = [];

if ($classId) {
    $cs = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
    $cs->execute([$classId]);
    $class = $cs->fetch();

    if ($class) {
        $ss = $pdo->prepare("SELECT id, admission_number, full_name, gender FROM students WHERE class_id = ? ORDER BY full_name");
        $ss->execute([$classId]);
        $students = $ss->fetchAll();

        $exStmt = $pdo->prepare("SELECT student_id, status FROM attendance WHERE class_id = ? AND date = ?");
        $exStmt->execute([$classId, $date]);
        foreach ($exStmt->fetchAll() as $row) {
            $existing[$row['student_id']] = $row['status'];
        }
    }
}

// Save attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $classId && !empty($students)) {
    $attendance = $_POST['attendance'] ?? [];
    foreach ($students as $student) {
        $status = ($attendance[$student['id']] ?? 'Absent') === 'Present' ? 'Present' : 'Absent';
        $ups = $pdo->prepare(
            "INSERT INTO attendance (student_id, class_id, date, status, marked_by)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by)"
        );
        $ups->execute([$student['id'], $classId, $date, $status, $_SESSION['user_id']]);
    }
    redirectWithMessage('mark.php', "Attendance saved for {$class['class_name']} on " . formatDate($date) . ".", 'success');
}

$pageTitle   = 'Mark Attendance';
$breadcrumbs = [['label' => 'Attendance']];
include __DIR__ . '/../../includes/page_start.php';
?>

<!-- Class/Date Selector -->
<div class="card" style="max-width:500px;margin-bottom:20px;">
    <div class="card-header"><h2>✅ Select Class</h2></div>
    <div class="card-body">
        <form method="GET" action="mark.php" data-validate>
            <div class="form-row">
                <div class="form-group">
                    <label for="class_id">Class *</label>
                    <select id="class_id" name="class_id" required>
                        <option value="">-- Choose Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $classId == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo e($c['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="att_date">Date *</label>
                    <input type="date" id="att_date" name="date" value="<?php echo e($date); ?>" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">📋 Load Students</button>
        </form>
    </div>
</div>

<!-- Attendance Sheet -->
<?php if ($class && !empty($students)): ?>
<div class="card" style="max-width:700px;">
    <div class="card-header">
        <div>
            <h2>✅ <?php echo e($class['class_name']); ?></h2>
            <p style="font-size:0.85rem;color:var(--text-mid);margin-top:4px;">
                📅 <?php echo formatDate($date); ?> &nbsp;|&nbsp; 👨‍🎓 <?php echo count($students); ?> students
            </p>
        </div>
    </div>
    <div class="card-body">
        <div style="display:flex;gap:10px;margin-bottom:20px;">
            <button type="button" class="btn btn-success btn-sm" id="markAllPresent">✅ Mark All Present</button>
            <button type="button" class="btn btn-danger btn-sm" id="markAllAbsent">❌ Mark All Absent</button>
        </div>
        <form method="POST" action="mark.php">
            <input type="hidden" name="class_id" value="<?php echo $classId; ?>">
            <input type="hidden" name="date"     value="<?php echo e($date); ?>">
            <?php foreach ($students as $student):
                $currentStatus = $existing[$student['id']] ?? 'Present';
            ?>
            <div class="attendance-grid">
                <div>
                    <div class="att-student-name"><?php echo e($student['full_name']); ?></div>
                    <div class="att-student-num"><?php echo e($student['admission_number']); ?> &bull; <?php echo e($student['gender']); ?></div>
                </div>
                <input type="hidden" name="attendance[<?php echo $student['id']; ?>]"
                       id="att_<?php echo $student['id']; ?>" value="<?php echo $currentStatus; ?>">
                <div class="att-toggle">
                    <button type="button" class="att-btn att-present <?php echo $currentStatus === 'Present' ? 'present-active' : ''; ?>"
                            data-student="<?php echo $student['id']; ?>">✅ Present</button>
                    <button type="button" class="att-btn att-absent <?php echo $currentStatus === 'Absent' ? 'absent-active' : ''; ?>"
                            data-student="<?php echo $student['id']; ?>">❌ Absent</button>
                </div>
            </div>
            <?php endforeach; ?>
            <div style="margin-top:24px;padding-top:16px;border-top:1px solid var(--border);display:flex;gap:12px;">
                <button type="submit" class="btn btn-primary">💾 Save Attendance</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.att-toggle').forEach(function(group) {
    var presentBtn  = group.querySelector('.att-present');
    var absentBtn   = group.querySelector('.att-absent');
    var studentId   = presentBtn ? presentBtn.dataset.student : null;
    var hiddenInput = studentId ? document.getElementById('att_' + studentId) : null;
    if (presentBtn) {
        presentBtn.addEventListener('click', function() {
            presentBtn.classList.add('present-active');
            absentBtn.classList.remove('absent-active');
            if (hiddenInput) hiddenInput.value = 'Present';
        });
    }
    if (absentBtn) {
        absentBtn.addEventListener('click', function() {
            absentBtn.classList.add('absent-active');
            presentBtn.classList.remove('present-active');
            if (hiddenInput) hiddenInput.value = 'Absent';
        });
    }
});
document.getElementById('markAllPresent').addEventListener('click', function() {
    document.querySelectorAll('.att-present').forEach(function(b) { b.click(); });
});
document.getElementById('markAllAbsent').addEventListener('click', function() {
    document.querySelectorAll('.att-absent').forEach(function(b) { b.click(); });
});
</script>

<?php elseif ($classId && empty($students)): ?>
<div class="card" style="max-width:500px;">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-icon">👥</div>
            <p>No students assigned to this class yet.</p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
