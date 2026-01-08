<?php
/**
 * Promoter Logout
 */

require_once __DIR__ . '/../includes/init.php';

if (isset($_SESSION['promoter_id'])) {
    Security::logEvent('LOGOUT', 'Promoter logged out', $_SESSION['promoter_id']);
}

// Destroy session
$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
