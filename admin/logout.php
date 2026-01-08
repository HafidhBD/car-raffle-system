<?php
/**
 * Admin Logout
 */

require_once __DIR__ . '/../includes/init.php';

if (isset($_SESSION['admin_id'])) {
    Security::logEvent('LOGOUT', 'Admin logged out', $_SESSION['admin_id']);
}

// Destroy session
$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
