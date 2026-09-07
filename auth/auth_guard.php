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

// Use one session token for every state-changing form.
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function valid_csrf_token() {
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function requireAdmin() {
    if ($_SESSION['role'] !== 'admin') {
        header("Location: " . app_url('shared/home.php'));
        exit;
    }
}
?>