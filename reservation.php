<?php
require_once 'config.php';
require_once 'auth_guard.php';
require_role('user');

$user_id = $_SESSION['user_id'];
$msg     = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cid  = (int)$_POST['cancel_id'];
    $stmt = $conn->prepare(
        "UPDATE reservations SET status='Cancelled'
         WHERE id=? AND user_id=? AND status='Pending'"
    );
    $stmt->bind_param("ii", $cid, $user_id);
    $stmt->execute();
    $msg      = $stmt->affected_rows > 0 ? 'Reservation cancelled.' : 'Could not cancel (already processed?).';
    $msg_type = $stmt->affected_rows > 0 ? 'ok' : 'err';
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_product_id'])) {
    $pid   = (int)$_POST['reserve_product_id'];
    $qty   = max(1, (int)$_POST['qty']);
    $notes = trim($_POST['notes'] ?? '');

    // Check stock
    $check = $conn->prepare("SELECT quantity, product_name FROM products WHERE id=?");
    $check->bind_param("i", $pid);
    $check->execute();
    $prod = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$prod) {
        $msg = 'Product not found.'; $msg_type = 'err';
    } elseif ($prod['quantity'] < $qty) {
        $msg = "Not enough stock for {$prod['product_name']}. Available: {$prod['quantity']} pcs."; $msg_type = 'err';
    } else {
        $ins = $conn->prepare(
            "INSERT INTO reservations (user_id, product_id, quantity, notes) VALUES (?,?,?,?)"
        );
        $ins->bind_param("iiis", $user_id, $pid, $qty, $notes);
        $ins->execute();
        $msg = "Reserved {$qty} pcs of {$prod['product_name']} successfully!"; $msg_type = 'ok';
        $ins->close();
    }
}

$filter = $_GET['status'] ?? 'All';
$allowed_filters = ['All', 'Pending', 'Approved', 'Cancelled'];
if (!in_array($filter, $allowed_filters)) $filter = 'All';

$where = $filter === 'All' ? '' : "AND r.status = '$filter'";

$reservations = $conn->query(
    "SELECT r.id, p.product_name, p.category, p.price,
            r.quantity, r.status, r.notes, r.reserved_at
     FROM reservations r
     JOIN products p ON p.id = r.product_id
     WHERE r.user_id = $user_id $where
     ORDER BY r.reserved_at DESC"
);

$counts = ['Total'=>0,'Pending'=>0,'Approved'=>0,'Cancelled'=>0];
$all = $conn->query("SELECT status FROM reservations WHERE user_id=$user_id");
while ($row = $all->fetch_assoc()) {
    $counts['Total']++;
    $counts[$row['status']]++;
}

