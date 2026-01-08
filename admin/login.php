<?php
/**
 * Admin Login Page
 */

require_once __DIR__ . '/../includes/init.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$csrf_token = Security::generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'جلسة غير صالحة. يرجى تحديث الصفحة';
    } else if (!Security::checkRateLimit($_SERVER['REMOTE_ADDR'], 5, 300)) {
        $error = 'تم تجاوز عدد المحاولات المسموحة. يرجى الانتظار 5 دقائق';
    } else {
        $username = Security::sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'يرجى إدخال اسم المستخدم وكلمة المرور';
        } else {
            try {
                $db = getDB();
                
                // Log attempt
                $stmt = $db->prepare("INSERT INTO login_attempts (username, ip_address, user_type) VALUES (?, ?, 'admin')");
                $stmt->execute([$username, $_SERVER['REMOTE_ADDR']]);
                
                // Get admin
                $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? AND is_active = 1");
                $stmt->execute([$username]);
                $admin = $stmt->fetch();
                
                if ($admin && password_verify($password, $admin['password'])) {
                    // Update login attempt to success
                    $stmt = $db->prepare("UPDATE login_attempts SET success = 1 WHERE id = ?");
                    $stmt->execute([$db->lastInsertId()]);
                    
                    // Update last login
                    $stmt = $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$admin['id']]);
                    
                    // Set session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_username'] = $admin['username'];
                    
                    Security::resetRateLimit($_SERVER['REMOTE_ADDR']);
                    Security::logEvent('LOGIN', 'Admin login successful', $admin['id']);
                    
                    header('Location: index.php');
                    exit;
                } else {
                    Security::logEvent('LOGIN_FAILED', "Admin login failed for: $username");
                    $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
                }
            } catch (Exception $e) {
                error_log("Admin login error: " . $e->getMessage());
                $error = 'حدث خطأ. يرجى المحاولة مرة أخرى';
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
    <title>تسجيل الدخول - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="register-page">
        <div class="hero" style="padding: 2rem;">
            <div class="hero-content">
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">🔐</div>
                <h1 style="font-size: 1.75rem;">لوحة تحكم المدير</h1>
            </div>
        </div>

        <div class="register-content">
            <div class="register-card">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">تسجيل الدخول</h3>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <span>⚠️</span>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        
                        <div class="form-group">
                            <label class="form-label">اسم المستخدم</label>
                            <input type="text" name="username" class="form-control" required autocomplete="username">
                        </div>

                        <div class="form-group">
                            <label class="form-label">كلمة المرور</label>
                            <input type="password" name="password" class="form-control" required autocomplete="current-password">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            تسجيل الدخول
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
