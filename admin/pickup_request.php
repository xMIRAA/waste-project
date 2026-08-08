<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

requireAdmin();
$active_page = 'pickup_requests';

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $request_id = (int) ($_POST['request_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';

    if ($request_id > 0 && in_array($new_status, $allowed_statuses, true)) {
        $update_stmt = $conn->prepare("UPDATE pickup_requests SET states = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $request_id);

        if ($update_stmt->execute()) {
            $_SESSION['status_message'] = "Request #{$request_id} marked as " . ucfirst($new_status) . ".";
        } else {
            $_SESSION['status_error'] = "Failed to update the request. Please try again.";
        }

        $update_stmt->close();
    } else {
        $_SESSION['status_error'] = "Invalid update request.";
    }

    // Post/Redirect/Get so refreshing the page doesn't resubmit the update.
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/* ---------------------------------------------------------------------
 * FETCH ALL PICKUP REQUESTS — joined with the requesting user
 * ------------------------------------------------------------------- */
$requests = [];
$fetch_stmt = $conn->prepare(
    "SELECT pr.id, pr.waste_type, pr.pickup_date, pr.time_slot, pr.notes, pr.states, pr.created_at,
            u.username
     FROM pickup_requests pr
     JOIN users u ON u.id = pr.user_id
     ORDER BY pr.created_at DESC"
);

if ($fetch_stmt) {
    $fetch_stmt->execute();
    $requests = $fetch_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $fetch_stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Pickup Requests</title>
  <link rel="stylesheet" href="/waste-project/shared/style.css">
  <link rel="stylesheet" href="/waste-project/admin/css/pickup-req.css">
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/waste-project/shared/navbar.php'; ?>
<div class="page-content">

  <div class="card-white requests-card">

    <h2>Pickup Requests</h2>
    <p>View and manage all pickup requests from here.</p>

    <?php if (!empty($status_message)): ?>
        <div class="alert success-alert"><?php echo htmlspecialchars($status_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($status_error)): ?>
        <div class="alert error-alert"><?php echo htmlspecialchars($status_error); ?></div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>

        <p style="text-align:center; color:#555;">No pickup requests yet.</p>

    <?php else: ?>

        <table class="requests-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Resident</th>
                    <th>Waste Type</th>
                    <th>Pickup Date</th>
                    <th>Time Slot</th>
                    <th>Notes</th>
                    <th>Status</th>
                    <th>Requested On</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><?php echo (int) $req['id']; ?></td>
                        <td><?php echo htmlspecialchars($req['username']); ?></td>
                        <td><?php echo htmlspecialchars($req['waste_type']); ?></td>
                        <td><?php echo date('d M Y', strtotime($req['pickup_date'])); ?></td>
                        <td><?php echo htmlspecialchars($req['time_slot']); ?></td>
                        <td><?php echo htmlspecialchars(($req['notes'] ?? '') !== '' ? $req['notes'] : '—'); ?></td>
                        <td>
                            <?php if ($req['states'] === 'pending'): ?>
                                <span class="pill-pending">Pending</span>
                            <?php elseif ($req['states'] === 'done'): ?>
                                <span class="pill-done">Done</span>
                            <?php else: ?>
                                <span class="pill-declined">Declined</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d M Y', strtotime($req['created_at'])); ?></td>
                        <td class="update-cell">
                            <form method="POST" class="status-update-form">
                                <input type="hidden" name="request_id" value="<?php echo (int) $req['id']; ?>">
                                <select name="status">
                                    <option value="pending" <?php echo $req['states'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="done" <?php echo $req['states'] === 'done' ? 'selected' : ''; ?>>Done</option>
                                    <option value="declined" <?php echo $req['states'] === 'declined' ? 'selected' : ''; ?>>Declined</option>
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