<?php
session_start();
require 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        $message = "All fields are required.";
    } elseif (strlen($username) < 3) {
        $message = "Username must be at least 3 characters.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Check if email/username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = "Username or Email already taken.";
        } else {
            // Insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            if ($stmt->execute()) {
                // Get inserted user id
                $new_user_id = $stmt->insert_id;

                // Auto login
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['username'] = $username;

                // Redirect to homepage as logged in
                header("Location: index.php");
                exit;
            } else {
                $message = "Error: Could not register user.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register – Esty Scents</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --primary: #e75480;
      --secondary: #ffb6c1;
      --dark: #4B3F2F;
      --light: #fffafc;
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
      padding: 40px 30px;
      text-align: center;
      color: white;
    }
    
    .auth-header h1 {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
    }
    
    .auth-header i {
      font-size: 32px;
    }
    
    .auth-header p {
      font-size: 14px;
      opacity: 0.95;
      margin: 0;
    }
    
    .auth-body {
      padding: 40px 30px;
    }
    
    .form-group {
      margin-bottom: 24px;
    }
    
    .form-group label {
      font-weight: 600;
      color: var(--dark);
      font-size: 14px;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 6px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .form-group .form-hint {
      font-size: 12px;
      color: #999;
      font-weight: 400;
      text-transform: none;
      letter-spacing: 0;
      margin-left: auto;
    }
    
    .form-control {
      border: 2px solid #f0f0f0;
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 15px;
      transition: all 0.3s ease;
      background: #fafafa;
    }
    
    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(231, 84, 128, 0.1);
      background: white;
      outline: none;
    }
    
    .form-control::placeholder {
      color: #999;
    }
    
    .form-control.error {
      border-color: #dc3545;
      background: rgba(220, 53, 69, 0.05);
    }
    
    .password-strength {
      display: none;
      margin-top: 8px;
      height: 4px;
      border-radius: 2px;
      background: #f0f0f0;
      overflow: hidden;
    }
    
    .password-strength.show {
      display: block;
    }
    
    .password-strength-meter {
      height: 100%;
      width: 0;
      transition: all 0.3s ease;
    }
    
    .password-strength-meter.weak {
      width: 33%;
      background: #dc3545;
    }
    
    .password-strength-meter.fair {
      width: 66%;
      background: #ffc107;
    }
    
    .password-strength-meter.strong {
      width: 100%;
      background: #28a745;
    }
    
    .alert-error {
      background: rgba(220, 53, 69, 0.1);
      border: 1px solid rgba(220, 53, 69, 0.2);
      border-radius: 12px;
      padding: 14px 16px;
      margin-bottom: 24px;
      color: #c41c3b;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .alert-error i {
      font-size: 18px;
      flex-shrink: 0;
    }
    
    .submit-btn {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, var(--primary) 0%, #e74a7a 100%);
      border: none;
      border-radius: 12px;
      color: white;
      font-weight: 600;
      font-size: 15px;
      cursor: pointer;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 4px 15px rgba(231, 84, 128, 0.3);
    }
    
    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(231, 84, 128, 0.4);
    }
    
    .submit-btn:active {
      transform: translateY(0);
    }
    
    .auth-footer {
      padding: 0 30px 30px;
      text-align: center;
    }
    
    .auth-footer p {
      font-size: 14px;
      color: #666;
      margin: 0;
    }
    
    .auth-footer a {
      color: var(--primary);
      font-weight: 600;
      text-decoration: none;
      transition: color 0.3s ease;
    }
    
    .auth-footer a:hover {
      color: #d93a6a;
      text-decoration: underline;
    }
    
    @media (max-width: 480px) {
      .auth-body {
        padding: 30px 20px;
      }
      
      .auth-footer {
        padding: 0 20px 20px;
      }
      
      .auth-header {
        padding: 30px 20px;
      }
      
      .auth-header h1 {
        font-size: 24px;
      }
    }
  </style>
