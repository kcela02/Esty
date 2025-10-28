<?php
// paymongo_webhook.php - receive PayMongo webhook events and update order status
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/cart_helpers.php';

$raw = file_get_contents('php://input');
$headers = function_exists('getallheaders') ? getallheaders() : [];

$sigHeader = null;
foreach ($headers as $k => $v) {
    $lk = strtolower($k);
    if ($lk === 'paymongo-signature' || $lk === 'paymongo_signature' || $lk === 'signature') {
        $sigHeader = $v;
        break;
    }
}

$verified = false;
if (file_exists(__DIR__ . '/paymongo_config.php')) {
    require_once __DIR__ . '/paymongo_config.php';
    if (!empty(PAYMONGO_WEBHOOK_SECRET) && $sigHeader) {
        // header may be in form 't=..., v1=signature' or 'v1=signature' or just the signature value
        $sig = $sigHeader;
        if (strpos($sigHeader, 'v1=') !== false) {
            if (preg_match('/v1=([a-f0-9]+)/i', $sigHeader, $m)) $sig = $m[1];
        }
        $calc = hash_hmac('sha256', $raw, PAYMONGO_WEBHOOK_SECRET);
        if (function_exists('hash_equals')) {
            $verified = hash_equals($calc, $sig);
        } else {
            $verified = ($calc === $sig);
        }
    } else {
        // No secret configured: treat as unverified but allow processing (still log)
        $verified = false;
    }
}

$payload = json_decode($raw, true);
@file_put_contents(__DIR__ . '/logs/paymongo_webhook.log', date('c') . " verified=" . ($verified ? '1' : '0') . "\n" . $raw . "\n---\n", FILE_APPEND);

// basic validation
if (!$payload || !isset($payload['data'])) {
    http_response_code(400);
    echo 'invalid payload';
    exit;
}

$data = $payload['data'];
$attrs = $data['attributes'] ?? [];

$metadata = $attrs['metadata'] ?? [];
$order_id = intval($metadata['order_id'] ?? 0);

