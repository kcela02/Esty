<!-- Sidebar -->
<?php
// admin pages: determine current slug for active link
$current = basename($_SERVER['SCRIPT_NAME']); // returns filename e.g. "dashboard.php" or "index.php"
// map index to dashboard if needed
if ($current === 'index.php') { $current = 'dashboard.php'; }
?>


<div class="sidebar">
  <h4>Admin Panel</h4>
  <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
  <a href="orders.php"    class="<?= $current === 'orders.php' ? 'active' : ''; ?>">Orders</a>
  <a href="products.php"  class="<?= $current === 'products.php' ? 'active' : ''; ?>">Products</a>
  <a href="subscribers.php" class="<?= $current === 'subscribers.php' ? 'active' : ''; ?>">Subscribers</a>
  <a href="logout.php">Logout</a>
</div>
