<?php
// Test SMTP connection
require 'config_email.php';

echo "<h2>SMTP Connection Test</h2>";
echo "<p>This will test if PHPMailer can connect to SMTP using your credentials.</p>";

global $PHPMailerAvailable, $mailSettings;

if (!$PHPMailerAvailable) {
    echo "<p style='color:red;'>ERROR: PHPMailer not available</p>";
    exit;
}

if (!is_array($mailSettings)) {
    echo "<p style='color:red;'>ERROR: mail_settings.php not loaded</p>";
    exit;
}

echo "<pre>";
echo "Provider: " . ($mailSettings['provider'] ?? 'N/A') . "\n";
echo "Host: " . ($mailSettings['host'][$mailSettings['provider']] ?? 'N/A') . "\n";
echo "Port: " . ($mailSettings['port'][$mailSettings['provider']] ?? 'N/A') . "\n";
echo "Username: " . ($mailSettings['username'] ?? 'N/A') . "\n";
echo "Password: " . str_repeat('*', strlen($mailSettings['password'] ?? '')) . " (" . strlen($mailSettings['password'] ?? 0) . " chars)\n";
echo "</pre>";

use PHPMailer\PHPMailer\PHPMailer;

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $mailSettings['host'][$mailSettings['provider']];
    $mail->Port = $mailSettings['port'][$mailSettings['provider']];
    $mail->SMTPAuth = true;
    $mail->Username = $mailSettings['username'];
    $mail->Password = $mailSettings['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    
    // Try to connect
    if ($mail->smtpConnect()) {
        echo "<p style='color:green;'><strong>✓ SMTP Connection Successful!</strong></p>";
        $mail->smtpClose();
    } else {
        echo "<p style='color:red;'>SMTP Connection Failed</p>";
    }
} catch (\Throwable $e) {
    echo "<p style='color:red;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
