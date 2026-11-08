<?php
// admin/classes/delete.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo = getDB();
$id  = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Check if class has students
    $check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE class_id = ?");
    $check->execute([$id]);
    if ($check->fetchColumn() > 0) {
        redirectWithMessage('index.php', 'Cannot delete: this class has students assigned to it. Reassign them first.', 'danger');
    }

    $stmt = $pdo->prepare("SELECT class_name FROM classes WHERE id = ?");
    $stmt->execute([$id]);
    $class = $stmt->fetch();

    if ($class) {
        $pdo->prepare("DELETE FROM classes WHERE id = ?")->execute([$id]);
        redirectWithMessage('index.php', "Class '{$class['class_name']}' deleted.", 'success');
    }
}
redirectWithMessage('index.php', 'Class not found.', 'danger');
