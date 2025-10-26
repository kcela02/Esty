<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once '../db.php';
require_once __DIR__ . '/admin_helpers.php';
requireAdminLogin();

function redirectBack(): void
{
    header('Location: admin_users.php');
    exit;
}

function setAdminUsersFlash(string $type, string $message): void
{
    $_SESSION['admin_users_flash'] = ['type' => $type, 'message' => $message];
}

if (!isset($_POST['action'])) {
    setAdminUsersFlash('error', 'Invalid action requested.');
    redirectBack();
}

$action = $_POST['action'];

$actingAdminUsername = $_SESSION['admin'] ?? null;
$actingAdminId = null;

if ($actingAdminUsername) {
    if ($stmt = $conn->prepare('SELECT id FROM admin_users WHERE username = ? LIMIT 1')) {
        $stmt->bind_param('s', $actingAdminUsername);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $actingAdminId = $row['id'] ?? null;
        }
        $stmt->close();
    }
}

if ($action === 'add') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        setAdminUsersFlash('error', 'Username and password are required.');
        redirectBack();
    }

    if (strlen($password) < 8) {
        setAdminUsersFlash('error', 'Password must be at least 8 characters long.');
        redirectBack();
    }

    if ($stmt = $conn->prepare('SELECT id FROM admin_users WHERE username = ? LIMIT 1')) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) {
            setAdminUsersFlash('error', 'That username is already in use.');
            redirectBack();
        }
    }

    if ($stmt = $conn->prepare('INSERT INTO admin_users (username, password) VALUES (?, ?)')) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param('ss', $username, $hash);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            setAdminUsersFlash('success', 'Admin user "' . $username . '" added successfully.');
            if ($actingAdminUsername) {
                logAdminActivity($conn, $actingAdminUsername, 'Added admin user', $username);
            }
        } else {
            setAdminUsersFlash('error', 'Failed to add admin user. Please try again.');
        }
    } else {
        setAdminUsersFlash('error', 'Unable to prepare add admin statement.');
    }

    redirectBack();
}

if ($action === 'edit') {
    $id = (int) ($_POST['id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';

    if ($id <= 0) {
        setAdminUsersFlash('error', 'Invalid admin ID.');
        redirectBack();
    }

    if ($username === '') {
        setAdminUsersFlash('error', 'Username cannot be empty.');
        redirectBack();
    }

    if ($stmt = $conn->prepare('SELECT id, username, password FROM admin_users WHERE id = ? LIMIT 1')) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$existing) {
            setAdminUsersFlash('error', 'Admin account not found.');
            redirectBack();
        }
    }

    if ($stmt = $conn->prepare('SELECT id FROM admin_users WHERE username = ? AND id <> ? LIMIT 1')) {
        $stmt->bind_param('si', $username, $id);
        $stmt->execute();
        $duplicate = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($duplicate) {
            setAdminUsersFlash('error', 'Another admin already uses that username.');
            redirectBack();
        }
    }

    $isSelfEdit = ($actingAdminId !== null && $id === (int) $actingAdminId);

    if ($isSelfEdit) {
        if ($currentPassword === '') {
            setAdminUsersFlash('error', 'Please provide your current password to update your own account.');
            redirectBack();
        }

        if (empty($existing['password']) || !password_verify($currentPassword, $existing['password'])) {
            setAdminUsersFlash('error', 'Current password is incorrect.');
            redirectBack();
        }
    }

    if ($password !== '') {
        if (strlen($password) < 8) {
            setAdminUsersFlash('error', 'New password must be at least 8 characters long.');
            redirectBack();
        }

        if ($confirmPassword === '') {
            setAdminUsersFlash('error', 'Please confirm the new password.');
            redirectBack();
        }

        if (!hash_equals($password, $confirmPassword)) {
            setAdminUsersFlash('error', 'New password and confirmation do not match.');
            redirectBack();
        }
    } elseif ($confirmPassword !== '') {
        setAdminUsersFlash('error', 'Enter a new password to use the confirmation field.');
        redirectBack();
    }

    if ($password !== '') {
        if ($stmt = $conn->prepare('UPDATE admin_users SET username = ?, password = ? WHERE id = ?')) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param('ssi', $username, $hash, $id);
            $ok = $stmt->execute();
            $stmt->close();
        } else {
            $ok = false;
        }
    } else {
        if ($stmt = $conn->prepare('UPDATE admin_users SET username = ? WHERE id = ?')) {
            $stmt->bind_param('si', $username, $id);
            $ok = $stmt->execute();
            $stmt->close();
        } else {
            $ok = false;
        }
    }

    if (!empty($ok)) {
        setAdminUsersFlash('success', 'Admin user updated successfully.');
        if ($actingAdminUsername) {
            $details = 'ID: ' . $id . ' (username: ' . $username . ')';
            if ($password !== '') {
                $details .= ' [password updated]';
            }
            logAdminActivity($conn, $actingAdminUsername, 'Edited admin user', $details);
        }
    } else {
        setAdminUsersFlash('error', 'Failed to update admin user.');
    }

    redirectBack();
}

if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {
        setAdminUsersFlash('error', 'Invalid admin ID supplied.');
        redirectBack();
    }

    if ($actingAdminId !== null && $id === (int) $actingAdminId) {
        setAdminUsersFlash('error', 'You cannot delete your own admin account while logged in.');
        redirectBack();
    }

    $target = null;
    if ($stmt = $conn->prepare('SELECT username FROM admin_users WHERE id = ? LIMIT 1')) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $target = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    if (!$target) {
        setAdminUsersFlash('error', 'Admin account not found.');
        redirectBack();
    }

    if ($stmt = $conn->prepare('DELETE FROM admin_users WHERE id = ?')) {
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
    } else {
        $ok = false;
    }

    if (!empty($ok)) {
        setAdminUsersFlash('success', 'Admin user "' . $target['username'] . '" deleted.');
        if ($actingAdminUsername) {
            logAdminActivity($conn, $actingAdminUsername, 'Deleted admin user', $target['username']);
        }
    } else {
        setAdminUsersFlash('error', 'Failed to delete admin user.');
    }

    redirectBack();
}

setAdminUsersFlash('error', 'Unsupported action.');
redirectBack();
