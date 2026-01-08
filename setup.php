<?php
/**
 * Setup Script
 * Run once to initialize the database and create admin user
 * DELETE THIS FILE AFTER SETUP!
 */

// Check if already set up
if (file_exists(__DIR__ . '/config/.setup_complete')) {
    die('<h1 style="color: red; font-family: Arial;">⚠️ Setup already completed. Delete this file for security.</h1>');
}

require_once __DIR__ . '/config/database.php';

$message = '';
$error = '';
$step = $_GET['step'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance()->getConnection();
        
        if ($_POST['action'] === 'create_tables') {
            // Read and execute schema
            $schema = file_get_contents(__DIR__ . '/database/schema.sql');
            
            // Split by semicolon and execute each statement
            $statements = array_filter(array_map('trim', explode(';', $schema)));
            
            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    $db->exec($statement);
                }
            }
            
            $message = 'تم إنشاء جداول قاعدة البيانات بنجاح!';
            $step = 2;
            
        } elseif ($_POST['action'] === 'create_admin') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            $name = trim($_POST['name']);
            
            if (empty($username) || empty($password) || empty($name)) {
                $error = 'يرجى ملء جميع الحقول';
            } elseif (strlen($password) < 6) {
                $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
            } else {
                // Hash password
                $hash = password_hash($password, PASSWORD_ARGON2ID, [
                    'memory_cost' => 65536,
                    'time_cost' => 4,
                    'threads' => 3
                ]);
                
                // Check if admin exists
                $stmt = $db->prepare("SELECT id FROM admins WHERE username = ?");
                $stmt->execute([$username]);
                
                if ($stmt->fetch()) {
                    // Update existing admin
                    $stmt = $db->prepare("UPDATE admins SET password = ?, name = ? WHERE username = ?");
                    $stmt->execute([$hash, $name, $username]);
                } else {
                    // Create new admin
                    $stmt = $db->prepare("INSERT INTO admins (username, password, name) VALUES (?, ?, ?)");
                    $stmt->execute([$username, $hash, $name]);
                }
                
                // Mark setup as complete
                file_put_contents(__DIR__ . '/config/.setup_complete', date('Y-m-d H:i:s'));
                
                $message = 'تم إنشاء حساب المدير بنجاح! يمكنك الآن تسجيل الدخول.';
                $step = 3;
            }
        }
    } catch (Exception $e) {
        $error = 'خطأ: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعداد النظام - سحب السيارة</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .setup-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        h1 {
            text-align: center;
            margin-bottom: 0.5rem;
            color: #1e293b;
        }
        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 2rem;
        }
        .steps {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background: #e2e8f0;
            color: #64748b;
        }
        .step.active {
            background: #2563eb;
            color: white;
        }
        .step.done {
            background: #10b981;
            color: white;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #334155;
        }
        input {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
        }
        input:focus {
            outline: none;
            border-color: #2563eb;
        }
        .btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }
        .success-icon {
            text-align: center;
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .links {
            margin-top: 1.5rem;
            text-align: center;
        }
        .links a {
            display: inline-block;
            margin: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #f1f5f9;
            color: #2563eb;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }
        .links a:hover {
            background: #e2e8f0;
        }
        .warning {
            background: #fef3c7;
            color: #92400e;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="setup-card">
        <h1>🚗 إعداد النظام</h1>
        <p class="subtitle">نظام سحب السيارة</p>

        <div class="steps">
            <div class="step <?= $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' ?>">1</div>
            <div class="step <?= $step >= 2 ? ($step > 2 ? 'done' : 'active') : '' ?>">2</div>
            <div class="step <?= $step >= 3 ? 'done' : '' ?>">3</div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">✓ <?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
            <h3 style="margin-bottom: 1rem;">الخطوة 1: إنشاء قاعدة البيانات</h3>
            <p style="color: #64748b; margin-bottom: 1.5rem;">
                سيتم إنشاء جميع الجداول المطلوبة في قاعدة البيانات.
            </p>
            <form method="POST">
                <input type="hidden" name="action" value="create_tables">
                <button type="submit" class="btn">إنشاء الجداول</button>
            </form>

        <?php elseif ($step == 2): ?>
            <h3 style="margin-bottom: 1rem;">الخطوة 2: إنشاء حساب المدير</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create_admin">
                
                <div class="form-group">
                    <label>اسم المستخدم</label>
                    <input type="text" name="username" value="admin" required pattern="[a-zA-Z0-9_]+">
                </div>
                
                <div class="form-group">
                    <label>كلمة المرور</label>
                    <input type="password" name="password" required minlength="6" placeholder="6 أحرف على الأقل">
                </div>
                
                <div class="form-group">
                    <label>الاسم</label>
                    <input type="text" name="name" value="المدير" required>
                </div>
                
                <button type="submit" class="btn">إنشاء الحساب</button>
            </form>

        <?php else: ?>
            <div class="success-icon">✅</div>
            <h3 style="text-align: center; margin-bottom: 1rem;">تم الإعداد بنجاح!</h3>
            <p style="text-align: center; color: #64748b;">
                النظام جاهز للاستخدام الآن.
            </p>
            
            <div class="links">
                <a href="admin/login.php">🔐 دخول المدير</a>
                <a href="promoter/login.php">👤 دخول المروجين</a>
                <a href="/">🏠 الصفحة الرئيسية</a>
            </div>
            
            <div class="warning">
                ⚠️ <strong>مهم:</strong> احذف ملف setup.php فوراً لأسباب أمنية!
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
