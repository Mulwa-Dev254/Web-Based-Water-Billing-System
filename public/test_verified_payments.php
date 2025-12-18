<?php
session_start();
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Payment.php';

$database = new Database($conn);
$payment = new Payment($database);

$total = $payment->getTotalPaymentsAmount();

$database->query('SELECT payment_id, client_name, amount_paid, payment_date, payment_type FROM verified_payments ORDER BY payment_date DESC');
$rows = $database->resultSet();
$database->closeStmt();

header('Content-Type: text/html; charset=UTF-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Verified Payments Test</title><style>
body{font-family:Inter,Arial,sans-serif;background:#f5f7fb;color:#0f172a;margin:24px}
.card{max-width:980px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden}
.header{padding:16px 20px;background:#0ea5e9;color:#fff;font-weight:700}
.section{padding:16px 20px}
table{width:100%;border-collapse:collapse}
th,td{padding:10px 8px;border-bottom:1px solid #e5e7eb;text-align:left;font-size:14px}
.total{font-size:18px;font-weight:700;color:#334155}
</style></head><body><div class="card">';
echo '<div class="header">Verified Payments Summary</div>';
echo '<div class="section"><div class="total">Total Payments: KES ' . number_format((float)$total, 2) . '</div></div>';
echo '<div class="section"><table><thead><tr><th>Payment ID</th><th>Client</th><th>Amount Paid</th><th>Date</th><th>Type</th></tr></thead><tbody>';
if (!empty($rows)) {
    foreach ($rows as $r) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars((string)$r['payment_id']) . '</td>';
        echo '<td>' . htmlspecialchars($r['client_name'] ?? '') . '</td>';
        echo '<td>KES ' . number_format((float)$r['amount_paid'], 2) . '</td>';
        echo '<td>' . htmlspecialchars($r['payment_date'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($r['payment_type'] ?? '') . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="5">No verified payments found</td></tr>';
}
echo '</tbody></table></div></div></body></html>';
