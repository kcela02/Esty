<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once '../db.php';
require_once __DIR__ . '/admin_helpers.php';
requireAdminLogin();

<?php
require_once __DIR__ . '/session_bootstrap.php';
requireAdminLogin();

$_SESSION['admin_users_flash'] = [
    'type' => 'info',
    'message' => 'Account settings have moved. Edit your profile from Admin User Management.'
];

header('Location: admin_users.php');
exit;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
