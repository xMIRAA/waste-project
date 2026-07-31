<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

$active_page = 'pickup';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickupType = isset($_POST['pickup_type']) ? trim($_POST['pickup_type']) : '';
    $preferredDate = isset($_POST['preferred_date']) ? trim($_POST['preferred_date']) : '';

    if (!in_array($pickupType, array('general', 'recycling', 'bulk', 'hazardous'), true)) {
        $error = 'Please select a valid pickup type.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $preferredDate)) {
        $error = 'Please choose a preferred date.';
    } else {
        $stmt = $conn->prepare('INSERT INTO pickup_requests (user_id, pickup_type, preferred_date, status) VALUES (?, ?, ?, "pending")');
        $stmt->bind_param('iss', $_SESSION['user_id'], $pickupType, $preferredDate);
        $stmt->execute();
        $stmt->close();

        $message = 'Pickup request submitted successfully.';
    }
}
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
  <div class="card-white">
    <?php if ($error): ?>
      <p class="error-text"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($message): ?>
      <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="POST">
      <label for="pickup_type">Pickup type</label>
      <select id="pickup_type" name="pickup_type" required>
        <option value="">Select one</option>
        <option value="general">General waste</option>
        <option value="recycling">Recycling</option>
        <option value="bulk">Bulk waste</option>
        <option value="hazardous">Hazardous waste</option>
      </select>

      <label for="preferred_date">Preferred date</label>
      <input type="date" id="preferred_date" name="preferred_date" required>

      <button type="submit" class="btn-primary">Submit request</button>
    </form>
  </div>
</div>
</body>
</html>
