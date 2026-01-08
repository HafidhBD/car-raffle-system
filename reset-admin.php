<?php
/**
 * Admin Password Reset
 * DELETE THIS FILE AFTER USE!
 */

require_once __DIR__ . '/config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Use bcrypt for compatibility
            $hash = password_hash($password, PASSWORD_BCRYPT);
            
            // Update admin password
            $stmt = $db->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
            $stmt->execute([$hash]);
            
            if ($stmt->rowCount() > 0) {
                $message = 'تم تحديث كلمة المرور بنجاح! يمكنك الآن تسجيل الدخول.';
            } else {
                // Insert if not exists
                $stmt = $db->prepare("INSERT INTO admins (username, password, name) VALUES ('admin', ?, 'المدير')");
                $stmt->execute([$hash]);
                $message = 'تم إنشاء حساب المدير بنجاح!';
            }
        } catch (Exception $e) {
            $error = 'خطأ: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        h1 { text-align: center; margin-bottom: 1.5rem; color: #1e293b; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
        }
        input:focus { outline: none; border-color: #2563eb; }
        .btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .links { text-align: center; margin-top: 1.5rem; }
        .links a {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #f1f5f9;
            color: #2563eb;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }
        .warning {
            background: #fef3c7;
            color: #92400e;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            text-align: center;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔑 إعادة تعيين كلمة المرور</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success">✓ <?= $message ?></div>
            <div class="links">
                <a href="admin/login.php">🔐 تسجيل الدخول</a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-error">⚠️ <?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>كلمة المرور الجديدة للمدير (admin)</label>
                    <input type="password" name="password" required minlength="6" placeholder="أدخل كلمة مرور جديدة">
                </div>
                <button type="submit" class="btn">تحديث كلمة المرور</button>
            </form>
        <?php endif; ?>
        
        <div class="warning">⚠️ احذف هذا الملف بعد الانتهاء!</div>
    </div>
</body>
</html>
