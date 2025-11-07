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
    <div class="row">
      <!-- Cart Items Section -->
      <div class="col-md-8">
        <h3 class="mb-4 fw-bold">
          <i class="bi bi-bag-check me-2"></i>Your Cart (<?= count($_SESSION['cart']); ?> items)
        </h3>
        
        <div class="cart-items">
          <?php $grandTotal = 0; ?>
          <?php foreach ($_SESSION['cart'] as $item): ?>
            <?php $total = $item['price'] * $item['quantity']; ?>
            <?php $grandTotal += $total; ?>
            
            <div class="card mb-3 shadow-sm border-0 cart-item">
              <div class="card-body p-4">
                <div class="row align-items-center">
                  <!-- Product Image -->
                  <div class="col-md-2 col-4 mb-3 mb-md-0 text-center">
                    <img src="<?= htmlspecialchars($item['image'] ?? 'images/default.jpg'); ?>" 
                         alt="<?= htmlspecialchars($item['name']); ?>"
                         class="img-fluid rounded"
                         style="max-width: 100%; height: auto; object-fit: cover; max-height: 120px;">
                  </div>
                  
                  <!-- Product Info -->
                  <div class="col-md-3 col-8 mb-3 mb-md-0">
                    <h6 class="mb-2 fw-bold text-truncate"><?= htmlspecialchars($item['name']); ?></h6>
                    <p class="text-muted small mb-0">
                      <strong>₱<?= number_format($item['price'], 2); ?></strong> per item
                    </p>
                  </div>
                  
                  <!-- Quantity Controls -->
                  <div class="col-md-2 col-6 mb-3 mb-md-0">
                    <label class="small text-muted d-block mb-2">Quantity</label>
                    <div class="d-flex justify-content-center align-items-center gap-2">
                      <button type="button" onclick="updateCartQuantity(<?= $item['id']; ?>, 'decrease')"
                         class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center cart-update-btn"
                         style="width:32px; height:32px; padding:0;"
                         data-product-id="<?= $item['id']; ?>"
                         data-action="decrease">
                        <i class="bi bi-dash"></i>
                      </button>
                      <span class="fw-bold qty-display" data-product-id="<?= $item['id']; ?>" style="min-width: 30px; text-align: center;"><?= $item['quantity']; ?></span>
                      <button type="button" onclick="updateCartQuantity(<?= $item['id']; ?>, 'increase')"
                         class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center cart-update-btn"
                         style="width:32px; height:32px; padding:0;"
                         data-product-id="<?= $item['id']; ?>"
                         data-action="increase">
                        <i class="bi bi-plus"></i>
                      </button>
                    </div>
                  </div>
                  
                  <!-- Subtotal -->
                  <div class="col-md-2 col-6 mb-3 mb-md-0 text-end text-md-center">
                    <label class="small text-muted d-block mb-2">Subtotal</label>
                    <h5 class="mb-0 fw-bold text-success">₱<?= number_format($total, 2); ?></h5>
                  </div>
                  
                  <!-- Remove Button -->
                  <div class="col-md-3 col-12 text-md-end">
                    <button type="button" onclick="removeFromCart(<?= $item['id']; ?>)" 
                       class="btn btn-sm btn-outline-danger w-100 w-md-auto cart-remove-btn"
                       data-product-id="<?= $item['id']; ?>">
                      <i class="bi bi-trash me-1"></i> Remove
                    </button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        
        <!-- Clear Cart Button -->
        <div class="mt-3">
          <a href="cart.php?clear=1" class="btn btn-outline-danger btn-sm" 
             onclick="return confirm('Are you sure you want to clear your entire cart?');">
            <i class="bi bi-trash-fill me-1"></i> Clear Cart
          </a>
        </div>
      </div>
      
      <!-- Cart Summary Section -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
          <div class="card-body">
            <h5 class="card-title mb-4 fw-bold">
              <i class="bi bi-receipt me-2"></i>Order Summary
            </h5>
            
            <div class="mb-3 pb-3 border-bottom">
              <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <strong>₱<?= number_format($grandTotal, 2); ?></strong>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span>Shipping:</span>
                <span class="badge bg-info text-dark">Free</span>
              </div>
              <div class="d-flex justify-content-between">
                <span>Tax:</span>
                <strong>₱0.00</strong>
              </div>
            </div>
            
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Total:</h6>
                <h5 class="mb-0 text-success fw-bold">₱<?= number_format($grandTotal, 2); ?></h5>
              </div>
            </div>
            
            <!-- Checkout Button -->
            <?php if (isset($_SESSION['user_id'])): ?>
              <a href="checkout.php" class="btn btn-success w-100 btn-lg mb-2">
                <i class="bi bi-credit-card me-2"></i> Proceed to Checkout
              </a>
            <?php else: ?>
              <button type="button" class="btn btn-success w-100 btn-lg mb-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                <i class="bi bi-box-arrow-in-right me-2"></i> Login to Checkout
              </button>
            <?php endif; ?>
            
            <!-- Continue Shopping Button -->
            <a href="index.php" class="btn btn-outline-secondary w-100">
              <i class="bi bi-arrow-left me-1"></i> Continue Shopping
            </a>
          </div>
        </div>
      </div>
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

<script>
// Update navbar badge with current cart quantity
document.addEventListener('DOMContentLoaded', function() {
  updateNavbarBadgeFromCart();
});

function updateNavbarBadgeFromCart() {
  // Calculate cart COUNT (number of different items) from PHP session cart data
  var cartData = <?= json_encode($_SESSION['cart'] ?? []); ?>;
  var itemCount = Array.isArray(cartData) ? cartData.length : 0;

  // Update navbar badge
  var cartLink = document.getElementById('cartIconLink');
  if (cartLink) {
    var badge = cartLink.querySelector('.badge');
    if (!badge && itemCount > 0) {
      // Create badge if it doesn't exist
      badge = document.createElement('span');
      badge.className = 'badge bg-danger position-absolute top-0 start-100 translate-middle';
      badge.style.width = '18px';
      badge.style.height = '18px';
      badge.style.display = 'flex';
      badge.style.alignItems = 'center';
      badge.style.justifyContent = 'center';
      badge.style.fontSize = '0.6rem';
      badge.style.borderRadius = '50%';
      cartLink.appendChild(badge);
    }
    if (badge) {
      badge.textContent = itemCount > 0 ? itemCount : '';
      badge.style.display = itemCount > 0 ? 'flex' : 'none';
    }
  }
}

// AJAX handler for quantity updates (increase/decrease)
function updateCartQuantity(productId, action) {
  var btn = event.target.closest('button');
  if (btn) btn.disabled = true;
  
  fetch('cart.php?update=' + action + '&id=' + productId, {
    method: 'GET'
  })
  .then(res => res.text())
  .then(html => {
    // Reload page to refresh cart display and update navbar badge
    location.reload();
  })
  .catch(err => {
    console.error('Error updating cart:', err);
    if (btn) btn.disabled = false;
    alert('Error updating cart');
  });
}

// AJAX handler for removing items from cart
function removeFromCart(productId) {
  if (!confirm('Are you sure you want to remove this item?')) return;
  
  var btn = event.target.closest('button');
  if (btn) btn.disabled = true;
  
  fetch('cart.php?remove=' + productId, {
    method: 'GET'
  })
  .then(res => res.text())
  .then(html => {
    // Reload page to refresh cart display and update navbar badge
    location.reload();
  })
  .catch(err => {
    console.error('Error removing from cart:', err);
    if (btn) btn.disabled = false;
    alert('Error removing item');
  });
}
</script>

</body>
</html>
