<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

requireAdmin();
$active_page = 'schedule';

$success_message = '';
$error_message = '';

/* Handle form submission for adding a new schedule */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup_date = trim($_POST['pickup_date'] ?? '');
    $waste_type  = trim($_POST['waste_type'] ?? '');
    $area        = trim($_POST['area'] ?? '');

    if (!empty($pickup_date) && !empty($waste_type) && !empty($area)) {
        $stmt = $conn->prepare("INSERT INTO pickup_schedule (pickup_date, waste_type, area) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $pickup_date, $waste_type, $area);

        if ($stmt->execute()) {
            $success_message = "Pickup schedule added successfully.";
        } else {
            $error_message = "Error adding schedule. Please try again.";
        }
        $stmt->close();
    } else {
        $error_message = "All fields are required.";
    }
}

/* Fetch existing schedules to list in the table */
$stmt = $conn->prepare("SELECT * FROM pickup_schedule ORDER BY pickup_date ASC");
$stmt->execute();
$schedules = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Manage Schedule - CleanCity</title>
  <link rel="stylesheet" href="/waste-project/shared/style.css">
  <link rel="stylesheet" href="/waste-project/admin/css/manage-schedule.css">
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/waste-project/shared/navbar.php'; ?>

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
            if ($schedules->num_rows > 0) {
                while ($row = $schedules->fetch_assoc()) {
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
            $stmt->close();
            ?>

            </tbody>
        </table>

    </div>

  </div>

</div>

</body>
</html>