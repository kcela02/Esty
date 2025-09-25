<?php
session_start();
require_once "db.php"; // database connection file
include 'navbar.php';
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

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, email, address, payment_method, total) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssd", $name, $email, $address, $payment, $grandTotal);
    $stmt->execute();
    $orderId = $stmt->insert_id; // get last inserted order ID

    // Insert each product into order_items
    foreach ($_SESSION['cart'] as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmtItem->bind_param("isddi", $orderId, $item['name'], $item['price'], $item['quantity'], $subtotal);
        $stmtItem->execute();
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
