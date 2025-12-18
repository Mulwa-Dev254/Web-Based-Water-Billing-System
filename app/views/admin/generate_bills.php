<?php
$total_bills = $data['total_bills'] ?? 0;
$pending_bills = $data['pending_bills'] ?? 0;
$total_payments = $data['total_payments'] ?? 0;
$total_balance = $data['total_balance'] ?? 0;
$pending_generation_meters = $data['pending_generation_meters'] ?? [];
$generation_results = $data['generation_results'] ?? [];
$success_message = $data['success_message'] ?? '';
$error_message = $data['error_message'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • Generate Bills</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#ff4757; --primary-dark:#e84118; --dark-bg:#1e1e2d; --sidebar-bg:#1a1a27; --card-bg:#2a2a3c; --text-light:#f8f9fa; --text-muted:#a1a5b7; --border-color:#2d2d3a; --success:#1dd1a1; --info:#2e86de; --warning:#ff9f43; }
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background-color:var(--dark-bg);color:var(--text-light);line-height:1.6;display:flex;min-height:100vh;overflow-x:hidden}
        .dashboard-layout{display:flex;width:100%;min-height:100vh}
        .sidebar{width:280px;background-color:var(--sidebar-bg);padding:1.5rem 0;display:flex;flex-direction:column;position:fixed;height:100vh;top:0;left:0;z-index:1000;border-right:1px solid var(--border-color)}
        .sidebar-header{padding:0 1.5rem 1.5rem;border-bottom:1px solid var(--border-color);margin-bottom:1.5rem}
        .sidebar-header h3{color:var(--primary);font-size:1.5rem;font-weight:700;display:flex;align-items:center;gap:.75rem}
        .sidebar-nav{flex-grow:1;overflow-y:auto;padding:0 1rem}
        .sidebar-nav ul{list-style:none}
        .sidebar-nav a{display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.5rem;font-weight:500;color:var(--text-muted);transition:all .3s}
        .sidebar-nav a:hover{background-color:rgba(255,71,87,.1);color:var(--text-light)}
        .sidebar-nav a.active{background-color:var(--primary);color:#fff}
        .sidebar-nav a i{width:1.5rem;text-align:center}
        .main-content{margin-left:280px;flex-grow:1;min-height:100vh}
        .header-bar{background-color:var(--sidebar-bg);padding:1.25rem 2rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border-color)}
        .header-title{display:flex;align-items:center;gap:1rem}
        .header-title h1{font-size:1.4rem;font-weight:600}
        .dashboard-container{padding:2rem}
        .content-section{background-color:var(--card-bg);padding:1.5rem;border-radius:.75rem;box-shadow:0 0 20px rgba(0,0,0,.1);margin-bottom:2rem;border:1px solid var(--border-color)}
        .section-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid var(--border-color)}
        .section-title{color:var(--primary);font-size:1.3rem;font-weight:600;display:flex;align-items:center;gap:.5rem}
        .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}
        .stat-card{background:#232336;border:1px solid var(--border-color);border-radius:.75rem;padding:1rem}
        .stat-card h4{color:var(--text-muted);font-size:.95rem;margin:0 0 .5rem}
        .stat-card .value{font-size:1.5rem;font-weight:700}
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem .9rem;border-radius:.5rem;border:1px solid var(--border-color);background:transparent;color:var(--text-light);cursor:pointer}
        .btn-primary{background-color:var(--primary);border-color:var(--primary)}
        .btn-outline{background:transparent}
        .alert{padding:.75rem 1rem;border-radius:.5rem;border:1px solid var(--border-color);background-color:#232336;color:var(--text-light);display:flex;align-items:center;gap:.5rem}
        .table-responsive{overflow-x:auto}
        table{width:100%;border-collapse:collapse}
        th,td{padding:.75rem;border-bottom:1px solid var(--border-color);text-align:left}
        th{color:var(--text-muted);font-weight:600;background-color:#232336}
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <div class="sidebar">
            <div class="sidebar-header"><h3><i class="fas fa-shield-alt"></i> Admin Panel</h3></div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php?page=admin_dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="index.php?page=admin_manage_users"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="index.php?page=admin_manage_billing_plans"><i class="fas fa-file-invoice"></i> Billing Plans</a></li>
                    <li><a href="index.php?page=admin_manage_services"><i class="fas fa-cogs"></i> Manage Services</a></li>
                    <li><a href="index.php?page=admin_manage_requests"><i class="fas fa-clipboard-list"></i> Service Requests</a></li>
                    <li><a href="index.php?page=admin_manage_meters"><i class="fas fa-tachometer-alt"></i> Manage Meters</a></li>
                    <li><a href="index.php?page=generate_bills" class="active"><i class="fas fa-file-invoice-dollar"></i> Generate Bills</a></li>
                    <li><a href="index.php?page=view_bills"><i class="fas fa-list"></i> View Bills</a></li>
                    <li><a href="index.php?page=admin_transactions"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
                    <li><a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
        <div class="main-content">
            <div class="header-bar">
                <div class="header-title"><h1>Generate Bills</h1></div>
                <div class="user-info"><a href="index.php?page=logout" class="btn btn-primary"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
            </div>
            <div class="dashboard-container">
                <?php if (!empty($success_message)): ?>
                    <div class="content-section"><div class="alert"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?></div></div>
                <?php endif; ?>
                <?php if (!empty($error_message)): ?>
                    <div class="content-section"><div class="alert"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?></div></div>
                <?php endif; ?>

                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-chart-line"></i> Summary</h2>
                        <div>
                            <a href="index.php?page=billing_reports" class="btn btn-outline"><i class="fas fa-chart-bar"></i> Reports</a>
                        </div>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card"><h4>Total Bills</h4><div class="value"><?= (int)$total_bills ?></div></div>
                        <div class="stat-card"><h4>Pending Bills</h4><div class="value"><?= (int)$pending_bills ?></div></div>
                        <div class="stat-card"><h4>Total Payments (KSh)</h4><div class="value"><?= number_format((float)$total_payments,2) ?></div></div>
                        <div class="stat-card"><h4>Total Outstanding (KSh)</h4><div class="value"><?= number_format((float)$total_balance,2) ?></div></div>
                    </div>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-file-invoice-dollar"></i> Generate All Pending Bills</h2>
                        <div>
                            <a href="index.php?page=generate_single_bill" class="btn btn-outline"><i class="fas fa-file-invoice"></i> Generate Single Bill</a>
                        </div>
                    </div>
                    <form method="post" action="index.php?page=generate_bills">
                        <input type="hidden" name="generate_all" value="1">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-bolt"></i> Generate Bills</button>
                    </form>
                </div>

                <?php if (!empty($generation_results)): ?>
                <div class="content-section">
                    <div class="section-header"><h2 class="section-title"><i class="fas fa-list"></i> Generation Results</h2></div>
                    <div class="alert">
                        <span>Successfully generated: <?= (int)($generation_results['success'] ?? 0) ?></span>
                        <span style="margin-left:1rem;">Failed: <?= (int)($generation_results['failed'] ?? 0) ?></span>
                    </div>
                    <?php if (!empty($generation_results['details'])): ?>
                        <div class="table-responsive">
                            <table>
                                <thead><tr><th>Client</th><th>Meter</th><th>Status</th><th>Message</th></tr></thead>
                                <tbody>
                                <?php foreach ($generation_results['details'] as $d): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($d['client'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($d['meter'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($d['status'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($d['message'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="content-section">
                    <div class="section-header"><h2 class="section-title"><i class="fas fa-tachometer-alt"></i> Meters Pending Billing</h2></div>
                    <?php if (empty($pending_generation_meters)): ?>
                        <div class="alert"><i class="fas fa-info-circle"></i> No meters pending billing.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table>
                                <thead><tr><th>Meter ID</th><th>Client ID</th><th>Last Billed Reading</th><th>Latest Reading</th><th>Action</th></tr></thead>
                                <tbody>
                                <?php foreach ($pending_generation_meters as $pm): ?>
                                    <tr>
                                        <td><?= (int)($pm['meter_id'] ?? 0) ?></td>
                                        <td><?= (int)($pm['client_id'] ?? 0) ?></td>
                                        <td><?= isset($pm['last_billed_reading_id']) && $pm['last_billed_reading_id']!==null ? (int)$pm['last_billed_reading_id'] : '-' ?></td>
                                        <td><?= (int)($pm['latest_reading_id'] ?? 0) ?></td>
                                        <td><a class="btn btn-outline" href="index.php?page=generate_single_bill&meter_id=<?= (int)($pm['meter_id'] ?? 0) ?>"><i class="fas fa-file-invoice"></i> Generate Single Bill</a></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
