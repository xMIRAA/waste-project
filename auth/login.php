<?php
// ------------------------------------------------------
// login.php
// Handles user login: checks the submitted username and
// password against the database, verifies the hash, and
// starts a session for the user's id, username, and role.
// ------------------------------------------------------

require_once __DIR__ . '/../config.php';

// Start the PHP session so user identity can be stored and checked later.
session_start();

// Load the shared database connection used for authentication queries.
require_once app_path('database/db.php');

// Store a login error message for display if the credentials are wrong.
$error = "";

// If this is a login form submission, verify the entered credentials.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim the username to avoid accidental spaces from user input.
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Look up the submitted username in the users table.
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    // Use a prepared statement so the user input is bound safely instead of being inserted directly into SQL.
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // If a matching user record exists, check the password against the stored hash.
    if ($row = $result->fetch_assoc()) {
        // Passwords are hashed, so we must verify the plain-text login password against the stored hash instead of comparing strings directly.
        if (password_verify($password, $row['password'])) {
            // Store the authenticated user's identity in the session so later pages can recognize them.
            $_SESSION['user_id']  = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role']     = $row['role'];
            // Send the user to the correct home page based on their role.
            header("Location: " . ($row['role'] === 'admin' ? app_url('admin/admin_home.php') : app_url('shared/home.php')));
            // Stop execution immediately so the redirect is not followed by any more page code.
            exit;
        } else {
            // If the password does not match, show a clear login error.
            $error = "Incorrect password.";
        }
    } else {
        // If no user with that username exists, show a clear login error.
        $error = "No account with that username.";
    }
    // Close the statement after the lookup is finished.
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Login - CleanCity</title>
  <link rel="stylesheet" href="<?= app_url('shared/style.css') ?>">
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <div class="login-head">
        <p class="brand">CleanCity waste collection</p>
        <p class="sub">Schedules, pickups and reports</p>
      </div>
      <div class="login-body">
        <?php if ($error): ?>
          <p class="error-text"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="<?= app_url('auth/login.php') ?>">
          <label>Username</label>
          <input type="text" name="username" placeholder="ucsc" required>
          <label>Password</label>
          <input type="password" name="password" placeholder="Enter password" required>
          <button type="submit" class="btn-primary full">Log in</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>