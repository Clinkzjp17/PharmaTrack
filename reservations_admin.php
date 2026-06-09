<?php
require_once 'config.php';
require_once 'auth_guard.php';
require_role('admin');

$msg = ''; $msg_type = '';

// ── APPROVE / CANCEL via POST ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($id && in_array($action, ['Approved', 'Cancelled'])) {
        // If approving, deduct stock
        if ($action === 'Approved') {
          $rStmt = $conn->prepare("SELECT product_id, quantity, status FROM reservations WHERE id=?");
          $rStmt->bind_param("i", $id);
          $rStmt->execute();
          $res = $rStmt->get_result()->fetch_assoc();
          $rStmt->close();

          if ($res && $res['status'] === 'Pending') {
            $dStmt = $conn->prepare("UPDATE products SET quantity = GREATEST(0, quantity - ?) WHERE id=?");
            $dStmt->bind_param("ii", $res['quantity'], $res['product_id']);
            $dStmt->execute();
            $dStmt->close();

            $conn->query("UPDATE products SET status = CASE
            WHEN quantity = 0   THEN 'Out of Stock'
            WHEN quantity <= 40 THEN 'Low Stock'
            ELSE 'In Stock' END WHERE id=" . (int)$res['product_id']);

            $logDate = date('Y-m-d');
            $logType = 'out';
            $note    = "Reservation #$id approved";
            $logStmt = $conn->prepare("INSERT INTO stock_logs (product_id, type, quantity, date, notes) VALUES (?,?,?,?,?)");
            $logStmt->bind_param("iisss", $res['product_id'], $logType, $res['quantity'], $logDate, $note);
            $logStmt->execute();
            $logStmt->close();
          }
        }

        $uStmt = $conn->prepare("UPDATE reservations SET status=? WHERE id=?");
        $uStmt->bind_param("si", $action, $id);
        $uStmt->execute();
        $uStmt->close();

        $msg = "Reservation #$id marked as $action.";
        $msg_type = $action === 'Approved' ? 'ok' : 'warn';
      }
}

// ── FILTERS ────────────────────────────────────────────────────────────────
$filter = $_GET['status'] ?? 'All';
$allowed = ['All','Pending','Approved','Cancelled'];
if (!in_array($filter, $allowed)) $filter = 'All';
$where = $filter === 'All' ? '' : "WHERE r.status = '$filter'";

// ── RESERVATIONS ───────────────────────────────────────────────────────────
$reservations = $conn->query(
    "SELECT r.id, u.username, p.product_name, p.category, p.price,
            r.quantity, r.status, r.notes, r.reserved_at
     FROM reservations r
     JOIN users u ON u.id = r.user_id
     JOIN products p ON p.id = r.product_id
     $where
     ORDER BY r.reserved_at DESC"
);

