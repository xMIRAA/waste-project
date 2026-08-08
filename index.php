<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /waste-project/shared/home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CleanCity Waste Collection</title>
  <link rel="stylesheet" href="/waste-project/shared/style.css">
</head>
<body>
  <div class="landing-page">
    <header class="landing-header">
      <div class="brand-block">CleanCity</div>
      <a href="/waste-project/auth/login.php" class="btn-primary">Login</a>
    </header>

    <main class="landing-hero">
      <section class="hero-copy">
        <p class="eyebrow">Cleaner streets, easier service</p>
        <h1>Keep your community clean with a smarter waste collection experience.</h1>
        <p>Residents can request pickups and report issues easily, while administrators manage schedules, accounts, and service operations from a simple and secure dashboard.</p>
        <div class="hero-actions">
          <a href="/waste-project/auth/login.php" class="btn-primary">Login</a>
          <a href="#features" class="btn-secondary">Explore features</a>
        </div>
      </section>

      <section class="hero-card">
        <h3>What you can do here</h3>
        <ul>
          <li>Request waste pickup quickly</li>
          <li>View collection schedules clearly</li>
          <li>Submit complaints in seconds</li>
          <li>Access the system securely</li>
        </ul>
      </section>
    </main>

    <section id="features" class="feature-grid">
      <article class="feature-card">
        <h4>Simple for residents</h4>
        <p>Access essential services without confusion or delays.</p>
      </article>
      <article class="feature-card">
        <h4>Powerful for admins</h4>
        <p>Manage users, schedules, and reports from one efficient workspace.</p>
      </article>
      <article class="feature-card">
        <h4>Modern and polished</h4>
        <p>Enjoy a bright, elegant interface designed to feel welcoming from the first glance.</p>
      </article>
    </section>
  </div>
</body>
</html>
