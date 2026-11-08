<?php
// includes/functions.php
// Shared utility functions used across the system

/**
 * Sanitize output for safe HTML display
 */
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Calculate grade from marks
 */
function getGrade($marks) {
    if ($marks >= 80) return 'A';
    if ($marks >= 70) return 'B';
    if ($marks >= 60) return 'C';
    if ($marks >= 50) return 'D';
    return 'E';
}

/**
 * Get grade CSS class for color coding
 */
function getGradeClass($grade) {
    switch ($grade) {
        case 'A': return 'grade-a';
        case 'B': return 'grade-b';
        case 'C': return 'grade-c';
        case 'D': return 'grade-d';
        default:  return 'grade-e';
    }
}

/**
 * Format a date to a readable string
 */
function formatDate($date) {
    if (!$date) return 'N/A';
    return date('d M Y', strtotime($date));
}

/**
 * Redirect with a session flash message
 */
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type']    = $type;
    header("Location: $url");
    exit;
}

/**
 * Display and clear flash message if set
 */
function showFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $msg  = e($_SESSION['flash_message']);
        $type = e($_SESSION['flash_type'] ?? 'success');
        echo "<div class='alert alert-{$type}' id='flashMsg'>{$msg}</div>";
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    }
}

/**
 * Generate a unique admission number
 */
function generateAdmissionNumber($pdo) {
    $year  = date('Y');
    $stmt  = $pdo->query("SELECT COUNT(*) as cnt FROM students WHERE YEAR(created_at) = $year");
    $count = $stmt->fetch()['cnt'] + 1;
    return 'ADM' . $year . str_pad($count, 3, '0', STR_PAD_LEFT);
}

/**
 * Generate a unique staff number
 */
function generateStaffNumber($pdo) {
    $stmt  = $pdo->query("SELECT COUNT(*) as cnt FROM teachers");
    $count = $stmt->fetch()['cnt'] + 1;
    return 'TCH' . str_pad($count, 3, '0', STR_PAD_LEFT);
}
