<?php
session_start();
require 'db.php';

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

// For logged-in users, synchronize cart from DB into session so cart display works consistently
require_once __DIR__ . '/cart_helpers.php';
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}
if (isset($_SESSION['user_id'])) {
  sync_session_cart_from_db($conn, $_SESSION['user_id']);
}

// Helper: sync cart from DB into session (used after DB modifications)
// function `sync_session_cart_from_db` is defined in `cart_helpers.php` and included above.

// Remove item
if (isset($_GET['remove'])) {
  $removeId = intval($_GET['remove']);
  // If user is logged in, remove from DB as well
  if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $dstmt = $conn->prepare("DELETE FROM carts WHERE user_id = ? AND product_id = ?");
    $dstmt->bind_param('ii', $uid, $removeId);
    $dstmt->execute();
    $dstmt->close();

    // Refresh session cart from DB
    sync_session_cart_from_db($conn, $uid);
  } else {
    foreach ($_SESSION['cart'] as $key => $item) {
      if ($item['id'] == $removeId) {
        unset($_SESSION['cart'][$key]);
      }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']);
  }
  header("Location: cart.php");
  exit;
}

// Clear cart
if (isset($_GET['clear'])) {
  if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $dstmt = $conn->prepare("DELETE FROM carts WHERE user_id = ?");
    $dstmt->bind_param('i', $uid);
    $dstmt->execute();
    $dstmt->close();
    $_SESSION['cart'] = [];
  } else {
    unset($_SESSION['cart']);
  }
  header("Location: cart.php");
  exit;
}

// Update quantity
if (isset($_GET['update']) && isset($_GET['id'])) {
  $pid = intval($_GET['id']);
  $action = $_GET['update'];
  // Check stock
  $stock = null;
  $pstmt = $conn->prepare("SELECT COALESCE(stock,0) FROM products WHERE id=?");
  $pstmt->bind_param("i", $pid);
  $pstmt->execute();
  $pstmt->bind_result($stock);
  $pstmt->fetch();
  $pstmt->close();

  if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    // get current quantity from DB
    $qstmt = $conn->prepare("SELECT quantity FROM carts WHERE user_id = ? AND product_id = ?");
    $qstmt->bind_param('ii', $uid, $pid);
    $qstmt->execute();
    $qstmt->bind_result($current);
    $has = $qstmt->fetch();
    $qstmt->close();
    $current = $has ? (int)$current : 0;

    if ($action === 'increase') {
      if ($stock === null || $current >= (int)$stock) {
        $_SESSION['flash'] = 'Reached available stock for this product';
      } else {
        if ($has) {
          $ustmt = $conn->prepare("UPDATE carts SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
          $ustmt->bind_param('ii', $uid, $pid);
          $ustmt->execute();
          $ustmt->close();
        } else {
          $inst = $conn->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, 1)");
          $inst->bind_param('ii', $uid, $pid);
          $inst->execute();
          $inst->close();
        }
      }
    } elseif ($action === 'decrease') {
      if ($has && $current > 1) {
        $dstmt = $conn->prepare("UPDATE carts SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ?");
        $dstmt->bind_param('ii', $uid, $pid);
        $dstmt->execute();
        $dstmt->close();
      } elseif ($has && $current <= 1) {
        $dstmt = $conn->prepare("DELETE FROM carts WHERE user_id = ? AND product_id = ?");
        $dstmt->bind_param('ii', $uid, $pid);
        $dstmt->execute();
        $dstmt->close();
      }
    }

    // Refresh session cart
    sync_session_cart_from_db($conn, $uid);
  } else {
    // Guest cart - update session only
    foreach ($_SESSION['cart'] as $key => $item) {
      if ($item['id'] == $pid) {
        if ($action === 'increase') {
          $current = (int)$_SESSION['cart'][$key]['quantity'];
          if ($stock === null || $current >= (int)$stock) {
            $_SESSION['flash'] = 'Reached available stock for ' . htmlspecialchars($item['name']);
          } else {
            $_SESSION['cart'][$key]['quantity'] = $current + 1;
          }
        } elseif ($action === 'decrease') {
          $_SESSION['cart'][$key]['quantity']--;
          if ($_SESSION['cart'][$key]['quantity'] <= 0) {
            unset($_SESSION['cart'][$key]);
          }
        }
      }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex
  }

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

<?php include 'navbar.php'; ?>

<div class="container my-5">
  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-warning"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
  <?php endif; ?>
  
  <?php if (!empty($_SESSION['checkout_message'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <i class="bi bi-info-circle me-2"></i>
      <?= htmlspecialchars($_SESSION['checkout_message']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['checkout_message']); ?>
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
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="checkout.php" class="btn btn-success checkout-btn">Proceed to Checkout</a>
      <?php else: ?>
        <button type="button" class="btn btn-success checkout-btn" data-bs-toggle="modal" data-bs-target="#loginModal">
          <i class="bi bi-box-arrow-in-right me-2"></i> Login to Checkout
        </button>
      <?php endif; ?>
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

<!-- Include Login/Register Modals for Guests -->
<?php if (!isset($_SESSION['user_id'])): ?>
  <?php include 'login_register_modals.php'; ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
