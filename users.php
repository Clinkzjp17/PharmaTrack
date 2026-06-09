<?php
require_once 'config.php';
require_once 'auth_guard.php';
require_role('admin');

$total = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$admins = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='admin'")->fetch_assoc()['c'];
$users  = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'];

$result = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY created_at ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PharmaTrack – Users</title>
  <link rel="stylesheet" href="users.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <?php include 'sidebar.php'; ?>

  <div class="main-content">
    <div class="page-header">
      <div class="page-header-row">
        <h1>Users</h1>
      </div>
    </div>

    <div class="page-body">

      <div class="stat-cards">
        <div class="stat-card">
          <div class="label">Total Users</div>
          <div class="value"><?= $total ?></div>
          <div class="sub green">All accounts</div>
        </div>
        <div class="stat-card">
          <div class="label">Users</div>
          <div class="value"><?= $users ?></div>
          <div class="sub green">Registered Users</div>
        </div>
        <div class="stat-card">
          <div class="label">Admins</div>
          <div class="value"><?= $admins ?></div>
          <div class="sub green">Full access</div>
        </div>
      </div>

      <div class="filter-row">
        <div class="search-box">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" id="search-input" placeholder="Search users..." oninput="filterTable()">
        </div>
        <select class="select-filter" id="role-filter" onchange="filterTable()">
          <option value="">ALL ROLES</option>
          <option value="admin">Admin</option>
          <option value="user">User</option>
        </select>
      </div>

      <div class="panel">
        <table class="data-table" id="users-table">
          <thead>
            <tr>
              <th>Username</th>
              <th>Role</th>
              <th>Joined</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr data-role="<?= $row['role'] ?>" data-name="<?= htmlspecialchars(strtolower($row['username'])) ?>">
              <td>
                <div class="user-name"><?= htmlspecialchars($row['username']) ?></div>
                <div class="user-role"><?= $row['role'] === 'admin' ? 'Administrator' : 'User' ?></div>
              </td>
              <td>
                <span class="badge <?= $row['role']==='admin' ? 'badge-orange' : 'badge-green' ?>">
                  <?= ucfirst($row['role']) ?>
                </span>
              </td>
              <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

  <script>
  function filterTable() {
    const search = document.getElementById('search-input').value.toLowerCase();
    const role   = document.getElementById('role-filter').value;
    document.querySelectorAll('#users-table tbody tr').forEach(row => {
      const nameMatch = row.dataset.name.includes(search);
      const roleMatch = !role || row.dataset.role === role;
      row.style.display = nameMatch && roleMatch ? '' : 'none';
    });
  }
  </script>
</body>
</html>