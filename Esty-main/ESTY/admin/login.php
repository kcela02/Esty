<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once "../db.php";
require_once __DIR__ . '/admin_helpers.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);

  $stmt = $conn->prepare("SELECT id, username, password FROM admin_users WHERE username = ? LIMIT 1");
  if ($stmt) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result ? $result->fetch_assoc() : null;
    $stmt->close();
  } else {
    $admin = null;
  }

  if ($admin) {
    $storedHash = $admin['password'];
    $loginOk = false;

    if (password_verify($password, $storedHash)) {
      $loginOk = true;

      if (password_needs_rehash($storedHash, PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
        if ($updateStmt) {
          $updateStmt->bind_param("si", $newHash, $admin['id']);
          $updateStmt->execute();
          $updateStmt->close();
        }
      }
    } elseif (strlen($storedHash) === 32 && ctype_xdigit($storedHash) && hash_equals(strtolower($storedHash), md5($password))) {
      $loginOk = true;
      $newHash = password_hash($password, PASSWORD_DEFAULT);
      $upgradeStmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
      if ($upgradeStmt) {
        $upgradeStmt->bind_param("si", $newHash, $admin['id']);
        $upgradeStmt->execute();
        $upgradeStmt->close();
      }
    }

    if ($loginOk) {
      session_regenerate_id(true);
      $_SESSION['admin'] = $admin['username'];
      $_SESSION['admin_logged_in'] = true;
      // Log admin login
      logAdminActivity($conn, $admin['username'], 'Login');
      header("Location: dashboard.php");
      exit;
    }
  }

  $error = "Invalid username or password!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="d-flex justify-content-center align-items-center" style="min-height: 100vh; background: linear-gradient(120deg, #ffb6c1 0%, #ffe6ec 100%);">
  <div class="card shadow p-4" style="width: 370px; border-radius: 22px;">
    <div class="text-center mb-3">
      <img src="../images/logo.jpg" alt="Esty Admin" style="width: 60px; border-radius: 50%; box-shadow: 0 2px 8px #ffb6c1;">
      <h3 class="fw-bold mt-2 mb-0" style="color:#e75480;">Admin Login</h3>
      <p class="muted mb-0">Sign in to manage your store</p>
    </div>
    <?php if ($error): ?>
      <div class="alert alert-danger text-center py-2 mb-3"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control form-control-lg" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control form-control-lg" required>
      </div>
      <button type="submit" class="btn btn-add w-100 py-2">Login</button>
    </form>
  </div>
</div>

</body>
</html>
