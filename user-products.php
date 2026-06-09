<?php
require_once 'config.php';
require_once 'auth_guard.php';
require_role('user');

$user_id = $_SESSION['user_id'];
$msg = ''; $msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_product_id'])) {
    $pid   = (int)$_POST['reserve_product_id'];
    $qty   = max(1, (int)$_POST['qty']);
    $notes = trim($_POST['notes'] ?? '');

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
        $ins = $conn->prepare("INSERT INTO reservations (user_id, product_id, quantity, notes) VALUES (?,?,?,?)");
        $ins->bind_param("iiis", $user_id, $pid, $qty, $notes);
        $ins->execute();
        $msg = "Reserved {$qty} pcs of {$prod['product_name']} successfully!"; $msg_type = 'ok';
        $ins->close();
    }
}

$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');

$where = ["quantity > 0"];
$params = []; $types = '';
if ($search) { $where[] = "(product_name LIKE ? OR category LIKE ?)"; $like = "%$search%"; $params[] = $like; $params[] = $like; $types .= 'ss'; }
if ($category && $category !== 'ALL') { $where[] = "category = ?"; $params[] = $category; $types .= 's'; }

$sql = "SELECT * FROM products WHERE " . implode(' AND ', $where) . " ORDER BY product_name ASC";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$categories = [];
$catRes = $conn->query("SELECT DISTINCT category FROM products WHERE quantity > 0 ORDER BY category");
while ($r = $catRes->fetch_assoc()) $categories[] = $r['category'];

