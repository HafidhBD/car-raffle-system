<?php
/**
 * Admin - Malls Management
 */

require_once __DIR__ . '/../includes/init.php';
requireAdmin();

$db = getDB();
$message = '';
$error = '';
$csrf_token = Security::generateCSRFToken();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'جلسة غير صالحة';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add') {
            $name = Security::sanitize($_POST['name'] ?? '');
            $name_en = Security::sanitize($_POST['name_en'] ?? '');
            $address = Security::sanitize($_POST['address'] ?? '');
            $latitude = floatval($_POST['latitude'] ?? 0);
            $longitude = floatval($_POST['longitude'] ?? 0);
            $radius = intval($_POST['radius'] ?? 500);
            
            if (empty($name) || $latitude == 0 || $longitude == 0) {
                $error = 'يرجى ملء جميع الحقول المطلوبة';
            } else {
                try {
                    $stmt = $db->prepare("INSERT INTO malls (name, name_en, address, latitude, longitude, radius) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $name_en, $address, $latitude, $longitude, $radius]);
                    $message = 'تم إضافة المول بنجاح';
                    logActivity($_SESSION['admin_id'], 'admin', 'add_mall', "Added mall: $name");
                } catch (Exception $e) {
                    error_log("Add mall error: " . $e->getMessage());
                    $error = 'حدث خطأ في إضافة المول';
                }
            }
        } elseif ($action === 'edit') {
            $id = intval($_POST['id'] ?? 0);
            $name = Security::sanitize($_POST['name'] ?? '');
            $name_en = Security::sanitize($_POST['name_en'] ?? '');
            $address = Security::sanitize($_POST['address'] ?? '');
            $latitude = floatval($_POST['latitude'] ?? 0);
            $longitude = floatval($_POST['longitude'] ?? 0);
            $radius = intval($_POST['radius'] ?? 500);
            
            if (empty($name) || $latitude == 0 || $longitude == 0) {
                $error = 'يرجى ملء جميع الحقول المطلوبة';
            } else {
                try {
                    $stmt = $db->prepare("UPDATE malls SET name = ?, name_en = ?, address = ?, latitude = ?, longitude = ?, radius = ? WHERE id = ?");
                    $stmt->execute([$name, $name_en, $address, $latitude, $longitude, $radius, $id]);
                    $message = 'تم تحديث المول بنجاح';
                } catch (Exception $e) {
                    $error = 'حدث خطأ في تحديث المول';
                }
            }
        } elseif ($action === 'toggle') {
            $id = intval($_POST['id'] ?? 0);
            try {
                $stmt = $db->prepare("UPDATE malls SET is_active = NOT is_active WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'تم تحديث حالة المول';
            } catch (Exception $e) {
                $error = 'حدث خطأ';
            }
        } elseif ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            try {
                $stmt = $db->prepare("DELETE FROM malls WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'تم حذف المول';
            } catch (Exception $e) {
                $error = 'لا يمكن حذف المول لوجود مشاركات مرتبطة به';
            }
        }
    }
}

// Get malls with entry counts
$malls = $db->query("
    SELECT m.*, 
           COUNT(e.id) as entry_count
    FROM malls m
    LEFT JOIN entries e ON e.mall_id = m.id
    GROUP BY m.id
    ORDER BY m.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المولات - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">إدارة المولات</h1>
                <button onclick="openModal('addModal')" class="btn btn-primary">➕ إضافة مول</button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success">✓ <?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="alert alert-info mb-3">
                💡 <strong>نصيحة:</strong> يمكنك الحصول على إحداثيات الموقع من خرائط جوجل بالضغط على الموقع المطلوب واختيار الإحداثيات. النطاق (Radius) هو المسافة بالأمتار التي يُسمح للعملاء بالتسجيل من خلالها.
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">قائمة المولات (<?= count($malls) ?>)</h3>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>العنوان</th>
                                <th>الإحداثيات</th>
                                <th>النطاق (م)</th>
                                <th>المشاركات</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($malls)): ?>
                                <tr>
                                    <td colspan="8" class="text-center" style="padding: 2rem;">لا توجد مولات</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($malls as $mall): ?>
                                    <tr>
                                        <td><?= $mall['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($mall['name']) ?></strong>
                                            <?php if ($mall['name_en']): ?>
                                                <br><small style="color: var(--gray-500);"><?= htmlspecialchars($mall['name_en']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($mall['address'] ?? '-') ?></td>
                                        <td dir="ltr" style="font-size: 0.85rem;">
                                            <?= $mall['latitude'] ?>,<br><?= $mall['longitude'] ?>
                                        </td>
                                        <td><?= number_format($mall['radius']) ?></td>
                                        <td><?= number_format($mall['entry_count']) ?></td>
                                        <td>
                                            <?php if ($mall['is_active']): ?>
                                                <span class="badge badge-success">نشط</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">معطل</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <button onclick='openEditModal(<?= json_encode($mall) ?>)' class="btn btn-secondary btn-sm">✏️</button>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?= $mall['id'] ?>">
                                                    <button type="submit" class="btn btn-<?= $mall['is_active'] ? 'warning' : 'success' ?> btn-sm">
                                                        <?= $mall['is_active'] ? '⏸️' : '▶️' ?>
                                                    </button>
                                                </form>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا المول؟')">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $mall['id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Mall Modal -->
    <div class="modal-overlay" id="addModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">إضافة مول جديد</h3>
                <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label class="form-label">اسم المول (عربي) *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">اسم المول (إنجليزي)</label>
                        <input type="text" name="name_en" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">العنوان</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">خط العرض (Latitude) *</label>
                        <input type="number" name="latitude" class="form-control" step="0.00000001" required placeholder="مثال: 24.774265">
                    </div>
                    <div class="form-group">
                        <label class="form-label">خط الطول (Longitude) *</label>
                        <input type="number" name="longitude" class="form-control" step="0.00000001" required placeholder="مثال: 46.738586">
                    </div>
                    <div class="form-group">
                        <label class="form-label">النطاق بالمتر</label>
                        <input type="number" name="radius" class="form-control" value="500" min="50" max="5000">
                        <span class="form-text">المسافة المسموحة للتسجيل (الافتراضي: 500 متر)</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">إضافة</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">إلغاء</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Mall Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">تعديل المول</h3>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="form-group">
                        <label class="form-label">اسم المول (عربي) *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">اسم المول (إنجليزي)</label>
                        <input type="text" name="name_en" id="edit_name_en" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">العنوان</label>
                        <input type="text" name="address" id="edit_address" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">خط العرض (Latitude) *</label>
                        <input type="number" name="latitude" id="edit_latitude" class="form-control" step="0.00000001" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">خط الطول (Longitude) *</label>
                        <input type="number" name="longitude" id="edit_longitude" class="form-control" step="0.00000001" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">النطاق بالمتر</label>
                        <input type="number" name="radius" id="edit_radius" class="form-control" min="50" max="5000">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">إلغاء</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }
        function openEditModal(mall) {
            document.getElementById('edit_id').value = mall.id;
            document.getElementById('edit_name').value = mall.name;
            document.getElementById('edit_name_en').value = mall.name_en || '';
            document.getElementById('edit_address').value = mall.address || '';
            document.getElementById('edit_latitude').value = mall.latitude;
            document.getElementById('edit_longitude').value = mall.longitude;
            document.getElementById('edit_radius').value = mall.radius;
            openModal('editModal');
        }
    </script>
</body>
</html>
