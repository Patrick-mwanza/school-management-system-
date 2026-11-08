<?php
// admin/teachers/delete.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo = getDB();
$id  = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT full_name FROM teachers WHERE id = ?");
    $stmt->execute([$id]);
    $teacher = $stmt->fetch();

    if ($teacher) {
        // Delete associated user account
        $pdo->prepare("DELETE FROM users WHERE teacher_id = ?")->execute([$id]);
        // Delete teacher
        $pdo->prepare("DELETE FROM teachers WHERE id = ?")->execute([$id]);
        redirectWithMessage('index.php', "Teacher '{$teacher['full_name']}' and their login account deleted.", 'success');
    }
}
redirectWithMessage('index.php', 'Teacher not found.', 'danger');
