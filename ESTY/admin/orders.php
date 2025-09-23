<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require_once "../db.php"; // your DB connection

// Fetch all orders
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">📦 Orders</h1>
    <a href="logout.php" class="btn btn-danger">Logout</a>
  </div>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Customer</th>
        <th>Email</th>
        <th>Payment</th>
        <th>Total</th>
        <th>Date</th>
        <th>Action</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td>#<?= $row['id']; ?></td>
          <td><?= htmlspecialchars($row['customer_name']); ?></td>
          <td><?= htmlspecialchars($row['email']); ?></td>
          <td><?= $row['payment_method']; ?></td>
          <td>₱<?= number_format($row['total'], 2); ?></td>
          <td><?= $row['created_at']; ?></td>
          <td>
            <a href="view_order.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">View</a>
          </td>
          <td>
  <form action="update_status.php" method="POST" class="d-inline">
    <input type="hidden" name="id" value="<?= $row['id']; ?>">
    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
      <option value="pending" <?= $row['status']=='pending'?'selected':''; ?>>Pending</option>
      <option value="processing" <?= $row['status']=='processing'?'selected':''; ?>>Processing</option>
      <option value="completed" <?= $row['status']=='completed'?'selected':''; ?>>Completed</option>
      <option value="cancelled" <?= $row['status']=='cancelled'?'selected':''; ?>>Cancelled</option>
    </select>
  </form>
</td>

        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

</body>
</html>
