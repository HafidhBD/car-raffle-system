<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/../includes/init.php';
requireAdmin();

$db = getDB();

// Get statistics
$stats = [];

// Total entries
$stmt = $db->query("SELECT COUNT(*) FROM entries");
$stats['total_entries'] = $stmt->fetchColumn();

// Today's entries
$stmt = $db->query("SELECT COUNT(*) FROM entries WHERE DATE(created_at) = CURDATE()");
$stats['today_entries'] = $stmt->fetchColumn();

// Customer entries
$stmt = $db->query("SELECT COUNT(*) FROM entries WHERE entry_type = 'customer'");
$stats['customer_entries'] = $stmt->fetchColumn();

// Promoter entries
$stmt = $db->query("SELECT COUNT(*) FROM entries WHERE entry_type = 'promoter'");
$stats['promoter_entries'] = $stmt->fetchColumn();

// Active promoters
$stmt = $db->query("SELECT COUNT(*) FROM promoters WHERE is_active = 1");
$stats['active_promoters'] = $stmt->fetchColumn();

// Active malls
$stmt = $db->query("SELECT COUNT(*) FROM malls WHERE is_active = 1");
$stats['active_malls'] = $stmt->fetchColumn();

// Recent entries
$stmt = $db->query("
    SELECT e.*, m.name as mall_name, p.name as promoter_name
    FROM entries e
    LEFT JOIN malls m ON e.mall_id = m.id
    LEFT JOIN promoters p ON e.promoter_id = p.id
    ORDER BY e.created_at DESC
    LIMIT 10
");
$recent_entries = $stmt->fetchAll();

$csrf_token = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - سحب السيارة</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">لوحة التحكم</h1>
                <span>مرحباً، <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">📊</div>
                    <div class="stat-content">
                        <h3><?= number_format($stats['total_entries']) ?></h3>
                        <p>إجمالي المشاركات</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">📅</div>
                    <div class="stat-content">
                        <h3><?= number_format($stats['today_entries']) ?></h3>
                        <p>مشاركات اليوم</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning">👥</div>
                    <div class="stat-content">
                        <h3><?= number_format($stats['customer_entries']) ?></h3>
                        <p>مشاركات العملاء</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon info">🎯</div>
                    <div class="stat-content">
                        <h3><?= number_format($stats['promoter_entries']) ?></h3>
                        <p>مشاركات المروجين</p>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="stats-grid" style="margin-bottom: 2rem;">
                <div class="stat-card">
                    <div class="stat-icon primary">👤</div>
                    <div class="stat-content">
                        <h3><?= number_format($stats['active_promoters']) ?></h3>
                        <p>المروجين النشطين</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">🏬</div>
                    <div class="stat-content">
                        <h3><?= number_format($stats['active_malls']) ?></h3>
                        <p>المولات النشطة</p>
                    </div>
                </div>
            </div>

            <!-- Recent Entries -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">آخر المشاركات</h3>
                    <a href="entries.php" class="btn btn-outline btn-sm">عرض الكل</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>الجوال</th>
                                <th>المول</th>
                                <th>النوع</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_entries)): ?>
                                <tr>
                                    <td colspan="6" class="text-center" style="padding: 2rem;">لا توجد مشاركات حتى الآن</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_entries as $entry): ?>
                                    <tr>
                                        <td><?= $entry['id'] ?></td>
                                        <td><?= htmlspecialchars($entry['customer_name']) ?></td>
                                        <td dir="ltr"><?= htmlspecialchars($entry['phone']) ?></td>
                                        <td><?= htmlspecialchars($entry['mall_name']) ?></td>
                                        <td>
                                            <?php if ($entry['entry_type'] === 'customer'): ?>
                                                <span class="badge badge-primary">عميل</span>
                                            <?php else: ?>
                                                <span class="badge badge-success"><?= htmlspecialchars($entry['promoter_name'] ?? 'مروج') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatDateArabic($entry['created_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
