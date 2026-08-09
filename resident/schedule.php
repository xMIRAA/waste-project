<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

$active_page = 'schedule';

/* Get pickup schedule */

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT id, waste_type, pickup_date, time_slot, states, created_at
     FROM pickup_requests
     WHERE user_id = ?
     ORDER BY pickup_date ASC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$schedules = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Schedule - CleanCity</title>

    <link rel="stylesheet" href="/waste-project/shared/style.css">
    <link rel="stylesheet" href="/waste-project/resident/resident-css/schedule.css">
</head>

<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/waste-project/shared/navbar.php'; ?>

<div class="page-content">

    <div class="schedule-section">

        <h2>Weekly Pickup Schedule</h2>

        <p>
            View your submitted pickup requests below.
        </p>

        <table class="schedule-table">

            <thead>

                <tr>
                    <th>Date</th>
                    <th>Waste Type</th>
                    <th>Time Slot</th>
                    <th>Status</th>
                </tr>

            </thead>

            <tbody>

            <?php

            if ($schedules->num_rows > 0) {

                while ($row = $schedules->fetch_assoc()) {

            ?>

                <tr>

                    <td>
                        <?php echo date('d M Y', strtotime($row['pickup_date'])); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['waste_type']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['time_slot']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars(ucfirst($row['states'])); ?>
                    </td>

                </tr>

            <?php

                }

            } else {

            ?>

                <tr>
                    <td colspan="4" style="text-align:center;">
                        No pickup requests submitted yet.
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

</body>
</html>