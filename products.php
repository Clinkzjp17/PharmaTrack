<?php require_once 'config.php';
require_once 'auth_guard.php'; 
require_role('admin'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PharmaTrack – Products</title>
  <link rel="stylesheet" href="product.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php

$host   = 'localhost';
$db     = 'pharmatrack';   
$user   = 'root';          
$pass   = '';              

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('<p style="color:red;padding:2rem;">Database connection failed: ' . $conn->connect_error . '</p>');
}

$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$status   = isset($_GET['status'])   ? trim($_GET['status'])   : '';
$tab      = isset($_GET['tab'])      ? trim($_GET['tab'])      : 'all';

$where = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[]  = '(product_name LIKE ? OR category LIKE ?)';
    $like     = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

if ($category !== '' && $category !== 'ALL CATEGORIES') {
    $where[]  = 'category = ?';
    $params[] = $category;
    $types   .= 's';
}

if ($tab !== 'all') {
    $statusMap = [
        'in-stock'     => 'In Stock',
        'low-stock'    => 'Low Stock',
        'out-of-stock' => 'Out of Stock',
    ];
    if (isset($statusMap[$tab])) {
        $where[]  = 'status = ?';
        $params[] = $statusMap[$tab];
        $types   .= 's';
    }
} elseif ($status !== '' && $status !== 'ALL STATUS') {
    $where[]  = 'status = ?';
    $params[] = $status;
    $types   .= 's';
}

$sql = 'SELECT * FROM products';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY product_name ASC';

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$counts = ['all' => 0, 'In Stock' => 0, 'Low Stock' => 0, 'Out of Stock' => 0];
$countRes = $conn->query('SELECT status, COUNT(*) as cnt FROM products GROUP BY status');
while ($row = $countRes->fetch_assoc()) {
    $counts[$row['status']] = (int)$row['cnt'];
    $counts['all'] += (int)$row['cnt'];
}

$categories = [];
$catRes = $conn->query('SELECT DISTINCT category FROM products ORDER BY category');
while ($row = $catRes->fetch_assoc()) {
    $categories[] = $row['category'];
}

function badgeClass(string $status): string {
    return match ($status) {
        'In Stock'     => 'badge-green',
        'Low Stock'    => 'badge-yellow',
        'Out of Stock' => 'badge-red',
        default        => 'badge-green',
    };
}

include 'sidebar.php';
?>

<div class="main-content">

  <div class="page-header">
    <div class="page-header-row">
      <h1>Products</h1>
      <div class="header-actions">
        <button class="btn-add-product" onclick="openAddModal()">
          <i class="fa-solid fa-plus"></i> Add Product
        </button>
        <button class="btn-batch-update" onclick="openBatchModal()">
          <i class="fa-solid fa-pen-to-square"></i> Batch Update
        </button>
      </div>
    </div>
  </div>

  <div class="page-body">

    <form method="GET" action="products.php" class="filter-row">
      
      <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">

      <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input
          type="text"
          name="search"
          placeholder="Search medicine..."
          value="<?= htmlspecialchars($search) ?>"
        >
      </div>

      <select name="category" class="select-filter" onchange="this.form.submit()">
        <option value="">ALL CATEGORIES</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= htmlspecialchars($cat) ?>"
            <?= ($category === $cat) ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select name="status" class="select-filter" onchange="this.form.submit()">
        <option value="">ALL STATUS</option>
        <option value="In Stock"     <?= ($status === 'In Stock')     ? 'selected' : '' ?>>In Stock</option>
        <option value="Low Stock"    <?= ($status === 'Low Stock')    ? 'selected' : '' ?>>Low Stock</option>
        <option value="Out of Stock" <?= ($status === 'Out of Stock') ? 'selected' : '' ?>>Out of Stock</option>
      </select>

      <button type="submit" style="display:none;">Search</button>
    </form>

    <div class="status-tabs">
      <?php
        $tabs = [
          'all'          => 'All',
          'in-stock'     => 'In Stock',
          'low-stock'    => 'Low Stock',
          'out-of-stock' => 'Out of Stock',
        ];
        $countKeys = [
          'all'          => $counts['all'],
          'in-stock'     => $counts['In Stock'],
          'low-stock'    => $counts['Low Stock'],
          'out-of-stock' => $counts['Out of Stock'],
        ];
        foreach ($tabs as $key => $label):
          $activeClass = ($tab === $key) ? 'active' : '';
          $tabUrl = '?' . http_build_query(array_merge($_GET, ['tab' => $key, 'status' => '']));
      ?>
        <a href="<?= $tabUrl ?>" class="status-tab <?= $key ?> <?= $activeClass ?>">
          <?= $label ?> (<?= $countKeys[$key] ?>)
        </a>
      <?php endforeach; ?>
    </div>

    <div class="panel">
      <table class="data-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows === 0): ?>
            <tr>
              <td colspan="6" style="text-align:center; padding: 2rem; color: #888;">
                No products found.
              </td>
            </tr>
          <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td>
                  <div class="product-name"><?= htmlspecialchars($row['product_name']) ?></div>
                  <div class="product-cat"><?= htmlspecialchars($row['category']) ?></div>
                </td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= htmlspecialchars($row['quantity']) ?> pcs</td>
                <td>₱ <?= number_format((float)$row['price'], 2) ?></td>
                <td>
                  <span class="badge <?= badgeClass($row['status']) ?>">
                    <?= htmlspecialchars($row['status']) ?>
                  </span>
                </td>
                <td>
                  <a href="update_product.php?id=<?= (int)$row['id'] ?>">
                    <button class="btn-update">Update</button>
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<?php
$stmt->close();
?>

