<?php
session_start();
require 'db.php';
require 'config_email.php';

$message = "";
$message_type = "";
$token = isset($_GET['token']) ? trim($_GET['token']) : null;
$show_password_form = false;
$show_otp_form = false;
$reset_user_id = null;
$reset_email = null;

// Validate token
if ($token) {
    $stmt = $conn->prepare("SELECT id, email, reset_otp, expires_at, verified_at FROM password_resets WHERE reset_token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $reset_record = $result->fetch_assoc();
        $reset_user_id = $reset_record['id'];
        $reset_email = $reset_record['email'];
        
        if ($reset_record['verified_at']) {
            // OTP already verified, show password reset form
            $show_password_form = true;
        } else {
            // Need to verify OTP first
            $show_otp_form = true;
        }
    } else {
        $message = "Invalid or expired reset link. Please request a new password reset.";
        $message_type = "danger";
    }
    $stmt->close();
} else {
    $message = "No reset token provided. Please request a password reset.";
    $message_type = "danger";
}

// Handle OTP verification
if ($show_otp_form && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    $otp_input = trim($_POST['otp']);
    
    if (empty($otp_input)) {
        $message = "Please enter the OTP code.";
        $message_type = "danger";
    } elseif (strlen($otp_input) != 6 || !ctype_digit($otp_input)) {
        $message = "OTP must be 6 digits.";
        $message_type = "danger";
    } else {
        // Get the stored OTP
        $stmt = $conn->prepare("SELECT reset_otp, attempts, expires_at FROM password_resets WHERE reset_token = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $stored_otp = $row['reset_otp'];
            $attempts = $row['attempts'];
            
            if ($attempts >= 5) {
                $message = "Too many failed attempts. Please request a new password reset.";
                $message_type = "danger";
                $show_otp_form = false;
            } elseif ($otp_input === $stored_otp) {
                // OTP is correct, mark as verified
                $update_stmt = $conn->prepare("UPDATE password_resets SET verified_at = CURRENT_TIMESTAMP WHERE reset_token = ?");
                $update_stmt->bind_param("s", $token);
                if ($update_stmt->execute()) {
                    $show_otp_form = false;
                    $show_password_form = true;
                } else {
                    $message = "Error verifying OTP. Please try again.";
                    $message_type = "danger";
                }
                $update_stmt->close();
            } else {
                // Wrong OTP
                $new_attempts = $attempts + 1;
                $update_stmt = $conn->prepare("UPDATE password_resets SET attempts = ? WHERE reset_token = ?");
                $update_stmt->bind_param("is", $new_attempts, $token);
                $update_stmt->execute();
                $update_stmt->close();
                
                $remaining = 5 - $new_attempts;
                $message = "Invalid OTP. " . ($remaining > 0 ? "You have " . $remaining . " attempts remaining." : "Too many attempts. Please request a new password reset.");
                $message_type = "danger";
            }
        } else {
            $message = "OTP expired. Please request a new password reset.";
            $message_type = "danger";
            $show_otp_form = false;
        }
        $stmt->close();
    }
}

// Handle password reset
if ($show_password_form && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password'])) {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($new_password) || empty($confirm_password)) {
        $message = "Please enter both password fields.";
        $message_type = "danger";
    } elseif (strlen($new_password) < 6) {
        $message = "Password must be at least 6 characters.";
        $message_type = "danger";
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "danger";
    } else {
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Get user_id from password_resets
        $get_user_stmt = $conn->prepare("SELECT user_id FROM password_resets WHERE reset_token = ?");
        $get_user_stmt->bind_param("s", $token);
        $get_user_stmt->execute();
        $user_result = $get_user_stmt->get_result();
        
        if ($user_result->num_rows == 1) {
            $user_row = $user_result->fetch_assoc();
            $user_id = $user_row['user_id'];
            
            // Update user password
            $update_user_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_user_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_user_stmt->execute()) {
                // Delete password reset record
                $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE reset_token = ?");
                $delete_stmt->bind_param("s", $token);
                $delete_stmt->execute();
                
                $message = "Password reset successful! Redirecting to login in 3 seconds...";
                $message_type = "success";
                $show_password_form = false;
                
                echo "<script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 3000);
                </script>";
            } else {
                $message = "Error updating password. Please try again.";
                $message_type = "danger";
            }
            $update_user_stmt->close();
        }
        $get_user_stmt->close();
    }
}

