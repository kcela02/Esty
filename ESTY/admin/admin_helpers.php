<?php

declare(strict_types=1);

/**
 * Fetch customers with aggregated order metrics.
 */
function fetchCustomersWithMetrics(mysqli $conn, ?string $search = null): array
{
    $sql = <<<SQL
        SELECT
            u.id,
            u.username,
            u.email,
            u.created_at,
            COALESCE(COUNT(DISTINCT o.id), 0) AS order_count,
            COALESCE(SUM(CASE WHEN o.status = 'completed' THEN o.total ELSE 0 END), 0) AS total_spent,
            MAX(o.created_at) AS last_order_at
        FROM users u
        LEFT JOIN orders o ON o.email = u.email
    SQL;

    $conditions = [];
    $params = [];
    $types = '';

    if ($search !== null && $search !== '') {
        $conditions[] = '(u.username LIKE ? OR u.email LIKE ?)';
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $types .= 'ss';
    }

    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' GROUP BY u.id, u.username, u.email, u.created_at ORDER BY u.created_at DESC';

    $result = [];

    if ($stmt = $conn->prepare($sql)) {
        if ($params) {
            $bindArgs = [$types];
            foreach ($params as $key => $value) {
                $bindArgs[] = &$params[$key];
            }
            call_user_func_array([$stmt, 'bind_param'], $bindArgs);
        }

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $result[] = $row;
                }
            }
        }

        $stmt->close();
    }

    return $result;
}

/**
 * Ensure the admin activity logs table exists.
 */
function ensureActivityLogTable(mysqli $conn): void
{
    static $created = false;
    if ($created) {
        return;
    }

    $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS admin_activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_username VARCHAR(50) NOT NULL,
            action VARCHAR(100) NOT NULL,
            details TEXT DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_admin_username (admin_username),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    SQL;

    $conn->query($sql);
    $created = true;
}

/**
 * Record an admin activity entry.
 */
function logAdminActivity(mysqli $conn, string $username, string $action, ?string $details = null): void
{
    ensureActivityLogTable($conn);

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;

    if ($stmt = $conn->prepare("INSERT INTO admin_activity_logs (admin_username, action, details, ip_address) VALUES (?, ?, ?, ?)")) {
        $stmt->bind_param('ssss', $username, $action, $details, $ip);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Retrieve recent admin activity logs with optional filters.
 */
function fetchAdminActivityLogs(
    mysqli $conn,
    int $limit = 100,
    ?string $adminUsername = null,
    ?string $actionFilter = null
): array {
    ensureActivityLogTable($conn);

    $sql = "SELECT admin_username, action, details, ip_address, created_at FROM admin_activity_logs";
    $conditions = [];
    $params = [];
    $types = '';

    if ($adminUsername !== null && $adminUsername !== '') {
        $conditions[] = 'admin_username = ?';
        $params[] = $adminUsername;
        $types .= 's';
    }

    if ($actionFilter !== null && $actionFilter !== '') {
        $conditions[] = 'action = ?';
        $params[] = $actionFilter;
        $types .= 's';
    }

    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY created_at DESC LIMIT ?';
    $params[] = $limit;
    $types .= 'i';

    $rows = [];

    if ($stmt = $conn->prepare($sql)) {
        $bindArgs = [$types];
        foreach ($params as $key => $value) {
            $bindArgs[] = &$params[$key];
        }

        call_user_func_array([$stmt, 'bind_param'], $bindArgs);

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $rows[] = $row;
                }
            }
        }
        $stmt->close();
    }

    return $rows;
}

/**
 * Fetch high-level sales summary figures optionally filtered by date range.
 */
function fetchSalesSummary(mysqli $conn, ?string $startDate = null, ?string $endDate = null): array
{
    $sql = "SELECT
                COUNT(*) AS order_count,
                SUM(total) AS gross_sales,
                SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END) AS completed_sales,
                AVG(total) AS average_order_value
            FROM orders";

    $conditions = [];
    $params = [];
    $types = '';

    if ($startDate !== null) {
        $conditions[] = 'created_at >= ?';
        $params[] = $startDate . ' 00:00:00';
        $types .= 's';
    }

    if ($endDate !== null) {
        $conditions[] = 'created_at <= ?';
        $params[] = $endDate . ' 23:59:59';
        $types .= 's';
    }

    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $summary = [
        'order_count' => 0,
        'gross_sales' => 0.0,
        'completed_sales' => 0.0,
        'average_order_value' => 0.0,
    ];

    if ($stmt = $conn->prepare($sql)) {
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res) {
                $data = $res->fetch_assoc();
                if ($data) {
                    $summary['order_count'] = (int) ($data['order_count'] ?? 0);
                    $summary['gross_sales'] = (float) ($data['gross_sales'] ?? 0);
                    $summary['completed_sales'] = (float) ($data['completed_sales'] ?? 0);
                    $summary['average_order_value'] = (float) ($data['average_order_value'] ?? 0);
                }
            }
        }
        $stmt->close();
    }

    return $summary;
}

/**
 * Fetch detailed sales by day within a date range.
 */
function fetchDailySales(mysqli $conn, string $startDate, string $endDate): array
{
    $sql = <<<SQL
        SELECT DATE(created_at) AS sale_date,
               SUM(total) AS daily_total,
               COUNT(*) AS daily_count,
               SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END) AS daily_completed
        FROM orders
        WHERE created_at BETWEEN ? AND ?
        GROUP BY sale_date
        ORDER BY sale_date ASC
    SQL;

    $rows = [];

    if ($stmt = $conn->prepare($sql)) {
        $start = $startDate . ' 00:00:00';
        $end = $endDate . ' 23:59:59';
        $stmt->bind_param('ss', $start, $end);

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $rows[] = $row;
                }
            }
        }

        $stmt->close();
    }

    return $rows;
}

/**
 * Fetch the current admin user record.
 */
function fetchAdminUser(mysqli $conn, string $username): ?array
{
    $sql = 'SELECT id, username, password FROM admin_users WHERE username = ? LIMIT 1';

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $username);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res) {
                $row = $res->fetch_assoc();
                $stmt->close();
                return $row ?: null;
            }
        }
        $stmt->close();
    }

    return null;
}

/**
 * Update an admin password with hashing.
 */
function updateAdminPassword(mysqli $conn, int $adminId, string $newPassword): bool
{
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);

    if ($stmt = $conn->prepare('UPDATE admin_users SET password = ? WHERE id = ?')) {
        $stmt->bind_param('si', $hash, $adminId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    return false;
}
