<?php
session_start();
require 'db.php';

// Only logged-in users can submit reviews
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Validation
    if ($product_id <= 0 || $rating < 1 || $rating > 5) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid rating']);
        exit;
    }

    if (strlen($title) < 5) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Title must be at least 5 characters']);
        exit;
    }

    if (strlen($comment) < 20) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Review must be at least 20 characters']);
        exit;
    }

    // Check if user already reviewed this product
    $stmt = $conn->prepare("SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $product_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'You already reviewed this product']);
        exit;
    }
    $stmt->close();

    // Insert review
    $stmt = $conn->prepare("INSERT INTO product_reviews (product_id, user_id, rating, title, comment) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiis", $product_id, $user_id, $rating, $title, $comment);

    if ($stmt->execute()) {
        // Update product ratings
        $rating_stmt = $conn->prepare("
            INSERT INTO product_ratings (product_id, average_rating, review_count, total_rating_value)
            SELECT 
                ?, 
                ROUND(AVG(rating), 2),
                COUNT(*),
                SUM(rating)
            FROM product_reviews
            WHERE product_id = ?
            ON DUPLICATE KEY UPDATE
                average_rating = ROUND(AVG(rating), 2),
                review_count = COUNT(*),
                total_rating_value = SUM(rating)
        ");
        $rating_stmt->bind_param("ii", $product_id, $product_id);
        $rating_stmt->execute();
        $rating_stmt->close();

        // Update product popularity (increment by review count)
        $pop_stmt = $conn->prepare("UPDATE products SET popularity = popularity + 1 WHERE id = ?");
        $pop_stmt->bind_param("i", $product_id);
        $pop_stmt->execute();
        $pop_stmt->close();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Review submitted successfully!']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
    }
    $stmt->close();
}
?>
