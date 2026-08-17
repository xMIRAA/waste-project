<?php
// ------------------------------------------------------
// navbar.php
// Displays the top navigation and swaps links based on
// whether the current user is an admin or a resident.
// ------------------------------------------------------
require_once __DIR__ . '/../config.php';
?>
<div class="navbar">
  <div class="navbar-brand">CleanCity</div>
  <div class="navbar-links">

    <?php // Show admin-only pages when the current session role is admin. ?>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
      <!-- Admin-specific links -->
      <a href="<?= app_url('shared/home.php') ?>">Home</a>
      <a href="<?= app_url('admin/manage_users.php') ?>">Manage users</a>
      <a href="<?= app_url('admin/manage_schedule.php') ?>">Manage schedule</a>
      <a href="<?= app_url('admin/pickup_request.php') ?>">Pickup Requests</a>
      <a href="<?= app_url('admin/reports.php') ?>">Reports</a>
      <a href="<?= app_url('shared/functionalities.php') ?>">Functionalities</a>
      <a href="<?= app_url('shared/help.php') ?>">Help</a>
    <?php else: ?>
      <!-- Resident / Default user links -->
      <a href="<?= app_url('shared/home.php') ?>">Home</a>
      <a href="<?= app_url('resident/schedule.php') ?>">Schedule</a>
      <a href="<?= app_url('resident/request_pickup.php') ?>">Request pickup</a>
      <a href="<?= app_url('resident/complaints.php') ?>">Complaints</a>
      <a href="<?= app_url('shared/functionalities.php') ?>">Functionalities</a>
      <a href="<?= app_url('shared/help.php') ?>">Help</a>
    <?php endif; ?>

  </div>
  <div class="navbar-user">
    <?php // Show the admin badge only for admin users. ?>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
      <span class="badge-admin">Admin</span>
    <?php endif; ?>
    <span><?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
    <a href="<?= app_url('auth/logout.php') ?>" class="logout">Logout</a>
  </div>
</div>