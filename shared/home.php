<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

if ($_SESSION['role'] === 'admin') {
    header('Location: /waste-project/admin/admin_home.php');
} else {
    header('Location: /waste-project/resident/resident_home.php');
}
exit;
