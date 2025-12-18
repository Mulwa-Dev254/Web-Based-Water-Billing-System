<?php
// Normalize inputs from controller-provided $data
$reportType = $data['reportType'] ?? ($reportType ?? 'clients');
$startDate = $data['startDate'] ?? ($startDate ?? date('Y-m-01'));
$endDate = $data['endDate'] ?? ($endDate ?? date('Y-m-t'));
$report = $data['report'] ?? ($report ?? false);
$report_title = $data['report_title'] ?? ($report_title ?? '');
$report_summary = $data['report_summary'] ?? ($report_summary ?? []);
$report_columns = $data['report_columns'] ?? ($report_columns ?? []);
$report_data = $data['report_data'] ?? ($report_data ?? []);
$chart_type = $data['chart_type'] ?? ($chart_type ?? '');
$chart_data = $data['chart_data'] ?? ($chart_data ?? []);
$error = $data['error'] ?? ($error ?? '');
$success = $data['success'] ?? ($success ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Financial Reports</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root{--primary:#ff4757;--primary-dark:#e84118;--dark-bg:#1e1e2d;--sidebar-bg:#1a1a27;--card-bg:#2a2a3c;--text-light:#f8f9fa;--text-muted:#a1a5b7;--border-color:#2d2d3a}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background-color:var(--dark-bg);color:var(--text-light);line-height:1.6;display:flex;min-height:100vh;overflow-x:hidden}
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
        .card{background-color:var(--card-bg);border:1px solid var(--border-color);border-radius:.75rem;box-shadow:0 0 20px rgba(0,0,0,.1);margin:1rem 1rem 2rem}
        .card-header{padding:1rem 1.25rem;border-bottom:1px solid var(--border-color);display:flex;align-items:center;gap:.75rem;color:var(--text-light);font-weight:700}
        .card-body{padding:1rem 1.25rem}
        .filter{display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:0}
        .filter label{color:var(--text-muted);font-size:.85rem}
        .filter input,.filter select{padding:.55rem .7rem;border-radius:.5rem;border:1px solid var(--border-color);background:#1f1f2e;color:var(--text-light)}
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem .9rem;border-radius:.5rem;border:1px solid var(--border-color);background:transparent;color:var(--text-light);cursor:pointer}
        .btn-primary{background-color:var(--primary);border-color:var(--primary)}
        .btn-green{background:#16a34a;border-color:#15803d;color:#fff}
        .grid-2{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1.5rem;margin-bottom:2rem}
        .table-responsive{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch}
        .report-table{width:100%;min-width:800px;border-collapse:collapse}
        .report-table thead th{padding:.75rem 1rem;color:var(--text-muted);border-bottom:1px solid var(--border-color);text-transform:uppercase;font-size:.75rem;background:#232336}
        .report-table tbody td{padding:1rem;border-bottom:1px solid var(--border-color);color:var(--text-light)}
        .summary-card{background:#232336;border:1px solid var(--border-color);border-radius:.75rem;padding:1rem}
        @media(max-width:992px){.main-content{margin-left:0}.dashboard-layout{flex-direction:column}}
        @media(max-width:768px){.dashboard-container{padding:1rem}.header-bar{padding:1rem}}
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php $page = 'finance_manager_reports'; include __DIR__ . '/../includes/admin_sidebar.php'; ?>

        <div class="main-content" id="mainContent">
            <div class="header-bar">
                <div class="header-title" style="display:flex;align-items:center;gap:1rem">
                    <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1 class="page-title">Financial Reports</h1>
                        <p class="page-subtitle">Generate and analyze financial performance</p>
                    </div>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error" role="alert"><p><?= htmlspecialchars($error) ?></p></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert"><p><?= htmlspecialchars($success) ?></p></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">Generate Report</div>
                <div class="card-body">
                <form method="GET" action="index.php?page=finance_manager_reports" class="filter">
                    <input type="hidden" name="page" value="finance_manager_reports" />
                    <div>
                        <label>Report Type</label>
                        <select name="report_type" required>
                            <option value="clients" <?= $reportType==='clients'?'selected':'' ?>>Payments by Clients</option>
                            <option value="revenue" <?= $reportType==='revenue'?'selected':'' ?>>Revenue</option>
                            <option value="flagged" <?= $reportType==='flagged'?'selected':'' ?>>Flagged Transactions</option>
                            <option value="bills" <?= $reportType==='bills'?'selected':'' ?>>Bills</option>
                        </select>
                    </div>
                    <div>
                        <label>Start Date</label>
                        <input type="date" name="date_from" value="<?= htmlspecialchars($startDate) ?>" required />
                    </div>
                    <div>
                        <label>End Date</label>
                        <input type="date" name="date_to" value="<?= htmlspecialchars($endDate) ?>" required />
                    </div>
                    <div style="align-self:flex-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Generate</button>
                    </div>
                </form>
                </div>
            </div>

            <div class="grid-2">
                <div class="card">
                    <div class="card-header">Summary</div>
                    <div class="card-body">
                    <?php if ($report && !empty($report_summary)): ?>
                        <div class="grid grid-cols-2 gap-4">
                            <?php foreach(($report_summary ?? []) as $k=>$v): ?>
                                <div class="summary-card">
                                    <div style="color:#c7d2fe;font-weight:600;"><?= htmlspecialchars($k) ?></div>
                                    <div style="font-size:1.25rem;font-weight:800;margin-top:.35rem;color:#f8f9fa">
                                        <?= is_numeric($v) ? number_format((float)$v,2) : htmlspecialchars((string)$v) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="page-subtitle">Select a report type and date range, then generate.</p>
                    <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Chart</div>
                    <div class="card-body"><div style="height:240px"><canvas id="reportChart"></canvas></div></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="justify-content:space-between">
                    <span>Report Results</span>
                    <div style="display:flex;gap:.5rem">
                        <button id="exportPdf" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Export PDF</button>
                        <button onclick="exportCSV()" class="btn btn-green"><i class="fas fa-file-csv"></i> Export CSV</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="reportTable" class="report-table">
                            <thead>
                                <tr>
                                    <?php foreach(($report_columns ?? []) as $c): ?>
                                        <th><?= htmlspecialchars($c) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach(($report_data ?? []) as $r): ?>
                                    <tr>
                                        <?php foreach($r as $cell): ?>
                                            <td><?= is_numeric($cell) ? number_format((float)$cell,2) : htmlspecialchars((string)$cell) ?></td>
                                        <?php endforeach; ?>
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
        const chartType = <?= json_encode($chart_type) ?>;
        const chartData = <?= json_encode($chart_data) ?>;
        const ctx = document.getElementById('reportChart');
        if (ctx && chartType && chartData && chartData.labels && chartData.dataset_data) {
            const config = {
                type: chartType === 'line' ? 'line' : 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: chartData.dataset_label || 'Dataset',
                        data: chartData.dataset_data,
                        backgroundColor: chartType === 'line' ? 'rgba(255,71,87,0.25)' : 'rgba(255,71,87,0.6)',
                        borderColor: 'rgba(255,71,87,1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: chartType === 'line'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,.15)' }, ticks: { color:'#e5e7eb' } },
                        x: { grid: { color: 'rgba(148,163,184,.10)' }, ticks: { color:'#e5e7eb' } }
                    }
                }
            };
            new Chart(ctx, config);
        }

        function exportCSV() {
            const table = document.getElementById('reportTable');
            if (!table) return;
            let csv = '';
            const rows = table.querySelectorAll('tr');
            rows.forEach((row) => {
                const cols = row.querySelectorAll('th, td');
                const line = Array.from(cols).map(c => '"' + (c.innerText || '').replace(/"/g, '""') + '"').join(',');
                csv += line + '\n';
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'financial-report.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
        document.getElementById('exportPdf')?.addEventListener('click', async function(){
            try {
                const el = document.querySelector('#mainContent');
                const canvas = await html2canvas(el, { scale: 2 });
                const imgData = canvas.toDataURL('image/png');
                const { jsPDF } = window.jspdf; const pdf = new jsPDF('p','mm','a4');
                const pageW = pdf.internal.pageSize.getWidth(); const pageH = pdf.internal.pageSize.getHeight(); const margin = 10;
                let imgW = pageW - margin*2; let imgH = canvas.height * imgW / canvas.width; let x = margin; let y = margin;
                if (imgH > pageH - margin*2) { const scale = (pageH - margin*2) / imgH; imgW = imgW * scale; imgH = imgH * scale; x = (pageW - imgW)/2; y = margin; }
                pdf.addImage(imgData, 'PNG', x, y, imgW, imgH);
                const fn = 'financial-report-' + (new Date().toISOString().slice(0,10)) + '.pdf';
                pdf.save(fn);
            } catch(e) { alert('PDF export failed'); }
        });
    </script>
</body>
</html>
