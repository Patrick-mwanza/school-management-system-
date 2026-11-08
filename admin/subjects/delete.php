<?php
// admin/subjects/delete.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pdo = getDB();
$id  = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT subject_name FROM subjects WHERE id = ?");
    $stmt->execute([$id]);
    $subject = $stmt->fetch();
    if ($subject) {
        $pdo->prepare("DELETE FROM subjects WHERE id = ?")->execute([$id]);
        redirectWithMessage('index.php', "Subject '{$subject['subject_name']}' deleted.", 'success');
    }
}
redirectWithMessage('index.php', 'Subject not found.', 'danger');