<div class="modal-overlay" id="batchModal">
  <div class="modal-box modal-box-wide">
    <div class="modal-header">
      <div>
        <h2>Batch Update</h2>
        <p class="modal-subtitle">Search and update multiple products, then save all at once</p>
      </div>
      <button class="modal-close" onclick="closeModal('batchModal')">×</button>
    </div>

    <div class="modal-search-wrap">
      <div class="modal-search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="batch-search" placeholder="Search medicine name..." oninput="filterBatchMedicines()" autocomplete="off">
      </div>
      <div class="medicine-dropdown" id="batch-dropdown"></div>
    </div>

    <div class="modal-list-header">
      <span>Medicine</span>
      <span>Category</span>
      <span>Price (₱)</span>
      <span>Qty</span>
      <span>Status</span>
      <span></span>
    </div>

    <div class="modal-list" id="batch-list">
      <div class="modal-list-empty" id="batch-list-empty">
        <i class="fa-solid fa-capsules"></i>
        <p>Search and click a product above to add it to the batch</p>
      </div>
    </div>

    <div class="modal-footer batch-modal-footer">
      <div class="modal-footer-left">
        <span id="batch-item-count">0 items added</span>
      </div>
      <div class="modal-footer-right">
        <button class="btn-modal-cancel" onclick="closeModal('batchModal')">Cancel</button>
        <button class="btn-save-all" id="btn-batch-save" onclick="saveBatch()" disabled>
          <i class="fa-solid fa-floppy-disk"></i> Save All
        </button>
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast">
  <i class="fa-solid fa-check-circle"></i>
  <span id="toast-msg">Done!</span>
</div>

<script>

const allProducts = [
  <?php
  
  $jsRes = $conn->query('SELECT id, product_name, category, quantity, price, status FROM products ORDER BY product_name ASC');
$jsRows = [];
while ($r = $jsRes->fetch_assoc()) {
  $jsRows[] = '{id:' . (int)$r['id'] . ',name:' . json_encode($r['product_name']) . ',category:' . json_encode($r['category']) . ',qty:' . (int)$r['quantity'] . ',price:' . (float)$r['price'] . ',status:' . json_encode($r['status']) . '}';
}
echo implode(",\n  ", $jsRows);
  ?>
];

let batchItems = [];

function openBatchModal() {
  batchItems = [];
  document.getElementById('batch-search').value = '';
  hideBatchDropdown();
  renderBatchList();
  document.getElementById('batchModal').classList.add('open');
}

