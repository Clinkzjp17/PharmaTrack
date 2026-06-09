<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PharmaTrack – Update Product</title>
  <link rel="stylesheet" href="product.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .form-card {
      background: var(--card-bg);
      border-radius: 14px;
      padding: 32px 36px;
      max-width: 620px;
      color: white;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 7px;
    }

    .form-group.full-width {
      grid-column: 1 / -1;
    }

    .form-group label {
      font-size: 12px;
      font-weight: 600;
      color: rgba(255,255,255,0.65);
      letter-spacing: 0.8px;
      text-transform: uppercase;
    }

    .form-group input,
    .form-group select {
      background: rgba(0,0,0,0.2);
      border: 1px solid var(--border-subtle);
      border-radius: 8px;
      padding: 10px 14px;
      color: white;
      font-size: 13px;
      font-family: 'Poppins', sans-serif;
      outline: none;
      transition: border-color 0.2s;
      width: 100%;
    }

    .form-group input:focus,
    .form-group select:focus {
      border-color: rgba(94,207,219,0.6);
    }

    .form-group input::placeholder {
      color: rgba(255,255,255,0.3);
    }

    .form-group select option {
      background: var(--sidebar-bg);
      color: white;
    }

    .form-actions {
      display: flex;
      gap: 12px;
      margin-top: 28px;
      align-items: center;
    }

    .btn-save {
      background: #2eb872;
      border: none;
      color: white;
      padding: 10px 28px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      transition: background 0.2s;
    }

    .btn-save:hover {
      background: #27a564;
    }

    .btn-cancel {
      background: transparent;
      border: 1.5px solid rgba(255,255,255,0.25);
      color: rgba(255,255,255,0.75);
      padding: 10px 24px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      transition: all 0.2s;
    }

    .btn-cancel:hover {
      background: rgba(255,255,255,0.08);
      color: white;
    }

    .btn-delete {
      margin-left: auto;
      background: transparent;
      border: 1.5px solid var(--accent-red);
      color: var(--accent-red);
      padding: 10px 20px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      transition: all 0.2s;
    }

    .btn-delete:hover {
      background: rgba(217,79,79,0.15);
    }

    .alert {
      padding: 12px 16px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 9px;
    }

    .alert-success {
      background: rgba(46,184,114,0.15);
      border: 1px solid rgba(46,184,114,0.4);
      color: #2eb872;
    }

    .alert-error {
      background: rgba(217,79,79,0.15);
      border: 1px solid rgba(217,79,79,0.4);
      color: var(--accent-red);
    }

    .product-id-label {
      font-size: 12px;
      color: rgba(255,255,255,0.4);
      margin-top: 4px;
    }
  </style>
</head>
<body>

<?php
// ── DB CONFIG ──────────────────────────────────────
$host = 'localhost';
$db   = 'pharmatrack';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('<p style="color:red;padding:2rem;">DB connection failed: ' . $conn->connect_error . '</p>');
}

// ── VALIDATE ID ────────────────────────────────────
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: products.php');
    exit;
}

$message = '';
$messageType = '';

// ── HANDLE DELETE ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $del = $conn->prepare('DELETE FROM products WHERE id = ?');
    $del->bind_param('i', $id);
    if ($del->execute()) {
        $conn->close();
        header('Location: products.php?deleted=1');
        exit;
    } else {
        $message = 'Failed to delete product.';
        $messageType = 'error';
    }
    $del->close();
}

// ── HANDLE UPDATE ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $product_name = trim($_POST['product_name'] ?? '');
    $category     = trim($_POST['category'] ?? '');
    $quantity     = (int)($_POST['quantity'] ?? 0);
    $price        = (float)($_POST['price'] ?? 0);
    $status       = trim($_POST['status'] ?? '');

    $allowed_statuses = ['In Stock', 'Low Stock', 'Out of Stock'];

    if ($product_name === '' || $category === '' || !in_array($status, $allowed_statuses)) {
        $message = 'Please fill in all required fields correctly.';
        $messageType = 'error';
    } else {
        $upd = $conn->prepare('UPDATE products SET product_name=?, category=?, quantity=?, price=?, status=? WHERE id=?');
        $upd->bind_param('ssdsi', $product_name, $category, $quantity, $price, $status, $id);
        // Note: bind_param type string: s=string, d=double, i=integer
        // Corrected:
        $upd->close();

        $upd2 = $conn->prepare('UPDATE products SET product_name=?, category=?, quantity=?, price=?, status=? WHERE id=?');
        $upd2->bind_param('ssidsi', $product_name, $category, $quantity, $price, $status, $id);

        if ($upd2->execute()) {
            $message = 'Product updated successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to update product: ' . $conn->error;
            $messageType = 'error';
        }
        $upd2->close();
    }
}

