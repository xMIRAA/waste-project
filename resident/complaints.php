<?php

require_once __DIR__ . '/../config.php';

require_once app_path('auth/auth_guard.php');
require_once app_path('database/db.php');
requireResident();

$active_page = 'complaints';

$message = '';
$error = '';
$edit_complaint = null;
$allowed_types = ['Missed Collection', 'Late Collection', 'Damaged Bin', 'Other'];

if (!empty($_SESSION['complaint_message'])) {
    $message = $_SESSION['complaint_message'];
    unset($_SESSION['complaint_message']);
}
if (!empty($_SESSION['complaint_error'])) {
    $error = $_SESSION['complaint_error'];
    unset($_SESSION['complaint_error']);
}

// Create, update, or delete only the current resident's pending complaints.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!valid_csrf_token()) {
        $_SESSION['complaint_error'] = "Invalid form submission. Please try again.";
    } else {
        $action = $_POST['action'] ?? 'create';
        $complaint_id = (int) ($_POST['complaint_id'] ?? 0);
        $user_id = (int) $_SESSION['user_id'];
        $type = trim($_POST['type'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($action === 'delete' && $complaint_id > 0) {
            $stmt = $conn->prepare("DELETE FROM complaints WHERE id = ? AND user_id = ? AND states = 'pending'");
            $stmt->bind_param("ii", $complaint_id, $user_id);
            if ($stmt->execute() && $stmt->affected_rows === 1) {
                $_SESSION['complaint_message'] = "Complaint deleted successfully.";
            } else {
                $_SESSION['complaint_error'] = "Only your pending complaints can be deleted.";
            }
        } elseif (!in_array($type, $allowed_types, true) || $subject === '' || $description === '') {
            $_SESSION['complaint_error'] = "Complaint type, subject, and description are required.";
        } elseif (strlen($subject) > 150 || strlen($description) > 10000) {
            $_SESSION['complaint_error'] = "The complaint subject or description is too long.";
        } elseif ($action === 'update' && $complaint_id > 0) {
            $exists_stmt = $conn->prepare("SELECT id FROM complaints WHERE id = ? AND user_id = ? AND states = 'pending'");
            $exists_stmt->bind_param("ii", $complaint_id, $user_id);
            $exists_stmt->execute();

            if (!$exists_stmt->get_result()->fetch_assoc()) {
                $_SESSION['complaint_error'] = "Only your pending complaints can be updated.";
            } else {
                $stmt = $conn->prepare(
                    "UPDATE complaints SET complaint_type = ?, complaint_subject = ?, complaint_text = ?
                     WHERE id = ? AND user_id = ? AND states = 'pending'"
                );
                $stmt->bind_param("sssii", $type, $subject, $description, $complaint_id, $user_id);
                if ($stmt->execute()) {
                    $_SESSION['complaint_message'] = "Complaint updated successfully.";
                } else {
                    $_SESSION['complaint_error'] = "Error updating complaint.";
                }
            }
        } elseif ($action === 'create') {
            $stmt = $conn->prepare(
                "INSERT INTO complaints (user_id, complaint_type, complaint_subject, complaint_text)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("isss", $user_id, $type, $subject, $description);
            if ($stmt->execute()) {
                $_SESSION['complaint_message'] = "Complaint submitted successfully!";
            } else {
                $_SESSION['complaint_error'] = "Error submitting complaint.";
            }
        } else {
            $_SESSION['complaint_error'] = "Invalid complaint action.";
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_complaint'])) {
    $edit_id = (int) $_GET['edit_complaint'];
    $edit_stmt = $conn->prepare("SELECT id, complaint_type, complaint_subject, complaint_text FROM complaints WHERE id = ? AND user_id = ? AND states = 'pending'");
    $edit_stmt->bind_param("ii", $edit_id, $_SESSION['user_id']);
    $edit_stmt->execute();
    $edit_complaint = $edit_stmt->get_result()->fetch_assoc() ?: null;
}

 $user_id = $_SESSION['user_id'];

// Read only the logged-in resident's complaint rows so they cannot see other residents' issues.
$stmt = $conn->prepare(
    "SELECT *
     FROM complaints
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$complaints = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
        <?php if (!empty($error)) { ?>
            <p style="color:#993c1d;font-weight:bold;">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php } ?>

        <p>
            If you have any issues regarding waste collection,
            please submit your complaint using the form below.
        </p>

        <div class="complaint-container">

            <!-- Complaint Form -->

            <div class="card-white complaint-form">

                <h3><?php echo $edit_complaint ? 'Edit Complaint' : 'Complaint Form'; ?></h3>

                <form  method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="<?php echo $edit_complaint ? 'update' : 'create'; ?>">
                    <?php if ($edit_complaint): ?>
                        <input type="hidden" name="complaint_id" value="<?php echo (int) $edit_complaint['id']; ?>">
                    <?php endif; ?>

                    <label>Complaint Type</label>

                    <select name="type" required>
                        <option value="">Select Complaint Type</option>
                        <?php foreach ($allowed_types as $type_option): ?>
                            <option <?php echo ($edit_complaint['complaint_type'] ?? '') === $type_option ? 'selected' : ''; ?>><?php echo htmlspecialchars($type_option); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Subject</label>

                    <input
                        type="text"
                        name="subject"
                        placeholder="Enter complaint subject"
                        value="<?php echo htmlspecialchars($edit_complaint['complaint_subject'] ?? ''); ?>"
                        maxlength="150"
                        required
                    >

                    <label>Description</label>

                    <textarea
                        name="description"
                        rows="5"
                        maxlength="10000"
                        placeholder="Describe your complaint..."
                        required
                    ><?php echo htmlspecialchars($edit_complaint['complaint_text'] ?? ''); ?></textarea>
            <button type="submit" class="btn-primary">
                <?php echo $edit_complaint ? 'Save Changes' : 'Submit Complaint'; ?>
            </button>
            <?php if ($edit_complaint): ?>
                <a href="complaints.php" class="cancel-edit-button">Cancel</a>
            <?php endif; ?>

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
                <th>Actions</th>
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
    <td>
        <?php if ($row['states'] === 'pending'): ?>
            <a href="?edit_complaint=<?php echo (int) $row['id']; ?>">Edit</a>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this complaint?');">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="complaint_id" value="<?php echo (int) $row['id']; ?>">
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

</div>

</body>
</html>