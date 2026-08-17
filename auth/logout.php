<?php
// ------------------------------------------------------
// logout.php
// Ends the current session and sends the user back to the
// login page so the next request is treated as logged out.
// ------------------------------------------------------

require_once __DIR__ . '/../config.php';

// Start the session so the current user data can be cleared.
session_start();

// Destroy the session to remove the logged-in user identity.
session_destroy();

// Redirect back to the login page after logout.
header("Location: " . app_url('auth/login.php'));
// Stop execution immediately so no extra page code runs after the redirect.
exit;
?>
