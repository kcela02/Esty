<?php
// paymongo_return.php - simple success/failure pages for PayMongo redirects
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/cart_helpers.php';

if (!function_exists('finalize_pending_order')) {
  function finalize_pending_order(mysqli $conn, array $pending, ?string $paymongoRef = null, ?string $paymentRef = null): ?int {
    $conn->begin_transaction();
    try {
      $userId = intval($pending['user_id'] ?? 0);
  $status = 'pending';
      $payRef = $paymongoRef ?: ($pending['paymongo_ref'] ?? '');
      $paymentReference = $paymentRef ?: ($pending['paymongo_payment_ref'] ?? '');

      if ($userId > 0) {
        $ins = $conn->prepare("INSERT INTO orders (customer_name,email,address,payment_method,total,user_id,status,paymongo_ref,paymongo_payment_ref) VALUES (?,?,?,?,?,?,?,?,?)");
            $ins->bind_param(
          'ssssdisss',
          $pending['customer_name'],
          $pending['email'],
          $pending['address'],
          $pending['payment_method'],
          $pending['total'],
          $userId,
          $status,
          $payRef,
          $paymentReference
        );
      } else {
        $ins = $conn->prepare("INSERT INTO orders (customer_name,email,address,payment_method,total,status,paymongo_ref,paymongo_payment_ref) VALUES (?,?,?,?,?,?,?,?)");
        $ins->bind_param(
          'ssssdsss',
          $pending['customer_name'],
          $pending['email'],
          $pending['address'],
          $pending['payment_method'],
          $pending['total'],
          $status,
          $payRef,
          $paymentReference
        );
      }
      $ins->execute();
      $newOrderId = $ins->insert_id;
      $ins->close();

      $cartJson = $pending['cart_json'] ?? '[]';
      $cartArr = json_decode($cartJson, true) ?: [];
      foreach ($cartArr as $item) {
        $name = (string)($item['name'] ?? 'Item');
        $price = (float)($item['price'] ?? 0);
        $qty = max(0, intval($item['quantity'] ?? 0));
        if ($qty <= 0) {
          continue;
        }
        $subtotal = $price * $qty;
        $it = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?)");
        $it->bind_param('isdid', $newOrderId, $name, $price, $qty, $subtotal);
        $it->execute();
        $it->close();

        if (!empty($item['id'])) {
          $pid = intval($item['id']);
          if ($pid > 0) {
            $upd = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $upd->bind_param('ii', $qty, $pid);
            $upd->execute();
            $upd->close();
          }
        }
      }

      $del = $conn->prepare("DELETE FROM pending_orders WHERE id = ?");
      $del->bind_param('i', $pending['id']);
      $del->execute();
      $del->close();

      $conn->commit();

      if ($userId > 0) {
        clear_user_cart($conn, $userId);
      } else {
        if (isset($_SESSION['cart'])) {
          unset($_SESSION['cart']);
        }
      }

      return $newOrderId;
    } catch (Throwable $th) {
      $conn->rollback();
      $log = [
        'ts' => date('c'),
        'pending_id' => $pending['id'] ?? null,
        'message' => $th->getMessage(),
      ];
      @file_put_contents(__DIR__ . '/logs/paymongo_return_errors.log', json_encode($log) . "\n---\n", FILE_APPEND);
      return null;
    }
  }
}

$status = $_GET['status'] ?? '';
$order_id = intval($_GET['order_id'] ?? 0);
$pending_id = intval($_GET['pending_id'] ?? 0);
$source_id = $_GET['id'] ?? null; // PayMongo may append source id as ?id=src_xxx

// Optionally fetch source status when `id` is present to show a more accurate message
$source_status = null;
if ($source_id && file_exists(__DIR__ . '/paymongo_config.php')) {
    require_once __DIR__ . '/paymongo_config.php';
    $ch = curl_init('https://api.paymongo.com/v1/sources/' . urlencode($source_id));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, PAYMONGO_SECRET_KEY . ':');
    $resp = curl_exec($ch);
    $hc = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = json_decode($resp, true);
    $source_status = $json['data']['attributes']['status'] ?? null;
}

