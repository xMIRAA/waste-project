<?php
// ------------------------------------------------------
// home.php
// Redirects the logged-in user to the correct dashboard based
// on their role, sending admins to the admin page and residents
// to the resident home screen.
// ------------------------------------------------------

require_once __DIR__ . '/../config.php';

// Protect this page so only logged-in users can reach dashboard routing.
require_once app_path('auth/auth_guard.php');
// Load the DB connection so the role can be checked against session data if needed.
require_once app_path('database/db.php');

// If the current user is an admin, send them to the admin dashboard.
if ($_SESSION['role'] === 'admin') {
    header('Location: ' . app_url('admin/admin_home.php'));
} else {
    // Otherwise, direct them to the resident dashboard.
    header('Location: ' . app_url('resident/resident_home.php'));
}
// Stop immediately after redirect so the page does not continue rendering protected content.
exit;
