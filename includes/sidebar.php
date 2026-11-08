<?php
// includes/sidebar.php
// Role-aware sidebar navigation
$base = getBasePath();
$role = $_SESSION['role'] ?? 'teacher';
?>
<aside class="sidebar" id="sidebar">

    <div class="sidebar-brand">
        <div class="brand-icon">🏫</div>
        <div class="brand-text">
            <h2>SchoolMS</h2>
            <span>Management System</span>
        </div>
    </div>

    <nav class="sidebar-nav">

        <div class="nav-section-label">Main</div>

        <?php if ($role === 'admin'): ?>
            <a href="<?php echo $base; ?>dashboard.php">
                <span class="nav-icon">📊</span> Dashboard
            </a>
        <?php else: ?>
            <a href="<?php echo $base; ?>teacher/dashboard.php">
                <span class="nav-icon">📊</span> Dashboard
            </a>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>

            <div class="nav-section-label">Students</div>

            <a href="<?php echo $base; ?>admin/students/index.php">
                <span class="nav-icon">🎓</span> Students
            </a>
            <a href="<?php echo $base; ?>admin/students/add.php">
                <span class="nav-icon">➕</span> Add Student
            </a>

            <div class="nav-section-label">Staff</div>

            <a href="<?php echo $base; ?>admin/teachers/index.php">
                <span class="nav-icon">👩‍🏫</span> Teachers
            </a>
            <a href="<?php echo $base; ?>admin/teachers/add.php">
                <span class="nav-icon">➕</span> Add Teacher
            </a>

            <div class="nav-section-label">Academic</div>

            <a href="<?php echo $base; ?>admin/classes/index.php">
                <span class="nav-icon">🏛️</span> Classes
            </a>
            <a href="<?php echo $base; ?>admin/subjects/index.php">
                <span class="nav-icon">📚</span> Subjects
            </a>

        <?php endif; ?>

        <div class="nav-section-label">Records</div>

        <?php if ($role === 'admin'): ?>
            <a href="<?php echo $base; ?>admin/attendance/index.php">
                <span class="nav-icon">✅</span> Attendance
            </a>
            <a href="<?php echo $base; ?>admin/exams/index.php">
                <span class="nav-icon">📝</span> Examinations
            </a>

            <div class="nav-section-label">Reports</div>
            <a href="<?php echo $base; ?>admin/reports/students.php">
                <span class="nav-icon">📄</span> Student Report
            </a>
            <a href="<?php echo $base; ?>admin/reports/attendance.php">
                <span class="nav-icon">📋</span> Attendance Report
            </a>
            <a href="<?php echo $base; ?>admin/reports/results.php">
                <span class="nav-icon">🏆</span> Results Report
            </a>

        <?php else: ?>
            <a href="<?php echo $base; ?>teacher/attendance/mark.php">
                <span class="nav-icon">✅</span> Mark Attendance
            </a>
            <a href="<?php echo $base; ?>teacher/attendance/view.php">
                <span class="nav-icon">📋</span> View Attendance
            </a>
            <a href="<?php echo $base; ?>teacher/exams/marks.php">
                <span class="nav-icon">📝</span> Enter Marks
            </a>
            <a href="<?php echo $base; ?>teacher/exams/results.php">
                <span class="nav-icon">🏆</span> View Results
            </a>

            <div class="nav-section-label">Account</div>
            <a href="<?php echo $base; ?>teacher/change_password.php">
                <span class="nav-icon">🔑</span> Change Password
            </a>
        <?php endif; ?>

    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar">
                <?php echo strtoupper(substr(getCurrentUserName(), 0, 1)); ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars(getCurrentUserName()); ?></div>
                <div class="user-role"><?php echo getCurrentUserRole(); ?></div>
            </div>
        </div>
        <a href="<?php echo $base; ?>logout.php" class="logout-link">
            🚪 Logout
        </a>
    </div>

</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
