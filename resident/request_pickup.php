<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

$active_page = 'pickup';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Request Pickup - CleanCity</title>
  <link rel="stylesheet" href="/waste-project/shared/style.css">
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/waste-project/shared/navbar.php'; ?>
<div class="page-content">
  <div class="card">
    <h2>Request a pickup</h2>
    <p>Use this form to request a special pickup.</p>
  </div>
</div>
</body>
</html>
