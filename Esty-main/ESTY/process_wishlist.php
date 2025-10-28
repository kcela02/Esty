<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$product_id = intval($_POST['product_id'] ?? 0);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to use wishlist', 'login_required' => true]);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($action === 'add') {
    $stmt = $conn->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $product_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '❤️ Added to wishlist', 'action' => 'added']);
    } else {
        if (strpos($stmt->error, 'Duplicate') !== false) {
            echo json_encode(['success' => false, 'message' => 'Already in wishlist']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding to wishlist']);
        }
    }
    $stmt->close();
}
elseif ($action === 'remove') {
    $stmt = $conn->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist', 'action' => 'removed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error removing from wishlist']);
    }
    $stmt->close();
}
elseif ($action === 'check') {
    $stmt = $conn->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $stmt->store_result();
    
    echo json_encode(['in_wishlist' => $stmt->num_rows > 0]);
    $stmt->close();
}
?>
