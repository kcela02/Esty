<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// Only logged-in users can submit reviews
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$product_id = intval($_POST['product_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$title = trim($_POST['title'] ?? '');
$comment = trim($_POST['comment'] ?? '');
$user_id = intval($_SESSION['user_id']);

// Validation
if ($product_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating']);
    exit;
}

if (strlen($title) < 5) {
    echo json_encode(['success' => false, 'message' => 'Title must be at least 5 characters']);
    exit;
}

if (strlen($comment) < 20) {
    echo json_encode(['success' => false, 'message' => 'Review must be at least 20 characters']);
    exit;
}

// Check if user already reviewed this product
$stmt = $conn->prepare("SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'You already reviewed this product']);
    exit;
}
$stmt->close();

// Insert review
$stmt = $conn->prepare("INSERT INTO product_reviews (product_id, user_id, rating, title, comment) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiiis", $product_id, $user_id, $rating, $title, $comment);

if (!$stmt->execute()) {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
    exit;
}
$stmt->close();

// Calculate fresh rating metrics for the product
$agg_stmt = $conn->prepare("SELECT ROUND(AVG(rating), 2) AS avg_rating, COUNT(*) AS review_count, COALESCE(SUM(rating), 0) AS total_rating FROM product_reviews WHERE product_id = ?");
$agg_stmt->bind_param("i", $product_id);
$agg_stmt->execute();
$agg_stmt->bind_result($avg_rating_raw, $review_count_raw, $total_rating_raw);
$agg_stmt->fetch();
$agg_stmt->close();

$avg_rating = $avg_rating_raw !== null ? (float) $avg_rating_raw : 0.0;
$review_count = $review_count_raw !== null ? (int) $review_count_raw : 0;
$total_rating = $total_rating_raw !== null ? (int) $total_rating_raw : 0;

if ($review_count > 0) {
    $rating_stmt = $conn->prepare("INSERT INTO product_ratings (product_id, average_rating, review_count, total_rating_value) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE average_rating = VALUES(average_rating), review_count = VALUES(review_count), total_rating_value = VALUES(total_rating_value)");
    $rating_stmt->bind_param("idii", $product_id, $avg_rating, $review_count, $total_rating);
    $rating_stmt->execute();
    $rating_stmt->close();
}

// Update product popularity (increment by 1 for each new review)
$pop_stmt = $conn->prepare("UPDATE products SET popularity = popularity + 1 WHERE id = ?");
$pop_stmt->bind_param("i", $product_id);
$pop_stmt->execute();
$pop_stmt->close();

echo json_encode(['success' => true, 'message' => 'Review submitted successfully!']);
?>
