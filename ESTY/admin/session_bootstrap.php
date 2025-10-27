<?php

// Increase session timeout to 12 hours (43200 seconds)
ini_set('session.gc_maxlifetime', 43200);

$cookieParams = session_get_cookie_params();
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_set_cookie_params([
    'lifetime' => 43200, // 12 hours
    'path' => $cookieParams['path'] ?? '/',
    'domain' => $cookieParams['domain'] ?? '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Strict',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Activity-based session refresh - refresh session timeout on every page load
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Update session cookie expiration on every request (extends timeout)
    setcookie(
        session_name(),
        session_id(),
        [
            'expires' => time() + 43200, // Extend by 12 hours
            'path' => session_get_cookie_params()['path'],
            'domain' => session_get_cookie_params()['domain'],
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Strict',
        ]
    );
}

function requireAdminLogin(): void
{
    if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin'])) {
        header('Location: login.php');
        exit;
    }
}

