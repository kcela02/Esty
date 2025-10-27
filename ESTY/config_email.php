<?php
// Email Configuration for OTP sending
// Using built-in PHP mail() function (can be upgraded to PHPMailer later)

define('MAIL_FROM', 'noreply@estyscents.com');
define('MAIL_FROM_NAME', 'Esty Scents');
define('OTP_EXPIRY_MINUTES', 10); // OTP valid for 10 minutes
define('OTP_LENGTH', 6); // 6-digit OTP

// For development: Set to true to skip actual email sending
define('DEBUG_MODE', true);
define('DEBUG_LOG_FILE', __DIR__ . '/logs/otp_debug.log');

// Ensure logs directory exists
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

/**
 * Send OTP via email using PHP mail()
 * In DEBUG_MODE, logs the OTP instead of sending
 * 
 * @param string $to Email address
 * @param string $otp The OTP code
 * @param string $username User's username
 * @param string $context Type of verification (verification or login)
 * @return bool
 */
function sendOtpEmail($to, $otp, $username, $context = 'verification') {
    $subject = "Your Esty Scents " . ($context === 'login' ? 'Login' : 'Verification') . " Code";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background: #f5e8d0; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
            .header { text-align: center; color: #ff4081; }
            .content { text-align: center; padding: 20px 0; }
            .otp-box { background: #f5e8d0; padding: 15px; border-radius: 5px; font-size: 32px; font-weight: bold; letter-spacing: 2px; color: #4B3F2F; }
            .footer { text-align: center; font-size: 12px; color: #666; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2 class='header'>Esty Scents</h2>
            <div class='content'>
                <p>Hello <strong>{$username}</strong>,</p>
                <p>Your " . ($context === 'login' ? 'login' : 'account verification') . " code is:</p>
                <div class='otp-box'>{$otp}</div>
                <p>This code will expire in " . OTP_EXPIRY_MINUTES . " minutes.</p>
                <p style='color: #666; font-size: 12px;'>If you didn't request this, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>&copy; 2025 Esty Scents. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    if (DEBUG_MODE) {
        // In debug mode, log the OTP instead of sending email
        $log_entry = date('Y-m-d H:i:s') . " | Email: $to | Username: $username | OTP: $otp | Context: $context\n";
        file_put_contents(DEBUG_LOG_FILE, $log_entry, FILE_APPEND);
        return true;
    }
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Generate a random OTP
 * 
 * @param int $length OTP length
 * @return string
 */
function generateOtp($length = OTP_LENGTH) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Get the OTP from debug log for testing
 * 
 * @param string $email
 * @return string|null
 */
function getDebugOtp($email) {
    if (!file_exists(DEBUG_LOG_FILE)) {
        return null;
    }
    
    $lines = array_reverse(file(DEBUG_LOG_FILE));
    foreach ($lines as $line) {
        if (strpos($line, $email) !== false) {
            preg_match('/OTP: (\d{6})/', $line, $matches);
            if (isset($matches[1])) {
                return $matches[1];
            }
        }
    }
    return null;
}
?>

