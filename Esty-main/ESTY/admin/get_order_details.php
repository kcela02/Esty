<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once "../db.php";

requireAdminLogin();

$order_id = intval($_GET['id'] ?? 0);
if ($order_id <= 0) {
    echo "<div class='alert alert-danger'>Invalid order ID.</div>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "<div class='alert alert-warning'>Order not found.</div>";
    exit;
}

$stmt = $conn->prepare("SELECT product_name, quantity, price FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();
$items = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<style>
  .order-detail-header {
    background-color: #fff0f5;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
  }
  .order-detail-header strong { color: #4B3F2F; }
  .order-items-list { border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
  .order-items-list .list-group-item { border: none; background: #fffafc; }
  .order-items-list .list-group-item:nth-child(even) { background-color: #fff0f5; }
  .order-total { font-weight: 700; color: #4B3F2F; text-align: right; font-size: 1.25rem; margin-top: 10px; }
  .status-pending { background: #fff1cc; color: #7a6000; }
  .status-processing { background: #ffe0b2; color: #8a4d00; animation: processingPulse 1.8s infinite ease-in-out; }
  .status-completed { background: #d4edda; color: #0a682e; }
  .status-cancelled { background: #f8d7da; color: #9b1b30; }

  @keyframes processingPulse {
    0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
  }


</style>

<div class="text-start">
  <div class="order-detail-header">
    <h5 class="fw-bold mb-2">Order #<?= $order['id']; ?></h5>
    <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']); ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($order['email']); ?></p>
    <p><strong>Status:</strong>
      <span class="status-badge status-<?= strtolower($order['status']); ?>">
        <?= ucfirst($order['status']); ?>
      </span>
    </p>
    <p><strong>Placed:</strong> <?= date("F j, Y, g:i a", strtotime($order['created_at'])); ?></p>
  </div>

  <h6 class="fw-bold mb-2" style="color:#4B3F2F;">Order Items:</h6>
  <?php if ($items): ?>
    <ul class="list-group order-items-list mb-3">
      <?php foreach ($items as $it): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div><?= htmlspecialchars($it['product_name']); ?>
            <small class="text-muted">× <?= $it['quantity']; ?></small>
          </div>
          <div><strong>₱<?= number_format($it['price'], 2); ?></strong></div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <div class="text-muted fst-italic">No items found for this order.</div>
  <?php endif; ?>

  <div class="order-total">Total: ₱<?= number_format($order['total'], 2); ?></div>
</div>
