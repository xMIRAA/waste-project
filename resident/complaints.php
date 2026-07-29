<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

$active_page = 'complaints';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Complaints - CleanCity</title>
  <link rel="stylesheet" href="/waste-project/shared/style.css">
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/waste-project/shared/navbar.php'; ?>
<div class="page-content">
  <div class="card">
    <h2>Submit a complaint</h2>
    <p>Report service issues here.</p>
  </div>
</div>
</body>
</html>
