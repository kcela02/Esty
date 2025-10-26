<?php
session_start();
require_once "../db.php"; // DB connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id']);
    $status = $_POST['status'];

    // Only allow valid statuses
    $validStatuses = ['pending','processing','completed','cancelled'];
    if (in_array($status, $validStatuses)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    }
}

// Go back to orders list
header("Location: orders.php");
exit;
?>
