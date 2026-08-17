<?php
// ------------------------------------------------------
// auth_guard.php
// Protects pages by checking whether a user is logged in
// before they can access protected content.
// ------------------------------------------------------

require_once __DIR__ . '/../config.php';

// Start a session only if one is not already active so page checks can read user data reliably.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If no logged-in user exists, redirect them to the login page before the page loads.
if (!isset($_SESSION['user_id'])) {
    header("Location: " . app_url('auth/login.php'));
    // Stop immediately after redirect so no protected content is displayed.
    exit;
}

// Redirect non-admin users away from admin-only pages.
function requireAdmin() {
    // Only an admin role should be allowed into admin screens.
    if ($_SESSION['role'] !== 'admin') {
        header("Location: " . app_url('shared/home.php'));
        // Stop execution so the redirect is the last action on this page.
        exit;
    }
}
?>