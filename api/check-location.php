<?php
/**
 * Check Location API
 * Verify if user is within authorized mall location
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'طريقة الطلب غير صحيحة');
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    jsonResponse(false, 'بيانات غير صالحة');
}

$latitude = floatval($input['latitude'] ?? 0);
$longitude = floatval($input['longitude'] ?? 0);

if ($latitude == 0 || $longitude == 0) {
    jsonResponse(false, 'إحداثيات الموقع غير صالحة');
}

try {
    $db = getDB();
    
    // Get all active malls
    $stmt = $db->prepare("SELECT id, name, latitude, longitude, radius FROM malls WHERE is_active = 1");
    $stmt->execute();
    $malls = $stmt->fetchAll();
    
    if (empty($malls)) {
        jsonResponse(false, 'لا توجد مولات متاحة حالياً');
    }
    
    // Check if user is within any mall's radius
    foreach ($malls as $mall) {
        $distance = calculateDistance(
            $latitude, 
            $longitude, 
            floatval($mall['latitude']), 
            floatval($mall['longitude'])
        );
        
        // Distance in meters, radius in meters
        if ($distance <= $mall['radius']) {
            jsonResponse(true, 'تم التحقق من الموقع', [
                'mall_id' => $mall['id'],
                'mall_name' => $mall['name']
            ]);
        }
    }
    
    jsonResponse(false, 'يجب أن تكون داخل أحد المولات المعتمدة للتسجيل');
    
} catch (Exception $e) {
    error_log("Location check error: " . $e->getMessage());
    jsonResponse(false, 'حدث خطأ في التحقق من الموقع');
}

/**
 * Calculate distance between two coordinates using Haversine formula
 * Returns distance in meters
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // meters
    
    $lat1Rad = deg2rad($lat1);
    $lat2Rad = deg2rad($lat2);
    $deltaLat = deg2rad($lat2 - $lat1);
    $deltaLon = deg2rad($lon2 - $lon1);
    
    $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
         cos($lat1Rad) * cos($lat2Rad) *
         sin($deltaLon / 2) * sin($deltaLon / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earthRadius * $c;
}
