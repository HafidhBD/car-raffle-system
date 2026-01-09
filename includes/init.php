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

// Get translations
function get_trans($key) {
    $lang = $_SERVER['HTTP_X_LANG'] ?? 'ar';
    $lang = ($lang === 'en') ? 'en' : 'ar';
    
    $trans = [
        'ar' => [
            'invalid_method' => 'طريقة الطلب غير صحيحة',
            'invalid_data' => 'بيانات غير صالحة',
            'invalid_coords' => 'إحداثيات الموقع غير صالحة',
            'no_malls' => 'لا توجد مولات متاحة حالياً',
            'loc_verified' => 'تم التحقق من الموقع',
            'outside_mall' => 'يجب أن تكون داخل أحد المولات المعتمدة للتسجيل',
            'loc_error' => 'حدث خطأ في التحقق من الموقع',
            'invalid_session' => 'جلسة غير صالحة. يرجى تحديث الصفحة',
            'rate_limit' => 'تم تجاوز عدد المحاولات المسموحة. يرجى الانتظار قليلاً',
            'enter_name' => 'يرجى إدخال الاسم الكامل',
            'invalid_phone' => 'رقم الجوال غير صحيح',
            'select_mall' => 'يرجى تحديد المول',
            'mall_not_found' => 'المول غير موجود أو غير متاح',
            'duplicate_entry' => 'رقم الجوال هذا مسجل مسبقاً',
            'reg_success' => 'تم التسجيل بنجاح',
            'reg_error' => 'حدث خطأ في التسجيل. يرجى المحاولة مرة أخرى'
        ],
        'en' => [
            'invalid_method' => 'Invalid request method',
            'invalid_data' => 'Invalid data',
            'invalid_coords' => 'Invalid coordinates',
            'no_malls' => 'No malls available currently',
            'loc_verified' => 'Location verified',
            'outside_mall' => 'You must be inside one of the authorized malls to register',
            'loc_error' => 'Error checking location',
            'invalid_session' => 'Invalid session. Please refresh the page',
            'rate_limit' => 'Rate limit exceeded. Please wait a moment',
            'enter_name' => 'Please enter full name',
            'invalid_phone' => 'Invalid mobile number',
            'select_mall' => 'Please select a mall',
            'mall_not_found' => 'Mall not found or inactive',
            'duplicate_entry' => 'You have already registered within the last 24 hours',
            'reg_success' => 'Registration successful',
            'reg_error' => 'Registration error. Please try again'
        ]
    ];
    
    return $trans[$lang][$key] ?? $key;
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
