<?php
/**
 * Initialization file
 * Car Raffle System
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Riyadh');

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

// Get database connection
function getDB() {
    return Database::getInstance()->getConnection();
}

// JSON response helper
function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Check if user is logged in (admin)
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Check if promoter is logged in
function isPromoterLoggedIn() {
    return isset($_SESSION['promoter_id']) && !empty($_SESSION['promoter_id']);
}

// Require admin login
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

// Require promoter login
function requirePromoter() {
    if (!isPromoterLoggedIn()) {
        header('Location: /promoter/login.php');
        exit;
    }
}

// Log activity
function logActivity($userId, $userType, $action, $details = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, user_type, action, details, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $userType, $action, $details, $_SERVER['REMOTE_ADDR']]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

// Format date in Arabic
function formatDateArabic($date) {
    $timestamp = strtotime($date);
    return date('Y/m/d H:i', $timestamp);
}
