<div class="navbar">
  <div class="navbar-brand">CleanCity</div>
  <div class="navbar-links">
    <a href="/waste-project/shared/home.php">Home</a>
    <a href="/waste-project/resident/schedule.php">Schedule</a>
    <a href="/waste-project/resident/request_pickup.php">Request pickup</a>
    <a href="/waste-project/resident/complaints.php">Complaints</a>
    <?php if ($_SESSION['role'] === 'admin'): ?>
      <a href="/waste-project/admin/manage_users.php">Manage users</a>
      <a href="/waste-project/admin/manage_schedule.php">Manage schedule</a>
      <a href="/waste-project/admin/reports.php">Reports</a>
    <?php endif; ?>
    <a href="/waste-project/shared/functionalities.php">Functionalities</a>
    <a href="/waste-project/shared/help.php">Help</a>
  </div>
  <div class="navbar-user">
    <?php if ($_SESSION['role'] === 'admin'): ?><span class="badge-admin">Admin</span><?php endif; ?>
    <span><?= htmlspecialchars($_SESSION['username']) ?></span>
    <a href="/waste-project/auth/logout.php" class="logout">Logout</a>
  </div>
</div>