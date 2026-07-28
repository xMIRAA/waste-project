<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id']  = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role']     = $row['role'];
            header("Location: " . ($row['role'] === 'admin' ? "/waste-project/admin/admin_home.php" : "/waste-project/shared/home.php"));
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No account with that username.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Login - CleanCity</title>
  <link rel="stylesheet" href="/waste-project/shared/style.css">
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
        <form method="POST" action="login.php">
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