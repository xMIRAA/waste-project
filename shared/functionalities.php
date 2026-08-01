<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Functionalities - CleanCity</title>
  <link rel="stylesheet" href="/waste-project/shared/style.css">
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/waste-project/shared/navbar.php'; ?>
  <div class="page-content">
    <header class="card-white" style="margin-bottom: 24px;">
      <h1>CleanCity Functionalities</h1>
      <p>CleanCity streamlines waste collection with intuitive resident tools and powerful administrative controls.</p>
    </header>

    <section class="card">
      <h2>Introduction</h2>
      <p>From viewing schedules to managing users and reporting issues, CleanCity gives every role the tools needed to keep waste collection running smoothly.</p>
    </section>

    <section class="card-white" style="display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-bottom: 24px;">
      <article class="card">
        <h3>Login & Logout</h3>
        <p>Secure authentication keeps resident and admin access separate, with a clear logout option when work is complete.</p>
      </article>
      <article class="card">
        <h3>Home Dashboard</h3>
        <p>Role-specific dashboards display the most important actions and status information for each user.</p>
      </article>
      <article class="card">
        <h3>Collection Schedule</h3>
        <p>Residents can quickly view upcoming pickups so they know when to prepare waste for collection.</p>
      </article>
      <article class="card">
        <h3>Request Pickup</h3>
        <p>Residents submit extra pickup requests and preferred dates for oversized or special waste items.</p>
      </article>
      <article class="card">
        <h3>Complaints</h3>
        <p>Issue reporting lets residents notify administrators about missed collections or service problems.</p>
      </article>
      <article class="card">
        <h3>Manage Users</h3>
        <p>Admin users can add, edit, and remove accounts, keeping user access up to date.</p>
      </article>
      <article class="card">
        <h3>Manage Schedule</h3>
        <p>Administrators maintain collection dates and make schedule adjustments from a single interface.</p>
      </article>
      <article class="card">
        <h3>Reports</h3>
        <p>Summary dashboards help administrators track system usage and prioritize workload.</p>
      </article>
    </section>

    <section class="card">
      <h2>Why Choose CleanCity?</h2>
      <ul>
        <li>Simple resident workflows for requests and issue reporting.</li>
        <li>Clear admin controls for managing schedules and users.</li>
        <li>Responsive design for desktop and mobile access.</li>
        <li>Built with plain PHP, HTML, CSS and vanilla JavaScript for easy maintenance.</li>
      </ul>
    </section>

    <section class="card-white" style="text-align: center;">
      <a href="/waste-project/shared/help.php" class="btn-primary">Visit Help Center</a>
    </section>

    <footer class="card" style="text-align: center; margin-top: 24px;">
      <p>&copy; <?= date('Y') ?> CleanCity Waste Collection Management System</p>
    </footer>
  </div>
</body>
</html>
