<?php
require_once __DIR__ . '/../session_bootstrap.php';
requireAdminLogin();
require_once __DIR__ . '/../db.php';

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    echo 'Invalid order id';
    exit;
}
$order_id = intval($_GET['order_id']);
require_once __DIR__ . '/../cart_helpers.php';
$backup = get_cart_backup($conn, $order_id);
if ($backup === null) {
    echo 'No backup found for order ' . $order_id;
    exit;
}
?><div class="main-content"><div class="card p-3"><h3>Backup for Order #<?php echo $order_id; ?></h3>
<pre><?php echo htmlspecialchars(json_encode($backup, JSON_PRETTY_PRINT)); ?></pre>
<a class="btn btn-secondary" href="cart_backups.php">Back</a>
</div></div>
<?php include __DIR__ . '/sidebar.php'; ?>
