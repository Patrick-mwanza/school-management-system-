<?php
// admin/students/delete.php — Delete a student record
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo = getDB();
$id  = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Get name for message
    $stmt = $pdo->prepare("SELECT full_name FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if ($student) {
        $del = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $del->execute([$id]);
        redirectWithMessage('index.php', "Student '{$student['full_name']}' deleted.", 'success');
    }
}

redirectWithMessage('index.php', 'Student not found.', 'danger');
