<?php
session_start();
require_once "../db.php"; // your DB connection
include 'navbar.php';
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
  <h1 class="mb-4">📦 Orders</h1>

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
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

</body>
</html>
