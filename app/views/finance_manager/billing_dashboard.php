<?php
// app/views/finance_manager/billing_dashboard.php

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'finance_manager'])) {
    // Cannot use header redirect here as headers are already sent
    echo "<script>window.location.href = 'index.php?page=login';</script>";
    exit();
}

// Get the current page for sidebar highlighting
$currentPage = 'billing_dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Dashboard - Water Billing System</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
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
            --sidebar-bg: var(--dark-bg, #1a1a27);
            --card-bg: #ffffff;
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

        .main-content {
            margin-left: 16rem;
            padding: 2rem;
            width: calc(100% - 16rem);
            overflow-x: hidden;
            min-height: 100vh;
            position: relative;
            box-sizing: border-box;
        }
        
        @media (max-width: 1024px) {
            .main-content {
                padding: 1.5rem;
            }
            
            .stats-cards {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
        
        .app-header {
            background-color: var(--header-bg);
            box-shadow: var(--header-shadow);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-radius: 0.75rem;
        }
        
        .app-header-left {
            display: flex;
            align-items: center;
        }
        
        .app-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
        }
        
        .app-header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 0;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
            position: relative;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 40px;
            height: 4px;
            background-color: var(--primary);
            border-radius: 2px;
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            text-align: center;
            user-select: none;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }
        
        .btn:after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            background-image: radial-gradient(circle, #fff 10%, transparent 10.01%);
            background-repeat: no-repeat;
            background-position: 50%;
            transform: scale(10, 10);
            opacity: 0;
            transition: transform .5s, opacity 1s;
        }
        
        .btn:active:after {
            transform: scale(0, 0);
            opacity: .3;
            transition: 0s;
        }

        .btn-primary {
            color: #fff;
            background-color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-dark);
        }

        .btn-outline:hover {
            background-color: rgba(79, 70, 229, 0.05);
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .btn i {
            margin-right: 0.5rem;
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stats-card {
            background-color: white;
            border-radius: 1rem;
            padding: 0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(229, 231, 235, 0.5);
            overflow: hidden;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: rgba(79, 70, 229, 0.3);
        }

        .stats-card-content {
            display: flex;
            padding: 1.75rem;
            align-items: center;
        }

        .stats-card-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 1rem;
            margin-right: 1.25rem;
            font-size: 1.5rem;
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover .stats-card-icon {
            transform: scale(1.1);
        }

        .total-bills .stats-card-icon {
            background-color: #dbeafe;
            color: var(--primary);
        }

        .pending-bills .stats-card-icon {
            background-color: #fef3c7;
            color: var(--warning);
        }

        .total-payments .stats-card-icon {
            background-color: #d1fae5;
            color: var(--success);
        }

        .stats-card-info {
            flex: 1;
        }

        .stats-card-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-muted);
            margin: 0 0 0.5rem 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stats-card-value {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
            line-height: 1.2;
        }
        
        .stats-card-trend {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        
        .trend-up {
            color: var(--success);
        }
        
        .trend-down {
            color: var(--danger);
        }

        .stats-card-desc {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        /* Activity Section */
        .activity-section {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .activity-card {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }

        .activity-header {
            margin-bottom: 1rem;
        }

        .activity-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #111827;
            display: flex;
            align-items: center;
        }

        .activity-title i {
            margin-right: 0.75rem;
            color: var(--primary);
        }

        .activity-body {
            padding: 1.5rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .status-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .empty-data {
            text-align: center;
            color: var(--text-muted);
            padding: 2rem 0;
            background-color: rgba(0, 0, 0, 0.02);
            border-radius: 0.25rem;
        }

        .view-all {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
        }

        .view-all-link {
            color: var(--primary);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            transition: color 0.2s;
        }

        .view-all-link:hover {
            color: var(--primary-dark);
        }

        .view-all-link i {
            margin-left: 0.5rem;
            font-size: 0.875rem;
        }
    </style>
    <style>
        .loader-overlay{position:fixed;inset:0;z-index:2000;background:linear-gradient(120deg,rgba(255,255,255,.8),rgba(255,255,255,.6));backdrop-filter:saturate(180%) blur(8px);display:flex;align-items:center;justify-content:center;transition:opacity .4s ease}
        .loader-overlay.loader-hidden{opacity:0;pointer-events:none}
        .spinner{position:relative;width:10em;height:10em}
        .spinner:before{transform:rotateX(60deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateBefore infinite linear reverse}
        .spinner:after{transform:rotateX(240deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateAfter infinite linear}
        .spinner:before,.spinner:after{top:50%;left:50%;}
        .spinner:before,.spinner:after{box-sizing:border-box;content:'';display:block;position:absolute;margin-top:-5em;margin-left:-5em;width:10em;height:10em;transform-style:preserve-3d;transform-origin:50%;perspective-origin:50% 50%;perspective:340px;background-size:10em 10em;background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjI2NnB4IiBoZWlnaHQ9IjI5N3B4IiB2aWV3Qm94PSIwIDAgMjY2IDI5NyIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWxuczpza2V0Y2g9Imh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaC9ucyI+CiAgICA8dGl0bGU+c3Bpbm5lcjwvdGl0bGU+CiAgICA8ZGVzY3JpcHRpb24+Q3JlYXRlZCB3aXRoIFNrZXRjaCAoaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoKTwvZGVzY3JpcHRpb24+CiAgICA8ZGVmcz48L2RlZnM+CiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBza2V0Y2g6dHlwZT0iTVNQYWdlIj4KICAgICAgICA8cGF0aCBkPSJNMTcxLjUwNzgxMywzLjI1MDAwMDM4IEMyMjYuMjA4MTgzLDEyLjg1NzcxMTEgMjk3LjExMjcyMiw3MS40OTEyODIzIDI1MC44OTU1OTksMTA4LjQxMDE1NSBDMjE2LjU4MjAyNCwxMzUuODIwMzEgMTg2LjUyODQwNSw5Ny4wNjI0OTY0IDE1Ni44MDA3NzQsODUuNzczNDM0NiBDMTI3LjA3MzE0Myw3NC40ODQzNzIxIDc2Ljg4ODQ2MzIsODQuMjE2MTQ2MiA2MC4xMjg5MDY1LDEwOC40MTAxNTMgQy0xNS45ODA0Njg1LDIxOC4yODEyNDcgMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IDE0NS4yNzczNDQsMjk2LjY2Nzk2OCBDMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IC0yNS40NDkyMTg3LDI1Ny4yNDIxOTggMy4zOTg0Mzc1LDEwOC40MTAxNTUgQzE2LjMwNzA2NjEsNDEuODExNDE3NCA4NC43Mjc1ODI5LC0xMS45OTIyOTg1IDE3MS41MDc4MTMsMy4yNTAwMDAzOCBaIiBpZD0iUGF0aC0xIiBmaWxsPSIjMDAwMDAwIiBza2V0Y2g6dHlwZT0iTVNTaGFwZUdyb3VwIj48L3BhdGg+CiAgICA8L2c+Cjwvc3ZnPg==)}
        @keyframes rotateBefore{from{transform:rotateX(60deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(60deg) rotateY(45deg) rotateZ(-360deg)}}
        @keyframes rotateAfter{from{transform:rotateX(240deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(240deg) rotateY(45deg) rotateZ(360deg)}}
    </style>
    <script>window.addEventListener('load',()=>{const l=document.getElementById('loader');if(l){setTimeout(()=>{l.classList.add('loader-hidden');setTimeout(()=>{try{l.remove()}catch(e){}},600);},1500)}});</script>
</head>
<body>
    <div id='loader' class='loader-overlay'><div class='spinner'></div></div>

<div class="dashboard-container">
    <!-- Sidebar -->
    <?php include_once '../app/views/finance_manager/partials/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="app-header animate__animated animate__fadeIn">
            <div class="app-header-left">
                <h1 class="app-title">Water Billing System</h1>
            </div>
            <div class="app-header-right">
                <div class="user-welcome">
                    <span>Welcome, <?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? 'User') ?></span>
                </div>
                <a href="index.php?page=profile" class="btn btn-outline">
                    <i class="fas fa-user-circle"></i>
                </a>
            </div>
        </div>
        
        <div class="page-header animate__animated animate__fadeIn">
            <h1 class="page-title">Billing Dashboard</h1>
            <div class="btn-group">
                <a href="index.php?page=generate_bills" class="btn btn-primary">
                    <i class="fas fa-file-invoice-dollar"></i> Generate Bills
                </a>
                <a href="index.php?page=billing_reports" class="btn btn-outline">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <?php if (!empty($data['error'])): ?>
            <div class="alert alert-error">
                <p><?= htmlspecialchars($data['error']) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($data['success'])): ?>
            <div class="alert alert-success">
                <p><?= htmlspecialchars($data['success']) ?></p>
            </div>
        <?php endif; ?>
            
        <!-- Summary Cards -->
        <div class="stats-cards animate__animated animate__fadeInUp">
            <div class="stats-card total-bills">
                <div class="stats-card-content">
                    <div class="stats-card-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="stats-card-info">
                        <h3 class="stats-card-title">Total Bills</h3>
                        <div class="stats-card-value"><?= number_format($totalBills) ?></div>
                        <div class="stats-card-desc">Total bills in the system</div>
                        <div class="stats-card-trend trend-up">
                            <i class="fas fa-arrow-up mr-1"></i> <span>4.3% from last month</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="stats-card pending-bills">
                <div class="stats-card-content">
                    <div class="stats-card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-card-info">
                        <h3 class="stats-card-title">Pending Bills</h3>
                        <div class="stats-card-value"><?= number_format($pendingBills) ?></div>
                        <div class="stats-card-desc">Bills awaiting payment</div>
                        <div class="stats-card-trend trend-down">
                            <i class="fas fa-arrow-down mr-1"></i> <span>2.1% from last month</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="stats-card total-payments">
                <div class="stats-card-content">
                    <div class="stats-card-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stats-card-info">
                        <h3 class="stats-card-title">Total Payments</h3>
                        <div class="stats-card-value">KSH <?= number_format($totalPayments, 2) ?></div>
                        <div class="stats-card-desc">Total payments received</div>
                        <div class="stats-card-trend trend-up">
                            <i class="fas fa-arrow-up mr-1"></i> <span>5.7% from last month</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            
        <!-- Recent Activity -->
        <div class="activity-section">
            <!-- Recent Bills -->
            <div class="activity-card">
                <div class="activity-header">
                    <h3 class="activity-title"><i class="fas fa-file-invoice"></i> Recent Bills</h3>
                </div>
                <div class="activity-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentBills)): ?>
                                <tr>
                                    <td colspan="5" class="empty-data">No recent bills found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentBills as $bill): ?>
                                    <tr>
                                        <td><?= $bill['id'] ?></td>
                                        <td><?= htmlspecialchars($bill['client_name'] ?? 'N/A') ?></td>
                                        <td>KSH <?= number_format($bill['amount_due'], 2) ?></td>
                                        <td>
                                            <span class="status-badge <?= $bill['payment_status'] === 'paid' ? 'status-success' : ($bill['payment_status'] === 'pending' ? 'status-warning' : 'status-danger') ?>">
                                                <?= ucfirst($bill['payment_status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($bill['bill_date'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="view-all">
                                <a href="index.php?page=view_bills" class="view-all-link">View All Bills <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                
                <!-- Recent Payments -->
                <div class="activity-card">
                    <div class="activity-header">
                        <h3 class="activity-title"><i class="fas fa-money-bill-wave"></i> Recent Payments</h3>
                    </div>
                    <div class="activity-body">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentPayments)): ?>
                                    <tr>
                                        <td colspan="5" class="empty-data">No recent payments found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentPayments as $payment): ?>
                                        <tr>
                                            <td><?= $payment['id'] ?></td>
                                            <td><?= htmlspecialchars($payment['username']) ?></td>
                                            <td>KSH <?= number_format($payment['amount'], 2) ?></td>
                                            <td><?= ucfirst($payment['payment_method']) ?></td>
                                            <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="view-all">
                            <a href="index.php?page=transactions" class="view-all-link">View All Payments <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
