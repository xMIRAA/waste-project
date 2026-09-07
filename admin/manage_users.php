<?php

require_once __DIR__ . '/../config.php';

require_once app_path('auth/auth_guard.php');
require_once app_path('database/db.php');

requireAdmin();
$active_page = 'users';

// Flash messages are read after each POST/redirect cycle.
$add_user_message = '';
$add_user_error   = '';
$delete_user_message = '';
$delete_user_error   = '';
$update_user_message = '';
$update_user_error   = '';
$edit_user = null;

if (!empty($_SESSION['add_user_message'])) {
    $add_user_message = $_SESSION['add_user_message'];
    unset($_SESSION['add_user_message']);
}
if (!empty($_SESSION['add_user_error'])) {
    $add_user_error = $_SESSION['add_user_error'];
    unset($_SESSION['add_user_error']);
}
if (!empty($_SESSION['delete_user_message'])) {
    $delete_user_message = $_SESSION['delete_user_message'];
    unset($_SESSION['delete_user_message']);
}
if (!empty($_SESSION['delete_user_error'])) {
    $delete_user_error = $_SESSION['delete_user_error'];
    unset($_SESSION['delete_user_error']);
}
if (!empty($_SESSION['update_user_message'])) {
    $update_user_message = $_SESSION['update_user_message'];
    unset($_SESSION['update_user_message']);
}
if (!empty($_SESSION['update_user_error'])) {
    $update_user_error = $_SESSION['update_user_error'];
    unset($_SESSION['update_user_error']);
}

