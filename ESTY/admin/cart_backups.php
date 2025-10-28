<?php
require_once __DIR__ . '/../session_bootstrap.php';
requireAdminLogin();
require_once __DIR__ . '/../db.php';

$title = 'Cart Backups';
include __DIR__ . '/sidebar.php';

// handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM order_cart_backups WHERE order_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: cart_backups.php');
    exit;
}

// fetch backups
ensure_cart_backup_table($conn);
$res = $conn->query("SELECT order_id, LEFT(cart_json, 200) AS snippet, created_at FROM order_cart_backups ORDER BY created_at DESC");
?>
<div class="main-content">
  <div class="card p-3">
    <h3>Cart Backups</h3>
    <p>View or remove cart backups created when orders were placed and awaiting payment confirmation.</p>
    <table class="table table-striped">
      <thead><tr><th>Order ID</th><th>Snippet</th><th>Created At</th><th>Actions</th></tr></thead>
      <tbody>
        <?php while ($row = $res->fetch_assoc()): ?>
          <tr>
            <td><?php echo intval($row['order_id']); ?></td>
            <td><pre style="max-width:400px; white-space:pre-wrap;"><?php echo htmlspecialchars($row['snippet']); ?></pre></td>
            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            <td>
              <a class="btn btn-sm btn-outline-primary" href="view_backup.php?order_id=<?php echo intval($row['order_id']); ?>">View</a>
              <a class="btn btn-sm btn-danger" href="cart_backups.php?delete=<?php echo intval($row['order_id']); ?>" onclick="return confirm('Delete backup for order <?php echo intval($row['order_id']); ?>?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <h5>Logs</h5>
    <ul>
      <li><a href="/Esty/logs/paymongo.log" target="_blank">paymongo.log</a></li>
      <li><a href="/Esty/logs/paymongo_webhook.log" target="_blank">paymongo_webhook.log</a></li>
    </ul>
  </div>
</div>

<?php include __DIR__ . '/sidebar.php'; ?>
