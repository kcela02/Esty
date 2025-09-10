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
</head>
<body>
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