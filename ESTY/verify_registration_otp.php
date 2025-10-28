<?php
session_start();
require 'db.php';
require_once __DIR__ . '/user_activity_helpers.php';
require 'config_email.php';

// Detect AJAX requests (so modal can post and receive JSON)
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// Check if user came from registration
if (!isset($_SESSION['registration_in_progress']) || !isset($_SESSION['otp_email'])) {
    header('Location: register.php');
    exit;
}

$email = $_SESSION['otp_email'];
$message = "";
$otp_verified = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp_input = trim($_POST['otp']);
    
    if (empty($otp_input)) {
        $message = "Please enter the OTP code.";
    } elseif (strlen($otp_input) != 6 || !ctype_digit($otp_input)) {
        $message = "OTP must be 6 digits.";
    } else {
        // Verify OTP
        $stmt = $conn->prepare("SELECT id, username, password, otp, attempts, expires_at FROM user_otp_verifications WHERE email = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
    if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $stored_otp = $row['otp'];
            $username = $row['username'];
            $password = $row['password'];
            $attempts = $row['attempts'];
            
            // Check attempts
      if ($attempts >= 5) {
        $message = "Too many failed attempts. Please register again.";
        // Delete expired/failed OTP
        $stmt = $conn->prepare("DELETE FROM user_otp_verifications WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
      } elseif ($otp_input === $stored_otp) {
        // OTP is correct - Create user account
        $ins_stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $ins_stmt->bind_param("sss", $username, $email, $password);

        if ($ins_stmt->execute()) {
          // Delete OTP record
          $del_stmt = $conn->prepare("DELETE FROM user_otp_verifications WHERE email = ?");
          $del_stmt->bind_param("s", $email);
          $del_stmt->execute();

          $newUserId = (int) $ins_stmt->insert_id;

          // Auto login
          $_SESSION['user_id'] = $newUserId;
          $_SESSION['username'] = $username;
          unset($_SESSION['otp_email']);
          unset($_SESSION['registration_in_progress']);

          logUserActivity($conn, $newUserId, 'account_created', 'Email verified and account created.');
          logUserActivity($conn, $newUserId, 'login', 'Automatic login after registration.');

          // Determine redirect target
          if (isset($_SESSION['redirect_after_login']) && $_SESSION['redirect_after_login'] === 'checkout') {
            $redirectTarget = 'checkout.php';
            unset($_SESSION['redirect_after_login']);
          } else {
            $redirectTarget = 'index.php';
          }

          // For AJAX requests return JSON; otherwise perform a normal redirect
          if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Registration verified', 'redirect' => $redirectTarget]);
            $ins_stmt->close();
            $del_stmt->close();
            exit;
          } else {
            if (isset($_SESSION['redirect_after_login']) && $_SESSION['redirect_after_login'] === 'checkout') {
              unset($_SESSION['redirect_after_login']);
              header("Location: checkout.php");
            } else {
              header("Location: index.php");
            }
            exit;
          }
        } else {
          $message = "Error creating account. Please try again.";
        }
        $ins_stmt->close();
      } else {
                // Wrong OTP - Increment attempts
                $new_attempts = $attempts + 1;
                $update_stmt = $conn->prepare("UPDATE user_otp_verifications SET attempts = ? WHERE email = ?");
                $update_stmt->bind_param("is", $new_attempts, $email);
                $update_stmt->execute();
                $update_stmt->close();
                
                $remaining = 5 - $new_attempts;
                $message = "Invalid OTP. " . ($remaining > 0 ? "You have " . $remaining . " attempts remaining." : "Too many attempts. Please register again.");
            }
        } else {
            $message = "OTP expired. Please register again.";
        }
        $stmt->close();
    }

  // If this was an AJAX POST, return JSON with the current message (success=false)
  if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
  }
}

// Show time remaining
$stmt = $conn->prepare("SELECT expires_at FROM user_otp_verifications WHERE email = ?");
$stmt->bind_param("s", $email);
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
  <title>Verify Email – Esty Scents</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --primary: #e75480;
      --secondary: #ffb6c1;
      --dark: #4B3F2F;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background: linear-gradient(135deg, #fff0f5 0%, #ffe6ec 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .auth-wrapper {
      width: 100%;
      max-width: 480px;
      padding: 20px;
    }
    
    .auth-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      border: 1px solid rgba(255, 182, 193, 0.3);
    }
    
    .auth-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
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
    
    .otp-input-group {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin: 30px 0;
    }
    
    .otp-input {
      width: 50px;
      height: 50px;
      font-size: 24px;
      text-align: center;
      border: 2px solid #e8e8e8;
      border-radius: 10px;
      font-weight: 600;
    }
    
    .otp-input:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 0.2rem rgba(231, 84, 128, 0.15);
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-control {
      border: 2px solid #e8e8e8;
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 16px;
      letter-spacing: 4px;
    }
    
    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.2rem rgba(231, 84, 128, 0.15);
    }
    
    .btn-verify {
      background: linear-gradient(135deg, var(--primary) 0%, #e74c9d 100%);
      border: none;
      color: white;
      padding: 12px 24px;
      border-radius: 10px;
      font-weight: 600;
      width: 100%;
      transition: all 0.3s;
    }
    
    .btn-verify:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(231, 84, 128, 0.3);
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
      color: #e75480;
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
      <h1><i class="bi bi-shield-check"></i> Verify Your Email</h1>
      <p>We've sent a code to <?= htmlspecialchars($email) ?></p>
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
        
        <button type="submit" class="btn-verify">Verify Email</button>
      </form>
      
      <div style="text-align: center; margin-top: 20px;">
        <p style="color: #666; font-size: 14px;">Didn't receive the code?</p>
        <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Start over</a>
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
      // Auto-focus submit button
      document.querySelector('button[type="submit"]').focus();
    }
  });
</script>
</body>
</html>
