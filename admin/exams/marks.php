<?php
// admin/exams/marks.php — Enter / edit student marks for an exam
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pdo    = getDB();
$examId = intval($_GET['exam_id'] ?? $_POST['exam_id'] ?? 0);

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

// Load students in that class
$students = $pdo->prepare(
    "SELECT id, admission_number, full_name, gender FROM students WHERE class_id = ? ORDER BY full_name"
);
$students->execute([$exam['class_id']]);
$students = $students->fetchAll();

// Load existing results
$existing = [];
$resStmt  = $pdo->prepare("SELECT student_id, marks_obtained, grade FROM results WHERE exam_id = ?");
$resStmt->execute([$examId]);
foreach ($resStmt->fetchAll() as $row) {
    $existing[$row['student_id']] = $row;
}

// Handle POST — save marks
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marks = $_POST['marks'] ?? [];
    $saved = 0;

    foreach ($students as $student) {
        $sid = $student['id'];
        if (!isset($marks[$sid]) || $marks[$sid] === '') continue;

        $m = max(0, min((float)$marks[$sid], $exam['max_marks']));
        $g = getGrade(($m / $exam['max_marks']) * 100); // grade based on percentage

        $ups = $pdo->prepare(
            "INSERT INTO results (exam_id, student_id, marks_obtained, grade)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE marks_obtained = VALUES(marks_obtained), grade = VALUES(grade)"
        );
        $ups->execute([$examId, $sid, $m, $g]);
        $saved++;
    }

    redirectWithMessage(
        'results.php?exam_id=' . $examId,
        "Marks saved for $saved student(s). View results below.",
        'success'
    );
}

$pageTitle   = 'Enter Marks';
$breadcrumbs = [['label' => 'Examinations', 'url' => 'index.php'], ['label' => e($exam['exam_name']), 'url' => ''], ['label' => 'Enter Marks']];
include __DIR__ . '/../../includes/page_start.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h2>📝 Enter Marks — <?php echo e($exam['exam_name']); ?></h2>
            <p style="font-size:0.85rem;color:var(--text-mid);margin-top:4px;">
                🏛️ <?php echo e($exam['class_name']); ?> &nbsp;|&nbsp;
                📚 <?php echo e($exam['subject_name']); ?> &nbsp;|&nbsp;
                📅 <?php echo formatDate($exam['exam_date']); ?> &nbsp;|&nbsp;
                Max: <strong><?php echo e($exam['max_marks']); ?></strong>
            </p>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="results.php?exam_id=<?php echo $examId; ?>" class="btn btn-success btn-sm">🏆 View Results</a>
            <a href="index.php" class="btn btn-outline btn-sm">← Back</a>
        </div>
    </div>
    <div class="card-body">

        <?php if (empty($students)): ?>
            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <p>No students are assigned to <?php echo e($exam['class_name']); ?> yet.</p>
            </div>
        <?php else: ?>

            <!-- Grade legend -->
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;font-size:0.8rem;">
                <span>Grading:</span>
                <span class="badge grade-a">A: 80–100%</span>
                <span class="badge grade-b">B: 70–79%</span>
                <span class="badge grade-c">C: 60–69%</span>
                <span class="badge grade-d">D: 50–59%</span>
                <span class="badge grade-e">E: Below 50%</span>
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
                                <th>Marks (out of <?php echo e($exam['max_marks']); ?>)</th>
                                <th>Grade Preview</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $i => $student):
                                $existMark  = $existing[$student['id']]['marks_obtained'] ?? '';
                                $existGrade = $existing[$student['id']]['grade'] ?? '';
                            ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><span class="badge badge-primary"><?php echo e($student['admission_number']); ?></span></td>
                                <td><?php echo e($student['full_name']); ?></td>
                                <td>
                                    <input
                                        type="number"
                                        class="marks-input"
                                        name="marks[<?php echo $student['id']; ?>]"
                                        value="<?php echo $existMark !== '' ? e($existMark) : ''; ?>"
                                        min="0"
                                        max="<?php echo $exam['max_marks']; ?>"
                                        step="0.5"
                                        placeholder="0–<?php echo $exam['max_marks']; ?>"
                                        data-marks="1"
                                        data-max="<?php echo $exam['max_marks']; ?>"
                                        data-student-id="<?php echo $student['id']; ?>">
                                </td>
                                <td>
                                    <?php
                                    $gradeClass = $existGrade ? 'badge grade-badge ' . getGradeClass($existGrade) : 'badge';
                                    $gradeLabel = $existGrade ?: '—';
                                    ?>
                                    <span class="<?php echo $gradeClass; ?>" id="grade_<?php echo $student['id']; ?>">
                                        <?php echo $gradeLabel; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);display:flex;gap:12px;">
                    <button type="submit" class="btn btn-primary">💾 Save All Marks</button>
                    <a href="index.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>

            <script>
            // Live grade preview when marks are typed
            function calcGrade(marks, max) {
                var pct = (marks / max) * 100;
                if (pct >= 80) return 'A';
                if (pct >= 70) return 'B';
                if (pct >= 60) return 'C';
                if (pct >= 50) return 'D';
                return 'E';
            }
            var gradeClasses = { A:'grade-a', B:'grade-b', C:'grade-c', D:'grade-d', E:'grade-e' };

            document.querySelectorAll('input[data-marks]').forEach(function(input) {
                input.addEventListener('input', function() {
                    var sid   = this.dataset.studentId;
                    var max   = parseFloat(this.dataset.max || 100);
                    var span  = document.getElementById('grade_' + sid);
                    if (!span) return;
                    var val   = parseFloat(this.value);
                    if (this.value === '' || isNaN(val)) {
                        span.textContent = '—';
                        span.className   = 'badge';
                    } else {
                        var g = calcGrade(val, max);
                        span.textContent = g;
                        span.className   = 'badge grade-badge ' + gradeClasses[g];
                    }
                });
            });
            </script>

        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
