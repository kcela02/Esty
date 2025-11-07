<?php
session_start();
require 'db.php';
require_once __DIR__ . '/user_activity_helpers.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';
$username = '';
$email = '';

// Fetch current user data
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

if (!$user_data) {
    header("Location: account.php");
    exit;
}

// Set initial values
$username = $user_data['username'];
$email = $user_data['email'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');
    error_log("edit_account.php: POST received with action: '$action'");
    
    if ($action === 'update_profile') {
        $new_username = trim($_POST['username'] ?? '');
        $new_email = trim($_POST['email'] ?? '');
        
        // Validation
        if (empty($new_username)) {
            $message = '❌ Username cannot be empty';
            $message_type = 'danger';
        } elseif (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $message = '❌ Valid email is required';
            $message_type = 'danger';
        } else {
            // Check if username/email already exists (except for current user)
            $check = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $check->bind_param("ssi", $new_username, $new_email, $user_id);
            $check->execute();
            $check_result = $check->get_result();
            
            if ($check_result->num_rows > 0) {
                $message = '❌ Username or email already in use';
                $message_type = 'danger';
            } else {
                // Update profile
                $update = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $update->bind_param("ssi", $new_username, $new_email, $user_id);
                
                if ($update->execute()) {
                    $_SESSION['username'] = $new_username;
                    $username = $new_username;
                    $email = $new_email;
                    $message = '✅ Profile updated successfully!';
                    $message_type = 'success';
                    
                    // Log activity
                    if (function_exists('logUserActivity')) {
                        logUserActivity($conn, $user_id, 'profile_updated', "Profile updated");
                    }
                } else {
                    $message = '❌ Failed to update profile';
                    $message_type = 'danger';
                }
                $update->close();
            }
            $check->close();
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($current_password)) {
            $message = '❌ Current password is required';
            $message_type = 'danger';
        } elseif (empty($new_password) || strlen($new_password) < 8) {
            $message = '❌ New password must be at least 8 characters';
            $message_type = 'danger';
        } elseif ($new_password !== $confirm_password) {
            $message = '❌ Passwords do not match';
            $message_type = 'danger';
        } else {
            // Verify current password
            $pwd_stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $pwd_stmt->bind_param("i", $user_id);
            $pwd_stmt->execute();
            $pwd_result = $pwd_stmt->get_result();
            $pwd_row = $pwd_result->fetch_assoc();
            $pwd_stmt->close();
            
            if (!$pwd_row || !password_verify($current_password, $pwd_row['password'])) {
                $message = '❌ Current password is incorrect';
                $message_type = 'danger';
            } else {
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $pwd_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $pwd_update->bind_param("si", $hashed_password, $user_id);
                
                if ($pwd_update->execute()) {
                    $message = '✅ Password changed successfully!';
                    $message_type = 'success';
                    
                    // Log activity
                    if (function_exists('logUserActivity')) {
                        logUserActivity($conn, $user_id, 'password_changed', 'Password was changed');
                    }
                } else {
                    $message = '❌ Failed to change password';
                    $message_type = 'danger';
                }
                $pwd_update->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account - Esty Scents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            padding-top: 80px;
            background-color: #F5E8D0;
        }

        .edit-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
            border: none;
        }

        .form-card h3 {
            color: #4B3F2F;
            margin-bottom: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-card h3 i {
            color: #ff4081;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem !important;
            display: block !important;
        }

        .form-label {
            font-weight: 600;
            color: #4B3F2F;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-label .icon {
            color: #ff4081;
            font-size: 0.9rem;
        }

        .form-control::before,
        .form-control::after {
            display: none !important;
        }

        /* Hide browser's native password reveal button on all browsers */
        input[type="password"]::-webkit-outer-spin-button,
        input[type="password"]::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
        }

        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
        }

        /* For Edge and other browsers - hide password manager icons */
        input:-webkit-autofill::after,
        input:-webkit-autofill::before {
            display: none !important;
        }

        .form-control {
            border: 1.5px solid #e0e0e0 !important;
            border-radius: 8px !important;
            padding: 0.75rem 1rem !important;
            font-size: 1rem !important;
            transition: all 0.3s ease !important;
            background-color: #fff !important;
            color: #333 !important;
            display: block !important;
            width: 100% !important;
            min-height: 44px !important;
            padding-right: 45px !important;
            background-image: none !important;
        }

        .form-control:focus {
            border-color: #ff4081;
            box-shadow: 0 0 0 0.2rem rgba(255, 64, 129, 0.15);
            outline: none;
        }

        .form-control::placeholder {
            color: #aaa;
        }

        .btn-submit {
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
            color: white;
            transform: translateY(-2px);
        }

        .password-strength {
            margin-top: 0.5rem;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 0.9rem;
            display: none;
        }

        .password-strength.weak {
            background-color: #f8d7da;
            color: #721c24;
            display: block;
        }

        .password-strength.medium {
            background-color: #fff3cd;
            color: #856404;
            display: block;
        }

        .password-strength.strong {
            background-color: #d4edda;
            color: #155724;
            display: block;
        }

        .input-icon {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            background: none;
            border: none;
            padding: 6px 8px;
            z-index: 10;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
        }

        .toggle-password:hover {
            color: #ff4081;
        }

        .toggle-password:focus {
            outline: none;
        }

        .toggle-password i {
            pointer-events: none;
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 2rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .section-divider {
            border-top: 2px solid #f0f0f0;
            margin: 3rem 0;
        }

        .help-text {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container edit-container my-5 pt-3">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="account.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-2"></i> Back to Account
        </a>
    </div>

    <!-- Messages -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($message_type); ?> d-flex align-items-center" role="alert">
            <i class="bi bi-<?= ($message_type === 'success' ? 'check-circle' : 'exclamation-circle'); ?> me-2 fs-5"></i>
            <?= htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Update Profile Section -->
    <div class="form-card">
        <h3>
            <i class="bi bi-person-circle"></i>
            Update Profile
        </h3>
        
        <form method="POST" action="" class="needs-validation">
            <input type="hidden" name="action" value="update_profile">

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" 
                       id="username"
                       name="username" 
                       class="form-control" 
                       value="<?= htmlspecialchars($username); ?>"
                       required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" 
                       id="email"
                       name="email" 
                       class="form-control" 
                       value="<?= htmlspecialchars($email); ?>"
                       required>
            </div>

            <button type="submit" class="btn btn-submit btn-success w-100">
                Save Changes
            </button>
        </form>
    </div>

    <!-- Change Password Section -->
    <div class="form-card">
        <h3>
            <i class="bi bi-shield-lock"></i>
            Change Password
        </h3>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="change_password">

            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <div style="position: relative;">
                    <input type="password" 
                           id="current_password"
                           name="current_password" 
                           class="form-control" 
                           placeholder="Enter your current password"
                           required>
                    <button type="button" class="toggle-password" onclick="togglePassword(this, 'current_password')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <div style="position: relative;">
                    <input type="password" 
                           id="new_password"
                           name="new_password" 
                           class="form-control" 
                           placeholder="At least 8 characters"
                           oninput="checkPasswordStrength(this.value)"
                           required>
                    <button type="button" class="toggle-password" onclick="togglePassword(this, 'new_password')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div id="strengthIndicator" class="password-strength"></div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div style="position: relative;">
                    <input type="password" 
                           id="confirm_password"
                           name="confirm_password" 
                           class="form-control" 
                           placeholder="Re-enter your new password"
                           required>
                    <button type="button" class="toggle-password" onclick="togglePassword(this, 'confirm_password')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-submit btn-primary w-100">
                Change Password
            </button>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="form-card" style="border-left: 4px solid #dc3545;">
        <h3 style="color: #dc3545;">
            <i class="bi bi-exclamation-triangle" style="color: #dc3545;"></i>
            Account Settings
        </h3>
        
        <p class="text-muted mb-3">
            Need to logout? Click the button below.
        </p>

        <a href="logout.php" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to logout?');">
            Logout
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function togglePassword(button, inputId) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function checkPasswordStrength(password) {
    const indicator = document.getElementById('strengthIndicator');
    const strength = {
        weak: /^.{8,}$/,
        medium: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/,
        strong: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/
    };
    
    indicator.className = 'password-strength';
    
    if (!password) {
        indicator.style.display = 'none';
        return;
    }
    
    if (strength.strong.test(password)) {
        indicator.textContent = '✅ Strong password';
        indicator.classList.add('strong');
    } else if (strength.medium.test(password)) {
        indicator.textContent = '⚠️ Medium strength (consider adding symbols)';
        indicator.classList.add('medium');
    } else if (strength.weak.test(password)) {
        indicator.textContent = '⚠️ Weak (add uppercase, numbers, and symbols)';
        indicator.classList.add('weak');
    } else {
        indicator.textContent = '❌ Too short (minimum 8 characters)';
        indicator.classList.add('weak');
    }
}
</script>

</body>
</html>
