<?php
// admin/attendance/mark.php — Mark attendance for a class on a date
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pdo     = getDB();
$classId = intval($_GET['class_id'] ?? $_POST['class_id'] ?? 0);
$date    = $_GET['date'] ?? $_POST['date'] ?? date('Y-m-d');

// Validate class
$classStmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
$classStmt->execute([$classId]);
$class = $classStmt->fetch();
if (!$class) {
    redirectWithMessage('index.php', 'Invalid class selected.', 'danger');
}

// Fetch students in this class
$students = $pdo->prepare(
    "SELECT id, admission_number, full_name, gender FROM students WHERE class_id = ? ORDER BY full_name"
);
$students->execute([$classId]);
$students = $students->fetchAll();

// Fetch existing attendance for this class+date
$existing = [];
if (!empty($students)) {
    $existStmt = $pdo->prepare("SELECT student_id, status FROM attendance WHERE class_id = ? AND date = ?");
    $existStmt->execute([$classId, $date]);
    foreach ($existStmt->fetchAll() as $row) {
        $existing[$row['student_id']] = $row['status'];
    }
}

// Handle POST — save attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendance = $_POST['attendance'] ?? [];

    foreach ($students as $student) {
        $status = ($attendance[$student['id']] ?? 'Absent') === 'Present' ? 'Present' : 'Absent';

        // UPSERT: insert or update
        $ups = $pdo->prepare(
            "INSERT INTO attendance (student_id, class_id, date, status, marked_by)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by)"
        );
        $ups->execute([$student['id'], $classId, $date, $status, $_SESSION['user_id']]);
    }

    redirectWithMessage('index.php', "Attendance saved for {$class['class_name']} on " . formatDate($date) . ".", 'success');
}

$pageTitle   = 'Mark Attendance';
$breadcrumbs = [['label' => 'Attendance', 'url' => 'index.php'], ['label' => $class['class_name']]];
include __DIR__ . '/../../includes/page_start.php';
?>

<div class="card" style="max-width:700px;">
    <div class="card-header">
        <div>
            <h2>✅ Attendance — <?php echo e($class['class_name']); ?></h2>
            <p style="font-size:0.85rem;color:var(--text-mid);margin-top:4px;">
                📅 Date: <strong><?php echo formatDate($date); ?></strong> &nbsp;|&nbsp;
                👨‍🎓 Students: <strong><?php echo count($students); ?></strong>
            </p>
        </div>
        <a href="index.php" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="card-body">

        <?php if (empty($students)): ?>
            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <p>No students are assigned to this class yet.</p>
                <a href="<?php echo getBasePath(); ?>admin/students/add.php" class="btn btn-primary" style="margin-top:12px;">Add Students</a>
            </div>
        <?php else: ?>

            <!-- Quick mark all -->
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
                    <!-- Hidden input holds the value, buttons update it -->
                    <input type="hidden" name="attendance[<?php echo $student['id']; ?>]"
                           id="att_<?php echo $student['id']; ?>"
                           value="<?php echo $currentStatus; ?>">
                    <div class="att-toggle">
                        <button type="button"
                                class="att-btn att-present <?php echo $currentStatus === 'Present' ? 'present-active' : ''; ?>"
                                data-student="<?php echo $student['id']; ?>"
                                data-status="Present">
                            ✅ Present
                        </button>
                        <button type="button"
                                class="att-btn att-absent <?php echo $currentStatus === 'Absent' ? 'absent-active' : ''; ?>"
                                data-student="<?php echo $student['id']; ?>"
                                data-status="Absent">
                            ❌ Absent
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>

                <div style="margin-top:24px;padding-top:16px;border-top:1px solid var(--border);display:flex;gap:12px;">
                    <button type="submit" class="btn btn-primary">💾 Save Attendance</button>
                    <a href="index.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
// Individual attendance button toggle
document.querySelectorAll('.att-toggle').forEach(function(group) {
    const presentBtn = group.querySelector('.att-present');
    const absentBtn  = group.querySelector('.att-absent');
    const studentId  = presentBtn ? presentBtn.dataset.student : null;
    const hiddenInput = studentId ? document.getElementById('att_' + studentId) : null;

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

// Mark all present
document.getElementById('markAllPresent').addEventListener('click', function() {
    document.querySelectorAll('.att-present').forEach(function(btn) { btn.click(); });
});

// Mark all absent
document.getElementById('markAllAbsent').addEventListener('click', function() {
    document.querySelectorAll('.att-absent').forEach(function(btn) { btn.click(); });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