function filterBatchMedicines() {
  const query    = document.getElementById('batch-search').value.trim().toLowerCase();
  const dropdown = document.getElementById('batch-dropdown');
  dropdown.innerHTML = '';
  if (!query) { hideBatchDropdown(); return; }

  const results = allProducts.filter(p =>
    p.name.toLowerCase().includes(query) || p.category.toLowerCase().includes(query)
  );
  if (!results.length) { hideBatchDropdown(); return; }

  results.forEach(p => {
    const already = batchItems.some(i => i.id === p.id);
    const opt = document.createElement('div');
    opt.className = 'medicine-option' + (already ? ' already-added' : '');
    opt.innerHTML = `
      <span class="med-opt-name">${p.name}</span>
      <span class="med-opt-cat">${p.category}</span>
      <span class="med-opt-stock" style="color:${already ? 'rgba(255,255,255,0.4)' : 'rgba(255,255,255,0.75)'}">
        ${already ? '✓ Added' : p.qty + ' pcs · ₱' + p.price.toFixed(2)}
      </span>
    `;
    if (!already) opt.onclick = () => addBatchItem(p);
    dropdown.appendChild(opt);
  });
  dropdown.classList.add('show');
}

function hideBatchDropdown() {
  const d = document.getElementById('batch-dropdown');
  d.classList.remove('show');
  d.innerHTML = '';
}

document.addEventListener('click', function(e) {
  if (!e.target.closest('.modal-search-wrap')) hideBatchDropdown();
});

function addBatchItem(p) {
  if (batchItems.some(i => i.id === p.id)) return;
  batchItems.push({ ...p });
  renderBatchList();
  document.getElementById('batch-search').value = '';
  hideBatchDropdown();
}

function renderBatchList() {
  const list    = document.getElementById('batch-list');
  const empty   = document.getElementById('batch-list-empty');
  const count   = document.getElementById('batch-item-count');
  const saveBtn = document.getElementById('btn-batch-save');

  list.querySelectorAll('.modal-item').forEach(el => el.remove());

  if (!batchItems.length) {
    empty.style.display = 'flex';
    count.textContent   = '0 items added';
    saveBtn.disabled    = true;
    return;
  }

  empty.style.display = 'none';
  count.textContent   = batchItems.length + (batchItems.length === 1 ? ' item added' : ' items added');
  saveBtn.disabled    = false;

  const statusOptions = ['In Stock','Low Stock','Out of Stock'];

  batchItems.forEach((item, idx) => {
    const row = document.createElement('div');
    row.className = 'modal-item';
    row.innerHTML = `
      <div>
        <div class="modal-item-name">${item.name}</div>
        <div class="modal-item-cat">${item.category}</div>
      </div>
      <div class="modal-item-cat" style="align-self:center;">${item.category}</div>
      <div>
        <input type="number" class="modal-item-qty" min="0" step="0.01"
          value="${item.price}" placeholder="0.00"
          oninput="updateBatchField(${idx}, 'price', this.value)">
      </div>
      <div>
        <input type="number" class="modal-item-qty" min="0"
          value="${item.qty}" placeholder="0"
          oninput="updateBatchField(${idx}, 'qty', this.value)">
      </div>
      <div>
        <select class="modal-item-select" onchange="updateBatchField(${idx}, 'status', this.value)">
          ${statusOptions.map(s => `<option value="${s}" ${item.status===s?'selected':''}>${s}</option>`).join('')}
        </select>
      </div>
      <button class="btn-remove-item" onclick="removeBatchItem(${idx})" title="Remove">
        <i class="fa-solid fa-xmark"></i>
      </button>
    `;
    list.appendChild(row);
  });
}

function updateBatchField(idx, field, value) {
  if (field === 'qty' || field === 'price') {
    batchItems[idx][field] = parseFloat(value) || 0;
  } else {
    batchItems[idx][field] = value;
  }
}

function removeBatchItem(idx) {
  batchItems.splice(idx, 1);
  renderBatchList();
}

function saveBatch() {
  if (!batchItems.length) { showToast('No items to save', 'red'); return; }

  const payload = batchItems.map(i => ({ id: i.id, qty: i.qty, price: i.price, status: i.status }));

  fetch('batch_update_products.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(`Updated ${batchItems.length} product${batchItems.length > 1 ? 's' : ''}`, 'green');
      closeModal('batchModal');
      setTimeout(() => location.reload(), 1200);
    } else {
      showToast(data.message || 'Update failed', 'red');
    }
  })
  .catch(() => showToast('Server error — check batch_update_products.php', 'red'));
}

function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}

document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('open'); });
});

function showToast(msg, type = 'green') {
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  t.className = 'toast toast-' + type + ' show';
  setTimeout(() => t.classList.remove('show'), 3200);
}
</script>

