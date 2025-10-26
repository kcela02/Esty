<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once '../db.php';
require_once __DIR__ . '/admin_helpers.php';
include 'sidebar.php';

requireAdminLogin();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$customers = fetchCustomersWithMetrics($conn, $search);

$totalCustomers = count($customers);
$customersWithOrders = 0;
$totalRevenue = 0.0;

foreach ($customers as $customer) {
    if ((int)($customer['order_count'] ?? 0) > 0) {
        $customersWithOrders++;
    }
    $totalRevenue += (float)($customer['total_spent'] ?? 0);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customers - Esty Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="admin-style.css">
  <style>
    .stats-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.08);
      padding: 22px;
      text-align: center;
      transition: all 0.3s ease-in-out;
    }
    .stats-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 25px rgba(255,182,193,0.4);
    }
    .stats-card h3 {
      font-size: 2rem;
      font-weight: 700;
      color: #2d2d2d;
    }
    .stats-card span {
      font-weight: 600;
      color: #7a6a56;
    }
    .badge-order {
      background-color: #ffb6c1;
      color: #4B3F2F;
    }
    .table thead {
      background-color: #ffb6c1;
      color: #4B3F2F;
    }
    .search-bar {
      max-width: 320px;
    }
    .customer-actions .btn {
      border-radius: 20px;
    }
  </style>
</head>
<body>


<div class="main-content">
  <div class="card mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 p-3 pb-0">
      <div>
        <h2 class="fw-bold mb-1">Customers</h2>
        <p class="muted mb-0">Monitor your shopper community and their order history.</p>
      </div>
      <form method="get" class="d-flex gap-2 search-bar">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input type="text"
                 class="form-control"
                 name="search"
                 value="<?= htmlspecialchars($search); ?>"
                 placeholder="Search by name or email">
        </div>
        <?php if ($search !== ''): ?>
          <a href="customers.php" class="btn btn-outline-secondary">Clear</a>
        <?php endif; ?>
        <button type="submit" class="btn btn-add">
          <i class="bi bi-filter"></i> Filter
        </button>
      </form>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="stats-card">
        <span>Total Customers<?= $search !== '' ? ' (matching)' : ''; ?></span>
        <h3><?= number_format($totalCustomers); ?></h3>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stats-card">
        <span>Customers with Orders</span>
        <h3><?= number_format($customersWithOrders); ?></h3>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stats-card">
        <span>Lifetime Completed Sales</span>
        <h3>₱<?= number_format($totalRevenue, 2); ?></h3>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Customer Directory</h5>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
          <i class="bi bi-printer"></i> Print
        </button>
      </div>
      <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
          <thead>
            <tr>
              <th scope="col">ID</th>
              <th scope="col">Username</th>
              <th scope="col">Email</th>
              <th scope="col">Joined</th>
              <th scope="col">Orders</th>
              <th scope="col">Completed Spend</th>
              <th scope="col">Last Order</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($customers)): ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No customers found<?= $search !== '' ? ' for this search.' : ' yet.'; ?></td>
            </tr>
          <?php else: ?>
            <?php foreach ($customers as $customer): ?>
              <?php
                $orderCount = (int)($customer['order_count'] ?? 0);
                $totalSpent = (float)($customer['total_spent'] ?? 0);
                $lastOrder = $customer['last_order_at'] ?? null;
              ?>
              <tr>
                <td><?= (int)$customer['id']; ?></td>
                <td><?= htmlspecialchars($customer['username']); ?></td>
                <td><?= htmlspecialchars($customer['email']); ?></td>
                <td><?= date('M d, Y', strtotime($customer['created_at'])); ?></td>
                <td>
                  <?php if ($orderCount > 0): ?>
                    <span class="badge bg-pink px-3 py-2"><?= number_format($orderCount); ?> orders</span>
                  <?php else: ?>
                    <span class="text-muted">None yet</span>
                  <?php endif; ?>
                </td>
                <td>₱<?= number_format($totalSpent, 2); ?></td>
                <td>
                  <?php if ($lastOrder): ?>
                    <?= date('M d, Y', strtotime($lastOrder)); ?>
                  <?php else: ?>
                    <span class="text-muted">N/A</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
