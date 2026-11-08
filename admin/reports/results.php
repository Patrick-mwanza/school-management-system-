<?php
// admin/reports/results.php — Examination results report (printable)
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pdo     = getDB();
$examId  = intval($_GET['exam_id'] ?? 0);
$classId = intval($_GET['class_id'] ?? 0);
$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();

// Load exams (filtered by class if selected)
$examSql = "SELECT e.id, e.exam_name, c.class_name, s.subject_name, e.exam_date
            FROM examinations e
            LEFT JOIN classes c ON e.class_id = c.id
            LEFT JOIN subjects s ON e.subject_id = s.id";
if ($classId) { $examSql .= " WHERE e.class_id = $classId"; }
$examSql .= " ORDER BY e.exam_date DESC, e.exam_name";
$exams = $pdo->query($examSql)->fetchAll();

// Load results for selected exam
$results    = [];
$exam       = null;
$gradeDist  = ['A'=>0,'B'=>0,'C'=>0,'D'=>0,'E'=>0];
$totalSat   = 0;
$avgMarks   = 0;

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
            "SELECT r.*, st.full_name, st.admission_number, st.gender
             FROM results r
             JOIN students st ON r.student_id = st.id
             WHERE r.exam_id = ?
             ORDER BY r.marks_obtained DESC"
        );
        $rStmt->execute([$examId]);
        $results   = $rStmt->fetchAll();
        $totalSat  = count($results);
        $avgMarks  = $totalSat > 0 ? round(array_sum(array_column($results, 'marks_obtained')) / $totalSat, 2) : 0;
        foreach ($results as $r) {
            $g = $r['grade'] ?? 'E';
            if (isset($gradeDist[$g])) $gradeDist[$g]++;
        }
    }
}

$pageTitle   = 'Results Report';
$breadcrumbs = [['label' => 'Reports'], ['label' => 'Results']];
include __DIR__ . '/../../includes/page_start.php';
?>

<!-- Filters -->
<div class="card" style="max-width:640px;margin-bottom:20px;" >
    <div class="card-header"><h2>🔍 Select Examination</h2></div>
    <div class="card-body">
        <form method="GET" action="results.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="class_id">Filter by Class</label>
                    <select id="class_id" name="class_id">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $classId == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo e($c['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="exam_id">Select Examination *</label>
                    <select id="exam_id" name="exam_id" required>
                        <option value="">-- Choose Exam --</option>
                        <?php foreach ($exams as $ex): ?>
                            <option value="<?php echo $ex['id']; ?>" <?php echo $examId == $ex['id'] ? 'selected' : ''; ?>>
                                <?php echo e($ex['exam_name']); ?> — <?php echo e($ex['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary">📊 Generate Report</button>
                <?php if (!empty($results)): ?>
                    <button onclick="window.print()" type="button" class="btn btn-outline no-print">🖨️ Print Report</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if ($exam && !empty($results)): ?>

<!-- Print Header (only shows when printing) -->
<div style="display:none;" class="print-header" id="printHeaderBanner">
    <h2 style="text-align:center;margin:0;">SCHOOL MANAGEMENT SYSTEM</h2>
    <h3 style="text-align:center;margin:4px 0;">Examination Results Report</h3>
    <p style="text-align:center;color:#666;margin:0;">
        <?php echo e($exam['exam_name']); ?> &bull;
        <?php echo e($exam['class_name']); ?> &bull;
        <?php echo e($exam['subject_name']); ?> &bull;
        <?php echo formatDate($exam['exam_date']); ?>
    </p>
    <hr>
</div>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card"><div class="stat-icon icon-blue">👥</div><div class="stat-info"><div class="stat-label">Total Sat</div><div class="stat-value"><?php echo $totalSat; ?></div></div></div>
    <div class="stat-card"><div class="stat-icon icon-green">📊</div><div class="stat-info"><div class="stat-label">Average</div><div class="stat-value"><?php echo $avgMarks; ?></div></div></div>
    <div class="stat-card"><div class="stat-icon icon-orange">🏅</div><div class="stat-info"><div class="stat-label">Highest</div><div class="stat-value"><?php echo max(array_column($results,'marks_obtained')); ?></div></div></div>
    <div class="stat-card"><div class="stat-icon icon-red">📉</div><div class="stat-info"><div class="stat-label">Lowest</div><div class="stat-value"><?php echo min(array_column($results,'marks_obtained')); ?></div></div></div>
</div>

<!-- Results Table -->
<div class="card">
    <div class="card-header">
        <h2>📋 <?php echo e($exam['exam_name']); ?> — Full Results</h2>
    </div>
    <div class="table-wrapper">
        <table id="dataTable">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Admission No.</th>
                    <th>Student Name</th>
                    <th>Gender</th>
                    <th>Marks / <?php echo e($exam['max_marks']); ?></th>
                    <th>Percentage</th>
                    <th>Grade</th>
                    <th>Pass/Fail</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $i => $r):
                    $pct    = round(($r['marks_obtained'] / $exam['max_marks']) * 100, 1);
                    $grade  = $r['grade'] ?? getGrade($pct);
                    $passed = $pct >= 50;
                ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><span class="badge badge-primary"><?php echo e($r['admission_number']); ?></span></td>
                    <td><?php echo e($r['full_name']); ?></td>
                    <td><?php echo e($r['gender']); ?></td>
                    <td><strong><?php echo e($r['marks_obtained']); ?></strong></td>
                    <td><?php echo $pct; ?>%</td>
                    <td><span class="badge grade-badge <?php echo getGradeClass($grade); ?>"><?php echo $grade; ?></span></td>
                    <td>
                        <?php if ($passed): ?>
                            <span class="badge badge-success">✅ Pass</span>
                        <?php else: ?>
                            <span class="badge badge-danger">❌ Fail</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:var(--bg-light);font-weight:600;">
                    <td colspan="4" style="padding:12px 16px;text-align:right;">Class Average:</td>
                    <td colspan="4" style="padding:12px 16px;"><?php echo $avgMarks; ?> / <?php echo $exam['max_marks']; ?> (<?php echo round(($avgMarks/$exam['max_marks'])*100,1); ?>%)</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<style>
@media print {
    #printHeaderBanner { display: block !important; margin-bottom:16px; }
}
</style>

<?php elseif ($examId && empty($results)): ?>
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-icon">📝</div>
            <p>No marks have been entered for this examination yet.</p>
            <a href="../exams/marks.php?exam_id=<?php echo $examId; ?>" class="btn btn-primary" style="margin-top:12px;">Enter Marks</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
