<?php

require_once __DIR__ . '/config.php';

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ' . app_url('shared/home.php'));
} else {
    header('Location: ' . app_url('auth/login.php'));
}
exit;
