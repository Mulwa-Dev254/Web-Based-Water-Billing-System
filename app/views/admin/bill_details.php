<?php
$bill = $data['bill'] ?? null;
$payments = $data['payments'] ?? [];
$client = $data['client'] ?? [];
$meter = $data['meter'] ?? [];
$planName = $data['planName'] ?? null;
$planBaseRate = $data['planBaseRate'] ?? 0;
$planUnitRate = $data['planUnitRate'] ?? 0;
$consumptionHistory = $data['consumptionHistory'] ?? [];
$hasVerifiedPayment = false; foreach ($payments as $pp) { if (strtolower($pp['status'] ?? '') === 'confirmed_and_verified') { $hasVerifiedPayment = true; break; } }
function formatCurrency($amount) { return 'KES ' . number_format((float)$amount, 2); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • Bill Details</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary:#ff4757;
            --primary-dark:#e84118;
            --dark-bg:#1e1e2d;
            --sidebar-bg:#1a1a27;
            --card-bg:#2a2a3c;
            --text-light:#f8f9fa;
            --text-muted:#a1a5b7;
            --border-color:#2d2d3a;
            --success:#1dd1a1;
            --info:#2e86de;
            --warning:#ff9f43;
            --danger:#ee5253;
        }
        html, body { height: 100%; width: 100%; }
        body { font-family:'Inter',sans-serif;background-color:var(--dark-bg);color:var(--text-light);margin:0;overflow-x:hidden; }
        .dashboard-layout { display:flex; width:100vw; min-height:100vh; }
        .sidebar { width:280px; background-color:var(--sidebar-bg); padding:1.5rem 0; display:flex; flex-direction:column; position:fixed; height:100vh; top:0; left:0; z-index:1000; border-right:1px solid var(--border-color); }
        .sidebar.collapsed { transform: translateX(-280px); transition:transform .3s; }
        .sidebar-header { padding:0 1.5rem 1.5rem; border-bottom:1px solid var(--border-color); margin-bottom:1.5rem; }
        .sidebar-header h3 { color:var(--primary); font-size:1.5rem; font-weight:700; display:flex; align-items:center; gap:.75rem; }
        .sidebar-nav { flex-grow:1; overflow-y:auto; padding:0 1rem; }
        .sidebar-nav ul { list-style:none; margin:0; padding:0; }
        .sidebar-nav a { display:flex; align-items:center; gap:.75rem; padding:.875rem 1rem; border-radius:.5rem; font-weight:500; color:var(--text-muted); transition:all .3s; }
        .sidebar-nav a:hover { background-color:rgba(255,71,87,.1); color:var(--text-light); }
        .sidebar-nav a.active { background-color:var(--primary); color:#fff; }
        .main-content {
            margin-left:280px;
            flex-grow:1;
            min-height:100vh;
            width:calc(100vw - 280px);
            display:flex;
            flex-direction:column;
            background:var(--dark-bg);
        }
        .main-content.full-width{margin-left:0;width:100vw;}
        .header-bar { background-color:var(--sidebar-bg); padding:1.25rem 2rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-color); }
        .header-title { display:flex; align-items:center; gap:1rem; }
        .dashboard-container {
            flex-grow:1;
            width:100%;
            max-width:100vw;
            min-width:0;
            padding:2rem;
            display:flex;
            flex-direction:column;
            gap:1.25rem;
        }
        .section-header {
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:.75rem;
            flex-wrap:wrap;
            margin-bottom:1rem;
        }
        .section-title { color:var(--primary); font-size:1.25rem; font-weight:600; display:flex; align-items:center; gap:.5rem; }
        .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .9rem; border-radius:.5rem; border:1px solid var(--border-color); background:transparent; color:var(--text-light); cursor:pointer; }
        .btn-primary { background-color:var(--primary); border-color:var(--primary);}
        .btn-outline { background:transparent;}
        .invoice-wrapper { display:flex; justify-content:center; width:100%; }
        .invoice {
            width:100%;
            max-width:900px;
            background:#1f1f2e;
            color:#f8f9fa;
            border:1px solid var(--border-color);
            box-shadow:0 6px 20px rgba(0,0,0,.3);
            border-radius:12px;
            overflow:hidden;
        }
        .invoice-header { display:flex; justify-content:space-between; align-items:center; padding:24px 32px; background:linear-gradient(90deg,#1e1e2d 0%,#151521 100%); color:#f8f9fa;}
        .invoice-title { display:flex; align-items:center; gap:12px;}
        .invoice-title .logo { width:40px; height:40px; border-radius:50%; background:var(--primary); display:flex; align-items:center; justify-content:center;}
        .invoice-title .logo i{ color:#fff; }
        .invoice-title .name { font-size:20px; font-weight:700; }
        .invoice-meta { text-align:right; font-size:13px; }
        .invoice-meta .badge{ display:inline-block; padding:6px 10px; border-radius:999px; background:var(--primary); color:#fff; font-weight:600; margin-bottom:6px; }
        .invoice-section { padding:24px 32px; }
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .info-card { background:#232336; border:1px solid var(--border-color); border-radius:10px; padding:16px;}
        .info-card h4{ margin:0 0 8px 0; font-size:14px; color:#a1a5b7; font-weight:600;}
        .info-row{ display:grid; grid-template-columns:160px 1fr; gap:8px; padding:6px 0; font-size:14px;}
        .info-row .label{ color:#a1a5b7; }
        .breakdown-table{width:100%;border-collapse:collapse;margin-top:16px;}
        .breakdown-table thead th{background:#1a1a27;color:#fff;font-weight:600;padding:10px;text-align:left;font-size:13px}
        .breakdown-table tbody td{border-bottom:1px solid var(--border-color);padding:10px;font-size:14px}
        .breakdown-table tfoot td{padding:10px;font-weight:700}
        .amount{text-align:right}
        .totals{margin-top:12px;display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .totals .total-card { background:#232336; border:1px solid var(--border-color); border-radius:10px; padding:12px; font-weight:600; display:flex; justify-content:space-between;}
        .chart-section { padding:0 32px 24px 32px; }
        .chart-card { background:#232336; border:1px solid var(--border-color); border-radius:10px; padding:16px;  }
        .chart-title{ font-size:14px; font-weight:600; color:#a1a5b7; margin-bottom:8px;}
        .table-responsive { width:100%; overflow-x:auto; }
        .data-table{ width:100%; min-width:700px; border-collapse:collapse; table-layout:auto;}
        .data-table th, .data-table td{padding:.75rem; border-bottom:1px solid var(--border-color); text-align:left;}
        .actions-sep{height:1px;background:var(--border-color);margin:8px 0}
        .send-client-btn{ display:inline-flex; align-items:center; gap:.5rem; padding:12px 16px; border:none; border-radius:10px; background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; font-weight:600; animation:bounceY 2.2s ease-in-out infinite, breatheGlowIndigo 2.6s ease-in-out infinite; }
        .send-client-btn i{ font-size:1.1rem; }
        @keyframes bounceY{ 0%,100%{ transform: translateY(0);} 50%{ transform: translateY(-3px);} }
        @keyframes breatheGlowIndigo{ 0%,100%{ box-shadow:0 0 0 0 rgba(99,102,241,.40), 0 0 0 0 rgba(139,92,246,.30);} 50%{ box-shadow:0 0 18px 6px rgba(99,102,241,.40), 0 0 30px 10px rgba(139,92,246,.25);} }
        @keyframes spin{ from{ transform:rotate(0deg);} to{ transform:rotate(360deg);} }
        .verify-success{ margin-top:12px; display:none; align-items:center; gap:10px; color:var(--success); font-weight:600; }
        @media (max-width:1200px){ .main-content {margin-left: 0; width:100vw;} .sidebar { position:relative; width:100vw; height:auto; } .dashboard-layout{flex-direction:column;} }
        @media (max-width:992px){ .dashboard-container { padding:1rem; } .invoice { max-width:100vw; } .main-content{margin-left:0; width:100vw;} .info-grid{ grid-template-columns:1fr; gap:8px; } }
        @media (max-width:760px){
          .dashboard-container { padding: 0.5rem; gap: 0.5rem; }
          .section-header { flex-direction:column; gap:0.3rem;}
          .invoice-header, .invoice-section, .chart-section { padding: 1rem;}
          .chart-card { padding: 8px;}
          .table-responsive { padding-bottom: 12px;}
          .breakdown-table th,.breakdown-table td, .data-table th,.data-table td { padding: 6px;}
        }
        @media print{ body{background:#fff} .invoice,.invoice *{visibility:visible} .invoice{position:absolute;left:0;top:0;width:210mm;box-shadow:none;border:none} body *{visibility:hidden} }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header"><h3><i class="fas fa-shield-alt"></i> Admin Panel</h3></div>
            <nav class="sidebar-nav"><ul>
                <li><a href="index.php?page=admin_dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="index.php?page=admin_manage_users"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                <li><a href="index.php?page=admin_manage_billing_plans"><i class="fas fa-file-invoice"></i> Billing Plans</a></li>
                <li><a href="index.php?page=admin_manage_services"><i class="fas fa-cogs"></i> Manage Services</a></li>
                <li><a href="index.php?page=admin_manage_requests"><i class="fas fa-clipboard-list"></i> Service Requests</a></li>
                <li><a href="index.php?page=admin_manage_meters"><i class="fas fa-tachometer-alt"></i> Manage Meters</a></li>
                <li><a href="index.php?page=generate_bills"><i class="fas fa-file-invoice-dollar"></i> Generate Bills</a></li>
                <li><a href="index.php?page=view_bills" class="active"><i class="fas fa-list"></i> View Bills</a></li>
                <li><a href="index.php?page=admin_transactions"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
                <li><a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul></nav>
        </div>
        <div class="main-content" id="mainContent">
            <div class="header-bar">
                <div class="header-title"><button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button><h1>Bill Details</h1></div>
                <div class="user-info"><a href="index.php?page=logout" class="btn btn-primary"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
            </div>
            <div class="dashboard-container">
                <div class="section-header">
                    <div class="section-title"><i class="fas fa-file-invoice-dollar"></i> Bill #<?= (int)$bill['id'] ?></div>
                    <div>
                        <a href="index.php?page=view_bills" class="btn btn-primary"><i class="fas fa-list"></i> View Bills</a>
                        <a href="index.php?page=billing_reports" class="btn btn-outline"><i class="fas fa-chart-bar"></i> Reports</a>
                        <a href="index.php?page=finance_manager_generate_receipt&bill_id=<?= (int)$bill['id'] ?>" class="btn btn-outline"><i class="fas fa-file-pdf"></i> Receipt (PDF)</a>
                        <button type="button" id="exportCsvBtn" class="btn btn-outline"><i class="fas fa-file-csv"></i> Export CSV</button>
                    </div>
                </div>
                <div class="invoice-wrapper">
                    <div class="invoice">
                        <div class="invoice-header">
                            <div class="invoice-title"><div class="logo"><i class="fas fa-tint"></i></div><div class="name">Water Billing System</div></div>
                            <div class="invoice-meta">
                                <div class="badge">Bill #<?= (int)$bill['id'] ?></div>
                                <div>Date: <?= htmlspecialchars(date('d M Y', strtotime($bill['bill_date'] ?? date('Y-m-d')))) ?></div>
                                <div>Due: <?= htmlspecialchars(date('d M Y', strtotime($bill['due_date'] ?? date('Y-m-d')))) ?></div>
                                <div>Status: <?= htmlspecialchars(ucfirst(str_replace('_',' ',$bill['payment_status'] ?? 'pending'))) ?><?= $hasVerifiedPayment ? ' <span style="display:inline-flex;align-items:center;gap:6px;background:var(--success);color:#fff;padding:4px 8px;border-radius:999px;">Verified <i class="fas fa-check-circle"></i></span>' : '' ?></div>
                            </div>
                        </div>
                        <div class="invoice-section">
                            <div class="info-grid">
                                <div class="info-card">
                                    <h4>Bill Information</h4>
                                    <div class="info-row"><div class="label">Billing Period</div><div><?= isset($bill['billing_period_start']) ? date('d M Y', strtotime($bill['billing_period_start'])) : 'N/A' ?> to <?= isset($bill['billing_period_end']) ? date('d M Y', strtotime($bill['billing_period_end'])) : 'N/A' ?></div></div>
                                    <div class="info-row"><div class="label">Client</div><div><?= htmlspecialchars($client['full_name'] ?? 'N/A') ?></div></div>
                                    <div class="info-row"><div class="label">Email</div><div><?= htmlspecialchars($client['email'] ?? 'N/A') ?></div></div>
                                    <div class="info-row"><div class="label">Phone</div><div><?= htmlspecialchars($client['contact_phone'] ?? 'N/A') ?></div></div>
                                </div>
                                <div class="info-card">
                                    <h4>Meter & Plan</h4>
                                    <div class="info-row"><div class="label">Serial Number</div><div><?= htmlspecialchars($meter['serial_number'] ?? 'N/A') ?></div></div>
                                    <div class="info-row"><div class="label">Location</div><div><?= htmlspecialchars($meter['gps_location'] ?? 'N/A') ?></div></div>
                                    <div class="info-row"><div class="label">Plan</div><div><?= htmlspecialchars($planName ?? 'Default Plan') ?></div></div>
                                    <div class="info-row"><div class="label">Status</div><div><?= htmlspecialchars(ucfirst(str_replace('_',' ',$bill['payment_status'] ?? 'pending'))) ?></div></div>
                                </div>
                            </div>
                            <div class="table-responsive" style="margin-top:16px;">
                                <table class="breakdown-table">
                                    <thead>
                                        <tr><th style="width:40%">Description</th><th style="width:20%">Units</th><th style="width:20%">Rate</th><th style="width:20%" class="amount">Amount</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>Base Rate</td><td>-</td><td><?= formatCurrency($planBaseRate) ?></td><td class="amount"><?= formatCurrency($planBaseRate) ?></td></tr>
                                        <tr><td>Consumption Charge</td><td><?= (float)($bill['consumption_units'] ?? 0) ?> units</td><td><?= formatCurrency($planUnitRate) ?>/unit</td><td class="amount"><?= formatCurrency(($planUnitRate) * ((float)($bill['consumption_units'] ?? 0))) ?></td></tr>
                                    </tbody>
                                    <tfoot><tr><td colspan="3" style="text-align:right">Subtotal (Amount Due)</td><td class="amount"><?= formatCurrency($bill['amount_due'] ?? 0) ?></td></tr></tfoot>
                                </table>
                            </div>
                            <div class="totals">
                                <div class="total-card"><span>Amount Paid</span><span class="amount" id="amountPaidValue"><?= formatCurrency($bill['amount_paid'] ?? 0) ?></span></div>
                                <div class="total-card"><span>Outstanding Balance</span><span class="amount" id="balanceValueTop"><?= formatCurrency($bill['balance'] ?? max(($bill['amount_due'] ?? 0) - ($bill['amount_paid'] ?? 0), 0)) ?></span></div>
                            </div>
                        </div>
                        <div class="chart-section"><div class="chart-card"><div class="chart-title">Consumption Trend</div><canvas id="consumptionTrend" height="120"></canvas></div></div>
                    </div>
                </div>
                <div class="content-section">
                    <div class="section-header"><h3 class="section-title"><i class="fas fa-history"></i> Payment History</h3></div>
                    <?php if (empty($payments)): ?>
                        <div class="alert"><i class="fas fa-info-circle"></i> No payments recorded for this bill.</div>
                    <?php else: ?>
                        <div class="table-responsive"><table class="data-table">
                            <thead><tr><th>Payment ID</th><th>Date</th><th>Amount</th><th>Method</th><th>Transaction ID</th><th>Notes</th></tr></thead>
                            <tbody>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td><?= (int)$p['id'] ?></td>
                                    <td><?= htmlspecialchars(date('d M Y H:i', strtotime($p['payment_date'] ?? date('Y-m-d H:i')))) ?></td>
                                    <td><?= formatCurrency($p['amount'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($p['payment_method'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($p['transaction_id'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($p['notes'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody></table></div>
                    <?php endif; ?>
                </div>
                <?php if (strtolower($bill['payment_status'] ?? '') !== 'paid'): ?>
                <div class="content-section">
                    <div class="section-header" style="display:flex;justify-content:space-between;align-items:center;gap:.5rem"><h3 class="section-title"><i class="fas fa-money-bill-wave"></i> Record Payment</h3><div style="display:flex;gap:.5rem;align-items:center"><select id="prefillModeAdmin" class="form-select" style="width:auto"><option value="balance">Remaining Balance</option><option value="total_paid">Total Paid</option></select><button type="button" id="prefillPaymentBtnAdmin" class="btn btn-outline"><i class="fas fa-magic"></i> Prefill</button></div></div>
                    <form method="post" action="index.php?page=record_payment&bill_id=<?= (int)$bill['id'] ?>" style="display:grid;gap:1rem;max-width:480px">
                        <div>
                            <label>Amount Paid by Client</label>
                            <div style="display:flex;align-items:center;gap:.5rem">
                                <span style="padding:.4rem .6rem;border:1px solid var(--border-color);border-radius:.5rem;background:#1f1f2e">KES</span>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" value="<?= (float)($bill['balance'] ?? max(($bill['amount_due'] ?? 0) - ($bill['amount_paid'] ?? 0), 0)) ?>" required>
                            </div>
                            <div style="margin-top:.35rem;color:var(--text-muted);font-size:.875rem">Maximum payment (info): <?= formatCurrency($bill['balance'] ?? max(($bill['amount_due'] ?? 0) - ($bill['amount_paid'] ?? 0), 0)) ?></div>
                        </div>
                        <div>
                            <label>Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Select payment method</option>
                                <option value="cash">Cash</option>
                                <option value="mpesa">M-Pesa STK-Push</option>
                            </select>
                        </div>
                        <div>
                            <label>Transaction ID</label>
                            <input type="text" class="form-control" id="transaction_id" name="transaction_id" placeholder="Optional">
                            <div style="margin-top:.35rem;color:var(--text-muted);font-size:.875rem">Required for M-Pesa, bank transfers, etc.</div>
                        </div>
                        <div>
                            <label>Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Optional"></textarea>
                        </div>
                        <button type="submit" name="record_payment" class="btn btn-primary"><i class="fas fa-money-bill-wave"></i> Record Payment</button>
                    </form>
                </div>
                <?php endif; ?>
                <div class="content-section">
                    <div class="section-header"><h3 class="section-title"><i class="fas fa-tools"></i> Actions</h3></div>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                        <button class="btn btn-outline" onclick="printBill()"><i class="fas fa-print"></i> Print Bill</button>
                        <button class="btn btn-outline" onclick="downloadPdf()"><i class="fas fa-file-pdf"></i> Download PDF</button>
                        <button class="btn btn-outline" onclick="savePdfToStorage()"><i class="fas fa-cloud-download-alt"></i> Save to Storage</button>
                        <button class="send-client-btn" onclick="openSendDialog()"><i class="fas fa-paper-plane"></i> Send Bill to Client</button>
                        <button class="btn btn-outline" onclick="emailBill()"><i class="fas fa-envelope"></i> Email to Client</button>
                        <button class="btn btn-outline" onclick="sendReminder()"><i class="fas fa-bell"></i> Send Payment Reminder</button>
                        <?php if (strtolower($bill['payment_status'] ?? '') !== 'confirmed_and_verified'): ?>
                            <button class="btn btn-primary" id="openVerifyBillDialog" data-bill-id="<?= (int)$bill['id'] ?>"><i class="fas fa-check-circle"></i> Verify Bill</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const toggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const main = document.getElementById('mainContent');
        if(toggle){ toggle.addEventListener('click', function(){ sidebar.classList.toggle('collapsed'); main.classList.toggle('full-width'); }); }
        const history = <?= json_encode($consumptionHistory ?? []) ?>;
        try {
            if (Array.isArray(history) && history.length) {
                const labels = history.map(r => r.bill_date);
                const data = history.map(r => parseFloat(r.consumption_units));
                const ctx = document.getElementById('consumptionTrend').getContext('2d');
                new Chart(ctx, { type:'line', data:{ labels, datasets:[{ label:'Units', data, borderColor:'#ff4757', backgroundColor:'rgba(255,71,87,0.15)', tension:0.3, fill:true, pointRadius:2, borderWidth:2 }] }, options:{ responsive:true, plugins:{ legend:{ display:false } }, scales:{ x:{ grid:{ display:false } }, y:{ beginAtZero:true } } } });
            }
        } catch(e){}
        const verifyBtn = document.getElementById('openVerifyBillDialog');
        if (verifyBtn) verifyBtn.addEventListener('click', openVerifyDialog);

        const prefillBtn = document.getElementById('prefillPaymentBtnAdmin');
        if (prefillBtn) {
            prefillBtn.addEventListener('click', function(){
                const amtEl = document.getElementById('amount');
                const methodEl = document.getElementById('payment_method');
                const txEl = document.getElementById('transaction_id');
                const notesEl = document.getElementById('notes');
                if (!amtEl || !methodEl || !txEl || !notesEl) return;
                const amtPaid = <?= json_encode((float)($bill['amount_paid'] ?? 0)) ?>;
                const remaining = <?= json_encode((float)max(($bill['amount_due'] ?? 0) - ($bill['amount_paid'] ?? 0), 0)) ?>;
                const modeEl = document.getElementById('prefillModeAdmin');
                const mode = modeEl ? modeEl.value : 'balance';
                const fillVal = mode === 'total_paid' ? amtPaid : remaining;
                amtEl.value = (isNaN(fillVal) ? 0 : fillVal).toFixed(2);
                methodEl.value = methodEl.querySelector('option[value="cash"]') ? 'cash' : (methodEl.querySelector('option[value="mpesa"]') ? 'mpesa' : 'other');
                <?php 
                $latestTx = '';
                if (!empty($payments) && is_array($payments)) {
                    foreach ($payments as $pp) { if (!empty($pp['transaction_id'])) { $latestTx = $pp['transaction_id']; break; } }
                }
                ?>
                txEl.value = <?= json_encode($latestTx) ?>;
                const tx = txEl.value.trim();
                let chosen = methodEl.value;
                if (/^(ws_|mpesa)/i.test(tx)) {
                    chosen = methodEl.querySelector('option[value="mpesa"]') ? 'mpesa' : chosen;
                } else if (/^[A-Z0-9]{10,}$/i.test(tx)) {
                    chosen = methodEl.querySelector('option[value="bank_transfer"]') ? 'bank_transfer' : chosen;
                }
                methodEl.value = chosen;
                notesEl.value = 'Payment for Bill #' + <?= (int)$bill['id'] ?> + ' (' + 'Client: ' + <?= json_encode(($client['full_name'] ?? $client['username'] ?? '')) ?> + ', Meter: ' + <?= json_encode(($meter['serial_number'] ?? 'N/A')) ?> + ')';
            });
        }
    });
    function printBill(){ window.print(); }
    function emailBill(){ alert('Email functionality will be implemented later.'); }
    function sendReminder(){ alert('Reminder functionality will be implemented later.'); }
    async function downloadPdf(){
        const billId = <?= (int)$bill['id'] ?>;
        const el = document.querySelector('.invoice');
        const canvas = await html2canvas(el, { scale: 2 });
        const imgData = canvas.toDataURL('image/png');
        const { jsPDF } = window.jspdf; const pdf = new jsPDF('p','mm','a4');
        const pageW = pdf.internal.pageSize.getWidth(); const pageH = pdf.internal.pageSize.getHeight(); const margin = 10;
        let imgW = pageW - margin*2; let imgH = canvas.height * imgW / canvas.width; let x = margin; let y = margin;
        if (imgH > pageH - margin*2) { const scale = (pageH - margin*2) / imgH; imgW = imgW * scale; imgH = imgH * scale; x = (pageW - imgW)/2; y = margin; }
        pdf.addImage(imgData, 'PNG', x, y, imgW, imgH); pdf.save('bill-' + billId + '.pdf');
    }
    async function savePdfToStorage(){
        const billId = <?= (int)$bill['id'] ?>; const el = document.querySelector('.invoice');
        const canvas = await html2canvas(el, { scale: 2 }); const imgData = canvas.toDataURL('image/png');
        const { jsPDF } = window.jspdf; const pdf = new jsPDF('p','mm','a4');
        const pageW = pdf.internal.pageSize.getWidth(); const pageH = pdf.internal.pageSize.getHeight(); const margin = 10;
        let imgW = pageW - margin*2; let imgH = canvas.height * imgW / canvas.width; let x = margin; let y = margin;
        if (imgH > pageH - margin*2) { const scale = (pageH - margin*2) / imgH; imgW = imgW * scale; imgH = imgH * scale; x = (pageW - imgW)/2; y = margin; }
        pdf.addImage(imgData, 'PNG', x, y, imgW, imgH);
        const dataUri = pdf.output('datauristring'); const base64 = dataUri.split(',')[1];
        try { const res = await fetch('index.php?page=billing_store_bill_pdf', { method:'POST', headers:{ 'Content-Type':'application/json' }, body: JSON.stringify({ bill_id: billId, pdf_base64: base64 }) }); const json = await res.json(); alert(json.message || 'Saved'); } catch(e){ alert('Save failed'); }
    }
    async function savePdfToStorageAndReturnPath(){
        const billId = <?= (int)$bill['id'] ?>; const el = document.querySelector('.invoice');
        const canvas = await html2canvas(el, { scale: 2 }); const imgData = canvas.toDataURL('image/png');
        const { jsPDF } = window.jspdf; const pdf = new jsPDF('p','mm','a4');
        const pageW = pdf.internal.pageSize.getWidth(); const pageH = pdf.internal.pageSize.getHeight(); const margin = 10;
        let imgW = pageW - margin*2; let imgH = canvas.height * imgW / canvas.width; let x = margin; let y = margin;
        if (imgH > pageH - margin*2) { const scale = (pageH - margin*2) / imgH; imgW = imgW * scale; imgH = imgH * scale; x = (pageW - imgW)/2; y = margin; }
        pdf.addImage(imgData, 'PNG', x, y, imgW, imgH); const dataUri = pdf.output('datauristring'); const base64 = dataUri.split(',')[1];
        const res = await fetch('index.php?page=billing_store_bill_pdf', { method:'POST', headers:{ 'Content-Type':'application/json' }, body: JSON.stringify({ bill_id: billId, pdf_base64: base64 }) }); const json = await res.json(); if (!json || !json.path) throw new Error('Save failed'); return json.path;
    }
    (function(){
        const btn=document.getElementById('exportCsvBtn');
        if(!btn) return;
        btn.addEventListener('click', function(){
            let csv='Bill Details\n';
            const meta=document.querySelector('.invoice-meta');
            if(meta){ const items=[...meta.querySelectorAll('div')].map(d=>d.innerText||''); csv += items.join(',') + '\n'; }
            const rows=document.querySelectorAll('.breakdown-table tr');
            rows.forEach(r=>{ const cols=[...r.querySelectorAll('th,td')].map(c=>'"'+(c.innerText||'').replace(/"/g,'""')+'"'); csv += cols.join(',') + '\n'; });
            const hist=document.querySelector('.content-section .data-table');
            if(hist){ csv += '\nPayment History\n'; const hr=[...hist.querySelectorAll('tr')]; hr.forEach(r=>{ const cols=[...r.querySelectorAll('th,td')].map(c=>'"'+(c.innerText||'').replace(/"/g,'""')+'"'); csv += cols.join(',') + '\n'; }); }
            const blob=new Blob([csv],{type:'text/csv;charset=utf-8;'});
            const url=URL.createObjectURL(blob);
            const a=document.createElement('a'); a.href=url; a.download='bill-'+<?= (int)$bill['id'] ?>+'.csv'; document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
        });
    })();
    async function saveImageToStorageAndReturnPath(){
        const billId = <?= (int)$bill['id'] ?>; const el = document.querySelector('.invoice');
        const canvas = await html2canvas(el, { scale: 2 }); const png = canvas.toDataURL('image/png'); const base64 = png.split(',')[1];
        const res = await fetch('index.php?page=billing_store_bill_image', { method:'POST', headers:{ 'Content-Type':'application/json' }, body: JSON.stringify({ bill_id: billId, image_base64: base64 }) }); const json = await res.json(); if (!json || !json.path) throw new Error('Save failed'); return json.path;
    }
    async function openSendDialog(){
        const billId = <?= (int)$bill['id'] ?>;
        const dlg = document.createElement('div'); dlg.style.position='fixed'; dlg.style.inset='0'; dlg.style.background='rgba(0,0,0,0.55)'; dlg.style.display='flex'; dlg.style.alignItems='center'; dlg.style.justifyContent='center'; dlg.style.zIndex='2000';
        const box = document.createElement('div'); box.style.background='#1f1f2e'; box.style.color='#f8f9fa'; box.style.width='420px'; box.style.maxWidth='90vw'; box.style.border='1px solid var(--border-color)'; box.style.borderRadius='12px'; box.style.boxShadow='0 20px 60px rgba(0,0,0,0.3)'; box.style.padding='16px 18px';
        box.innerHTML = '<div style="font-weight:600;margin-bottom:8px">Send Bill</div><div style="font-size:14px;color:#a1a5b7">Send this bill to the client?</div>';
        const progress = document.createElement('div'); progress.style.marginTop='12px'; progress.style.display='none'; progress.style.alignItems='center'; progress.style.gap='10px'; progress.innerHTML='<div style="width:22px;height:22px;border:3px solid #93c5fd;border-top-color:#6366f1;border-radius:50%;animation:spin .9s linear infinite"></div><div style="font-size:14px;color:#6366f1">Processing…</div>';
        const actions = document.createElement('div'); actions.style.marginTop='12px'; actions.style.display='flex'; actions.style.justifyContent='flex-end'; actions.style.gap='10px';
        const cancelBtn = document.createElement('button'); cancelBtn.className='btn btn-outline'; cancelBtn.textContent='Cancel';
        const okBtn = document.createElement('button'); okBtn.className='btn btn-primary'; okBtn.textContent='Send';
        actions.appendChild(cancelBtn); actions.appendChild(okBtn); box.appendChild(progress); box.appendChild(actions); dlg.appendChild(box); document.body.appendChild(dlg);
        cancelBtn.onclick=function(){ document.body.removeChild(dlg); };
        okBtn.onclick=async function(){
            okBtn.disabled=true; progress.style.display='flex';
            try {
                const pdfPath = await savePdfToStorageAndReturnPath();
                const imagePath = await saveImageToStorageAndReturnPath();
                const res = await fetch('index.php?page=billing_send_bill_to_client', { method:'POST', headers:{ 'Content-Type':'application/json' }, body: JSON.stringify({ bill_id: billId, pdf_path: pdfPath, image_path: imagePath }) });
                const json = await res.json(); alert(json.message || 'Sent'); document.body.removeChild(dlg);
            } catch(e){ alert('Failed'); okBtn.disabled=false; progress.style.display='none'; }
        };
    }
    async function openVerifyDialog(){
        const billId = <?= (int)$bill['id'] ?>;
        const dlg = document.createElement('div'); dlg.style.position='fixed'; dlg.style.inset='0'; dlg.style.background='rgba(0,0,0,0.55)'; dlg.style.display='flex'; dlg.style.alignItems='center'; dlg.style.justifyContent='center'; dlg.style.zIndex='2000';
        const box = document.createElement('div'); box.style.background='#1f1f2e'; box.style.color='#f8f9fa'; box.style.width='420px'; box.style.maxWidth='90vw'; box.style.border='1px solid var(--border-color)'; box.style.borderRadius='12px'; box.style.boxShadow='0 20px 60px rgba(0,0,0,0.3)'; box.style.overflow='hidden';
        const head = document.createElement('div'); head.style.padding='16px 18px'; head.style.borderBottom='1px solid var(--border-color)'; head.style.display='flex'; head.style.justifyContent='space-between'; head.innerHTML='<div style="font-weight:600">Confirm Bill Verification</div>';
        const close = document.createElement('button'); close.textContent='×'; close.style.background='none'; close.style.border='none'; close.style.color='#a1a5b7'; close.style.fontSize='18px'; close.style.cursor='pointer'; head.appendChild(close);
        const body = document.createElement('div'); body.style.padding='16px 18px'; body.innerHTML='<div style="font-size:14px;color:#a1a5b7">Has the client paid the full amount?</div>';
        const row = document.createElement('div'); row.style.marginTop='10px'; row.style.display='flex'; row.style.gap='16px'; row.style.flexWrap='wrap';
        const yes = document.createElement('input'); yes.type='checkbox'; const no = document.createElement('input'); no.type='checkbox';
        const yesWrap = document.createElement('label'); yesWrap.style.display='inline-flex'; yesWrap.style.alignItems='center'; yesWrap.style.gap='8px'; yesWrap.appendChild(yes); yesWrap.appendChild(document.createTextNode('Yes'));
        const noWrap = document.createElement('label'); noWrap.style.display='inline-flex'; noWrap.style.alignItems='center'; noWrap.style.gap='8px'; noWrap.appendChild(no); noWrap.appendChild(document.createTextNode('No'));
        row.appendChild(yesWrap); row.appendChild(noWrap);
        const amtRow = document.createElement('div'); amtRow.style.marginTop='10px'; amtRow.style.display='none';
        const amtLabel = document.createElement('label'); amtLabel.textContent='Enter amount paid'; amtLabel.style.display='block'; amtLabel.style.fontSize='13px'; amtLabel.style.color='#a1a5b7'; amtLabel.style.marginBottom='6px';
        const amtInput = document.createElement('input'); amtInput.type='number'; amtInput.min='0.01'; amtInput.step='0.01'; amtInput.className='form-control';
        const amtErr = document.createElement('div'); amtErr.style.marginTop='6px'; amtErr.style.color='#ee5253'; amtErr.style.fontSize='13px'; amtErr.style.display='none'; amtErr.textContent='Please enter a valid amount not exceeding total due.';
        amtRow.appendChild(amtLabel); amtRow.appendChild(amtInput); amtRow.appendChild(amtErr);
        const progress = document.createElement('div'); progress.style.marginTop='12px'; progress.style.display='none'; progress.style.alignItems='center'; progress.style.gap='10px'; progress.innerHTML='<div style="width:22px;height:22px;border:3px solid #93c5fd;border-top-color:#6366f1;border-radius:50%;animation:spin .9s linear infinite"></div><div style="font-size:14px;color:#6366f1">Processing…</div>';
        const successBar = document.createElement('div'); successBar.className='verify-success'; successBar.innerHTML='<i class="fas fa-check-circle"></i><span>Bill verified successfully</span>';
        const actions = document.createElement('div'); actions.style.padding='12px 18px'; actions.style.borderTop='1px solid var(--border-color)'; actions.style.display='flex'; actions.style.justifyContent='flex-end'; actions.style.gap='10px';
        const cancelBtn = document.createElement('button'); cancelBtn.className='btn btn-outline'; cancelBtn.textContent='Cancel';
        const okBtn = document.createElement('button'); okBtn.className='btn btn-primary'; okBtn.textContent='Verify';
        actions.appendChild(cancelBtn); actions.appendChild(okBtn);
        body.appendChild(row); body.appendChild(amtRow); body.appendChild(progress); body.appendChild(successBar);
        box.appendChild(head); box.appendChild(body); box.appendChild(actions); dlg.appendChild(box); document.body.appendChild(dlg);
        yes.addEventListener('change', function(){ if (yes.checked){ no.checked=false; amtRow.style.display='none'; } });
        no.addEventListener('change', function(){ if (no.checked){ yes.checked=false; amtRow.style.display='block'; } else { amtRow.style.display='none'; } });
        cancelBtn.onclick=function(){ document.body.removeChild(dlg); };
        close.onclick=function(){ document.body.removeChild(dlg); };
        okBtn.onclick=async function(){
            okBtn.disabled=true; progress.style.display='flex';
            try {
                let fullPaid = yes.checked ? 'yes' : 'no';
                let paidAmt = null;
                if (!yes.checked && !no.checked){ amtErr.style.display='block'; okBtn.disabled=false; progress.style.display='none'; return; }
                if (no.checked){ const val = parseFloat(amtInput.value || '0'); const totalDue = parseFloat(<?= json_encode((float)($bill['amount_due'] ?? 0)) ?>); if (isNaN(val) || val<=0 || val>totalDue){ amtErr.style.display='block'; okBtn.disabled=false; progress.style.display='none'; return; } amtErr.style.display='none'; paidAmt = val; }
                const formBody = new URLSearchParams(); formBody.append('bill_id', String(billId)); formBody.append('ajax', '1'); formBody.append('full_paid', fullPaid); if (paidAmt!==null) formBody.append('paid_amount', String(paidAmt));
                const res = await fetch('index.php?page=finance_manager_verify_bill', { method:'POST', headers:{ 'Content-Type':'application/x-www-form-urlencoded' }, body: formBody.toString() });
                const json = await res.json(); successBar.style.display='inline-flex'; setTimeout(function(){ document.body.removeChild(dlg); location.reload(); }, 1200);
            } catch(e){ alert('Verification failed'); okBtn.disabled=false; progress.style.display='none'; }
        };
    }
    </script>
</body>
</html>
