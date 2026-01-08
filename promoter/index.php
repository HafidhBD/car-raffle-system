<?php
/**
 * Promoter Registration Page
 */

require_once __DIR__ . '/../includes/init.php';
requirePromoter();

$db = getDB();

// Get malls
$malls = $db->query("SELECT id, name FROM malls WHERE is_active = 1 ORDER BY name")->fetchAll();

// Get promoter stats
$stmt = $db->prepare("SELECT COUNT(*) FROM entries WHERE promoter_id = ?");
$stmt->execute([$_SESSION['promoter_id']]);
$total_entries = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM entries WHERE promoter_id = ? AND DATE(created_at) = CURDATE()");
$stmt->execute([$_SESSION['promoter_id']]);
$today_entries = $stmt->fetchColumn();

$csrf_token = Security::generateCSRFToken();
$message = '';
$error = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'جلسة غير صالحة. يرجى تحديث الصفحة';
    } else {
        $name = Security::sanitize($_POST['name'] ?? '');
        $phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
        $mall_id = intval($_POST['mall_id'] ?? 0);
        $invoices = intval($_POST['invoices'] ?? 1);
        
        // Validate
        if (empty($name) || mb_strlen($name) < 3) {
            $error = 'يرجى إدخال الاسم الكامل';
        } elseif (!Security::validatePhone($phone)) {
            $error = 'رقم الجوال غير صحيح';
        } elseif ($mall_id <= 0) {
            $error = 'يرجى اختيار المول';
        } elseif ($invoices < 1 || $invoices > 100) {
            $error = 'عدد الفواتير غير صالح (1-100)';
        } else {
            try {
                // Verify mall exists
                $stmt = $db->prepare("SELECT id FROM malls WHERE id = ? AND is_active = 1");
                $stmt->execute([$mall_id]);
                if (!$stmt->fetch()) {
                    $error = 'المول غير موجود';
                } else {
                    // Insert entries (one for each invoice)
                    $stmt = $db->prepare("
                        INSERT INTO entries (customer_name, phone, mall_id, entry_type, promoter_id, invoice_number, total_invoices, ip_address)
                        VALUES (?, ?, ?, 'promoter', ?, ?, ?, ?)
                    ");
                    
                    for ($i = 1; $i <= $invoices; $i++) {
                        $stmt->execute([
                            $name,
                            $phone,
                            $mall_id,
                            $_SESSION['promoter_id'],
                            $i,
                            $invoices,
                            $_SERVER['REMOTE_ADDR']
                        ]);
                    }
                    
                    $message = "تم تسجيل $invoices مشاركة بنجاح للعميل: $name";
                    logActivity($_SESSION['promoter_id'], 'promoter', 'register_customer', "Registered: $phone with $invoices invoices");
                    
                    // Update stats
                    $stmt = $db->prepare("SELECT COUNT(*) FROM entries WHERE promoter_id = ?");
                    $stmt->execute([$_SESSION['promoter_id']]);
                    $total_entries = $stmt->fetchColumn();
                    
                    $stmt = $db->prepare("SELECT COUNT(*) FROM entries WHERE promoter_id = ? AND DATE(created_at) = CURDATE()");
                    $stmt->execute([$_SESSION['promoter_id']]);
                    $today_entries = $stmt->fetchColumn();
                }
            } catch (Exception $e) {
                error_log("Promoter registration error: " . $e->getMessage());
                $error = 'حدث خطأ في التسجيل. يرجى المحاولة مرة أخرى';
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
    <title>تسجيل العملاء - المروجين</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .promoter-header {
            background: var(--gradient);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .promoter-header h1 {
            color: white;
            font-size: 1.25rem;
            margin: 0;
        }
        .promoter-stats {
            display: flex;
            gap: 1.5rem;
        }
        .promoter-stat {
            text-align: center;
        }
        .promoter-stat strong {
            display: block;
            font-size: 1.5rem;
        }
        .promoter-stat span {
            font-size: 0.875rem;
            opacity: 0.9;
        }
        .promoter-content {
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="promoter-header">
        <div>
            <h1>🎯 مرحباً، <?= htmlspecialchars($_SESSION['promoter_name']) ?></h1>
        </div>
        <div class="promoter-stats">
            <div class="promoter-stat">
                <strong><?= number_format($today_entries) ?></strong>
                <span>اليوم</span>
            </div>
            <div class="promoter-stat">
                <strong><?= number_format($total_entries) ?></strong>
                <span>الإجمالي</span>
            </div>
        </div>
        <a href="logout.php" class="btn btn-outline" style="border-color: white; color: white;">🚪 خروج</a>
    </div>

    <div class="promoter-content">
        <!-- Conditions Box -->
        <div class="conditions-box">
            <h4>📋 شروط قبول الفواتير:</h4>
            <ul>
                <li>يتم اعتماد الفواتير التي قيمتها تساوي ٣٠ ريال أو أكثر</li>
                <li>زمن عملية الشراء خلال الساعتين الماضية</li>
                <li>يتم ختم الفاتورة بختم الحملة</li>
            </ul>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">✓ <?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Registration Form -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">تسجيل عميل جديد</h3>
            </div>

            <form method="POST" id="registrationForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <div class="form-group">
                    <label class="form-label">اسم العميل *</label>
                    <input type="text" name="name" class="form-control" placeholder="أدخل اسم العميل الكامل" required>
                </div>

                <div class="form-group">
                    <label class="form-label">رقم الجوال *</label>
                    <input type="tel" name="phone" class="form-control" placeholder="05xxxxxxxx" required pattern="^(05|5|9665|00966)[0-9]{8}$">
                </div>

                <div class="form-group">
                    <label class="form-label">المول *</label>
                    <select name="mall_id" class="form-control" required>
                        <option value="">اختر المول</option>
                        <?php foreach ($malls as $mall): ?>
                            <option value="<?= $mall['id'] ?>"><?= htmlspecialchars($mall['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">عدد الفواتير *</label>
                    <input type="number" name="invoices" class="form-control" value="1" min="1" max="100" required>
                    <span class="form-text">عدد الفواتير المؤهلة للعميل (سيتم تسجيل مشاركة لكل فاتورة)</span>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    ✓ تسجيل المشاركة
                </button>
            </form>
        </div>

        <!-- Recent Entries -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">آخر التسجيلات</h3>
            </div>
            <?php
            $stmt = $db->prepare("
                SELECT e.*, m.name as mall_name 
                FROM entries e 
                LEFT JOIN malls m ON e.mall_id = m.id 
                WHERE e.promoter_id = ? 
                ORDER BY e.created_at DESC 
                LIMIT 10
            ");
            $stmt->execute([$_SESSION['promoter_id']]);
            $recent = $stmt->fetchAll();
            ?>
            <?php if (empty($recent)): ?>
                <p style="text-align: center; color: var(--gray-500); padding: 1rem;">لا توجد تسجيلات حتى الآن</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>الجوال</th>
                                <th>المول</th>
                                <th>الفواتير</th>
                                <th>الوقت</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $entry): ?>
                                <tr>
                                    <td><?= htmlspecialchars($entry['customer_name']) ?></td>
                                    <td dir="ltr"><?= htmlspecialchars($entry['phone']) ?></td>
                                    <td><?= htmlspecialchars($entry['mall_name']) ?></td>
                                    <td><?= $entry['invoice_number'] ?>/<?= $entry['total_invoices'] ?></td>
                                    <td><?= formatDateArabic($entry['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Clear form after successful submission
        <?php if ($message): ?>
        document.getElementById('registrationForm').reset();
        <?php endif; ?>
    </script>
</body>
</html>
