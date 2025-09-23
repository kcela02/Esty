<?php
require_once "../db.php";

if (!isset($_GET['id'])) {
    die("Order ID missing.");
}

$orderId = intval($_GET['id']);

// Fetch order
$order = $conn->query("SELECT * FROM orders WHERE id = $orderId")->fetch_assoc();

// Fetch order items
$items = $conn->query("SELECT * FROM order_items WHERE order_id = $orderId");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order #<?= $orderId; ?> Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
  <h2>Order #<?= $orderId; ?> Details</h2>
  <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']); ?></p>
  <p><strong>Email:</strong> <?= htmlspecialchars($order['email']); ?></p>
  <p><strong>Address:</strong> <?= htmlspecialchars($order['address']); ?></p>
  <p><strong>Payment Method:</strong> <?= $order['payment_method']; ?></p>
  <p><strong>Total:</strong> ₱<?= number_format($order['total'], 2); ?></p>
  <p><strong>Date:</strong> <?= $order['created_at']; ?></p>

  <h4 class="mt-4">Items</h4>
  <table class="table table-bordered">
    <thead class="table-secondary">
      <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Qty</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($item = $items->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($item['product_name']); ?></td>
          <td>₱<?= number_format($item['price'], 2); ?></td>
          <td><?= $item['quantity']; ?></td>
          <td>₱<?= number_format($item['subtotal'], 2); ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <a href="orders.php" class="btn btn-secondary">⬅ Back to Orders</a>
</div>

</body>
</html>
