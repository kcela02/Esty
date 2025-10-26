<?php
session_start();
require 'db.php';
include 'navbar.php';

// Ensure products table has `stock` column for cart enforcement
if (!function_exists('ensureProductStockColumn')) {
  function ensureProductStockColumn(mysqli $conn): void {
    $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'stock'";
    if ($res = $conn->query($sql)) {
      if ($res->num_rows === 0) {
        @$conn->query("ALTER TABLE products ADD COLUMN stock INT NOT NULL DEFAULT 0 AFTER featured");
      }
      $res->close();
    }
  }
}
ensureProductStockColumn($conn);

// Remove item
if (isset($_GET['remove'])) {
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $_GET['remove']) {
            unset($_SESSION['cart'][$key]);
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header("Location: cart.php");
    exit;
}

// Clear cart
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit;
}

// Update quantity
if (isset($_GET['update']) && isset($_GET['id'])) {
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $_GET['id']) {
            if ($_GET['update'] === 'increase') {
        // Check stock before increasing
        $pid = intval($_GET['id']);
        $stock = null;
        $pstmt = $conn->prepare("SELECT COALESCE(stock,0) FROM products WHERE id=?");
        $pstmt->bind_param("i", $pid);
        $pstmt->execute();
        $pstmt->bind_result($stock);
        $pstmt->fetch();
        $pstmt->close();
        $current = (int)$_SESSION['cart'][$key]['quantity'];
        if ($stock === null || $current >= (int)$stock) {
          $_SESSION['flash'] = 'Reached available stock for ' . htmlspecialchars($item['name']);
        } else {
          $_SESSION['cart'][$key]['quantity'] = $current + 1;
        }
            } elseif ($_GET['update'] === 'decrease') {
                $_SESSION['cart'][$key]['quantity']--;
                if ($_SESSION['cart'][$key]['quantity'] <= 0) {
                    unset($_SESSION['cart'][$key]);
                }
            }
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex
    header("Location: cart.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Cart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container my-5">
  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-warning"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['cart'])): ?>
    <table class="table table-bordered text-center align-middle">
      <thead class="table-dark">
        <tr>
          <th>Product</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Total</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php $grandTotal = 0; ?>
        <?php foreach ($_SESSION['cart'] as $item): ?>
          <?php $total = $item['price'] * $item['quantity']; ?>
          <?php $grandTotal += $total; ?>
          <tr>
            <td><?= htmlspecialchars($item['name']); ?></td>
            <td>₱<?= number_format($item['price'], 2); ?></td>

            <!-- Quantity with + / - icon buttons -->
            <td>
              <div class="d-flex justify-content-center align-items-center gap-1">
                <!-- Decrease Button -->
                <a href="cart.php?update=decrease&id=<?= $item['id']; ?>"
                   class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center p-1"
                   style="width:28px; height:28px;">
                  <i class="bi bi-dash"></i>
                </a>

                <!-- Quantity Number -->
                <span class="mx-2"><?= $item['quantity']; ?></span>

                <!-- Increase Button -->
                <a href="cart.php?update=increase&id=<?= $item['id']; ?>"
                   class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center p-1"
                   style="width:28px; height:28px;">
                  <i class="bi bi-plus"></i>
                </a>
              </div>
            </td>

            <td>₱<?= number_format($total, 2); ?></td>
            <td><a href="cart.php?remove=<?= $item['id']; ?>" class="btn btn-danger btn-sm">Remove</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="text-end mb-3">
      <h4>Grand Total: ₱<?= number_format($grandTotal, 2); ?></h4>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-4">
      <a href="index.php" class="btn btn-primary">Continue Shopping</a>
      <a href="checkout.php" class="btn btn-success checkout-btn">Proceed to Checkout</a>
    </div>

  <?php else: ?>
    <div class="empty-cart text-center my-5 p-5">
      <i class="bi bi-cart-x display-1 text-muted"></i>
      <h3 class="fw-bold mt-3">Your cart is empty!</h3>
      <p class="text-muted">Looks like you haven’t added anything yet.</p>
      <a href="index.php" class="btn btn-pastel mt-3">Go Back to Shop</a>
    </div>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
