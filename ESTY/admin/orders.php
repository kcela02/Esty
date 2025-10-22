<?php
session_start();
require_once "../db.php";
include 'sidebar.php';


// require admin login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Handle status update (quick page POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';

    // validate status
    $allowed = ['pending', 'processing', 'completed', 'cancelled'];
    if ($order_id > 0 && in_array($new_status, $allowed, true)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        $stmt->close();

        // flash message
        $_SESSION['admin_msg'] = "Order #{$order_id} status updated to {$new_status}.";
    } else {
        $_SESSION['admin_msg'] = "Invalid status or order.";
    }

    // Redirect to avoid repost on reload
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch orders (most recent first)
$orders = [];
$result = $conn->query("SELECT id, customer_name, email, total, created_at, status FROM orders ORDER BY created_at DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

// grab any flash message
$flash = $_SESSION['admin_msg'] ?? '';
unset($_SESSION['admin_msg']);

// helper: map status -> badge class & label color
function status_badge_class($status) {
    switch ($status) {
        case 'pending': return 'bg-pink text-white';   // custom class inlined below
        case 'processing': return 'bg-warning text-dark';
        case 'completed': return 'bg-success text-white';
        case 'cancelled': return 'bg-secondary text-white';
        default: return 'bg-light text-dark';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Admin - Orders | Esty Scents</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="admin-style.css">

</head>
<body>



  <!-- Main content -->
  <div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="mb-0">Orders</h2>
      <?php if ($flash): ?>
        <div class="alert alert-success mb-0"><?= htmlspecialchars($flash); ?></div>
      <?php endif; ?>
    </div>

    <?php if (empty($orders)): ?>
      <div class="alert alert-light">No orders found.</div>
    <?php else: ?>
      <div class="cards-row">
        <?php foreach ($orders as $o): ?>
          <?php
            $oid = (int)$o['id'];
            $status = $o['status'];
            $badgeClass = status_badge_class($status);
          ?>
          <div class="order-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <h5 class="mb-1">Order #<?= $oid; ?></h5>
                <div class="muted">Placed: <?= date("F j, Y, g:i a", strtotime($o['created_at'])); ?></div>
              </div>
              <div class="text-end">
                <div class="badge <?= $badgeClass; ?> px-3 py-2"><?= ucfirst($status); ?></div>
                <div class="muted mt-1">Total: <strong>₱<?= number_format($o['total'], 2); ?></strong></div>
              </div>
            </div>

            <div class="order-meta mb-3">
              <div class="meta"><strong>Customer:</strong> <?= htmlspecialchars($o['customer_name']); ?></div>
              <div class="meta"><strong>Email:</strong> <?= htmlspecialchars($o['email']); ?></div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex gap-2">
                <a href="view_order.php?id=<?= $oid; ?>" class="btn btn-outline-primary btn-view">
                  <i class="bi bi-eye me-1"></i> View Details
                </a>

                <!-- Inline status update form -->
                <form method="post" class="form-inline" >
                  <input type="hidden" name="order_id" value="<?= $oid; ?>">
                  <div>
                    <select name="status" class="form-select form-select-sm" aria-label="Update status">
                      <?php
                        $states = ['pending','processing','completed','cancelled'];
                        foreach ($states as $s):
                      ?>
                        <option value="<?= $s; ?>" <?= $s === $status ? 'selected' : ''; ?>><?= ucfirst($s); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                </form>
              </div>

              <div class="muted">Order ID: <?= $oid; ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>
