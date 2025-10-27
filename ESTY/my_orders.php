<?php
session_start();
require 'db.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// get user email
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

$email = $user['email'] ?? null;

// fetch orders for this user (by email)
$orders = [];
if ($email) {
    $stmt = $conn->prepare("SELECT id, customer_name, email, total, created_at, status FROM orders WHERE email = ? ORDER BY created_at DESC");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Orders - Esty Scents</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="style.css">
  <style>body { padding-top: 80px; }</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-5 pt-3">
  <h2 class="mb-4">My Orders</h2>

  <?php if (empty($orders)): ?>
    <div class="alert alert-info">You have no orders yet.</div>
  <?php else: ?>
    <div class="accordion" id="ordersAccordion">
      <?php foreach ($orders as $i => $order): ?>
        <div class="card mb-2">
          <div class="card-header d-flex justify-content-between align-items-center" id="heading<?= $order['id'] ?>">
            <div>
              <strong>Order #<?= $order['id'] ?></strong>
              <div class="text-muted small">Placed: <?= date('F j, Y, g:ia', strtotime($order['created_at'])) ?></div>
            </div>
            <div class="text-end">
              <div>Status: <span class="badge bg-secondary"><?= htmlspecialchars($order['status']) ?></span></div>
              <div class="mt-1">Total: ₱<?= number_format($order['total'], 2) ?></div>
              <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $order['id'] ?>" aria-expanded="false" aria-controls="collapse<?= $order['id'] ?>">View details</button>
            </div>
          </div>

          <div id="collapse<?= $order['id'] ?>" class="collapse" aria-labelledby="heading<?= $order['id'] ?>" data-bs-parent="#ordersAccordion">
            <div class="card-body">
              <?php
                $stmt = $conn->prepare("SELECT product_name, price, quantity, subtotal FROM order_items WHERE order_id = ?");
                $stmt->bind_param('i', $order['id']);
                $stmt->execute();
                $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
              ?>
              <?php if (empty($items)): ?>
                <p class="text-muted">No items found for this order.</p>
              <?php else: ?>
                <table class="table">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>Price</th>
                      <th>Qty</th>
                      <th>Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($items as $it): ?>
                      <tr>
                        <td><?= htmlspecialchars($it['product_name']) ?></td>
                        <td>₱<?= number_format($it['price'], 2) ?></td>
                        <td><?= (int)$it['quantity'] ?></td>
                        <td>₱<?= number_format($it['subtotal'], 2) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
