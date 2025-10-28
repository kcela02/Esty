<?php
session_start();
require 'db.php';
require_once __DIR__ . '/cart_helpers.php';
// Ensure session cart exists and sync from DB for logged in users
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (isset($_SESSION['user_id'])) {
    // sync_session_cart_from_db should be defined in cart_helpers.php and accept ($conn, $user_id)
    if (function_exists('sync_session_cart_from_db')) {
        sync_session_cart_from_db($conn, $_SESSION['user_id']);
    }
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
?>
<div id="cartFragment">
  <?php if (empty($cart)): ?>
    <div class="text-center py-4">
      <i class="bi bi-cart-x fs-1 text-muted"></i>
      <p class="mt-2 text-muted">Your cart is empty.</p>
      <a href="products.php" class="btn btn-sm btn-primary">Shop Now</a>
    </div>
  <?php else: ?>

    <div class="mb-2 d-flex align-items-center justify-content-between">
      <div>
        <input type="checkbox" id="cartSelectAll" />
        <label for="cartSelectAll" class="mb-0 ms-1">Select all</label>
      </div>
      <small class="text-muted">Selected subtotal: <span id="selectedSubtotal">₱0.00</span></small>
    </div>

    <div class="list-group">
      <?php foreach ($cart as $product):
        $subtotal = ($product['price'] ?? 0) * ($product['quantity'] ?? 0);
        $total += $subtotal;
      ?>
  <div class="list-group-item d-flex gap-3 align-items-start" data-item-id="<?= htmlspecialchars($product['id']) ?>" data-price="<?= number_format($subtotal, 2, '.', '') ?>" data-quantity="<?= htmlspecialchars($product['quantity']) ?>" style="overflow:hidden;">
          <div class="form-check">
            <input class="form-check-input cart-item-checkbox" type="checkbox" value="<?= htmlspecialchars($product['id']) ?>" id="cartItem<?= htmlspecialchars($product['id']) ?>">
          </div>
          <img src="<?= htmlspecialchars($product['image'] ?? 'images/no-image.png') ?>" alt="<?= htmlspecialchars($product['name'] ?? '') ?>" style="width:56px; height:56px; object-fit:cover; flex:0 0 56px;" class="rounded">
          <div class="flex-grow-1" style="min-width:0;">
            <div class="d-flex justify-content-between">
              <div>
                <div class="fw-bold"><?= htmlspecialchars($product['name']) ?></div>
                <div class="text-muted small">SKU: <?= htmlspecialchars($product['sku'] ?? '-') ?></div>
              </div>
              <div class="text-end">
                <div class="text-muted small">₱<?= number_format($product['price'] ?? 0,2) ?> each</div>
              </div>
            </div>

            <div class="mt-2 d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <button class="btn-decrease" data-id="<?= htmlspecialchars($product['id']) ?>" style="padding:.12rem .4rem; line-height:1; background:transparent; border:none; color:#d9534f; font-weight:700;">-</button>
                <span class="badge bg-light text-dark item-qty-badge" style="min-width:36px; text-align:center;"><?= htmlspecialchars($product['quantity']) ?></span>
                <button class="btn-increase" data-id="<?= htmlspecialchars($product['id']) ?>" style="padding:.12rem .4rem; line-height:1; background:transparent; border:none; color:#28a745; font-weight:700;">+</button>
              </div>
              <div>
                <button class="btn btn-sm btn-link text-danger btn-remove" data-id="<?= htmlspecialchars($product['id']) ?>">Remove</button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    </div>

    <div class="mt-3 d-flex justify-content-start align-items-center">
      <div>
        <button id="checkoutSelectedBtn" class="btn btn-primary btn-sm ms-2" disabled>Checkout Selected</button>
      </div>
    </div>
  <?php endif; ?>
</div>
