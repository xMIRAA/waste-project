<?php

require_once __DIR__ . '/../config.php';

require_once app_path('auth/auth_guard.php');
require_once app_path('database/db.php');

requireAdmin();
$active_page = 'schedule';

$success_message = '';
$error_message = '';
$edit_schedule = null;

if (!empty($_SESSION['schedule_message'])) {
    $success_message = $_SESSION['schedule_message'];
    unset($_SESSION['schedule_message']);
}
if (!empty($_SESSION['schedule_error'])) {
    $error_message = $_SESSION['schedule_error'];
    unset($_SESSION['schedule_error']);
}

/* Handle schedule CRUD form submissions */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action      = $_POST['action'] ?? 'create';
    $schedule_id = (int) ($_POST['schedule_id'] ?? 0);
    $pickup_date = trim($_POST['pickup_date'] ?? '');
    $waste_type  = trim($_POST['waste_type'] ?? '');
    $area        = trim($_POST['area'] ?? '');

    if ($action === 'delete' && $schedule_id > 0) {
        $stmt = $conn->prepare("DELETE FROM pickup_schedule WHERE id = ?");
        $stmt->bind_param("i", $schedule_id);
        if ($stmt->execute() && $stmt->affected_rows === 1) {
            $_SESSION['schedule_message'] = "Pickup schedule deleted successfully.";
        } else {
            $_SESSION['schedule_error'] = "Schedule not found or could not be deleted.";
        }
    } elseif ($action === 'create' || $action === 'update') {
        $date_obj = DateTime::createFromFormat('Y-m-d', $pickup_date);
        $valid_date = $date_obj && $date_obj->format('Y-m-d') === $pickup_date;

        if (!$valid_date || $waste_type === '' || $area === '') {
            $_SESSION['schedule_error'] = "A valid date, waste type, and area are required.";
        } elseif (strlen($waste_type) > 50 || strlen($area) > 100) {
            $_SESSION['schedule_error'] = "Waste type or area is too long.";
        } elseif ($action === 'update' && $schedule_id > 0) {
            $stmt = $conn->prepare("UPDATE pickup_schedule SET pickup_date = ?, waste_type = ?, area = ? WHERE id = ?");
            $stmt->bind_param("sssi", $pickup_date, $waste_type, $area, $schedule_id);
            if ($stmt->execute()) {
                $_SESSION['schedule_message'] = "Pickup schedule updated successfully.";
            } else {
                $_SESSION['schedule_error'] = "Error updating schedule. Please try again.";
            }
        } elseif ($action === 'create') {
            $stmt = $conn->prepare("INSERT INTO pickup_schedule (pickup_date, waste_type, area) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $pickup_date, $waste_type, $area);
            if ($stmt->execute()) {
                $_SESSION['schedule_message'] = "Pickup schedule added successfully.";
            } else {
                $_SESSION['schedule_error'] = "Error adding schedule. Please try again.";
            }
        } else {
            $_SESSION['schedule_error'] = "Invalid schedule selected.";
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_schedule'])) {
    $edit_schedule_id = (int) $_GET['edit_schedule'];
    if ($edit_schedule_id > 0) {
        $edit_stmt = $conn->prepare("SELECT id, pickup_date, waste_type, area FROM pickup_schedule WHERE id = ?");
        $edit_stmt->bind_param("i", $edit_schedule_id);
        $edit_stmt->execute();
        $edit_schedule = $edit_stmt->get_result()->fetch_assoc() ?: null;
    }
}

// Load schedules for the admin table.
$stmt = $conn->prepare("SELECT * FROM pickup_schedule ORDER BY pickup_date ASC");
$stmt->execute();
$schedules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
          <h1 class="form-title"><?php echo $edit_schedule ? 'Edit Schedule' : 'Add Schedule'; ?></h1>
          <p class="form-subtitle">Fill in the collection details below</p>

          <?php if (!empty($success_message)): ?>
              <div class="alert success-alert"><?php echo htmlspecialchars($success_message); ?></div>
          <?php endif; ?>

          <?php if (!empty($error_message)): ?>
              <div class="alert error-alert"><?php echo htmlspecialchars($error_message); ?></div>
          <?php endif; ?>

          <form action="manage_schedule.php" method="POST" class="waste-form">
              <input type="hidden" name="action" value="<?php echo $edit_schedule ? 'update' : 'create'; ?>">
              <?php if ($edit_schedule): ?>
                  <input type="hidden" name="schedule_id" value="<?php echo (int) $edit_schedule['id']; ?>">
              <?php endif; ?>

              <div class="form-group">
                  <label for="pickup_date">Pickup Date</label>
                  <input type="date" id="pickup_date" name="pickup_date" value="<?php echo htmlspecialchars($edit_schedule['pickup_date'] ?? ''); ?>" required>
              </div>

              <div class="form-group">
                  <label for="waste_type">Waste Type</label>
                  <input type="text" id="waste_type" name="waste_type" value="<?php echo htmlspecialchars($edit_schedule['waste_type'] ?? ''); ?>" placeholder="e.g. Organic, Recyclable" maxlength="50" required>
              </div>

              <div class="form-group">
                  <label for="area">Area / Zone</label>
                  <input type="text" id="area" name="area" value="<?php echo htmlspecialchars($edit_schedule['area'] ?? ''); ?>" placeholder="e.g. Zone A, Downtown" maxlength="100" required>
              </div>

              <button type="submit" class="btn-primary full"><?php echo $edit_schedule ? 'Save Changes' : 'Add Schedule'; ?></button>
              <?php if ($edit_schedule): ?>
                  <a href="manage_schedule.php" class="cancel-edit-button">Cancel</a>
              <?php endif; ?>

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
                    <th>Actions</th>
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
                    <td class="schedule-actions">
                        <a href="?edit_schedule=<?php echo (int) $row['id']; ?>" class="edit-schedule-button">Edit</a>
                        <form action="manage_schedule.php" method="POST" class="delete-form" onsubmit="return confirm('Delete this pickup schedule?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="schedule_id" value="<?php echo (int) $row['id']; ?>">
                            <button type="submit" class="delete-schedule-button">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php
                }
            } else {
            ?>
                <tr>
                    <td colspan="4" style="text-align:center;">
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