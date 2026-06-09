<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
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
    <a href="dashboard.php" class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
      <i class="fa-solid fa-chart-bar"></i>
      <span>Dashboard</span>
    </a>
    <a href="products.php" class="<?php echo ($currentPage == 'products.php') ? 'active' : ''; ?>">
      <i class="fa-solid fa-capsules"></i>
      <span>Products</span>
    </a>
    <a href="stocks.php" class="<?php echo ($currentPage == 'stocks.php') ? 'active' : ''; ?>">
      <i class="fa-solid fa-cart-shopping"></i>
      <span>Stocks</span>
    </a>
    <a href="users.php" class="<?php echo ($currentPage == 'users.php') ? 'active' : ''; ?>">
      <i class="fa-solid fa-user"></i>
      <span>Users</span>
    </a>

    <div class="sidebar-section-label">MONITORING</div>
    <a href="expiry.php" class="<?php echo ($currentPage == 'expiry.php') ? 'active' : ''; ?>">
      <i class="fa-regular fa-clock"></i>
      <span>Expiry</span>
    </a>
    <a href="lowstock.php" class="<?php echo ($currentPage == 'lowstock.php') ? 'active' : ''; ?>">
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
    <a href="admin-login.php" class="logout-btn" title="Logout">
      <i class="fa-solid fa-right-from-bracket"></i>
    </a>
  </div>
</div>
