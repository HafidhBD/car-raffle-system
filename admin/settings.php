<?php
/**
 * Admin - Settings
 */

require_once __DIR__ . '/../includes/init.php';
requireAdmin();

$db = getDB();
$message = '';
$error = '';
$csrf_token = Security::generateCSRFToken();

// Get current admin
$stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'جلسة غير صالحة';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $name = Security::sanitize($_POST['name'] ?? '');
            $email = Security::sanitize($_POST['email'] ?? '');
            
            if (empty($name)) {
                $error = 'يرجى إدخال الاسم';
            } else {
                try {
                    $stmt = $db->prepare("UPDATE admins SET name = ?, email = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $_SESSION['admin_id']]);
                    $_SESSION['admin_name'] = $name;
                    $message = 'تم تحديث البيانات بنجاح';
                    
                    // Refresh admin data
                    $stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
                    $stmt->execute([$_SESSION['admin_id']]);
                    $admin = $stmt->fetch();
                } catch (Exception $e) {
                    $error = 'حدث خطأ في تحديث البيانات';
                }
            }
        } elseif ($action === 'change_password') {
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            
            if (empty($current) || empty($new) || empty($confirm)) {
                $error = 'يرجى ملء جميع الحقول';
            } elseif ($new !== $confirm) {
                $error = 'كلمة المرور الجديدة غير متطابقة';
            } elseif (strlen($new) < 6) {
                $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
            } elseif (!password_verify($current, $admin['password'])) {
                $error = 'كلمة المرور الحالية غير صحيحة';
            } else {
                try {
                    $hashed = Security::hashPassword($new);
                    $stmt = $db->prepare("UPDATE admins SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed, $_SESSION['admin_id']]);
                    $message = 'تم تغيير كلمة المرور بنجاح';
                    logActivity($_SESSION['admin_id'], 'admin', 'password_change', 'Password changed');
                } catch (Exception $e) {
                    $error = 'حدث خطأ في تغيير كلمة المرور';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإعدادات - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">الإعدادات</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success">✓ <?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="stats-grid">
                <!-- Profile Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">👤 بيانات الحساب</h3>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label class="form-label">اسم المستخدم</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">الاسم</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($admin['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </form>
                </div>

                <!-- Password Change -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🔑 تغيير كلمة المرور</h3>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label class="form-label">كلمة المرور الحالية</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">كلمة المرور الجديدة</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label class="form-label">تأكيد كلمة المرور</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">تغيير كلمة المرور</button>
                    </form>
                </div>
            </div>

            <!-- System Info -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">ℹ️ معلومات النظام</h3>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <div class="stat-card" style="flex: 1; min-width: 200px;">
                        <div class="stat-content">
                            <p>رابط صفحة التسجيل للعملاء</p>
                            <code style="font-size: 0.9rem; color: var(--primary);" id="customerUrl"></code>
                        </div>
                    </div>
                    <div class="stat-card" style="flex: 1; min-width: 200px;">
                        <div class="stat-content">
                            <p>رابط صفحة تسجيل الدخول للمروجين</p>
                            <code style="font-size: 0.9rem; color: var(--primary);" id="promoterUrl"></code>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Set URLs based on current location
        const baseUrl = window.location.origin;
        document.getElementById('customerUrl').textContent = baseUrl + '/';
        document.getElementById('promoterUrl').textContent = baseUrl + '/promoter/';
    </script>
</body>
</html>
