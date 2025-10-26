<?php
session_start();
require_once "db.php"; // database connection file
include 'navbar.php';
// Ensure products table has `stock` column used by checkout validation
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
// Redirect if cart is empty
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    header("Location: cart.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $address = trim($_POST['address']);
    $payment = trim($_POST['payment']);

    // Calculate total
    $grandTotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $grandTotal += $item['price'] * $item['quantity'];
    }

  // Validate stock and place order in a transaction
  $conn->begin_transaction();
  try {
    // Check stock for each item
    foreach ($_SESSION['cart'] as $item) {
      $pid = (int)$item['id'];
      $qty = (int)$item['quantity'];
      $stock = null;
      $check = $conn->prepare("SELECT COALESCE(stock,0) FROM products WHERE id = ? FOR UPDATE");
      $check->bind_param("i", $pid);
      $check->execute();
      $check->bind_result($stock);
      $check->fetch();
      $check->close();
      if ($stock === null || $stock < $qty) {
        $_SESSION['flash'] = 'Insufficient stock for ' . htmlspecialchars($item['name']) . '. Available: ' . (int)$stock . ', requested: ' . $qty . '.';
        $conn->rollback();
        header('Location: cart.php');
        exit;
      }
    }

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, email, address, payment_method, total) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssd", $name, $email, $address, $payment, $grandTotal);
    $stmt->execute();
    $orderId = $stmt->insert_id; // get last inserted order ID

    // Insert items and decrement stock
    foreach ($_SESSION['cart'] as $item) {
      $subtotal = $item['price'] * $item['quantity'];
      $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?)");
      $stmtItem->bind_param("isddi", $orderId, $item['name'], $item['price'], $item['quantity'], $subtotal);
      $stmtItem->execute();

      $pid = (int)$item['id'];
      $qty = (int)$item['quantity'];
      $upd = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
      $upd->bind_param("ii", $qty, $pid);
      $upd->execute();
    }

    $conn->commit();
  } catch (Throwable $e) {
    $conn->rollback();
    $_SESSION['flash'] = 'Unable to place order at this time. Please try again.';
    header('Location: cart.php');
    exit;
  }

    // Build email message
    $orderDetails = "Thank you for your order, $name!\n\n";
    $orderDetails .= "Order ID: #$orderId\n";
    $orderDetails .= "Payment Method: $payment\n";
    $orderDetails .= "Delivery Address: $address\n\n";
    $orderDetails .= "Items:\n";

    foreach ($_SESSION['cart'] as $item) {
        $orderDetails .= $item['name'] . " - " . $item['quantity'] . " x ₱" . number_format($item['price'], 2) . " = ₱" . number_format($item['price'] * $item['quantity'], 2) . "\n";
    }

    $orderDetails .= "\nTotal: ₱" . number_format($grandTotal, 2) . "\n\n";
    $orderDetails .= "We’ll contact you once your order is ready.\n";
    $orderDetails .= "Thank you for shopping with us!";

    // Email setup
    $subjectCustomer = "Your Order Confirmation (Order #$orderId)";
    $subjectAdmin    = "New Order Received (#$orderId)";
    $headers = "From: no-reply@yourshop.com";

    // Send email to customer
    @mail($email, $subjectCustomer, $orderDetails, $headers);

    // Send email copy to shop owner
    @mail("youremail@example.com", $subjectAdmin, $orderDetails, $headers);

  // Clear cart
  unset($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Confirmation</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

</head>
<body>

<div class="container my-5">
  <div class="empty-cart text-center p-5">
    <h2 class="mb-3">🎉 Order Confirmed!</h2>
    <p>Thank you <strong><?php echo htmlspecialchars($name); ?></strong> for your order.</p>
    <p>A copy of your order has been sent to <strong><?php echo htmlspecialchars($email); ?></strong>.</p>
    <p class="mb-4">We’ll notify you once your order is ready for delivery.</p>
    <a href="index.php" class="btn btn-pastel">🛍️ Continue Shopping</a>
  </div>
</div>

</body>
</html>
