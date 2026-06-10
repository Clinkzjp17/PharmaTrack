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
  <title>PharmaTrack – Expiry</title>
  <link rel="stylesheet" href="expiry.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content">
  <div class="page-header">
    <div class="page-header-row">
      <h1>Expiry</h1>
      <button class="btn-batch-update" onclick="openBatchModal()">
        <i class="fa-solid fa-pen-to-square"></i> Batch Update
      </button>
    </div>
  </div>

  <div class="page-body">

    <div class="stat-cards">
      <div class="stat-card card-red">
        <div class="card-label">Expired</div>
        <div class="card-value" id="count-expired">0</div>
        <div class="card-sub">Remove immediately</div>
      </div>
      <div class="stat-card card-orange">
        <div class="card-label">Expiring in 7 days</div>
        <div class="card-value" id="count-soon">0</div>
        <div class="card-sub">Need to remove soon</div>
      </div>
      <div class="stat-card card-green">
        <div class="card-label">Expiring in 90 days</div>
        <div class="card-value" id="count-ok">0</div>
        <div class="card-sub">Safe for customers</div>
      </div>
    </div>

    <div class="filter-row">
      <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="search-input" placeholder="Search medicines..." oninput="applyFilters()">
      </div>
      <select class="select-filter" id="status-filter" onchange="applyFilters()">
        <option value="ALL">ALL</option>
        <option value="expired">Expired</option>
        <option value="soon">Expiring in a week</option>
        <option value="ok">Expiring in a few months</option>
      </select>
    </div>

    <div class="panel">
      <table class="data-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Days Left</th>
            <th>Expiry Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="expiry-tbody"></tbody>
      </table>
    </div>

  </div>
</div>

<div class="modal-overlay" id="updateModal">
  <div class="modal-box">
    <div class="modal-header">
      <div>
        <h2>Update Expiry Details</h2>
        <p class="modal-subtitle">Edit the expiry date or quantity for this product</p>
      </div>
      <button class="modal-close" onclick="closeModal('updateModal')">×</button>
    </div>
    <div class="modal-body">
      <div class="modal-product-info">
        <div class="modal-product-icon"><i class="fa-solid fa-capsules"></i></div>
        <div>
          <div class="modal-product-name" id="u-prod-name">–</div>
          <div class="modal-product-meta" id="u-prod-meta">–</div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Expiry Date</label>
          <input type="date" class="form-input" id="u-expiry-date">
        </div>
        <div class="form-group">
          <label class="form-label">Quantity</label>
          <input type="number" class="form-input" id="u-qty" min="0" placeholder="0">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Batch / Lot Number</label>
        <input type="text" class="form-input" id="u-batch" placeholder="e.g. LOT-2024-001">
      </div>
      <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea class="form-textarea" id="u-notes" placeholder="Optional notes about this update..."></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-modal-cancel" onclick="closeModal('updateModal')">Cancel</button>
      <button class="btn-confirm-update" onclick="saveUpdate()">
        <i class="fa-solid fa-floppy-disk"></i> Save Changes
      </button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="removeModal">
  <div class="modal-box">
    <div class="modal-header">
      <div>
        <h2>Remove Expired Product</h2>
        <p class="modal-subtitle">Log how this expired stock was handled</p>
      </div>
      <button class="modal-close" onclick="closeModal('removeModal')">×</button>
    </div>
    <div class="modal-body">
      <div class="modal-product-info">
        <div class="modal-product-icon"><i class="fa-solid fa-capsules"></i></div>
        <div>
          <div class="modal-product-name" id="r-prod-name">–</div>
          <div class="modal-product-meta" id="r-prod-meta">–</div>
        </div>
      </div>

      <div class="expiry-warning-box">
        <i class="fa-solid fa-circle-exclamation"></i>
        <p>This product is expired or expiring soon. Please choose how this stock was handled before removing it from inventory.</p>
      </div>

      <div class="form-group">
        <label class="form-label">Disposal Method</label>
        <div class="disposal-options" id="disposal-options">
          <div class="disposal-option selected" onclick="selectDisposal(this, 'returned')">
            <div class="opt-icon">↩️</div>
            <div class="opt-label">Return to Supplier</div>
            <div class="opt-sub">Send back for credit</div>
          </div>
          <div class="disposal-option" onclick="selectDisposal(this, 'destroyed')">
            <div class="opt-icon">🗑️</div>
            <div class="opt-label">Destroy / Dispose</div>
            <div class="opt-sub">Proper waste disposal</div>
          </div>
          <div class="disposal-option" onclick="selectDisposal(this, 'donated')">
            <div class="opt-icon">🤝</div>
            <div class="opt-label">Donate</div>
            <div class="opt-sub">Non-critical items only</div>
          </div>
          <div class="disposal-option" onclick="selectDisposal(this, 'other')">
            <div class="opt-icon">📋</div>
            <div class="opt-label">Other</div>
            <div class="opt-sub">Specify in notes</div>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Date Removed</label>
        <input type="date" class="form-input" id="r-date">
      </div>
      <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea class="form-textarea" id="r-notes" placeholder="e.g. Returned to PhilPharma supplier, reference #12345..."></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-modal-cancel" onclick="closeModal('removeModal')">Cancel</button>
      <button class="btn-confirm-remove" onclick="confirmRemove()">
        <i class="fa-solid fa-trash-can"></i> Confirm Removal
      </button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="batchModal">
  <div class="modal-box modal-box-wide">
    <div class="modal-header">
      <div>
        <h2>Batch Update Expiry</h2>
        <p class="modal-subtitle">Search and add medicines, then set new expiry dates and quantities</p>
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
      <span>Current Expiry</span>
      <span>New Expiry Date</span>
      <span>Quantity</span>
      <span></span>
    </div>

    <div class="modal-list" id="batch-list">
      <div class="modal-list-empty" id="batch-list-empty">
        <i class="fa-regular fa-clock"></i>
        <p>Search and click a medicine above to add it to the batch</p>
      </div>
    </div>

    <div class="modal-footer">
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
let expiryData   = [];
let currentItem  = null;
let disposalMethod = 'returned';
let selectedIds  = new Set();
let filteredData = [];

