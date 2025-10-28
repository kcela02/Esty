<?php
session_start();
require 'db.php';
require 'config_email.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if (strlen($username) < 3) {
        echo json_encode(['success' => false, 'message' => 'Username must be at least 3 characters']);
        exit;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        exit;
    }
    
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }

    // Check if email/username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or Email already taken']);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Generate OTP
    $otp = generateOtp();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Create OTP table if not exists
    $createTableSql = "CREATE TABLE IF NOT EXISTS `user_otp_verifications` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `email` VARCHAR(100) NOT NULL,
        `username` VARCHAR(50) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `otp` VARCHAR(6) NOT NULL,
        `attempts` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `expires_at` TIMESTAMP DEFAULT (DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 10 MINUTE)),
        UNIQUE KEY `email` (`email`),
        INDEX `expires_at` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $conn->query($createTableSql);
    
    // Store OTP temporarily
    $stmt = $conn->prepare("INSERT INTO user_otp_verifications (email, username, password, otp) VALUES (?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE otp = VALUES(otp), attempts = 0, created_at = CURRENT_TIMESTAMP, expires_at = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 10 MINUTE)");
    $stmt->bind_param("ssss", $email, $username, $hashed_password, $otp);
    
    if ($stmt->execute()) {
        // Send OTP via email
        if (sendOtpEmail($email, $otp, $username, 'verification')) {
            $_SESSION['otp_email'] = $email;
            $_SESSION['registration_in_progress'] = true;
            
            // For the modal flow don't force the client to navigate to the standalone verify page.
            $response = [
                'success' => true,
                'message' => 'Account created! Please verify your email',
                'redirect' => ''
            ];
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $response['debug_otp'] = getDebugOtp($email);
            }
            echo json_encode($response);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error sending verification email. Please try again.']);
            // Clean up if email fails
            $del_stmt = $conn->prepare("DELETE FROM user_otp_verifications WHERE email = ?");
            $del_stmt->bind_param("s", $email);
            $del_stmt->execute();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error processing registration. Please try again.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
