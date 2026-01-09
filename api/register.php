<?php
/**
 * Customer Registration API
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, get_trans('invalid_method'));
}

// Verify CSRF token
if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    jsonResponse(false, get_trans('invalid_session'));
}

// Rate limiting
if (!Security::checkRateLimit($_SERVER['REMOTE_ADDR'], 5, 300)) {
    jsonResponse(false, get_trans('rate_limit'));
}

// Validate input
$name = Security::sanitize($_POST['name'] ?? '');
$phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
$mall_id = intval($_POST['mall_id'] ?? 0);
$latitude = floatval($_POST['latitude'] ?? 0);
$longitude = floatval($_POST['longitude'] ?? 0);

// Validation
if (empty($name) || mb_strlen($name) < 3) {
    jsonResponse(false, get_trans('enter_name'));
}

if (!Security::validatePhone($phone)) {
    jsonResponse(false, get_trans('invalid_phone'));
}

if ($mall_id <= 0) {
    jsonResponse(false, get_trans('select_mall'));
}

try {
    $db = getDB();
    
    // Verify mall exists and is active
    $stmt = $db->prepare("SELECT id, name FROM malls WHERE id = ? AND is_active = 1");
    $stmt->execute([$mall_id]);
    $mall = $stmt->fetch();
    
    if (!$mall) {
        jsonResponse(false, get_trans('mall_not_found'));
    }
    
    // Check for duplicate entry (same phone in same mall within last 24 hours)
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM entries 
        WHERE phone = ? AND mall_id = ? AND entry_type = 'customer'
        AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $stmt->execute([$phone, $mall_id]);
    
    if ($stmt->fetchColumn() > 0) {
        jsonResponse(false, get_trans('duplicate_entry'));
    }
    
    // Insert entry
    $stmt = $db->prepare("
        INSERT INTO entries (customer_name, phone, mall_id, entry_type, ip_address, user_agent, latitude, longitude)
        VALUES (?, ?, ?, 'customer', ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $name,
        $phone,
        $mall_id,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? '',
        $latitude,
        $longitude
    ]);
    
    Security::logEvent('REGISTRATION', "Customer registered: $phone at mall $mall_id");
    
    jsonResponse(true, get_trans('reg_success'), [
        'entry_id' => $db->lastInsertId()
    ]);
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    jsonResponse(false, get_trans('reg_error'));
}
