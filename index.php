<?php
// index.php - Entry point: redirect to appropriate page based on session
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: dashboard.php');
    } else {
        header('Location: teacher/dashboard.php');
    }
} else {
    header('Location: login.php');
}
exit;
