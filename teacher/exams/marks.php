<?php
// teacher/exams/marks.php — Teacher enters marks
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pdo    = getDB();
$examId = intval($_GET['exam_id'] ?? $_POST['exam_id'] ?? 0);

// Load all exams teacher can see
$allExams = $pdo->query(
    "SELECT e.id, e.exam_name, c.class_name, s.subject_name, e.exam_date
     FROM examinations e
     LEFT JOIN classes c ON e.class_id = c.id
     LEFT JOIN subjects s ON e.subject_id = s.id
     ORDER BY e.exam_date DESC, e.exam_name"
)->fetchAll();

$exam     = null;
$students = [];
$existing = [];

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
        $ss = $pdo->prepare("SELECT id, admission_number, full_name, gender FROM students WHERE class_id = ? ORDER BY full_name");
        $ss->execute([$exam['class_id']]);
        $students = $ss->fetchAll();

        $rs = $pdo->prepare("SELECT student_id, marks_obtained, grade FROM results WHERE exam_id = ?");
        $rs->execute([$examId]);
        foreach ($rs->fetchAll() as $row) { $existing[$row['student_id']] = $row; }
    }
}

// Save marks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $examId && $exam) {
    $marks = $_POST['marks'] ?? [];
    $saved = 0;
    foreach ($students as $student) {
        $sid = $student['id'];
        if (!isset($marks[$sid]) || $marks[$sid] === '') continue;
        $m = max(0, min((float)$marks[$sid], $exam['max_marks']));
        $g = getGrade(($m / $exam['max_marks']) * 100);
        $ups = $pdo->prepare(
            "INSERT INTO results (exam_id, student_id, marks_obtained, grade)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE marks_obtained = VALUES(marks_obtained), grade = VALUES(grade)"
        );
        $ups->execute([$examId, $sid, $m, $g]);
        $saved++;
    }
    redirectWithMessage('results.php?exam_id=' . $examId, "Marks saved for $saved student(s).", 'success');
}

$pageTitle   = 'Enter Marks';
$breadcrumbs = [['label' => 'Examinations', 'url' => 'marks.php'], ['label' => 'Enter Marks']];
include __DIR__ . '/../../includes/page_start.php';
?>

<!-- Exam Selector -->
<div class="card" style="max-width:500px;margin-bottom:20px;">
    <div class="card-header"><h2>📝 Select Examination</h2></div>
    <div class="card-body">
        <form method="GET" action="marks.php" data-validate>
            <div class="form-group">
                <label for="exam_id">Examination *</label>
                <select id="exam_id" name="exam_id" required>
                    <option value="">-- Choose Exam --</option>
                    <?php foreach ($allExams as $ex): ?>
                        <option value="<?php echo $ex['id']; ?>" <?php echo $examId == $ex['id'] ? 'selected' : ''; ?>>
                            <?php echo e($ex['exam_name']); ?> — <?php echo e($ex['class_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">📋 Load Students</button>
        </form>
    </div>
</div>

<!-- Marks Entry -->
<?php if ($exam && !empty($students)): ?>
<div class="card">
    <div class="card-header">
        <div>
            <h2>📝 <?php echo e($exam['exam_name']); ?></h2>
            <p style="font-size:0.85rem;color:var(--text-mid);margin-top:4px;">
                🏛️ <?php echo e($exam['class_name']); ?> &nbsp;|&nbsp;
                📚 <?php echo e($exam['subject_name']); ?> &nbsp;|&nbsp;
                Max: <strong><?php echo e($exam['max_marks']); ?></strong>
            </p>
        </div>
        <a href="results.php?exam_id=<?php echo $examId; ?>" class="btn btn-success btn-sm">🏆 View Results</a>
    </div>
    <div class="card-body">
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;font-size:0.8rem;">
            <span>Grading (by %):</span>
            <span class="badge grade-a">A ≥80%</span>
            <span class="badge grade-b">B ≥70%</span>
            <span class="badge grade-c">C ≥60%</span>
            <span class="badge grade-d">D ≥50%</span>
            <span class="badge grade-e">E &lt;50%</span>
        </div>
        <form method="POST" action="marks.php">
            <input type="hidden" name="exam_id" value="<?php echo $examId; ?>">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Admission No.</th>
                            <th>Student Name</th>
                            <th>Marks (of <?php echo e($exam['max_marks']); ?>)</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $i => $student):
                            $em = $existing[$student['id']]['marks_obtained'] ?? '';
                            $eg = $existing[$student['id']]['grade'] ?? '';
                        ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><span class="badge badge-primary"><?php echo e($student['admission_number']); ?></span></td>
                            <td><?php echo e($student['full_name']); ?></td>
                            <td>
                                <input type="number" class="marks-input"
                                       name="marks[<?php echo $student['id']; ?>]"
                                       value="<?php echo $em !== '' ? e($em) : ''; ?>"
                                       min="0" max="<?php echo $exam['max_marks']; ?>" step="0.5"
                                       placeholder="0–<?php echo $exam['max_marks']; ?>"
                                       data-marks="1"
                                       data-max="<?php echo $exam['max_marks']; ?>"
                                       data-student-id="<?php echo $student['id']; ?>">
                            </td>
                            <td>
                                <span class="<?php echo $eg ? 'badge grade-badge '.getGradeClass($eg) : 'badge'; ?>"
                                      id="grade_<?php echo $student['id']; ?>">
                                    <?php echo $eg ?: '—'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);">
                <button type="submit" class="btn btn-primary">💾 Save All Marks</button>
            </div>
        </form>
    </div>
</div>

<script>
function calcGrade(marks, max) {
    var pct = (marks / max) * 100;
    if (pct >= 80) return 'A'; if (pct >= 70) return 'B';
    if (pct >= 60) return 'C'; if (pct >= 50) return 'D'; return 'E';
}
var gradeClasses = { A:'grade-a', B:'grade-b', C:'grade-c', D:'grade-d', E:'grade-e' };
document.querySelectorAll('input[data-marks]').forEach(function(input) {
    input.addEventListener('input', function() {
        var sid  = this.dataset.studentId;
        var max  = parseFloat(this.dataset.max || 100);
        var span = document.getElementById('grade_' + sid);
        if (!span) return;
        var val  = parseFloat(this.value);
        if (this.value === '' || isNaN(val)) {
            span.textContent = '—'; span.className = 'badge';
        } else {
            var g = calcGrade(val, max);
            span.textContent = g;
            span.className   = 'badge grade-badge ' + gradeClasses[g];
        }
    });
});
</script>

<?php elseif ($examId && empty($students)): ?>
<div class="alert alert-warning">No students found in this exam's class.</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
