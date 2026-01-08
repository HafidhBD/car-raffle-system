<?php
/**
 * Admin - Entries Management
 */

require_once __DIR__ . '/../includes/init.php';
requireAdmin();

$db = getDB();

// Filters
$filter_type = $_GET['type'] ?? '';
$filter_mall = $_GET['mall'] ?? '';
$filter_promoter = $_GET['promoter'] ?? '';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Build query
$where = [];
$params = [];

if ($filter_type === 'customer') {
    $where[] = "e.entry_type = 'customer'";
} elseif ($filter_type === 'promoter') {
    $where[] = "e.entry_type = 'promoter'";
}

if ($filter_mall) {
    $where[] = "e.mall_id = ?";
    $params[] = $filter_mall;
}

if ($filter_promoter) {
    $where[] = "e.promoter_id = ?";
    $params[] = $filter_promoter;
}

if ($filter_date_from) {
    $where[] = "DATE(e.created_at) >= ?";
    $params[] = $filter_date_from;
}

if ($filter_date_to) {
    $where[] = "DATE(e.created_at) <= ?";
    $params[] = $filter_date_to;
}

if ($search) {
    $where[] = "(e.customer_name LIKE ? OR e.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM entries e $where_clause";
$stmt = $db->prepare($count_sql);
$stmt->execute($params);
$total = $stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Get entries
$sql = "
    SELECT e.*, m.name as mall_name, p.name as promoter_name
    FROM entries e
    LEFT JOIN malls m ON e.mall_id = m.id
    LEFT JOIN promoters p ON e.promoter_id = p.id
    $where_clause
    ORDER BY e.created_at DESC
    LIMIT $per_page OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$entries = $stmt->fetchAll();

// Get malls for filter
$malls = $db->query("SELECT id, name FROM malls ORDER BY name")->fetchAll();

// Get promoters for filter
$promoters = $db->query("SELECT id, name FROM promoters ORDER BY name")->fetchAll();

$csrf_token = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المشاركات - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">المشاركات</h1>
                <div class="d-flex gap-2">
                    <a href="export.php?<?= http_build_query($_GET) ?>" class="btn btn-success btn-sm">📥 تصدير Excel</a>
                </div>
            </div>

            <!-- Filters -->
            <div class="table-container">
                <form method="GET" class="filter-bar">
                    <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو الجوال..." value="<?= htmlspecialchars($search) ?>">
                    
                    <select name="type" class="form-control">
                        <option value="">كل الأنواع</option>
                        <option value="customer" <?= $filter_type === 'customer' ? 'selected' : '' ?>>عملاء</option>
                        <option value="promoter" <?= $filter_type === 'promoter' ? 'selected' : '' ?>>مروجين</option>
                    </select>
                    
                    <select name="mall" class="form-control">
                        <option value="">كل المولات</option>
                        <?php foreach ($malls as $mall): ?>
                            <option value="<?= $mall['id'] ?>" <?= $filter_mall == $mall['id'] ? 'selected' : '' ?>><?= htmlspecialchars($mall['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="promoter" class="form-control">
                        <option value="">كل المروجين</option>
                        <?php foreach ($promoters as $promoter): ?>
                            <option value="<?= $promoter['id'] ?>" <?= $filter_promoter == $promoter['id'] ? 'selected' : '' ?>><?= htmlspecialchars($promoter['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($filter_date_from) ?>" placeholder="من تاريخ">
                    <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($filter_date_to) ?>" placeholder="إلى تاريخ">
                    
                    <button type="submit" class="btn btn-primary">🔍 بحث</button>
                    <a href="entries.php" class="btn btn-secondary">إعادة تعيين</a>
                </form>

                <div class="table-header">
                    <h3 class="table-title">إجمالي: <?= number_format($total) ?> مشاركة</h3>
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
                                <th>المروج</th>
                                <th>رقم الفاتورة</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($entries)): ?>
                                <tr>
                                    <td colspan="9" class="text-center" style="padding: 2rem;">لا توجد مشاركات</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($entries as $entry): ?>
                                    <tr>
                                        <td><?= $entry['id'] ?></td>
                                        <td><?= htmlspecialchars($entry['customer_name']) ?></td>
                                        <td dir="ltr"><?= htmlspecialchars($entry['phone']) ?></td>
                                        <td><?= htmlspecialchars($entry['mall_name']) ?></td>
                                        <td>
                                            <?php if ($entry['entry_type'] === 'customer'): ?>
                                                <span class="badge badge-primary">عميل</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">مروج</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $entry['promoter_name'] ? htmlspecialchars($entry['promoter_name']) : '-' ?></td>
                                        <td>
                                            <?php if ($entry['total_invoices'] > 1): ?>
                                                <?= $entry['invoice_number'] ?> / <?= $entry['total_invoices'] ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatDateArabic($entry['created_at']) ?></td>
                                        <td>
                                            <button onclick="deleteEntry(<?= $entry['id'] ?>)" class="btn btn-danger btn-sm">🗑️</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">السابق</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">التالي</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function deleteEntry(id) {
            if (!confirm('هل أنت متأكد من حذف هذه المشاركة؟')) return;
            
            fetch('api/delete-entry.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, csrf_token: '<?= $csrf_token ?>' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    </script>
</body>
</html>
