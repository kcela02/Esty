<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once "../db.php";
include 'sidebar.php';

requireAdminLogin();

// Summary Data
$total_orders = $conn->query("SELECT COUNT(*) AS count FROM orders")->fetch_assoc()['count'];
$total_sales = $conn->query("SELECT SUM(total) AS sum FROM orders WHERE status = 'completed'")->fetch_assoc()['sum'] ?? 0;
$pending_orders = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$processing_orders = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE status = 'processing'")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) AS count FROM products")->fetch_assoc()['count'];
$low_stock_threshold = 5;
$low_stock_products = $conn->prepare("SELECT id, name, COALESCE(stock,0) as stock FROM products WHERE COALESCE(stock,0) <= ? ORDER BY stock ASC, name ASC LIMIT 10");
$low_stock_products->bind_param("i", $low_stock_threshold);
$low_stock_products->execute();
$low_stock_list = $low_stock_products->get_result();

// Recent Orders (include payment method)
$recent_orders = $conn->query("
  SELECT id, customer_name, payment_method, total, status, created_at
  FROM orders
  WHERE status != 'completed'
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
    /* 🌸 Dashboard Layout */
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

    /* 🪶 Section Styling */
    .section-title {
      font-weight: 700;
      color: #4B3F2F;
      margin-top: 50px;
      margin-bottom: 20px;
      border-left: 5px solid #ffb6c1;
      padding-left: 10px;
    }

    /* 🌼 Table Styling */
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

    /* 🟡 Unified Status Badges */
    .status-badge {
      padding: 5px 12px;
      border-radius: 12px;
      font-size: 0.9rem;
      font-weight: 600;
      text-transform: capitalize;
    }
    .status-pending {
      background-color: #fff1cc;
      color: #7a6000;
    }
    .status-processing {
      background-color: #ffe0b2;
      color: #8a4d00;
      animation: processingPulse 2s infinite ease-in-out;
    }
    @keyframes processingPulse {
      0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
      70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
      100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
    }
    .status-completed {
      background-color: #d4edda;
      color: #0a682e;
    }
    .status-cancelled {
      background-color: #f8d7da;
      color: #9b1b30;
    }

    /* 📊 Chart Section */
    .chart-container {
      background: #fff;
      padding: 20px;
      border-radius: 20px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.08);
      margin-top: 40px;
    }
    .toggle-btn {
      background-color: #ffb6c1;
      color: #4B3F2F;
      border: none;
      border-radius: 25px;
      padding: 6px 16px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .toggle-btn:hover {
      background-color: #ff91a4;
      color: white;
    }
  </style>
</head>
<body>


<div class="main-content">
  <div class="card mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 p-3 pb-0">
      <div>
        <h2 class="fw-bold mb-1">Admin Dashboard</h2>
        <p class="muted mb-0">Quick stats and sales overview.</p>
      </div>
    </div>
    <div class="dashboard-cards">
      <div class="dashboard-card">
        <i class="bi bi-cart-check"></i>
        <h5>Total Orders</h5>
        <h3><?= $total_orders; ?></h3>
      </div>
      <div class="dashboard-card">
        <i class="bi bi-hourglass-split"></i>
        <h5>Pending Orders</h5>
        <h3><?= $pending_orders; ?></h3>
      </div>
      <div class="dashboard-card">
        <i class="bi bi-clock-history"></i>
        <h5>Processing Orders</h5>
        <h3><?= $processing_orders; ?></h3>
      </div>
      <div class="dashboard-card">
        <i class="bi bi-cash-stack"></i>
        <h5>Total Sales</h5>
        <h3>₱<?= number_format($total_sales, 2); ?></h3>
      </div>
      <div class="dashboard-card">
        <i class="bi bi-box-seam"></i>
        <h5>Total Products</h5>
        <h3><?= $total_products; ?></h3>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="chart-container">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <h4 class="section-title mb-0"><i class="bi bi-bar-chart-line me-2"></i>Reports Overview</h4>
          <select id="metricSelect" class="form-select form-select-sm" style="width:auto;">
            <option value="sales" selected>Sales (₱)</option>
            <option value="count">Order Count</option>
            <option value="avg">Average Order Value (₱)</option>
          </select>
          <select id="monthSelect" class="form-select form-select-sm" style="width:auto;">
            <?php for ($m = 1; $m <= 12; $m++): ?>
              <option value="<?= $m; ?>" <?= $m == date('n') ? 'selected' : ''; ?>>
                <?= date('F', mktime(0, 0, 0, $m, 1)); ?>
              </option>
            <?php endfor; ?>
          </select>
          <select id="yearSelect" class="form-select form-select-sm" style="width:auto;">
            <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
              <option value="<?= $y; ?>"><?= $y; ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <button id="toggleChartType" class="toggle-btn">
          <i class="bi bi-graph-up"></i> Switch to Bar
        </button>
      </div>
      <canvas id="salesChart" height="120"></canvas>
      <div id="salesSummary" class="mt-4 text-center fw-semibold" style="color:#4B3F2F;"></div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="section-title mb-3"><i class="bi bi-receipt-cutoff me-2"></i>Recent Orders</h5>
          <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Customer</th>
                  <th>Payment</th>
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
                    <td><?= htmlspecialchars($o['payment_method'] ?? ''); ?></td>
                    <td>₱<?= number_format($o['total'], 2); ?></td>
                    <td><span class="badge bg-<?= strtolower($o['status']); ?> px-3 py-2"><?= ucfirst($o['status']); ?></span></td>
                    <td><?= date("M d, Y", strtotime($o['created_at'])); ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="section-title mb-3"><i class="bi bi-box2-heart me-2"></i>Recently Added Products</h5>
          <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
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
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="section-title mb-3"><i class="bi bi-pie-chart me-2"></i>Order Status Distribution (Last 30 days)</h5>
          <canvas id="statusChart" height="220"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="section-title mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Low Stock Alerts (≤ <?= $low_stock_threshold; ?>)</h5>
          <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Stock</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($low_stock_list && $low_stock_list->num_rows > 0): ?>
                  <?php while ($ls = $low_stock_list->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars($ls['name']); ?></td>
                      <td>
                        <?php if ((int)$ls['stock'] <= 0): ?>
                          <span class="badge bg-secondary">Out</span>
                        <?php elseif ((int)$ls['stock'] <= $low_stock_threshold): ?>
                          <span class="badge bg-warning text-dark">Low (<?= (int)$ls['stock']; ?>)</span>
                        <?php endif; ?>
                      </td>
                      <td><a href="products.php" class="btn btn-outline-primary btn-sm">Manage</a></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="3" class="text-center text-muted">All stocks look healthy.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js -->
<script>
const ctx = document.getElementById('salesChart');
let salesChart;
let currentType = 'line';

function loadChart() {
  const month = document.getElementById('monthSelect').value;
  const year = document.getElementById('yearSelect').value;
  const metric = document.getElementById('metricSelect').value;

  fetch(`get_sales_data.php?month=${month}&year=${year}&metric=${metric}`)
    .then(res => res.json())
    .then(data => {
      const summaryDiv = document.getElementById('salesSummary');
      const percentChange = data.previousTotal > 0
        ? ((data.currentTotal - data.previousTotal) / data.previousTotal * 100).toFixed(1)
        : 100;
      const trendIcon = percentChange >= 0
        ? '<i class="bi bi-arrow-up-right text-success"></i>'
        : '<i class="bi bi-arrow-down-right text-danger"></i>';

      const unit = metric === 'count' ? 'orders' : '₱';
      const label =
        metric === 'sales' ? 'Sales (₱)' :
        metric === 'count' ? 'Order Count' :
        'Average Order Value (₱)';

      summaryDiv.innerHTML = `
        <span>${data.currentMonth} Total: <strong>${unit}${data.currentTotal.toLocaleString()}</strong></span><br>
        <small>${trendIcon} vs ${data.previousMonth}: ${unit}${data.previousTotal.toLocaleString()} (${percentChange}% change)</small>
      `;

      if (salesChart) salesChart.destroy();

      salesChart = new Chart(ctx, {
        type: currentType,
        data: {
          labels: data.days.map(d => "Day " + d),
          datasets: [{
            label,
            data: data.values,
            backgroundColor: currentType === 'bar' ? 'rgba(255, 182, 193, 0.6)' : 'rgba(255, 182, 193, 0.3)',
            borderColor: '#ff8fab',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#ff69b4'
          }]
        },
        options: {
          responsive: true,
          animation: { duration: 1000, easing: 'easeInOutCubic' },
          scales: { y: { beginAtZero: true } },
          plugins: { legend: { display: true, position: 'bottom' } }
        }
      });
    });
}

// 🪄 FIXED Toggle Button Behavior
document.getElementById('toggleChartType').addEventListener('click', () => {
  currentType = currentType === 'line' ? 'bar' : 'line';

  // 🔁 Update button text and icon dynamically
  const toggleBtn = document.getElementById('toggleChartType');
  if (currentType === 'bar') {
    toggleBtn.innerHTML = '<i class="bi bi-bar-chart"></i> Switch to Line';
  } else {
    toggleBtn.innerHTML = '<i class="bi bi-graph-up"></i> Switch to Bar';
  }

  loadChart();
});

// Refresh chart when filters change
['monthSelect', 'yearSelect', 'metricSelect'].forEach(id =>
  document.getElementById(id).addEventListener('change', loadChart)
);

loadChart();
</script>

<?php
// Prepare status distribution data (last 30 days)
$statusData = [];
$q = $conn->query("SELECT status, COUNT(*) as cnt FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY status");
while ($r = $q->fetch_assoc()) { $statusData[$r['status']] = (int)$r['cnt']; }
$labels = array_keys($statusData);
$values = array_values($statusData);
?>

<script>
// Doughnut chart for order statuses
const statusCtx = document.getElementById('statusChart');
if (statusCtx) {
  new Chart(statusCtx, {
    type: 'doughnut',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [{
        data: <?= json_encode($values) ?>,
        backgroundColor: ['#fff1cc', '#ffe0b2', '#d4edda', '#f8d7da', '#cde7f0', '#ffd1dc'],
        borderColor: '#ffffff',
        borderWidth: 2
      }]
    },
    options: {
      plugins: { legend: { position: 'bottom' } },
      cutout: '60%'
    }
  });
}
</script>


</body>
</html>
