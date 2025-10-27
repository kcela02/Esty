<?php
session_start();
require 'db.php';
require 'config_email.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $username, $hashed_password);
        $stmt->fetch();
        
        if (password_verify($password, $hashed_password)) {
            // Generate OTP for login verification
            $otp = generateOtp();
            
            // Create login_otp_verifications table if not exists
            $createTableSql = "CREATE TABLE IF NOT EXISTS `login_otp_verifications` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `email` VARCHAR(100) NOT NULL,
                `otp` VARCHAR(6) NOT NULL,
                `attempts` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `expires_at` TIMESTAMP DEFAULT (DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 10 MINUTE)),
                UNIQUE KEY `user_id` (`user_id`),
                INDEX `expires_at` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
            $conn->query($createTableSql);
            
            // Store OTP temporarily
            $otp_stmt = $conn->prepare("INSERT INTO login_otp_verifications (user_id, email, otp) VALUES (?, ?, ?) 
                                        ON DUPLICATE KEY UPDATE otp = VALUES(otp), attempts = 0, created_at = CURRENT_TIMESTAMP, expires_at = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 10 MINUTE)");
            $otp_stmt->bind_param("iss", $id, $email, $otp);
            
            if ($otp_stmt->execute()) {
                // Send OTP via email
                if (sendOtpEmail($email, $otp, $username, 'login')) {
                    $_SESSION['login_user_id'] = $id;
                    $_SESSION['login_email'] = $email;
                    $_SESSION['login_username'] = $username;
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Verification code sent to your email',
                        'redirect' => 'verify_login_otp.php'
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error sending verification code. Please try again.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error processing login. Please try again.']);
            }
            $otp_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No account found with that email']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
