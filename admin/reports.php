<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

requireAdmin();
$active_page = 'reports';

$summary = array(
    'pickup_total' => 0,
    'pickup_pending' => 0,
    'pickup_done' => 0,
    'complaint_total' => 0,
    'complaint_pending' => 0,
    'complaint_done' => 0,
);
$recent_pickups = array();
$recent_complaints = array();

$stmt = $conn->prepare('SELECT COUNT(*) AS total_pickups FROM pickup_requests');
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $summary['pickup_total'] = (int)$row['total_pickups'];
}
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) AS pending_pickups FROM pickup_requests WHERE status = ?');
$pending = 'pending';
$stmt->bind_param('s', $pending);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $summary['pickup_pending'] = (int)$row['pending_pickups'];
}
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) AS done_pickups FROM pickup_requests WHERE status = ?');
$done = 'done';
$stmt->bind_param('s', $done);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $summary['pickup_done'] = (int)$row['done_pickups'];
}
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) AS total_complaints FROM complaints');
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $summary['complaint_total'] = (int)$row['total_complaints'];
}
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) AS pending_complaints FROM complaints WHERE status = ?');
$pending = 'pending';
$stmt->bind_param('s', $pending);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $summary['complaint_pending'] = (int)$row['pending_complaints'];
}
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) AS done_complaints FROM complaints WHERE status = ?');
$done = 'done';
$stmt->bind_param('s', $done);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $summary['complaint_done'] = (int)$row['done_complaints'];
}
$stmt->close();

$stmt = $conn->prepare('SELECT pr.id, pr.pickup_type, pr.preferred_date, pr.status, u.username FROM pickup_requests pr JOIN users u ON u.id = pr.user_id ORDER BY pr.created_at DESC LIMIT 5');
$stmt->execute();
$pickupResult = $stmt->get_result();
while ($row = $pickupResult->fetch_assoc()) {
    $recent_pickups[] = $row;
}
$stmt->close();

$stmt = $conn->prepare('SELECT c.id, c.complaint_text, c.status, u.username FROM complaints c JOIN users u ON u.id = c.user_id ORDER BY c.created_at DESC LIMIT 5');
$stmt->execute();
$complaintResult = $stmt->get_result();
while ($row = $complaintResult->fetch_assoc()) {
    $recent_complaints[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Reports - CleanCity</title>
  <link rel="stylesheet" href="/waste-project/shared/style.css">
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/waste-project/shared/navbar.php'; ?>
<div class="page-content">
  <div class="card">
    <h2>Reports</h2>
    <p>Overview of pickup requests and complaints.</p>
  </div>
  <div class="card-white">
    <h3>Summary</h3>
    <p>Pickup requests: <?= (int)$summary['pickup_total'] ?> total, <?= (int)$summary['pickup_pending'] ?> pending, <?= (int)$summary['pickup_done'] ?> done.</p>
    <p>Complaints: <?= (int)$summary['complaint_total'] ?> total, <?= (int)$summary['complaint_pending'] ?> pending, <?= (int)$summary['complaint_done'] ?> done.</p>
  </div>
  <div class="card-white">
    <h3>Recent pickup requests</h3>
    <?php if ($recent_pickups): ?>
      <ul>
        <?php foreach ($recent_pickups as $pickup): ?>
          <li><?= htmlspecialchars($pickup['username']) ?> requested <?= htmlspecialchars($pickup['pickup_type']) ?> for <?= htmlspecialchars($pickup['preferred_date']) ?> — <?= htmlspecialchars($pickup['status']) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No pickup requests yet.</p>
    <?php endif; ?>
  </div>
  <div class="card-white">
    <h3>Recent complaints</h3>
    <?php if ($recent_complaints): ?>
      <ul>
        <?php foreach ($recent_complaints as $complaint): ?>
          <li><?= htmlspecialchars($complaint['username']) ?>: <?= htmlspecialchars($complaint['complaint_text']) ?> — <?= htmlspecialchars($complaint['status']) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No complaints yet.</p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
