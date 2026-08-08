<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

requireAdmin();
$active_page = 'users';

/* ---------------------------------------------------------------------
 * ADD USER — insert a new record into add_users
 * ------------------------------------------------------------------- */
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user_submit'])) {
    $full_name  = trim($_POST['fullName'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['mobile_phone'] ?? '');
    $address    = trim($_POST['AddressMain'] ?? '');
    $entry_date = trim($_POST['entryDate'] ?? '');

    $preferred_days = (isset($_POST['preferred_days']) && is_array($_POST['preferred_days']))
        ? implode(', ', $_POST['preferred_days'])
        : '';

    if ($full_name === '' || $email === '' || $phone === '' || $address === '' || $entry_date === '') {
        $_SESSION['add_user_error'] = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['add_user_error'] = "Please enter a valid email address.";
    } else {
        $insert_stmt = $conn->prepare(
            "INSERT INTO add_users (full_name, email, phone, address, preferred_days, entry_date)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        if ($insert_stmt) {
            $insert_stmt->bind_param(
                "ssssss",
                $full_name,
                $email,
                $phone,
                $address,
                $preferred_days,
                $entry_date
            );

            if ($insert_stmt->execute()) {
                $_SESSION['add_user_message'] = "User details added successfully.";
            } else {
                $_SESSION['add_user_error'] = "Error saving user details. Please try again.";
            }

            $insert_stmt->close();
        } else {
            $_SESSION['add_user_error'] = "Unable to save user details right now.";
        }
    }

    // Post/Redirect/Get so refreshing the page doesn't resubmit the form.
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/* ---------------------------------------------------------------------
 * REGISTERED USERS — fetch all records from add_users
 * ------------------------------------------------------------------- */
$users = [];
$users_result = $conn->query("SELECT * FROM add_users ORDER BY created_at DESC");
if ($users_result) {
    $users = $users_result->fetch_all(MYSQLI_ASSOC);
}

/* ---------------------------------------------------------------------
 * SEARCH USERS — by full name, email, or mobile number
 * Queries the real `add_users` table. Column choice is restricted to
 * a whitelist so the field name is never taken directly from input.
 * (No username column exists here — this table stores resident
 * detail records, not login credentials — so search uses Full Name
 * in its place.)
 * ------------------------------------------------------------------- */
$search_field_columns = [
    'full_name' => 'full_name',
    'email'     => 'email',
    'phone'     => 'phone',
];
$search_field_labels = [
    'full_name' => 'Full Name',
    'email'     => 'Email',
    'phone'     => 'Mobile Number',
];

$search_query     = '';
$search_field     = 'full_name';
$search_results   = [];
$search_error     = '';
$search_performed = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search_submit'])) {
    $search_performed = true;
    $search_query = trim($_GET['search_query'] ?? '');
    $search_field = $_GET['search_field'] ?? 'full_name';

    if (!array_key_exists($search_field, $search_field_columns)) {
        $search_field = 'full_name';
    }

    if ($search_query === '') {
        $search_error = "Please enter a value to search for.";
    } else {
        $column    = $search_field_columns[$search_field];
        $like_term = '%' . $search_query . '%';

        $search_stmt = $conn->prepare(
            "SELECT full_name, email, phone, address, preferred_days, entry_date, created_at
             FROM add_users
             WHERE {$column} LIKE ?
             ORDER BY created_at DESC"
        );

        if ($search_stmt) {
            $search_stmt->bind_param("s", $like_term);
            $search_stmt->execute();
            $search_results = $search_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $search_stmt->close();
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

  <link rel="stylesheet" href="/waste-project/shared/style.css">
  <link rel="stylesheet" href="/waste-project/admin/css/userformv1.css">

</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/waste-project/shared/navbar.php'; ?>

<div class="page-content">

<div class="udf-layout">

  <!-- Form Section -->
  <div class="form-card-wrapper">
    <div class="form-card">
        <h1 class="form-title">Add User Details</h1>
        <p class="form-subtitle">Fill in the details below</p>

        <?php if (!empty($add_user_message)): ?>
            <div class="alert success-alert"><?php echo htmlspecialchars($add_user_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($add_user_error)): ?>
            <div class="alert error-alert"><?php echo htmlspecialchars($add_user_error); ?></div>
        <?php endif; ?>

        <form class="waste-form" action="" method="POST">

            <div class="form-group">
                <label for="fullName">Full Name</label>
                <input type="text" id="fullName" name="fullName" placeholder="e.g. Lahiru" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="e.g. lahiruacb@gmail.com" required>
            </div>

            <div class="form-group">
                <label for="mobilePhone">Mobile Phone</label>
                <input type="tel" id="mobilePhone" name="mobile_phone" placeholder="e.g. 077 123 4567" pattern="[0-9+\-\s]{7,15}" required>
            </div>

            <div class="form-group">
                <label for="AddressMain">Address</label>
                <input type="text" id="AddressMain" name="AddressMain" placeholder="e.g. 45/B, Galle Road" required>
            </div>

            <fieldset class="form-group address-group">
                <legend>Preferred Days</legend>
                <div class="days-row">
                    <label class="day-option">
                        <input type="checkbox" name="preferred_days[]" value="Sunday">
                        <span>Sun</span>
                    </label>
                    <label class="day-option">
                        <input type="checkbox" name="preferred_days[]" value="Monday">
                        <span>Mon</span>
                    </label>
                    <label class="day-option">
                        <input type="checkbox" name="preferred_days[]" value="Tuesday">
                        <span>Tue</span>
                    </label>
                    <label class="day-option">
                        <input type="checkbox" name="preferred_days[]" value="Wednesday">
                        <span>Wed</span>
                    </label>
                    <label class="day-option">
                        <input type="checkbox" name="preferred_days[]" value="Thursday">
                        <span>Thu</span>
                    </label>
                    <label class="day-option">
                        <input type="checkbox" name="preferred_days[]" value="Friday">
                        <span>Fri</span>
                    </label>
                    <label class="day-option">
                        <input type="checkbox" name="preferred_days[]" value="Saturday">
                        <span>Sat</span>
                    </label>
                </div>
            </fieldset>

            <div class="form-group">
                <label for="entryDate">Date</label>
                <input type="date" id="entryDate" name="entryDate" required>
            </div>

            <button type="submit" name="add_user_submit" value="1" class="btn-primary full">Add Entry</button>

        </form>
    </div>
  </div>

  <!-- Search Section -->
  <div class="form-card-wrapper">
    <div class="form-card">
        <h1 class="form-title">Search Users</h1>
        <p class="form-subtitle">Find a user by full name, email, or mobile number</p>

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
      <p>View and manage all registered user records below.</p>

      <?php if (empty($users)): ?>

          <p style="text-align:center; color: #555;">No users added yet.</p>

      <?php else: ?>

          <table class="schedule-table">
              <thead>
                  <tr>
                      <th>Full Name</th>
                      <th>Email</th>
                      <th>Telephone No</th>
                      <th>Address</th>
                      <th>Preferred Days</th>
                      <th>Entry Date</th>
                      <th>Created Date</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach ($users as $user): ?>
                      <tr>
                          <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                          <td><?php echo htmlspecialchars($user['email']); ?></td>
                          <td><?php echo htmlspecialchars($user['phone']); ?></td>
                          <td><?php echo htmlspecialchars($user['address']); ?></td>
                          <td><?php echo htmlspecialchars($user['preferred_days']); ?></td>
                          <td><?php echo date('d M Y', strtotime($user['entry_date'])); ?></td>
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
                      <th>Full Name</th>
                      <th>Email</th>
                      <th>Telephone No</th>
                      <th>Address</th>
                      <th>Preferred Days</th>
                      <th>Entry Date</th>
                      <th>Created Date</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach ($search_results as $user): ?>
                      <tr>
                          <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                          <td><?php echo htmlspecialchars($user['email']); ?></td>
                          <td><?php echo htmlspecialchars($user['phone']); ?></td>
                          <td><?php echo htmlspecialchars($user['address']); ?></td>
                          <td><?php echo htmlspecialchars($user['preferred_days']); ?></td>
                          <td><?php echo date('d M Y', strtotime($user['entry_date'])); ?></td>
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