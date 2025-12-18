<?php
// app/views/client/billing_dashboard.php

// Ensure this page is only accessible to clients
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php?page=login');
    exit;
}

// Extract data from controller
$client = $data['client'] ?? null;
$current_plan = $data['current_plan'] ?? null;
$recent_bills = $data['recent_bills'] ?? [];
$billing_summary = $data['billing_summary'] ?? [];
$meters = $data['meters'] ?? [];
$error = $data['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* General Styling */
        .client-theme {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .client-dashboard-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .client-sidebar {
            width: 220px;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 20px 0;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .client-sidebar h3 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 0 15px;
        }

        .client-sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .client-sidebar li a {
            display: block;
            padding: 10px 15px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-size: 0.9rem;
        }

        .client-sidebar li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid #fff;
        }

        .client-sidebar li a.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 3px solid #fff;
            font-weight: 500;
        }

        .client-sidebar li a i {
            margin-right: 8px;
            width: 18px;
            text-align: center;
            font-size: 0.9rem;
        }

        /* Main Content Styling */
        .client-main-content {
            flex: 1;
            transition: all 0.3s ease;
            padding: 15px;
        }

        .client-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        /* Overlay for meter quick details */
        .overlay-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.35);
            backdrop-filter: blur(2px);
            display: none;
            z-index: 1000;
        }
        .overlay-card {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 92%;
            max-width: 720px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 12px 24px rgba(0,0,0,0.2);
            z-index: 1001;
            display: none;
        }
        .overlay-card .card-header {
            display:flex;justify-content:space-between;align-items:center;
            background: linear-gradient(135deg, #3498db, #2980b9); color:#fff;
        }
        .overlay-close {
            border:none;background:transparent;color:#fff;font-size:1.2rem;cursor:pointer;
        }
        .kv-row { display:flex; gap:12px; margin-bottom:10px; }
        .kv { flex:1; background:#f9fbfd; border:1px solid #eef2f7; border-radius:6px; padding:10px; }
        .kv small { color:#6b7280; display:block; margin-bottom:4px; }
        .kv strong { color:#111827; }

        .client-header-bar h1 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info span {
            font-size: 0.9rem;
            color: #555;
        }

        .user-info a {
            color: #e74c3c;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .client-sidebar-toggle {
            display: none;
            background: none;
            border: none;
            color: #2c3e50;
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        /* Card Styling with Animations */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            border-bottom: none;
            padding: 15px 20px;
        }
        
        .card-header h5 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Status Badge Styling */
        .badge {
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        /* Button Styling */
        .btn {
            border-radius: 6px;
            font-weight: 500;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9, #1c6ea4);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-info {
            background: linear-gradient(135deg, #17a2b8, #138496);
            border: none;
            color: white;
        }
        
        .btn-info:hover {
            background: linear-gradient(135deg, #138496, #0f6674);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #218838);
            border: none;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #218838, #1e7e34);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .client-sidebar {
                position: fixed;
                left: -250px;
                top: 0;
                height: 100%;
                bottom: 0;
                z-index: 1000;
            }
            
            .client-sidebar.visible {
                left: 0;
            }
            
            .client-main-content {
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .client-main-content.full-width {
                margin-left: 0;
            }
            
            .client-sidebar-toggle {
                display: block;
            }
            
            .client-header-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .user-info {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body class="client-theme">
    <div class="client-dashboard-layout">
        <!-- Include Sidebar -->
        <?php include_once dirname(__DIR__) . '/includes/client_sidebar.php'; ?>

        <div class="client-main-content" id="clientMainContent">
            <div class="client-header-bar">
                <button class="client-sidebar-toggle" id="clientSidebarToggle"><i class="fas fa-bars"></i></button>
                <h1>My Billing Dashboard</h1>
                <div class="user-info">
                    <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <div class="container-fluid p-0">
    <!-- Heading is already in the header bar -->
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                    <h5 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Current Billing Plan</h5>
                </div>
                <div class="card-body">
                    <?php if ($current_plan): ?>
                        <h4><?= htmlspecialchars($current_plan['plan_name']) ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($current_plan['description']) ?></p>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Base Rate</label>
                                    <h5>KES <?= number_format($current_plan['base_rate'], 2) ?></h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Unit Rate</label>
                                    <h5>KES <?= number_format($current_plan['unit_rate'], 2) ?> per unit</h5>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Billing Cycle</label>
                                    <h5><?= ucfirst($current_plan['billing_cycle']) ?></h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Next Billing Date</label>
                                    <h5><?= date('d M Y', strtotime($current_plan['next_billing_date'])) ?></h5>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($current_plan['min_consumption']) || !empty($current_plan['max_consumption'])): ?>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php if (!empty($current_plan['min_consumption'])): ?>
                                Minimum billable consumption: <?= number_format($current_plan['min_consumption'], 2) ?> units.
                            <?php endif; ?>
                            <?php if (!empty($current_plan['max_consumption'])): ?>
                                <?= !empty($current_plan['min_consumption']) ? '<br>' : '' ?>
                                Maximum consumption at this rate: <?= number_format($current_plan['max_consumption'], 2) ?> units.
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> You are not currently subscribed to any billing plan.
                            Please contact customer support for assistance.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Billing Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-4">
                                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 d-inline-flex mb-3">
                                            <i class="fas fa-clock text-warning fa-2x"></i>
                                        </div>
                                        <h6 class="text-muted">Pending Bills</h6>
                                        <h3 class="mt-2 mb-1"><?= $billing_summary['pending_count'] ?? 0 ?></h3>
                                        <p class="mb-0 text-primary fw-bold">KES <?= number_format($billing_summary['pending_amount'] ?? 0, 2) ?></p>
                                    </div>
                                </div>
                            </div>
                        
                        <div class="col-md-6 mb-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-4">
                                        <div class="rounded-circle bg-danger bg-opacity-10 p-3 d-inline-flex mb-3">
                                            <i class="fas fa-exclamation-circle text-danger fa-2x"></i>
                                        </div>
                                        <h6 class="text-muted">Overdue Bills</h6>
                                        <h3 class="mt-2 mb-1"><?= $billing_summary['overdue_count'] ?? 0 ?></h3>
                                        <p class="mb-0 text-danger fw-bold">KES <?= number_format($billing_summary['overdue_amount'] ?? 0, 2) ?></p>
                                    </div>
                                </div>
                            </div>
                        
                        <div class="col-md-6 mb-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-4">
                                        <div class="rounded-circle bg-info bg-opacity-10 p-3 d-inline-flex mb-3">
                                            <i class="fas fa-calendar-alt text-info fa-2x"></i>
                                        </div>
                                        <h6 class="text-muted">Last Month</h6>
                                        <h3 class="mt-2 mb-1">KES <?= number_format($billing_summary['last_month_amount'] ?? 0, 2) ?></h3>
                                        <p class="mb-0 text-info fw-bold"><?= number_format($billing_summary['last_month_consumption'] ?? 0, 2) ?> units</p>
                                    </div>
                                </div>
                            </div>
                        
                        <div class="col-md-6 mb-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-4">
                                        <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-flex mb-3">
                                            <i class="fas fa-chart-line text-success fa-2x"></i>
                                        </div>
                                        <h6 class="text-muted">Average Monthly</h6>
                                        <h3 class="mt-2 mb-1">KES <?= number_format($billing_summary['average_amount'] ?? 0, 2) ?></h3>
                                        <p class="mb-0 text-success fw-bold"><?= number_format($billing_summary['average_consumption'] ?? 0, 2) ?> units</p>
                                    </div>
                                </div>
                            </div>

                        <div class="col-md-6 mb-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-4">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-flex mb-3">
                                            <i class="fas fa-file-invoice-dollar text-primary fa-2x"></i>
                                        </div>
                                        <h6 class="text-muted">Bills Sent To You</h6>
                                        <h3 class="mt-2 mb-1"><?= (int)($sent_bills_count ?? 0) ?></h3>
                                        <div class="d-grid mt-2">
                                            <a href="index.php?page=client_payments&show=sent" class="btn btn-sm" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
                                                <i class="fas fa-list me-2"></i> View Sent Bills
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <div class="col-md-6 mb-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-4">
                                        <div class="rounded-circle bg-secondary bg-opacity-10 p-3 d-inline-flex mb-3">
                                            <i class="fas fa-wallet text-secondary fa-2x"></i>
                                        </div>
                                        <h6 class="text-muted">Total Outstanding Balance</h6>
                                        <h3 class="mt-2 mb-1">KES <?= number_format($billing_summary['total_balance'] ?? 0, 2) ?></h3>
                                        <div class="d-grid mt-2">
                                            <a href="index.php?page=client_payments" class="btn btn-sm" style="background: linear-gradient(135deg, #28a745, #218838); color: white;">
                                                <i class="fas fa-money-bill-wave me-2"></i> Go to Payments
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <a href="index.php?page=client_view_bills" class="btn" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
                            <i class="fas fa-file-invoice-dollar me-2"></i> View Bills
                        </a>
                        <a href="index.php?page=client_payments" class="btn" style="background: linear-gradient(135deg, #28a745, #218838); color: white;">
                            <i class="fas fa-money-bill-wave me-2"></i> Go to Payments
                        </a>
                        <a href="index.php?page=client_view_bills" class="btn" style="background: linear-gradient(135deg, #e67e22, #d35400); color: white;">
                            <i class="fas fa-exclamation-circle me-2"></i> View Pending Bills
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Bills Sent To You</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($sent_bills)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Bill #</th>
                                        <th>Meter</th>
                                        <th>Bill Date</th>
                                        <th>Due Date</th>
                                        <th>Consumption</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sent_bills as $bill): ?>
                                    <tr>
                                        <td><?= $bill['id'] ?></td>
                                        <td>
                                            <?php if (!empty($bill['meter_id']) && !empty($bill['serial_number'])): ?>
                                                <a href="index.php?page=client_meters&meter_id=<?= (int)$bill['meter_id'] ?>" class="link-primary" title="Open meter">
                                                    <?= htmlspecialchars($bill['serial_number']) ?>
                                                </a>
                                            <?php else: ?>
                                                <?= htmlspecialchars($bill['serial_number'] ?? 'N/A') ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d M Y', strtotime($bill['bill_date'])) ?></td>
                                        <td><?= date('d M Y', strtotime($bill['due_date'])) ?></td>
                                        <td><?= number_format($bill['consumption_units'] ?? 0, 2) ?> units</td>
                                        <td>KES <?= number_format($bill['amount_due'], 2) ?></td>
                                        <td>
                                            <?php $clientStatus = strtolower($bill['client_bill_status'] ?? ''); $status = strtolower($bill['payment_status'] ?? ($bill['status'] ?? 'pending')); $combined = $clientStatus ?: $status; ?>
                                            <?php if ($combined == 'paid' || $combined == 'confirmed_and_verified'): ?>
                                                <span class="badge" style="background: linear-gradient(135deg, #28a745, #218838);">Paid</span>
                                            <?php elseif ($combined == 'partial' || $combined == 'partially_paid'): ?>
                                                <span class="badge" style="background: linear-gradient(135deg, #ffc107, #d39e00);">Partially Paid</span>
                                            <?php elseif ($combined == 'overdue'): ?>
                                                <span class="badge" style="background: linear-gradient(135deg, #dc3545, #c82333);">Overdue</span>
                                            <?php else: ?>
                                                <span class="badge" style="background: linear-gradient(135deg, #6c757d, #5a6268);">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="index.php?page=client_bill_details&bill_id=<?= $bill['id'] ?>" class="btn btn-sm" style="background: linear-gradient(135deg, #17a2b8, #138496); color: white;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (!in_array($combined, ['paid','confirmed_and_verified'], true)): ?>
                                            <a href="index.php?page=client_payments&bill_id=<?= $bill['id'] ?>" class="btn btn-sm" style="background: linear-gradient(135deg, #28a745, #218838); color: white;">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No bills have been sent to you.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-tachometer-alt"></i> My Meters</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-primary"><?= (int)($meters_count ?? 0) ?></span>
                        <a href="index.php?page=client_meters" class="btn btn-sm btn-light">
                            <i class="fas fa-map"></i> View Meters
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($meters)): ?>
                        <div class="row">
                            <?php foreach ($meters as $meter): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-tint me-2 text-primary"></i><?= htmlspecialchars($meter['serial_number']) ?></h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-light rounded-circle p-2 me-3">
                                                <i class="fas fa-map-marker-alt text-primary"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Location</small>
                                                <strong><?= htmlspecialchars($meter['location']) ?></strong>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-light rounded-circle p-2 me-3">
                                                <i class="fas fa-tag text-primary"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Type</small>
                                                <strong><?= htmlspecialchars($meter['meter_type']) ?></strong>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-light rounded-circle p-2 me-3">
                                                <i class="fas fa-tachometer-alt text-primary"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Last Reading</small>
                                                <strong><?= number_format($meter['last_reading'] ?? 0, 2) ?> units</strong>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle p-2 me-3">
                                                <i class="fas fa-calendar-alt text-primary"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Last Reading Date</small>
                                                <strong><?= !empty($meter['last_reading_date']) ? date('d M Y', strtotime($meter['last_reading_date'])) : 'N/A' ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-top-0 text-center">
                                        <button class="btn btn-primary view-readings-btn"
                                                data-id="<?= (int)$meter['id'] ?>"
                                                data-serial="<?= htmlspecialchars($meter['serial_number']) ?>"
                                                data-type="<?= htmlspecialchars($meter['meter_type'] ?? '') ?>"
                                                data-status="<?= htmlspecialchars($meter['status'] ?? '') ?>"
                                                data-location="<?= htmlspecialchars($meter['location'] ?? '') ?>"
                                                data-installed="<?= htmlspecialchars(($meter['installation_date'] ?? '') ?: '') ?>"
                                                data-last="<?= htmlspecialchars(isset($meter['last_reading']) ? (string)$meter['last_reading'] : '') ?>"
                                                data-last-date="<?= htmlspecialchars(isset($meter['last_reading_date']) ? (string)$meter['last_reading_date'] : '') ?>"
                                                data-prev="<?= htmlspecialchars(isset($meter['prev_reading']) ? (string)$meter['prev_reading'] : '') ?>"
                                                data-prev-date="<?= htmlspecialchars(isset($meter['prev_reading_date']) ? (string)$meter['prev_reading_date'] : '') ?>"
                                                data-has-bill="<?= !empty($meter['has_bill']) ? '1' : '0' ?>">
                                            <i class="fas fa-chart-line me-2"></i> View Readings
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No meters found for your account.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>
    
    <!-- Meter Quick Details Overlay -->
    <div class="overlay-backdrop" id="meterOverlayBackdrop"></div>
    <div class="overlay-card" id="meterOverlayCard">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-tint me-2"></i><span id="ovSerial">Meter</span></h5>
            <button class="overlay-close" id="meterOverlayClose" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <div class="card-body">
            <div class="kv-row">
                <div class="kv">
                    <small>Previous Reading</small>
                    <strong id="ovPrev">N/A</strong>
                    <div class="text-muted" style="font-size:0.85rem;" id="ovPrevDate">—</div>
                </div>
                <div class="kv">
                    <small>Current Reading</small>
                    <strong id="ovCurrent">N/A</strong>
                    <div class="text-muted" style="font-size:0.85rem;" id="ovCurrentDate">—</div>
                </div>
            </div>
            <div class="kv-row">
                <div class="kv">
                    <small>Installation / Verification Date</small>
                    <strong id="ovInstalled">N/A</strong>
                </div>
                <div class="kv">
                    <small>Billing Status</small>
                    <strong id="ovHasBill">No bill</strong>
                </div>
            </div>
            <div class="kv-row">
                <div class="kv">
                    <small>Type</small>
                    <strong id="ovType">—</strong>
                </div>
                <div class="kv">
                    <small>Location</small>
                    <strong id="ovLocation">—</strong>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <a href="index.php?page=client_meters" class="btn btn-primary"><i class="fas fa-map me-2"></i> View Full Information</a>
                <button class="btn btn-secondary" id="meterOverlayClose2"><i class="fas fa-times me-2"></i> Close</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('clientSidebarToggle');
            const sidebar = document.querySelector('.client-sidebar');
            const mainContent = document.getElementById('clientMainContent');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('visible');
                    mainContent.classList.toggle('full-width');
                });
            }
            
            // Set active link in sidebar
            const currentPage = 'client_billing_dashboard';
            const sidebarLinks = document.querySelectorAll('.client-sidebar li a');
            
            sidebarLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && href.includes('page=' + currentPage)) {
                    link.classList.add('active');
                }
            });

            // Meter overlay interactions
            function openMeterOverlay(payload) {
                document.getElementById('ovSerial').textContent = payload.serial || 'Meter';
                document.getElementById('ovPrev').textContent = payload.prev || 'N/A';
                document.getElementById('ovPrevDate').textContent = payload.prevDate ? new Date(payload.prevDate).toLocaleString() : '—';
                document.getElementById('ovCurrent').textContent = payload.last || 'N/A';
                document.getElementById('ovCurrentDate').textContent = payload.lastDate ? new Date(payload.lastDate).toLocaleString() : '—';
                document.getElementById('ovInstalled').textContent = payload.installed ? new Date(payload.installed).toLocaleDateString() : 'N/A';
                document.getElementById('ovHasBill').textContent = payload.hasBill === '1' ? 'Has bill(s)' : 'No bill';
                document.getElementById('ovType').textContent = payload.type || '—';
                document.getElementById('ovLocation').textContent = payload.location || '—';
                document.getElementById('meterOverlayBackdrop').style.display = 'block';
                document.getElementById('meterOverlayCard').style.display = 'block';
            }
            function closeMeterOverlay() {
                document.getElementById('meterOverlayBackdrop').style.display = 'none';
                document.getElementById('meterOverlayCard').style.display = 'none';
            }
            const closeBtn = document.getElementById('meterOverlayClose');
            const closeBtn2 = document.getElementById('meterOverlayClose2');
            const backdrop = document.getElementById('meterOverlayBackdrop');
            [closeBtn, closeBtn2].forEach(function(btn){ if (btn) btn.addEventListener('click', closeMeterOverlay); });
            if (backdrop) backdrop.addEventListener('click', closeMeterOverlay);
            document.querySelectorAll('.view-readings-btn').forEach(function(btn){
                btn.addEventListener('click', function(){
                    openMeterOverlay({
                        serial: this.dataset.serial,
                        type: this.dataset.type,
                        status: this.dataset.status,
                        location: this.dataset.location,
                        installed: this.dataset.installed,
                        last: this.dataset.last,
                        lastDate: this.dataset['lastDate'],
                        prev: this.dataset.prev,
                        prevDate: this.dataset['prevDate'],
                        hasBill: this.dataset.hasBill
                    });
                });
            });
        });
    </script>
</body>
</html>
