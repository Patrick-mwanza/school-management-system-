<?php
// dashboard.php - Admin dashboard
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
requireAdmin();

$pdo = getDB();

// Summary counts
$totalStudents  = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalTeachers  = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
$totalClasses   = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$totalSubjects  = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();

// Recent student registrations (last 5)
$recentStudents = $pdo->query(
    "SELECT s.full_name, s.admission_number, s.gender, c.class_name, s.created_at
     FROM students s
     LEFT JOIN classes c ON s.class_id = c.id
     ORDER BY s.created_at DESC LIMIT 5"
)->fetchAll();

// Today's attendance count
$today         = date('Y-m-d');
$todayAttendance = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ? AND status = 'Present'");
$todayAttendance->execute([$today]);
$presentToday  = $todayAttendance->fetchColumn();

$pageTitle   = 'Dashboard';
$breadcrumbs = [];
include __DIR__ . '/includes/page_start.php';
?>

<!-- Stats Grid -->
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon icon-blue">🎓</div>
        <div class="stat-info">
            <div class="stat-label">Total Students</div>
            <div class="stat-value"><?php echo $totalStudents; ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon icon-green">👩‍🏫</div>
        <div class="stat-info">
            <div class="stat-label">Total Teachers</div>
            <div class="stat-value"><?php echo $totalTeachers; ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon icon-orange">🏛️</div>
        <div class="stat-info">
            <div class="stat-label">Total Classes</div>
            <div class="stat-value"><?php echo $totalClasses; ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon icon-cyan">📚</div>
        <div class="stat-info">
            <div class="stat-label">Total Subjects</div>
            <div class="stat-value"><?php echo $totalSubjects; ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon icon-green">✅</div>
        <div class="stat-info">
            <div class="stat-label">Present Today</div>
            <div class="stat-value"><?php echo $presentToday; ?></div>
        </div>
    </div>

</div>

<!-- Recent Students -->
<div class="card">
    <div class="card-header">
        <h2>🎓 Recent Student Registrations</h2>
        <a href="admin/students/add.php" class="btn btn-primary btn-sm">➕ Add Student</a>
    </div>
    <div class="table-wrapper">
        <table id="dataTable">
            <thead>
                <tr>
                    <th>Admission No.</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Class</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentStudents)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:var(--text-light); padding:40px;">
                            No students registered yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentStudents as $s): ?>
                        <tr>
                            <td><span class="badge badge-primary"><?php echo e($s['admission_number']); ?></span></td>
                            <td><?php echo e($s['full_name']); ?></td>
                            <td><?php echo e($s['gender']); ?></td>
                            <td><?php echo e($s['class_name'] ?? '—'); ?></td>
                            <td><?php echo formatDate($s['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (!empty($recentStudents)): ?>
        <div style="padding:14px 24px; border-top:1px solid var(--border);">
            <a href="admin/students/index.php" class="btn btn-outline btn-sm">View All Students →</a>
        </div>
    <?php endif; ?>
</div>

<!-- Quick Actions -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-top:24px;">
    <a href="admin/students/add.php" class="btn btn-primary" style="justify-content:center; padding:14px;">
        🎓 Add Student
    </a>
    <a href="admin/teachers/add.php" class="btn btn-success" style="justify-content:center; padding:14px;">
        👩‍🏫 Add Teacher
    </a>
    <a href="admin/attendance/index.php" class="btn btn-warning" style="justify-content:center; padding:14px;">
        ✅ Take Attendance
    </a>
    <a href="admin/exams/add.php" class="btn btn-outline" style="justify-content:center; padding:14px;">
        📝 Add Exam
    </a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