// helper: create a PayMongo payment from a source and update order/payment refs
function create_payment_from_source(mysqli $conn, $orderId, $sourceId) {
    if (!$sourceId || !$orderId) return null;
    // First try to fetch from orders table
    $stmt = $conn->prepare("SELECT id, total, user_id, paymongo_payment_ref FROM orders WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $res = $stmt->get_result();
    $order = $res->fetch_assoc();
    $stmt->close();

    $isPending = false;
    if (!$order) {
        // maybe this id refers to a pending_order
        $pst = $conn->prepare("SELECT id, total, user_id, paymongo_payment_ref, cart_json, paymongo_ref FROM pending_orders WHERE id = ? LIMIT 1");
        $pst->bind_param('i', $orderId);
        $pst->execute();
        $prs = $pst->get_result();
        $pending = $prs->fetch_assoc();
        $pst->close();
        if ($pending) {
            $order = $pending;
            $isPending = true;
        }
    }

    if (!$order) return null;
    if (!empty($order['paymongo_payment_ref'])) return $order['paymongo_payment_ref']; // already created

    $amount = (int) round($order['total'] * 100);
    if ($amount <= 0) return null;

    // build payload for payments
    $payload = [
        'data' => [
            'attributes' => [
                'amount' => $amount,
                'currency' => 'PHP',
                'source' => [ 'id' => $sourceId, 'type' => 'source' ]
            ]
        ]
    ];
    $json = json_encode($payload);
    // perform API call
    if (!file_exists(__DIR__ . '/paymongo_config.php')) return null;
    require_once __DIR__ . '/paymongo_config.php';
    $ch = curl_init('https://api.paymongo.com/v1/payments');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_USERPWD, PAYMONGO_SECRET_KEY . ':');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $resp = curl_exec($ch);
    $hc = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $j = json_decode($resp, true);
    $paymentId = $j['data']['id'] ?? null;
    $paymentStatus = $j['data']['attributes']['status'] ?? null;

    if ($paymentId) {
        if ($isPending) {
            @$conn->query("UPDATE pending_orders SET paymongo_payment_ref = '" . $conn->real_escape_string($paymentId) . "' WHERE id = " . intval($orderId));
        } else {
            @$conn->query("UPDATE orders SET paymongo_payment_ref = '" . $conn->real_escape_string($paymentId) . "' WHERE id = " . intval($orderId));
        }
    }

    // If payment is paid/succeeded, mark order pending for admin review and clear cart/backup (and finalize pending_orders)
    $s = strtolower($paymentStatus ?? '');
    if (in_array($s, ['paid','succeeded','captured','consumed'])) {
        if ($isPending) {
            // finalize pending into actual order
            $conn->begin_transaction();
            $user_id = intval($order['user_id'] ?? 0);
            if ($user_id > 0) {
                $ins = $conn->prepare("INSERT INTO orders (customer_name,email,address,payment_method,total,user_id,status,paymongo_ref,paymongo_payment_ref) VALUES (?,?,?,?,?,?,?,?,?)");
                $st = 'pending';
                $ins->bind_param('ssssdisss', $order['customer_name'], $order['email'], $order['address'], $order['payment_method'], $order['total'], $user_id, $st, $order['paymongo_ref'] ?? $sourceId, $paymentId);
            } else {
                $ins = $conn->prepare("INSERT INTO orders (customer_name,email,address,payment_method,total,status,paymongo_ref,paymongo_payment_ref) VALUES (?,?,?,?,?,?,?,?)");
                $st = 'pending';
                $ins->bind_param('ssssdsss', $order['customer_name'], $order['email'], $order['address'], $order['payment_method'], $order['total'], $st, $order['paymongo_ref'] ?? $sourceId, $paymentId);
            }
            $ins->execute();
            $newOrderId = $ins->insert_id;
            $ins->close();

            // Insert order items
            $cartJson = $order['cart_json'] ?? '[]';
            $cartArr = json_decode($cartJson, true) ?: [];
            foreach ($cartArr as $item) {
                $subtotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                $it = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?)");
                $it->bind_param('isdid', $newOrderId, $item['name'], $item['price'], $item['quantity'], $subtotal);
                $it->execute();
                $it->close();
                if (!empty($item['id'])) {
                    $pid = intval($item['id']);
                    $qty = intval($item['quantity']);
                    $upd = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $upd->bind_param('ii', $qty, $pid);
                    $upd->execute();
                    $upd->close();
                }
            }

            // delete pending_orders row
            $d = $conn->prepare("DELETE FROM pending_orders WHERE id = ?");
            $d->bind_param('i', $orderId);
            $d->execute();
            $d->close();

            $conn->commit();

            // clear user's cart
            if ($user_id > 0) clear_user_cart($conn, $user_id);
        } else {
            $ust = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $statusVal = 'pending';
            $ust->bind_param('si', $statusVal, $orderId);
            $ust->execute();
            $ust->close();

            // clear user cart
            $orderUserId = intval($order['user_id'] ?? 0);
            if ($orderUserId > 0) clear_user_cart($conn, $orderUserId);

            // delete backup
            delete_cart_backup($conn, $orderId);
        }
    }

    return $paymentId ?: null;
}

// Determine status from attributes or event type
$newStatus = null;
$attrStatus = strtolower($attrs['status'] ?? '');
$eventType = strtolower($payload['type'] ?? '');
if ($attrStatus) {
    if (in_array($attrStatus, ['succeeded','paid','captured','consumed'])) {
        $newStatus = 'pending';
    } elseif (in_array($attrStatus, ['failed','canceled','cancelled'])) {
        $newStatus = 'failed';
    } elseif ($attrStatus === 'chargeable') {
        // special: attempt to create a Payment from this source
        $sourceId = $data['id'] ?? null;
        if ($sourceId && $order_id > 0) {
            create_payment_from_source($conn, $order_id, $sourceId);
        }
        $newStatus = 'processing';
    } else {
        $newStatus = 'processing';
    }
} elseif ($eventType) {
    if (strpos($eventType, 'paid') !== false || strpos($eventType, 'succeed') !== false || strpos($eventType, 'consum') !== false) $newStatus = 'pending';
    elseif (strpos($eventType, 'failed') !== false) $newStatus = 'failed';
    elseif (strpos($eventType, 'source.chargeable') !== false) {
        $sourceId = $data['id'] ?? null;
        if ($sourceId && $order_id > 0) create_payment_from_source($conn, $order_id, $sourceId);
        $newStatus = 'processing';
    } else $newStatus = 'processing';
}

