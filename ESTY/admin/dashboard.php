<?php
session_start();
require_once "../db.php";
include 'sidebar.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Summary Data
$total_orders = $conn->query("SELECT COUNT(*) AS count FROM orders")->fetch_assoc()['count'];
$total_sales = $conn->query("SELECT SUM(total) AS sum FROM orders WHERE status = 'completed'")->fetch_assoc()['sum'] ?? 0;
$pending_orders = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) AS count FROM products")->fetch_assoc()['count'];

// Recent Orders
$recent_orders = $conn->query("
    SELECT id, customer_name, total, status, created_at
    FROM orders
    ORDER BY created_at DESC
    LIMIT 5
");

// Recently Added Products
$recent_products = $conn->query("
    SELECT name, price, created_at
    FROM products
    ORDER BY created_at DESC
    LIMIT 5
");

// Monthly Sales Data (for Chart.js)
$sales_data = [];
$months = [];
$query = "
    SELECT DATE_FORMAT(created_at, '%b') AS month, SUM(total) AS total
    FROM orders
    WHERE status = 'completed'
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)
";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $months[] = $row['month'];
    $sales_data[] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Esty Scents</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="admin-style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      margin-top: 30px;
    }
    .dashboard-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
      padding: 25px;
      text-align: center;
      transition: all 0.3s ease-in-out;
    }
    .dashboard-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(255, 182, 193, 0.4);
    }
    .dashboard-card i {
      font-size: 3rem;
      margin-bottom: 10px;
      color: #ffb6c1;
    }
    .dashboard-card h5 {
      font-weight: 600;
      color: #4B3F2F;
      margin-bottom: 10px;
    }
    .dashboard-card h3 {
      font-weight: bold;
      color: #2d2d2d;
    }
    .section-title {
      font-weight: 700;
      color: #4B3F2F;
      margin-top: 50px;
      margin-bottom: 20px;
      border-left: 5px solid #ffb6c1;
      padding-left: 10px;
    }
    table {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.08);
      overflow: hidden;
    }
    table th {
      background: #ffb6c1;
      color: #4B3F2F;
      font-weight: 600;
    }
    table td, table th {
      vertical-align: middle;
    }
    .status-badge {
      padding: 5px 10px;
      border-radius: 10px;
      font-size: 0.9rem;
    }
    .status-pending {
      background: #fff3cd;
      color: #856404;
    }
    .status-completed {
      background: #d4edda;
      color: #155724;
    }
    .status-cancelled {
      background: #f8d7da;
      color: #721c24;
    }
    .chart-container {
      background: #fff;
      padding: 20px;
      border-radius: 20px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.08);
      margin-top: 40px;
    }
  </style>
</head>
<body>

<div class="main-content">
  <h2 class="fw-bold mb-3">Admin Dashboard</h2>

  <!-- Dashboard Summary -->
  <div class="dashboard-cards">
    <div class="dashboard-card">
      <i class="bi bi-cart-check"></i>
      <h5>Total Orders</h5>
      <h3><?= $total_orders; ?></h3>
    </div>
    <div class="dashboard-card">
      <i class="bi bi-cash-stack"></i>
      <h5>Total Sales</h5>
      <h3>₱<?= number_format($total_sales, 2); ?></h3>
    </div>
    <div class="dashboard-card">
      <i class="bi bi-hourglass-split"></i>
      <h5>Pending Orders</h5>
      <h3><?= $pending_orders; ?></h3>
    </div>
    <div class="dashboard-card">
      <i class="bi bi-box-seam"></i>
      <h5>Total Products</h5>
      <h3><?= $total_products; ?></h3>
    </div>
  </div>

  <!-- Monthly Sales Chart -->
  <div class="chart-container">
    <h4 class="section-title mb-4"><i class="bi bi-bar-chart-line me-2"></i>Monthly Sales Overview</h4>
    <canvas id="salesChart" height="120"></canvas>
  </div>

  <!-- Recent Orders -->
  <h4 class="section-title"><i class="bi bi-receipt-cutoff me-2"></i>Recent Orders</h4>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Customer</th>
          <th>Total</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php while($o = $recent_orders->fetch_assoc()): ?>
          <tr>
            <td><?= $o['id']; ?></td>
            <td><?= htmlspecialchars($o['customer_name']); ?></td>
            <td>₱<?= number_format($o['total'], 2); ?></td>
            <td>
              <span class="status-badge status-<?= strtolower($o['status']); ?>">
                <?= ucfirst($o['status']); ?>
              </span>
            </td>
            <td><?= date("M d, Y", strtotime($o['created_at'])); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Recently Added Products -->
  <h4 class="section-title"><i class="bi bi-box2-heart me-2"></i>Recently Added Products</h4>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Product</th>
          <th>Price</th>
          <th>Date Added</th>
        </tr>
      </thead>
      <tbody>
        <?php while($p = $recent_products->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($p['name']); ?></td>
            <td>₱<?= number_format($p['price'], 2); ?></td>
            <td><?= date("M d, Y", strtotime($p['created_at'])); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
const ctx = document.getElementById('salesChart');
const salesChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?= json_encode($months); ?>,
    datasets: [{
      label: 'Total Sales (₱)',
      data: <?= json_encode($sales_data); ?>,
      backgroundColor: 'rgba(255, 182, 193, 0.4)',
      borderColor: '#ffb6c1',
      borderWidth: 3,
      fill: true,
      tension: 0.3,
      pointBackgroundColor: '#ff69b4',
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true }
    },
    plugins: {
      legend: { display: true, position: 'bottom' }
    }
  }
});
</script>

</body>
</html>
