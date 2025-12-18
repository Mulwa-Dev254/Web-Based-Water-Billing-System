<?php
// Ensure admin is logged in; controller already enforces access
$bills = $data['bills'] ?? [];
$clients = $data['clients'] ?? [];
$status = $_GET['status'] ?? 'all';
$clientId = (int)($_GET['client_id'] ?? 0);
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • View Bills</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:#ff4757;
            --primary-dark:#e84118;
            --dark-bg:#1e1e2d;
            --darker-bg:#151521;
            --sidebar-bg:#1a1a27;
            --card-bg:#2a2a3c;
            --text-light:#f8f9fa;
            --text-muted:#a1a5b7;
            --border-color:#2d2d3a;
            --success:#1dd1a1;
            --info:#2e86de;
            --warning:#ff9f43;
            --danger:#ee5253;
            --purple:#5f27cd;
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; width: 100%; }
        body {
            font-family:'Inter',sans-serif;
            background-color:var(--dark-bg);
            color:var(--text-light);
            line-height:1.6;
            margin: 0;
            overflow-x: hidden;
        }
        a { text-decoration:none; color:inherit; }
        .dashboard-layout {
            display: flex;
            width: 100vw;
            min-height: 100vh;
        }
        .sidebar {
            width: 280px;
            background-color:var(--sidebar-bg);
            padding:1.5rem 0;
            display:flex;
            flex-direction:column;
            position:fixed;
            height:100vh;
            top:0; left:0;
            z-index:1000;
            transition:transform .3s;
            border-right:1px solid var(--border-color);
            box-shadow:0 0 15px rgba(0,0,0,.1);
        }
        .sidebar.collapsed { transform: translateX(-280px); }
        .sidebar-header {
            padding:0 1.5rem 1.5rem;
            border-bottom:1px solid var(--border-color);
            margin-bottom:1.5rem;
        }
        .sidebar-header h3 {
            color:var(--primary);
            font-size:1.5rem;
            font-weight:700;
            display:flex;
            align-items:center; gap:.75rem;
        }
        .sidebar-nav { flex-grow:1; overflow-y:auto; padding:0 1rem; }
        .sidebar-nav ul { list-style:none; margin: 0; padding: 0; }
        .sidebar-nav a {
            display:flex; align-items:center; gap:.75rem;
            padding:.875rem 1rem;
            border-radius:.5rem;
            font-weight:500; color:var(--text-muted);
            transition:all .3s;
        }
        .sidebar-nav a:hover {
            background-color:rgba(255,71,87,.1);
            color:var(--text-light);
        }
        .sidebar-nav a.active {
            background-color:var(--primary);
            color:#fff;
            box-shadow:0 4px 15px rgba(255,71,87,.3);
        }
        .sidebar-nav a i {
            width:1.5rem;
            text-align:center;
            font-size:1.1rem;
        }
        .main-content {
            margin-left: 280px;
            flex-grow:1;
            min-height:100vh;
            width: calc(100vw - 280px);
            padding-bottom: 0;
            background: var(--dark-bg);
            display: flex;
            flex-direction: column;
        }
        .main-content.full-width { margin-left:0; width:100vw;}
        .header-bar {
            background-color:var(--sidebar-bg);
            padding:1.25rem 2rem;
            display:flex; justify-content:space-between; align-items:center;
            border-bottom:1px solid var(--border-color);
            position: sticky; top:0; z-index:100;
        }
        .header-title { display:flex; align-items:center; gap:1rem; }
        .header-title h1 { font-size:1.4rem; font-weight:600; color:var(--text-light); margin:0; }
        .dashboard-container {
            flex-grow: 1;
            width:100%;
            max-width: 100vw;
            min-width: 0;
            padding:2rem;
            overflow-x: auto;
        }
        .content-section {
            background-color:var(--card-bg);
            padding:1.5rem;
            border-radius:.75rem;
            box-shadow:0 0 20px rgba(0,0,0,.1);
            margin-bottom:2rem;
            border:1px solid var(--border-color);
            min-width: 0;
        }
        .section-header {
            display:flex;
            justify-content:space-between; align-items:center;
            flex-wrap:wrap; gap:.75rem;
            margin-bottom:1rem; padding-bottom:1rem;
            border-bottom:1px solid var(--border-color);
        }
        .section-title {
            color:var(--primary);
            font-size:1.3rem;
            font-weight:600; display:flex; align-items:center; gap:.5rem;
        }
        .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .9rem; border-radius:.5rem; border:1px solid var(--border-color); background:transparent; color:var(--text-light); cursor:pointer; }
        .btn-primary { background-color:var(--primary); border-color:var(--primary);}
        .btn-outline { background:transparent;}
        .filters-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; }
        .form-control, .form-select { width:100%; padding:.6rem .7rem; border-radius:.5rem; border:1px solid var(--border-color); background-color:#1f1f2e; color:var(--text-light);}
        .table-responsive { width:100%; overflow-x:auto; }
        .table-responsive.bills-scroll {
            width: 100%;
            max-width: 100vw;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .data-table {
            width: 100%;
            min-width: 992px;
            border-collapse: collapse;
            table-layout: auto;
        }
        .data-table th, .data-table td {
            padding: .75rem;
            border-bottom: 1px solid var(--border-color);
            text-align: left; white-space: nowrap;
            vertical-align: middle;
        }
        .data-table th {
            color: var(--text-muted);
            font-weight: 600;
            background-color: #232336;
        }
        .alert {
            padding: .75rem 1rem; border-radius: .5rem; border: 1px solid var(--border-color);
            background-color: #232336; color: var(--text-light); display: flex; align-items: center; gap: .5rem;
        }
        .sidebar-toggle { background-color: var(--primary); color: #fff; border:none; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; }

        /* Mobile/Tablets Responsive Fixes */
        @media (max-width:1200px){
            .main-content { margin-left: 0; width: 100vw; }
            .sidebar { position: relative; width: 100vw; height: auto; }
            .dashboard-layout { flex-direction: column; }
        }
        @media (max-width:992px){ 
            .filters-grid{ grid-template-columns:1fr 1fr; }
            .main-content { margin-left: 0; width:100vw;}
            .sidebar { position:relative; width:100vw; height:auto; }
            .dashboard-layout { flex-direction: column; }
        }
        @media (max-width:760px){
            .dashboard-container { padding: 1rem; }
            .content-section { padding:1rem; margin-bottom:1rem;}
            .header-bar { padding: 1rem; }
        }
        @media (max-width:640px){
            .filters-grid{ grid-template-columns:1fr; }
            .dashboard-container { padding: 0.5rem;}
            .data-table th, .data-table td{ padding: .4rem;}
            .content-section{ padding: 0.7rem;}
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-shield-alt"></i> Admin Panel</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php?page=admin_dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="index.php?page=admin_manage_users"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="index.php?page=admin_manage_billing_plans"><i class="fas fa-file-invoice"></i> Billing Plans</a></li>
                    <li><a href="index.php?page=admin_manage_services"><i class="fas fa-cogs"></i> Manage Services</a></li>
                    <li><a href="index.php?page=admin_manage_requests"><i class="fas fa-clipboard-list"></i> Service Requests</a></li>
                    <li><a href="index.php?page=admin_manage_meters"><i class="fas fa-tachometer-alt"></i> Manage Meters</a></li>
                    <li><a href="index.php?page=admin_manage_client_plans"><i class="fas fa-layer-group"></i> Client Plans</a></li>
                    <li><a href="index.php?page=generate_bills"><i class="fas fa-file-invoice-dollar"></i> Generate Bills</a></li>
                    <li><a href="index.php?page=view_bills" class="active"><i class="fas fa-list"></i> View Bills</a></li>
                    <li><a href="index.php?page=finance_manager_reports"><i class="fas fa-chart-pie"></i> Financial Reports</a></li>
                    <li><a href="index.php?page=billing_reports"><i class="fas fa-chart-line"></i> Billing Reports</a></li>
                    <li><a href="index.php?page=admin_transactions"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
                    <li><a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
        <div class="main-content" id="mainContent">
            <div class="header-bar">
                <div class="header-title">
                    <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                    <h1>View Bills</h1>
                </div>
                <div class="user-info">
                    <a href="index.php?page=logout" class="btn btn-primary"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            <div class="dashboard-container">
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-file-invoice-dollar"></i> Filter Bills</h2>
                        <div>
                            <a href="index.php?page=generate_bills" class="btn btn-primary"><i class="fas fa-file-invoice-dollar"></i> Generate Bills</a>
                            <a href="index.php?page=billing_reports&type=monthly" class="btn btn-outline"><i class="fas fa-chart-bar"></i> Reports</a>
                        </div>
                    </div>
                    <form method="get" action="index.php" class="filters-grid">
                        <input type="hidden" name="page" value="view_bills">
                        <div>
                            <label>Status</label>
                            <select class="form-select" name="status">
                                <option value="all" <?= $status==='all'?'selected':'' ?>>All</option>
                                <option value="paid" <?= $status==='paid'?'selected':'' ?>>Paid</option>
                                <option value="pending" <?= $status==='pending'?'selected':'' ?>>Pending</option>
                                <option value="partially_paid" <?= $status==='partially_paid'?'selected':'' ?>>Partially Paid</option>
                                <option value="overdue" <?= $status==='overdue'?'selected':'' ?>>Overdue</option>
                            </select>
                        </div>
                        <div>
                            <label>Client</label>
                            <select class="form-select" name="client_id">
                                <option value="0" <?= $clientId===0?'selected':'' ?>>All Clients</option>
                                <?php foreach ($clients as $c): ?>
                                    <option value="<?= (int)($c['id'] ?? 0) ?>" <?= $clientId===(int)($c['id'] ?? 0)?'selected':'' ?>><?= htmlspecialchars($c['full_name'] ?? $c['username'] ?? 'Client') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Start Date</label>
                            <input type="date" class="form-control" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                        </div>
                        <div>
                            <label>End Date</label>
                            <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply Filters</button>
                        </div>
                    </form>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-list"></i> Bills</h2>
                        <div>
                            <a href="index.php?page=generate_bills" class="btn btn-primary"><i class="fas fa-file-invoice-dollar"></i> Generate Bills</a>
                            <a href="index.php?page=generate_single_bill" class="btn btn-outline"><i class="fas fa-file-invoice"></i> Generate Single Bill</a>
                        </div>
                    </div>
                    <?php if (empty($bills)): ?>
                        <div class="alert"><i class="fas fa-info-circle"></i> No bills found matching your criteria.</div>
                    <?php else: ?>
                        <div class="table-responsive bills-scroll">
                            <table class="data-table" id="billsTable">
                                <thead>
                                    <tr>
                                        <th>Bill #</th>
                                        <th>Client</th>
                                        <th>Meter</th>
                                        <th>Bill Date</th>
                                        <th>Due Date</th>
                                        <th>Consumption</th>
                                        <th>Amount Due</th>
                                        <th>Amount Paid</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bills as $bill): ?>
                                        <?php 
                                            $amountDue = (float)($bill['amount_due'] ?? 0);
                                            $amountPaid = (float)($bill['amount_paid'] ?? 0);
                                            $balance = max($amountDue - $amountPaid, 0);
                                        ?>
                                        <tr>
                                            <td><?= (int)$bill['id'] ?></td>
                                            <td><?= htmlspecialchars($bill['client_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($bill['serial_number'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($bill['bill_date'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($bill['due_date'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($bill['consumption'] ?? '-') ?></td>
                                            <td><?= number_format($amountDue, 2) ?></td>
                                            <td><?= number_format($amountPaid, 2) ?></td>
                                            <td><?= number_format($balance, 2) ?></td>
                                            <td><?= htmlspecialchars(ucfirst(str_replace('_',' ', $bill['payment_status'] ?? 'pending'))) ?></td>
                                            <td>
                                                <a href="index.php?page=view_bill_details&bill_id=<?= (int)$bill['id'] ?>" class="btn btn-primary"><i class="fas fa-eye"></i> View</a>
                                            </td>
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
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const toggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const main = document.getElementById('mainContent');
            if(toggle){ toggle.addEventListener('click', function(){ sidebar.classList.toggle('collapsed'); main.classList.toggle('full-width'); }); }
            // Ensure content fits when resizing (mobile/tablet)
            window.addEventListener('resize', function(){
                if (window.innerWidth > 992) {
                    // keep current collapsed state
                } else {
                    // on small screens, ensure full-width when collapsed
                    if (sidebar.classList.contains('collapsed')) {
                        main.classList.add('full-width');
                    }
                }
            });
        });
    </script>
</body>
</html>
