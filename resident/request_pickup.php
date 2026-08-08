<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';


$active_page = 'request_pickup';

$message = '';

/* Submit Pickup Request */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = $_SESSION['user_id'];

    $waste_type = trim($_POST['waste_type']);
    $pickup_date = $_POST['pickup_date'];
    $time_slot = trim($_POST['time_slot']);
    $notes = trim($_POST['notes']);

    $stmt = $conn->prepare(
        "INSERT INTO pickup_requests
        (user_id, waste_type, pickup_date, time_slot, notes)
        VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "issss",
        $user_id,
        $waste_type,
        $pickup_date,
        $time_slot,
        $notes
    );

    if($stmt->execute()){
        $message = "Pickup request submitted successfully!";
    }else{
        $message = "Error : ".$stmt->error;
    }

    $stmt->close();
}  

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT * FROM pickup_requests
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$pickup_requests = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Pickup - CleanCity</title>

    <link rel="stylesheet" href="/waste-project/shared/style.css">
    <link rel="stylesheet" href="/waste-project/resident/resident-css/pickup.css">
</head>

<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/waste-project/shared/navbar.php'; ?>

<div class="page-content">

    <center><h1>Request Pickup</h1>
    <?php if(!empty($message)){ ?>
    <p style="color:green;font-weight:bold;">
        <?php echo htmlspecialchars($message); ?>
    </p>
    <?php } ?>
    <p class="page-description">
        Need an additional waste collection? Submit your pickup request below.
    </p></center>

    <div class="request-container">

        <!-- Pickup Form -->
        <div class="card-white form-card">

            <h2>Pickup Request Form</h2>

            <form  method="POST">

                <label>Waste Type</label>

                <select name="waste_type" required>
                    <option value="">Select Waste Type</option>
                    <option>General Waste</option>
                    <option>Recyclables</option>
                    <option>Garden Waste</option>
                    <option>E-Waste</option>
                </select>

                <label>Preferred Pickup Date</label>

                <input type="date" name="pickup_date" required>

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

            <?php

                while($row = $pickup_requests->fetch_assoc()){

            ?>

                <tr>

                    <td><?php echo $row['id']; ?></td>

                    <td><?php echo htmlspecialchars($row['waste_type']); ?></td>

                    <td><?php echo date('d M Y',strtotime($row['pickup_date'])); ?></td>

                    <td><?php echo htmlspecialchars($row['time_slot']); ?></td>

                    <td>

                <?php

                    if($row['status']=="pending"){

                        echo "<span class='pill-pending'>Pending</span>";

                    }else{

                        echo "<span class='pill-done'>".ucfirst($row['status'])."</span>";

                    }

                    ?>

                    </td>

                    <td><?php echo date('d M Y',strtotime($row['created_at'])); ?></td>

                    </tr>

                    <?php

                    }

                    $stmt->close();

                    ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