// ── COUNTS ─────────────────────────────────────────────────────────────────
$counts = ['Total'=>0,'Pending'=>0,'Approved'=>0,'Cancelled'=>0];
$cRes = $conn->query("SELECT status, COUNT(*) AS cnt FROM reservations GROUP BY status");
while ($r = $cRes->fetch_assoc()) {
    $counts[$r['status']] = (int)$r['cnt'];
    $counts['Total'] += (int)$r['cnt'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PharmaTrack — Reservations</title>
<link rel="stylesheet" href="dashboard.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.flash { padding:12px 20px; border-radius:10px; margin-bottom:18px; font-size:14px; font-weight:500; display:flex; align-items:center; gap:10px; }
.flash.ok   { background:#d1fae5; color:#065f46; }
.flash.warn { background:#fef9c3; color:#854d0e; }
.filter-tabs { display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap; }
.filter-tab { padding:8px 18px; border-radius:20px; font-size:13px; font-weight:600; text-decoration:none; transition:.2s; }
.filter-tab.all      { background:#e0f2f1; color:#0d5961; }
.filter-tab.pending  { background:#fff3cd; color:#856404; }
.filter-tab.approved { background:#d4edda; color:#155724; }
.filter-tab.cancelled{ background:#f8d7da; color:#721c24; }
.filter-tab.active   { outline:2.5px solid #0d5961; }
.badge-pending  { background:#fff3cd; color:#856404; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; }
.badge-approved { background:#d4edda; color:#155724; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; }
.badge-cancelled{ background:#f8d7da; color:#721c24; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; }
.btn-approve { background:#16a34a; color:white; border:none; padding:7px 14px; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer; font-family:inherit; }
.btn-approve:hover { background:#15803d; }
.btn-deny    { background:#dc2626; color:white; border:none; padding:7px 14px; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer; font-family:inherit; margin-left:6px; }
.btn-deny:hover { background:#b91c1c; }
.stat-cards { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="page-header">
    <h1>Reservations</h1>
  </div>

  <div class="page-body">

    <?php if ($msg): ?>
    <div class="flash <?= $msg_type ?>">
      <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <!-- STAT CARDS -->
    <div class="stat-cards">
      <div class="stat-card">
        <div class="label">Total</div>
        <div class="value"><?= $counts['Total'] ?></div>
        <div class="sub green">All reservations</div>
      </div>
      <div class="stat-card">
        <div class="label">Pending</div>
        <div class="value"><?= $counts['Pending'] ?></div>
        <div class="sub orange">Needs action</div>
      </div>
      <div class="stat-card">
        <div class="label">Approved</div>
        <div class="value"><?= $counts['Approved'] ?></div>
        <div class="sub green">Stock deducted</div>
      </div>
      <div class="stat-card">
        <div class="label">Cancelled</div>
        <div class="value"><?= $counts['Cancelled'] ?></div>
        <div class="sub red">By user or admin</div>
      </div>
    </div>

    <!-- FILTER TABS -->
    <div class="filter-tabs">
      <?php foreach (['All','Pending','Approved','Cancelled'] as $f):
        $cls = ['All'=>'all','Pending'=>'pending','Approved'=>'approved','Cancelled'=>'cancelled'][$f];
        $active = $filter === $f ? 'active' : '';
      ?>
      <a href="?status=<?= $f ?>" class="filter-tab <?= $cls ?> <?= $active ?>">
        <?= $f ?> (<?= $f === 'All' ? $counts['Total'] : ($counts[$f] ?? 0) ?>)
      </a>
      <?php endforeach; ?>
    </div>

    <!-- TABLE -->
    <div class="panel">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Medicine</th>
            <th>Qty</th>
            <th>Total</th>
            <th>Date</th>
            <th>Notes</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($reservations->num_rows === 0): ?>
          <tr><td colspan="9" style="text-align:center;color:#aaa;padding:30px;">No reservations<?= $filter !== 'All' ? " with status: $filter" : '' ?>.</td></tr>
          <?php else: ?>
          <?php while ($r = $reservations->fetch_assoc()):
            $total = $r['price'] * $r['quantity'];
            $badgeCls = 'badge-' . strtolower($r['status']);
          ?>
          <tr>
            <td style="color:#aaa;font-size:12px;">#<?= $r['id'] ?></td>
            <td style="font-weight:600;"><?= htmlspecialchars($r['username']) ?></td>
            <td>
              <div style="font-weight:600;"><?= htmlspecialchars($r['product_name']) ?></div>
              <div style="font-size:12px;color:#aaa;"><?= htmlspecialchars($r['category']) ?></div>
            </td>
            <td><?= $r['quantity'] ?> pcs</td>
            <td>₱<?= number_format($total,2) ?></td>
            <td style="font-size:13px;"><?= date('M d, Y', strtotime($r['reserved_at'])) ?></td>
            <td style="font-size:12px;color:#888;"><?= htmlspecialchars($r['notes'] ?: '—') ?></td>
            <td><span class="<?= $badgeCls ?>"><?= $r['status'] ?></span></td>
            <td>
              <?php if ($r['status'] === 'Pending'): ?>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Approve reservation #<?= $r['id'] ?>? This will deduct stock.')">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <input type="hidden" name="action" value="Approved">
                <button type="submit" class="btn-approve"><i class="fa-solid fa-check"></i> Approve</button>
              </form>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Cancel this reservation?')">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <input type="hidden" name="action" value="Cancelled">
                <button type="submit" class="btn-deny"><i class="fa-solid fa-xmark"></i> Cancel</button>
              </form>
              <?php else: ?>
              <span style="font-size:12px;color:#aaa;"><?= $r['status'] ?></span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

</body>
</html>