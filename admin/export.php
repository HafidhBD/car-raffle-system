<?php
/**
 * Export Entries to CSV
 */

require_once __DIR__ . '/../includes/init.php';
requireAdmin();

$db = getDB();

// Filters (same as entries page)
$filter_type = $_GET['type'] ?? '';
$filter_mall = $_GET['mall'] ?? '';
$filter_promoter = $_GET['promoter'] ?? '';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

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

// Get entries
$sql = "
    SELECT e.id, e.customer_name, e.phone, m.name as mall_name, 
           e.entry_type, p.name as promoter_name, 
           e.invoice_number, e.total_invoices, e.created_at
    FROM entries e
    LEFT JOIN malls m ON e.mall_id = m.id
    LEFT JOIN promoters p ON e.promoter_id = p.id
    $where_clause
    ORDER BY e.created_at DESC
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$entries = $stmt->fetchAll();

// Output CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="entries_' . date('Y-m-d_H-i-s') . '.csv"');

// Add BOM for Excel Arabic support
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, [
    'رقم المشاركة',
    'الاسم',
    'رقم الجوال',
    'المول',
    'نوع المشاركة',
    'المروج',
    'رقم الفاتورة',
    'إجمالي الفواتير',
    'تاريخ التسجيل'
]);

// Data rows
foreach ($entries as $entry) {
    fputcsv($output, [
        $entry['id'],
        $entry['customer_name'],
        $entry['phone'],
        $entry['mall_name'],
        $entry['entry_type'] === 'customer' ? 'عميل' : 'مروج',
        $entry['promoter_name'] ?? '-',
        $entry['invoice_number'],
        $entry['total_invoices'],
        $entry['created_at']
    ]);
}

fclose($output);
exit;
