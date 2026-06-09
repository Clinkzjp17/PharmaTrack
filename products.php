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
// ─────────────────────────────────────────────
// CONFIGURATION — replace with your DB details
// ─────────────────────────────────────────────
$host   = 'localhost';
$db     = 'pharmatrack';   // your database name
$user   = 'root';          // your DB username
$pass   = '';              // your DB password

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('<p style="color:red;padding:2rem;">Database connection failed: ' . $conn->connect_error . '</p>');
}

// ─────────────────────────────────────────────
// FILTERS from GET params
// ─────────────────────────────────────────────
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$status   = isset($_GET['status'])   ? trim($_GET['status'])   : '';
$tab      = isset($_GET['tab'])      ? trim($_GET['tab'])      : 'all';

// ─────────────────────────────────────────────
// BUILD QUERY
// ─────────────────────────────────────────────
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

// Tab overrides the status dropdown
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

// ─────────────────────────────────────────────
// COUNT BADGES (always unfiltered by tab/status)
// ─────────────────────────────────────────────
$counts = ['all' => 0, 'In Stock' => 0, 'Low Stock' => 0, 'Out of Stock' => 0];
$countRes = $conn->query('SELECT status, COUNT(*) as cnt FROM products GROUP BY status');
while ($row = $countRes->fetch_assoc()) {
    $counts[$row['status']] = (int)$row['cnt'];
    $counts['all'] += (int)$row['cnt'];
}

// ─────────────────────────────────────────────
// DISTINCT CATEGORIES for dropdown
// ─────────────────────────────────────────────
$categories = [];
$catRes = $conn->query('SELECT DISTINCT category FROM products ORDER BY category');
while ($row = $catRes->fetch_assoc()) {
    $categories[] = $row['category'];
}

// Helper: badge class by status
function badgeClass(string $status): string {
    return match ($status) {
        'In Stock'     => 'badge-green',
        'Low Stock'    => 'badge-yellow',
        'Out of Stock' => 'badge-red',
        default        => 'badge-green',
    };
}

include __DIR__ . '/sidebar.php';
?>

<div class="main-content">

  <div class="page-header">
    <div class="page-header-row">
      <h1>Products</h1>
      <a href="add_product.php" class="btn-add-product">
        <i class="fa-solid fa-plus"></i> Add Product
      </a>
    </div>
  </div>

  <div class="page-body">

    <!-- FILTER ROW -->
    <form method="GET" action="products.php" class="filter-row">
      <!-- Preserve active tab when filtering -->
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

    <!-- STATUS TABS -->
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

    <!-- TABLE -->
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

  </div><!-- /.page-body -->
</div><!-- /.main-content -->

<?php
$stmt->close();
$conn->close();
?>

</body>
</html>
