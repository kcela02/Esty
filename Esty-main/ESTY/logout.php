<?php
session_start();

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

if ($userId) {
	require 'db.php';
	require_once __DIR__ . '/user_activity_helpers.php';
	logUserActivity($conn, $userId, 'logout', 'User signed out.');
}

session_unset();
session_destroy();
header("Location: index.php");
exit;
