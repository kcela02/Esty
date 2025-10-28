<?php
session_start();
require 'db.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// get user email
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

$email = $user['email'] ?? null;

$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $reason = trim($_POST['reason'] ?? '');

    if ($order_id <= 0 || $reason === '') {
        $message = ['type' => 'danger','text' => 'Please provide order ID and a reason.'];
    } else {
        // create returns table if not exists
        $createSql = "CREATE TABLE IF NOT EXISTS returns (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            email VARCHAR(100) DEFAULT NULL,
            reason TEXT,
            status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $conn->query($createSql);

        $ins = $conn->prepare("INSERT INTO returns (order_id, email, reason, status) VALUES (?, ?, ?, 'pending')");
        $ins->bind_param('iss', $order_id, $email, $reason);
        if ($ins->execute()) {
            $message = ['type' => 'success','text' => 'Return request submitted. We will contact you soon.'];
        } else {
            $message = ['type' => 'danger','text' => 'Failed to submit return request.'];
        }
        $ins->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Return Order - Esty Scents</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="style.css">
  <style>body { padding-top: 80px; }</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-5 pt-3">
  <h2 class="mb-4">Return Order</h2>

  <?php if ($message): ?>
    <div class="alert alert-<?= $message['type'] ?>"><?= htmlspecialchars($message['text']) ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label for="order_id" class="form-label">Order ID</label>
      <input type="number" class="form-control" id="order_id" name="order_id" required>
    </div>
    <div class="mb-3">
      <label for="reason" class="form-label">Reason for return</label>
      <textarea class="form-control" id="reason" name="reason" rows="4" required></textarea>
    </div>
    <button class="btn btn-primary" type="submit">Submit Return Request</button>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