$preselect = (int)($_GET['reserve'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PharmaTrack — Available Medicines</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body { background:#eef2f5; min-height:100vh; }

.navbar { background:#0d5961; color:white; display:flex; justify-content:space-between; align-items:center; padding:16px 32px; position:sticky; top:0; z-index:100; box-shadow:0 2px 12px rgba(0,0,0,.2); }
.navbar-left { display:flex; align-items:center; gap:36px; }
.logo { display:flex; align-items:center; gap:10px; font-size:22px; font-weight:800; }
.logo-box { width:36px; height:36px; background:white; color:#0d5961; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:900; }
.nav-links { display:flex; gap:4px; }
.nav-link { color:rgba(255,255,255,.75); text-decoration:none; padding:8px 16px; border-radius:8px; font-size:14px; font-weight:500; display:flex; align-items:center; gap:7px; transition:.2s; }
.nav-link:hover { background:rgba(255,255,255,.12); color:white; }
.nav-link.active { background:rgba(255,255,255,.2); color:white; font-weight:600; }
.navbar-right { display:flex; align-items:center; gap:12px; }
.avatar { width:40px; height:40px; border-radius:50%; background:white; color:#0d5961; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:15px; }
.logout-link { color:rgba(255,255,255,.75); text-decoration:none; font-size:13px; padding:8px 14px; border:1px solid rgba(255,255,255,.3); border-radius:8px; transition:.2s; }
.logout-link:hover { background:rgba(255,255,255,.1); color:white; }

.page-hero { background:linear-gradient(135deg,#0d5961,#1d7a84); color:white; padding:32px 36px; margin:24px 28px 0; border-radius:16px; display:flex; justify-content:space-between; align-items:center; }
.page-hero h1 { font-size:26px; font-weight:800; margin-bottom:4px; }
.page-hero p { opacity:.8; font-size:14px; }

.container { padding:20px 28px 40px; }

.filter-bar { display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; }
.search-wrap { flex:1; min-width:200px; position:relative; }
.search-wrap i { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#aaa; }
.search-input { width:100%; padding:11px 14px 11px 38px; border:1.5px solid #e5e7eb; border-radius:10px; font-size:14px; font-family:inherit; outline:none; background:white; }
.search-input:focus { border-color:#0d5961; }
.cat-select { padding:11px 14px; border:1.5px solid #e5e7eb; border-radius:10px; font-size:14px; font-family:inherit; background:white; outline:none; min-width:170px; }
.cat-select:focus { border-color:#0d5961; }
.search-btn { padding:11px 20px; background:#0d5961; color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; font-family:inherit; }

.products-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(270px,1fr)); gap:18px; }
.product-card { background:white; border-radius:14px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.07); transition:.25s; }
.product-card:hover { transform:translateY(-4px); box-shadow:0 8px 24px rgba(0,0,0,.12); }
.card-header { background:#0d5961; color:white; padding:18px 20px; }
.card-header h3 { font-size:16px; font-weight:700; margin-bottom:2px; }
.card-header span { font-size:12px; opacity:.75; }
.card-body { padding:18px 20px; display:flex; flex-direction:column; gap:8px; }
.card-row { display:flex; justify-content:space-between; font-size:13px; }
.card-row .lbl { color:#888; }
.card-row .val { font-weight:600; color:#222; }
.stock-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
.stock-badge.in { background:#d1fae5; color:#065f46; }
.stock-badge.low { background:#fef9c3; color:#854d0e; }
.card-footer { padding:12px 20px 18px; }
.reserve-btn { display:block; text-align:center; background:#0d5961; color:white; padding:11px; border-radius:9px; font-size:14px; font-weight:600; text-decoration:none; border:none; cursor:pointer; width:100%; font-family:inherit; transition:.2s; }
.reserve-btn:hover { background:#0a464c; }

.flash { padding:13px 20px; border-radius:10px; margin-bottom:16px; font-size:14px; font-weight:500; display:flex; align-items:center; gap:10px; }
.flash.ok  { background:#d1fae5; color:#065f46; }
.flash.err { background:#fee2e2; color:#991b1b; }

.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:999; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background:white; border-radius:16px; padding:32px; width:440px; max-width:95vw; box-shadow:0 20px 60px rgba(0,0,0,.2); }
.modal-box h3 { font-size:18px; color:#0d5961; font-weight:700; margin-bottom:4px; }
.modal-box .sub { font-size:13px; color:#888; margin-bottom:22px; }
.modal-field { margin-bottom:16px; }
.modal-field label { display:block; font-size:13px; font-weight:600; color:#333; margin-bottom:6px; }
.modal-field select, .modal-field input, .modal-field textarea { width:100%; padding:10px 12px; border:1.5px solid #ddd; border-radius:9px; font-size:14px; font-family:inherit; outline:none; }
.modal-field select:focus, .modal-field input:focus, .modal-field textarea:focus { border-color:#0d5961; }
.modal-field textarea { resize:vertical; height:80px; }
.stock-hint { font-size:12px; color:#0d5961; margin-top:5px; font-weight:600; }
.modal-footer { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; }
.btn-cancel { padding:10px 18px; border-radius:8px; border:1.5px solid #ddd; background:white; cursor:pointer; font-size:14px; font-family:inherit; }
.btn-confirm { padding:10px 22px; border-radius:8px; border:none; background:#0d5961; color:white; font-size:14px; font-weight:600; cursor:pointer; font-family:inherit; }
.btn-confirm:hover { background:#0a464c; }

.empty { text-align:center; padding:60px 20px; color:#aaa; }
.empty i { font-size:48px; margin-bottom:16px; display:block; }
</style>
</head>
<body>

<nav class="navbar">
  <div class="navbar-left">
    <div class="logo"><div class="logo-box">+</div> PharmaTrack</div>
    <div class="nav-links">
      <a href="user-dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
      <a href="user-products.php" class="nav-link active"><i class="fa-solid fa-capsules"></i> Medicines</a>
      <a href="reservation.php" class="nav-link"><i class="fa-solid fa-calendar-check"></i> My Reservations</a>
    </div>
  </div>
  <div class="navbar-right">
    <div class="avatar"><?= strtoupper(substr($_SESSION['username'],0,2)) ?></div>
    <a href="logout.php" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </div>
</nav>

<div class="page-hero">
  <div>
    <h1><i class="fa-solid fa-capsules"></i> Available Medicines</h1>
    <p>Browse and reserve medicines for pickup</p>
  </div>
  <button class="reserve-btn" style="width:auto;padding:11px 22px;" onclick="openModal(0)">
    <i class="fa-solid fa-plus"></i> New Reservation
  </button>
</div>

<div class="container">

  <?php if ($msg): ?>
  <div class="flash <?= $msg_type ?>">
    <i class="fa-solid fa-<?= $msg_type === 'ok' ? 'circle-check' : 'circle-exclamation' ?>"></i>
    <?= htmlspecialchars($msg) ?>
    <?php if ($msg_type === 'ok'): ?>
      &nbsp;— <a href="reservation.php" style="color:inherit;font-weight:700;">View My Reservations →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <form class="filter-bar" method="GET">
    <div class="search-wrap">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input class="search-input" type="text" name="search" placeholder="Search medicine..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <select class="cat-select" name="category" onchange="this.form.submit()">
      <option value="ALL">All Categories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="search-btn" type="submit"><i class="fa-solid fa-search"></i> Search</button>
  </form>

  <?php if ($result->num_rows === 0): ?>
    <div class="empty">
      <i class="fa-solid fa-capsules"></i>
      <p>No medicines found<?= $search ? " for \"$search\"" : '' ?>.</p>
    </div>
  <?php else: ?>
  <div class="products-grid">
    <?php while ($row = $result->fetch_assoc()): ?>
    <div class="product-card">
      <div class="card-header">
        <h3><?= htmlspecialchars($row['product_name']) ?></h3>
        <span><?= htmlspecialchars($row['category']) ?></span>
      </div>
      <div class="card-body">
        <div class="card-row"><span class="lbl">Price</span><span class="val">₱<?= number_format($row['price'],2) ?></span></div>
        <div class="card-row"><span class="lbl">Available</span><span class="val"><?= $row['quantity'] ?> pcs</span></div>
        <div class="card-row"><span class="lbl">Status</span>
          <span class="stock-badge <?= $row['quantity'] <= 40 ? 'low' : 'in' ?>">
            <?= $row['quantity'] <= 40 ? 'Low Stock' : 'In Stock' ?>
          </span>
        </div>
      </div>
      <div class="card-footer">
        <button class="reserve-btn" onclick="openModal(<?= $row['id'] ?>, '<?= addslashes($row['product_name']) ?>', <?= $row['quantity'] ?>, <?= $row['price'] ?>)">
          <i class="fa-solid fa-calendar-plus"></i> Reserve
        </button>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
  <?php endif; ?>

</div>

<div class="modal-overlay" id="reserveModal">
  <div class="modal-box">
    <h3><i class="fa-solid fa-calendar-plus"></i> New Reservation</h3>
    <p class="sub">Select a medicine and quantity to reserve for pickup</p>

    <form method="POST">
      <div class="modal-field">
        <label>Medicine</label>
        <select name="reserve_product_id" id="modal-product" required onchange="updateStock(this)">
          <option value="" disabled selected>Select a medicine...</option>
          <?php
          $allProds = $conn->query("SELECT id, product_name, category, price, quantity FROM products WHERE quantity > 0 ORDER BY product_name");
          while ($p = $allProds->fetch_assoc()): ?>
          <option value="<?= $p['id'] ?>"
                  data-stock="<?= $p['quantity'] ?>"
                  data-price="<?= $p['price'] ?>"
                  data-name="<?= htmlspecialchars($p['product_name']) ?>">
            <?= htmlspecialchars($p['product_name']) ?> (<?= htmlspecialchars($p['category']) ?>) — ₱<?= number_format($p['price'],2) ?>
          </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div id="stock-hint" class="stock-hint" style="display:none;margin-top:-8px;margin-bottom:12px;"></div>
      <div class="modal-field">
        <label>Quantity</label>
        <input type="number" name="qty" id="modal-qty" value="1" min="1" required>
      </div>
      <div class="modal-field">
        <label>Notes <span style="font-weight:400;color:#aaa;">(optional)</span></label>
        <textarea name="notes" placeholder="e.g. 3x a day, after meals"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn-confirm"><i class="fa-solid fa-calendar-check"></i> Confirm Reservation</button>
      </div>
    </form>
  </div>
</div>

<script>
const preselect = <?= $preselect ?>;

function openModal(id, name, stock, price) {
  const sel = document.getElementById('modal-product');
  if (id) {
    for (let i = 0; i < sel.options.length; i++) {
      if (parseInt(sel.options[i].value) === id) {
        sel.selectedIndex = i;
        break;
      }
    }
    updateStock(sel);
  } else {
    sel.selectedIndex = 0;
    document.getElementById('stock-hint').style.display = 'none';
    document.getElementById('modal-qty').max = '';
  }
  document.getElementById('reserveModal').classList.add('open');
}

function closeModal() {
  document.getElementById('reserveModal').classList.remove('open');
}

function updateStock(sel) {
  const opt   = sel.options[sel.selectedIndex];
  const stock = parseInt(opt.dataset.stock) || 1;
  const price = parseFloat(opt.dataset.price) || 0;
  const hint  = document.getElementById('stock-hint');
  const qtyEl = document.getElementById('modal-qty');
  qtyEl.max = stock;
  if (parseInt(qtyEl.value) > stock) qtyEl.value = stock;
  hint.textContent = `${stock} pcs available · ₱${price.toFixed(2)} each`;
  hint.style.display = 'block';
}

document.getElementById('reserveModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

if (preselect) { openModal(preselect); }
</script>

</body>
</html>
