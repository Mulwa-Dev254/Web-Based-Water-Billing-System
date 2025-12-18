<?php
// app/views/finance_manager/billing_reports.php

// Ensure this page is only accessible to finance managers and admins
// Using the auth object passed from the controller
if (!isset($_SESSION['user']) || !isset($auth) || !in_array($auth->getUserRole(), ['finance_manager', 'admin'])) {
    // Cannot use header redirect here as headers are already sent
    echo "<script>window.location.href = 'index.php?page=login';</script>";
    exit;
}

// Extract data from controller
$reportType = $data['reportType'] ?? 'monthly';
$clients = $data['clients'] ?? [];
$reportData = $data['reportData'] ?? [];
$startDate = $data['startDate'] ?? date('Y-m-01');
$endDate = $data['endDate'] ?? date('Y-m-t');
$clientId = $data['clientId'] ?? 0;
$summary = $data['summary'] ?? [];
$autoClientHistory = $data['autoClientHistory'] ?? [];
$autoOutstanding = $data['autoOutstanding'] ?? [];

// Derive report meta used by the view
$reportTitle = '';
switch ($reportType) {
    case 'monthly':
        $reportTitle = 'Monthly Billing Summary';
        break;
    case 'client':
        $reportTitle = 'Client Billing History';
        break;
    case 'consumption':
        $reportTitle = 'Consumption Analysis';
        break;
    case 'payment':
        $reportTitle = 'Payment Collection';
        break;
    case 'outstanding':
        $reportTitle = 'Outstanding Balances';
        break;
    default:
        $reportTitle = 'Billing Report';
}
$reportPeriod = date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate));
$reportSummary = $summary;

// Format currency function
function formatCurrency($amount) {
    return 'KSH ' . number_format($amount, 2);
}

