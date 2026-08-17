<?php
// ------------------------------------------------------
// request_pickup.php
// Lets a resident submit a new pickup request and view their
// own past requests and current status.
// ------------------------------------------------------

require_once __DIR__ . '/../config.php';

// Protect this page so a resident must be logged in before submitting a request.
require_once app_path('auth/auth_guard.php');
// Load the database connection used for pickup insert and lookup queries.
require_once app_path('database/db.php');

$active_page = 'request_pickup';

$message   = '';
$error     = '';

/* Whitelisted option values — must match the <select> options below */
// Restrict valid waste and time options to a safe allowlist so unexpected values are rejected.
$allowed_waste_types = ['General Waste', 'Recyclables', 'Garden Waste', 'E-Waste'];
$allowed_time_slots  = ['Morning (8 AM - 12 PM)', 'Afternoon (12 PM - 4 PM)', 'Evening (4 PM - 7 PM)'];

/* Pick up any flash message left by a previous redirect (PRG pattern) */
// Read the success message from the previous redirect so it is shown once after submit.
if (!empty($_SESSION['pickup_message'])) {
    $message = $_SESSION['pickup_message'];
    unset($_SESSION['pickup_message']);
}

/* Submit Pickup Request */
// If the resident submits the form, validate the request before inserting it.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id     = $_SESSION['user_id'];

    // Safely read POST fields (avoids "Undefined array key" warnings)
    $waste_type  = isset($_POST['waste_type']) ? trim($_POST['waste_type']) : '';
    $pickup_date = isset($_POST['pickup_date']) ? trim($_POST['pickup_date']) : '';
    $time_slot   = isset($_POST['time_slot']) ? trim($_POST['time_slot']) : '';
    $notes       = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    // Validate waste type / time slot against the allowed lists
    if (!in_array($waste_type, $allowed_waste_types, true)) {
        $error = "Please select a valid waste type.";
    } elseif (!in_array($time_slot, $allowed_time_slots, true)) {
        $error = "Please select a valid time slot.";
    } else {
        // Validate the date: must be a real date and at least 24 hours ahead
        $date_obj = DateTime::createFromFormat('Y-m-d', $pickup_date);
        $today    = new DateTime('today');

        if (!$date_obj || $date_obj->format('Y-m-d') !== $pickup_date) {
            $error = "Please provide a valid pickup date.";
        } else {
            $min_date = (clone $today)->modify('+1 day');
            if ($date_obj < $min_date) {
                $error = "Pickup requests must be made at least 24 hours in advance.";
            } else {
                // Enforce "one pickup request per day" per user so residents cannot flood the system with duplicate requests.
                $check = $conn->prepare(
                    "SELECT COUNT(*) AS cnt FROM pickup_requests
                     WHERE user_id = ? AND pickup_date = ?"
                );
                $check->bind_param("is", $user_id, $pickup_date);
                $check->execute();
                $count_row = $check->get_result()->fetch_assoc();
                $check->close();

                if ($count_row['cnt'] > 0) {
                    $error = "You already have a pickup request for that date. Only one request per day is allowed.";
                } else {
                    // Insert the resident's request with a record tied to their own user_id only.
                    $insert_stmt = $conn->prepare(
                        "INSERT INTO pickup_requests
                        (user_id, waste_type, pickup_date, time_slot, notes)
                        VALUES (?, ?, ?, ?, ?)"
                    );

                    $insert_stmt->bind_param(
                        "issss",
                        $user_id,
                        $waste_type,
                        $pickup_date,
                        $time_slot,
                        $notes
                    );

                    if ($insert_stmt->execute()) {
                        $_SESSION['pickup_message'] = "Pickup request submitted successfully!";
                    } else {
                        $_SESSION['pickup_message'] = "Error submitting request. Please try again.";
                    }

                    $insert_stmt->close();

                    // Redirect (Post/Redirect/Get) so refreshing the page does not resubmit the form.
                    header("Location: " . $_SERVER['PHP_SELF']);
                    // Stop immediately so no extra code runs after the redirect.
                    exit;
                }
            }
        }
    }
}

// Only show rows linked to the current resident's user_id so they can see their own requests.
$user_id = $_SESSION['user_id'];

$select_stmt = $conn->prepare(
    "SELECT * FROM pickup_requests
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$select_stmt->bind_param("i", $user_id);
$select_stmt->execute();

$pickup_requests = $select_stmt->get_result();
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

    <center><h1>Request Pickup</h1>
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

                <label>Waste Type</label>

                <select name="waste_type" required>
                    <option value="">Select Waste Type</option>
                    <option>General Waste</option>
                    <option>Recyclables</option>
                    <option>Garden Waste</option>
                    <option>E-Waste</option>
                </select>

                <label>Preferred Pickup Date</label>

                <input type="date" name="pickup_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">

                <label>Preferred Time</label>

                <select name="time_slot" required>
                    <option value="">Select Time</option>
                    <option>Morning (8 AM - 12 PM)</option>
                    <option>Afternoon (12 PM - 4 PM)</option>
                    <option>Evening (4 PM - 7 PM)</option>
                </select>

                <label>Additional Notes</label>

                <textarea
                    name="notes"
                    rows="5"
                    placeholder="Enter additional information (optional)"
                ></textarea>

                <button type="submit" class="btn-primary">
                    Submit Request
                </button>

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
                </tr>

            </thead>
            <tbody>

            <?php if ($pickup_requests->num_rows === 0) { ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No pickup requests yet.</td>
                </tr>
            <?php } ?>

            <?php while ($row = $pickup_requests->fetch_assoc()) { ?>

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

                </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>

<?php $select_stmt->close(); ?>

</body>
</html>