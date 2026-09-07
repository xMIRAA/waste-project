<?php

require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . app_url('auth/login.php'));
    exit;
}

// Require an authenticated resident for resident-only workflows.
function requireResident() {
    if (($_SESSION['role'] ?? '') !== 'resident') {
        header("Location: " . app_url('admin/admin_home.php'));
        exit;
    }
}

function requireAdmin() {
    if ($_SESSION['role'] !== 'admin') {
        header("Location: " . app_url('shared/home.php'));
        exit;
    }
}
?>