$products = $conn->query(
    "SELECT id, product_name, category, price, quantity
     FROM products WHERE quantity > 0 ORDER BY product_name"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PharmaTrack — My Reservations</title>
<link rel="stylesheet" href="reservation.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  * { font-family: 'Poppins', sans-serif; }

  .flash { padding:12px 18px; border-radius:10px; margin:0 25px 16px; font-size:14px; font-weight:500; }
  .flash.ok  { background:#d1fae5; color:#065f46; }
  .flash.err { background:#fee2e2; color:#991b1b; }

  .tag.active-tag { outline:2px solid #0d5961; }

  .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:999; align-items:center; justify-content:center; }
  .modal-overlay.open { display:flex; }
  .modal-box { background:#fff; border-radius:16px; padding:32px; width:440px; max-width:95vw; }
  .modal-box h3 { font-size:18px; color:#0d5961; margin-bottom:4px; }
  .modal-box p.sub { font-size:13px; color:#888; margin-bottom:20px; }
  .modal-field { margin-bottom:14px; }
  .modal-field label { display:block; font-size:13px; font-weight:600; color:#333; margin-bottom:6px; }
  .modal-field select,
  .modal-field input,
  .modal-field textarea { width:100%; padding:10px 12px; border:1.5px solid #ddd; border-radius:8px; font-size:14px; font-family:inherit; outline:none; }
  .modal-field select:focus,
  .modal-field input:focus,
  .modal-field textarea:focus { border-color:#0d5961; }
  .modal-field textarea { resize:vertical; height:80px; }
  .modal-footer { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; }
  .btn-cancel-modal { padding:10px 18px; border-radius:8px; border:1.5px solid #ddd; background:#fff; cursor:pointer; font-size:14px; }
  .btn-reserve-confirm { padding:10px 20px; border-radius:8px; border:none; background:#0d5961; color:#fff; font-size:14px; font-weight:600; cursor:pointer; }
  .btn-reserve-confirm:hover { background:#0a474e; }

  .reserve-new-btn { background:#0d5961; color:white; border:none; padding:9px 16px; border-radius:8px; cursor:pointer; font-size:13px; font-weight:600; }
  .reserve-new-btn:hover { background:#0a474e; }

  .cancel-btn:disabled { background:#ccc; cursor:not-allowed; }
</style>
</head>
<body>

<nav style="background:#0d5961;color:white;display:flex;justify-content:space-between;align-items:center;padding:16px 32px;position:sticky;top:0;z-index:100;box-shadow:0 2px 12px rgba(0,0,0,.2);">
  <div style="display:flex;align-items:center;gap:36px;">
    <div style="display:flex;align-items:center;gap:10px;font-size:22px;font-weight:800;">
      <div style="width:36px;height:36px;background:white;color:#0d5961;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:900;">+</div>
      PharmaTrack
    </div>
    <div style="display:flex;gap:4px;">
      <a href="user-dashboard.php" style="color:rgba(255,255,255,.75);text-decoration:none;padding:8px 16px;border-radius:8px;font-size:14px;font-weight:500;display:flex;align-items:center;gap:7px;transition:.2s;"><i class="fa-solid fa-house"></i> Dashboard</a>
      <a href="user-products.php" style="color:rgba(255,255,255,.75);text-decoration:none;padding:8px 16px;border-radius:8px;font-size:14px;font-weight:500;display:flex;align-items:center;gap:7px;transition:.2s;"><i class="fa-solid fa-capsules"></i> Medicines</a>
      <a href="reservation.php" style="color:white;text-decoration:none;padding:8px 16px;border-radius:8px;font-size:14px;font-weight:600;background:rgba(255,255,255,.2);display:flex;align-items:center;gap:7px;"><i class="fa-solid fa-calendar-check"></i> My Reservations</a>
    </div>
  </div>
  <div style="display:flex;align-items:center;gap:12px;">
    <button onclick="openReserveModal()" style="background:white;color:#0d5961;border:none;padding:9px 18px;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:7px;font-family:inherit;"><i class="fa-solid fa-plus"></i> New Reservation</button>
    <a href="logout.php" style="color:rgba(255,255,255,.75);text-decoration:none;font-size:13px;padding:8px 14px;border:1px solid rgba(255,255,255,.3);border-radius:8px;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    <div style="width:40px;height:40px;border-radius:50%;background:white;color:#0d5961;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;"><?= strtoupper(substr($_SESSION['username'],0,2)) ?></div>
  </div>
</nav>

<?php if ($msg): ?>
<div class="flash <?= $msg_type ?>">
  <i class="fa-solid fa-<?= $msg_type === 'ok' ? 'circle-check' : 'circle-exclamation' ?>"></i>
  <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="header">
  <h2>My Reservations</h2>
  <p>Track and manage your reserved medicines — logged in as <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
</div>

<div class="container">

  <div class="cards">
    <div class="card">
      <h3>Total Reservations</h3>
      <h1><?= $counts['Total'] ?></h1>
      <p class="green">All orders</p>
    </div>
    <div class="card">
      <h3>Pending</h3>
      <h1><?= $counts['Pending'] ?></h1>
      <p class="orange">Waiting for approval</p>
    </div>
    <div class="card">
      <h3>Approved</h3>
      <h1><?= $counts['Approved'] ?></h1>
      <p class="green">Approved reservations</p>
    </div>
    <div class="card">
      <h3>Cancelled</h3>
      <h1><?= $counts['Cancelled'] ?></h1>
      <p class="red">Cancelled reservations</p>
    </div>
  </div>

  <div class="tags" style="margin-bottom:20px;">
    <?php foreach (['All','Approved','Pending','Cancelled'] as $f):
      $cls = $f === 'All' ? 'green-tag' : ($f === 'Approved' ? 'green-tag' : ($f === 'Pending' ? 'orange-tag' : 'red-tag'));
      $active = $filter === $f ? 'active-tag' : '';
    ?>
    <a href="?status=<?= $f ?>" style="text-decoration:none;">
      <div class="tag <?= $cls ?> <?= $active ?>"><?= $f ?></div>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th>Date</th>
          <th>Quantity</th>
          <th>Total</th>
          <th>Status</th>
          <th>Notes</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($reservations->num_rows === 0): ?>
        <tr>
          <td colspan="7" style="text-align:center;color:#999;padding:30px;">
            No reservations found<?= $filter !== 'All' ? " with status: $filter" : '' ?>.
          </td>
        </tr>
        <?php else: ?>
        <?php while ($r = $reservations->fetch_assoc()):
          $total    = $r['price'] * $r['quantity'];
          $can_cancel = $r['status'] === 'Pending';
        ?>
        <tr>
          <td class="product-name">
            <?= htmlspecialchars($r['product_name']) ?><br>
            <span><?= htmlspecialchars($r['category']) ?></span>
          </td>
          <td><?= date('M d, Y', strtotime($r['reserved_at'])) ?></td>
          <td><?= $r['quantity'] ?> pcs</td>
          <td>₱<?= number_format($total, 2) ?></td>
          <td>
            <span class="status <?= strtolower($r['status']) ?>">
              <?= $r['status'] ?>
            </span>
          </td>
          <td><?= htmlspecialchars($r['notes'] ?: '—') ?></td>
          <td>
            <?php if ($can_cancel): ?>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Cancel this reservation?')">
              <input type="hidden" name="cancel_id" value="<?= $r['id'] ?>">
              <button type="submit" class="update-btn cancel-btn">Cancel</button>
            </form>
            <?php else: ?>
            <button class="update-btn cancel-btn" disabled><?= $r['status'] ?></button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<div class="modal-overlay" id="reserveModal">
  <div class="modal-box">
    <h3><i class="fa-solid fa-calendar-plus"></i> New Reservation</h3>
    <p class="sub">Select a medicine and quantity to reserve</p>

    <form method="POST">
      <div class="modal-field">
        <label>Medicine</label>
        <select name="reserve_product_id" required>
          <option value="" disabled selected>Select a medicine...</option>
          <?php
          
          $prods2 = $conn->query(
              "SELECT id, product_name, category, price, quantity
               FROM products WHERE quantity > 0 ORDER BY product_name"
          );
          while ($p = $prods2->fetch_assoc()):
          ?>
          <option value="<?= $p['id'] ?>"
                  data-price="<?= $p['price'] ?>"
                  data-stock="<?= $p['quantity'] ?>">
            <?= htmlspecialchars($p['product_name']) ?>
            (<?= htmlspecialchars($p['category']) ?>) — ₱<?= number_format($p['price'],2) ?> — <?= $p['quantity'] ?> pcs left
          </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="modal-field">
        <label>Quantity</label>
        <input type="number" name="qty" id="modal-qty" value="1" min="1" required>
      </div>

      <div class="modal-field">
        <label>Notes <span style="font-weight:400;color:#aaa;">(optional)</span></label>
        <textarea name="notes" placeholder="e.g. 3x a day, after meals"></textarea>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-cancel-modal" onclick="closeReserveModal()">Cancel</button>
        <button type="submit" class="btn-reserve-confirm">
          <i class="fa-solid fa-calendar-check"></i> Confirm Reservation
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openReserveModal()  { document.getElementById('reserveModal').classList.add('open'); }
function closeReserveModal() { document.getElementById('reserveModal').classList.remove('open'); }

if (window.location.hash === '#reserve') openReserveModal();

document.getElementById('reserveModal').addEventListener('click', function(e) {
  if (e.target === this) closeReserveModal();
});

document.querySelector('select[name="reserve_product_id"]').addEventListener('change', function() {
  const opt   = this.options[this.selectedIndex];
  const stock = parseInt(opt.dataset.stock) || 1;
  const qtyEl = document.getElementById('modal-qty');
  qtyEl.max   = stock;
  if (parseInt(qtyEl.value) > stock) qtyEl.value = stock;
});
</script>

</body>
</html>