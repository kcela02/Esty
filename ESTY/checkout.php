<?php
session_start();

// If cart is empty, redirect back
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// If user is not logged in, redirect to cart with message
if (!isset($_SESSION['user_id'])) {
    $_SESSION['checkout_message'] = "Please login or create an account to proceed with checkout.";
    header("Location: cart.php");
    exit;
}

// Calculate grand total
$grandTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $grandTotal += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - Esty Scents</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3 px-4 fixed-top">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <a class="navbar-brand d-flex align-items-center" href="index.php">
        <img src="images/logo.jpg" alt="Esty Scents Logo" class="logo me-2">
        <span class="fw-bold">Esty Scents</span>
      </a>
    </div>
  </nav>

  <div class="container my-5">
    <h1 class="mb-4 text-center">Checkout</h1>

    <!-- Order Summary -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h4 class="mb-3">Order Summary</h4>
        <ul class="list-group">
          <?php foreach ($_SESSION['cart'] as $item): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <?= htmlspecialchars($item['name']); ?> (x<?= $item['quantity']; ?>)
              <span>₱<?= $item['price'] * $item['quantity']; ?></span>
            </li>
          <?php endforeach; ?>
          <li class="list-group-item d-flex justify-content-between">
            <strong>Grand Total</strong>
            <strong>₱<?= $grandTotal; ?></strong>
          </li>
        </ul>
      </div>
    </div>

    <!-- Billing Details -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h4 class="mb-3">Billing Information</h4>
        <form action="process_order.php" method="POST">
          <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" id="name" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <!-- Email removed: use account email on file for order updates/receipts -->
          </div>
          <div class="mb-3">
            <label for="address" class="form-label">Shipping Address</label>
            <textarea id="address" name="address" class="form-control" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label for="payment" class="form-label">Payment Method</label>
            <select id="payment" name="payment" class="form-select" required>
              <option value="">Select Payment</option>
              <option value="cod">Cash on Delivery</option>
              <option value="gcash">GCash</option>
            </select>
          </div>
          <button type="submit" class="btn w-100">Place Order</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
