<?php
session_start();
require 'db.php';
require 'config_email.php';

// Check if user came from login
if (!isset($_SESSION['login_user_id']) || !isset($_SESSION['login_email'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['login_user_id'];
$email = $_SESSION['login_email'];
$username = $_SESSION['login_username'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp_input = trim($_POST['otp']);
    
    if (empty($otp_input)) {
        $message = "Please enter the verification code.";
    } elseif (strlen($otp_input) != 6 || !ctype_digit($otp_input)) {
        $message = "Code must be 6 digits.";
    } else {
        // Verify OTP
        $stmt = $conn->prepare("SELECT id, otp, attempts, expires_at FROM login_otp_verifications WHERE user_id = ? AND expires_at > NOW()");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $stored_otp = $row['otp'];
            $attempts = $row['attempts'];
            
            // Check attempts
            if ($attempts >= 5) {
                $message = "Too many failed attempts. Please login again.";
                // Delete expired/failed OTP
                $stmt = $conn->prepare("DELETE FROM login_otp_verifications WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                
                // Clear session
                unset($_SESSION['login_user_id']);
                unset($_SESSION['login_email']);
                unset($_SESSION['login_username']);
            } elseif ($otp_input === $stored_otp) {
                // OTP is correct - Complete login
                // Delete OTP record
                $del_stmt = $conn->prepare("DELETE FROM login_otp_verifications WHERE user_id = ?");
                $del_stmt->bind_param("i", $user_id);
                $del_stmt->execute();
                
                // Set session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                
                // Load user's cart
                $_SESSION['cart'] = [];
                
                $cart_stmt = $conn->prepare("
                    SELECT c.product_id, c.quantity, p.name, p.price
                    FROM carts c
                    JOIN products p ON c.product_id = p.id
                    WHERE c.user_id = ?
                ");
                $cart_stmt->bind_param("i", $user_id);
                $cart_stmt->execute();
                $cart_result = $cart_stmt->get_result();
                
                while ($cart_row = $cart_result->fetch_assoc()) {
                    $_SESSION['cart'][] = [
                        'id' => $cart_row['product_id'],
                        'name' => $cart_row['name'],
                        'price' => $cart_row['price'],
                        'quantity' => $cart_row['quantity']
                    ];
                }
                $cart_stmt->close();
                
                // Clear temporary session variables
                unset($_SESSION['login_user_id']);
                unset($_SESSION['login_email']);
                unset($_SESSION['login_username']);
                
                // Redirect to index (normal login), unless they came from checkout
                if (isset($_SESSION['redirect_after_login']) && $_SESSION['redirect_after_login'] === 'checkout') {
                    unset($_SESSION['redirect_after_login']);
                    header("Location: checkout.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                // Wrong OTP - Increment attempts
                $new_attempts = $attempts + 1;
                $update_stmt = $conn->prepare("UPDATE login_otp_verifications SET attempts = ? WHERE user_id = ?");
                $update_stmt->bind_param("ii", $new_attempts, $user_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                $remaining = 5 - $new_attempts;
                $message = "Invalid code. " . ($remaining > 0 ? "You have " . $remaining . " attempts remaining." : "Too many attempts. Please login again.");
            }
        } else {
            $message = "Code expired. Please login again.";
        }
        $stmt->close();
    }
}

// Show time remaining
$stmt = $conn->prepare("SELECT expires_at FROM login_otp_verifications WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$time_remaining = "";
if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $expires_at = strtotime($row['expires_at']);
    $now = time();
    $remaining_seconds = $expires_at - $now;
    if ($remaining_seconds > 0) {
        $minutes = floor($remaining_seconds / 60);
        $seconds = $remaining_seconds % 60;
        $time_remaining = sprintf("%02d:%02d", $minutes, $seconds);
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify Login – Esty Scents</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --primary: #c9a646;
      --primary-soft: #e7d38d;
      --primary-dark: #8b6b2d;
      --charcoal: #3c3327;
      --taupe: #6f665b;
      --canvas: #f9f3e6;
      --canvas-soft: #f3ebdd;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
      background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.9), rgba(243, 235, 221, 0.94) 55%, rgba(236, 225, 205, 0.96)), linear-gradient(135deg, #fefaf1 0%, #f3e8d7 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px 16px;
      color: var(--taupe);
    }

    .auth-wrapper {
      width: 100%;
      max-width: 460px;
    }

    .auth-card {
      background: rgba(255, 255, 255, 0.82);
      border-radius: 24px;
      border: 1px solid rgba(201, 166, 70, 0.26);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .auth-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-soft) 100%);
      padding: 40px 20px;
      text-align: center;
      color: white;
    }

    .auth-header h1 {
      font-size: 28px;
      font-weight: 700;
      margin: 0;
    }

    .auth-header p {
      margin: 10px 0 0;
      font-size: 14px;
      opacity: 0.9;
    }

    .auth-body {
      padding: 40px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-control {
      border: 2px solid rgba(201, 166, 70, 0.2);
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 16px;
      letter-spacing: 4px;
      background: rgba(249, 243, 230, 0.5);
      color: var(--charcoal);
    }

    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.2rem rgba(201, 166, 70, 0.15);
      background: white;
      color: var(--charcoal);
    }

    .form-control::placeholder {
      color: #bbb;
    }

    .btn-login {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      border: none;
      color: white;
      padding: 12px 24px;
      border-radius: 10px;
      font-weight: 600;
      width: 100%;
      transition: all 0.3s;
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(201, 166, 70, 0.3);
      color: white;
    }

    .alert {
      border-radius: 10px;
      border: none;
      margin-bottom: 20px;
    }

    .alert-danger {
      background: #ffe8e8;
      color: #d32f2f;
    }

    .alert-info {
      background: #e3f2fd;
      color: #1976d2;
    }

    .timer {
      text-align: center;
      color: var(--primary-dark);
      font-weight: 600;
      font-size: 18px;
      margin: 20px 0;
    }

    .email-display {
      text-align: center;
      color: #666;
      margin: 10px 0 20px;
      word-break: break-all;
    }
  </style>
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-header">
      <h1><i class="bi bi-shield-lock"></i> Verify Your Login</h1>
      <p>Confirmation code sent to <?= htmlspecialchars($email) ?></p>
    </div>

    <div class="auth-body">
      <?php if ($message): ?>
        <div class="alert alert-<?= strpos($message, 'Invalid') !== false || strpos($message, 'Too many') !== false ? 'danger' : 'info' ?> alert-dismissible fade show" role="alert">
          <i class="bi bi-<?= strpos($message, 'Invalid') !== false ? 'exclamation-circle' : 'info-circle' ?>"></i> <?= htmlspecialchars($message) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if (DEBUG_MODE): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
          <strong><i class="bi bi-bug"></i> DEBUG MODE ENABLED</strong><br>
          <small>For testing: OTP is being logged instead of emailed.<br>
          <strong>Your OTP Code: <span style="font-family: monospace; background: #fff3cd; padding: 2px 6px; border-radius: 3px;"><?= htmlspecialchars(getDebugOtp($email) ?? 'N/A') ?></span></strong></small>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label class="form-label fw-600">Enter Verification Code</label>
          <input type="text"
                 name="otp"
                 class="form-control text-center"
                 placeholder="000000"
                 maxlength="6"
                 inputmode="numeric"
                 pattern="[0-9]{6}"
                 required
                 autofocus>
          <small class="d-block mt-2 text-muted">Enter the 6-digit code sent to your email</small>
        </div>

        <?php if ($time_remaining): ?>
          <div class="timer">
            ⏱️ Time remaining: <span id="timer"><?= htmlspecialchars($time_remaining) ?></span>
          </div>
        <?php endif; ?>

        <button type="submit" class="btn-login">Verify & Login</button>
      </form>

      <div style="text-align: center; margin-top: 20px;">
        <p style="color: #666; font-size: 14px;">Didn't receive the code?</p>
        <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Try again</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Only allow numeric input
  document.querySelector('input[name="otp"]').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^0-9]/g, '');
  });

  // Auto-submit when 6 digits entered
  document.querySelector('input[name="otp"]').addEventListener('input', function(e) {
    if (e.target.value.length === 6) {
      document.querySelector('button[type="submit"]').focus();
    }
  });
</script>
</body>
</html>
