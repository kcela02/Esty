<?php
session_start();

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
  <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3 px-4 fixed-top">
  <div class="container-fluid d-flex justify-content-between align-items-center">

    <!-- Left: Logo -->
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="images/logo-placeholder.png" alt="Esty Scents Logo" class="logo me-2">
      <span class="fw-bold">Esty Scents</span>
    </a>

    <!-- Right: Search + Icons -->
    <div class="d-flex align-items-center gap-3">

      <!-- Search Bar -->
      <form class="d-flex" role="search">
        <input class="form-control search-bar me-2" type="search" placeholder="Search scents..." aria-label="Search">
        <button class="btn btn" type="submit">Search</button>
      </form>

      <!-- User Account -->
      <a href="#" class="text-dark fs-5">
        <i class="bi bi-person"></i>
      </a>

      <!-- Cart -->
      <a href="cart.php" class="text-dark fs-5 position-relative">
        <i class="bi bi-cart"></i>
        <?php if (!empty($_SESSION['cart'])): ?>
          <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
            <?= array_sum(array_column($_SESSION['cart'], 'quantity')); ?>
          </span>
        <?php endif; ?>
      </a>
    </div>
  </div>
</nav>

<div class="container my-5">
  <h1 class="mb-4">Shopping Cart</h1>
  <?php if (!empty($_SESSION['cart'])): ?>
    <table class="table table-bordered text-center">
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
            <td>₱<?= $item['price']; ?></td>
            <td><?= $item['quantity']; ?></td>
            <td>₱<?= $total; ?></td>
            <td><a href="cart.php?remove=<?= $item['id']; ?>" class="btn btn-danger btn-sm">Remove</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="text-end mb-3">
      <h4>Grand Total: ₱<?= $grandTotal; ?></h4>
    </div>
    <div class="d-flex justify-content-between">
      <a href="index.php" class="btn btn-primary">Continue Shopping</a>
      <a href="cart.php?clear=1" class="btn btn-warning">Clear Cart</a>
    </div>
  <?php else: ?>
    <p>Your cart is empty.</p>
    <a href="index.php" class="btn btn-primary">Go Back to Shop</a>
  <?php endif; ?>
</div>
</body>
</html>
