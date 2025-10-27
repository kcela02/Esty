<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
  .sidebar {
    width: 250px;
    background: linear-gradient(180deg, #ffb6c1 0%, #ffe6ec 100%);
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    padding: 20px 0;
    display: flex;
    flex-direction: column;
    box-shadow: 4px 0 15px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    overflow-y: auto;
  }

  .sidebar h4 {
    font-weight: 700;
    color: #4B3F2F;
    text-align: center;
    margin-bottom: 25px;
  }

  .sidebar a {
    display: flex;
    align-items: center;
    color: #4B3F2F;
    text-decoration: none;
    padding: 12px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
    border-radius: 10px;
    margin: 3px 10px;
  }

  .sidebar a i {
    font-size: 1.2rem;
    margin-right: 10px;
  }

  .sidebar a:hover {
    background-color: #fff;
    color: #e75480;
    transform: translateX(5px);
  }

  .sidebar a.active {
    background-color: #fff;
    color: #e75480;
    box-shadow: inset 0 0 8px rgba(231,84,128,0.2);
  }

  .sidebar a.active i {
    color: #e75480;
  }

  .logout-btn {
    margin-top: auto;
    padding: 15px 20px;
    text-align: center;
  }

  .logout-btn a {
    background-color: #e75480;
    color: white;
    border-radius: 25px;
    padding: 10px 20px;
    display: inline-block;
    transition: 0.3s;
  }

  .logout-btn a:hover {
    background-color: #d94a70;
  }

  /* 🌺 Collapsible Menu Styling */
  .collapse-menu {
    background: transparent;
    border: none;
    width: 100%;
    text-align: left;
    color: white;
    font-weight: 600;
    padding: 12px 20px;
    border-radius: 10px;
    margin: 3px 10px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .collapse-menu:hover {
    background-color: rgba(255, 255, 255, 0.3);
    color: black;
  }

  .collapse-menu.active {
    background-color: rgba(255, 255, 255, 0.6);
    color: black;
  }

  .collapse-menu i {
    margin-right: 10px;
  }

  .collapse-menu .arrow {
    transition: transform 0.3s ease;
  }

  .collapse-menu.active .arrow {
    transform: rotate(180deg);
  }

  .collapse a {
    font-size: 0.9rem;
    padding-left: 50px;
  }

  @media (max-width: 768px) {
    .sidebar {
      width: 70px;
      padding: 10px 0;
    }

    .sidebar a span,
    .collapse-menu span,
    .collapse-menu .arrow {
      display: none;
    }

    .sidebar a i, .collapse-menu i {
      margin: 0 auto;
    }

    .sidebar h4 {
      display: none;
    }
  }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="sidebar">
  <h4><i class="bi bi-flower1 me-1"></i>Esty Admin</h4>

  <a href="dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
    <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
  </a>

  <a href="orders.php" class="<?= ($current_page == 'orders.php') ? 'active' : ''; ?>">
    <i class="bi bi-receipt"></i> <span>Orders</span>
  </a>

  <a href="products.php" class="<?= ($current_page == 'products.php') ? 'active' : ''; ?>">
    <i class="bi bi-box-seam"></i> <span>Products</span>
  </a>

  <a href="manage_categories.php" class="<?= ($current_page == 'manage_categories.php') ? 'active' : ''; ?>">
    <i class="bi bi-tags"></i> <span>Categories</span>
  </a>

  <a href="manage_brands.php" class="<?= ($current_page == 'manage_brands.php') ? 'active' : ''; ?>">
    <i class="bi bi-bookmark"></i> <span>Brands</span>
  </a>

  <a href="customers.php" class="<?= ($current_page == 'customers.php') ? 'active' : ''; ?>">
    <i class="bi bi-people"></i> <span>Customers</span>
  </a>

  <!-- 🌸 Reports & Logs Dropdown -->
  <button class="collapse-menu" id="reportsToggle">
    <div>
      <i class="bi bi-bar-chart"></i> <span>Reports & Logs</span>
    </div>
    <i class="bi bi-chevron-down arrow"></i>
  </button>

  <div class="collapse" id="reportsMenu">
    <a href="sales_report.php" class="<?= ($current_page == 'sales_report.php') ? 'active' : ''; ?>">
      <i class="bi bi-graph-up"></i> Sales Report
    </a>
    <a href="activity_logs.php" class="<?= ($current_page == 'activity_logs.php') ? 'active' : ''; ?>">
      <i class="bi bi-journal-text"></i> Activity Logs
    </a>
    <a href="account_report.php" class="<?= ($current_page == 'account_report.php') ? 'active' : ''; ?>">
      <i class="bi bi-person-lines-fill"></i> Account Reports
    </a>
      <a href="admin_users.php" class="<?= ($current_page == 'admin_users.php') ? 'active' : ''; ?>">
        <i class="bi bi-shield-lock"></i> Admin User Management
      </a>
  </div>

  <div class="logout-btn">
    <a href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
  </div>
</div>

<script>
  const reportsToggle = document.getElementById('reportsToggle');
  const reportsMenu = document.getElementById('reportsMenu');

  // Toggle open/close manually without Bootstrap auto behavior
  reportsToggle.addEventListener('click', () => {
    const isOpen = reportsMenu.classList.contains('show');
    reportsMenu.classList.toggle('show', !isOpen);
    reportsToggle.classList.toggle('active', !isOpen);
  });

  // Keep dropdown open if any child link is active
  const currentPage = "<?= $current_page ?>";
  if (["sales_report.php", "activity_logs.php", "account_report.php"].includes(currentPage)) {
    reportsMenu.classList.add('show');
    reportsToggle.classList.add('active');
  }
</script>
