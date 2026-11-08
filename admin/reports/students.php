<?php
// admin/reports/students.php — Student list report
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pdo     = getDB();
$classId = intval($_GET['class_id'] ?? 0);
$gender  = $_GET['gender'] ?? '';
$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();

// Build query with optional filters
$sql    = "SELECT s.*, c.class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE 1=1";
$params = [];
if ($classId) { $sql .= " AND s.class_id = ?"; $params[] = $classId; }
if ($gender)  { $sql .= " AND s.gender = ?";   $params[] = $gender; }
$sql .= " ORDER BY c.class_name, s.full_name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$pageTitle   = 'Student Report';
$breadcrumbs = [['label' => 'Reports'], ['label' => 'Students']];
include __DIR__ . '/../../includes/page_start.php';
?>

<!-- Filters -->
<div class="card" style="max-width:600px;margin-bottom:20px;" >
    <div class="card-header"><h2>🔍 Filter Report</h2></div>
    <div class="card-body">
        <form method="GET" action="students.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="class_id">Class</label>
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
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="">All Genders</option>
                        <option value="Male"   <?php echo $gender === 'Male'   ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $gender === 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary">📊 Generate</button>
                <a href="students.php" class="btn btn-outline">Reset</a>
                <?php if (!empty($students)): ?>
                    <button onclick="window.print()" type="button" class="btn btn-outline no-print">🖨️ Print</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Results -->
<div class="card">
    <div class="card-header">
        <h2>
            Student List
            <?php if (!empty($students)): ?>
                <span class="badge badge-primary"><?php echo count($students); ?> students</span>
            <?php endif; ?>
        </h2>
    </div>
    <div class="table-wrapper">
        <table id="dataTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Admission No.</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Phone</th>
                    <th>Class</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-light);">No students match the selected filters.</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $i => $s): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><span class="badge badge-primary"><?php echo e($s['admission_number']); ?></span></td>
                        <td><?php echo e($s['full_name']); ?></td>
                        <td><?php echo e($s['gender']); ?></td>
                        <td><?php echo formatDate($s['date_of_birth']); ?></td>
                        <td><?php echo e($s['phone_number'] ?: '—'); ?></td>
                        <td><?php echo e($s['class_name'] ?? '—'); ?></td>
                        <td><?php echo formatDate($s['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
