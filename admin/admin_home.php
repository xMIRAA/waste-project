<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

requireAdmin();
$active_page = 'home';

/* ---------------------------------------------------------------------
 * FETCH ALL ADD_USERS RECORDS
 * ------------------------------------------------------------------- */
$user_records = [];
$fetch_stmt = $conn->prepare(
    "SELECT id, full_name, email, phone, address, preferred_days, entry_date, created_at
     FROM add_users
     ORDER BY created_at ASC"
);

if ($fetch_stmt) {
    $fetch_stmt->execute();
    $user_records = $fetch_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $fetch_stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Admin Home - CleanCity</title>
  <link rel="stylesheet" href="/waste-project/shared/style.css">
  <link rel="stylesheet" href="/waste-project/admin/css/admin.css">
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/waste-project/shared/navbar.php'; ?>
<div class="page-content">

  <div class="card">
    <h2>Administrator dashboard</h2>
    <p>Manage users, schedules, and reports from here.</p>
  </div>

  <!-- Quick links -->
  <div class="admin-links">

    <a href="/waste-project/admin/manage_users.php" class="admin-link-card">
      <div class="admin-link-icon">👤</div>
      <div class="admin-link-title">Users</div>
      <div class="admin-link-desc">View and manage resident accounts and details.</div>
    </a>

    <a href="/waste-project/admin/manage_schedule.php" class="admin-link-card">
      <div class="admin-link-icon">📅</div>
      <div class="admin-link-title">Schedule</div>
      <div class="admin-link-desc">View and manage upcoming pickup schedules.</div>
    </a>

    <a href="/waste-project/admin/pickup_request.php" class="admin-link-card">
      <div class="admin-link-icon">📬</div>
      <div class="admin-link-title">Pickup Requests</div>
      <div class="admin-link-desc">Review and manage pickup requests.</div>
    </a> 


    <a href="/waste-project/admin/reports.php" class="admin-link-card">
      <div class="admin-link-icon">📋</div>
      <div class="admin-link-title">Reports</div>
      <div class="admin-link-desc">Review and update resident complaints.</div>
    </a>

  </div>

  <!-- User details table -->
  <div class="card-white user-records-card">

    <h2>User Details</h2>
    <p>Records submitted through the Add User Details form.</p>

    <?php if (empty($user_records)): ?>

        <p style="text-align:center; color:#555;">No user records yet.</p>

    <?php else: ?>

        <table class="user-records-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Preferred Days</th>
                    <th>Entry Date</th>
                    <th>Added On</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($user_records as $u): ?>
                    <tr>
                        <td><?php echo (int) $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['phone']); ?></td>
                        <td><?php echo htmlspecialchars($u['address']); ?></td>
                        <td><?php echo htmlspecialchars($u['preferred_days'] ?? '—'); ?></td>
                        <td><?php echo date('d M Y', strtotime($u['entry_date'])); ?></td>
                        <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

  </div>

</div>
</body>
</html>