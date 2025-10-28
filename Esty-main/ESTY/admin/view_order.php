<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once "../db.php";

requireAdminLogin();

if (!isset($_GET['id'])) {
    die("Order ID missing.");
}

$orderId = intval($_GET['id']);

// Fetch order
$orderStmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$orderStmt->bind_param("i", $orderId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
$order = $orderResult ? $orderResult->fetch_assoc() : null;
$orderStmt->close();

if (!$order) {
  header("Location: orders.php");
  exit;
}

$items = [];
$itemsStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemsStmt->bind_param("i", $orderId);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();
if ($itemsResult) {
  while ($row = $itemsResult->fetch_assoc()) {
    $items[] = $row;
  }
}
$itemsStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order #<?= $orderId; ?> Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="main-content">
  <div class="card mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 p-3 pb-0">
      <div>
        <h2 class="fw-bold mb-1">Order #<?= $orderId; ?> Details</h2>
        <p class="muted mb-0">Full order breakdown and items</p>
      </div>
      <a href="orders.php" class="btn btn-outline-primary">⬅ Back to Orders</a>
    </div>
    <div class="row g-3 p-3">
      <div class="col-md-6">
        <div class="mb-2"><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']); ?></div>
        <div class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($order['email']); ?></div>
        <div class="mb-2"><strong>Address:</strong> <?= htmlspecialchars($order['address']); ?></div>
        <div class="mb-2"><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']); ?></div>
      </div>
      <div class="col-md-6">
        <div class="mb-2"><strong>Total:</strong> ₱<?= number_format($order['total'], 2); ?></div>
        <div class="mb-2"><strong>Date:</strong> <?= date('M d, Y H:i', strtotime($order['created_at'])); ?></div>
        <div class="mb-2"><strong>Status:</strong> <span class="badge bg-<?= strtolower($order['status']); ?> px-3 py-2"><?= ucfirst($order['status']); ?></span></div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-body">
      <h5 class="mb-3"><i class="bi bi-bag-check me-2"></i>Order Items</h5>
      <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
          <thead>
            <tr>
              <th>Product</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
              <tr>
                <td><?= htmlspecialchars($item['product_name']); ?></td>
                <td>₱<?= number_format($item['price'], 2); ?></td>
                <td><?= $item['quantity']; ?></td>
                <td>₱<?= number_format($item['subtotal'], 2); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

</body>
</html>
