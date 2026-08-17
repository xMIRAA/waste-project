<?php
// ------------------------------------------------------
// help.php
// Shows quick support guidance so users can understand how
// to navigate and use the waste collection system.
// ------------------------------------------------------

require_once __DIR__ . '/../config.php';

// Protect the page so only logged-in users can access help content.
require_once app_path('auth/auth_guard.php');
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Help - CleanCity</title>
  <link rel="stylesheet" href="<?= app_url('shared/style.css') ?>">
</head>
<body>
<?php include app_path('shared/navbar.php'); ?>
  <div class="page-content">
    <header class="card-white" style="margin-bottom: 24px;">
      <h1>Help Center</h1>
      <p>Find quick answers, step-by-step guidance, and support details to use CleanCity with confidence.</p>
    </header>

    <section class="card">
      <h2>Quick Help</h2>
      <div style="display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="card-white">
          <h3>Accessing the system</h3>
          <p>Use your username and password to sign in. Log out when finished to protect your account.</p>
        </div>
        <div class="card-white">
          <h3>Finding pages</h3>
          <p>The navbar shows links based on your role. Admin-only pages are hidden for residents.</p>
        </div>
        <div class="card-white">
          <h3>Submitting requests</h3>
          <p>Use the Request Pickup page to send extra pickup requests and choose a preferred date.</p>
        </div>
        <div class="card-white">
          <h3>Reporting issues</h3>
          <p>Submit complaints when a collection is missed or if there is a service problem in your area.</p>
        </div>
      </div>
    </section>

    <section class="card-white" style="margin-top: 24px;">
      <h2>How to Use the System</h2>
      <ol>
        <li>Log in with your credentials.</li>
        <li>Use the navbar to access your dashboard or resident pages.</li>
        <li>Residents can view schedules, request pickups, and file complaints.</li>
        <li>Admins can manage users, schedules, and view reports.</li>
      </ol>
    </section>

    <section class="card">
      <h2>Frequently Asked Questions</h2>
      <dl>
      
        <dt>What should I do if a collection is missed?</dt>
        <dd>Submit a complaint through the Complaints page and include details about the missed service.</dd>
        <dt>Can residents access admin pages?</dt>
        <dd>No. Admin pages are restricted by role and redirected if a resident attempts to visit them.</dd>
        <dt>How do I request a special pickup?</dt>
        <dd>Open the Request Pickup page, select the type of pickup, and choose a preferred date.</dd>
      </dl>
    </section>

    <section class="card-white">
      <h2>Quick Tips</h2>
      <ul>
        <li>Keep your login details private and log out after using the system.</li>
        <li>Use the Home Dashboard for the fastest access to core features.</li>
        
     
      </ul>
    </section>

    <section class="card" style="text-align: center;">
      <h2>Contact Support</h2>
      <p>If you need more help, email support@cleancity.example or contact your local waste management office.</p>
    </section>

   
  </div>
</body>
</html>
