<?php
// ------------------------------------------------------
// config.php
// Centralizes the app root path and the public base URL so
// every page can reference the project in one place.
// ------------------------------------------------------

if (!defined('APP_ROOT')) {
    define('APP_ROOT', str_replace('\\', '/', __DIR__));
}

if (!defined('APP_BASE_URL')) {
    define('APP_BASE_URL', '/' . basename(APP_ROOT));
}

if (!function_exists('app_path')) {
    function app_path($relative_path = '') {
        $relative_path = ltrim($relative_path, '/');
        return APP_ROOT . ($relative_path === '' ? '' : '/' . $relative_path);
    }
}

if (!function_exists('app_url')) {
    function app_url($relative_path = '') {
        $relative_path = ltrim($relative_path, '/');
        return APP_BASE_URL . ($relative_path === '' ? '' : '/' . $relative_path);
    }
}
