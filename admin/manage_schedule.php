<?php
// ------------------------------------------------------
// manage_schedule.php
// Lets an admin add weekly pickup dates and display the
// current schedule for residents and operations teams.
// ------------------------------------------------------

require_once __DIR__ . '/../config.php';

// Protect this page so only logged-in admins can access it.
require_once app_path('auth/auth_guard.php');
// Load the database connection used for schedule queries and inserts.
require_once app_path('database/db.php');

// Only admins are allowed to create or edit the collection schedule.
requireAdmin();
$active_page = 'schedule';

$success_message = '';
$error_message = '';

/* Handle form submission for adding a new schedule */
// If the admin submits a new schedule entry, validate the fields and insert it.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup_date = trim($_POST['pickup_date'] ?? '');
    $waste_type  = trim($_POST['waste_type'] ?? '');
    $area        = trim($_POST['area'] ?? '');

    // Require each schedule field before inserting a new row.
    if (!empty($pickup_date) && !empty($waste_type) && !empty($area)) {
        // Insert the schedule row with the selected date, waste type, and service area.
        $stmt = $conn->prepare("INSERT INTO pickup_schedule (pickup_date, waste_type, area) VALUES (?, ?, ?)");
        // Use a prepared statement so the form values are bound safely instead of being placed directly into SQL.
        if ($stmt->execute([$pickup_date, $waste_type, $area])) {
            $success_message = "Pickup schedule added successfully.";
        } else {
            $error_message = "Error adding schedule. Please try again.";
        }
    } else {
        $error_message = "All fields are required.";
    }
}

/* Fetch existing schedules to list in the table */
// Read the saved schedule rows so the current week and future dates can be displayed.
$stmt = $conn->prepare("SELECT * FROM pickup_schedule ORDER BY pickup_date ASC");
$stmt->execute();
$schedules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Manage Schedule - CleanCity</title>
  <link rel="stylesheet" href="<?= app_url('shared/style.css') ?>">
  <link rel="stylesheet" href="<?= app_url('admin/css/manage-schedule.css') ?>">
</head>
<body>

<?php include app_path('shared/navbar.php'); ?>

<div class="page-content">

  <div class="udf-layout">

    <!-- Form Section -->
    <div class="form-card-wrapper">
      <div class="form-card">
          <h1 class="form-title">Add Schedule</h1>
          <p class="form-subtitle">Fill in the collection details below</p>

          <?php if (!empty($success_message)): ?>
              <div class="alert success-alert"><?php echo htmlspecialchars($success_message); ?></div>
          <?php endif; ?>

          <?php if (!empty($error_message)): ?>
              <div class="alert error-alert"><?php echo htmlspecialchars($error_message); ?></div>
          <?php endif; ?>

          <form action="manage_schedule.php" method="POST" class="waste-form">

              <div class="form-group">
                  <label for="pickup_date">Pickup Date</label>
                  <input type="date" id="pickup_date" name="pickup_date" required>
              </div>

              <div class="form-group">
                  <label for="waste_type">Waste Type</label>
                  <input type="text" id="waste_type" name="waste_type" placeholder="e.g. Organic, Recyclable" required>
              </div>

              <div class="form-group">
                  <label for="area">Area / Zone</label>
                  <input type="text" id="area" name="area" placeholder="e.g. Zone A, Downtown" required>
              </div>

              <button type="submit" class="btn-primary full">Add Schedule</button>

          </form>
      </div>
    </div>

    <!-- Table Section -->
    <div class="user-schedule-section">

        <h2>Weekly Pickup Schedule</h2>
        <p>Find your designated waste collection schedule below.</p>

        <table class="schedule-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Waste Type</th>
                    <th>Area / Zone</th>
                </tr>
            </thead>
            <tbody>

            <?php
            if (!empty($schedules)) {
                foreach ($schedules as $row) {
            ?>
                <tr>
                    <td><?php echo date('d M Y', strtotime($row['pickup_date'])); ?></td>
                    <td><?php echo htmlspecialchars($row['waste_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['area']); ?></td>
                </tr>
            <?php
                }
            } else {
            ?>
                <tr>
                    <td colspan="3" style="text-align:center;">
                        No pickup schedule available.
                    </td>
                </tr>
            <?php
            }
            ?>

            </tbody>
        </table>

    </div>

  </div>

</div>

</body>
</html>