<?php
session_start();
require 'db.php';
require 'config_email.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $response = [
            'success' => false,
            'message' => 'Please enter your email address.'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = [
            'success' => false,
            'message' => 'Please enter a valid email address.'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        // Check if email exists in users table
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
            $username = $user['username'];
            
            // Generate reset token and OTP
            $reset_token = bin2hex(random_bytes(32)); // 64-char token
            $reset_otp = generateOtp();
            
            // Create password_resets table if not exists
            $createTableSql = "CREATE TABLE IF NOT EXISTS `password_resets` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `email` VARCHAR(100) NOT NULL,
                `reset_token` VARCHAR(64) NOT NULL UNIQUE,
                `reset_otp` VARCHAR(6) NOT NULL,
                `attempts` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `expires_at` TIMESTAMP DEFAULT (DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 30 MINUTE)),
                `verified_at` TIMESTAMP NULL,
                INDEX `user_id` (`user_id`),
                INDEX `expires_at` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
            $conn->query($createTableSql);
            
            // Insert or update password reset request
            $insert_stmt = $conn->prepare("INSERT INTO password_resets (user_id, email, reset_token, reset_otp) VALUES (?, ?, ?, ?) 
                                          ON DUPLICATE KEY UPDATE reset_token = VALUES(reset_token), reset_otp = VALUES(reset_otp), attempts = 0, created_at = CURRENT_TIMESTAMP, expires_at = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 30 MINUTE), verified_at = NULL");
            $insert_stmt->bind_param("isss", $user_id, $email, $reset_token, $reset_otp);
            
            if ($insert_stmt->execute()) {
                // Send reset email with OTP
                $reset_link = "http://localhost/Esty-main/ESTY/reset_password.php?token=" . $reset_token;
                
                $subject = "Password Reset Request - Esty Scents";
                $email_body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; background: #f5e8d0; }
                        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
                        .header { text-align: center; color: #ff4081; }
                        .content { text-align: center; padding: 20px 0; }
                        .otp-box { background: #f5e8d0; padding: 15px; border-radius: 5px; font-size: 32px; font-weight: bold; letter-spacing: 2px; color: #4B3F2F; margin: 20px 0; }
                        .button { display: inline-block; background: #ff4081; color: white; padding: 12px 30px; border-radius: 25px; text-decoration: none; margin: 10px 0; }
                        .footer { text-align: center; font-size: 12px; color: #666; margin-top: 20px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2 class='header'>Password Reset Request</h2>
                        <div class='content'>
                            <p>Hello <strong>{$username}</strong>,</p>
                            <p>We received a request to reset your password. If you didn't request this, please ignore this email.</p>
                            <p>Your password reset code is:</p>
                            <div class='otp-box'>{$reset_otp}</div>
                            <p>Or click the link below to reset your password:</p>
                            <a href='{$reset_link}' class='button'>Reset Password</a>
                            <p style='color: #666; font-size: 12px; margin-top: 20px;'>This link will expire in 30 minutes.</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2025 Esty Scents. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
                ";
                
                // Log OTP for debug mode
                if (DEBUG_MODE) {
                    $log_entry = date('Y-m-d H:i:s') . " | Email: $email | Username: $username | RESET OTP: $reset_otp | Token: $reset_token\n";
                    $debug_file = __DIR__ . '/logs/password_reset_debug.log';
                    if (!is_dir(__DIR__ . '/logs')) {
                        mkdir(__DIR__ . '/logs', 0755, true);
                    }
                    file_put_contents($debug_file, $log_entry, FILE_APPEND);
                }
                
                // Send email
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
                $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">" . "\r\n";
                mail($email, $subject, $email_body, $headers);
                
                $_SESSION['password_reset_email'] = $email;
                $_SESSION['password_reset_token'] = $reset_token;
                
                // Return JSON response for AJAX
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Password reset instructions have been sent to your email.'
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Error processing password reset. Please try again.'
                ]);
            }
            $insert_stmt->close();
        } else {
            // Email not found
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'No account found with this email address.'
            ]);
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password – Esty Scents</title>
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

    .btn-reset {
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

    .btn-reset:hover {
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
      <h1><i class="bi bi-key"></i> Forgot Password</h1>
      <p>Don't worry! We'll help you recover your account</p>
    </div>

    <div class="auth-body">
      <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show" role="alert">
          <i class="bi bi-<?= $message_type === 'success' ? 'check-circle' : ($message_type === 'danger' ? 'exclamation-circle' : 'info-circle') ?>"></i>
          <?= htmlspecialchars($message) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" 
                 name="email" 
                 id="email"
                 class="form-control" 
                 placeholder="your@email.com" 
                 required
                 autofocus>
          <small class="d-block mt-2 text-muted">Enter the email associated with your account</small>
        </div>

        <button type="submit" class="btn-reset">Send Reset Instructions</button>
      </form>
    </div>

    <div class="auth-footer">
      <p>Remember your password? <a href="login.php">Login here</a></p>
      <p style="margin-top: 10px;">Don't have an account? <a href="register.php">Create one</a></p>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
