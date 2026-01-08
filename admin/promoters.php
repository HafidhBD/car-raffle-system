<?php
/**
 * Admin - Promoters Management
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
            $username = Security::sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $name = Security::sanitize($_POST['name'] ?? '');
            $phone = Security::sanitize($_POST['phone'] ?? '');
            
            if (empty($username) || empty($password) || empty($name)) {
                $error = 'يرجى ملء جميع الحقول المطلوبة';
            } elseif (strlen($password) < 6) {
                $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
            } else {
                try {
                    $stmt = $db->prepare("SELECT id FROM promoters WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->fetch()) {
                        $error = 'اسم المستخدم موجود مسبقاً';
                    } else {
                        $hashed = Security::hashPassword($password);
                        $stmt = $db->prepare("INSERT INTO promoters (username, password, name, phone, created_by) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$username, $hashed, $name, $phone, $_SESSION['admin_id']]);
                        $message = 'تم إضافة المروج بنجاح';
                        logActivity($_SESSION['admin_id'], 'admin', 'add_promoter', "Added promoter: $username");
                    }
                } catch (Exception $e) {
                    error_log("Add promoter error: " . $e->getMessage());
                    $error = 'حدث خطأ في إضافة المروج';
                }
            }
        } elseif ($action === 'toggle') {
            $id = intval($_POST['id'] ?? 0);
            try {
                $stmt = $db->prepare("UPDATE promoters SET is_active = NOT is_active WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'تم تحديث حالة المروج';
            } catch (Exception $e) {
                $error = 'حدث خطأ';
            }
        } elseif ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            try {
                $stmt = $db->prepare("DELETE FROM promoters WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'تم حذف المروج';
                logActivity($_SESSION['admin_id'], 'admin', 'delete_promoter', "Deleted promoter ID: $id");
            } catch (Exception $e) {
                $error = 'لا يمكن حذف المروج لوجود مشاركات مرتبطة به';
            }
        } elseif ($action === 'reset_password') {
            $id = intval($_POST['id'] ?? 0);
            $new_password = $_POST['new_password'] ?? '';
            
            if (strlen($new_password) < 6) {
                $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
            } else {
                try {
                    $hashed = Security::hashPassword($new_password);
                    $stmt = $db->prepare("UPDATE promoters SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed, $id]);
                    $message = 'تم تغيير كلمة المرور بنجاح';
                } catch (Exception $e) {
                    $error = 'حدث خطأ في تغيير كلمة المرور';
                }
            }
        }
    }
}

// Get promoters with entry counts
$promoters = $db->query("
    SELECT p.*, 
           COUNT(e.id) as entry_count,
           (SELECT COUNT(*) FROM entries WHERE promoter_id = p.id AND DATE(created_at) = CURDATE()) as today_count
    FROM promoters p
    LEFT JOIN entries e ON e.promoter_id = p.id
    GROUP BY p.id
    ORDER BY p.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المروجين - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">إدارة المروجين</h1>
                <button onclick="openModal('addModal')" class="btn btn-primary">➕ إضافة مروج</button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success">✓ <?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">قائمة المروجين (<?= count($promoters) ?>)</h3>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>اسم المستخدم</th>
                                <th>الجوال</th>
                                <th>المشاركات</th>
                                <th>اليوم</th>
                                <th>الحالة</th>
                                <th>آخر دخول</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($promoters)): ?>
                                <tr>
                                    <td colspan="9" class="text-center" style="padding: 2rem;">لا يوجد مروجين</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($promoters as $p): ?>
                                    <tr>
                                        <td><?= $p['id'] ?></td>
                                        <td><?= htmlspecialchars($p['name']) ?></td>
                                        <td><?= htmlspecialchars($p['username']) ?></td>
                                        <td dir="ltr"><?= htmlspecialchars($p['phone'] ?? '-') ?></td>
                                        <td><?= number_format($p['entry_count']) ?></td>
                                        <td><?= number_format($p['today_count']) ?></td>
                                        <td>
                                            <?php if ($p['is_active']): ?>
                                                <span class="badge badge-success">نشط</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">معطل</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $p['last_login'] ? formatDateArabic($p['last_login']) : '-' ?></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                    <button type="submit" class="btn btn-<?= $p['is_active'] ? 'warning' : 'success' ?> btn-sm">
                                                        <?= $p['is_active'] ? '⏸️' : '▶️' ?>
                                                    </button>
                                                </form>
                                                <button onclick="openResetModal(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name']) ?>')" class="btn btn-secondary btn-sm">🔑</button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد؟')">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
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

    <!-- Add Promoter Modal -->
    <div class="modal-overlay" id="addModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">إضافة مروج جديد</h3>
                <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label class="form-label">الاسم *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">اسم المستخدم *</label>
                        <input type="text" name="username" class="form-control" required pattern="[a-zA-Z0-9_]+" title="حروف إنجليزية وأرقام فقط">
                    </div>
                    <div class="form-group">
                        <label class="form-label">كلمة المرور *</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label class="form-label">رقم الجوال</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">إضافة</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">إلغاء</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal-overlay" id="resetModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">تغيير كلمة المرور - <span id="resetName"></span></h3>
                <button class="modal-close" onclick="closeModal('resetModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="id" id="resetId">
                    
                    <div class="form-group">
                        <label class="form-label">كلمة المرور الجديدة</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">تغيير</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('resetModal')">إلغاء</button>
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
        function openResetModal(id, name) {
            document.getElementById('resetId').value = id;
            document.getElementById('resetName').textContent = name;
            openModal('resetModal');
        }
    </script>
</body>
</html>