// If the user returned from PayMongo but we didn't get a webhook (or the webhook hasn't been delivered yet),
// try to reconcile using the stored paymongo_ref on the order or pending_order (best-effort).
if (file_exists(__DIR__ . '/paymongo_config.php')) {
  require_once __DIR__ . '/paymongo_config.php';

  // Helper to fetch source status from PayMongo
  $fetch_source_status = function($ref) {
    $ch = curl_init('https://api.paymongo.com/v1/sources/' . urlencode($ref));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, PAYMONGO_SECRET_KEY . ':');
    $resp = curl_exec($ch);
    $hc = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $j = json_decode($resp, true);
    return $j['data']['attributes']['status'] ?? null;
  };

  if ($order_id > 0) {
    // existing order flow
    $stmt = $conn->prepare("SELECT id, status, paymongo_ref, user_id, total FROM orders WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $orderRow = $res->fetch_assoc();
    $stmt->close();

  if ($orderRow && !in_array(strtolower($orderRow['status']), ['completed','pending'])) {
      $ref = $orderRow['paymongo_ref'] ?? null;
      if (!$ref && $source_id) $ref = $source_id;
      if ($ref) {
        $remoteStatus = $fetch_source_status($ref);
        $s = strtolower($remoteStatus ?? '');
        if (in_array($s, ['succeeded', 'paid', 'captured', 'consumed'])) {
          $u = 'pending';
          $ust = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
          $ust->bind_param('si', $u, $order_id);
          $ust->execute();
          $ust->close();

          $orderUserId = intval($orderRow['user_id'] ?? 0);
          if ($orderUserId > 0) clear_user_cart($conn, $orderUserId);
          delete_cart_backup($conn, $order_id);

          $source_status = $remoteStatus;
          $status = 'success';
        } elseif ($s === 'chargeable') {
          // attempt to create a Payment from this source
          $amount = (int) round(floatval($orderRow['total'] ?? 0) * 100);
          if ($amount > 0) {
            $payPayload = [
              'data' => [ 'attributes' => [ 'amount' => $amount, 'currency' => 'PHP', 'source' => [ 'id' => $ref, 'type' => 'source' ] ] ]
            ];
            $ch3 = curl_init('https://api.paymongo.com/v1/payments');
            curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch3, CURLOPT_POST, true);
            curl_setopt($ch3, CURLOPT_POSTFIELDS, json_encode($payPayload));
            curl_setopt($ch3, CURLOPT_USERPWD, PAYMONGO_SECRET_KEY . ':');
            curl_setopt($ch3, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $resp3 = curl_exec($ch3);
            $hc3 = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
            curl_close($ch3);
            $j3 = json_decode($resp3, true);
            $paymentId = $j3['data']['id'] ?? null;
            $paymentStatus = $j3['data']['attributes']['status'] ?? null;
            if ($paymentId) {
              @$conn->query("UPDATE orders SET paymongo_payment_ref = '" . $conn->real_escape_string($paymentId) . "' WHERE id = " . intval($order_id));
            }
            if (in_array(strtolower($paymentStatus ?? ''), ['paid','succeeded','captured','consumed'])) {
              $ust = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
              $st = 'pending';
              $ust->bind_param('si', $st, $order_id);
              $ust->execute();
              $ust->close();
              $orderUserId = intval($orderRow['user_id'] ?? 0);
              if ($orderUserId > 0) clear_user_cart($conn, $orderUserId);
              delete_cart_backup($conn, $order_id);
              $source_status = $paymentStatus;
              $status = 'success';
            }
          }
        }
      }
    }
  } elseif ($pending_id > 0) {
    // pending order flow: try to reconcile and, if paid, create actual order from pending_orders
    $pst = $conn->prepare("SELECT * FROM pending_orders WHERE id = ? LIMIT 1");
    $pst->bind_param('i', $pending_id);
    $pst->execute();
    $prs = $pst->get_result();
    $pending = $prs->fetch_assoc();
    $pst->close();

    if ($pending) {
      $ref = $pending['paymongo_ref'] ?? null;
      if (!$ref && $source_id) $ref = $source_id;
      if ($ref) {
        $remoteStatus = $fetch_source_status($ref);
        $s = strtolower($remoteStatus ?? '');
        if (in_array($s, ['succeeded', 'paid', 'captured', 'consumed'])) {
          $newOrderId = finalize_pending_order($conn, $pending, $ref, null);
          if ($newOrderId) {
            $order_id = $newOrderId;
            $source_status = $remoteStatus;
            $status = 'success';
          }
        } elseif ($s === 'chargeable') {
          // attempt to create a payment; if successful and paid, finalize as above
          $amount = (int) round(floatval($pending['total'] ?? 0) * 100);
          if ($amount > 0) {
            $payPayload = [ 'data' => [ 'attributes' => [ 'amount' => $amount, 'currency' => 'PHP', 'source' => [ 'id' => $ref, 'type' => 'source' ] ] ] ];
            $ch3 = curl_init('https://api.paymongo.com/v1/payments');
            curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch3, CURLOPT_POST, true);
            curl_setopt($ch3, CURLOPT_POSTFIELDS, json_encode($payPayload));
            curl_setopt($ch3, CURLOPT_USERPWD, PAYMONGO_SECRET_KEY . ':');
            curl_setopt($ch3, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $resp3 = curl_exec($ch3);
            $hc3 = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
            curl_close($ch3);
            $j3 = json_decode($resp3, true);
            $paymentId = $j3['data']['id'] ?? null;
            $paymentStatus = $j3['data']['attributes']['status'] ?? null;
            if ($paymentId) {
              @$conn->query("UPDATE pending_orders SET paymongo_payment_ref = '" . $conn->real_escape_string($paymentId) . "' WHERE id = " . intval($pending_id));
            }
            if (in_array(strtolower($paymentStatus ?? ''), ['paid','succeeded','captured','consumed'])) {
              $newOrderId = finalize_pending_order($conn, $pending, $ref, $paymentId);
              if ($newOrderId) {
                $order_id = $newOrderId;
                $source_status = $paymentStatus;
                $status = 'success';
              }
            }
          }
        }
      }
    }
  }
}

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Payment Result</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>body{background:#f8fafc;padding:40px} .card{border-radius:12px}</style>
</head>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card p-4 text-center">
          <?php if ($status === 'success'): ?>
            <div class="text-success mb-3"><i class="bi bi-check-circle" style="font-size:48px"></i></div>
            <h3>Thank you — payment processing started</h3>
            <?php $displayId = $order_id ? intval($order_id) : ($pending_id ? intval($pending_id) : 'N/A'); ?>
            <p class="mb-2">Order ID: <strong>#<?= $displayId; ?></strong></p>
            <?php if ($source_id): ?>
              <p>Source ID: <strong><?= htmlspecialchars($source_id); ?></strong></p>
            <?php endif; ?>
            <?php if ($source_status): ?>
              <p>Current payment status: <strong><?= htmlspecialchars($source_status); ?></strong></p>
            <?php else: ?>
              <p>Your payment will be finalized shortly. You will receive confirmation once the payment is completed.</p>
            <?php endif; ?>
            <a href="index.php" class="btn btn-primary mt-3">Continue Shopping</a>
      <?php elseif ($status === 'failed'): ?>
            <div class="text-danger mb-3"><i class="bi bi-x-circle" style="font-size:48px"></i></div>
            <h3>Payment Failed or Cancelled</h3>
            <p class="mb-2">Order ID: <strong>#<?= $order_id ? intval($order_id) : 'N/A'; ?></strong></p>
      <p>Please try again or choose another payment method.</p>
      <?php
      // Attempt automatic cart restore from backup
      if ($order_id || $pending_id) {
        $restored = false;
        if ($order_id) {
          if (isset($_SESSION['user_id'])) {
            $uid = $_SESSION['user_id'];
            $restored = restore_cart_from_backup($conn, $order_id, $uid);
          } else {
            $restored = restore_cart_from_backup($conn, $order_id, null);
          }
        } elseif ($pending_id) {
          // pending_orders stores cart_json; restore directly from there
          $pst = $conn->prepare("SELECT cart_json FROM pending_orders WHERE id = ? LIMIT 1");
          $pst->bind_param('i', $pending_id);
          $pst->execute();
          $prs = $pst->get_result();
          $pRow = $prs->fetch_assoc();
          $pst->close();
          if ($pRow && !empty($pRow['cart_json'])) {
            $cartArr = json_decode($pRow['cart_json'], true) ?: [];
            $_SESSION['cart'] = $cartArr;
            if (isset($_SESSION['user_id'])) {
              // also sync to DB cart for logged in user
              sync_user_cart($conn, $_SESSION['user_id']);
            }
            $restored = true;
          }
        }

        if ($restored) {
          echo '<div class="alert alert-success mt-3">Your cart has been restored. You can retry checkout.</div>';
        }
      }
      ?>
      <a href="checkout.php" class="btn btn-warning mt-3">Return to Checkout</a>
          <?php else: ?>
            <div class="text-muted mb-3"><i class="bi bi-info-circle" style="font-size:48px"></i></div>
            <h3>Payment</h3>
            <p>No status provided.</p>
            <a href="index.php" class="btn btn-secondary mt-3">Home</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
