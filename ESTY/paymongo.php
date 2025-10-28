<?php
// paymongo.php - create a GCash source via PayMongo and redirect user to checkout
require_once __DIR__ . '/db.php';

// Load config (copy paymongo_config.example.php -> paymongo_config.php)
if (file_exists(__DIR__ . '/paymongo_config.php')) {
    require_once __DIR__ . '/paymongo_config.php';
} else {
    // fail early with clear message
    http_response_code(500);
    echo "PayMongo config missing. Copy paymongo_config.example.php to paymongo_config.php and set your keys.";
    exit;
}

// Accept either an existing order_id or a pending_id (for redirect-based payments)
$order_id = intval($_GET['order_id'] ?? 0);
$pending_id = intval($_GET['pending_id'] ?? 0);
if ($pending_id > 0) {
    $stmt = $conn->prepare("SELECT id, customer_name, email, total, cart_json FROM pending_orders WHERE id = ?");
    $stmt->bind_param('i', $pending_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $order = $res->fetch_assoc();
    $stmt->close();
    if (!$order) {
        http_response_code(404);
        echo "Pending order not found";
        exit;
    }
    $is_pending = true;
} elseif ($order_id > 0) {
    $stmt = $conn->prepare("SELECT id, customer_name, email, total FROM orders WHERE id = ?");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $order = $res->fetch_assoc();
    $stmt->close();
    if (!$order) {
        http_response_code(404);
        echo "Order not found";
        exit;
    }
    $is_pending = false;
} else {
    http_response_code(400);
    echo "Missing order_id or pending_id";
    exit;
}

// PayMongo expects amount in centavos (PHP -> multiply by 100)
$amount = (int) round($order['total'] * 100);

// Build redirect base. Allow override via config constant.
if (defined('PAYMONGO_REDIRECT_BASE') && PAYMONGO_REDIRECT_BASE) {
    $base = rtrim(PAYMONGO_REDIRECT_BASE, '/');
} else {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    // dirname of the script, keep path to project root (assumes project served at /Esty)
    $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $base = $scheme . '://' . $host . $path;
}

$idParam = $is_pending ? ('pending_id=' . intval($pending_id)) : ('order_id=' . intval($order_id));
$success_url = $base . '/paymongo_return.php?status=success&' . $idParam;
$failed_url  = $base . '/paymongo_return.php?status=failed&' . $idParam;

$payload = [
    'data' => [
        'attributes' => [
            'amount' => $amount,
            'currency' => 'PHP',
            'type' => 'gcash',
            'redirect' => [
                'success' => $success_url,
                'failed' => $failed_url,
            ],
            'metadata' => [
                // set metadata.order_id to the appropriate id (pending or real order)
                'order_id' => (string)($is_pending ? $pending_id : $order_id),
            ],
        ],
    ],
];

$jsonPayload = json_encode($payload);

$ch = curl_init('https://api.paymongo.com/v1/sources');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
curl_setopt($ch, CURLOPT_USERPWD, PAYMONGO_SECRET_KEY . ':');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

$logEntry = [
    'ts' => date('c'),
    'endpoint' => 'https://api.paymongo.com/v1/sources',
    'payload' => $payload,
    'http_code' => $httpCode,
    'response' => json_decode($response, true),
    'curl_error' => $curlError,
];

@file_put_contents(__DIR__ . '/logs/paymongo.log', json_encode($logEntry, JSON_PRETTY_PRINT) . "\n---\n", FILE_APPEND);

if ($httpCode >= 200 && $httpCode < 300) {
    $parsed = json_decode($response, true);
    $sourceId = $parsed['data']['id'] ?? null;
    // Store paymongo_ref on the pending_orders or orders table depending on flow
    if ($sourceId) {
        if (!empty($is_pending)) {
            @$conn->query("UPDATE pending_orders SET paymongo_ref = '" . $conn->real_escape_string($sourceId) . "' WHERE id = " . intval($pending_id));
        } else {
            @$conn->query("UPDATE orders SET paymongo_ref = '" . $conn->real_escape_string($sourceId) . "' WHERE id = " . intval($order_id));
        }
    }
    $checkout = $parsed['data']['attributes']['redirect']['checkout_url'] ?? null;
    if ($checkout) {
        header('Location: ' . $checkout);
        exit;
    }
}

// On error, show a friendly message with details
http_response_code(500);
echo "Unable to create PayMongo checkout. Please try again later.";
if ($curlError) echo "\nCurl error: " . htmlspecialchars($curlError);
if ($response)  echo "\nResponse: " . htmlspecialchars($response);

?>
