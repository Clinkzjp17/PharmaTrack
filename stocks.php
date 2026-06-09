<?php
require_once 'config.php';
require_once 'auth_guard.php';
require_role('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PharmaTrack – Stocks In/Out</title>
  <link rel="stylesheet" href="stocks.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .stock-form-section { display: none; }
    .stock-form-section.visible { display: block; }
  </style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content">

  <div class="page-header">
    <h1>Stocks In/Out</h1>
  </div>

  <div class="page-body">

    <div class="stock-toggle">
      <div class="stock-toggle-card active-in" id="toggle-in" onclick="switchMode('in')">
        <div class="toggle-icon green-icon">+</div>
        <div class="toggle-text">
          <div class="title">Stock In</div>
          <div class="sub">New items to stock</div>
        </div>
        <div class="check-mark" id="check-in"><i class="fa-solid fa-check" style="font-size:9px;"></i></div>
      </div>

      <div class="stock-toggle-card" id="toggle-out" onclick="switchMode('out')">
        <div class="toggle-icon red-icon">−</div>
        <div class="toggle-text">
          <div class="title">Stock Out</div>
          <div class="sub">Items sold / used</div>
        </div>
        <div class="check-mark red" id="check-out" style="display:none;"><i class="fa-solid fa-check" style="font-size:9px;"></i></div>
      </div>
    </div>

    <div class="stock-form-section visible" id="section-in">
      <div class="stocks-grid">

        <div class="log-form-panel">
          <h3>Log Stock In</h3>

          <div class="form-group">
            <label class="form-label">Select Medicine</label>
            <select class="form-select" id="medicine-in" onchange="loadMedicineStock('in')">
              <option value="" disabled selected>MEDICINE</option>
              <?php
              
              $medRes  = $conn->query('SELECT id, product_name, quantity FROM products ORDER BY product_name ASC');
              while ($m = $medRes->fetch_assoc()):
              ?>
              <option value="<?= $m['id'] ?>" data-qty="<?= $m['quantity'] ?>">
                <?= htmlspecialchars($m['product_name']) ?>
              </option>
              <?php endwhile;  ?>
            </select>
          </div>

          <div class="form-row" style="grid-template-columns:1fr 1fr;">
            <div class="form-group">
              <label class="form-label">Quantity</label>
              <input type="number" class="form-input" value="0" min="0" id="qty-in">
            </div>
            <div class="form-group">
              <label class="form-label">Date Received</label>
              <input type="date" class="form-input" id="date-in" value="">
            </div>
            <div class="form-group">
              <label class="form-label">Expiry Date</label>
              <input type="date" class="form-input" id="expiry-in">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea class="form-textarea" placeholder="ex. Delivery from supplier"></textarea>
          </div>

          <div class="stock-summary">
            <div class="stock-summary-row">
              <span>Current Stock</span>
              <span id="current-stock-in">0 pcs</span>
            </div>
            <div class="stock-summary-row">
              <span>To be added</span>
              <span id="to-add-in">0 pcs</span>
            </div>
            <div class="stock-summary-row total">
              <span>New Total</span>
              <span id="new-total-in">0 pcs</span>
            </div>
          </div>

          <div style="display:flex; justify-content:flex-end;">
            <button class="btn-confirm" onclick="confirmStockIn()">Confirm Stock In</button>
          </div>
        </div>

        <div class="panel">
          <div class="panel-header">
            Stock In History
            <a href="#" class="see-all">See all</a>
          </div>
          <table class="data-table">
            <thead>
              <tr>
                <th>Medicine</th>
                <th>Date</th>
                <th>Quantity</th>
              </tr>
            </thead>
            
            <tbody>
              <?php
                
                $histRes  = $conn->query("
                SELECT sl.quantity, sl.date, p.product_name, p.category
                FROM stock_logs sl
                JOIN products p ON p.id = sl.product_id
                WHERE sl.type = 'in'
                ORDER BY sl.created_at DESC
                LIMIT 10
                ");
                while ($h = $histRes->fetch_assoc()):
              ?>
              <tr>
                <td>
                  <div class="product-name"><?= htmlspecialchars($h['product_name']) ?></div>
                  <div class="product-cat"><?= htmlspecialchars($h['category']) ?></div>
                </td>
                <td><?= htmlspecialchars($h['date']) ?></td>
                <td>+ <?= (int)$h['quantity'] ?> pcs</td>
              </tr>
              <?php endwhile;  ?>
            </tbody>

          </table>
        </div>

      </div>
    </div>

    <div class="stock-form-section" id="section-out">
      <div class="stocks-grid">

        <div class="log-form-panel">
          <h3>Log Stock Out</h3>

          <div class="form-group">
            <label class="form-label">Select Medicine</label>
            <select class="form-select" id="medicine-out" onchange="loadMedicineStock('out')">
              <option value="" disabled selected>MEDICINE</option>
              <?php
              
              $medRes  = $conn->query('SELECT id, product_name, quantity FROM products ORDER BY product_name ASC');
              while ($m = $medRes->fetch_assoc()):
              ?>
              <option value="<?= $m['id'] ?>" data-qty="<?= $m['quantity'] ?>">
                <?= htmlspecialchars($m['product_name']) ?>
              </option>
              <?php endwhile;  ?>
            </select>
          </div>

          <div class="form-row" style="grid-template-columns:1fr 1fr;">
            <div class="form-group">
              <label class="form-label">Quantity</label>
              <input type="number" class="form-input" value="0" min="0" id="qty-out">
            </div>
            <div class="form-group">
              <label class="form-label">Date Sold / Used</label>
              <input type="date" class="form-input" id="date-out">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea class="form-textarea" placeholder="ex. Delivery from supplier"></textarea>
          </div>

          <div class="stock-summary">
            <div class="stock-summary-row">
              <span>Current Stock</span>
              <span id="current-stock-out">0 pcs</span>
            </div>
            <div class="stock-summary-row">
              <span>To be deducted</span>
              <span id="to-deduct-out">0 pcs</span>
            </div>
            <div class="stock-summary-row total">
              <span>New Total</span>
              <span id="new-total-out">0 pcs</span>
            </div>
          </div>

          <div style="display:flex; justify-content:flex-end;">
            <button class="btn-confirm" onclick="confirmStockOut()">Confirm Stock Out</button>
          </div>
        </div>

        <div class="panel">
          <div class="panel-header">
            Stock Out History
            <a href="#" class="see-all">See all</a>
          </div>
          <table class="data-table">
            <thead>
              <tr>
                <th>Medicine</th>
                <th>Date</th>
                <th>Quantity</th>
              </tr>
            </thead>
            <tbody>
              <?php
                
                $histRes  = $conn->query("
                SELECT sl.quantity, sl.date, p.product_name, p.category
                FROM stock_logs sl
                JOIN products p ON p.id = sl.product_id
                WHERE sl.type = 'out'
                ORDER BY sl.created_at DESC
                LIMIT 10
                ");
                while ($h = $histRes->fetch_assoc()):
              ?>
              <tr>
                <td>
                  <div class="product-name"><?= htmlspecialchars($h['product_name']) ?></div>
                  <div class="product-cat"><?= htmlspecialchars($h['category']) ?></div>
                </td>
                <td><?= htmlspecialchars($h['date']) ?></td>
                <td>- <?= (int)$h['quantity'] ?> pcs</td>
              </tr>
              <?php endwhile;  ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>

  </div>
</div>

<script>
  let currentStockIn  = 0;
  let currentStockOut = 0;

  function loadMedicineStock(mode) {
    const select = document.getElementById('medicine-' + mode);
    const opt    = select.options[select.selectedIndex];
    const qty    = parseInt(opt?.dataset.qty) || 0;

    if (mode === 'in') {
      currentStockIn = qty;
      document.getElementById('current-stock-in').textContent = qty + ' pcs';
      document.getElementById('new-total-in').textContent     = qty + ' pcs';
      document.getElementById('to-add-in').textContent        = '0 pcs';
      document.getElementById('qty-in').value = 0;
    } else {
      currentStockOut = qty;
      document.getElementById('current-stock-out').textContent = qty + ' pcs';
      document.getElementById('new-total-out').textContent     = qty + ' pcs';
      document.getElementById('to-deduct-out').textContent     = '0 pcs';
      document.getElementById('qty-out').value = 0;
    }
  }

  function switchMode(mode) {
    const toggleIn  = document.getElementById('toggle-in');
    const toggleOut = document.getElementById('toggle-out');
    const checkIn   = document.getElementById('check-in');
    const checkOut  = document.getElementById('check-out');
    const secIn     = document.getElementById('section-in');
    const secOut    = document.getElementById('section-out');

    if (mode === 'in') {
      toggleIn.classList.add('active-in');
      toggleOut.classList.remove('active-out');
      checkIn.style.display = 'flex';
      checkOut.style.display = 'none';
      secIn.classList.add('visible');
      secOut.classList.remove('visible');
    } else {
      toggleOut.classList.add('active-out');
      toggleIn.classList.remove('active-in');
      checkOut.style.display = 'flex';
      checkIn.style.display = 'none';
      secOut.classList.add('visible');
      secIn.classList.remove('visible');
    }
  }

  document.getElementById('qty-in').addEventListener('input', function() {
    const v = parseInt(this.value) || 0;
    document.getElementById('to-add-in').textContent    = v + ' pcs';
    document.getElementById('new-total-in').textContent = (currentStockIn + v) + ' pcs';
  });

  document.getElementById('qty-out').addEventListener('input', function() {
    const v = parseInt(this.value) || 0;
    document.getElementById('to-deduct-out').textContent = v + ' pcs';
    document.getElementById('new-total-out').textContent  = Math.max(0, currentStockOut - v) + ' pcs';
  });

  function confirmStockIn() {
  const select  = document.getElementById('medicine-in');
  const id      = parseInt(select.value);
  const qty     = parseInt(document.getElementById('qty-in').value) || 0;
  const date    = document.getElementById('date-in').value || new Date().toISOString().split('T')[0];
  const expiry  = document.getElementById('expiry-in').value || '';
  const notes   = document.querySelector('#section-in textarea').value;

  if (!id)  { alert('Please select a medicine.'); return; }
  if (!qty) { alert('Please enter a quantity.'); return; }

  fetch('save_stock.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ type: 'in', product_id: id, qty, date, expiry, notes })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      alert('Stock In saved!');
      location.reload();
    } else {
      alert('Error: ' + (data.message || 'Unknown error'));
    }
  })
  .catch(() => alert('Server error — check save_stock.php'));
}

function confirmStockOut() {
  const select = document.getElementById('medicine-out');
  const id     = parseInt(select.value);
  const qty    = parseInt(document.getElementById('qty-out').value) || 0;
  const date   = document.getElementById('date-out').value || new Date().toISOString().split('T')[0];
  const notes  = document.querySelector('#section-out textarea').value;

  if (!id)  { alert('Please select a medicine.'); return; }
  if (!qty) { alert('Please enter a quantity.'); return; }
  if (qty > currentStockOut) { alert('Not enough stock!'); return; }

  fetch('save_stock.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ type: 'out', product_id: id, qty, date, notes })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      alert('Stock Out saved!');
      location.reload();
    } else {
      alert('Error: ' + (data.message || 'Unknown error'));
    }
  })
  .catch(() => alert('Server error — check save_stock.php'));
}
</script>

</body>
</html>