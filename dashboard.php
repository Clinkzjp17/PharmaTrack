<?php
require_once 'config.php';
require_once 'auth_guard.php';
require_role('admin');

$counts = ['total' => 0, 'In Stock' => 0, 'Low Stock' => 0, 'Out of Stock' => 0];
$cntRes = $conn->query("SELECT status, COUNT(*) AS cnt FROM products GROUP BY status");
while ($r = $cntRes->fetch_assoc()) {
    $counts[$r['status']] = (int)$r['cnt'];
    $counts['total']     += (int)$r['cnt'];
}

$pendingRes = $conn->query("SELECT COUNT(*) AS cnt FROM reservations WHERE status='Pending'")->fetch_assoc()['cnt'];

$invRes = $conn->query(
    "SELECT product_name, category, quantity, price, status
     FROM products
     ORDER BY product_name ASC
     LIMIT 10"
);

$alertRes = $conn->query(
    "SELECT product_name, status, quantity
     FROM products
     WHERE status IN ('Low Stock','Out of Stock')
     ORDER BY FIELD(status,'Out of Stock','Low Stock'), product_name ASC
     LIMIT 8"
);

$stockInRes = $conn->query(
    "SELECT sl.quantity, sl.date, p.id AS pid, p.product_name, p.category
     FROM stock_logs sl
     JOIN products p ON p.id = sl.product_id
     WHERE sl.type = 'in'
     ORDER BY sl.created_at DESC
     LIMIT 5"
);

$stockOutRes = $conn->query(
    "SELECT sl.quantity, sl.date, p.id AS pid, p.product_name, p.category
     FROM stock_logs sl
     JOIN products p ON p.id = sl.product_id
     WHERE sl.type = 'out'
     ORDER BY sl.created_at DESC
     LIMIT 5"
);

function badgeClass(string $s): string {
    return match($s) {
        'In Stock'     => 'badge-green',
        'Low Stock'    => 'badge-orange',
        'Out of Stock' => 'badge-red',
        default        => 'badge-green',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PharmaTrack – Admin Dashboard</title>
  <link rel="stylesheet" href="dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

  <div class="page-header">
    <h1>Admin Dashboard</h1>
  </div>

  <div class="page-body">

    <div class="stat-cards">
      <div class="stat-card">
        <div class="label">Total Products</div>
        <div class="value"><?= $counts['total'] ?></div>
        <div class="sub green">All registered products</div>
      </div>
      <div class="stat-card">
        <div class="label">In Stock</div>
        <div class="value"><?= $counts['In Stock'] ?></div>
        <div class="sub green">
          <?= $counts['total'] > 0 ? number_format($counts['In Stock'] / $counts['total'] * 100, 2) : 0 ?>% of total products
        </div>
      </div>
      <div class="stat-card">
        <div class="label">Low Stock</div>
        <div class="value"><?= $counts['Low Stock'] ?></div>
        <div class="sub orange">Needs restocking</div>
      </div>
      <div class="stat-card">
        <div class="label">Out of Stock</div>
        <div class="value"><?= $counts['Out of Stock'] ?></div>
        <div class="sub red">Urgent reorder</div>
      </div>
      <div class="stat-card">
        <div class="label">Pending Reservations</div>
        <div class="value"><?= $pendingRes ?></div>
        <div class="sub orange"><a href="reservations_admin.php?status=Pending" style="color:inherit;">Needs review →</a></div>
      </div>
    </div>

    <div class="content-grid" style="margin-bottom:16px;">

      <div class="panel">
        <div class="panel-header">Medicine Inventory</div>
        <table class="data-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Category</th>
              <th>Quantity</th>
              <th>Price</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($invRes->num_rows === 0): ?>
              <tr><td colspan="5" style="text-align:center;color:#888;padding:1.5rem;">No products found.</td></tr>
            <?php else: ?>
              <?php while ($row = $invRes->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= (int)$row['quantity'] ?></td>
                <td>₱<?= number_format((float)$row['price'], 2) ?></td>
                <td><span class="badge <?= badgeClass($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
              </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="panel">
        <div class="panel-header">Alerts</div>
        <ul class="alert-list">
          <?php if ($alertRes->num_rows === 0): ?>
            <li><span style="color:#888;">No alerts — all stock levels OK.</span></li>
          <?php else: ?>
            <?php while ($a = $alertRes->fetch_assoc()): ?>
            <li>
              <span>
                <?= htmlspecialchars($a['product_name']) ?>
                <?= $a['status'] === 'Out of Stock'
                    ? 'is out of stock'
                    : 'is low on stock (' . (int)$a['quantity'] . ' pcs left)' ?>
              </span>
              <span class="date"><?= date('F j, Y') ?></span>
            </li>
            <?php endwhile; ?>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <div class="content-grid">

      <div class="panel">
        <div class="panel-header">Recent Stock In</div>
        <ul class="alert-list stock-list">
          <?php if ($stockInRes->num_rows === 0): ?>
            <li><span style="color:#888;">No recent stock-in records.</span></li>
          <?php else: ?>
            <?php while ($si = $stockInRes->fetch_assoc()): ?>
            <li>
              <div class="medicine-info">
                <div class="name"><?= htmlspecialchars($si['product_name']) ?></div>
                <div class="cat"><?= htmlspecialchars($si['category']) ?></div>
              </div>
              <span class="qty">+<?= (int)$si['quantity'] ?> pcs</span>
              <span class="date"><?= date('F j, Y', strtotime($si['date'])) ?></span>
              <a href="stocks.php"><button class="btn-stock-in">Stock in</button></a>
            </li>
            <?php endwhile; ?>
          <?php endif; ?>
        </ul>
      </div>

      <div class="panel">
        <div class="panel-header">Recent Stock Out</div>
        <ul class="alert-list stock-list">
          <?php if ($stockOutRes->num_rows === 0): ?>
            <li><span style="color:#888;">No recent stock-out records.</span></li>
          <?php else: ?>
            <?php while ($so = $stockOutRes->fetch_assoc()): ?>
            <li>
              <div class="medicine-info">
                <div class="name"><?= htmlspecialchars($so['product_name']) ?></div>
                <div class="cat"><?= htmlspecialchars($so['category']) ?></div>
              </div>
              <span class="qty">-<?= (int)$so['quantity'] ?> pcs</span>
              <span class="date"><?= date('F j, Y', strtotime($so['date'])) ?></span>
              <a href="stocks.php"><button class="btn-stock-out">Stock out</button></a>
            </li>
            <?php endwhile; ?>
          <?php endif; ?>
        </ul>
      </div>

    </div>
  </div>
</div>

</body>
</html>
