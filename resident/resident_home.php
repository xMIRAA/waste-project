<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

$active_page = 'home';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Resident';

/* Get Next Pickup Schedule */

$stmt = $conn->prepare(
    "SELECT *
     FROM pickup_schedule
     WHERE pickup_date >= CURDATE()
     ORDER BY pickup_date ASC
     LIMIT 1"
);

$stmt->execute();

$next_pickup = $stmt->get_result()->fetch_assoc();

$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Resident Home - CleanCity</title>

    <link rel="stylesheet" href="/waste-project/shared/style.css">
    <link rel="stylesheet" href="/waste-project/resident/resident-css/resident_home.css">
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/waste-project/shared/navbar.php'; ?>

<div class="page-content">

    <div class="card-white welcome-card">

        <h2>Welcome, <?= htmlspecialchars($username) ?>!</h2>

        <p>
            Welcome to the <strong>CleanCity Waste Collection Management System</strong>.
            This system helps residents stay informed about waste collection schedules,
            request additional waste pickups, and submit complaints regarding collection services.
        </p>

    </div>

    <div class="dashboard-container">

        <div class="card-white next-pickup-card">

            <center><h3>Next Pickup Schedule</h3></center>

        <?php if ($next_pickup) { ?>

<table class="pickup-table">

    <tr>
        <td><strong>Waste Type</strong></td>
        <td><?php echo htmlspecialchars($next_pickup['waste_type']); ?></td>
    </tr>

    <tr>
        <td><strong>Date</strong></td>
        <td><?php echo date('l, d F Y', strtotime($next_pickup['pickup_date'])); ?></td>
    </tr>

    <tr>
        <td><strong>Area</strong></td>
        <td><?php echo htmlspecialchars($next_pickup['area']); ?></td>
    </tr>

    <tr>
        <td><strong>Status</strong></td>
        <td><span class="pill-pending">Upcoming</span></td>
    </tr>

</table>

<?php } else { ?>

<p>No upcoming pickup schedule available.</p>

<?php } ?>

        </div>

        <div class="card-white information-card">

            <h3>Resident Information</h3>

            <ul>
                <li>Place waste bins outside before the scheduled pickup time.</li>
                <li>Separate recyclable and general waste.</li>
                <li>Pickup requests should be submitted at least 24 hours in advance.</li>
                <li>Use the Complaints page to report missed collections or other issues.</li>
            </ul>

        </div>

    </div>

</div>

</body>
</html>
