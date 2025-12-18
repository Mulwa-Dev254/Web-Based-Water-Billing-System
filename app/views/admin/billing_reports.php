<?php
// Admin Billing Reports view (mirrors finance_manager/billing_reports.php functionality, admin theme)
if (!isset($auth) && isset($data['auth'])) { $auth = $data['auth']; }
$reportType = $data['reportType'] ?? ($reportType ?? 'client');
$clientId = $data['clientId'] ?? ($clientId ?? 'all');
$startDate = $data['startDate'] ?? ($startDate ?? date('Y-m-01'));
$endDate = $data['endDate'] ?? ($endDate ?? date('Y-m-t'));
$reportData = $data['reportData'] ?? ($reportData ?? []);
$clients = $data['clients'] ?? ($clients ?? []);
$summary = $data['summary'] ?? ($summary ?? []);
$autoClientHistory = $data['autoClientHistory'] ?? ($autoClientHistory ?? []);
$autoOutstanding = $data['autoOutstanding'] ?? ($autoOutstanding ?? []);
$recentPayment = $data['recentPayment'] ?? ($recentPayment ?? []);
$error = $data['error'] ?? ($error ?? '');
$success = $data['success'] ?? ($success ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Billing Reports</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--primary:#ff4757;--primary-dark:#e84118;--dark-bg:#1e1e2d;--darker-bg:#151521;--sidebar-bg:#1a1a27;--card-bg:#2a2a3c;--text-light:#f8f9fa;--text-muted:#a1a5b7;--border-color:#2d2d3a}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background-color:var(--dark-bg);color:var(--text-light);line-height:1.6;display:flex;min-height:100vh;overflow-x:hidden}
        a{text-decoration:none;color:inherit}
        .dashboard-layout{display:flex;width:100%;min-height:100vh}
        .sidebar{width:280px;background-color:var(--sidebar-bg);padding:1.5rem 0;display:flex;flex-direction:column;position:fixed;height:100vh;top:0;left:0;z-index:1000;transition:transform .3s;border-right:1px solid var(--border-color);box-shadow:0 0 15px rgba(0,0,0,.1)}
        .sidebar.hidden{transform:translateX(-280px)}
        .main-content{margin-left:280px;flex-grow:1;min-height:100vh;transition:margin-left .3s;min-width:0}
        .main-content.full-width{margin-left:0}
        .header-bar{background-color:var(--sidebar-bg);padding:1.25rem 2rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border-color);position:sticky;top:0;z-index:100}
        .header-title{display:flex;align-items:center;gap:1rem}
        .header-title h1{font-size:1.5rem;font-weight:600;color:var(--text-light);margin:0}
        .page-subtitle{color:var(--text-muted);margin-top:.25rem}
        .sidebar-toggle{background-color:var(--primary);color:#fff;border:none;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer}
        .dashboard-container{padding:2rem}
        .content-section{background-color:var(--card-bg);padding:2rem;border-radius:.75rem;box-shadow:0 0 20px rgba(0,0,0,.1);margin:1rem 0 2rem;border:1px solid var(--border-color)}
        .section-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid var(--border-color)}
        .section-title{color:var(--primary);font-size:1.4rem;font-weight:600;display:flex;align-items:center;gap:.6rem}
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem .9rem;border-radius:.5rem;border:1px solid var(--border-color);background:transparent;color:var(--text-light);cursor:pointer}
        .btn-primary{background-color:var(--primary);border-color:var(--primary)}
        .btn-green{background:#16a34a;border-color:#15803d;color:#fff}
        .filters-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem}
        .form-control,.form-select{width:100%;padding:.6rem .7rem;border-radius:.5rem;border:1px solid var(--border-color);background-color:#1f1f2e;color:var(--text-light)}
        .table-responsive{width:100%;overflow-x:auto}
        .data-table{width:100%;min-width:720px;border-collapse:collapse;table-layout:auto}
        .data-table th,.data-table td{padding:.75rem;border-bottom:1px solid var(--border-color);text-align:left;white-space:nowrap;vertical-align:middle}
        .data-table th{color:var(--text-muted);font-weight:600;background-color:#232336}
        .status-badge{display:inline-flex;align-items:center;padding:.35rem .6rem;border-radius:10px;font-size:.72rem;font-weight:700}
        .status-paid{background:rgba(16,185,129,.12);color:#34d399;border:1px solid rgba(16,185,129,.35)}
        .status-partial{background:rgba(245,158,11,.12);color:#fbbf24;border:1px solid rgba(245,158,11,.35)}
        .status-pending{background:rgba(107,114,128,.12);color:#d1d5db;border:1px solid rgba(107,114,128,.35)}
        .status-overdue{background:rgba(239,68,68,.12);color:#f87171;border:1px solid rgba(239,68,68,.35)}
        @media(max-width:992px){.main-content{margin-left:0}.dashboard-layout{flex-direction:column}}
        @media(max-width:768px){.dashboard-container{padding:1rem}.content-section{padding:1.5rem}.header-bar{padding:1rem}}
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php $page = 'billing_reports'; include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="main-content" id="mainContent">
            <div class="header-bar">
                <div class="header-title">
                    <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1>Billing Reports</h1>
                        <p class="page-subtitle">Generate and analyze billing activity</p>
                    </div>
                </div>
                <div style="display:flex;gap:.5rem">
                    <button class="btn btn-primary" id="exportPDF"><i class="fas fa-file-pdf"></i> Export PDF</button>
                    <button class="btn btn-outline" id="printReport"><i class="fas fa-print"></i> Print</button>
                    <button class="btn btn-green" id="exportCSV"><i class="fas fa-file-csv"></i> Export CSV</button>
                </div>
            </div>
            <div class="dashboard-container">
                <?php if (!empty($summary) && is_array($summary)): ?>
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-chart-pie"></i> Summary</h2>
                    </div>
                    <div class="filters-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
                        <?php foreach ($summary as $key => $value): ?>
                            <div class="content-section" style="padding:1rem;border-radius:.5rem;margin:0;">
                                <div style="color:var(--text-muted);font-size:.85rem;"><?= ucwords(str_replace('_',' ', $key)) ?></div>
                                <div style="font-weight:700;font-size:1.2rem;color:var(--text-light);margin-top:.25rem;">
                                    <?= is_numeric($value) ? number_format((float)$value, 2) : htmlspecialchars((string)$value) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-chart-line"></i> Generate Report</h2>
                    </div>
                    <form method="GET" action="index.php?page=billing_reports">
                        <input type="hidden" name="page" value="billing_reports" />
                        <div class="filters-grid">
                            <div>
                                <label>Report Type</label>
                                <select name="report_type" class="form-select" required>
                                    <option value="monthly" <?= $reportType==='monthly'?'selected':'' ?>>Monthly Summary</option>
                                    <option value="client" <?= $reportType==='client'?'selected':'' ?>>Client Billing History</option>
                                    <option value="outstanding" <?= $reportType==='outstanding'?'selected':'' ?>>Outstanding</option>
                                    <option value="consumption" <?= $reportType==='consumption'?'selected':'' ?>>Consumption Analysis</option>
                                    <option value="payment" <?= $reportType==='payment'?'selected':'' ?>>Payment Collection</option>
                                </select>
                            </div>
                            <div>
                                <label>Client</label>
                                <select name="client_id" class="form-select">
                                    <option value="all" <?= $clientId==='all'?'selected':'' ?>>All</option>
                                    <?php foreach ($clients as $c): ?>
                                        <option value="<?= (int)$c['id'] ?>" <?= ((string)$clientId===(string)$c['id'])?'selected':'' ?>><?= htmlspecialchars($c['full_name'] ?? $c['username'] ?? $c['email'] ?? ('Client #'.$c['id'])) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label>Start Date</label>
                                <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="form-control" required />
                            </div>
                            <div>
                                <label>End Date</label>
                                <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="form-control" required />
                            </div>
                            <div style="align-self:end">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-magnifying-glass"></i> Generate Report</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-table"></i> Report Results</h2>
                    </div>
                    <div class="table-responsive">
                    <table class="data-table" id="reportTable">
                        <thead>
                        <tr>
                            <?php if ($reportType === 'monthly'): ?>
                                <th>Month</th><th>Bills Generated</th><th>Total Amount</th><th>Amount Collected</th><th>Outstanding</th><th>Collection Rate</th>
                            <?php elseif ($reportType === 'client'): ?>
                                <th>Client</th><th>Bill Date</th><th>Due Date</th><th>Consumption</th><th>Amount</th><th>Status</th>
                            <?php elseif ($reportType === 'outstanding'): ?>
                                <th>Client</th><th>Bill ID</th><th>Bill Date</th><th>Due Date</th><th>Days Overdue</th><th>Amount Due</th>
                            <?php elseif ($reportType === 'consumption'): ?>
                                <th>Client</th><th>Meter</th><th>Period</th><th>Consumption</th><th>Average Daily</th><th>Trend</th><th>Status</th>
                            <?php elseif ($reportType === 'payment'): ?>
                                <th>Date</th><th>Client</th><th>Bill ID</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <?php if ($reportType === 'monthly'): ?>
                                    <td><?= htmlspecialchars($row['month'] ?? date('M Y', strtotime($startDate))) ?></td>
                                    <td><?= (int)($row['bills_count'] ?? 0) ?></td>
                                    <td>KES <?= number_format((float)($row['total_amount'] ?? 0), 2) ?></td>
                                    <td>KES <?= number_format((float)($row['amount_collected'] ?? 0), 2) ?></td>
                                    <td>KES <?= number_format((float)($row['outstanding'] ?? 0), 2) ?></td>
                                    <td><?= is_numeric($row['collection_rate'] ?? null) ? number_format((float)$row['collection_rate'], 2) . '%' : htmlspecialchars((string)($row['collection_rate'] ?? '0%')) ?></td>
                                <?php elseif ($reportType === 'client'): ?>
                                    <td><?= htmlspecialchars($row['client_name']) ?></td>
                                    <td><?= date('d M Y', strtotime($row['bill_date'])) ?></td>
                                    <td><?= date('d M Y', strtotime($row['due_date'])) ?></td>
                                    <td><?= number_format(($row['consumption'] ?? $row['consumption_units'] ?? 0), 2) ?> units</td>
                                    <td>KES <?= number_format($row['amount_due'], 2) ?></td>
                                    <td><?php $st=strtolower($row['payment_status'] ?? ($row['status'] ?? 'pending')); $cls='status-info'; if(in_array($st,['paid','completed']))$cls='status-paid'; elseif(in_array($st,['partially_paid','partial']))$cls='status-partial'; elseif($st==='pending')$cls='status-pending'; elseif($st==='overdue')$cls='status-overdue'; ?><span class="status-badge <?= $cls ?>"><?= htmlspecialchars(ucfirst(str_replace('_',' ', ($row['payment_status'] ?? ($row['status'] ?? 'Pending'))))) ?></span></td>
                                <?php elseif ($reportType === 'outstanding'): ?>
                                    <td><?= htmlspecialchars($row['client_name']) ?></td>
                                    <td>#<?= (int)($row['id'] ?? $row['bill_id'] ?? 0) ?></td>
                                    <td><?= isset($row['bill_date']) ? date('d M Y', strtotime($row['bill_date'])) : '' ?></td>
                                    <td><?= isset($row['due_date']) ? date('d M Y', strtotime($row['due_date'])) : '' ?></td>
                                    <td><?= (int)($row['days_overdue'] ?? 0) ?> days</td>
                                    <td>KES <?= number_format(($row['amount_due'] ?? 0), 2) ?></td>
                                <?php elseif ($reportType === 'consumption'): ?>
                                    <td><?= htmlspecialchars($row['client_name']) ?></td>
                                    <td><?= htmlspecialchars($row['meter_serial']) ?></td>
                                    <td><?= $row['period'] ?></td>
                                    <td><?= number_format($row['consumption'], 2) ?> units</td>
                                    <td><?= number_format($row['daily_average'], 2) ?> units</td>
                                    <td><?php if ($row['trend'] > 0): ?><span class="status-badge status-overdue">▲ <?= number_format(abs($row['trend']),1) ?>%</span><?php elseif ($row['trend'] < 0): ?><span class="status-badge status-paid">▼ <?= number_format(abs($row['trend']),1) ?>%</span><?php else: ?><span class="status-badge status-pending">— 0%</span><?php endif; ?></td>
                                    <td><?php $st=strtolower($row['status'] ?? 'pending'); $cls='status-info'; if(in_array($st,['paid','completed']))$cls='status-paid'; elseif(in_array($st,['partially_paid','partial']))$cls='status-partial'; elseif($st==='pending')$cls='status-pending'; elseif($st==='overdue')$cls='status-overdue'; ?><span class="status-badge <?= $cls ?>"><?= htmlspecialchars(ucfirst(str_replace('_',' ', ($row['status'] ?? 'Pending')))) ?></span></td>
                                <?php elseif ($reportType === 'payment'): ?>
                                    <td><?= isset($row['payment_date']) ? date('d M Y', strtotime($row['payment_date'])) : '' ?></td>
                                    <td><?= htmlspecialchars($row['client_name'] ?? ($row['client_email'] ?? '')) ?></td>
                                    <td><?php 
                                        $bid = null; 
                                        if (isset($row['bill_id']) && $row['bill_id']) { $bid = (int)$row['bill_id']; }
                                        elseif (isset($row['reference_id']) && $row['reference_id']) { $bid = (int)$row['reference_id']; }
                                        echo ($bid !== null ? $bid : '—');
                                    ?></td>
                                    <td>KES <?= number_format(($row['amount'] ?? 0), 2) ?></td>
                                    <td><?= ucfirst($row['payment_method'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['transaction_id'] ?? 'N/A') ?></td>
                                    <td><?php $st=strtolower($row['status'] ?? 'pending'); $cls='status-info'; if(in_array($st,['paid','completed','confirmed_and_verified']))$cls='status-paid'; elseif(in_array($st,['partially_paid','partial']))$cls='status-partial'; elseif($st==='pending')$cls='status-pending'; elseif($st==='rejected')$cls='status-overdue'; ?><span class="status-badge <?= $cls ?>"><?= htmlspecialchars(ucfirst(str_replace('_',' ', ($row['status'] ?? 'Pending')))) ?></span></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-file-invoice-dollar"></i> Client Billing History (This Month)</h2>
                    </div>
                    <div class="table-responsive">
                    <table class="data-table" id="autoClientTable">
                        <thead><tr><th>Client</th><th>Bill Date</th><th>Due Date</th><th>Consumption</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ($autoClientHistory as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['client_name'] ?? '') ?></td>
                                <td><?= isset($row['bill_date']) ? date('d M Y', strtotime($row['bill_date'])) : '' ?></td>
                                <td><?= isset($row['due_date']) ? date('d M Y', strtotime($row['due_date'])) : '' ?></td>
                                <td><?= number_format(($row['consumption_units'] ?? 0), 2) ?> units</td>
                                <td>KES <?= number_format(($row['amount_due'] ?? 0), 2) ?></td>
                            <td><?php $st=strtolower($row['payment_status'] ?? 'pending'); $cls='status-info'; if(in_array($st,['paid','completed','confirmed_and_verified']))$cls='status-paid'; elseif(in_array($st,['partially_paid','partial']))$cls='status-partial'; elseif($st==='pending')$cls='status-pending'; elseif($st==='overdue')$cls='status-overdue'; ?><span class="status-badge <?= $cls ?>"><?= htmlspecialchars(ucfirst(str_replace('_',' ',($row['payment_status'] ?? 'Pending')))) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-exclamation-circle"></i> Outstanding Balances</h2>
                    </div>
                    <div class="table-responsive">
                    <table class="data-table" id="autoOutstandingTable">
                        <thead><tr><th>Client</th><th>Bill ID</th><th>Bill Date</th><th>Due Date</th><th>Days Overdue</th><th>Amount Due</th></tr></thead>
                        <tbody>
                            <?php foreach ($autoOutstanding as $r): $d=(int)($r['days_overdue']??0); ?>
                            <tr>
                                <td><?= htmlspecialchars($r['client_name'] ?? '') ?></td>
                                <td>#<?= (int)($r['bill_id'] ?? 0) ?></td>
                                <td><?= isset($r['bill_date']) ? date('d M Y', strtotime($r['bill_date'])) : '' ?></td>
                                <td><?= isset($r['due_date']) ? date('d M Y', strtotime($r['due_date'])) : '' ?></td>
                                <td><?= $d ?> days</td>
                                <td>KES <?= number_format(($r['amount_due'] ?? 0), 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
document.getElementById('exportCSV')?.addEventListener('click', function(){
    const tables = ['reportTable','autoClientTable','autoOutstandingTable'];
    let csv = '';
    tables.forEach(id => {
        const table = document.getElementById(id);
        if (!table) return;
        csv += '\n' + id + '\n';
        const rows = table.querySelectorAll('tr');
        rows.forEach((row) => {
            const cols = row.querySelectorAll('th, td');
            const line = Array.from(cols).map(c => '"' + (c.innerText || '').replace(/"/g, '""') + '"').join(',');
            csv += line + '\n';
        });
    });
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'billing-reports.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
});
document.getElementById('printReport')?.addEventListener('click', function(){ window.print(); });
document.getElementById('exportPDF')?.addEventListener('click', async function(){
    try {
        const el = document.querySelector('#mainContent');
        const canvas = await html2canvas(el, { scale: 2 });
        const imgData = canvas.toDataURL('image/png');
        const { jsPDF } = window.jspdf; const pdf = new jsPDF('p','mm','a4');
        const pageW = pdf.internal.pageSize.getWidth(); const pageH = pdf.internal.pageSize.getHeight(); const margin = 10;
        let imgW = pageW - margin*2; let imgH = canvas.height * imgW / canvas.width; let x = margin; let y = margin;
        if (imgH > pageH - margin*2) { const scale = (pageH - margin*2) / imgH; imgW = imgW * scale; imgH = imgH * scale; x = (pageW - imgW)/2; y = margin; }
        pdf.addImage(imgData, 'PNG', x, y, imgW, imgH);
        const fn = 'billing-report-' + (new Date().toISOString().slice(0,10)) + '.pdf';
        pdf.save(fn);
    } catch(e) { alert('PDF export failed'); }
});
document.addEventListener('DOMContentLoaded', function(){
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar') || document.querySelector('.sidebar');
    const mainContent = document.getElementById('mainContent');
    sidebarToggle?.addEventListener('click', function(){
        sidebar.classList.toggle('hidden');
        mainContent.classList.toggle('full-width');
    });
    window.addEventListener('resize', function(){
        if (window.innerWidth > 992) {
            sidebar.classList.remove('hidden');
            mainContent.classList.remove('full-width');
        }
    });
});
</script>
</body>
</html>