// Update a selected account without exposing its stored password hash.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_submit'])) {
    $update_user_id = (int) ($_POST['user_id'] ?? 0);
    $username       = trim($_POST['username'] ?? '');
    $password       = $_POST['password'] ?? '';
    $role           = trim($_POST['role'] ?? '');
    $name           = trim($_POST['name'] ?? '');
    $contact        = trim($_POST['contact'] ?? '');
    $address        = trim($_POST['address'] ?? '');
    $allowed_roles  = ['admin', 'resident'];

    if ($update_user_id <= 0) {
        $_SESSION['update_user_error'] = "Invalid user selected.";
    } elseif ($username === '' || $role === '' || $name === '' || $contact === '' || $address === '') {
        $_SESSION['update_user_error'] = "Username, role, name, contact, and address are required.";
    } elseif (strlen($username) > 50 || strlen($name) > 100 || strlen($address) > 255 || strlen($contact) > 20) {
        $_SESSION['update_user_error'] = "One or more fields exceed the allowed length.";
    } elseif (!in_array($role, $allowed_roles, true)) {
        $_SESSION['update_user_error'] = "Invalid role selected. Please choose admin or resident.";
    } elseif (!preg_match('/^[0-9+\-\s]{7,20}$/', $contact)) {
        $_SESSION['update_user_error'] = "Please enter a valid contact number.";
    } else {
        $user_stmt = $conn->prepare("SELECT id, role FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $update_user_id);
        $user_stmt->execute();
        $user_row = $user_stmt->get_result()->fetch_assoc();

        if (!$user_row) {
            $_SESSION['update_user_error'] = "User account not found.";
        } elseif ($update_user_id === (int) $_SESSION['user_id'] && $role !== 'admin') {
            $_SESSION['update_user_error'] = "You cannot remove the admin role from the account currently in use.";
        } else {
            $duplicate_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id <> ?");
            $duplicate_stmt->bind_param("si", $username, $update_user_id);
            $duplicate_stmt->execute();

            if ($duplicate_stmt->get_result()->fetch_assoc()) {
                $_SESSION['update_user_error'] = "That username is already taken. Please choose a different username.";
            } else {
                if ($password !== '') {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $conn->prepare(
                        "UPDATE users SET username = ?, password = ?, role = ?, name = ?, address = ?, contact = ? WHERE id = ?"
                    );
                    $update_stmt->bind_param("ssssssi", $username, $password_hash, $role, $name, $address, $contact, $update_user_id);
                } else {
                    $update_stmt = $conn->prepare(
                        "UPDATE users SET username = ?, role = ?, name = ?, address = ?, contact = ? WHERE id = ?"
                    );
                    $update_stmt->bind_param("sssssi", $username, $role, $name, $address, $contact, $update_user_id);
                }

                if ($update_stmt && $update_stmt->execute()) {
                    if ($update_user_id === (int) $_SESSION['user_id']) {
                        $_SESSION['username'] = $username;
                    }
                    $_SESSION['update_user_message'] = "User account updated successfully.";
                } else {
                    $_SESSION['update_user_error'] = "Error updating user account. Please try again.";
                }
            }
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Related requests and complaints are removed by the database cascade.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_submit'])) {
    $delete_user_id = (int) ($_POST['user_id'] ?? 0);

    if ($delete_user_id <= 0) {
        $_SESSION['delete_user_error'] = "Invalid user selected.";
    } elseif ($delete_user_id === (int) $_SESSION['user_id']) {
        $_SESSION['delete_user_error'] = "You cannot delete the account currently in use.";
    } else {
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");

        if ($delete_stmt) {
            $delete_stmt->bind_param("i", $delete_user_id);

            if ($delete_stmt->execute() && $delete_stmt->affected_rows === 1) {
                $_SESSION['delete_user_message'] = "User account deleted successfully.";
            } else {
                $_SESSION['delete_user_error'] = "User account not found or could not be deleted.";
            }
        } else {
            $_SESSION['delete_user_error'] = "Unable to delete the user account right now.";
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Create a new account after validating all fields server-side.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user_submit'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = trim($_POST['role'] ?? '');
    $name     = trim($_POST['name'] ?? '');
    $contact  = trim($_POST['contact'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    $allowed_roles = ['admin', 'resident'];

    if ($username === '' || $password === '' || $role === '' || $name === '' || $contact === '' || $address === '') {
        $_SESSION['add_user_error'] = "Please fill in all required fields.";
    } elseif (strlen($username) > 50 || strlen($name) > 100 || strlen($address) > 255 || strlen($contact) > 20) {
        $_SESSION['add_user_error'] = "One or more fields exceed the allowed length.";
    } elseif (!preg_match('/^[0-9+\-\s]{7,20}$/', $contact)) {
        $_SESSION['add_user_error'] = "Please enter a valid contact number.";
    } elseif (!in_array($role, $allowed_roles, true)) {
        $_SESSION['add_user_error'] = "Invalid role selected. Please choose admin or resident.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $insert_stmt = $conn->prepare(
            "INSERT INTO users (username, password, role, name, address, contact)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        if ($insert_stmt) {
            $insert_stmt->bind_param(
                "ssssss",
                $username,
                $password_hash,
                $role,
                $name,
                $address,
                $contact
            );

            if ($insert_stmt->execute()) {
                $_SESSION['add_user_message'] = "User account created successfully.";
            } elseif ($conn->errno === 1062) {
                $_SESSION['add_user_error'] = "That username is already taken. Please choose a different username.";
            } else {
                $_SESSION['add_user_error'] = "Error creating user account. Please try again.";
            }
        } else {
            $_SESSION['add_user_error'] = "Unable to create user account right now.";
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Load all accounts for the main user table.
$users = [];
$users_result = $conn->query("SELECT id, username, role, name, address, contact, created_at FROM users ORDER BY created_at DESC");
if ($users_result) {
    $users = $users_result->fetch_all(MYSQLI_ASSOC);
}

// Whitelist searchable columns so input cannot become a SQL identifier.
$search_field_columns = [
    'name'     => 'name',
    'username' => 'username',
    'contact'  => 'contact',
];
$search_field_labels = [
    'name'     => 'Name',
    'username' => 'Username',
    'contact'  => 'Contact',
];

$search_query     = '';
$search_field     = 'name';
$search_results   = [];
$search_error     = '';
$search_performed = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search_submit'])) {
    $search_performed = true;
    $search_query = trim($_GET['search_query'] ?? '');
    $search_field = $_GET['search_field'] ?? 'name';

    if (!array_key_exists($search_field, $search_field_columns)) {
        $search_field = 'name';
    }

    if ($search_query === '') {
        $search_error = "Please enter a value to search for.";
    } else {
        $column    = $search_field_columns[$search_field];
        $like_term = '%' . $search_query . '%';

        $search_stmt = $conn->prepare(
            "SELECT id, username, role, name, address, contact, created_at
             FROM users
             WHERE {$column} LIKE ?
             ORDER BY created_at DESC"
        );

        if ($search_stmt) {
            $search_stmt->bind_param("s", $like_term);
            $search_stmt->execute();
            $search_results = $search_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $search_error = "Search is temporarily unavailable. Please try again later.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_user'])) {
    $edit_user_id = (int) $_GET['edit_user'];
    if ($edit_user_id > 0) {
        $edit_stmt = $conn->prepare("SELECT id, username, role, name, address, contact FROM users WHERE id = ?");
        $edit_stmt->bind_param("i", $edit_user_id);
        $edit_stmt->execute();
        $edit_user = $edit_stmt->get_result()->fetch_assoc() ?: null;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Manage Users - CleanCity</title>

  <link rel="stylesheet" href="<?= app_url('shared/style.css') ?>">
  <link rel="stylesheet" href="<?= app_url('admin/css/userformv1.css') ?>">

</head>
<body>
<?php include app_path('shared/navbar.php'); ?>

<div class="page-content">

<div class="udf-layout">

  <!-- Form Section -->
  <div class="form-card-wrapper">
    <div class="form-card">
        <h1 class="form-title">Add User Account</h1>
        <p class="form-subtitle">Create a new resident or admin login account.</p>

        <?php if (!empty($add_user_message)): ?>
            <div class="alert success-alert"><?php echo htmlspecialchars($add_user_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($add_user_error)): ?>
            <div class="alert error-alert"><?php echo htmlspecialchars($add_user_error); ?></div>
        <?php endif; ?>
        <?php if (!empty($delete_user_message)): ?>
            <div class="alert success-alert"><?php echo htmlspecialchars($delete_user_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($delete_user_error)): ?>
            <div class="alert error-alert"><?php echo htmlspecialchars($delete_user_error); ?></div>
        <?php endif; ?>

        <form class="waste-form" action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="e.g. janedoe" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="resident">Resident</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="e.g. Lahiru" required>
            </div>

            <div class="form-group">
                <label for="contact">Contact Phone</label>
                <input type="tel" id="contact" name="contact" placeholder="e.g. 077 123 4567" pattern="[0-9+\-\s]{7,15}" required>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" placeholder="e.g. 45/B, Galle Road" required>
            </div>

            <button type="submit" name="add_user_submit" value="1" class="btn-primary full">Create Account</button>

        </form>
    </div>
  </div>

  <!-- Search Section -->
  <div class="form-card-wrapper">
    <div class="form-card">
        <h1 class="form-title">Search Users</h1>
        <p class="form-subtitle">Find a user by name, username, or contact number</p>

        <?php if (!empty($search_error)): ?>
            <div class="alert error-alert"><?php echo htmlspecialchars($search_error); ?></div>
        <?php endif; ?>

        <form class="waste-form" action="" method="GET">

            <div class="form-group">
                <label for="search_field">Search By</label>
                <select id="search_field" name="search_field">
                    <?php foreach ($search_field_labels as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $search_field === $value ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="search_query">Search Term</label>
                <input type="text" id="search_query" name="search_query" placeholder="e.g. jane.doe" value="<?php echo htmlspecialchars($search_query); ?>">
            </div>

            <button type="submit" name="search_submit" value="1" class="btn-primary full">Search</button>

        </form>
    </div>
  </div>

</div>

<?php if ($edit_user): ?>
  <div class="form-card-wrapper edit-user-wrapper">
    <div class="form-card edit-user-card">
        <h1 class="form-title">Edit User Account</h1>
        <p class="form-subtitle">Update the selected user's account details.</p>

        <form class="waste-form" action="" method="POST">
            <input type="hidden" name="user_id" value="<?php echo (int) $edit_user['id']; ?>">

            <div class="form-group">
                <label for="edit_username">Username</label>
                <input type="text" id="edit_username" name="username" value="<?php echo htmlspecialchars($edit_user['username']); ?>" maxlength="50" required>
            </div>

            <div class="form-group">
                <label for="edit_password">New Password</label>
                <input type="password" id="edit_password" name="password" placeholder="Leave blank to keep current password">
            </div>

            <div class="form-group">
                <label for="edit_role">Role</label>
                <?php if ((int) $edit_user['id'] === (int) $_SESSION['user_id']): ?>
                    <input type="hidden" name="role" value="admin">
                    <input type="text" id="edit_role" value="Admin" readonly>
                <?php else: ?>
                    <select id="edit_role" name="role" required>
                        <option value="resident" <?php echo $edit_user['role'] === 'resident' ? 'selected' : ''; ?>>Resident</option>
                        <option value="admin" <?php echo $edit_user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="edit_name">Full Name</label>
                <input type="text" id="edit_name" name="name" value="<?php echo htmlspecialchars($edit_user['name']); ?>" maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="edit_contact">Contact Phone</label>
                <input type="tel" id="edit_contact" name="contact" value="<?php echo htmlspecialchars($edit_user['contact']); ?>" maxlength="20" pattern="[0-9+\-\s]{7,20}" required>
            </div>

            <div class="form-group">
                <label for="edit_address">Address</label>
                <input type="text" id="edit_address" name="address" value="<?php echo htmlspecialchars($edit_user['address']); ?>" maxlength="255" required>
            </div>

            <div class="edit-form-actions">
                <button type="submit" name="update_user_submit" value="1" class="btn-primary">Save Changes</button>
                <a href="manage_users.php" class="cancel-edit-button">Cancel</a>
            </div>
        </form>
    </div>
  </div>
<?php endif; ?>

<!-- Table Section styled consistently with schedule.css -->
  <div class="user-schedule-section">

      <h2>Registered Users</h2>
      <p>View and manage all registered login accounts below.</p>

      <?php if (empty($users)): ?>

          <p style="text-align:center; color: #555;">No users added yet.</p>

      <?php else: ?>

          <table class="schedule-table">
              <thead>
                  <tr>
                      <th>Name</th>
                      <th>Username</th>
                      <th>Role</th>
                      <th>Contact</th>
                      <th>Address</th>
                      <th>Created Date</th>
                      <th>Actions</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach ($users as $user): ?>
                      <tr>
                          <td><?php echo htmlspecialchars($user['name']); ?></td>
                          <td><?php echo htmlspecialchars($user['username']); ?></td>
                          <td><?php echo htmlspecialchars($user['role']); ?></td>
                          <td><?php echo htmlspecialchars($user['contact']); ?></td>
                          <td><?php echo htmlspecialchars($user['address']); ?></td>
                          <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                          <td class="user-actions">
                              <a href="?edit_user=<?php echo (int) $user['id']; ?>" class="edit-user-button">Edit</a>
                              <?php if ((int) $user['id'] !== (int) $_SESSION['user_id']): ?>
                                  <form action="" method="POST" onsubmit="return confirm('Delete this user account? Related requests and complaints will also be deleted.');">
                                      <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                      <button type="submit" name="delete_user_submit" value="1" class="delete-user-button">Delete</button>
                                  </form>
                              <?php else: ?>
                                  <span class="current-user-label">Current account</span>
                              <?php endif; ?>
                          </td>
                      </tr>
                  <?php endforeach; ?>
              </tbody>
          </table>

      <?php endif; ?>

  </div>

  <?php if ($search_performed): ?>
  <div class="user-schedule-section">

      <h2>Search Results</h2>
      <p>
          Showing results for "<?php echo htmlspecialchars($search_query); ?>"
          in <?php echo htmlspecialchars($search_field_labels[$search_field]); ?>.
      </p>

      <?php if (!empty($search_error)): ?>

          <p style="text-align:center; color:#9c1c1c;"><?php echo htmlspecialchars($search_error); ?></p>

      <?php elseif (empty($search_results)): ?>

          <p style="text-align:center; color:#555;">No matching users found.</p>

      <?php else: ?>

          <table class="schedule-table">
              <thead>
                  <tr>
                      <th>Name</th>
                      <th>Username</th>
                      <th>Role</th>
                      <th>Contact</th>
                      <th>Address</th>
                      <th>Created Date</th>
                      <th>Actions</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach ($search_results as $user): ?>
                      <tr>
                          <td><?php echo htmlspecialchars($user['name']); ?></td>
                          <td><?php echo htmlspecialchars($user['username']); ?></td>
                          <td><?php echo htmlspecialchars($user['role']); ?></td>
                          <td><?php echo htmlspecialchars($user['contact']); ?></td>
                          <td><?php echo htmlspecialchars($user['address']); ?></td>
                          <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                          <td class="user-actions">
                              <a href="?edit_user=<?php echo (int) $user['id']; ?>" class="edit-user-button">Edit</a>
                              <?php if ((int) $user['id'] !== (int) $_SESSION['user_id']): ?>
                                  <form action="" method="POST" onsubmit="return confirm('Delete this user account? Related requests and complaints will also be deleted.');">
                                      <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                      <button type="submit" name="delete_user_submit" value="1" class="delete-user-button">Delete</button>
                                  </form>
                              <?php else: ?>
                                  <span class="current-user-label">Current account</span>
                              <?php endif; ?>
                          </td>
                      </tr>
                  <?php endforeach; ?>
              </tbody>
          </table>

      <?php endif; ?>

  </div>
  <?php endif; ?>

</div>
</body>
</html>