<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

requireAdmin();
$active_page = 'home';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Admin Home - CleanCity</title>
  <link rel="stylesheet" href="/waste-project/shared/style.css">
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/waste-project/shared/navbar.php'; ?>
<div class="page-content">
  <div class="card">
    <h2>Administrator dashboard</h2>
    <p>Manage users, schedules, and reports from here.</p>
  </div>
</div>
</body>
</html>
