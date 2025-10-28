<?php
// cart_helpers.php - shared cart helper functions: sync, backup, restore, clear
require_once __DIR__ . '/db.php';

function ensure_cart_backup_table(mysqli $conn) {
    $sql = "CREATE TABLE IF NOT EXISTS order_cart_backups (
        order_id INT PRIMARY KEY,
        cart_json TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $conn->query($sql);
}

function create_cart_backup(mysqli $conn, int $order_id, array $cart) {
    ensure_cart_backup_table($conn);
    $json = json_encode($cart);
    $stmt = $conn->prepare("REPLACE INTO order_cart_backups (order_id, cart_json) VALUES (?, ?)");
    $stmt->bind_param('is', $order_id, $json);
    $stmt->execute();
    $stmt->close();
}

function get_cart_backup(mysqli $conn, int $order_id) {
    ensure_cart_backup_table($conn);
    $stmt = $conn->prepare("SELECT cart_json FROM order_cart_backups WHERE order_id = ?");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $stmt->bind_result($json);
    if ($stmt->fetch()) {
        $stmt->close();
        return json_decode($json, true) ?: [];
    }
    $stmt->close();
    return null;
}

function delete_cart_backup(mysqli $conn, int $order_id) {
    $stmt = $conn->prepare("DELETE FROM order_cart_backups WHERE order_id = ?");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $stmt->close();
}

function sync_user_cart(mysqli $conn, int $user_id): void {
    $stmt = $conn->prepare("SELECT c.product_id, c.quantity, p.name, p.price, p.image FROM carts c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $_SESSION['cart'] = [];
    while ($row = $res->fetch_assoc()) {
        $_SESSION['cart'][] = [
            'id' => $row['product_id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'quantity' => (int)$row['quantity'],
            'image' => $row['image'] ?? null,
        ];
    }
    $stmt->close();
}

function sync_session_cart_from_db(mysqli $conn, int $user_id) {
    // alias to sync_user_cart for naming compatibility
    sync_user_cart($conn, $user_id);
}

function clear_user_cart(mysqli $conn, int $user_id) {
    $stmt = $conn->prepare("DELETE FROM carts WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
    // clear session as well
    if (isset($_SESSION['cart'])) unset($_SESSION['cart']);
}

function restore_cart_from_backup(mysqli $conn, int $order_id, ?int $user_id = null) {
    $backup = get_cart_backup($conn, $order_id);
    if ($backup === null) return false;
    if ($user_id) {
        // insert items into carts table (upsert)
        foreach ($backup as $item) {
            $pid = intval($item['id'] ?? 0);
            $qty = intval($item['quantity'] ?? 0);
            if ($pid <= 0 || $qty <= 0) continue;
            // check existing
            $stmt = $conn->prepare("SELECT quantity FROM carts WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param('ii', $user_id, $pid);
            $stmt->execute();
            $stmt->bind_result($existing);
            $has = $stmt->fetch();
            $stmt->close();
            if ($has) {
                $ust = $conn->prepare("UPDATE carts SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $ust->bind_param('iii', $qty, $user_id, $pid);
                $ust->execute();
                $ust->close();
            } else {
                $inst = $conn->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $inst->bind_param('iii', $user_id, $pid, $qty);
                $inst->execute();
                $inst->close();
            }
        }
        // sync session
        sync_user_cart($conn, $user_id);
    } else {
        // restore into session for guest
        $_SESSION['cart'] = $backup;
    }
    return true;
}

?>
