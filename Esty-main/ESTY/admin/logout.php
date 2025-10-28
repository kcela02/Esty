<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once '../db.php';
require_once __DIR__ . '/admin_helpers.php';

// Log activity before clearing session
if (!empty($_SESSION['admin'])) {
	logAdminActivity($conn, $_SESSION['admin'], 'Logout');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], ($params['secure'] ?? false), true);
}

session_destroy();

header("Location: login.php");
exit;
?>
