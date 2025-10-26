<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize cart session if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$account_user_id = $_SESSION['user_id']; // Unique variable

// Fetch account details
$stmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $account_user_id);
$stmt->execute();
$result = $stmt->get_result();
$account_user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account - Esty Scents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
    body { padding-top: 80px; } /* space for fixed navbar */

    /* Card is completely static on account page */
    .card {
        transition: none !important;
        transform: none !important;
        box-shadow: 0 0 0 rgba(0,0,0,0) !important;
    }

    .card:hover {
        transform: none !important;
        box-shadow: 0 0 0 rgba(0,0,0,0) !important;
    }

    /* Only the Edit Account button is interactive */
    .btn-edit {
        background-color: #ff4081;
        color: #fff;
        border-radius: 25px;
        padding: 10px 25px;
        transition: transform 0.25s ease, background-color 0.25s ease;
    }

    /* Button moves only when hovered directly */
    .btn-edit:hover {
        background-color: #e91e63;
        transform: translateY(-3px) scale(1.05);
    }
</style>

</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-5 pt-5">
    <h2 class="mb-4">My Account</h2>

    <!-- Account Details -->
    <div class="card">
        <div class="card-header">Account Details</div>
        <div class="card-body">
            <p><strong>Username:</strong> <?= htmlspecialchars($account_user['username']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($account_user['email']); ?></p>
            <p><strong>Date Created:</strong> <?= date("F j, Y", strtotime($account_user['created_at'])); ?></p>
            <a href="edit_account.php" class="btn btn-edit">Edit Account</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
