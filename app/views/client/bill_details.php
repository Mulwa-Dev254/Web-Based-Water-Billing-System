<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php?page=login');
    exit;
}
$bill = $data['bill'] ?? [];
$payments = $data['payments'] ?? [];
$client = $data['client'] ?? [];
$meter = $data['meter'] ?? [];
$startReading = $data['startReading'] ?? [];
$endReading = $data['endReading'] ?? [];
$planName = $data['planName'] ?? null;
$planBaseRate = $data['planBaseRate'] ?? null;
$planUnitRate = $data['planUnitRate'] ?? null;
$consumptionHistory = $data['consumptionHistory'] ?? [];
function formatCurrency($v){ return 'KES ' . number_format((float)$v, 2); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill #<?= (int)$bill['id'] ?> - AquaBill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        body { background: #f1f5f9; }
        .top-ribbon { position: sticky; top: 0; z-index: 10; background: linear-gradient(135deg, #1f2937, #0f172a); color: #fff; padding: 12px 16px; display:flex; align-items:center; justify-content: space-between; }
        .top-ribbon a { color: #93c5fd; text-decoration: none; font-weight: 500; }
        .page { max-width: 980px; margin: 24px auto; padding: 0 12px; }
        .invoice { width: 100%; background: #fff; box-shadow: 0 10px 30px rgba(2,6,23,0.08); border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
        .invoice-header { display:flex; justify-content: space-between; padding: 18px 20px; background: linear-gradient(90deg, #1f2937, #0f172a); color:#fff; }
        .invoice-title { display:flex; align-items:center; gap:12px; }
        .logo { width: 42px; height: 42px; border-radius: 50%; background:#2563eb; color:#fff; display:flex; align-items:center; justify-content:center; }
        .invoice-meta { display:flex; align-items:center; gap:16px; font-size: 14px; }
        .badge { background:#2563eb; color:#fff; border-radius: 20px; padding:4px 10px; }
        .invoice-body { padding: 18px 20px; }
        .info-grid { display:grid; grid-template-columns: 1fr 1fr; gap:16px; }
        .info-card { background: #f8fafc; border:1px solid #e5e7eb; border-radius: 12px; padding: 14px; }
        .info-card h4 { font-size: 16px; margin:0 0 10px; color:#0f172a; }
        .info-row { display:flex; justify-content: space-between; margin:6px 0; font-size:14px; color:#334155; }
        .breakdown-table { width:100%; border-collapse: collapse; margin-top: 16px; }
        .breakdown-table thead th { background:#1f2937; color:#fff; padding:10px; font-weight:600; }
        .breakdown-table td, .breakdown-table th { border:1px solid #e5e7eb; padding:10px; font-size:14px; }
        .totals { display:grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top:16px; }
        .total-card { background:#f8fafc; border:1px solid #e5e7eb; border-radius: 12px; padding: 16px; font-weight:600; }
        .chart-title { font-size: 14px; font-weight: 600; color: #334155; margin-bottom: 8px; }
        .calc-card { margin-top: 18px; background:#fff; border:1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 6px 18px rgba(2,6,23,0.06); }
        .calc-header { padding: 14px 16px; border-bottom:1px solid #e5e7eb; font-weight:600; color:#0f172a; }
        .calc-body { padding: 16px; }
        .pay-btn { display:inline-flex; align-items:center; gap:8px; background:#16a34a; color:#fff; border:none; border-radius: 10px; padding: 12px 18px; font-weight:600; box-shadow: 0 0 0 0 rgba(22,163,74,0.6); animation: pulseGlow 2s infinite, bounce 2s infinite; text-decoration:none; }
        @keyframes pulseGlow { 0% { box-shadow: 0 0 0 0 rgba(22,163,74,0.6); } 70% { box-shadow: 0 0 0 12px rgba(22,163,74,0); } 100% { box-shadow: 0 0 0 0 rgba(22,163,74,0); } }
        @keyframes bounce { 0%, 20%, 50%, 80%, 100% { transform: translateY(0); } 40% { transform: translateY(-4px); } 60% { transform: translateY(-2px); } }
        .summary-card { margin-top: 18px; background:#fff; border:1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 6px 18px rgba(2,6,23,0.06); }
        .summary-header { padding: 14px 16px; border-bottom:1px solid #e5e7eb; font-weight:600; color:#0f172a; }
        .summary-body { padding: 16px; }
        @page { size: A4; margin: 12mm; }
        @media print {
            body { margin:0; background:#fff; }
            body * { visibility: hidden; }
            .invoice, .invoice * { visibility: visible; }
            .invoice { position: absolute; left: 0; top: 0; width: 210mm; box-shadow: none; border: none; }
            .invoice, .invoice * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>
    <div class="top-ribbon">
        <div><a href="index.php?page=client_view_bills"><i class="fas fa-arrow-left"></i> Back to bills</a></div>
        <div style="display:flex; align-items:center; gap:10px;">
            <span>Bill #<?= (int)$bill['id'] ?></span>
            <a href="#" onclick="window.print(); return false;" class="btn btn-sm btn-light" style="color:#0f172a;">Print</a>
            <a href="index.php?page=client_download_bill&bill_id=<?= (int)$bill['id'] ?>" class="btn btn-sm btn-light" style="color:#0f172a;" target="_blank">Download PDF</a>
        </div>
    </div>
    <div class="page">
        <div class="invoice">
            <div class="invoice-header">
                <div class="invoice-title"><div class="logo"><i class="fas fa-tint"></i></div><div>Water Billing System</div></div>
                <div class="invoice-meta"><div class="badge">Bill #<?= (int)$bill['id'] ?></div><div><?= date('d M Y', strtotime($bill['bill_date'] ?? date('Y-m-d'))) ?></div><div>Due <?= date('d M Y', strtotime($bill['due_date'] ?? date('Y-m-d'))) ?></div><div><?= ucfirst(str_replace('_',' ', $bill['payment_status'] ?? 'pending')) ?></div></div>
            </div>
            <div class="invoice-body">
                <div class="info-grid">
                    <div class="info-card"><h4>Bill Information</h4><div class="info-row"><span>Billing Period</span><span><?= htmlspecialchars(($startReading['reading_date'] ?? '') && ($endReading['reading_date'] ?? '') ? (date('d M Y', strtotime($startReading['reading_date'])) . ' to ' . date('d M Y', strtotime($endReading['reading_date']))) : 'N/A') ?></span></div><div class="info-row"><span>Client</span><span><?= htmlspecialchars($client['full_name'] ?? '') ?></span></div><div class="info-row"><span>Email</span><span><?= htmlspecialchars($client['email'] ?? '') ?></span></div><div class="info-row"><span>Phone</span><span><?= htmlspecialchars($client['phone'] ?? '') ?></span></div></div>
                    <div class="info-card"><h4>Meter & Plan</h4><div class="info-row"><span>Serial Number</span><span><?= htmlspecialchars($meter['serial_number'] ?? '') ?></span></div><div class="info-row"><span>Location</span><span><?= htmlspecialchars($meter['location'] ?? '') ?></span></div><div class="info-row"><span>Plan</span><span><?= htmlspecialchars($planName ?? 'N/A') ?></span></div><div class="info-row"><span>Status</span><span><?= htmlspecialchars(ucfirst(str_replace('_',' ', $bill['payment_status'] ?? 'pending'))) ?></span></div></div>
                </div>
                <table class="breakdown-table">
                    <thead><tr><th>Description</th><th>Units</th><th>Rate</th><th>Amount</th></tr></thead>
                    <tbody>
                        <tr><td>Base Rate</td><td>-</td><td><?= formatCurrency($planBaseRate ?? 0) ?></td><td><?= formatCurrency($planBaseRate ?? 0) ?></td></tr>
                        <tr><td>Consumption Charge</td><td><?= number_format((float)($bill['consumption_units'] ?? 0), 3) ?> units</td><td><?= formatCurrency($planUnitRate ?? ($bill['unit_rate'] ?? 0)) ?>/unit</td><td><?= formatCurrency(((float)($planUnitRate ?? ($bill['unit_rate'] ?? 0))) * ((float)($bill['consumption_units'] ?? 0))) ?></td></tr>
                        <tr><th colspan="3" style="text-align:right;">Subtotal (Amount Due)</th><th><?= formatCurrency($bill['amount_due'] ?? 0) ?></th></tr>
                    </tbody>
                </table>
                <div class="totals">
                    <div class="total-card">Amount Paid<br><?= formatCurrency($bill['amount_paid'] ?? 0) ?></div>
                    <div class="total-card">Outstanding Balance<br><?= formatCurrency(($bill['amount_due'] ?? 0) - ($bill['amount_paid'] ?? 0)) ?></div>
                </div>
                <div style="margin-top:16px;">
                    <div class="chart-title">Consumption Trend</div>
                    <canvas id="consumptionTrend" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="calc-card">
            <div class="calc-header">Calculation Breakdown</div>
            <div class="calc-body">
                <div class="d-flex justify-content-between mb-2"><span>Base Rate</span><span><?= formatCurrency($planBaseRate ?? 0) ?></span></div>
                <div class="d-flex justify-content-between mb-2"><span>Consumption (<?= number_format((float)($bill['consumption_units'] ?? 0), 3) ?> units)</span><span><?= formatCurrency(((float)($planUnitRate ?? ($bill['unit_rate'] ?? 0))) * ((float)($bill['consumption_units'] ?? 0))) ?></span></div>
                <hr>
                <div class="d-flex justify-content-between mb-3 fw-bold"><span>Total Amount</span><span><?= formatCurrency($bill['amount_due'] ?? 0) ?></span></div>
                <div class="d-flex justify-content-between mb-3 text-success"><span>Amount Paid</span><span><?= formatCurrency($bill['amount_paid'] ?? 0) ?></span></div>
                <div class="d-flex justify-content-between fw-bold fs-5"><span>Balance</span><span class="<?= (($bill['amount_due'] ?? 0) - ($bill['amount_paid'] ?? 0)) > 0 ? 'text-danger' : 'text-success' ?>"><?= formatCurrency(($bill['amount_due'] ?? 0) - ($bill['amount_paid'] ?? 0)) ?></span></div>
                <div class="mt-3">
                    <?php if ((($bill['amount_due'] ?? 0) - ($bill['amount_paid'] ?? 0)) > 0): ?>
                        <a class="pay-btn" href="index.php?page=client_payments&bill_id=<?= (int)$bill['id'] ?>"><i class="fas fa-money-bill-wave"></i> Pay</a>
                    <?php else: ?>
                        <a class="btn btn-secondary" href="#" disabled>Paid</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-header">Billing Summary</div>
            <div class="summary-body">
                <div class="row g-3">
                    <div class="col-md-4"><div class="p-3 border rounded">Client<br><strong><?= htmlspecialchars($client['full_name'] ?? '') ?></strong></div></div>
                    <div class="col-md-4"><div class="p-3 border rounded">Meter<br><strong><?= htmlspecialchars($meter['serial_number'] ?? '') ?></strong></div></div>
                    <div class="col-md-4"><div class="p-3 border rounded">Status<br><strong><?= htmlspecialchars(ucfirst(str_replace('_',' ', $bill['payment_status'] ?? 'pending'))) ?></strong></div></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function(){
        const history = <?= json_encode($consumptionHistory ?? []) ?>;
        if (!Array.isArray(history) || history.length === 0) return;
        const labels = history.map(r => r.bill_date);
        const data = history.map(r => parseFloat(r.consumption_units));
        const ctx = document.getElementById('consumptionTrend').getContext('2d');
        new Chart(ctx, { type:'line', data:{ labels, datasets:[{ label:'Units', data, borderColor:'#2563eb', backgroundColor:'rgba(37,99,235,0.15)', tension:0.3, fill:true, pointRadius:2, borderWidth:2 }] }, options:{ responsive:true, plugins:{ legend:{ display:false } }, scales:{ x:{ grid:{ display:false } }, y:{ beginAtZero:true } } } });
    })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
