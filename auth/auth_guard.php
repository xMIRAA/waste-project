<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /waste-project/auth/login.php");
    exit;
}

function requireAdmin() {
    if ($_SESSION['role'] !== 'admin') {
        header("Location: /waste-project/shared/home.php");
        exit;
    }
}
?>