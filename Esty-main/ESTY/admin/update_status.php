<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once "../db.php"; // DB connection
require_once __DIR__ . '/admin_helpers.php';

requireAdminLogin();
ensureAdminLogsTable($conn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id']);
    $status = $_POST['status'];

    // Only allow valid statuses
    $validStatuses = ['pending','processing','completed','cancelled'];
    if (in_array($status, $validStatuses)) {
        $previousStatus = null;
        $prevStmt = $conn->prepare("SELECT status FROM orders WHERE id = ?");
        if ($prevStmt) {
            $prevStmt->bind_param("i", $id);
            $prevStmt->execute();
            $prevStmt->bind_result($previousStatus);
            $prevStmt->fetch();
            $prevStmt->close();
        }

        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();

        $detail = $previousStatus
            ? sprintf('Order #%d: %s → %s', $id, $previousStatus, $status)
            : sprintf('Order #%d set to %s', $id, $status);
        logAdminActivity($conn, 'order_status_updated', $detail);
    }
}

// Go back to orders list
header("Location: orders.php");
exit;
?>
