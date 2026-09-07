<?php

require_once __DIR__ . '/../config.php';

require_once app_path('auth/auth_guard.php');
require_once app_path('database/db.php');
requireResident();

$active_page = 'request_pickup';

$message   = '';
$error     = '';
$edit_request = null;

// These values must match the form options and database workflow.
$allowed_waste_types = ['General Waste', 'Recyclables', 'Garden Waste', 'E-Waste'];
$allowed_time_slots  = ['Morning (8 AM - 12 PM)', 'Afternoon (12 PM - 4 PM)', 'Evening (4 PM - 7 PM)'];

// Read one-time feedback after a POST/redirect cycle.
if (!empty($_SESSION['pickup_message'])) {
    $message = $_SESSION['pickup_message'];
    unset($_SESSION['pickup_message']);
}

/* Handle pickup request CRUD submissions */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id     = (int) $_SESSION['user_id'];
    $action      = $_POST['action'] ?? 'create';
    $request_id  = (int) ($_POST['request_id'] ?? 0);

    // Use defaults so malformed requests fail validation cleanly.
    $waste_type  = isset($_POST['waste_type']) ? trim($_POST['waste_type']) : '';
    $pickup_date = isset($_POST['pickup_date']) ? trim($_POST['pickup_date']) : '';
    $time_slot   = isset($_POST['time_slot']) ? trim($_POST['time_slot']) : '';
    $notes       = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    if ($action === 'delete' && $request_id > 0) {
        // DELETE: remove only the resident's pending request.
        $delete_stmt = $conn->prepare("DELETE FROM pickup_requests WHERE id = ? AND user_id = ? AND states = 'pending'");
        $delete_stmt->bind_param("ii", $request_id, $user_id);
        if ($delete_stmt->execute() && $delete_stmt->affected_rows === 1) {
            $_SESSION['pickup_message'] = "Pickup request deleted successfully.";
        } else {
            $_SESSION['pickup_error'] = "Only your pending pickup requests can be deleted.";
        }
    } elseif (!in_array($waste_type, $allowed_waste_types, true)) {
        $_SESSION['pickup_error'] = "Please select a valid waste type.";
    } elseif (!in_array($time_slot, $allowed_time_slots, true)) {
        $_SESSION['pickup_error'] = "Please select a valid time slot.";
    } else {
        // Pickup dates must be real dates at least one day ahead.
        $date_obj = DateTime::createFromFormat('Y-m-d', $pickup_date);
        $today    = new DateTime('today');

        if (!$date_obj || $date_obj->format('Y-m-d') !== $pickup_date) {
            $_SESSION['pickup_error'] = "Please provide a valid pickup date.";
        } else {
            $min_date = (clone $today)->modify('+1 day');
            if ($date_obj < $min_date) {
                $_SESSION['pickup_error'] = "Pickup requests must be made at least 24 hours in advance.";
            } else {
                // Keep one request per resident and date.
                $check = $conn->prepare(
                    "SELECT COUNT(*) AS cnt FROM pickup_requests
                    WHERE user_id = ? AND pickup_date = ? AND id <> ?"
                );
                $check->bind_param("isi", $user_id, $pickup_date, $request_id);
                $check->execute();
                $count_row = $check->get_result()->fetch_assoc();

                if ($count_row['cnt'] > 0) {
                    $_SESSION['pickup_error'] = "You already have a pickup request for that date. Only one request per day is allowed.";
                } else {
                    if (strlen($notes) > 10000) {
                        $_SESSION['pickup_error'] = "Notes are too long.";
                    } elseif ($action === 'update' && $request_id > 0) {
                        // READ: confirm that the resident owns a pending request.
                        $exists_stmt = $conn->prepare("SELECT id FROM pickup_requests WHERE id = ? AND user_id = ? AND states = 'pending'");
                        $exists_stmt->bind_param("ii", $request_id, $user_id);
                        $exists_stmt->execute();

                        if (!$exists_stmt->get_result()->fetch_assoc()) {
                            $_SESSION['pickup_error'] = "Only your pending pickup requests can be updated.";
                        } else {
                            // UPDATE: change the resident's pending request.
                        $update_stmt = $conn->prepare(
                            "UPDATE pickup_requests SET waste_type = ?, pickup_date = ?, time_slot = ?, notes = ?
                             WHERE id = ? AND user_id = ? AND states = 'pending'"
                        );
                        $update_stmt->bind_param("ssssii", $waste_type, $pickup_date, $time_slot, $notes, $request_id, $user_id);
                        if ($update_stmt->execute()) {
                            $_SESSION['pickup_message'] = "Pickup request updated successfully!";
                        } else {
                            $_SESSION['pickup_error'] = "Error updating pickup request.";
                        }
                        }
                    } elseif ($action === 'create') {
                        // CREATE: submit a new pickup request.
                        $insert_stmt = $conn->prepare(
                            "INSERT INTO pickup_requests (user_id, waste_type, pickup_date, time_slot, notes)
                             VALUES (?, ?, ?, ?, ?)"
                        );
                        $insert_stmt->bind_param("issss", $user_id, $waste_type, $pickup_date, $time_slot, $notes);
                        if ($insert_stmt->execute()) {
                            $_SESSION['pickup_message'] = "Pickup request submitted successfully!";
                        } else {
                            $_SESSION['pickup_message'] = "Error submitting request. Please try again.";
                        }
                    } else {
                        $_SESSION['pickup_error'] = "Invalid pickup request action.";
                    }
                }
            }
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (!empty($_SESSION['pickup_error'])) {
    $error = $_SESSION['pickup_error'];
    unset($_SESSION['pickup_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_request'])) {
    $edit_id = (int) $_GET['edit_request'];
    $edit_stmt = $conn->prepare("SELECT id, waste_type, pickup_date, time_slot, notes FROM pickup_requests WHERE id = ? AND user_id = ? AND states = 'pending'");
    $edit_stmt->bind_param("ii", $edit_id, $_SESSION['user_id']);
    $edit_stmt->execute();
    $edit_request = $edit_stmt->get_result()->fetch_assoc() ?: null;
}

// READ: load only the current resident's requests.
$user_id = $_SESSION['user_id'];

$select_stmt = $conn->prepare(
    "SELECT * FROM pickup_requests
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$select_stmt->bind_param("i", $user_id);
$select_stmt->execute();
$pickup_requests = $select_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Pickup - CleanCity</title>

    <link rel="stylesheet" href="<?= app_url('shared/style.css') ?>">
    <link rel="stylesheet" href="<?= app_url('resident/resident-css/pickup.css') ?>">
</head>

<body>

<?php include app_path('shared/navbar.php'); ?>

<div class="page-content">

    <center><h1><?php echo $edit_request ? 'Edit Pickup Request' : 'Request Pickup'; ?></h1>
    <?php if (!empty($message)) { ?>
    <p style="color:green;font-weight:bold;">
        <?php echo htmlspecialchars($message); ?>
    </p>
    <?php } ?>
    <?php if (!empty($error)) { ?>
    <p style="color:#993c1d;font-weight:bold;">
        <?php echo htmlspecialchars($error); ?>
    </p>
    <?php } ?>
    <p class="page-description">
        Need an additional waste collection? Submit your pickup request below.
    </p></center>

    <div class="request-container">

        <!-- Pickup Form -->
        <div class="card-white form-card">

            <h2>Pickup Request Form</h2>

            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $edit_request ? 'update' : 'create'; ?>">
                <?php if ($edit_request): ?>
                    <input type="hidden" name="request_id" value="<?php echo (int) $edit_request['id']; ?>">
                <?php endif; ?>

                <label>Waste Type</label>

                <select name="waste_type" required>
                    <option value="">Select Waste Type</option>
                    <?php foreach ($allowed_waste_types as $waste_option): ?>
                        <option <?php echo ($edit_request['waste_type'] ?? '') === $waste_option ? 'selected' : ''; ?>><?php echo htmlspecialchars($waste_option); ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Preferred Pickup Date</label>

                <input type="date" name="pickup_date" value="<?php echo htmlspecialchars($edit_request['pickup_date'] ?? ''); ?>" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">

                <label>Preferred Time</label>

                <select name="time_slot" required>
                    <option value="">Select Time</option>
                    <?php foreach ($allowed_time_slots as $time_option): ?>
                        <option <?php echo ($edit_request['time_slot'] ?? '') === $time_option ? 'selected' : ''; ?>><?php echo htmlspecialchars($time_option); ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Additional Notes</label>

                <textarea
                    name="notes"
                    rows="5"
                    maxlength="10000"
                    placeholder="Enter additional information (optional)"
                ><?php echo htmlspecialchars($edit_request['notes'] ?? ''); ?></textarea>

                <button type="submit" class="btn-primary">
                    <?php echo $edit_request ? 'Save Changes' : 'Submit Request'; ?>
                </button>
                <?php if ($edit_request): ?>
                    <a href="request_pickup.php">Cancel</a>
                <?php endif; ?>

            </form>

        </div>

        <!-- Guidelines -->
        <div class="card-white guide-card">

            <h2>Request Guidelines</h2>

            <ul>
                <li>Requests must be made at least 24 hours in advance.</li>
                <li>Only one pickup request is allowed per day.</li>
                <li>Bulky waste collections may require additional approval.</li>
                <li>Please provide accurate pickup details.</li>
            </ul>

        </div>

    </div>

    <!-- Previous Requests -->

    <div class="card-white history-card">

        <h2>My Pickup Requests</h2>

        <table class="history-table">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Waste Type</th>
                    <th>Pickup Date</th>
                    <th>Time Slot</th>
                    <th>Status</th>
                    <th>Requested On</th>
                    <th>Actions</th>
                </tr>

            </thead>
            <tbody>

            <?php if (empty($pickup_requests)) { ?>
                <tr>
                    <td colspan="7" style="text-align:center;">No pickup requests yet.</td>
                </tr>
            <?php } ?>

            <?php foreach ($pickup_requests as $row) { ?>

                <tr>

                    <td><?php echo (int) $row['id']; ?></td>

                    <td><?php echo htmlspecialchars($row['waste_type']); ?></td>

                    <td><?php echo date('d M Y', strtotime($row['pickup_date'])); ?></td>

                    <td><?php echo htmlspecialchars($row['time_slot']); ?></td>

                    <td>
                        <?php if ($row['states'] == "pending") { ?>
                            <span class="pill-pending">Pending</span>
                        <?php } else { ?>
                            <span class="pill-done"><?php echo htmlspecialchars(ucfirst($row['states'])); ?></span>
                        <?php } ?>
                    </td>

                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <?php if ($row['states'] === 'pending'): ?>
                            <a href="?edit_request=<?php echo (int) $row['id']; ?>">Edit</a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this pickup request?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="request_id" value="<?php echo (int) $row['id']; ?>">
                                <button type="submit">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>