// Get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'paid':
            return 'success';
        case 'pending':
            return 'warning';
        case 'partially_paid':
            return 'info';
        case 'overdue':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Reports - Water Billing System</title>
    <!-- Match Billing Dashboard styles -->
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <!-- Icons only -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- DataTables base CSS (non-Bootstrap) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --light: #f9fafb;
            --dark: #1f2937;
            --text-light: #f9fafb;
            --text-dark: #111827;
            --text-muted: #6b7280;
            --bg-light: #ffffff;
            --bg-dark: #111827;
            --border-color: #e5e7eb;
            --dark-bg: #1e1e2d; /* match dashboard.php */
            --sidebar-bg: #1a1a27; /* ensure sidebar uses dark theme */
            --header-bg: #ffffff;
            --header-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: var(--text-dark);
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }
        .text-muted { color: var(--text-muted); }

        .dashboard-container { display: flex; min-height: 100vh; }
        .main-content {
            margin-left: 16rem;
            padding: 2rem;
            width: calc(100% - 16rem);
            overflow-x: hidden;
            min-height: 100vh;
            position: relative;
            box-sizing: border-box;
        }
        @media (max-width: 1024px) { .main-content { padding: 1.5rem; } }
        @media (max-width: 768px) { .main-content { margin-left: 0; width: 100%; padding: 1rem; } }

        .app-header {
            background-color: var(--header-bg);
            box-shadow: var(--header-shadow);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0;
            border-radius: 0;
            position: sticky;
            top: 0; z-index: 1000;
        }
        .app-header .logo { font-size: 1.25rem; font-weight: 600; color: var(--primary); display: flex; align-items: center; gap: 0.5rem; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .page-title { font-size: 1.75rem; font-weight: 700; color: var(--text-dark); margin: 0; position: relative; }
        .page-title:after { content: ''; position: absolute; bottom: -8px; left: 0; width: 40px; height: 4px; background-color: var(--primary); border-radius: 2px; }

        .btn { display: inline-flex; align-items: center; justify-content: center; font-weight: 500; padding: 0.75rem 1.5rem; font-size: 0.875rem; border-radius: 0.5rem; transition: all 0.2s ease; cursor: pointer; border: 1px solid var(--border-color); background-color: #fff; color: var(--text-dark); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .btn i { margin-right: 0.5rem; }
        .btn-primary { color: #fff; background-color: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background-color: var(--primary-dark); border-color: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25); }
        .btn-outline { background-color: transparent; border: 1px solid var(--border-color); color: var(--text-dark); }
        .btn-outline:hover { background-color: rgba(79, 70, 229, 0.05); border-color: var(--primary); color: var(--primary); transform: translateY(-2px); }
        .btn-sm { padding: 0.5rem 0.75rem; font-size: 0.75rem; }

        .card { background-color: #fff; border: 1px solid var(--border-color); border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
        .card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
        .card-body { padding: 1.25rem; }

        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .alert-info { background-color: rgba(59, 130, 246, 0.08); border-left: 4px solid var(--info); color: #2563eb; }
        .alert-danger { background-color: rgba(239, 68, 68, 0.08); border-left: 4px solid var(--danger); color: #dc2626; }
        .alert-success { background-color: rgba(16, 185, 129, 0.08); border-left: 4px solid var(--success); color: #0f766e; }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { text-align: left; padding: 0.75rem 1rem; font-size: 0.75rem; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        .data-table td { padding: 1rem; border-bottom: 1px solid #e5e7eb; }
        .data-table tr:last-child td { border-bottom: none; }

        .form-label { font-weight: 500; color: var(--text-dark); margin-bottom: 0.5rem; }
        .form-control, .form-select { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; background: #fff; color: var(--text-dark); }
        .breadcrumb { list-style: none; display: flex; gap: 0.5rem; padding: 0; margin: 0 0 1rem; color: var(--text-muted); }
        .breadcrumb a { color: var(--primary); text-decoration: none; }

        .neo-card { position: relative; border: 1px solid rgba(99,102,241,0.25); background: radial-gradient(1200px 400px at -10% -20%, rgba(99,102,241,0.08), transparent), linear-gradient(180deg, rgba(2,6,23,0.9), rgba(2,6,23,0.85)); color: #e5e7eb; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(2,6,23,0.45), inset 0 0 0 1px rgba(99,102,241,0.05); margin-bottom: 1.25rem; }
        .neo-card .title { color: #c7d2fe; letter-spacing: .02em; font-weight: 600; }
        .neo-divider { height: 1px; background: linear-gradient(90deg, transparent, rgba(99,102,241,0.35), transparent); margin: 8px 0 12px; }
        .chip { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .55rem; border-radius: 999px; font-size: .75rem; font-weight: 600; background: rgba(99,102,241,0.12); color: #c7d2fe; border: 1px solid rgba(99,102,241,0.35); }
        .metric { font-size: 1.1rem; font-weight: 700; color: #e5e7eb; }
        .muted { color: #94a3b8; font-size: .85rem; }
        .sparkline-wrap { height: 80px; width: 100%; position: relative; }
        .status-badge { display: inline-flex; align-items: center; padding: .35rem .6rem; border-radius: 10px; font-size: .72rem; font-weight: 700; letter-spacing: .02em; text-transform: none; }
        .status-paid { background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(16,185,129,0.35); box-shadow: 0 0 18px rgba(16,185,129,0.2); }
        .status-partial { background: rgba(245,158,11,0.12); color: #fbbf24; border: 1px solid rgba(245,158,11,0.35); box-shadow: 0 0 18px rgba(245,158,11,0.2); }
        .status-pending { background: rgba(107,114,128,0.12); color: #d1d5db; border: 1px solid rgba(107,114,128,0.35); box-shadow: 0 0 18px rgba(107,114,128,0.15); }
        .status-overdue { background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.35); box-shadow: 0 0 18px rgba(239,68,68,0.2); }
        .status-info { background: rgba(59,130,246,0.12); color: #93c5fd; border: 1px solid rgba(59,130,246,0.35); box-shadow: 0 0 18px rgba(59,130,246,0.2); }
        .neo-grid .col-md-4 { margin-bottom: 1rem; }
        .neo-card .data-table { color: #e5e7eb; }
        .neo-card .data-table th { background: linear-gradient(180deg, rgba(99,102,241,0.08), rgba(99,102,241,0.02)); color: #c7d2fe; border-bottom-color: rgba(99,102,241,0.25); }
        .neo-card .data-table td { border-bottom-color: rgba(148,163,184,0.25); }
        .text-danger { color: #f87171 !important; }
        .text-warning { color: #fbbf24 !important; }
        .sev-chip { display:inline-flex; align-items:center; gap:.35rem; padding:.25rem .5rem; border-radius:999px; font-size:.72rem; font-weight:700; }
        .sev-30 { background: rgba(245,158,11,0.12); color:#fbbf24; border:1px solid rgba(245,158,11,0.35); }
        .sev-60 { background: rgba(239,120,11,0.12); color:#fb923c; border:1px solid rgba(239,120,11,0.35); }
        .sev-90 { background: rgba(239,68,68,0.12); color:#f87171; border:1px solid rgba(239,68,68,0.35); }
    </style>
    <style>
        .loader-overlay{position:fixed;inset:0;z-index:2000;background:linear-gradient(120deg,rgba(255,255,255,.8),rgba(255,255,255,.6));backdrop-filter:saturate(180%) blur(8px);display:flex;align-items:center;justify-content:center;transition:opacity .4s ease}
        .loader-overlay.loader-hidden{opacity:0;pointer-events:none}
        .spinner{position:relative;width:10em;height:10em}
        .spinner:before{transform:rotateX(60deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateBefore infinite linear reverse}
        .spinner:after{transform:rotateX(240deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateAfter infinite linear}
        .spinner:before,.spinner:after{box-sizing:border-box;content:'';display:block;position:absolute;top:50%;left:50%;margin-top:-5em;margin-left:-5em;width:10em;height:10em;transform-style:preserve-3d;transform-origin:50%;perspective-origin:50% 50%;perspective:340px;background-size:10em 10em;background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjI2NnB4IiBoZWlnaHQ9IjI5N3B4IiB2aWV3Qm94PSIwIDAgMjY2IDI5NyIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWxuczpza2V0Y2g9Imh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaC9ucyI+CiAgICA8dGl0bGU+c3Bpbm5lcjwvdGl0bGU+CiAgICA8ZGVzY3JpcHRpb24+Q3JlYXRlZCB3aXRoIFNrZXRjaCAoaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoKTwvZGVzY3JpcHRpb24+CiAgICA8ZGVmcz48L2RlZnM+CiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBza2V0Y2g6dHlwZT0iTVNQYWdlIj4KICAgICAgICA8cGF0aCBkPSJNMTcxLjUwNzgxMywzLjI1MDAwMDM4IEMyMjYuMjA4MTgzLDEyLjg1NzcxMTEgMjk3LjExMjcyMiw3MS40OTEyODIzIDI1MC44OTU1OTksMTA4LjQxMDE1NSBDMjE2LjU4MjAyNCwxMzUuODIwMzEgMTg2LjUyODQwNSw5Ny4wNjI0OTY0IDE1Ni44MDA3NzQsODUuNzczNDM0NiBDMTI3LjA3MzE0Myw3NC40ODQzNzIxIDc2Ljg4ODQ2MzIsODQuMjE2MTQ2MiA2MC4xMjg5MDY1LDEwOC40MTAxNTMgQy0xNS45ODA0Njg1LDIxOC4yODEyNDcgMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IDE0NS4yNzczNDQsMjk2LjY2Nzk2OCBDMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IC0yNS40NDkyMTg3LDI1Ny4yNDIxOTggMy4zOTg0Mzc1LDEwOC40MTAxNTUgQzE2LjMwNzA2NjEsNDEuODExNDE3NCA4NC43Mjc1ODI5LC0xMS45OTIyOTg1IDE3MS41MDc4MTMsMy4yNTAwMDAzOCBaIiBpZD0iUGF0aC0xIiBmaWxsPSIjMDAwMDAwIiBza2V0Y2g6dHlwZT0iTVNTaGFwZUdyb3VwIj48L3BhdGg+CiAgICA8L2c+Cjwvc3ZnPg==)}
        @keyframes rotateBefore{from{transform:rotateX(60deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(60deg) rotateY(45deg) rotateZ(-360deg)}}
        @keyframes rotateAfter{from{transform:rotateX(240deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(240deg) rotateY(45deg) rotateZ(360deg)}}
    </style>
    <script>window.addEventListener('load',()=>{const l=document.getElementById('loader');if(l){setTimeout(()=>{l.classList.add('loader-hidden');setTimeout(()=>{try{l.remove()}catch(e){}},600);},1500)}});</script>
</head>
<body>
    <div id='loader' class='loader-overlay'><div class='spinner'></div></div>
    <div class="dashboard-container">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Billing Reports</h1>
                <div class="page-actions">
                    <a href="index.php?page=billing_dashboard" class="btn btn-outline">
                        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>


    
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?page=billing_dashboard">Billing Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Billing Reports</li>
        </ol>
    </nav>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger animate__animated animate__fadeInDown" role="alert">
        <strong>Error!</strong> <?= $error ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
    <div id="successOverlay" style="position:fixed;inset:0;background:rgba(0,0,0,0.55);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:2000;">
        <div style="width:420px;max-width:90vw;background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.3);overflow:hidden;">
            <div style="padding:16px 18px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;">
                <div style="font-weight:600;color:#0f766e;">Payment Recorded</div>
                <button id="closeSuccessDialog" style="background:none;border:none;font-size:18px;cursor:pointer;color:#4b5563;">×</button>
            </div>
            <div style="padding:16px 18px;">
                <div style="font-size:14px;color:#374151;">
                    <strong>Success!</strong> <?= htmlspecialchars($success) ?>
                </div>
            </div>
            <div style="padding:12px 18px;border-top:1px solid var(--border-color);display:flex;justify-content:flex-end;gap:10px;">
                <a class="btn btn-outline" href="index.php?page=billing_dashboard">Close</a>
                <a class="btn btn-primary" href="index.php?page=billing_reports&report_type=payment&client_id=<?= urlencode((string)$clientId) ?>&start_date=<?= htmlspecialchars($startDate) ?>&end_date=<?= htmlspecialchars($endDate) ?>">View Payments</a>
            </div>
        </div>
    </div>
    <script>
    (function(){
        var closeBtn = document.getElementById('closeSuccessDialog');
        if (closeBtn) closeBtn.onclick = function(){ var ov = document.getElementById('successOverlay'); if (ov) ov.remove(); };
        setTimeout(function(){ var ov = document.getElementById('successOverlay'); if (ov) ov.remove(); }, 7000);
    })();
    </script>
    <?php endif; ?>
    <?php if (!empty($recentPayment)): ?>
    <div class="card" style="border-left:4px solid var(--success);">
        <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;">
            <div>
                <h6 style="margin:0;color:#0f766e;">Recent Payment Recorded</h6>
                <div class="text-muted" style="margin-top:.25rem;">
                    <?= htmlspecialchars($recentPayment['client_name'] ?? '') ?> • Bill #<?= (int)($recentPayment['bill_id'] ?? 0) ?> • <?= ucfirst($recentPayment['payment_method'] ?? '') ?>
                </div>
            </div>
            <div style="font-weight:700;color:#0f766e;">KSH <?= number_format((float)($recentPayment['amount'] ?? 0), 2) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Generate Report</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="" id="reportForm">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="report_type" class="form-label">Report Type</label>
                                <select class="form-select" id="report_type" name="report_type" required>
                                    <option value="">Select report type</option>
                                    <option value="monthly" <?= $reportType == 'monthly' ? 'selected' : '' ?>>Monthly Billing Summary</option>
                                    <option value="client" <?= $reportType == 'client' ? 'selected' : '' ?>>Client Billing History</option>
                                    <option value="consumption" <?= $reportType == 'consumption' ? 'selected' : '' ?>>Consumption Analysis</option>
                                    <option value="payment" <?= $reportType == 'payment' ? 'selected' : '' ?>>Payment Collection</option>
                                    <option value="outstanding" <?= $reportType == 'outstanding' ? 'selected' : '' ?>>Outstanding Balances</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3" id="clientSelectContainer" style="display: none;">
                                <label for="client_id" class="form-label">Select Client</label>
                                <select class="form-select" id="client_id" name="client_id">
                                    <option value="all">All Clients</option>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client['id'] ?>">
                                            <?= htmlspecialchars($client['full_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?= date('Y-m-01') ?>" required>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        
                        <button type="submit" name="generate_report" class="btn btn-primary">
                            <i class="fas fa-chart-bar me-2"></i> Generate Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($reportData)): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="neo-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= $reportTitle ?></h5>
                    <div>
                        <button class="btn btn-sm btn-primary" id="printReport">
                            <i class="fas fa-print me-2"></i> Print
                        </button>
                        <button class="btn btn-sm btn-primary ms-2" id="exportCSV">
                            <i class="fas fa-file-csv me-2"></i> Export CSV
                        </button>
                    </div>
                </div>
                <div class="card-body" id="reportContent">
                    <?php if (!empty($reportPeriod)): ?>
                    <div class="alert alert-info">
                        <strong>Report Period:</strong> <?= $reportPeriod ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($reportSummary)): ?>
                    <div class="row mb-4 neo-summary">
                        <?php foreach ($reportSummary as $key => $value): ?>
                            <div class="col-md-3">
                                <div class="neo-card">
                                    <div class="card-body" style="padding:1rem 1.1rem;">
                                        <div class="title"><?= ucwords(str_replace('_', ' ', $key)) ?></div>
                                        <div class="metric" style="margin-top:.35rem;">
                                            <?= is_numeric($value) ? number_format((float)$value, 2) : htmlspecialchars((string)$value) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($reportType == 'consumption' && !empty($reportData)): ?>
                    <div class="row mb-4 neo-grid">
                        <?php foreach ($reportData as $row): ?>
                        <div class="col-md-4">
                            <div class="neo-card">
                                <div class="card-body">
                                    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;">
                                        <div>
                                            <div class="title">Meter</div>
                                            <div class="metric"><?= htmlspecialchars($row['meter_serial']) ?></div>
                                            <div class="muted" style="margin-top:.25rem;">Client • <?= htmlspecialchars($row['client_name']) ?></div>
                                        </div>
                                        <div>
                                            <span class="chip"><i class="bi bi-flash"></i> <?= number_format($row['consumption'], 2) ?> units</span>
                                        </div>
                                    </div>
                                    <div class="neo-divider"></div>
                                    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;">
                                        <div>
                                            <div class="muted">Period</div>
                                            <div class="metric" style="font-size:.95rem;"><?= $row['period'] ?></div>
                                        </div>
                                        <div>
                                            <div class="muted">Daily Avg</div>
                                            <div class="metric" style="font-size:1rem;"><?= number_format($row['daily_average'], 2) ?> units</div>
                                        </div>
                                    </div>
                                    <div class="neo-divider"></div>
                                    <div class="sparkline-wrap">
                                        <canvas class="sparkline" width="320" height="80"
                                            data-labels='<?= json_encode($row['history_labels'] ?? []) ?>'
                                            data-data='<?= json_encode($row['history'] ?? []) ?>'
                                            data-status='<?= htmlspecialchars(strtolower($row['status'] ?? 'pending')) ?>'></canvas>
                                    </div>
                                    <div class="neo-divider"></div>
                                    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;">
                                        <div>
                                            <?php $st = strtolower($row['status'] ?? 'pending'); ?>
                                            <?php 
                                                $cls = 'status-info';
                                                $txt = ucfirst($row['status'] ?? 'Pending');
                                                if ($st === 'paid' || $st === 'completed') $cls = 'status-paid';
                                                elseif ($st === 'partially_paid' || $st === 'partial') $cls = 'status-partial';
                                                elseif ($st === 'pending') $cls = 'status-pending';
                                                elseif ($st === 'overdue') $cls = 'status-overdue';
                                            ?>
                                            <span class="status-badge <?= $cls ?>"><i class="bi bi-shield-check"></i> <?= htmlspecialchars($txt) ?></span>
                                        </div>
                                        <div class="muted">Bill #<?= (int)($row['bill_id'] ?? 0) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="data-table" id="reportTable">
                            <thead>
                                <tr>
                                    <?php 
                                    // Display table headers based on report type
                                    if ($reportType == 'monthly'): ?>
                                        <th>Month</th>
                                        <th>Bills Generated</th>
                                        <th>Total Amount</th>
                                        <th>Amount Collected</th>
                                        <th>Outstanding</th>
                                        <th>Collection Rate</th>
                                    <?php elseif ($reportType == 'client'): ?>
                                        <th>Client</th>
                                        <th>Bill Date</th>
                                        <th>Due Date</th>
                                        <th>Consumption</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    <?php elseif ($reportType == 'consumption'): ?>
                                        <th>Client</th>
                                        <th>Meter</th>
                                        <th>Period</th>
                                        <th>Consumption</th>
                                        <th>Average Daily</th>
                                        <th>Trend</th>
                                        <th>Status</th>
                                    <?php elseif ($reportType == 'payment'): ?>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Bill ID</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th>Status</th>
                                    <?php elseif ($reportType == 'outstanding'): ?>
                                        <th>Client</th>
                                        <th>Bill ID</th>
                                        <th>Bill Date</th>
                                        <th>Due Date</th>
                                        <th>Days Overdue</th>
                                        <th>Amount Due</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportData as $row): ?>
                                <tr>
                                    <?php 
                                    // Display table data based on report type
                                    if ($reportType == 'monthly'): ?>
                                        <td><?= $row['month'] ?></td>
                                        <td><?= $row['bills_count'] ?></td>
                                        <td>KES <?= number_format($row['total_amount'], 2) ?></td>
                                        <td>KES <?= number_format($row['amount_collected'], 2) ?></td>
                                        <td>KES <?= number_format($row['outstanding'], 2) ?></td>
                                        <td><?= number_format($row['collection_rate'], 1) ?>%</td>
                                    <?php elseif ($reportType == 'client'): ?>
                                        <td><?= htmlspecialchars($row['client_name']) ?></td>
                                        <td><?= date('d M Y', strtotime($row['bill_date'])) ?></td>
                                        <td><?= date('d M Y', strtotime($row['due_date'])) ?></td>
                                        <td><?= number_format(($row['consumption'] ?? $row['consumption_units'] ?? 0), 2) ?> units</td>
                                        <td>KES <?= number_format($row['amount_due'], 2) ?></td>
                                        <td>
                                            <?php $st = strtolower($row['payment_status'] ?? ($row['status'] ?? 'pending')); ?>
                                            <?php 
                                                $cls = 'status-info';
                                                $txt = ucfirst(str_replace('_',' ', ($row['payment_status'] ?? ($row['status'] ?? 'Pending'))));
                                                if ($st === 'paid' || $st === 'completed') $cls = 'status-paid';
                                                elseif ($st === 'partially_paid' || $st === 'partial') $cls = 'status-partial';
                                                elseif ($st === 'pending') $cls = 'status-pending';
                                                elseif ($st === 'overdue') $cls = 'status-overdue';
                                            ?>
                                            <span class="status-badge <?= $cls ?>"><i class="bi bi-shield-check"></i> <?= htmlspecialchars($txt) ?></span>
                                        </td>
                                    <?php elseif ($reportType == 'consumption'): ?>
                                        <td><?= htmlspecialchars($row['client_name']) ?></td>
                                        <td><?= htmlspecialchars($row['meter_serial']) ?></td>
                                        <td><?= $row['period'] ?></td>
                                        <td><?= number_format($row['consumption'], 2) ?> units</td>
                                        <td><?= number_format($row['daily_average'], 2) ?> units</td>
                                        <td>
                                            <?php if ($row['trend'] > 0): ?>
                                                <span class="text-danger"><i class="fas fa-arrow-up"></i> <?= number_format(abs($row['trend']), 1) ?>%</span>
                                            <?php elseif ($row['trend'] < 0): ?>
                                                <span class="text-success"><i class="fas fa-arrow-down"></i> <?= number_format(abs($row['trend']), 1) ?>%</span>
                                            <?php else: ?>
                                                <span class="text-muted"><i class="fas fa-minus"></i> 0%</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php $st = strtolower($row['status'] ?? 'pending'); ?>
                                            <?php if ($st === 'paid' || $st === 'completed'): ?>
                                                <span class="badge bg-success">Paid</span>
                                            <?php elseif ($st === 'partially_paid' || $st === 'partial'): ?>
                                                <span class="badge bg-warning">Partially Paid</span>
                                            <?php elseif ($st === 'pending'): ?>
                                                <span class="badge bg-secondary">Pending</span>
                                            <?php else: ?>
                                                <span class="badge bg-info"><?= htmlspecialchars(ucfirst($row['status'])) ?></span>
                                            <?php endif; ?>
                                        </td>
                                    <?php elseif ($reportType == 'payment'): ?>
                                        <td><?= date('d M Y', strtotime($row['payment_date'])) ?></td>
                                        <td><?= htmlspecialchars($row['client_name']) ?></td>
                                        <td><?= $row['bill_id'] ?></td>
                                        <td>KES <?= number_format($row['amount'], 2) ?></td>
                                        <td><?= ucfirst($row['payment_method']) ?></td>
                                        <td><?= htmlspecialchars($row['transaction_id'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php $st = strtolower($row['status'] ?? 'pending'); ?>
                                            <?php 
                                                $cls = 'status-info';
                                                $txt = ucfirst(str_replace('_',' ', $row['status'] ?? 'Pending'));
                                                if ($st === 'paid' || $st === 'completed' || $st === 'confirmed_and_verified') $cls = 'status-paid';
                                                elseif ($st === 'partially_paid' || $st === 'partial') $cls = 'status-partial';
                                                elseif ($st === 'pending') $cls = 'status-pending';
                                                elseif ($st === 'rejected') $cls = 'status-overdue';
                                                elseif ($st === 'flagged') $cls = 'status-info';
                                            ?>
                                            <span class="status-badge <?= $cls ?>"><i class="bi bi-shield-check"></i> <?= htmlspecialchars($txt) ?></span>
                                        </td>
                                    <?php elseif ($reportType == 'outstanding'): ?>
                                        <td><?= htmlspecialchars($row['client_name']) ?></td>
                                        <td><?= $row['bill_id'] ?? $row['id'] ?></td>
                                        <td><?= date('d M Y', strtotime($row['bill_date'])) ?></td>
                                        <td><?= date('d M Y', strtotime($row['due_date'])) ?></td>
                                        <td class="<?= $row['days_overdue'] > 30 ? 'text-danger' : 'text-warning' ?>">
                                            <?= $row['days_overdue'] ?> days
                                        </td>
                                        <td>KES <?= number_format($row['amount_due'] - $row['amount_paid'], 2) ?></td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Auto-generated insights -->
    <div class="row">
        <div class="col-md-12">
            <div class="neo-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Client Billing History (This Month)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="data-table" id="autoClientHistoryTable">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Bill Date</th>
                                    <th>Due Date</th>
                                    <th>Consumption</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($autoClientHistory as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['client_name'] ?? '') ?></td>
                                    <td><?= isset($row['bill_date']) ? date('d M Y', strtotime($row['bill_date'])) : '' ?></td>
                                    <td><?= isset($row['due_date']) ? date('d M Y', strtotime($row['due_date'])) : '' ?></td>
                                    <td><?= number_format(($row['consumption'] ?? $row['consumption_units'] ?? 0), 2) ?> units</td>
                                    <td>KES <?= number_format((float)($row['amount_due'] ?? $row['total_amount'] ?? 0), 2) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($row['payment_status'] ?? $row['status'] ?? 'pending')) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="neo-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Outstanding Balances</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="data-table" id="autoOutstandingTable">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Bill ID</th>
                                    <th>Bill Date</th>
                                    <th>Due Date</th>
                                    <th>Days Overdue</th>
                                    <th>Amount Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($autoOutstanding as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['client_name'] ?? '') ?></td>
                                    <td><?= $row['bill_id'] ?? $row['id'] ?></td>
                                    <td><?= isset($row['bill_date']) ? date('d M Y', strtotime($row['bill_date'])) : '' ?></td>
                                    <td><?= isset($row['due_date']) ? date('d M Y', strtotime($row['due_date'])) : '' ?></td>
                                    <td>
                                        <?php 
                                            $d = (int)($row['days_overdue'] ?? 0);
                                            $sev = ($d >= 90) ? 'sev-90' : (($d >= 60) ? 'sev-60' : (($d >= 30) ? 'sev-30' : ''));
                                        ?>
                                        <?php if ($sev): ?>
                                            <span class="sev-chip <?= $sev ?>"><i class="bi bi-lightning"></i> <?= $d ?> days</span>
                                        <?php else: ?>
                                            <span class="sev-chip" style="background:rgba(99,102,241,0.12);color:#c7d2fe;border:1px solid rgba(99,102,241,0.35);"><i class="bi bi-clock"></i> <?= $d ?> days</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>KES <?= number_format(((float)($row['amount_due'] ?? 0) - (float)($row['amount_paid'] ?? 0)), 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
</div>
</div>
</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportTypeSelect = document.getElementById('report_type');
    const clientSelectContainer = document.getElementById('clientSelectContainer');
    
    // Show/hide client select based on report type
    reportTypeSelect.addEventListener('change', function() {
        const reportType = this.value;
        if (reportType === 'client') {
            clientSelectContainer.style.display = 'block';
        } else {
            clientSelectContainer.style.display = 'none';
        }
    });
    
    // Trigger change event to set initial state
    reportTypeSelect.dispatchEvent(new Event('change'));
    
    // Print report functionality
    document.getElementById('printReport').addEventListener('click', function() {
        const printContents = document.getElementById('reportContent').innerHTML;
        const originalContents = document.body.innerHTML;
        
        document.body.innerHTML = `
            <div class="container mt-4">
                <h1 class="text-center mb-4">${document.querySelector('.card-header h5').textContent}</h1>
                ${printContents}
            </div>
        `;
        
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    });
    
    // Export to CSV functionality
    document.getElementById('exportCSV').addEventListener('click', function() {
        const table = document.getElementById('reportTable');
        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length; j++) {
                // Replace HTML entities and remove HTML tags
                let data = cols[j].innerText.replace(/(
|\n|\r)/gm, '').replace(/"/g, '""');
                row.push('"' + data + '"');
            }
            
            csv.push(row.join(','));
        }
        
        const csvString = csv.join('\n');
        const filename = document.querySelector('.card-header h5').textContent.replace(/\s+/g, '_').toLowerCase() + '_' + 
                        new Date().toISOString().slice(0, 10) + '.csv';
        const link = document.createElement('a');
        link.style.display = 'none';
        link.setAttribute('target', '_blank');
        link.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvString));
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Initialize DataTable for better user experience
    if (document.getElementById('reportTable')) {
        $('#reportTable').DataTable({
            "order": [[0, "asc"]],
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]]
        });
    }
    // Success toast animation
    const successToast = document.getElementById('successToast');
    if (successToast) {
        setTimeout(() => { successToast.classList.add('animate__fadeOutUp'); }, 2500);
        setTimeout(() => { successToast.style.display = 'none'; }, 3500);
    }
    if (document.getElementById('autoClientHistoryTable')) {
        $('#autoClientHistoryTable').DataTable({
            "order": [[1, "desc"]],
            "pageLength": 25
        });
    }
    if (document.getElementById('autoOutstandingTable')) {
        $('#autoOutstandingTable').DataTable({
            "order": [[4, "desc"]],
            "pageLength": 25
        });
    }
    // Render sparklines in consumption cards
    try {
        if (typeof Chart !== 'undefined') {
            var canvases = document.querySelectorAll('.sparkline');
            var colorMap = {
                paid: { border: 'rgba(16,185,129,1)', fill: 'rgba(16,185,129,0.15)' },
                completed: { border: 'rgba(16,185,129,1)', fill: 'rgba(16,185,129,0.15)' },
                partial: { border: 'rgba(245,158,11,1)', fill: 'rgba(245,158,11,0.15)' },
                partially_paid: { border: 'rgba(245,158,11,1)', fill: 'rgba(245,158,11,0.15)' },
                pending: { border: 'rgba(107,114,128,1)', fill: 'rgba(107,114,128,0.15)' },
                overdue: { border: 'rgba(239,68,68,1)', fill: 'rgba(239,68,68,0.15)' },
                default: { border: 'rgba(59,130,246,1)', fill: 'rgba(59,130,246,0.15)' }
            };
            canvases.forEach(function(cv){
                var labels = [];
                var data = [];
                var status = (cv.getAttribute('data-status') || 'default').toLowerCase();
                var colors = colorMap[status] || colorMap.default;
                try {
                    labels = JSON.parse(cv.getAttribute('data-labels') || '[]');
                    data = JSON.parse(cv.getAttribute('data-data') || '[]');
                } catch(e) {}
                if (!labels.length || !data.length) return;
                var ctx = cv.getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            borderColor: colors.border,
                            backgroundColor: colors.fill,
                            tension: 0.35,
                            pointRadius: 2,
                            pointHoverRadius: 4,
                            borderWidth: 2,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { display: false }, 
                            tooltip: { 
                                enabled: true, 
                                callbacks: {
                                    title: function(items){
                                        return items[0].label || '';
                                    },
                                    label: function(ctx){
                                        var v = ctx.parsed.y;
                                        return 'Units: ' + (typeof v === 'number' ? v.toFixed(2) : v);
                                    }
                                }
                            } 
                        },
                        scales: {
                            x: { display: false },
                            y: { display: false }
                        }
                    }
                });
            });
        }
    } catch(e) {}
});
</script>