if ($order_id > 0 && $newStatus) {
    // determine whether this id refers to an existing order or a pending_order
    $isExistingOrder = false;
    $check = $conn->prepare("SELECT id FROM orders WHERE id = ? LIMIT 1");
    $check->bind_param('i', $order_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) $isExistingOrder = true;
    $check->close();

    $sourceId = $data['id'] ?? null;

    if ($isExistingOrder) {
        // update order status in DB
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $newStatus, $order_id);
        $stmt->execute();
        $stmt->close();

        // store source id if present
        if ($sourceId) {
            @$conn->query("UPDATE orders SET paymongo_ref = '" . $conn->real_escape_string($sourceId) . "' WHERE id = " . intval($order_id));
        }

        // If payment completed, clear user's cart (use backup removal) and delete backup
        if ($newStatus === 'pending') {
            $orderUserId = null;
            $stmt2 = $conn->prepare("SELECT user_id FROM orders WHERE id = ? LIMIT 1");
            $stmt2->bind_param('i', $order_id);
            $stmt2->execute();
            $stmt2->bind_result($orderUserId);
            $stmt2->fetch();
            $stmt2->close();

            if ($orderUserId && intval($orderUserId) > 0) {
                clear_user_cart($conn, intval($orderUserId));
            }

            delete_cart_backup($conn, $order_id);
        }
    } else {
        // might be a pending_order id - let create_payment_from_source handle creating payment and finalizing
        if ($sourceId) {
            create_payment_from_source($conn, $order_id, $sourceId);
        }
    // For paid events without a source/payments step above, attempt to finalize pending into orders
        if ($newStatus === 'pending') {
            // try to fetch pending and finalize
            $pst = $conn->prepare("SELECT * FROM pending_orders WHERE id = ? LIMIT 1");
            $pst->bind_param('i', $order_id);
            $pst->execute();
            $prs = $pst->get_result();
            $pending = $prs->fetch_assoc();
            $pst->close();
            if ($pending) {
                // create order from pending (no payment details available here beyond metadata)
                $conn->begin_transaction();
                $user_id = intval($pending['user_id'] ?? 0);
                if ($user_id > 0) {
                    $ins = $conn->prepare("INSERT INTO orders (customer_name,email,address,payment_method,total,user_id,status,paymongo_ref) VALUES (?,?,?,?,?,?,?,?)");
                    $st = 'pending';
                    $ins->bind_param('ssssdiss', $pending['customer_name'], $pending['email'], $pending['address'], $pending['payment_method'], $pending['total'], $user_id, $st, $pending['paymongo_ref'] ?? $sourceId);
                } else {
                    $ins = $conn->prepare("INSERT INTO orders (customer_name,email,address,payment_method,total,status,paymongo_ref) VALUES (?,?,?,?,?,?,?)");
                    $st = 'pending';
                    $ins->bind_param('ssssdss', $pending['customer_name'], $pending['email'], $pending['address'], $pending['payment_method'], $pending['total'], $st, $pending['paymongo_ref'] ?? $sourceId);
                }
                $ins->execute();
                $newOrderId = $ins->insert_id;
                $ins->close();

                $cartJson = $pending['cart_json'] ?? '[]';
                $cartArr = json_decode($cartJson, true) ?: [];
                foreach ($cartArr as $item) {
                    $subtotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                    $it = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?)");
                    $it->bind_param('isdid', $newOrderId, $item['name'], $item['price'], $item['quantity'], $subtotal);
                    $it->execute();
                    $it->close();
                    if (!empty($item['id'])) {
                        $pid = intval($item['id']);
                        $qty = intval($item['quantity']);
                        $upd = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                        $upd->bind_param('ii', $qty, $pid);
                        $upd->execute();
                        $upd->close();
                    }
                }

                $d = $conn->prepare("DELETE FROM pending_orders WHERE id = ?");
                $d->bind_param('i', $order_id);
                $d->execute();
                $d->close();

                $conn->commit();
            }
        }
    }
}

// respond 200 to acknowledge
http_response_code(200);
echo 'ok';

?>
