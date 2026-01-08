<?php
/**
 * Promoter Login Page
 */

require_once __DIR__ . '/../includes/init.php';

// Redirect if already logged in
if (isPromoterLoggedIn()) {
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
                $stmt = $db->prepare("INSERT INTO login_attempts (username, ip_address, user_type) VALUES (?, ?, 'promoter')");
                $stmt->execute([$username, $_SERVER['REMOTE_ADDR']]);
                
                // Get promoter
                $stmt = $db->prepare("SELECT * FROM promoters WHERE username = ? AND is_active = 1");
                $stmt->execute([$username]);
                $promoter = $stmt->fetch();
                
                if ($promoter && password_verify($password, $promoter['password'])) {
                    // Update login attempt to success
                    $stmt = $db->prepare("UPDATE login_attempts SET success = 1 WHERE id = ?");
                    $stmt->execute([$db->lastInsertId()]);
                    
                    // Update last login
                    $stmt = $db->prepare("UPDATE promoters SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$promoter['id']]);
                    
                    // Set session
                    $_SESSION['promoter_id'] = $promoter['id'];
                    $_SESSION['promoter_name'] = $promoter['name'];
                    $_SESSION['promoter_username'] = $promoter['username'];
                    
                    Security::resetRateLimit($_SERVER['REMOTE_ADDR']);
                    Security::logEvent('LOGIN', 'Promoter login successful', $promoter['id']);
                    
                    header('Location: index.php');
                    exit;
                } else {
                    Security::logEvent('LOGIN_FAILED', "Promoter login failed for: $username");
                    $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
                }
            } catch (Exception $e) {
                error_log("Promoter login error: " . $e->getMessage());
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
    <title>تسجيل الدخول - المروجين</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --brand-orange: #f97630;
            --brand-blue: #193a63;
        }
        body {
            background: #f8fafc;
            min-height: 100vh;
        }
        .login-header {
            background: var(--brand-blue);
            padding: 2rem;
            text-align: center;
        }
        .login-logo {
            max-width: 180px;
            height: auto;
            margin-bottom: 1rem;
        }
        .login-header h1 {
            color: white;
            font-size: 1.5rem;
            margin: 0 0 0.5rem 0;
        }
        .login-header p {
            color: rgba(255,255,255,0.8);
            margin: 0;
        }
        .card {
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .card-header {
            background: var(--brand-blue);
            border-radius: 16px 16px 0 0;
            padding: 1rem 1.5rem;
            margin: -2rem -2rem 1.5rem -2rem;
        }
        .card-title {
            color: white;
            margin: 0;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--brand-orange) 0%, #e55a1b 100%);
            border: none;
            box-shadow: 0 6px 20px rgba(249, 118, 48, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(249, 118, 48, 0.4);
        }
        .form-control:focus {
            border-color: var(--brand-orange);
            box-shadow: 0 0 0 3px rgba(249, 118, 48, 0.15);
        }
    </style>
</head>
<body>
    <div class="register-page">
        <div class="login-header">
            <img src="../logos/logo -Family Bonds.png" alt="السحب" class="login-logo" onerror="this.style.display='none'">
            <h1>بوابة المروجين</h1>
            <p>سجّل دخولك لتسجيل مشاركات العملاء</p>
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
