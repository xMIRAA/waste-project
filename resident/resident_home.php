<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

$active_page = 'home';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Resident';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Resident Home - CleanCity</title>
  <link rel="stylesheet" href="/waste-project/shared/style.css">
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/waste-project/shared/navbar.php'; ?>
<div class="page-content">
  <div class="card">
    <h2>Welcome back, <?= htmlspecialchars($username) ?>!</h2>
    <p>Your waste collection dashboard is ready.</p>
  </div>
</div>
</body>
</html>
