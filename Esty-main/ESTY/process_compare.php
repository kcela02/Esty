<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$product_id = intval($_POST['product_id'] ?? 0);

// Use user_id if logged in, otherwise use session_id
$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();

if ($action === 'add') {
    // Check if user already has 4 products in compare
    if ($user_id) {
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM compare_products WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM compare_products WHERE session_id = ?");
        $stmt->bind_param("s", $session_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row['cnt'] >= 4) {
        echo json_encode(['success' => false, 'message' => 'Maximum 4 products can be compared']);
        exit;
    }
    
    // Add product to compare
    $stmt = $conn->prepare("INSERT INTO compare_products (user_id, session_id, product_id) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $user_id, $session_id, $product_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '📊 Added to compare']);
    } else {
        if (strpos($stmt->error, 'Duplicate') !== false) {
            echo json_encode(['success' => false, 'message' => 'Already in compare list']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding to compare']);
        }
    }
    $stmt->close();
}
elseif ($action === 'remove') {
    if ($user_id) {
        $stmt = $conn->prepare("DELETE FROM compare_products WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM compare_products WHERE session_id = ? AND product_id = ?");
        $stmt->bind_param("si", $session_id, $product_id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Removed from compare']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error removing from compare']);
    }
    $stmt->close();
}
elseif ($action === 'get_list') {
    // Get all products in compare list
    if ($user_id) {
        $stmt = $conn->prepare("
            SELECT p.id, p.name, p.price, p.image 
            FROM compare_products cp
            JOIN products p ON cp.product_id = p.id
            WHERE cp.user_id = ?
            ORDER BY cp.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
    } else {
        $stmt = $conn->prepare("
            SELECT p.id, p.name, p.price, p.image 
            FROM compare_products cp
            JOIN products p ON cp.product_id = p.id
            WHERE cp.session_id = ?
            ORDER BY cp.created_at DESC
        ");
        $stmt->bind_param("s", $session_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    echo json_encode(['success' => true, 'products' => $products, 'count' => count($products)]);
    $stmt->close();
}
elseif ($action === 'check') {
    if ($user_id) {
        $stmt = $conn->prepare("SELECT id FROM compare_products WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
    } else {
        $stmt = $conn->prepare("SELECT id FROM compare_products WHERE session_id = ? AND product_id = ?");
        $stmt->bind_param("si", $session_id, $product_id);
    }
    
    $stmt->execute();
    $stmt->store_result();
    
    echo json_encode(['in_compare' => $stmt->num_rows > 0]);
    $stmt->close();
}
?>