// Get time remaining for OTP
$time_remaining = "";
if ($show_otp_form && $token) {
    $stmt = $conn->prepare("SELECT expires_at FROM password_resets WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password – Esty Scents</title>
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

    .form-label {
      font-weight: 600;
      color: var(--charcoal);
      margin-bottom: 8px;
    }

    .form-control {
      border: 2px solid rgba(201, 166, 70, 0.2);
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 16px;
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

    .btn-submit {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      border: none;
      color: white;
      padding: 12px 24px;
      border-radius: 10px;
      font-weight: 600;
      width: 100%;
      transition: all 0.3s;
      cursor: pointer;
    }

    .btn-submit:hover {
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

    .alert-success {
      background: #e8f5e9;
      color: #2e7d32;
    }

    .alert-warning {
      background: #fff3cd;
      color: #856404;
    }

    .timer {
      text-align: center;
      color: var(--primary-dark);
      font-weight: 600;
      font-size: 18px;
      margin: 20px 0;
    }

    .auth-footer {
      text-align: center;
      padding: 20px 40px 30px 40px;
    }

    .auth-footer p {
      margin: 0;
      color: #666;
      font-size: 14px;
    }

    .auth-footer a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
    }

    .auth-footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-header">
      <h1><i class="bi bi-shield-lock"></i> Reset Password</h1>
      <p>Secure your account with a new password</p>
    </div>

    <div class="auth-body">
      <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show" role="alert">
          <i class="bi bi-<?= $message_type === 'success' ? 'check-circle' : ($message_type === 'danger' ? 'exclamation-circle' : 'info-circle') ?>"></i>
          <?= htmlspecialchars($message) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- OTP Verification Form -->
      <?php if ($show_otp_form): ?>
        <form method="POST" action="">
          <div class="form-group">
            <label for="otp" class="form-label">Enter Verification Code</label>
            <input type="text"
                   name="otp"
                   id="otp"
                   class="form-control text-center"
                   placeholder="000000"
                   maxlength="6"
                   inputmode="numeric"
                   pattern="[0-9]{6}"
                   required
                   autofocus>
            <small class="d-block mt-2 text-muted">Enter the 6-digit code sent to <?= htmlspecialchars($reset_email) ?></small>
          </div>

          <?php if ($time_remaining): ?>
            <div class="timer">
              ⏱️ Time remaining: <span id="timer"><?= htmlspecialchars($time_remaining) ?></span>
            </div>
          <?php endif; ?>

          <?php if (DEBUG_MODE): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
              <strong><i class="bi bi-bug"></i> DEBUG MODE</strong><br>
              <small>For testing, OTP is logged to file.<br>
              Check the code in <code>logs/password_reset_debug.log</code></small>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <button type="submit" class="btn-submit">Verify Code</button>
        </form>
      <?php endif; ?>

      <!-- Password Reset Form -->
      <?php if ($show_password_form): ?>
        <form method="POST" action="">
          <div class="form-group">
            <label for="password" class="form-label">New Password</label>
            <input type="password"
                   name="password"
                   id="password"
                   class="form-control"
                   placeholder="At least 6 characters"
                   required>
            <small class="d-block mt-2 text-muted">Use a strong password with letters, numbers, and symbols</small>
          </div>

          <div class="form-group">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password"
                   name="confirm_password"
                   id="confirm_password"
                   class="form-control"
                   placeholder="Confirm password"
                   required>
          </div>

          <button type="submit" class="btn-submit">Reset Password</button>
        </form>
      <?php endif; ?>

      <?php if (!$show_otp_form && !$show_password_form && $message_type === 'danger'): ?>
        <div style="text-align: center; margin-top: 20px;">
          <a href="forgot_password.php" class="btn btn-primary">Request New Reset Link</a>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!$show_otp_form && !$show_password_form && !$message_type === 'success'): ?>
      <div class="auth-footer">
        <p>Remember your password? <a href="login.php">Login here</a></p>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Only allow numeric input for OTP
  const otpInput = document.getElementById('otp');
  if (otpInput) {
    otpInput.addEventListener('input', function(e) {
      e.target.value = e.target.value.replace(/[^0-9]/g, '');
    });
  }

  // Password strength indicator
  const passwordInput = document.getElementById('password');
  if (passwordInput) {
    passwordInput.addEventListener('input', function(e) {
      const strength = checkPasswordStrength(e.target.value);
      // Could add visual indicator here
    });
  }

  function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    return strength;
  }
</script>
</body>
</html>
