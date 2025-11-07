<?php
// SMTP settings for PHPMailer. Fill in your provider credentials.
// Safe defaults are provided; update values per account.

return [
    // 'gmail' or 'outlook' (or 'custom')
    'provider' => 'gmail',

    // Common fields for all providers
    'host' => [
        'gmail' => 'smtp.gmail.com',
        'outlook' => 'smtp.office365.com',
        'custom' => 'smtp.example.com',
    ],
    'port' => [
        'gmail' => 587,
        'outlook' => 587,
        'custom' => 587,
    ],
    'encryption' => [
        'gmail' => 'tls',
        'outlook' => 'tls',
        'custom' => 'tls', // or 'ssl' (465) as needed
    ],

    // Sender identity
    'from_email' => 'estyscents@gmail.com',
    'from_name'  => 'Esty Scents',

    // SMTP auth credentials
    // For Gmail: use an App Password (not your normal password)
    // For Outlook/Office365: use your account password or app password if MFA is on
    'username'   => 'estyscents@gmail.com',
    'password'   => 'dictczdbijuxrjym',
];
