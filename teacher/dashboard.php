<?php
// teacher/dashboard.php — Teacher dashboard
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';
requireLogin();

$pdo       = getDB();
$teacherId = $_SESSION['teacher_id'] ?? 0;

// Get teacher's subject
$subject = null;
if ($teacherId) {
    $ts = $pdo->prepare("SELECT t.*, s.subject_name, s.id as sid FROM teachers t LEFT JOIN subjects s ON t.subject_id = s.id WHERE t.id = ?");
    $ts->execute([$teacherId]);
    $subject = $ts->fetch();
}

// Count students (all classes - teacher sees all)
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalClasses  = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();

// Today's attendance marked by this teacher
$today    = date('Y-m-d');
$myAttStmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE marked_by = ? AND date = ?");
$myAttStmt->execute([$_SESSION['user_id'], $today]);
$markedToday = $myAttStmt->fetchColumn();

// Recent exams for this teacher's subject
$recentExams = [];
if ($subject && $subject['sid']) {
    $exStmt = $pdo->prepare(
        "SELECT e.*, c.class_name FROM examinations e
         LEFT JOIN classes c ON e.class_id = c.id
         WHERE e.subject_id = ?
         ORDER BY e.exam_date DESC LIMIT 5"
    );
    $exStmt->execute([$subject['sid']]);
    $recentExams = $exStmt->fetchAll();
}

$pageTitle   = 'Teacher Dashboard';
$breadcrumbs = [];
include __DIR__ . '/../includes/page_start.php';
?>

<!-- Welcome banner -->
<div class="card" style="margin-bottom:24px;background:linear-gradient(135deg,#1a56db,#0ea5e9);border:none;">
    <div class="card-body" style="color:#fff;">
        <h2 style="color:#fff;font-size:1.3rem;">
            👋 Welcome, <?php echo e(getCurrentUserName()); ?>!
        </h2>
        <p style="color:rgba(255,255,255,0.8);margin-top:4px;">
            <?php if ($subject && $subject['subject_name']): ?>
                You teach <strong><?php echo e($subject['subject_name']); ?></strong>
            <?php else: ?>
                No subject assigned yet. Contact admin.
            <?php endif; ?>
            &nbsp;|&nbsp; Today: <strong><?php echo date('l, d M Y'); ?></strong>
        </p>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon icon-blue">🎓</div>
        <div class="stat-info">
            <div class="stat-label">Total Students</div>
            <div class="stat-value"><?php echo $totalStudents; ?></div>
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
        <div class="stat-icon icon-green">✅</div>
        <div class="stat-info">
            <div class="stat-label">Marked Today</div>
            <div class="stat-value"><?php echo $markedToday; ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-cyan">📝</div>
        <div class="stat-info">
            <div class="stat-label">My Exams</div>
            <div class="stat-value"><?php echo count($recentExams); ?></div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin:24px 0;">
    <a href="attendance/mark.php" class="btn btn-primary" style="justify-content:center;padding:14px;">✅ Take Attendance</a>
    <a href="attendance/view.php" class="btn btn-outline" style="justify-content:center;padding:14px;">📋 View Attendance</a>
    <a href="exams/marks.php"     class="btn btn-success" style="justify-content:center;padding:14px;">📝 Enter Marks</a>
    <a href="exams/results.php"   class="btn btn-outline" style="justify-content:center;padding:14px;">🏆 View Results</a>
</div>

<!-- Recent Exams -->
<?php if (!empty($recentExams)): ?>
<div class="card">
    <div class="card-header">
        <h2>📝 Recent Examinations (My Subject)</h2>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Exam Name</th>
                    <th>Class</th>
                    <th>Date</th>
                    <th>Max Marks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentExams as $i => $ex): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo e($ex['exam_name']); ?></td>
                    <td><?php echo e($ex['class_name']); ?></td>
                    <td><?php echo formatDate($ex['exam_date']); ?></td>
                    <td><?php echo e($ex['max_marks']); ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="exams/marks.php?exam_id=<?php echo $ex['id']; ?>" class="btn btn-warning btn-sm">📝 Marks</a>
                            <a href="exams/results.php?exam_id=<?php echo $ex['id']; ?>" class="btn btn-success btn-sm">🏆 Results</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
