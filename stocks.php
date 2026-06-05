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

<<<<<<< Updated upstream:stocks.html
<div class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon"></div>
    <div class="logo-text">
      <h3>PharmaTrack</h3>
      <span>Admin</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section-label">MAIN</div>
    <a href="dashboard.html">
      <i class="fa-solid fa-chart-bar"></i>
      <span>Dashboard</span>
    </a>
    <a href="products.html">
      <i class="fa-solid fa-capsules"></i>
      <span>Products</span>
    </a>
    <a href="stocks.html" class="active">
      <i class="fa-solid fa-cart-shopping"></i>
      <span>Stocks</span>
    </a>
    <a href="#">
      <i class="fa-solid fa-user"></i>
      <span>Users</span>
    </a>

    <div class="sidebar-section-label">MONITORING</div>
    <a href="#">
      <i class="fa-regular fa-clock"></i>
      <span>Expiry</span>
    </a>
    <a href="#">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <span>Low Stock</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="avatar">AD</div>
    <div class="admin-info">
      <div class="name">Admin</div>
      <div class="role">Administrator</div>
    </div>
    <a href="admin-login.html" class="logout-btn" title="Logout">
      <i class="fa-solid fa-right-from-bracket"></i>
    </a>
  </div>
</div>
=======
<?php include 'includes/sidebar.php'; ?>
>>>>>>> Stashed changes:stocks.php

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
            <select class="form-select">
              <option value="" disabled selected>MEDICINE</option>
              <option>Biogesic</option>
              <option>Neozep</option>
              <option>Cetirizine</option>
              <option>Amoxicillin</option>
            </select>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Quantity</label>
              <input type="number" class="form-input" value="0" min="0" id="qty-in">
            </div>
            <div class="form-group">
              <label class="form-label">Date Received</label>
              <input type="date" class="form-input">
            </div>
            <div class="form-group" style="grid-column: span 2 / span 2;" hidden></div>
          </div>

          <div class="form-row" style="grid-template-columns:1fr 1fr;">
            <div class="form-group">
              <label class="form-label">Date Received</label>
              <input type="date" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Expiry Date</label>
              <input type="date" class="form-input">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea class="form-textarea" placeholder="ex. Delivery from supplier"></textarea>
          </div>

          <div class="stock-summary">
            <div class="stock-summary-row">
              <span>Current Stock</span>
              <span>45 pcs</span>
            </div>
            <div class="stock-summary-row">
              <span>To be added</span>
              <span id="to-add-in">0 pcs</span>
            </div>
            <div class="stock-summary-row total">
              <span>New Total</span>
              <span id="new-total-in">45 pcs</span>
            </div>
          </div>

          <div style="display:flex; justify-content:flex-end;">
            <button class="btn-confirm" href="#">Confirm Stock In</button>
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
              <tr>
                <td>
                  <div class="product-name">Biogesic</div>
                  <div class="product-cat">Analgesic</div>
                </td>
                <td>April 10</td>
                <td>+ 50 pcs</td>
              </tr>
              <tr>
                <td>
                  <div class="product-name">Biogesic</div>
                  <div class="product-cat">Analgesic</div>
                </td>
                <td>April 9</td>
                <td>+ 50 pcs</td>
              </tr>
              <tr>
                <td>
                  <div class="product-name">Cetirizine</div>
                  <div class="product-cat">Antihistamine</div>
                </td>
                <td>April 9</td>
                <td>+ 50 pcs</td>
              </tr>
              <tr>
                <td>
                  <div class="product-name">Amoxicilin</div>
                  <div class="product-cat">Antibiotic</div>
                </td>
                <td>April 9</td>
                <td>+ 50 pcs</td>
              </tr>
              <tr>
                <td>
                  <div class="product-name">Amoxicilin</div>
                  <div class="product-cat">Antibiotic</div>
                </td>
                <td>April 9</td>
                <td>+ 50 pcs</td>
              </tr>
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
            <select class="form-select">
              <option value="" disabled selected>MEDICINE</option>
              <option>Biogesic</option>
              <option>Neozep</option>
              <option>Cetirizine</option>
              <option>Amoxicillin</option>
            </select>
          </div>

          <div class="form-row" style="grid-template-columns:1fr 1fr;">
            <div class="form-group">
              <label class="form-label">Quantity</label>
              <input type="number" class="form-input" value="0" min="0" id="qty-out">
            </div>
            <div class="form-group">
              <label class="form-label">Date Sold / Used</label>
              <input type="date" class="form-input">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea class="form-textarea" placeholder="ex. Delivery from supplier"></textarea>
          </div>

          <div class="stock-summary">
            <div class="stock-summary-row">
              <span>Current Stock</span>
              <span>45 pcs</span>
            </div>
            <div class="stock-summary-row">
              <span>To be deducted</span>
              <span id="to-deduct-out">0 pcs</span>
            </div>
            <div class="stock-summary-row total">
              <span>New Total</span>
              <span id="new-total-out">45 pcs</span>
            </div>
          </div>

          <div style="display:flex; justify-content:flex-end;">
            <button class="btn-confirm" href="#">Confirm Stock Out</button>
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
              <tr>
                <td>
                  <div class="product-name">Biogesic</div>
                  <div class="product-cat">Analgesic</div>
                </td>
                <td>April 10</td>
                <td>- 5 pcs</td>
              </tr>
              <tr>
                <td>
                  <div class="product-name">Biogesic</div>
                  <div class="product-cat">Analgesic</div>
                </td>
                <td>April 9</td>
                <td>- 5 pcs</td>
              </tr>
              <tr>
                <td>
                  <div class="product-name">Cetirizine</div>
                  <div class="product-cat">Antihistamine</div>
                </td>
                <td>April 9</td>
                <td>- 5 pcs</td>
              </tr>
              <tr>
                <td>
                  <div class="product-name">Amoxicilin</div>
                  <div class="product-cat">Antibiotic</div>
                </td>
                <td>April 9</td>
                <td>- 5 pcs</td>
              </tr>
              <tr>
                <td>
                  <div class="product-name">Amoxicilin</div>
                  <div class="product-cat">Antibiotic</div>
                </td>
                <td>April 9</td>
                <td>- 5 pcs</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>

  </div>
</div>

<script>
  const currentStock = 45;

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
    document.getElementById('new-total-in').textContent = (currentStock + v) + ' pcs';
  });

  document.getElementById('qty-out').addEventListener('input', function() {
    const v = parseInt(this.value) || 0;
    document.getElementById('to-deduct-out').textContent = v + ' pcs';
    document.getElementById('new-total-out').textContent  = Math.max(0, currentStock - v) + ' pcs';
  });
</script>

</body>
</html>