// ── FETCH PRODUCT ──────────────────────────────────
$stmt = $conn->prepare('SELECT * FROM products WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    $conn->close();
    header('Location: products.php');
    exit;
}

// ── DISTINCT CATEGORIES ────────────────────────────
$categories = [];
$catRes = $conn->query('SELECT DISTINCT category FROM products ORDER BY category');
while ($row = $catRes->fetch_assoc()) {
    $categories[] = $row['category'];
}
// Make sure current product category is always in the list
if (!in_array($product['category'], $categories)) {
    array_unshift($categories, $product['category']);
}

include __DIR__ . '/sidebar.php';
?>

<div class="main-content">

  <div class="page-header">
    <div class="page-header-row">
      <div>
        <h1>Update Product</h1>
        <div class="product-id-label">ID #<?= $id ?> &mdash; <?= htmlspecialchars($product['product_name']) ?></div>
      </div>
      <a href="products.php" class="btn-add-product">
        <i class="fa-solid fa-arrow-left"></i> Back to Products
      </a>
    </div>
  </div>

  <div class="page-body">

    <?php if ($message): ?>
      <div class="alert alert-<?= $messageType ?>">
        <i class="fa-solid fa-<?= $messageType === 'success' ? 'circle-check' : 'circle-exclamation' ?>"></i>
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <div class="form-card">
      <form method="POST" action="update_product.php?id=<?= $id ?>">
        <input type="hidden" name="action" value="update">

        <div class="form-grid">

          <div class="form-group full-width">
            <label>Product Name</label>
            <input
              type="text"
              name="product_name"
              placeholder="e.g. Biogesic"
              value="<?= htmlspecialchars($product['product_name']) ?>"
              required
            >
          </div>

          <div class="form-group">
            <label>Category</label>
            <select name="category" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"
                  <?= ($product['category'] === $cat) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat) ?>
                </option>
              <?php endforeach; ?>
              <option value="Analgesic"      <?= ($product['category'] === 'Analgesic')      ? 'selected' : '' ?>>Analgesic</option>
              <option value="Antihistamine"  <?= ($product['category'] === 'Antihistamine')  ? 'selected' : '' ?>>Antihistamine</option>
              <option value="Antibiotic"     <?= ($product['category'] === 'Antibiotic')     ? 'selected' : '' ?>>Antibiotic</option>
              <option value="Antiviral"      <?= ($product['category'] === 'Antiviral')      ? 'selected' : '' ?>>Antiviral</option>
              <option value="Vitamin"        <?= ($product['category'] === 'Vitamin')        ? 'selected' : '' ?>>Vitamin</option>
              <option value="Other"          <?= ($product['category'] === 'Other')          ? 'selected' : '' ?>>Other</option>
            </select>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select name="status" required>
              <option value="In Stock"     <?= ($product['status'] === 'In Stock')     ? 'selected' : '' ?>>In Stock</option>
              <option value="Low Stock"    <?= ($product['status'] === 'Low Stock')    ? 'selected' : '' ?>>Low Stock</option>
              <option value="Out of Stock" <?= ($product['status'] === 'Out of Stock') ? 'selected' : '' ?>>Out of Stock</option>
            </select>
          </div>

          <div class="form-group">
            <label>Quantity (pcs)</label>
            <input
              type="number"
              name="quantity"
              min="0"
              placeholder="0"
              value="<?= (int)$product['quantity'] ?>"
              required
            >
          </div>

          <div class="form-group">
            <label>Price (₱)</label>
            <input
              type="number"
              name="price"
              min="0"
              step="0.01"
              placeholder="0.00"
              value="<?= number_format((float)$product['price'], 2, '.', '') ?>"
              required
            >
          </div>

        </div><!-- /.form-grid -->

        <div class="form-actions">
          <button type="submit" class="btn-save">
            <i class="fa-solid fa-floppy-disk"></i> Save Changes
          </button>
          <a href="products.php" class="btn-cancel">
            <i class="fa-solid fa-xmark"></i> Cancel
          </a>

          <!-- DELETE -->
          <button
            type="button"
            class="btn-delete"
            onclick="confirmDelete()"
          >
            <i class="fa-solid fa-trash"></i> Delete
          </button>
        </div>

      </form>

      <!-- Separate delete form so it doesn't conflict with update -->
      <form method="POST" action="update_product.php?id=<?= $id ?>" id="deleteForm">
        <input type="hidden" name="action" value="delete">
      </form>
    </div>

  </div>
</div>

<script>
function confirmDelete() {
  if (confirm('Are you sure you want to delete this product? This cannot be undone.')) {
    document.getElementById('deleteForm').submit();
  }
}
</script>

<?php $conn->close(); ?>
</body>
</html>
