<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/../db.php';
requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orders.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid order id.'];
    header('Location: orders.php');
    exit;
}

try {
    $conn->begin_transaction();

    // delete order items
    $d1 = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
    $d1->bind_param('i', $id);
    $d1->execute();
    $d1->close();

    // delete cart backup if exists
    $d2 = $conn->prepare("DELETE FROM order_cart_backups WHERE order_id = ?");
    $d2->bind_param('i', $id);
    $d2->execute();
    $d2->close();

    // delete pending_orders with same id if present
    $d3 = $conn->prepare("DELETE FROM pending_orders WHERE id = ?");
    $d3->bind_param('i', $id);
    $d3->execute();
    $d3->close();

    // delete actual order (if exists)
    $d4 = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $d4->bind_param('i', $id);
    $d4->execute();
    $d4->close();

    $conn->commit();
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Order #' . $id . ' deleted.'];
} catch (Throwable $e) {
    $conn->rollback();
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Unable to delete order: ' . $e->getMessage()];
}

header('Location: orders.php');
exit;
