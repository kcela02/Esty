<?php
// Quick test to see if reset_password.php loads without errors
session_start();
require 'db.php';
require 'config_email.php';

$token = $_GET['token'] ?? 'test-token';
file_put_contents(__DIR__ . '/logs/otp_debug.log', date('Y-m-d H:i:s') . " | test_reset.php - Token: $token\n", FILE_APPEND);

// Check if password_resets table exists and has data
$result = $conn->query("SELECT * FROM password_resets LIMIT 1");
if ($result) {
    file_put_contents(__DIR__ . '/logs/otp_debug.log', date('Y-m-d H:i:s') . " | password_resets table exists, rows: " . $result->num_rows . "\n", FILE_APPEND);
} else {
    file_put_contents(__DIR__ . '/logs/otp_debug.log', date('Y-m-d H:i:s') . " | password_resets table error: " . $conn->error . "\n", FILE_APPEND);
}

echo "Test completed. Check logs.";
?>
