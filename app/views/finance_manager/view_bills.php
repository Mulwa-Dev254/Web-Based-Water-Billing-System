<?php
// app/views/finance_manager/view_bills.php

// Ensure this page is only accessible to finance managers and admins
// Using the auth object passed from the controller
if (!isset($_SESSION['user']) || !isset($auth) || !in_array($auth->getUserRole(), ['finance_manager', 'admin'])) {
    // Cannot use header redirect here as headers are already sent
    echo "<script>window.location.href = 'index.php?page=login';</script>";
    exit;
}

// Extract data from controller
$bills = $data['bills'] ?? [];
$clients = $data['clients'] ?? [];
$status = $data['status'] ?? 'all';
$clientId = $data['clientId'] ?? 0;
$startDate = $data['startDate'] ?? date('Y-m-01');
$endDate = $data['endDate'] ?? date('Y-m-t');

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
    <title>View Bills - Water Billing System</title>
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

    <!-- Dashboard theme variables and component styles (aligned with billing_dashboard) -->
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

        .dashboard-container {
            display: flex;
            min-height: 100vh;
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
            .main-content { padding: 1.5rem; }
        }
        @media (max-width: 768px) {
            .main-content { margin-left: 0; width: 100%; padding: 1rem; }
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
        .app-title { font-size: 1.5rem; font-weight: 600; color: var(--text-dark); margin: 0; }

        .btn-group { display: flex; gap: 0.5rem; }
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
            border: 1px solid var(--border-color);
            background-color: #fff;
            color: var(--text-dark);
        }
        .btn i { margin-right: 0.5rem; }
        .btn-primary { color: #fff; background-color: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background-color: var(--primary-dark); border-color: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25); }
        .btn-outline { background-color: transparent; border: 1px solid var(--border-color); color: var(--text-dark); }
        .btn-outline:hover { background-color: rgba(79, 70, 229, 0.05); border-color: var(--primary); color: var(--primary); transform: translateY(-2px); }
        .btn-sm { padding: 0.5rem 0.75rem; font-size: 0.75rem; }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .page-title { font-size: 1.75rem; font-weight: 700; color: var(--text-dark); margin: 0; position: relative; }
        .page-title:after { content: ''; position: absolute; bottom: -8px; left: 0; width: 40px; height: 4px; background-color: var(--primary); border-radius: 2px; }

        /* Card styling aligned to dashboard */
        .card { background-color: #fff; border: 1px solid var(--border-color); border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
        .card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
        .card-body { padding: 1.25rem; }

        /* Table styling similar to dashboard data tables */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { text-align: left; padding: 0.75rem 1rem; font-size: 0.75rem; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        .data-table td { padding: 1rem; border-bottom: 1px solid #e5e7eb; }
        .data-table tr:last-child td { border-bottom: none; }

        /* Alerts */
        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .alert-info { background-color: rgba(59, 130, 246, 0.08); border-left: 4px solid var(--info); color: #2563eb; }
    </style>
    <style>
        .loader-overlay{position:fixed;inset:0;z-index:2000;background:linear-gradient(120deg,rgba(255,255,255,.8),rgba(255,255,255,.6));backdrop-filter:saturate(180%) blur(8px);display:flex;align-items:center;justify-content:center;transition:opacity .4s ease}
        .loader-overlay.loader-hidden{opacity:0;pointer-events:none}
        .spinner{position:relative;width:10em;height:10em}
        .spinner:before{transform:rotateX(60deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateBefore infinite linear reverse}
        .spinner:after{transform:rotateX(240deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateAfter infinite linear}
        .spinner:before,.spinner:after{box-sizing:border-box;content:'';display:block;position:absolute;top:50%;left:50%;margin-top:-5em;margin-left:-5em;width:10em;height:10em;transform-style:preserve-3d;transform-origin:50%;perspective-origin:50% 50%;perspective:340px;background-size:10em 10em;background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjI2NnB4IiBoZWlnaHQ9IjI5N3B4IiB2aWV3Qm94PSIwIDAgMjY2IDI5NyIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWxuczpza2V0Y2g9Imh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaC9ucyI+CiAgICA8dGl0bGU+c3Bpbm5lcjwvdGl0bGU+CiAgICA8ZGVzY3JpcHRpb24+Q3JlYXRlZCB3aXRoIFNrZXRjaCAoaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoKTwvZGVzY3JpcHRpb24+CiAgICA8ZGVmcz48L2RlZnM+CiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBza2V0Y2g6dHlwZT0iTVNQYWdlIj4KICAgICAgICA8cGF0aCBkPSJNMT71LjUwNzgxMywzLjI1MDAwMDM4IEMyMjYuMjA4MTgzLDEyLjg1NzcxMTEgMjk3LjExMjcyMiw3MS40OTEyODIzIDI1MC44OTU1OTksMTA4LjQxMDE1NSBDMjE2LjU4MjAyNCwxMzUuODIwMzEgMTg2LjUyODQwNSw5Ny4wNjI0OTY0IDE1Ni44MDA3NzQsODUuNzczNDM0NiBDMTI3LjA3MzE0Myw3NC40ODQzNzIxIDc2Ljg4ODQ2MzIsODQuMjE2MTQ2MiA2MC4xMjg5MDY1LDEwOC40MTAxNTMgQy0xNS45ODA0Njg1LDIxOC4yODEyNDcgMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IDE0NS4yNzczNDQsMjk2LjY2Nzk2OCBDMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IC0yNS40NDkyMTg3LDI1Ny4yNDIxOTggMy4zOTg0Mzc1LDEwOC40MTAxNTUgQzE2LjMwNzA2NjEsNDEuODExNDE3NCA4NC43Mjc1ODI5LC0xMS45OTIyOTg1IDE3MS41MDc4MTMsMy4yNTAwMDAzOCBaIiBpZD0iUGF0aC0xIiBmaWxsPSIjMDAwMDAwIiBza2V0Y2g6dHlwZT0iTVNTaGFwZUdyb3VwIj48L3BhdGg+CiAgICA8L2c+Cjwvc3ZnPg==)}
        @keyframes rotateBefore{from{transform:rotateX(60deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(60deg) rotateY(45deg) rotateZ(-360deg)}}
        @keyframes rotateAfter{from{transform:rotateX(240deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(240deg) rotateY(45deg) rotateZ(360deg)}}
    </style>
    <script>window.addEventListener('load',()=>{const l=document.getElementById('loader');if(l){setTimeout(()=>{l.classList.add('loader-hidden');setTimeout(()=>{try{l.remove()}catch(e){}},600);},1500)}});</script>
</head>
<body>
    <div id='loader' class='loader-overlay'><div class='spinner'></div></div>

<div class="dashboard-container">
    <!-- Sidebar: use the same partial as Billing Dashboard -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content: align with dashboard structure/colors -->
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
            <h1 class="page-title">View Bills</h1>
            <div class="btn-group">
                <a href="index.php?page=generate_bills" class="btn btn-primary">
                    <i class="fas fa-file-invoice-dollar"></i> Generate Bills
                </a>
                <a href="index.php?page=billing_reports&type=monthly" class="btn btn-outline">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </div>
        </div>
    
        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Filter Bills</h5>
            </div>
            <div class="card-body">
                <form method="get" action="index.php" class="row g-3">
                    <input type="hidden" name="page" value="view_bills">
                    
                    <div class="col-md-3">
                        <label for="status" class="form-label">Payment Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
                            <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="partially_paid" <?= $status === 'partially_paid' ? 'selected' : '' ?>>Partially Paid</option>
                            <option value="overdue" <?= $status === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="client_id" class="form-label">Client</label>
                        <select class="form-select" id="client_id" name="client_id">
                            <option value="0">All Clients</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>" <?= $clientId == $client['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($client['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $startDate ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $endDate ?>">
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i> Apply Filters
                        </button>
                        <a href="index.php?page=view_bills" class="btn btn-outline">
                            <i class="fas fa-undo me-2"></i> Reset Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>
    
        <!-- Bills Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Bills</h5>
                <div>
                    <a href="index.php?page=generate_bills" class="btn btn-sm btn-primary">
                        <i class="fas fa-file-invoice-dollar me-2"></i> Generate Bills
                    </a>
                    <a href="index.php?page=billing_reports&type=monthly" class="btn btn-sm btn-outline ms-2">
                        <i class="fas fa-chart-bar me-2"></i> Billing Reports
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($bills)): ?>
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>
                            No bills found matching your criteria.
                            <a href="index.php?page=generate_bills" class="ms-2 btn btn-sm btn-primary">
                                <i class="fas fa-file-invoice-dollar me-1"></i> Generate Bills Now
                            </a>
                            <a href="index.php?page=generate_single_bill" class="ms-2 btn btn-sm btn-outline">
                                <i class="fas fa-file-invoice me-1"></i> Generate Single Bill
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
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
                                    <tr>
                                        <td><?= $bill['id'] ?></td>
                                        <td><?= htmlspecialchars($bill['client_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($bill['serial_number'] ?? 'N/A') ?></td>
                                        <td><?= date('d M Y', strtotime($bill['bill_date'])) ?></td>
                                        <td>
                                            <?php 
                                            $dueDate = new DateTime($bill['due_date']);
                                            $today = new DateTime();
                                            $interval = $today->diff($dueDate);
                                            $daysLeft = $dueDate > $today ? $interval->days : -$interval->days;
                                            
                                            echo date('d M Y', strtotime($bill['due_date']));
                                            
                                            if ($bill['payment_status'] !== 'paid') {
                                                if ($daysLeft > 0) {
                                                    echo " <small class='text-muted'>({$daysLeft} days left)</small>";
                                                } elseif ($daysLeft === 0) {
                                                    echo " <small class='text-warning'>(Due today)</small>";
                                                } else {
                                                    echo " <small class='text-danger'>({$daysLeft} days overdue)</small>";
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td><?= $bill['consumption_units'] ?> units</td>
                                        <td><?= formatCurrency($bill['amount_due']) ?></td>
                                        <td><?= formatCurrency($bill['amount_paid']) ?></td>
                                        <td><?= formatCurrency($bill['balance']) ?></td>
                                        <td>
                                            <?php 
                                                $isVerified = false; 
                                                try { 
                                                    require_once dirname(__DIR__, 2) . '/models/Payment.php'; 
                                                    $pm = new Payment(); 
                                                    $pps = $pm->getPaymentsByBill((int)$bill['id']); 
                                                    foreach ($pps as $pv) { if (strtolower($pv['status'] ?? '') === 'confirmed_and_verified') { $isVerified = true; break; } }
                                                } catch (\Throwable $e) {}
                                            ?>
                                            <span class="badge bg-<?= getStatusBadgeClass($bill['payment_status']) ?>">
                                                <?= ucfirst(str_replace('_', ' ', $bill['payment_status'])) ?>
                                                <?php if ($isVerified): ?><i class="fas fa-check-circle" style="color:#10b981;margin-left:6px;"></i><?php endif; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="index.php?page=view_bill_details&bill_id=<?= (int)$bill['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
// Initialize DataTable for better user experience
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('billsTable')) {
        $('#billsTable').DataTable({
            "order": [[3, "desc"]], // Sort by bill date (newest first)
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]]
        });
    }
});
</script>
