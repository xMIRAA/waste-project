<?php
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/auth/auth_guard.php';
require $_SERVER['DOCUMENT_ROOT'] . '/waste-project/database/db.php';

requireAdmin();
$active_page = 'users';

/* ---------------------------------------------------------------------
 * PLACEHOLDER DATA — replace with a real fetch once user_details (and
 * a username source) exist, e.g.:
 *   $result = $conn->query('SELECT * FROM user_details ORDER BY created_at DESC');
 *   $users  = $result->fetch_all(MYSQLI_ASSOC);
 *
 * NOTE: "username" is not currently collected by the form above — this
 * placeholder includes it so the table can be previewed, but you'll need
 * to either add a username input to the form or decide how it's
 * generated before this connects to real data.
 * ------------------------------------------------------------------- */
$users = [
    [
        'username'    => 'jane.doe',
        'full_name'   => 'Jane Doe',
        'address'     => '45/B, Galle Road, Nugegoda, Colombo',
        'phone'       => '077 123 4567',
        'email'       => 'jane@example.com',
        'created_at'  => '2026-07-20',
    ],
    [
        'username'    => 'kasun.p',
        'full_name'   => 'Kasun Perera',
        'address'     => '12, Kandy Road, Peradeniya, Kandy',
        'phone'       => '071 987 6543',
        'email'       => 'kasun@example.com',
        'created_at'  => '2026-07-18',
    ],
];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Manage Users - CleanCity</title>

  <link rel="stylesheet" href="/waste-project/shared/style.css">
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/waste-project/shared/navbar.php'; ?>
<div class="page-content">

<div class="udf-layout">

<div class="form-card-wrapper">
  <div class="form-card">
      <h1 class="form-title">Add User Details</h1>
      <p class="form-subtitle">Fill in the details below</p>

      <form class="waste-form" action="#" method="POST">

          <div class="form-group">
              <label for="fullName">Full Name</label>
              <input type="text" id="fullName" name="fullName" placeholder="e.g. Jane Doe" required>
          </div>

          <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" placeholder="e.g. lahiruacb@gmail.com" required>
          </div>

          <div class="form-group">
              <label for="mobilePhone">Mobile Phone</label>
              <input type="tel" id="mobilePhone" name="mobile_phone" placeholder="e.g. 077 123 4567" pattern="[0-9+\-\s]{7,15}" required>
          </div>

          <fieldset class="form-group address-group">
              <legend>Address</legend>
              <div class="address-row">
                  <div class="address-field">
                      <label for="streetName">Street Name</label>
                      <input type="text" id="streetName" name="address_street" placeholder="e.g. Galle Road" required>
                  </div>
                  <div class="address-field">
                      <label for="homeNumber">Home Number</label>
                      <input type="text" id="homeNumber" name="address_home_number" placeholder="e.g. 45/B" required>
                  </div>
              </div>
              <div class="address-row">
                  <div class="address-field">
                      <label for="mainCity">Main City</label>
                      <input type="text" id="mainCity" name="address_main_city" placeholder="e.g. Colombo" required>
                  </div>
                  <div class="address-field">
                      <label for="subCity">Sub City</label>
                      <input type="text" id="subCity" name="address_sub_city" placeholder="e.g. Nugegoda" required>
                  </div>
              </div>
          </fieldset>

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


          <button type="submit" class="btn-primary full">Add Entry</button>

      </form>
  </div>
  </div>

  <div class="udf-table-card">

      <h2>Registered Users</h2>

      <?php if (empty($users)): ?>

          <p class="page-description">No users added yet.</p>

      <?php else: ?>

          <table class="udf-table">
              <thead>
                  <tr>
                      <th>Username</th>
                      <th>Full Name</th>
                      <th>Address</th>
                      <th>Telephone No</th>
                      <th>Email No</th>
                      <th>Created Date</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach ($users as $user): ?>
                      <tr>
                          <td><?= htmlspecialchars($user['username']) ?></td>
                          <td><?= htmlspecialchars($user['full_name']) ?></td>
                          <td><?= htmlspecialchars($user['address']) ?></td>
                          <td><?= htmlspecialchars($user['phone']) ?></td>
                          <td><?= htmlspecialchars($user['email']) ?></td>
                          <td><?= htmlspecialchars(date('d M Y', strtotime($user['created_at']))) ?></td>
                      </tr>
                  <?php endforeach; ?>
              </tbody>
          </table>

      <?php endif; ?>

  </div>

</div>

</div>
</body>
</html>