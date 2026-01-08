<?php
/**
 * Delete Entry API
 */

require_once __DIR__ . '/../../includes/init.php';
requireAdmin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'طريقة غير صحيحة');
}

$input = json_decode(file_get_contents('php://input'), true);

if (!Security::verifyCSRFToken($input['csrf_token'] ?? '')) {
    jsonResponse(false, 'جلسة غير صالحة');
}

$id = intval($input['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(false, 'معرف غير صالح');
}

try {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM entries WHERE id = ?");
    $stmt->execute([$id]);
    
    logActivity($_SESSION['admin_id'], 'admin', 'delete_entry', "Deleted entry ID: $id");
    
    jsonResponse(true, 'تم الحذف بنجاح');
} catch (Exception $e) {
    error_log("Delete entry error: " . $e->getMessage());
    jsonResponse(false, 'حدث خطأ في الحذف');
}