</head>
<body>

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-header">
      <h1><i class="bi bi-shop"></i> Esty Scents</h1>
      <p>Join our scent community</p>
    </div>
    
    <div class="auth-body">
      <?php if ($message): ?>
        <div class="alert-error">
          <i class="bi bi-exclamation-circle-fill"></i>
          <span><?= htmlspecialchars($message); ?></span>
        </div>
      <?php endif; ?>
      
      <form method="POST" id="registerForm">
        <div class="form-group">
          <label for="username">
            <i class="bi bi-person"></i> Username
            <span class="form-hint" id="usernameHint">3+ characters</span>
          </label>
          <input 
            type="text" 
            id="username"
            name="username" 
            class="form-control" 
            placeholder="Choose a unique username"
            minlength="3"
            maxlength="32"
            required
            autofocus
          >
        </div>
        
        <div class="form-group">
          <label for="email">
            <i class="bi bi-envelope"></i> Email Address
          </label>
          <input 
            type="email" 
            id="email"
            name="email" 
            class="form-control" 
            placeholder="your.email@example.com"
            required
          >
        </div>
        
        <div class="form-group">
          <label for="password">
            <i class="bi bi-key"></i> Password
            <span class="form-hint" id="passwordHint">6+ characters</span>
          </label>
          <input 
            type="password" 
            id="password"
            name="password" 
            class="form-control" 
            placeholder="Create a strong password"
            minlength="6"
            required
          >
          <div class="password-strength" id="passwordStrength">
            <div class="password-strength-meter"></div>
          </div>
        </div>
        
        <div class="form-group">
          <label for="confirm_password">
            <i class="bi bi-check-circle"></i> Confirm Password
          </label>
          <input 
            type="password" 
            id="confirm_password"
            name="confirm_password" 
            class="form-control" 
            placeholder="Re-enter your password"
            required
          >
        </div>
        
        <button type="submit" class="submit-btn">
          <i class="bi bi-person-plus"></i> Create Account
        </button>
      </form>
    </div>
    
    <div class="auth-footer">
      <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
  </div>
</div>

<script>
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('confirm_password');
const strengthMeter = document.getElementById('passwordStrength');
const strengthBar = strengthMeter.querySelector('.password-strength-meter');
const form = document.getElementById('registerForm');

// Password strength checker
function checkPasswordStrength(password) {
  if (password.length === 0) {
    strengthMeter.classList.remove('show');
    return;
  }
  
  strengthMeter.classList.add('show');
  
  let strength = 0;
  
  if (password.length >= 6) strength++;
  if (password.length >= 10) strength++;
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
  if (/\d/.test(password)) strength++;
  if (/[^a-zA-Z\d]/.test(password)) strength++;
  
  strengthBar.classList.remove('weak', 'fair', 'strong');
  
  if (strength <= 2) {
    strengthBar.classList.add('weak');
  } else if (strength <= 3) {
    strengthBar.classList.add('fair');
  } else {
    strengthBar.classList.add('strong');
  }
}

// Real-time validation
passwordInput.addEventListener('input', function() {
  checkPasswordStrength(this.value);
});

confirmInput.addEventListener('input', function() {
  if (this.value && passwordInput.value !== this.value) {
    this.classList.add('error');
  } else {
    this.classList.remove('error');
  }
});

// Form submission validation
form.addEventListener('submit', function(e) {
  const username = document.getElementById('username').value.trim();
  const email = document.getElementById('email').value.trim();
  const password = passwordInput.value;
  const confirmPassword = confirmInput.value;
  
  if (!username || !email || !password || !confirmPassword) {
    e.preventDefault();
    document.querySelector('.alert-error')?.remove();
    const error = document.createElement('div');
    error.className = 'alert-error';
    error.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> <span>Please fill in all fields.</span>';
    document.querySelector('.auth-body').insertBefore(error, document.querySelector('form'));
  }
  
  if (password !== confirmPassword) {
    e.preventDefault();
    document.querySelector('.alert-error')?.remove();
    const error = document.createElement('div');
    error.className = 'alert-error';
    error.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> <span>Passwords do not match.</span>';
    document.querySelector('.auth-body').insertBefore(error, document.querySelector('form'));
  }
});
</script>

</body>
</html>
