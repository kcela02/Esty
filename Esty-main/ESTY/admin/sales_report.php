<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once '../db.php';

requireAdminLogin();

// Date filter
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-d');

$stmt = $conn->prepare("SELECT COUNT(*) as order_count, SUM(total) as gross_sales, SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END) as completed_sales, AVG(total) as avg_order FROM orders WHERE created_at BETWEEN ? AND ?");
$startTime = $start . ' 00:00:00';
$endTime = $end . ' 23:59:59';
$stmt->bind_param('ss', $startTime, $endTime);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

// CSV export (orders in range)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="sales_report_' . $start . '_to_' . $end . '.csv"');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['Order ID', 'Customer', 'Status', 'Total', 'Created At']);
  foreach ($orders as $o) {
    fputcsv($out, [
      $o['id'],
      $o['customer_name'],
      $o['status'],
      $o['total'],
      $o['created_at']
    ]);
  }
  fclose($out);
  exit;
}

include 'sidebar.php';

$orders = [];
$stmt = $conn->prepare("SELECT * FROM orders WHERE created_at BETWEEN ? AND ? ORDER BY created_at DESC");
$stmt->bind_param('ss', $startTime, $endTime);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $orders[] = $row;
$stmt->close();
?>
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>Sales Report - Esty Admin</title>
  <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
  <link rel='stylesheet' href='admin-style.css'>
</head>
<body>
<div class='main-content'>
  <div class='card mb-4'>
    <div class='d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 p-3 pb-0'>
      <div>
        <h2 class='fw-bold mb-1'>Sales Report</h2>
        <p class='muted mb-0'>Summary and breakdown of sales/orders.</p>
      </div>
      <form method='get' class='d-flex gap-2'>
        <input type='date' name='start' class='form-control' value='<?= htmlspecialchars($start) ?>'>
        <input type='date' name='end' class='form-control' value='<?= htmlspecialchars($end) ?>'>
        <button class='btn btn-add'>Filter</button>
        <a class='btn btn-outline-secondary' href='sales_report.php?start=<?= urlencode($start) ?>&end=<?= urlencode($end) ?>&export=csv'>Export CSV</a>
      </form>
    </div>
    <div class='row g-3 p-3'>
      <div class='col-md-3'><div class='stats-card'><span>Total Orders</span><h3><?= (int)($summary['order_count'] ?? 0) ?></h3></div></div>
      <div class='col-md-3'><div class='stats-card'><span>Gross Sales</span><h3>₱<?= number_format($summary['gross_sales'] ?? 0, 2) ?></h3></div></div>
      <div class='col-md-3'><div class='stats-card'><span>Completed Sales</span><h3>₱<?= number_format($summary['completed_sales'] ?? 0, 2) ?></h3></div></div>
      <div class='col-md-3'><div class='stats-card'><span>Avg. Order Value</span><h3>₱<?= number_format($summary['avg_order'] ?? 0, 2) ?></h3></div></div>
    </div>
  </div>
  <div class='card'>
    <div class='card-body'>
      <h5 class='mb-3'><i class='bi bi-receipt me-2'></i>Orders in Range</h5>
      <div class='table-responsive'>
        <table class='table table-striped align-middle mb-0'>
          <thead>
            <tr>
              <th>ID</th><th>Customer</th><th>Status</th><th>Total</th><th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orders)): ?>
              <tr><td colspan='5' class='text-center text-muted py-4'>No orders found.</td></tr>
            <?php else: foreach ($orders as $o): ?>
              <tr>
                <td><?= (int)$o['id'] ?></td>
                <td><?= htmlspecialchars($o['customer_name']) ?></td>
                <td><span class='badge bg-<?= strtolower($o['status']) ?> px-3 py-2'><?= ucfirst($o['status']) ?></span></td>
                <td>₱<?= number_format($o['total'], 2) ?></td>
                <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
