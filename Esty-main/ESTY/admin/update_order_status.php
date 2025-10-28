<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once "../db.php";
require_once __DIR__ . '/admin_helpers.php';

requireAdminLogin();

if (isset($_POST['order_id'], $_POST['status'])) {
    $id = (int)$_POST['order_id'];
    $status = $_POST['status'];

    $allowed = ['pending', 'processing', 'completed', 'cancelled'];
    if (in_array($status, $allowed, true) && $id > 0) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $status, $id);
            $stmt->execute();
            $stmt->close();
            // Log activity
            if (!empty($_SESSION['admin'])) {
                logAdminActivity($conn, $_SESSION['admin'], 'Updated order status', 'Order ID: ' . $id . ' -> ' . $status);
            }
        }
    }

    $redirect = "orders.php";
    $queryParams = [];

    if (!empty($_POST['current_status'])) {
        $queryParams[] = "status=" . urlencode($_POST['current_status']);
    }
    if (!empty($_POST['search'])) {
        $queryParams[] = "search=" . urlencode($_POST['search']);
    }

    if ($queryParams) {
        $redirect .= "?" . implode("&", $queryParams);
    }

    header("Location: $redirect");
    exit;
}
?>