<div class="modal-overlay" id="addProductModal">
  <div class="modal-box" style="max-width:480px;">
    <div class="modal-header">
      <div>
        <h2>Add New Product</h2>
        <p class="modal-subtitle">Fill in the details to register a new medicine</p>
      </div>
      <button class="modal-close" onclick="closeModal('addProductModal')">×</button>
    </div>

    <div style="padding: 0 1.5rem 1.5rem;">
      <div class="modal-list-header" style="grid-template-columns:1fr; margin-bottom:1rem; display:block; font-size:0.7rem; text-transform:uppercase; color:rgba(255,255,255,0.45); letter-spacing:0.06em;">Product Details</div>

      <div style="display:flex; flex-direction:column; gap:0.85rem;">
        <div>
          <label style="font-size:0.75rem;color:rgba(255,255,255,0.6);display:block;margin-bottom:4px;">Medicine Name <span style="color:#f87171;">*</span></label>
          <input type="text" id="add-name" placeholder="e.g. Biogesic" style="width:100%;box-sizing:border-box;" class="modal-item-qty">
        </div>
        <div>
          <label style="font-size:0.75rem;color:rgba(255,255,255,0.6);display:block;margin-bottom:4px;">Category <span style="color:#f87171;">*</span></label>
          <input type="text" id="add-category" placeholder="e.g. Analgesic" list="category-list" style="width:100%;box-sizing:border-box;" class="modal-item-qty">
          <datalist id="category-list">
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>">
            <?php endforeach; ?>
          </datalist>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
          <div>
            <label style="font-size:0.75rem;color:rgba(255,255,255,0.6);display:block;margin-bottom:4px;">Quantity</label>
            <input type="number" id="add-qty" value="0" min="0" style="width:100%;box-sizing:border-box;" class="modal-item-qty">
          </div>
          <div>
            <label style="font-size:0.75rem;color:rgba(255,255,255,0.6);display:block;margin-bottom:4px;">Price (₱)</label>
            <input type="number" id="add-price" value="0" min="0" step="0.01" style="width:100%;box-sizing:border-box;" class="modal-item-qty">
          </div>
        </div>
        <div>
          <label style="font-size:0.75rem;color:rgba(255,255,255,0.6);display:block;margin-bottom:4px;">Status</label>
          <select id="add-status" class="modal-item-select" style="width:100%;box-sizing:border-box;">
            <option value="In Stock">In Stock</option>
            <option value="Low Stock">Low Stock</option>
            <option value="Out of Stock">Out of Stock</option>
          </select>
        </div>
      </div>
    </div>

    <div class="modal-footer batch-modal-footer">
      <div class="modal-footer-right" style="width:100%;justify-content:flex-end;display:flex;gap:0.75rem;">
        <button class="btn-modal-cancel" onclick="closeModal('addProductModal')">Cancel</button>
        <button class="btn-save-all" id="btn-add-save" onclick="saveNewProduct()">
          <i class="fa-solid fa-plus"></i> Add Product
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function openAddModal() {
  document.getElementById('add-name').value     = '';
  document.getElementById('add-category').value = '';
  document.getElementById('add-qty').value      = '0';
  document.getElementById('add-price').value    = '0';
  document.getElementById('add-status').value   = 'In Stock';
  document.getElementById('addProductModal').classList.add('open');
}

function saveNewProduct() {
  const name     = document.getElementById('add-name').value.trim();
  const category = document.getElementById('add-category').value.trim();
  const qty      = parseInt(document.getElementById('add-qty').value)   || 0;
  const price    = parseFloat(document.getElementById('add-price').value) || 0;
  const status   = document.getElementById('add-status').value;

  if (!name)     { showToast('Product name is required', 'red'); return; }
  if (!category) { showToast('Category is required', 'red'); return; }

  const btn = document.getElementById('btn-add-save');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving…';

  fetch('add_product.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ name, category, quantity: qty, price, status })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('Product added successfully!', 'green');
      closeModal('addProductModal');
      setTimeout(() => location.reload(), 1200);
    } else {
      showToast(data.message || 'Failed to add product', 'red');
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-plus"></i> Add Product';
    }
  })
  .catch(() => {
    showToast('Server error — check add_product.php', 'red');
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-plus"></i> Add Product';
  });
}
</script>

</body>
</html>