<?php
// admin/attendance/index.php — Select class and date to take/view attendance
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pdo     = getDB();
$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();

$pageTitle   = 'Attendance';
$breadcrumbs = [['label' => 'Attendance']];
include __DIR__ . '/../../includes/page_start.php';
?>

<div class="card" style="max-width:520px; margin-bottom:24px;">
    <div class="card-header"><h2>✅ Take Attendance</h2></div>
    <div class="card-body">
        <form method="GET" action="mark.php" data-validate id="attSelectForm">
            <div class="form-group">
                <label for="class_id">Select Class *</label>
                <select id="class_id" name="class_id" required>
                    <option value="">-- Choose Class --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo e($c['class_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="att_date">Date *</label>
                <input type="date" id="att_date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">📋 Open Attendance Sheet</button>
        </form>
    </div>
</div>

<!-- View Records Link -->
<div class="card" style="max-width:520px;">
    <div class="card-header"><h2>📋 View Attendance Records</h2></div>
    <div class="card-body">
        <form method="GET" action="view.php" data-validate id="attViewForm">
            <div class="form-group">
                <label for="v_class_id">Select Class *</label>
                <select id="v_class_id" name="class_id" required>
                    <option value="">-- Choose Class --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo e($c['class_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="from_date">From</label>
                    <input type="date" id="from_date" name="from_date" value="<?php echo date('Y-m-01'); ?>">
                </div>
                <div class="form-group">
                    <label for="to_date">To</label>
                    <input type="date" id="to_date" name="to_date" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-outline">📊 View Records</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
