<?php
$currentPage  = basename($_SERVER['SCRIPT_NAME']);
$sidebarUser  = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';
$sidebarInit  = strtoupper(substr($sidebarUser, 0, 2));
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
    <a href="dashboard.php" class="<?= $currentPage==='dashboard.php'?'active':'' ?>">
      <i class="fa-solid fa-chart-bar"></i><span>Dashboard</span>
    </a>
    <a href="products.php" class="<?= $currentPage==='products.php'?'active':'' ?>">
      <i class="fa-solid fa-capsules"></i><span>Products</span>
    </a>
    <a href="stocks.php" class="<?= $currentPage==='stocks.php'?'active':'' ?>">
      <i class="fa-solid fa-cart-shopping"></i><span>Stocks</span>
    </a>
    <a href="users.php" class="<?= $currentPage==='users.php'?'active':'' ?>">
      <i class="fa-solid fa-user"></i><span>Users</span>
    </a>
    <a href="reservations_admin.php" class="<?= $currentPage==='reservations_admin.php'?'active':'' ?>">
      <i class="fa-solid fa-calendar-check"></i><span>Reservations</span>
    </a>

    <div class="sidebar-section-label">MONITORING</div>
    <a href="expiry.php" class="<?= $currentPage==='expiry.php'?'active':'' ?>">
      <i class="fa-regular fa-clock"></i><span>Expiry</span>
    </a>
    <a href="lowstock.php" class="<?= $currentPage==='lowstock.php'?'active':'' ?>">
      <i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="avatar"><?= $sidebarInit ?></div>
    <div class="admin-info">
      <div class="name"><?= $sidebarUser ?></div>
      <div class="role">Administrator</div>
    </div>
    <a href="logout.php" class="logout-btn" title="Logout">
      <i class="fa-solid fa-right-from-bracket"></i>
    </a>
  </div>
</div>