function loadExpiryData() {
  fetch('get_expiry.php')
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        expiryData   = data.data;
        filteredData = [...expiryData];
        applyFilters();
      } else {
        showToast('Failed to load expiry data: ' + (data.message || 'unknown error'), 'red');
      }
    })
    .catch(() => showToast('Could not reach get_expiry.php', 'red'));
}

function updateStatCards() {
  document.getElementById('count-expired').textContent = expiryData.filter(p => p.status === 'expired').length;
  document.getElementById('count-soon').textContent    = expiryData.filter(p => p.status === 'soon').length;
  document.getElementById('count-ok').textContent      = expiryData.filter(p => p.status === 'ok').length;
}

function daysClass(status) {
  if (status === 'expired') return 'days-expired';
  if (status === 'soon')    return 'days-soon';
  return 'days-ok';
}

function renderTable() {
  document.getElementById('expiry-tbody').innerHTML = filteredData.map(p => `
    <tr>
      <td><div class="product-name">${p.name}</div></td>
      <td>${p.category}</td>
      <td>${p.qty} pcs</td>
      <td><span class="${daysClass(p.status)}">${p.daysLeft}</span></td>
      <td>${p.expiry}</td>
      <td>
        <button class="btn-update" onclick="openUpdate(${p.id})">Update</button>
        <button class="btn-remove" onclick="openRemove(${p.id})">Remove</button>
      </td>
    </tr>
  `).join('');

  const selectAll = document.getElementById('select-all');
  if (selectAll) {
    selectAll.checked = filteredData.length > 0 && filteredData.every(p => selectedIds.has(p.id));
  }

  updateStatCards();
}

function applyFilters() {
  const q      = document.getElementById('search-input').value.toLowerCase();
  const status = document.getElementById('status-filter').value;
  filteredData = expiryData.filter(p => {
    const matchSearch = p.name.toLowerCase().includes(q) || p.category.toLowerCase().includes(q);
    const matchStatus = status === 'ALL' || p.status === status;
    return matchSearch && matchStatus;
  });
  renderTable();
}

function toggleCheck(id, checked) {
  checked ? selectedIds.add(id) : selectedIds.delete(id);
  const selectAll = document.getElementById('select-all');
  if (selectAll) {
    selectAll.checked = filteredData.length > 0 && filteredData.every(p => selectedIds.has(p.id));
  }
}

function toggleSelectAll(cb) {
  filteredData.forEach(p => cb.checked ? selectedIds.add(p.id) : selectedIds.delete(p.id));
  renderTable();
}

function openUpdate(id) {
  currentItem = expiryData.find(p => p.id === id);
  document.getElementById('u-prod-name').textContent = currentItem.name;
  document.getElementById('u-prod-meta').textContent = currentItem.category + ' · ' + currentItem.qty + ' pcs · Expires ' + currentItem.expiry;
  document.getElementById('u-qty').value = currentItem.qty;
  document.getElementById('u-expiry-date').value = '';
  document.getElementById('u-batch').value = '';
  document.getElementById('u-notes').value = '';
  document.getElementById('updateModal').classList.add('open');
}

function saveUpdate() {
  if (!currentItem) return;
  const qty    = parseInt(document.getElementById('u-qty').value);
  const expiry = document.getElementById('u-expiry-date').value;
  const batch  = document.getElementById('u-batch').value.trim();
  const notes  = document.getElementById('u-notes').value.trim();

  fetch('update_expiry.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: currentItem.id, qty, expiry, batch, notes })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      closeModal('updateModal');
      showToast('Expiry details updated for ' + currentItem.name, 'green');
      loadExpiryData(); // refresh from DB so daysLeft & status recompute correctly
    } else {
      showToast(data.message || 'Update failed', 'red');
    }
  })
  .catch(() => showToast('Server error — check update_expiry.php', 'red'));
}

