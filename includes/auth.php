<?php
// includes/auth.php
// Session authentication helper - include this at the top of every protected page

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require the user to be logged in.
 * If not, redirect to login page.
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . getBasePath() . 'login.php');
        exit;
    }
}

/**
 * Require admin role.
 * If logged in but not admin, redirect to their dashboard.
 */
function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ' . getBasePath() . 'teacher/dashboard.php');
        exit;
    }
}

/**
 * Returns the base URL path to the project root.
 * Works regardless of which subfolder the current file is in.
 */
function getBasePath() {
    // Count how many directory levels deep we are from project root
    $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
    $rootPath   = str_replace('\\', '/', realpath(__DIR__ . '/..')) . '/';
    $depth = substr_count(str_replace($rootPath, '', $scriptPath), '/');
    return str_repeat('../', $depth);
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get current logged-in user's display name
 */
function getCurrentUserName() {
    return $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
}

/**
 * Get current user's role label
 */
function getCurrentUserRole() {
    return ucfirst($_SESSION['role'] ?? 'guest');
}
