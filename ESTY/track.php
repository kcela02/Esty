<?php
session_start();
require 'db.php';

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);
$user_email = null;

if ($logged_in) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();
    $user_email = $user['email'] ?? null;
}

// Handle tracking form submission
$tracking_result = null;
$tracking_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = trim($_POST['order_id'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($order_id) || empty($email)) {
        $tracking_error = "Please enter both Order ID and Email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $tracking_error = "Please enter a valid email address.";
    } else {
        // Check if order exists and matches the email
        $stmt = $conn->prepare("SELECT id, customer_name, email, total, created_at, status FROM orders WHERE id = ? AND email = ?");
        $stmt->bind_param('is', $order_id, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $tracking_result = $result->fetch_assoc();

            // Get order items
            $stmt_items = $conn->prepare("SELECT product_name, price, quantity, subtotal FROM order_items WHERE order_id = ?");
            $stmt_items->bind_param('i', $order_id);
            $stmt_items->execute();
            $tracking_result['items'] = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt_items->close();
        } else {
            $tracking_error = "Order not found. Please check your Order ID and Email.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Track Order - Esty Scents</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="style.css">
  <style>
    body { padding-top: 80px; }
    .tracking-form { max-width: 500px; margin: 0 auto; }
    .status-badge {
      font-size: 0.875rem;
      padding: 0.375rem 0.75rem;
    }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-5 pt-3">
  <div class="row">
    <div class="col-md-8 mx-auto">
      <h2 class="mb-4 text-center">Track Your Order</h2>

      <!-- Tracking Form -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <form method="post" class="tracking-form">
            <div class="mb-3">
              <label for="order_id" class="form-label">Order ID <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="order_id" name="order_id"
                     value="<?= htmlspecialchars($_POST['order_id'] ?? '') ?>" required>
              <div class="form-text">Enter the order number from your order confirmation</div>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="email" name="email"
                     value="<?= htmlspecialchars($_POST['email'] ?? $user_email ?? '') ?>" required>
              <div class="form-text">Enter the email address used for this order</div>
            </div>

            <?php if ($tracking_error): ?>
              <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($tracking_error) ?>
              </div>
            <?php endif; ?>

            <div class="d-grid">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Track Order
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Tracking Results -->
      <?php if ($tracking_result): ?>
        <div class="card shadow-sm">
          <div class="card-header bg-success text-white">
            <h5 class="mb-0">
              <i class="bi bi-check-circle"></i> Order Found
            </h5>
          </div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <strong>Order #<?= $tracking_result['id'] ?></strong><br>
                <small class="text-muted">Placed: <?= date('F j, Y, g:ia', strtotime($tracking_result['created_at'])) ?></small>
              </div>
              <div class="col-md-6 text-end">
                <div class="mb-2">
                  Status:
                  <?php
                  $status_class = 'bg-secondary';
                  switch (strtolower($tracking_result['status'])) {
                    case 'pending': $status_class = 'bg-warning text-dark'; break;
                    case 'processing': $status_class = 'bg-info'; break;
                    case 'shipped': $status_class = 'bg-primary'; break;
                    case 'delivered': $status_class = 'bg-success'; break;
                    case 'cancelled': $status_class = 'bg-danger'; break;
                  }
                  ?>
                  <span class="badge <?= $status_class ?> status-badge">
                    <?= htmlspecialchars($tracking_result['status']) ?>
                  </span>
                </div>
                <div><strong>Total: ₱<?= number_format($tracking_result['total'], 2) ?></strong></div>
              </div>
            </div>

            <hr>

            <h6>Order Items</h6>
            <?php if (empty($tracking_result['items'])): ?>
              <p class="text-muted">No items found for this order.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th class="text-center">Qty</th>
                      <th class="text-end">Price</th>
                      <th class="text-end">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($tracking_result['items'] as $item): ?>
                      <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td class="text-center"><?= (int)$item['quantity'] ?></td>
                        <td class="text-end">₱<?= number_format($item['price'], 2) ?></td>
                        <td class="text-end">₱<?= number_format($item['subtotal'], 2) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>

            <hr>

            <div class="row">
              <div class="col-md-6">
                <h6>Customer Information</h6>
                <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($tracking_result['customer_name']) ?></p>
                <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($tracking_result['email']) ?></p>
              </div>
              <div class="col-md-6">
                <h6>Order Status Timeline</h6>
                <div class="timeline">
                  <?php
                  $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered'];
                  $current_status = strtolower($tracking_result['status']);
                  foreach ($statuses as $status) {
                    $status_lower = strtolower($status);
                    $is_completed = $status_lower === $current_status ||
                                  array_search($status_lower, array_map('strtolower', $statuses)) <
                                  array_search($current_status, array_map('strtolower', $statuses));
                    ?>
                    <div class="timeline-item mb-2">
                      <div class="d-flex align-items-center">
                        <div class="me-2">
                          <i class="bi bi-<?= $is_completed ? 'check-circle-fill text-success' : 'circle text-muted' ?>"></i>
                        </div>
                        <div>
                          <small class="<?= $is_completed ? 'text-success fw-bold' : 'text-muted' ?>">
                            <?= $status ?>
                          </small>
                        </div>
                      </div>
                    </div>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Help Section -->
      <div class="card shadow-sm mt-4">
        <div class="card-body">
          <h6><i class="bi bi-question-circle"></i> Need Help?</h6>
          <p class="mb-2">If you're having trouble tracking your order:</p>
          <ul class="mb-0 small">
            <li>Check your order confirmation email for the Order ID</li>
            <li>Make sure you're using the same email address used during checkout</li>
            <li>Order IDs are numeric (e.g., 12345)</li>
            <?php if (!$logged_in): ?>
            <li><a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a> to view your orders directly</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php include 'login_register_modals.php'; ?>