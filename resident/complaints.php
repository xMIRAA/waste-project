<?php
// ------------------------------------------------------
// complaints.php
// Lets a resident submit a complaint and view only their
// own complaint history with its current status.
// ------------------------------------------------------

require_once __DIR__ . '/../config.php';

// Protect this page so only logged-in residents can submit issues.
require_once app_path('auth/auth_guard.php');
// Load the database connection used for complaint inserts and reads.
require_once app_path('database/db.php');

$active_page = 'complaints';

$message = '';  

/* Submit Complaint */
// If the resident submits a complaint, validate and insert the record tied to their user_id.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = $_SESSION['user_id'];

    $type = trim($_POST['type']);
    $subject = trim($_POST['subject']);
    $description = trim($_POST['description']);

    // Insert the complaint with the resident's user id so admins can track who reported it.
    $stmt = $conn->prepare(
    "INSERT INTO complaints
    (user_id, complaint_type, complaint_subject, complaint_text)
    VALUES (?, ?, ?, ?)"
);

if ($stmt->execute([
    $user_id,
    $type,
    $subject,
    $description
])) {
    $message = "Complaint submitted successfully!";
} else {
    $message = "Error submitting complaint.";
}

}

 $user_id = $_SESSION['user_id'];

// Read only the logged-in resident's complaint rows so they cannot see other residents' issues.
$stmt = $conn->prepare(
    "SELECT *
     FROM complaints
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->execute([$user_id]);
$complaints = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Complaints - CleanCity</title>

    <link rel="stylesheet" href="<?= app_url('shared/style.css') ?>">
    <link rel="stylesheet" href="<?= app_url('resident/resident-css/complaints.css') ?>">
</head>

<body>

<?php include app_path('shared/navbar.php'); ?>

<div class="page-content">

    <div class="complaints-section">

        <h2>Submit a Complaint</h2>
        <?php if (!empty($message)) { ?>
    <p style="color: green; font-weight: bold;">
        <?php echo htmlspecialchars($message); ?>
    </p>
<?php } ?>

        <p>
            If you have any issues regarding waste collection,
            please submit your complaint using the form below.
        </p>

        <div class="complaint-container">

            <!-- Complaint Form -->

            <div class="card-white complaint-form">

                <h3>Complaint Form</h3>

                <form  method="POST">

                    <label>Complaint Type</label>

                    <select name="type" required>
                        <option value="">Select Complaint Type</option>
                        <option>Missed Collection</option>
                        <option>Late Collection</option>
                        <option>Damaged Bin</option>
                        <option>Other</option>
                    </select>

                    <label>Subject</label>

                    <input
                        type="text"
                        name="subject"
                        placeholder="Enter complaint subject"
                        required
                    >

                    <label>Description</label>

                    <textarea
                        name="description"
                        rows="5"
                        placeholder="Describe your complaint..."
                        required
                    ></textarea>
            <button type="submit" class="btn-primary">
                Submit Complaint
            </button>

                </form>

            </div>

            <!-- Guidelines -->

            <div class="card-white complaint-guide">

                <h3>Guidelines</h3>

                <ul>
                    <li>Provide accurate information.</li>
                    <li>Describe the issue clearly.</li>
                    <li>One complaint per issue.</li>
                    <li>Our team will review your complaint.</li>
                </ul>

            </div>

        </div>

       <!-- Complaint History -->

<div class="card-white complaint-history">

    <h3>My Complaints</h3>

    <table>

        <thead>

            <tr>
                <th>ID</th>
                <th>Complaint Type</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Submitted On</th>
            </tr>

        </thead>

        <tbody>

<?php foreach ($complaints as $row) { ?>

<tr>

    <td><?php echo $row['id']; ?></td>

    <td><?php echo htmlspecialchars($row['complaint_type']); ?></td>

    <td><?php echo htmlspecialchars($row['complaint_subject']); ?></td>

    <td>

        <?php

        if ($row['states'] == "pending") {

            echo "<span class='pill-pending'>Pending</span>";

        } elseif ($row['states'] == "declined") {

            echo "<span class='pill-declined'>Declined</span>";

        } else {

            echo "<span class='pill-done'>Resolved</span>";

        }

        ?>

    </td>

    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>

</tr>

<?php } ?>

</tbody>

    </table>

</div>

</div>

</div>

</body>
</html>