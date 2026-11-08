<?php
// admin/exams/delete.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo = getDB();
$id  = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT exam_name FROM examinations WHERE id = ?");
    $stmt->execute([$id]);
    $exam = $stmt->fetch();
    if ($exam) {
        $pdo->prepare("DELETE FROM examinations WHERE id = ?")->execute([$id]);
        redirectWithMessage('index.php', "Examination '{$exam['exam_name']}' and all its results deleted.", 'success');
    }
}
redirectWithMessage('index.php', 'Examination not found.', 'danger');
