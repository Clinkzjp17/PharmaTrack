<?php
require_once 'config.php';
require_once 'auth_guard.php';
require_role('user');

$user_id = $_SESSION['user_id'];

$counts = ['Total' => 0, 'Pending' => 0, 'Approved' => 0, 'Cancelled' => 0];
$res = $conn->query("SELECT status, COUNT(*) AS cnt FROM reservations WHERE user_id=$user_id GROUP BY status");
while ($r = $res->fetch_assoc()) {
    $counts[$r['status']] = (int)$r['cnt'];
    $counts['Total'] += (int)$r['cnt'];
}

$totalMeds = $conn->query("SELECT COUNT(*) AS cnt FROM products WHERE quantity > 0")->fetch_assoc()['cnt'];

$recent = $conn->query(
    "SELECT r.id, p.product_name, p.category, r.quantity, r.status, r.reserved_at
     FROM reservations r
     JOIN products p ON p.id = r.product_id
     WHERE r.user_id = $user_id
     ORDER BY r.reserved_at DESC
     LIMIT 3"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PharmaTrack — Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body { background:#eef2f5; min-height:100vh; }

.navbar { background:#0d5961; color:white; display:flex; justify-content:space-between; align-items:center; padding:16px 32px; position:sticky; top:0; z-index:100; box-shadow:0 2px 12px rgba(0,0,0,.2); }
.navbar-left { display:flex; align-items:center; gap:36px; }
.logo { display:flex; align-items:center; gap:10px; font-size:22px; font-weight:800; letter-spacing:-0.5px; }
.logo-box { width:36px; height:36px; background:white; color:#0d5961; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:900; }
.nav-links { display:flex; gap:4px; }
.nav-link { color:rgba(255,255,255,.75); text-decoration:none; padding:8px 16px; border-radius:8px; font-size:14px; font-weight:500; display:flex; align-items:center; gap:7px; transition:.2s; }
.nav-link:hover { background:rgba(255,255,255,.12); color:white; }
.nav-link.active { background:rgba(255,255,255,.2); color:white; font-weight:600; }
.navbar-right { display:flex; align-items:center; gap:12px; }
.avatar { width:40px; height:40px; border-radius:50%; background:white; color:#0d5961; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:15px; }
.logout-link { color:rgba(255,255,255,.75); text-decoration:none; font-size:13px; padding:8px 14px; border:1px solid rgba(255,255,255,.3); border-radius:8px; transition:.2s; }
.logout-link:hover { background:rgba(255,255,255,.1); color:white; }

.hero { background:linear-gradient(135deg,#0d5961 0%,#1d7a84 100%); color:white; padding:40px 40px 30px; margin:28px 28px 0; border-radius:18px; display:flex; justify-content:space-between; align-items:flex-end; }
.hero-text h1 { font-size:32px; font-weight:800; margin-bottom:6px; }
.hero-text p { opacity:.8; font-size:15px; }
.hero-date { font-size:13px; opacity:.6; text-align:right; }

.container { padding:24px 28px 40px; }

.stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:28px; }
.stat-card { background:white; border-radius:14px; padding:22px; box-shadow:0 2px 10px rgba(0,0,0,.06); border-left:4px solid #0d5961; }
.stat-card .label { font-size:12px; text-transform:uppercase; letter-spacing:.06em; color:#888; font-weight:600; margin-bottom:8px; }
.stat-card .value { font-size:36px; font-weight:800; color:#0d5961; margin-bottom:4px; }
.stat-card .sub { font-size:12px; font-weight:600; }
.sub.green { color:#22c55e; } .sub.orange { color:#f59e0b; } .sub.red { color:#ef4444; } .sub.blue { color:#3b82f6; }
.stat-card.orange { border-color:#f59e0b; } .stat-card.orange .value { color:#d97706; }
.stat-card.green-card { border-color:#22c55e; } .stat-card.green-card .value { color:#16a34a; }
.stat-card.red-card { border-color:#ef4444; } .stat-card.red-card .value { color:#dc2626; }

.two-col { display:grid; grid-template-columns:1fr 1fr; gap:20px; }

.panel { background:white; border-radius:14px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,.06); }
.panel-title { font-size:15px; font-weight:700; color:#0d5961; margin-bottom:18px; display:flex; align-items:center; gap:8px; }

.action-list { display:flex; flex-direction:column; gap:12px; }
.action-item { display:flex; align-items:center; gap:16px; padding:16px; border-radius:12px; border:1.5px solid #e5e7eb; text-decoration:none; color:#333; transition:.2s; }
.action-item:hover { border-color:#0d5961; background:#f0fafa; transform:translateX(3px); }
.action-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
.action-icon.teal { background:#e0f2f1; color:#0d5961; }
.action-icon.blue { background:#dbeafe; color:#1d4ed8; }
.action-label { font-weight:600; font-size:14px; }
.action-desc { font-size:12px; color:#888; margin-top:2px; }
.action-arrow { margin-left:auto; color:#aaa; }

.recent-table { width:100%; border-collapse:collapse; }
.recent-table th { font-size:11px; text-transform:uppercase; color:#888; font-weight:600; padding:0 0 10px; text-align:left; border-bottom:1px solid #f0f0f0; }
.recent-table td { padding:12px 0; border-bottom:1px solid #f7f7f7; font-size:13px; }
.recent-table tr:last-child td { border-bottom:none; }
.status-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
.status-badge.pending { background:#fff3cd; color:#856404; }
.status-badge.approved { background:#d4edda; color:#155724; }
.status-badge.cancelled { background:#f8d7da; color:#721c24; }

.btn-reserve { display:inline-flex; align-items:center; gap:6px; background:#0d5961; color:white; padding:10px 20px; border-radius:9px; text-decoration:none; font-size:14px; font-weight:600; transition:.2s; }
.btn-reserve:hover { background:#0a464c; transform:translateY(-1px); }

@media(max-width:900px) { .stat-grid { grid-template-columns:1fr 1fr; } .two-col { grid-template-columns:1fr; } }
@media(max-width:600px) { .stat-grid { grid-template-columns:1fr; } .hero { flex-direction:column; gap:12px; align-items:flex-start; } }
</style>
</head>
<body>

<nav class="navbar">
  <div class="navbar-left">
    <div class="logo">
      <div class="logo-box">+</div>
      PharmaTrack
    </div>
    <div class="nav-links">
      <a href="user-dashboard.php" class="nav-link active"><i class="fa-solid fa-house"></i> Dashboard</a>
      <a href="user-products.php" class="nav-link"><i class="fa-solid fa-capsules"></i> Medicines</a>
      <a href="reservation.php" class="nav-link"><i class="fa-solid fa-calendar-check"></i> My Reservations</a>
    </div>
  </div>
  <div class="navbar-right">
    <div class="avatar"><?= strtoupper(substr($_SESSION['username'],0,2)) ?></div>
    <a href="logout.php" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </div>
</nav>

<div class="hero">
  <div class="hero-text">
    <h1>Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
    <p>Manage your reservations and browse available medicines.</p>
  </div>
  <div class="hero-date">
    <div><?= date('l') ?></div>
    <div style="font-size:15px;opacity:.8;"><?= date('F j, Y') ?></div>
  </div>
</div>

<div class="container">

  <div class="stat-grid">
    <div class="stat-card">
      <div class="label">Total Reservations</div>
      <div class="value"><?= $counts['Total'] ?></div>
      <div class="sub blue">All time</div>
    </div>
    <div class="stat-card orange">
      <div class="label">Pending</div>
      <div class="value"><?= $counts['Pending'] ?></div>
      <div class="sub orange">Awaiting approval</div>
    </div>
    <div class="stat-card green-card">
      <div class="label">Approved</div>
      <div class="value"><?= $counts['Approved'] ?></div>
      <div class="sub green">Ready to claim</div>
    </div>
    <div class="stat-card red-card">
      <div class="label">Cancelled</div>
      <div class="value"><?= $counts['Cancelled'] ?></div>
      <div class="sub red">Not processed</div>
    </div>
  </div>

  <div class="two-col">

    <div class="panel">
      <div class="panel-title"><i class="fa-solid fa-bolt"></i> Quick Actions</div>
      <div class="action-list">
        <a href="reservation.php" class="action-item">
          <div class="action-icon teal"><i class="fa-solid fa-calendar-check"></i></div>
          <div>
            <div class="action-label">My Reservations</div>
            <div class="action-desc">View and manage your reservations</div>
          </div>
          <i class="fa-solid fa-chevron-right action-arrow"></i>
        </a>
        <a href="user-products.php" class="action-item">
          <div class="action-icon blue"><i class="fa-solid fa-capsules"></i></div>
          <div>
            <div class="action-label">Browse Medicines</div>
            <div class="action-desc"><?= $totalMeds ?> medicines available right now</div>
          </div>
          <i class="fa-solid fa-chevron-right action-arrow"></i>
        </a>
        <a href="reservation.php#reserve" class="action-item">
          <div class="action-icon teal"><i class="fa-solid fa-plus"></i></div>
          <div>
            <div class="action-label">New Reservation</div>
            <div class="action-desc">Reserve a medicine for pickup</div>
          </div>
          <i class="fa-solid fa-chevron-right action-arrow"></i>
        </a>
      </div>
    </div>

    <div class="panel">
      <div class="panel-title" style="justify-content:space-between;">
        <span><i class="fa-solid fa-clock-rotate-left"></i> Recent Reservations</span>
        <a href="reservation.php" style="font-size:12px;color:#0d5961;text-decoration:none;font-weight:600;">View all →</a>
      </div>
      <?php if ($recent->num_rows === 0): ?>
        <p style="color:#aaa;font-size:13px;text-align:center;padding:24px 0;">No reservations yet. <a href="user-products.php" style="color:#0d5961;">Browse medicines →</a></p>
      <?php else: ?>
      <table class="recent-table">
        <thead><tr><th>Medicine</th><th>Qty</th><th>Status</th></tr></thead>
        <tbody>
          <?php while ($r = $recent->fetch_assoc()): ?>
          <tr>
            <td>
              <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($r['product_name']) ?></div>
              <div style="font-size:11px;color:#888;"><?= htmlspecialchars($r['category']) ?></div>
            </td>
            <td><?= $r['quantity'] ?> pcs</td>
            <td><span class="status-badge <?= strtolower($r['status']) ?>"><?= $r['status'] ?></span></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

  </div>
</div>

</body>
</html>
