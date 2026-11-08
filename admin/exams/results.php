<?php
// admin/exams/results.php — View examination results
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pdo    = getDB();
$examId = intval($_GET['exam_id'] ?? 0);

// Load exam
$examStmt = $pdo->prepare(
    "SELECT e.*, c.class_name, s.subject_name
     FROM examinations e
     LEFT JOIN classes c ON e.class_id = c.id
     LEFT JOIN subjects s ON e.subject_id = s.id
     WHERE e.id = ?"
);
$examStmt->execute([$examId]);
$exam = $examStmt->fetch();
if (!$exam) redirectWithMessage('index.php', 'Examination not found.', 'danger');

// Load results with student info
$resStmt = $pdo->prepare(
    "SELECT r.*, s.full_name, s.admission_number, s.gender
     FROM results r
     JOIN students s ON r.student_id = s.id
     WHERE r.exam_id = ?
     ORDER BY r.marks_obtained DESC"
);
$resStmt->execute([$examId]);
$results = $resStmt->fetchAll();

// Calculate stats
$totalStudents  = count($results);
$totalMarks     = array_sum(array_column($results, 'marks_obtained'));
$averageMarks   = $totalStudents > 0 ? round($totalMarks / $totalStudents, 2) : 0;
$highestMarks   = $totalStudents > 0 ? max(array_column($results, 'marks_obtained')) : 0;
$lowestMarks    = $totalStudents > 0 ? min(array_column($results, 'marks_obtained')) : 0;
$passCount      = count(array_filter($results, fn($r) => $r['marks_obtained'] >= ($exam['max_marks'] * 0.5)));

// Grade distribution
$gradeDist = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];
foreach ($results as $r) {
    $g = $r['grade'] ?? 'E';
    if (isset($gradeDist[$g])) $gradeDist[$g]++;
}

$pageTitle   = 'Exam Results';
$breadcrumbs = [['label' => 'Examinations', 'url' => 'index.php'], ['label' => 'Results']];
include __DIR__ . '/../../includes/page_start.php';
?>

<!-- Exam Info Card -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <div>
            <h2>🏆 <?php echo e($exam['exam_name']); ?></h2>
            <p style="font-size:0.85rem;color:var(--text-mid);margin-top:4px;">
                🏛️ <?php echo e($exam['class_name']); ?> &nbsp;|&nbsp;
                📚 <?php echo e($exam['subject_name']); ?> &nbsp;|&nbsp;
                📅 <?php echo formatDate($exam['exam_date']); ?> &nbsp;|&nbsp;
                Max Marks: <strong><?php echo e($exam['max_marks']); ?></strong>
            </p>
        </div>
        <div style="display:flex;gap:8px;" class="no-print">
            <a href="marks.php?exam_id=<?php echo $examId; ?>" class="btn btn-warning btn-sm">📝 Edit Marks</a>
            <button onclick="window.print()" class="btn btn-outline btn-sm">🖨️ Print</button>
        </div>
    </div>
</div>

<?php if (empty($results)): ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-icon">📝</div>
                <p>No marks entered yet for this examination.</p>
                <a href="marks.php?exam_id=<?php echo $examId; ?>" class="btn btn-primary" style="margin-top:12px;">Enter Marks Now</a>
            </div>
        </div>
    </div>
<?php else: ?>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon icon-blue">👥</div>
        <div class="stat-info">
            <div class="stat-label">Total Sat</div>
            <div class="stat-value"><?php echo $totalStudents; ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-green">📊</div>
        <div class="stat-info">
            <div class="stat-label">Average</div>
            <div class="stat-value"><?php echo $averageMarks; ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-orange">🏅</div>
        <div class="stat-info">
            <div class="stat-label">Highest</div>
            <div class="stat-value"><?php echo $highestMarks; ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-red">📉</div>
        <div class="stat-info">
            <div class="stat-label">Lowest</div>
            <div class="stat-value"><?php echo $lowestMarks; ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-green">✅</div>
        <div class="stat-info">
            <div class="stat-label">Passed</div>
            <div class="stat-value"><?php echo $passCount; ?>/<?php echo $totalStudents; ?></div>
        </div>
    </div>
</div>

<!-- Grade Distribution -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><h2>Grade Distribution</h2></div>
    <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap;">
        <?php foreach ($gradeDist as $g => $count): ?>
            <div style="text-align:center;min-width:70px;">
                <span class="badge grade-badge <?php echo getGradeClass($g); ?>" style="font-size:1.2rem;width:48px;height:48px;">
                    <?php echo $g; ?>
                </span>
                <div style="margin-top:6px;font-size:0.85rem;color:var(--text-mid);"><?php echo $count; ?> student<?php echo $count !== 1 ? 's' : ''; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Results Table -->
<div class="card">
    <div class="card-header">
        <h2>Student Results</h2>
        <div class="search-bar no-print">
            <span class="search-icon">🔍</span>
            <input type="text" id="tableSearch" placeholder="Search student...">
        </div>
    </div>
    <div class="table-wrapper">
        <table id="dataTable">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Admission No.</th>
                    <th>Student Name</th>
                    <th>Gender</th>
                    <th>Marks (<?php echo e($exam['max_marks']); ?>)</th>
                    <th>Percentage</th>
                    <th>Grade</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $i => $r):
                    $pct    = round(($r['marks_obtained'] / $exam['max_marks']) * 100, 1);
                    $grade  = $r['grade'] ?? getGrade($pct);
                    $remark = $pct >= 50 ? 'Pass' : 'Fail';
                ?>
                <tr>
                    <td><strong><?php echo $i + 1; ?></strong></td>
                    <td><span class="badge badge-primary"><?php echo e($r['admission_number']); ?></span></td>
                    <td><?php echo e($r['full_name']); ?></td>
                    <td><?php echo e($r['gender']); ?></td>
                    <td><strong><?php echo e($r['marks_obtained']); ?></strong></td>
                    <td><?php echo $pct; ?>%</td>
                    <td>
                        <span class="badge grade-badge <?php echo getGradeClass($grade); ?>">
                            <?php echo $grade; ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($remark === 'Pass'): ?>
                            <span class="badge badge-success">✅ Pass</span>
                        <?php else: ?>
                            <span class="badge badge-danger">❌ Fail</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr id="noResultsRow" style="display:none;">
                    <td colspan="8" style="text-align:center;padding:20px;color:var(--text-light);">No matching students.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
