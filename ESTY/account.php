<?php
session_start();
require 'db.php';
require_once __DIR__ . '/user_activity_helpers.php';

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

// Fetch recent activity logs for display
$activity_logs = fetchUserActivityLogs($conn, $account_user_id, 20);
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

    .activity-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 12px 0;
    }

    .activity-item + .activity-item {
        border-top: 1px solid #f0f0f0;
    }

    .activity-icon {
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: rgba(255, 64, 129, 0.08);
        color: #ff4081;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .activity-title {
        font-weight: 600;
        color: #333;
    }

    .activity-meta {
        color: #8f8f8f;
        font-size: 0.85rem;
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

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Recent Activity</span>
            <small class="text-muted">Latest 20 entries</small>
        </div>
        <div class="card-body">
            <?php if (empty($activity_logs)): ?>
                <p class="text-muted mb-0">No activity recorded yet.</p>
            <?php else: ?>
                <?php foreach ($activity_logs as $log): ?>
                    <?php
                        $icon = iconForUserActivity($log['action']);
                        $label = describeUserActivity($log['action']);
                        $timestamp = strtotime($log['created_at']);
                        $formattedTime = date('M j, Y \a\t g:i A', $timestamp);
                    ?>
                    <div class="activity-item">
                        <div class="activity-icon"><i class="bi <?= htmlspecialchars($icon); ?>"></i></div>
                        <div class="activity-content">
                            <div class="activity-title"><?= htmlspecialchars($label); ?></div>
                            <?php if (!empty($log['details'])): ?>
                                <div class="text-muted small"><?= htmlspecialchars($log['details']); ?></div>
                            <?php endif; ?>
                            <div class="activity-meta">
                                <?= htmlspecialchars($formattedTime); ?>
                                <?php if (!empty($log['ip_address'])): ?>
                                    <span> • IP <?= htmlspecialchars($log['ip_address']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
