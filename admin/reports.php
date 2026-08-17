<?php
// ------------------------------------------------------
// reports.php
// Shows resident complaints and lets the admin update each
// complaint state from pending to done or declined.
// ------------------------------------------------------

require_once __DIR__ . '/../config.php';

// Protect this page so only logged-in admins can manage complaints.
require_once app_path('auth/auth_guard.php');
// Load the shared database connection used for complaint updates and reads.
require_once app_path('database/db.php');

// Only admins are allowed to view or change complaint statuses.
requireAdmin();
$active_page = 'reports';

$status_message = '';
$status_error   = '';

if (!empty($_SESSION['status_message'])) {
    $status_message = $_SESSION['status_message'];
    unset($_SESSION['status_message']);
}
if (!empty($_SESSION['status_error'])) {
    $status_error = $_SESSION['status_error'];
    unset($_SESSION['status_error']);
}

$allowed_statuses = ['pending', 'done', 'declined'];

/* ---------------------------------------------------------------------
 * UPDATE STATUS — pending / done / declined
 * ------------------------------------------------------------------- */
// If the admin changes a complaint status, validate the complaint ID and the new status before updating it.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $complaint_id = (int) ($_POST['complaint_id'] ?? 0);
    $new_status   = $_POST['status'] ?? '';

    // Reject invalid complaint IDs or unknown statuses before altering the database.
    if ($complaint_id > 0 && in_array($new_status, $allowed_statuses, true)) {
        // Update the complaint row to the selected status.
        $update_stmt = $conn->prepare("UPDATE complaints SET states = ? WHERE id = ?");
        // Bind the status and complaint ID securely with prepared parameters.
        $update_stmt->bind_param("si", $new_status, $complaint_id);

        if ($update_stmt->execute()) {
            $_SESSION['status_message'] = "Complaint #{$complaint_id} marked as " . ucfirst($new_status) . ".";
        } else {
            $_SESSION['status_error'] = "Failed to update the complaint. Please try again.";
        }

        $update_stmt->close();
    } else {
        $_SESSION['status_error'] = "Invalid update request.";
    }

    // Redirect back to this page after the POST so a refresh does not resubmit the same update.
    header("Location: " . $_SERVER['PHP_SELF']);
    // Stop immediately so no extra code runs after the redirect.
    exit;
}

/* ---------------------------------------------------------------------
 * FETCH ALL COMPLAINTS — joined with the submitting user
 * ------------------------------------------------------------------- */
// Pull each complaint alongside the resident username so the admin can see who submitted it.
$complaints = [];
$fetch_stmt = $conn->prepare(
    "SELECT c.id, c.complaint_type, c.complaint_subject, c.complaint_text, c.states, c.created_at,
            u.username
     FROM complaints c
     JOIN users u ON u.id = c.user_id
     ORDER BY c.created_at DESC"
);

if ($fetch_stmt) {
    $fetch_stmt->execute();
    $complaints = $fetch_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $fetch_stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Reports - CleanCity</title>
  <link rel="stylesheet" href="<?= app_url('shared/style.css') ?>">
  <link rel="stylesheet" href="<?= app_url('admin/css/reports.css') ?>">
</head>
<body>
<?php include app_path('shared/navbar.php'); ?>
<div class="page-content">

  <div class="card-white reports-card">

    <h2>Complaints</h2>
    <p>View and manage all resident complaints from here.</p>

    <?php if (!empty($status_message)): ?>
        <div class="alert success-alert"><?php echo htmlspecialchars($status_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($status_error)): ?>
        <div class="alert error-alert"><?php echo htmlspecialchars($status_error); ?></div>
    <?php endif; ?>

    <?php if (empty($complaints)): ?>

        <p style="text-align:center; color:#555;">No complaints yet.</p>

    <?php else: ?>

        <table class="reports-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Resident</th>
                    <th>Type</th>
                    <th>Subject</th>
                    <th>Complaint</th>
                    <th>Status</th>
                    <th>Submitted On</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($complaints as $c): ?>
                    <tr>
                        <td><?php echo (int) $c['id']; ?></td>
                        <td><?php echo htmlspecialchars($c['username']); ?></td>
                        <td><?php echo htmlspecialchars($c['complaint_type']); ?></td>
                        <td><?php echo htmlspecialchars($c['complaint_subject']); ?></td>
                        <td class="complaint-text"><?php echo nl2br(htmlspecialchars($c['complaint_text'])); ?></td>
                        <td>
                            <?php if ($c['states'] === 'pending'): ?>
                                <span class="pill-pending">Pending</span>
                            <?php elseif ($c['states'] === 'done'): ?>
                                <span class="pill-done">Done</span>
                            <?php else: ?>
                                <span class="pill-declined">Declined</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
                        <td class="update-cell">
                            <form method="POST" class="status-update-form">
                                <input type="hidden" name="complaint_id" value="<?php echo (int) $c['id']; ?>">
                                <select name="status">
                                    <option value="pending" <?php echo $c['states'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="done" <?php echo $c['states'] === 'done' ? 'selected' : ''; ?>>Done</option>
                                    <option value="declined" <?php echo $c['states'] === 'declined' ? 'selected' : ''; ?>>Declined</option>
                                </select>
                                <button type="submit" name="update_status" value="1" class="btn-primary">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

  </div>

</div>
</body>
</html>