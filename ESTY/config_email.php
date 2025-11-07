<?php
// Email Configuration for OTP sending using PHPMailer (SMTP) with graceful fallback to mail()

define('MAIL_FROM', 'noreply@estyscents.com');
define('MAIL_FROM_NAME', 'Esty Scents');
define('OTP_EXPIRY_MINUTES', 10); // OTP valid for 10 minutes
define('OTP_LENGTH', 6); // 6-digit OTP

// Toggle debug mode: set to false for real email sending
define('DEBUG_MODE', false);
define('DEBUG_LOG_FILE', __DIR__ . '/logs/otp_debug.log');

if (!function_exists('estyIsDebugMode')) {
    function estyIsDebugMode(): bool {
        return defined('DEBUG_MODE') ? (bool) DEBUG_MODE : false;
    }
}

// Ensure logs directory exists
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Attempt to load PHPMailer via Composer if available
$PHPMailerAvailable = false;
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    $PHPMailerAvailable = class_exists('PHPMailer\\PHPMailer\\PHPMailer');
}

// Load SMTP settings (provider, host, port, encryption, creds)
$mailSettings = file_exists(__DIR__ . '/mail_settings.php') ? require __DIR__ . '/mail_settings.php' : null;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send OTP via email using PHPMailer SMTP if available; otherwise fallback to mail().
 * In DEBUG_MODE, log the OTP instead of sending.
 *
 * @param string $to Email address
 * @param string $otp The OTP code
 * @param string $username User's username
 * @param string $context Type of verification (verification or login)
 * @return bool
 */
function sendOtpEmail($to, $otp, $username, $context = 'verification') {
    $subject = 'Your Esty Scents ' . ($context === 'login' ? 'Login' : 'Verification') . ' Code';
    $message = "<html><head><style>body{font-family:Arial,sans-serif;background:#f5e8d0}.container{max-width:600px;margin:0 auto;background:#fff;padding:20px;border-radius:10px}.header{text-align:center;color:#ff4081}.content{text-align:center;padding:20px 0}.otp-box{background:#f5e8d0;padding:15px;border-radius:5px;font-size:32px;font-weight:bold;letter-spacing:2px;color:#4B3F2F}.footer{text-align:center;font-size:12px;color:#666;margin-top:20px}</style></head><body><div class='container'><h2 class='header'>Esty Scents</h2><div class='content'><p>Hello <strong>".htmlspecialchars($username)."</strong>,</p><p>Your ".($context==='login'?'login':'account verification')." code is:</p><div class='otp-box'>".htmlspecialchars($otp)."</div><p>This code will expire in ".OTP_EXPIRY_MINUTES." minutes.</p><p style='color:#666;font-size:12px;'>If you didn't request this, please ignore this email.</p></div><div class='footer'><p>&copy; ".date('Y')." Esty Scents. All rights reserved.</p></div></div></body></html>";

    if (estyIsDebugMode()) {
        $log_entry = date('Y-m-d H:i:s') . " | Email: $to | Username: $username | OTP: $otp | Context: $context\n";
        file_put_contents(DEBUG_LOG_FILE, $log_entry, FILE_APPEND);
        return true;
    }

    // Prefer PHPMailer if available and settings provided
    global $PHPMailerAvailable, $mailSettings;
    if ($PHPMailerAvailable && is_array($mailSettings)) {
        try {
            $provider = $mailSettings['provider'] ?? 'gmail';
            $host = $mailSettings['host'][$provider] ?? 'smtp.gmail.com';
            $port = $mailSettings['port'][$provider] ?? 587;
            $enc  = $mailSettings['encryption'][$provider] ?? 'tls';
            $fromEmail = $mailSettings['from_email'] ?? MAIL_FROM;
            $fromName  = $mailSettings['from_name'] ?? MAIL_FROM_NAME;
            $username  = $mailSettings['username'] ?? '';
            $password  = $mailSettings['password'] ?? '';

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->Port = (int)$port;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password;
            if (strtolower($enc) === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // 465
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // 587
            }

            $mail->CharSet = 'UTF-8';
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            // Log and fallback to mail()
            file_put_contents(DEBUG_LOG_FILE, date('Y-m-d H:i:s') . ' | PHPMailer error: ' . $e->getMessage() . "\n", FILE_APPEND);
        }
    }

    // Fallback to mail() if PHPMailer not available
    $headers = "MIME-Version: 1.0\r\n" .
               "Content-type: text/html; charset=UTF-8\r\n" .
               'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . ">\r\n";
    return mail($to, $subject, $message, $headers);
}

/**
 * Send arbitrary HTML email using the same PHPMailer SMTP pipeline.
 */
function sendHtmlEmail($to, $subject, $htmlBody, $fromEmail = MAIL_FROM, $fromName = MAIL_FROM_NAME) {
    if (estyIsDebugMode()) {
        // Still send in debug mode for non-OTP emails, but also log
        file_put_contents(DEBUG_LOG_FILE, date('Y-m-d H:i:s') . " | HTML email to $to | Subject: $subject\n", FILE_APPEND);
    }

    global $PHPMailerAvailable, $mailSettings;
    $sent = false;
    
    if ($PHPMailerAvailable && is_array($mailSettings)) {
        try {
            $provider = $mailSettings['provider'] ?? 'gmail';
            $host = $mailSettings['host'][$provider] ?? 'smtp.gmail.com';
            $port = $mailSettings['port'][$provider] ?? 587;
            $enc  = $mailSettings['encryption'][$provider] ?? 'tls';
            $username  = $mailSettings['username'] ?? '';
            $password  = $mailSettings['password'] ?? '';

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->Port = (int)$port;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password;
            $mail->SMTPSecure = (strtolower($enc) === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

            $mail->CharSet = 'UTF-8';
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            
            if ($mail->send()) {
                file_put_contents(DEBUG_LOG_FILE, date('Y-m-d H:i:s') . " | Email sent successfully via SMTP to $to\n", FILE_APPEND);
                $sent = true;
            }
        } catch (\Throwable $e) {
            file_put_contents(DEBUG_LOG_FILE, date('Y-m-d H:i:s') . ' | PHPMailer error (generic): ' . $e->getMessage() . "\n", FILE_APPEND);
            // Try fallback
        }
    }
    
    // If PHPMailer didn't work, try mail()
    if (!$sent) {
        $headers = "MIME-Version: 1.0\r\n" .
                   "Content-type: text/html; charset=UTF-8\r\n" .
                   'From: ' . $fromName . ' <' . $fromEmail . ">\r\n";
        $result = mail($to, $subject, $htmlBody, $headers);
        if ($result) {
            file_put_contents(DEBUG_LOG_FILE, date('Y-m-d H:i:s') . " | Email sent successfully via mail() to $to\n", FILE_APPEND);
            $sent = true;
        } else {
            file_put_contents(DEBUG_LOG_FILE, date('Y-m-d H:i:s') . " | mail() failed to send to $to\n", FILE_APPEND);
        }
    }
    
    return $sent;
}

/** Generate a random OTP */
function generateOtp($length = OTP_LENGTH) {
    return str_pad((string)random_int(0, (int)pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/** Read last logged OTP for given email when in DEBUG_MODE */
function getDebugOtp($email) {
    if (!file_exists(DEBUG_LOG_FILE)) return null;
    $lines = array_reverse(@file(DEBUG_LOG_FILE));
    foreach ($lines as $line) {
        if (strpos($line, $email) !== false && preg_match('/OTP: (\d{6})/', $line, $m)) {
            return $m[1];
        }
    }
    return null;
}
?>

