<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

$active_page = 'complaints';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $complaintText = isset($_POST['complaint_text']) ? trim($_POST['complaint_text']) : '';

    if (strlen($complaintText) < 5) {
        $error = 'Please describe the issue in a bit more detail.';
    } else {
        $stmt = $conn->prepare('INSERT INTO complaints (user_id, complaint_text, status) VALUES (?, ?, "pending")');
        $stmt->bind_param('is', $_SESSION['user_id'], $complaintText);
        $stmt->execute();
        $stmt->close();

        $message = 'Complaint submitted successfully.';
    }
}
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
  <div class="card-white">
    <?php if ($error): ?>
      <p class="error-text"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($message): ?>
      <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="POST">
      <label for="complaint_text">Description</label>
      <textarea id="complaint_text" name="complaint_text" rows="6" required></textarea>
      <button type="submit" class="btn-primary">Submit complaint</button>
    </form>
  </div>
</div>
</body>
</html>
