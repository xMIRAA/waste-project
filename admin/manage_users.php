<?php
// ------------------------------------------------------
// manage_users.php
// Lets an admin create new user accounts and search for
// existing users by name, username, or contact details.
// ------------------------------------------------------

require_once __DIR__ . '/../config.php';

// Protect this page so only logged-in admins can access it.
require_once app_path('auth/auth_guard.php');
// Load the shared database connection needed for account and search queries.
require_once app_path('database/db.php');

// Only admin users are allowed to manage accounts.
requireAdmin();
$active_page = 'users';

/* ---------------------------------------------------------------------
 * ADD USER — insert a new login record into users
 * ------------------------------------------------------------------- */
// Store success or error messages for the add-user form after redirecting back to the page.
$add_user_message = '';
$add_user_error   = '';

if (!empty($_SESSION['add_user_message'])) {
    $add_user_message = $_SESSION['add_user_message'];
    unset($_SESSION['add_user_message']);
}
if (!empty($_SESSION['add_user_error'])) {
    $add_user_error = $_SESSION['add_user_error'];
    unset($_SESSION['add_user_error']);
}

// If the admin submits the add-user form, validate and insert the new account.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user_submit'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = trim($_POST['role'] ?? '');
    $name     = trim($_POST['name'] ?? '');
    $contact  = trim($_POST['contact'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    $allowed_roles = ['admin', 'resident'];

    // Reject incomplete form data before creating a user record.
    if ($username === '' || $password === '' || $role === '' || $name === '' || $contact === '' || $address === '') {
        $_SESSION['add_user_error'] = "Please fill in all required fields.";
    } elseif (!in_array($role, $allowed_roles, true)) {
        $_SESSION['add_user_error'] = "Invalid role selected. Please choose admin or resident.";
    } else {
        // Hash the password before saving it so the stored value is not plain text.
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user record with the hashed password and chosen role.
        $insert_stmt = $conn->prepare(
            "INSERT INTO users (username, password, role, name, address, contact)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        if ($insert_stmt) {
            try {
                $insert_stmt->execute([
                    $username,
                    $password_hash,
                    $role,
                    $name,
                    $address,
                    $contact,
                ]);
                $_SESSION['add_user_message'] = "User account created successfully.";
            } catch (PDOException $e) {
                if (($e->errorInfo[1] ?? null) === 1062) {
                    $_SESSION['add_user_error'] = "That username is already taken. Please choose a different username.";
                } else {
                    $_SESSION['add_user_error'] = "Error creating user account. Please try again.";
                }
            }
        } else {
            $_SESSION['add_user_error'] = "Unable to create user account right now.";
        }
    }

    // Redirect back to the page after the POST so the form is not resubmitted on refresh.
    header("Location: " . $_SERVER['PHP_SELF']);
    // Stop execution immediately so the redirect is the final action.
    exit;
}

/* ---------------------------------------------------------------------
 * REGISTERED USERS — fetch all login accounts from users
 * ------------------------------------------------------------------- */
// Load the current list of users for the table on the page.
$users = [];
$users_result = $conn->query("SELECT id, username, role, name, address, contact, created_at FROM users ORDER BY created_at DESC");
if ($users_result) {
    $users = $users_result->fetchAll();
}

/* ---------------------------------------------------------------------
 * SEARCH USERS — by name, username, or contact
 * Queries the real `users` table. Column choice is restricted to
 * a whitelist so the field name is never taken directly from input.
 * ------------------------------------------------------------------- */
// Limit the search to safe columns only so a user cannot query arbitrary database fields.
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

// If the user pressed the search button, run a filtered lookup against the users table.
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

        // Search only the selected field using a wildcard match to find partially matching records.
        $search_stmt = $conn->prepare(
            "SELECT id, username, role, name, address, contact, created_at
             FROM users
             WHERE {$column} LIKE ?
             ORDER BY created_at DESC"
        );

        if ($search_stmt) {
            $search_stmt->execute([$like_term]);
            $search_results = $search_stmt->fetchAll();
        } else {
            $search_error = "Search is temporarily unavailable. Please try again later.";
        }
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