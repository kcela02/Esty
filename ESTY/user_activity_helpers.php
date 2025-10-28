<?php

declare(strict_types=1);

/**
 * Helper functions to manage user activity logs.
 */

/**
 * Ensure the user activity log table exists.
 */
function ensureUserActivityLogTable(mysqli $conn): void
{
    static $created = false;
    if ($created) {
        return;
    }

    $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS user_activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(60) NOT NULL,
            details TEXT DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_created_at (user_id, created_at),
            CONSTRAINT fk_user_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    SQL;

    $conn->query($sql);
    $created = true;
}

/**
 * Record a user activity event.
 */
function logUserActivity(mysqli $conn, int $userId, string $action, ?string $details = null): void
{
    if ($userId <= 0) {
        return;
    }

    ensureUserActivityLogTable($conn);

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    if ($stmt = $conn->prepare(
        'INSERT INTO user_activity_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)'
    )) {
        $stmt->bind_param('issss', $userId, $action, $details, $ip, $userAgent);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Fetch activity entries for a user ordered by most recent first.
 *
 * @return array<int, array<string, mixed>>
 */
function fetchUserActivityLogs(mysqli $conn, int $userId, int $limit = 25): array
{
    ensureUserActivityLogTable($conn);

    $rows = [];

    if ($stmt = $conn->prepare(
        'SELECT action, details, ip_address, user_agent, created_at FROM user_activity_logs WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT ?'
    )) {
        $stmt->bind_param('ii', $userId, $limit);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($result && ($row = $result->fetch_assoc())) {
                $rows[] = $row;
            }
        }
        $stmt->close();
    }

    return $rows;
}

/**
 * Convert an activity key into a user-friendly label.
 */
function describeUserActivity(string $action): string
{
    $map = [
        'account_created' => 'Account created',
        'login' => 'Logged in',
        'logout' => 'Logged out',
        'order_placed' => 'Order placed',
        'order_pending_payment' => 'Order pending payment',
        'profile_updated' => 'Profile updated',
        'password_reset' => 'Password reset',
    ];

    return $map[$action] ?? ucwords(str_replace('_', ' ', $action));
}

/**
 * Retrieve an icon name for an activity entry.
 */
function iconForUserActivity(string $action): string
{
    $map = [
        'account_created' => 'bi-person-plus',
        'login' => 'bi-box-arrow-in-right',
        'logout' => 'bi-box-arrow-right',
        'order_placed' => 'bi-bag-check',
        'order_pending_payment' => 'bi-clock-history',
        'profile_updated' => 'bi-person-gear',
        'password_reset' => 'bi-shield-lock',
    ];

    return $map[$action] ?? 'bi-dot';
}