function openRemove(id) {
  currentItem = expiryData.find(p => p.id === id);
  document.getElementById('r-prod-name').textContent = currentItem.name;
  document.getElementById('r-prod-meta').textContent = currentItem.category + ' · ' + currentItem.qty + ' pcs · Expired ' + currentItem.expiry;
  document.getElementById('r-date').value = new Date().toISOString().split('T')[0];
  document.getElementById('r-notes').value = '';
  disposalMethod = 'returned';
  document.querySelectorAll('.disposal-option').forEach((el, i) => el.classList.toggle('selected', i === 0));
  document.getElementById('removeModal').classList.add('open');
}

function confirmRemove() {
  if (!currentItem) return;
  const dateRemoved = document.getElementById('r-date').value;
  const notes       = document.getElementById('r-notes').value.trim();

  fetch('remove_expiry.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: currentItem.id, disposal: disposalMethod, date_removed: dateRemoved, notes })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      selectedIds.delete(currentItem.id);
      closeModal('removeModal');
      showToast(currentItem.name + ' removed from inventory', 'red');
      loadExpiryData();
    } else {
      showToast(data.message || 'Removal failed', 'red');
    }
  })
  .catch(() => showToast('Server error — check remove_expiry.php', 'red'));
}

let batchItems = []; 

function openBatchModal() {
  batchItems = [];
  document.getElementById('batch-search').value = '';
  hideBatchDropdown();
  renderBatchList();
  document.getElementById('batchModal').classList.add('open');
}

function filterBatchMedicines() {
  const query = document.getElementById('batch-search').value.trim().toLowerCase();
  const dropdown = document.getElementById('batch-dropdown');
  dropdown.innerHTML = '';

  if (!query) { hideBatchDropdown(); return; }

  const results = expiryData.filter(p =>
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
      <span class="med-opt-stock ${p.status === 'expired' ? 'text-red' : p.status === 'soon' ? 'text-orange' : ''}">${already ? '✓ Added' : 'Exp: ' + p.expiry}</span>
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
  batchItems.push({ ...p, newExpiry: '', newQty: p.qty });
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
    count.textContent = '0 items added';
    saveBtn.disabled = true;
    return;
  }

  empty.style.display = 'none';
  count.textContent = batchItems.length + (batchItems.length === 1 ? ' item added' : ' items added');
  saveBtn.disabled = false;

  batchItems.forEach((item, idx) => {
    const row = document.createElement('div');
    row.className = 'modal-item';
    row.innerHTML = `
      <div>
        <div class="modal-item-name">${item.name}</div>
        <div class="modal-item-cat">${item.category}</div>
      </div>
      <div class="modal-item-stock">
        <span class="${item.status === 'expired' ? 'text-red' : item.status === 'soon' ? 'text-orange' : 'text-green'}">${item.expiry}</span>
      </div>
      <div>
        <input type="date" class="modal-item-date" value="${item.newExpiry}" oninput="updateBatchExpiry(${idx}, this.value)">
      </div>
      <div>
        <input type="number" class="modal-item-qty" min="0" value="${item.newQty}" placeholder="0" oninput="updateBatchQty(${idx}, this.value)">
      </div>
      <button class="btn-remove-item" onclick="removeBatchItem(${idx})" title="Remove">
        <i class="fa-solid fa-xmark"></i>
      </button>
    `;
    list.appendChild(row);
  });
}

function updateBatchExpiry(idx, val) { batchItems[idx].newExpiry = val; }
function updateBatchQty(idx, val)    { batchItems[idx].newQty = parseInt(val) || 0; }

function removeBatchItem(idx) {
  batchItems.splice(idx, 1);
  renderBatchList();
}

function saveBatch() {
  if (!batchItems.length) return;
  const payload = batchItems.map(item => ({
    id:        item.id,
    newExpiry: item.newExpiry,
    newQty:    item.newQty,
  }));

  fetch('batch_update_expiry.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(r => r.json())
  .then(data => {
    const count = batchItems.length;
    batchItems = [];
    closeModal('batchModal');
    if (data.success) {
      showToast(`Batch update applied to ${count} product${count > 1 ? 's' : ''}`, 'green');
    } else {
      showToast(data.message || 'Some items failed to update', 'red');
    }
    loadExpiryData();
  })
  .catch(() => showToast('Server error — check batch_update_expiry.php', 'red'));
}

function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}

document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('open'); });
});

function selectDisposal(el, method) {
  disposalMethod = method;
  document.querySelectorAll('.disposal-option').forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
}

function showToast(msg, type = 'green') {
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  t.className = 'toast toast-' + type + ' show';
  setTimeout(() => t.classList.remove('show'), 3200);
}

loadExpiryData();
</script>
</body>
</html>
