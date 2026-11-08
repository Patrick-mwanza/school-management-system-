<?php
// teacher/exams/results.php — Teacher views exam results
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pdo    = getDB();
$examId = intval($_GET['exam_id'] ?? 0);

// All exams
$allExams = $pdo->query(
    "SELECT e.id, e.exam_name, c.class_name, s.subject_name, e.exam_date
     FROM examinations e
     LEFT JOIN classes c ON e.class_id = c.id
     LEFT JOIN subjects s ON e.subject_id = s.id
     ORDER BY e.exam_date DESC"
)->fetchAll();

$exam    = null;
$results = [];

if ($examId) {
    $eStmt = $pdo->prepare(
        "SELECT e.*, c.class_name, s.subject_name
         FROM examinations e
         LEFT JOIN classes c ON e.class_id = c.id
         LEFT JOIN subjects s ON e.subject_id = s.id
         WHERE e.id = ?"
    );
    $eStmt->execute([$examId]);
    $exam = $eStmt->fetch();

    if ($exam) {
        $rStmt = $pdo->prepare(
            "SELECT r.*, st.full_name, st.admission_number
             FROM results r
             JOIN students st ON r.student_id = st.id
             WHERE r.exam_id = ?
             ORDER BY r.marks_obtained DESC"
        );
        $rStmt->execute([$examId]);
        $results = $rStmt->fetchAll();
    }
}

$totalSat = count($results);
$avgMarks = $totalSat > 0 ? round(array_sum(array_column($results,'marks_obtained')) / $totalSat, 2) : 0;

$pageTitle   = 'View Results';
$breadcrumbs = [['label' => 'Results']];
include __DIR__ . '/../../includes/page_start.php';
?>

<!-- Exam Selector -->
<div class="card" style="max-width:500px;margin-bottom:20px;">
    <div class="card-header"><h2>🏆 Select Examination</h2></div>
    <div class="card-body">
        <form method="GET" action="results.php">
            <div class="form-group">
                <label for="exam_id">Examination</label>
                <select id="exam_id" name="exam_id">
                    <option value="">-- Choose Exam --</option>
                    <?php foreach ($allExams as $ex): ?>
                        <option value="<?php echo $ex['id']; ?>" <?php echo $examId == $ex['id'] ? 'selected' : ''; ?>>
                            <?php echo e($ex['exam_name']); ?> — <?php echo e($ex['class_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary">📊 View</button>
                <?php if (!empty($results)): ?>
                    <button onclick="window.print()" type="button" class="btn btn-outline no-print">🖨️ Print</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if ($exam): ?>

<?php if (!empty($results)): ?>
<!-- Stats -->
<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card"><div class="stat-icon icon-blue">👥</div><div class="stat-info"><div class="stat-label">Total Sat</div><div class="stat-value"><?php echo $totalSat; ?></div></div></div>
    <div class="stat-card"><div class="stat-icon icon-green">📊</div><div class="stat-info"><div class="stat-label">Average</div><div class="stat-value"><?php echo $avgMarks; ?></div></div></div>
    <div class="stat-card"><div class="stat-icon icon-orange">🏅</div><div class="stat-info"><div class="stat-label">Highest</div><div class="stat-value"><?php echo max(array_column($results,'marks_obtained')); ?></div></div></div>
    <div class="stat-card"><div class="stat-icon icon-red">📉</div><div class="stat-info"><div class="stat-label">Lowest</div><div class="stat-value"><?php echo min(array_column($results,'marks_obtained')); ?></div></div></div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div>
            <h2>🏆 <?php echo e($exam['exam_name']); ?></h2>
            <p style="font-size:0.85rem;color:var(--text-mid);margin-top:4px;">
                <?php echo e($exam['class_name']); ?> &nbsp;|&nbsp;
                <?php echo e($exam['subject_name']); ?> &nbsp;|&nbsp;
                <?php echo formatDate($exam['exam_date']); ?>
            </p>
        </div>
        <a href="marks.php?exam_id=<?php echo $examId; ?>" class="btn btn-warning btn-sm no-print">📝 Edit Marks</a>
    </div>
    <div class="table-wrapper">
        <table id="dataTable">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Admission No.</th>
                    <th>Student Name</th>
                    <th>Marks / <?php echo e($exam['max_marks']); ?></th>
                    <th>Percentage</th>
                    <th>Grade</th>
                    <th>Pass/Fail</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($results)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-light);">No marks entered yet. <a href="marks.php?exam_id=<?php echo $examId; ?>">Enter marks</a>.</td></tr>
                <?php else: ?>
                    <?php foreach ($results as $i => $r):
                        $pct   = round(($r['marks_obtained'] / $exam['max_marks']) * 100, 1);
                        $grade = $r['grade'] ?? getGrade($pct);
                    ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><span class="badge badge-primary"><?php echo e($r['admission_number']); ?></span></td>
                        <td><?php echo e($r['full_name']); ?></td>
                        <td><strong><?php echo e($r['marks_obtained']); ?></strong></td>
                        <td><?php echo $pct; ?>%</td>
                        <td><span class="badge grade-badge <?php echo getGradeClass($grade); ?>"><?php echo $grade; ?></span></td>
                        <td>
                            <?php if ($pct >= 50): ?>
                                <span class="badge badge-success">✅ Pass</span>
                            <?php else: ?>
                                <span class="badge badge-danger">❌ Fail</span>
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
