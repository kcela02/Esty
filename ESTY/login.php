<?php
session_start();
require 'db.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $username, $hashed_password);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;

            $_SESSION['cart'] = [];

            $stmt = $conn->prepare(
                "
                SELECT c.product_id, c.quantity, p.name, p.price
                FROM carts c
                JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ?
                "
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $_SESSION['cart'][] = [
                    'id' => $row['product_id'],
                    'name' => $row['name'],
                    'price' => $row['price'],
                    'quantity' => $row['quantity']
                ];
            }

            header("Location: index.php");
            exit;
        } else {
            $message = "Invalid password.";
        }
    } else {
        $message = "No account found with that email.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login – Esty Scents</title>
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
      box-shadow: 0 25px 55px rgba(60, 51, 39, 0.22);
      overflow: hidden;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
    }

    .auth-header {
      background: linear-gradient(135deg, rgba(201, 166, 70, 0.95) 0%, rgba(231, 207, 146, 0.88) 100%);
      padding: 42px 36px;
      text-align: center;
      color: #fefcf7;
      position: relative;
    }

    .auth-header::after {
      content: "";
      position: absolute;
      inset: 12px;
      border: 1px solid rgba(255, 255, 255, 0.32);
      border-radius: 20px;
      opacity: 0.65;
    }

    .auth-header h1 {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      letter-spacing: 0.6px;
      position: relative;
      z-index: 1;
    }

    .auth-header i {
      font-size: 32px;
    }

    .auth-header p {
      font-size: 15px;
      margin: 0;
      letter-spacing: 0.3px;
      opacity: 0.95;
      position: relative;
      z-index: 1;
    }

    .auth-body {
      padding: 42px 36px 36px;
    }

    .form-group {
      margin-bottom: 26px;
    }

    .form-group label {
      font-weight: 600;
      color: var(--charcoal);
      font-size: 13px;
      margin-bottom: 10px;
      display: block;
      text-transform: uppercase;
      letter-spacing: 0.55px;
    }

    .form-group label i {
      color: var(--primary);
      margin-right: 6px;
    }

    .form-control {
      border: 1px solid rgba(201, 166, 70, 0.22);
      border-radius: 14px;
      padding: 13px 16px;
      font-size: 15px;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.92);
      color: var(--charcoal);
      box-shadow: inset 0 2px 6px rgba(60, 51, 39, 0.04);
    }

    .form-control:focus {
      border-color: rgba(201, 166, 70, 0.75);
      box-shadow: 0 0 0 4px rgba(201, 166, 70, 0.18);
      background: #fff;
      outline: none;
    }

    .form-control::placeholder {
      color: rgba(111, 102, 91, 0.55);
    }

    .alert-error {
      background: rgba(148, 61, 32, 0.08);
      border: 1px solid rgba(148, 61, 32, 0.18);
      border-radius: 14px;
      padding: 14px 18px;
      margin-bottom: 26px;
      color: #8b3d24;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 12px;
      box-shadow: 0 8px 18px rgba(139, 61, 36, 0.08);
    }

    .alert-error i {
      font-size: 18px;
      flex-shrink: 0;
    }

    .submit-btn {
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, rgba(201, 166, 70, 0.95) 0%, rgba(159, 130, 58, 0.92) 100%);
      border: none;
      border-radius: 16px;
      color: #fff8ea;
      font-weight: 600;
      font-size: 15px;
      cursor: pointer;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      box-shadow: 0 15px 35px rgba(60, 51, 39, 0.25);
      position: relative;
      overflow: hidden;
    }

    .submit-btn::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.32), rgba(255, 255, 255, 0));
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 18px 36px rgba(60, 51, 39, 0.28);
    }

    .submit-btn:hover::after {
      opacity: 1;
    }

    .submit-btn:active {
      transform: translateY(0);
    }

    .auth-footer {
      padding: 0 36px 38px;
      text-align: center;
    }

    .auth-footer p {
      font-size: 14px;
      color: var(--taupe);
      margin: 0;
      letter-spacing: 0.3px;
    }

    .auth-footer a {
      color: var(--primary-dark);
      font-weight: 600;
      text-decoration: none;
      position: relative;
    }

    .auth-footer a::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: -3px;
      width: 100%;
      height: 1px;
      background: rgba(201, 166, 70, 0.45);
      transform: scaleX(0);
      transform-origin: left;
      transition: transform 0.3s ease;
    }

    .auth-footer a:hover::after {
      transform: scaleX(1);
    }

    @media (max-width: 480px) {
      body {
        padding: 24px 14px;
      }

      .auth-card {
        border-radius: 20px;
      }

      .auth-header {
        padding: 32px 24px;
      }

      .auth-header h1 {
        font-size: 24px;
      }

      .auth-body {
        padding: 32px 24px 28px;
      }

      .auth-footer {
        padding: 0 24px 30px;
      }
    }
  </style>
</head>
<body>

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-header">
      <h1><i class="bi bi-shop"></i> Esty Scents</h1>
      <p>Welcome back to your account</p>
    </div>

    <div class="auth-body">
      <?php if ($message): ?>
        <div class="alert-error">
          <i class="bi bi-exclamation-circle-fill"></i>
          <span><?= htmlspecialchars($message); ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" id="loginForm">
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
            autofocus
          >
        </div>

        <div class="form-group">
          <label for="password">
            <i class="bi bi-key"></i> Password
          </label>
          <input
            type="password"
            id="password"
            name="password"
            class="form-control"
            placeholder="Enter your password"
            required
          >
        </div>

        <button type="submit" class="submit-btn">
          <i class="bi bi-box-arrow-in-right"></i> Login
        </button>
      </form>
    </div>

    <div class="auth-footer">
      <p>Don't have an account? <a href="register.php">Create one now</a></p>
    </div>
  </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;

  if (!email || !password) {
    e.preventDefault();
    document.querySelector('.alert-error')?.remove();
    const error = document.createElement('div');
    error.className = 'alert-error';
    error.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> <span>Please fill in all fields.</span>';
    document.querySelector('.auth-body').insertBefore(error, document.querySelector('form'));
  }
});
</script>

</body>
</html>
