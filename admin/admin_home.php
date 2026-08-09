<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

requireAdmin();
$active_page = 'home';

/* ---------------------------------------------------------------------
 * FETCH ALL REGISTERED USER ACCOUNTS
 * ------------------------------------------------------------------- */
$user_records = [];
$fetch_stmt = $conn->prepare(
    "SELECT id, username, role, name, contact, address, created_at
     FROM users
     ORDER BY created_at ASC"
);

if ($fetch_stmt) {
    $fetch_stmt->execute();
    $user_records = $fetch_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $fetch_stmt->close();
}
?>
<!DOCTYPE html>
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

    <h2>Registered User Accounts</h2>
    <p>Accounts created through the admin user management page.</p>

    <?php if (empty($user_records)): ?>

        <p style="text-align:center; color:#555;">No user accounts yet.</p>

    <?php else: ?>

        <table class="user-records-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Created On</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($user_records as $u): ?>
                    <tr>
                        <td><?php echo (int) $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['role']); ?></td>
                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                        <td><?php echo htmlspecialchars($u['contact']); ?></td>
                        <td><?php echo htmlspecialchars($u['address']); ?></td>
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