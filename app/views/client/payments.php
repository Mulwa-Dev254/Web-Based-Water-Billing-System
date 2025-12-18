<?php
// app/views/client/payments.php

// Ensure this page is only accessible to clients
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php?page=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Client Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .client-header-bar h1 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin: 0;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
        }

        .user-info a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 500;
        }

        .user-info a:hover {
            text-decoration: underline;
        }

        .client-sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.1rem;
            cursor: pointer;
            color: #2c3e50;
            display: none;
        }

        /* Content Sections */
        .client-content-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .client-content-section {
            background: white;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .client-content-section h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #2c3e50;
            font-size: 1.1rem;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .summary-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            align-items: stretch;
            margin-bottom: 20px;
        }

        .summary-card {
            position: relative;
            border-radius: 14px;
            padding: 18px 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            border: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            color: #fff;
            overflow: hidden;
        }

        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.12);
        }

        .summary-card h3 {
            border-bottom: none;
            margin-bottom: 8px;
            font-size: 1rem;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .summary-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255,255,255,0.18);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .summary-count {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            line-height: 1;
            margin: 6px 0;
        }

        .summary-desc {
            color: rgba(255,255,255,0.9);
            font-size: 0.85rem;
        }

        /* Card theme variants */
        .summary-card--bills {
            background: linear-gradient(135deg, #4f7bd7 0%, #2d5bbf 100%);
        }

        .summary-card--plans {
            background: linear-gradient(135deg, #0ea5a3 0%, #0a7f7e 100%);
        }

        .summary-card--services {
            background: linear-gradient(135deg, #ee6c4d 0%, #d25436 100%);
        }

        .summary-card--meters {
            background: linear-gradient(135deg, #6c5ce7 0%, #4b3bc1 100%);
        }
        .summary-card--warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        .summary-card--flagged {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        .summary-card--verified {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        /* Two-column content layout for sections */
        .content-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 16px;
        }
        @media (max-width: 992px) {
            .content-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 992px) {
            .summary-row { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 640px) {
            .summary-row { grid-template-columns: 1fr; }
        }

        /* Form Styling */
        .client-form-section {
            background: white;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
            font-size: 0.85rem;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        /* Button Styling */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 0.85rem;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        /* Alert Styling */
        .alert {
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-error {
            background-color: #fdecea;
            color: #d32f2f;
            border-left: 4px solid #d32f2f;
        }

        .alert-success {
            background-color: #e8f5e9;
            color: #388e3c;
            border-left: 4px solid #388e3c;
        }

        .alert-info {
            background-color: #e3f2fd;
            color: #1565c0;
            border-left: 4px solid #1565c0;
        }

        /* Table Styling */
        .table-responsive {
            overflow-x: auto;
        }

        .client-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .client-table th, .client-table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .client-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .client-table tr:hover {
            background-color: #f8f9fa;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3e0;
            color: #e65100;
        }

        .status-active, .status-completed, .status-paid {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .status-failed {
            background-color: #ffebee;
            color: #d32f2f;
        }

        .status-cancelled {
            background-color: #f5f5f5;
            color: #757575;
        }

        .status-pending_payment {
            background-color: #e0f2fe;
            color: #0288d1;
        }

        /* Payment Form Specific Styles */
        .text-muted {
            color: #777;
            font-size: 0.85rem;
        }

        .text-center {
            text-align: center;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .mt-2 {
            margin-top: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .client-sidebar {
                position: fixed;
                left: -220px;
                top: 0;
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
    <?php include_once __DIR__ . '/partials/status_badges.php'; ?>
</head>
<body class="client-theme">
    <div class="client-dashboard-layout">
        <!-- Sidebar Navigation -->
        <?php include_once dirname(__DIR__) . '/includes/client_sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="client-main-content" id="clientMainContent">
            <!-- Header Bar -->
            <div class="client-header-bar">
                <button class="client-sidebar-toggle" id="clientSidebarToggle"><i class="fas fa-bars"></i></button>
                <h1><i class="fas fa-credit-card"></i> Payment History & Options</h1>
                <div class="user-info">
                    <span>Hello, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                    <a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>

            </div>

            <!-- Page Content -->
            <div class="client-content-container">
                

                <!-- Billing and services summary row under meter overview -->
                <div class="summary-row">
                    <div class="summary-card summary-card--bills" id="sentBillsSummaryCard" style="cursor:pointer;" role="button" aria-label="View outstanding bills">
                        <h3>
                            <span class="summary-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                            Outstanding Bills
                        </h3>
                        <div class="summary-count">
                            <?= (int)($data['sentBillsCount'] ?? 0) ?>
                        </div>
                        <div class="summary-desc">Bills sent to you</div>
                    </div>
                    <div class="summary-card summary-card--plans" id="renewablePlansSummaryCard" style="cursor:pointer;" role="button" aria-label="View plans due for renewal">
                        <h3>
                            <span class="summary-icon"><i class="fas fa-sync-alt"></i></span>
                            Plans Due for Renewal
                        </h3>
                        <div class="summary-count">
                            <?= (int)count($data['renewablePlans'] ?? []) ?>
                        </div>
                        <div class="summary-desc">Plans requiring renewal</div>
                    </div>
                    <div class="summary-card summary-card--services" id="outstandingServicesSummaryCard" style="cursor:pointer;" role="button" aria-label="View outstanding service payments">
                        <h3>
                            <span class="summary-icon"><i class="fas fa-exclamation-circle"></i></span>
                            Outstanding Service Payments
                        </h3>
                        <div class="summary-count">
                            <?= (int)count($data['outstandingServices'] ?? []) ?>
                        </div>
                        <div class="summary-desc">Services pending payment</div>
                    </div>
                </div>
                <?php if (!empty($data['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($data['error']); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($data['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($data['success']); ?>
                    </div>
                <?php endif; ?>

                <!-- Services Pending Confirmation Reminder -->
                <?php if (!empty($data['servicesPendingConfirmation'])): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> There are services that are marked as 'Serviced' but not yet 'Confirmed'. Please go to <a href="index.php?page=client_apply_service" class="text-blue-700 font-semibold underline">Apply for Service</a> to confirm them for payment.
                    </div>
                <?php endif; ?>


                <div class="content-grid">
                <!-- Outstanding Services Section -->
                <div class="client-content-section" id="outstandingServicesSection">
                    <h3><i class="fas fa-exclamation-circle"></i> Outstanding Service Payments</h3>
                    <?php if (!empty($data['outstandingServices'])): ?>
                        <div class="table-responsive">
                            <table class="client-table">
                                <thead>
                                    <tr>
                                        <th>Service Name</th>
                                        <th>Cost (KSh)</th>
                                        <th>Status</th>
                                        <th>Application Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['outstandingServices'] as $service): ?>
                                        <tr data-service-id="<?php echo htmlspecialchars($service['id']); ?>" id="service-row-<?php echo htmlspecialchars($service['id']); ?>">
                                            <td><?php echo htmlspecialchars($service['service_name'] ?? 'N/A'); ?></td>
                                            <td>KSh <?php echo number_format($service['cost'] ?? 0, 2); ?></td>
                                            <td>
                                                <?php if (!empty($service['has_pending_payment'])): ?>
                                                    <span class="status-badge status-pending">
                                                        Payment Initiated<?php if (!empty($service['latest_transaction_short'])): ?> • Ref <?php echo htmlspecialchars($service['latest_transaction_short']); ?><?php endif; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="status-badge status-<?php echo strtolower($service['status'] ?? ''); ?>">
                                                        <?php echo htmlspecialchars(ucfirst($service['status'] ?? 'Pending')); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($service['application_date'] ?? 'now')); ?></td>
                                            <td>
                                                <?php if (!empty($service['has_pending_payment'])): ?>
                                                    <a href="index.php?page=client_payments&service_id=<?= htmlspecialchars($service['id']); ?>&mode=view#serviceStatusTracker" class="btn btn-outline btn-sm">
                                                        <i class="fas fa-eye"></i> View Tracker<?php if (!empty($service['latest_transaction_short'])): ?> • Ref <?php echo htmlspecialchars($service['latest_transaction_short']); ?><?php endif; ?>
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-primary btn-sm pay-button" 
                                                            data-type="service" 
                                                            data-id="<?php echo htmlspecialchars($service['id']); ?>" 
                                                            data-amount="<?php echo number_format($service['cost'], 2, '.', ''); ?>"
                                                            data-name="<?php echo htmlspecialchars($service['service_name']); ?>">
                                                        <i class="fas fa-money-bill-wave"></i> Pay Now
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No outstanding service payments.</p>
                    <?php endif; ?>
                </div>

                <!-- Renewable Plans Section -->
                <div class="client-content-section" id="renewablePlansSection">
                    <h3><i class="fas fa-sync-alt"></i> Plans Due for Renewal</h3>
                    <?php if (!empty($data['renewablePlans'])): ?>
                        <div class="table-responsive">
                            <table class="client-table">
                                <thead>
                                    <tr>
                                        <th>Plan Name</th>
                                        <th>Base Rate (KSh)</th>
                                        <th>Billing Cycle</th>
                                        <th>Status</th>
                                        <th>Next Bill Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['renewablePlans'] as $plan): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($plan['plan_name'] ?? 'N/A'); ?></td>
                                            <td>KSh <?php echo number_format($plan['base_rate'] ?? 0, 2); ?></td>
                                            <td><?php echo htmlspecialchars(ucfirst($plan['billing_cycle'] ?? 'Monthly')); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo strtolower($plan['status'] ?? ''); ?>">
                                                    <?php echo htmlspecialchars(ucfirst($plan['status'] ?? 'Active')); ?>
                                                    <?php if ($plan['status'] == 'active' && !empty($plan['payment_status']) && $plan['payment_status'] == 'pending'): ?>
                                                        (Payment Due)
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $plan['next_billing_date'] ? date('M d, Y', strtotime($plan['next_billing_date'])) : 'N/A'; ?></td>
                                            <td>
                                                <?php if (($plan['status'] == 'active' || $plan['status'] == 'expired') && 
                                                      (empty($plan['payment_date']) || $plan['payment_status'] == 'pending')): ?>
                                                    <button class="btn btn-primary btn-sm pay-button" 
                                                            data-type="plan" 
                                                            data-id="<?php echo htmlspecialchars($plan['id']); ?>" 
                                                            data-amount="<?php echo number_format($plan['base_rate'], 2, '.', ''); ?>"
                                                            data-name="<?php echo htmlspecialchars($plan['plan_name']); ?>">
                                                        <i class="fas fa-redo"></i> Renew Now
                                                    </button>
                                                <?php elseif (!empty($plan['payment_date'])): ?>
                                                    <span class="status-badge status-paid">Paid</span>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No plans currently due for renewal.</p>
                    <?php endif; ?>
                </div>

                <!-- Payment Modal (initially hidden) -->
                <div class="client-form-section" id="paymentModal" style="display: none;">
                    <h3><i class="fas fa-mobile-alt"></i> Complete M-Pesa Payment</h3>
                    <p class="text-muted">You are about to pay for: <strong id="paymentItemName"></strong></p>
                    <form action="index.php?page=client_payments" method="POST" id="paymentForm">
                        <input type="hidden" name="action" value="process_mpesa_payment">
                        <input type="hidden" name="payment_type" id="paymentTypeInput">
                        <input type="hidden" name="item_id" id="itemIdInput">
                        
                        <div class="form-group">
                            <label for="amountInput">Amount (KSh):</label>
                            <input type="number" id="amountInput" name="amount" class="form-control" step="0.01" required readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone_number">M-Pesa Phone Number (2547XXXXXXXX):</label>
                            <input type="text" id="phone_number" name="phone_number" class="form-control" 
                                   placeholder="e.g., 254712345678" pattern="2547[0-9]{8}" 
                                   title="Enter a valid M-Pesa phone number (2547XXXXXXXX)" required>
                            <small class="text-muted">Ensure your phone number is registered with M-Pesa and has sufficient funds.</small>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block" id="submitPaymentBtn">
                                <i class="fas fa-mobile-alt"></i> Initiate M-Pesa STK Push
                            </button>
                            <button type="button" class="btn btn-secondary btn-block mt-2" onclick="closePaymentModal()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
                </div><!-- /.content-grid -->

                <!-- Service Status Tracker (appears when a service is selected) -->
                <div class="client-content-section" id="serviceStatusTracker" style="display:none;" data-service-id="<?php echo htmlspecialchars(($data['selectedService']['id'] ?? '')); ?>">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                        <h3><i class="fas fa-stream"></i> Service Request Tracker</h3>
                        <button type="button" class="btn btn-outline" style="display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:6px;" onclick="closeServiceTracker()">
                            <i class="fas fa-times"></i>
                            Close
                        </button>
                    </div>
                    <?php 
                        $ss = $data['selectedService'] ?? null; 
                        $ssp = $data['selectedServicePayments'] ?? []; 
                        $initiated = !empty($ssp);
                        $completedSvc = false; foreach ($ssp as $p) { if (strtolower($p['status'] ?? '') === 'completed' || strtolower($p['status'] ?? '') === 'confirmed_and_verified') { $completedSvc = true; break; } }
                        $svcStatus = $ss ? (string)($ss['status'] ?? 'pending_payment') : '';
                    ?>
                    <?php if ($ss): ?>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:12px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="status-badge status-<?php echo ($ss['application_date'] ? 'active' : 'pending'); ?>">Requested</span>
                            <i class="fas fa-chevron-right" style="color:#999;"></i>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="status-badge status-<?php echo (in_array(strtolower($svcStatus), ['serviced','completed','confirmed','pending_payment','approved','paid']) ? 'active' : 'pending'); ?>">Serviced/Completed</span>
                            <i class="fas fa-chevron-right" style="color:#999;"></i>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="status-badge status-<?php echo (in_array(strtolower($svcStatus), ['confirmed','pending_payment','approved','paid']) ? 'active' : 'pending'); ?>">Confirmed</span>
                            <i class="fas fa-chevron-right" style="color:#999;"></i>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="status-badge status-<?php echo ($initiated ? 'active' : 'pending'); ?>">Payment Initiated</span>
                            <i class="fas fa-chevron-right" style="color:#999;"></i>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="status-badge status-<?php echo ($completedSvc || strtolower($svcStatus)==='paid' ? 'paid' : 'pending'); ?>">Payment Completed</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="client-table">
                            <thead>
                                <tr><th>Event</th><th>Detail</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Request Created</td><td><?php echo htmlspecialchars($ss['service_name'] ?? 'Service'); ?> applied</td><td><?php echo htmlspecialchars(date('M d, Y', strtotime($ss['application_date'] ?? date('Y-m-d')))); ?></td></tr>
                                <?php if (!empty($ss['updated_at'])): ?><tr><td>Status</td><td><?php echo htmlspecialchars(ucfirst($svcStatus)); ?></td><td><?php echo htmlspecialchars($ss['updated_at']); ?></td></tr><?php endif; ?>
                                <?php foreach ($ssp as $p): ?>
                                    <tr><td>Payment</td><td><?php echo htmlspecialchars(ucfirst($p['status'] ?? 'pending')); ?> via <?php echo htmlspecialchars($p['payment_method'] ?? 'M-Pesa'); ?> (<?php echo htmlspecialchars($p['transaction_id'] ?? 'N/A'); ?>)</td><td><?php echo htmlspecialchars($p['payment_date'] ?? '-'); ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Bill Status Tracker (appears when a bill is selected) -->
                <div class="client-content-section" id="billStatusTracker" style="display:none;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                        <h3><i class="fas fa-stream"></i> Bill Status Tracker</h3>
                        <button type="button" class="btn btn-outline" style="display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:6px;" onclick="closeBillTracker()">
                            <i class="fas fa-times"></i>
                            Close
                        </button>
                    </div>
                    <?php 
                        $sb = $data['selectedBill'] ?? null; 
                        $sbp = $data['selectedBillPayments'] ?? []; 
                        $scb = $data['selectedClientBill'] ?? null; 
                        $delivered = $scb && (($scb['bill_status'] ?? '') === 'delivered' || ($scb['bill_status'] ?? '') === 'paid');
                        $initiated = !empty($sbp);
                        $completed = false; foreach ($sbp as $p) { if (($p['status'] ?? '') === 'completed') { $completed = true; break; } }
                        $billStatus = $sb ? (string)($sb['status'] ?? ($sb['payment_status'] ?? 'pending')) : '';
                    ?>
                    <?php if ($sb): ?>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:12px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="status-badge <?= $delivered ? 'status-active' : 'status-pending' ?>">Delivered</span>
                            <i class="fas fa-chevron-right" style="color:#999;"></i>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="status-badge <?= $initiated ? 'status-active' : 'status-pending' ?>">Payment Initiated</span>
                            <i class="fas fa-chevron-right" style="color:#999;"></i>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="status-badge <?= $completed ? 'status-active' : 'status-pending' ?>">Payment Completed</span>
                            <i class="fas fa-chevron-right" style="color:#999;"></i>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="status-badge <?= strtolower($billStatus)==='paid' ? 'status-paid' : (strtolower($billStatus)==='partial' || strtolower($billStatus)==='partially_paid' ? 'status-pending' : 'status-pending') ?>">Bill <?= htmlspecialchars(ucfirst(str_replace('_',' ', $billStatus))) ?></span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="client-table">
                            <thead>
                                <tr><th>Event</th><th>Detail</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Bill Created</td><td>Bill #<?= (int)$sb['id'] ?> issued</td><td><?= htmlspecialchars(date('M d, Y', strtotime($sb['bill_date'] ?? date('Y-m-d')))) ?></td></tr>
                                <tr><td>Due</td><td>Due date</td><td><?= htmlspecialchars(date('M d, Y', strtotime($sb['due_date'] ?? date('Y-m-d')))) ?></td></tr>
                                <?php if ($delivered): ?><tr><td>Delivered</td><td>Bill delivered to client</td><td><?= htmlspecialchars($scb['created_at'] ?? '-') ?></td></tr><?php endif; ?>
                                <?php foreach ($sbp as $p): ?>
                                    <tr><td>Payment</td><td><?= htmlspecialchars(ucfirst($p['status'] ?? 'pending')) ?> via <?= htmlspecialchars($p['payment_method'] ?? 'M-Pesa') ?> (<?= htmlspecialchars($p['transaction_id'] ?? 'N/A') ?>)</td><td><?= htmlspecialchars($p['payment_date'] ?? '-') ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Payment History Section -->
                <div class="client-content-section">
                    <h3><i class="fas fa-history"></i> Payment History</h3>
                    <?php if (!empty($data['paymentHistory'])): ?>
                        <button onclick="printPaymentHistory()" class="btn btn-primary mb-4">
                            <i class="fas fa-print"></i> Print History
                        </button>
                        <div class="table-responsive">
                            <table class="client-table" id="paymentHistoryTable">
                                <thead>
                                    <tr>
                                        <th>Transaction ID</th>
                                        <th>Type</th>
                                        <th>Amount (KSh)</th>
                                        <th>Payment Method</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                        <th>Receipt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['paymentHistory'] as $payment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></td>
                                            <td><?php 
                                                $ptype = (string)($payment['type'] ?? '');
                                                if ($ptype === 'bill_payment') {
                                                    echo 'Billing Payment #'.(int)($payment['reference_id'] ?? 0);
                                                } elseif ($ptype === 'service_payment') {
                                                    echo 'Service Request #'.(int)($payment['reference_id'] ?? 0);
                                                } else {
                                                    echo htmlspecialchars(ucfirst(str_replace('_',' ', $ptype)));
                                                }
                                            ?></td>
                                            <td>KSh <?php echo number_format($payment['amount'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($payment['payment_date'])); ?></td>
                                            <td>
                                                <?php $pstat = strtolower($payment['status']); $plabel = ($pstat==='confirmed_and_verified')?'Confirmed & Verified':ucfirst($payment['status']); ?>
                                                <span class="status-badge status-<?php echo ($pstat==='confirmed_and_verified')?'completed':$pstat; ?>">
                                                    <?php echo htmlspecialchars($plabel); ?>
                                                    <?php if ($pstat==='confirmed_and_verified'): ?><i class="fas fa-check-circle" style="color:#10b981"></i><?php endif; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $ptype = (string)($payment['type'] ?? '');
                                                    $refId = (int)($payment['reference_id'] ?? 0);
                                                    $canViewBill = $ptype === 'bill_payment' && $refId > 0 && in_array((string)($payment['status'] ?? ''), ['pending','completed','failed','flagged','rejected','confirmed_and_verified'], true);
                                                    $canViewSvc = $ptype === 'service_payment' && $refId > 0 && in_array((string)($payment['status'] ?? ''), ['pending','completed','failed','flagged','rejected','confirmed_and_verified'], true);
                                                ?>
                                                <?php if ($canViewBill): ?>
                                                    <a href="index.php?page=client_payments&bill_id=<?= $refId ?>&mode=view#billStatusTracker" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                <?php elseif ($canViewSvc): ?>
                                                    <a href="index.php?page=client_payments&service_id=<?= $refId ?>&mode=view#serviceStatusTracker" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (in_array(strtolower($payment['status']), ['completed','confirmed_and_verified'], true)): ?>
                                                    <a href="index.php?page=client_generate_receipt&id=<?= (int)($payment['id'] ?? 0) ?>" class="btn btn-sm btn-primary" target="_blank">
                                                        <i class="fas fa-file-invoice"></i> Receipt
                                                    </a>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No payment history found.</p>
                    <?php endif; ?>
                </div>

                <!-- Bills Sent To You moved below history -->
                <div class="client-content-section" id="sentBillsPanel" style="display:none;">
                    <h3><i class="fas fa-list"></i> Bills Sent To You</h3>
                    <?php if (!empty($data['sentBills'])): ?>
                        <div class="table-responsive">
                            <table class="client-table">
                                <thead>
                                    <tr>
                                        <th>Bill #</th>
                                        <th>Amount Due (KSh)</th>
                                        <th>Amount Paid (KSh)</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Sent By</th>
                                        <th>Sent At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['sentBills'] as $bill): ?>
                                        <?php 
                                            $billId = (int)($bill['id'] ?? 0);
                                            $amountDue = (float)($bill['amount_due'] ?? 0);
                                            $amountPaid = (float)($bill['amount_paid'] ?? 0);
                                            $balance = max($amountDue - $amountPaid, 0);
                                            $status = (string)($bill['payment_status'] ?? 'pending');
                                        ?>
                                        <tr>
                                            <td><?= $billId ?></td>
                                            <td>KSh <?= number_format($amountDue, 2) ?></td>
                                            <td>KSh <?= number_format($amountPaid, 2) ?></td>
                                            <td>
                                                <span class="status-badge status-<?= htmlspecialchars(strtolower($status)) ?>">
                                                    <?= htmlspecialchars(ucfirst(str_replace('_',' ', $status))) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars(date('M d, Y', strtotime($bill['due_date'] ?? date('Y-m-d')))) ?></td>
                                            <td><?= htmlspecialchars($bill['sender_full_name'] ?? ($bill['sender_username'] ?? 'Finance')) ?></td>
                                            <td><?= htmlspecialchars($bill['sent_at'] ?? '-') ?></td>
                                            <td>
                                                <?php if ($balance > 0): ?>
                                                    <button class="btn btn-primary btn-sm pay-button"
                                                            data-type="bill"
                                                            data-id="<?= $billId ?>"
                                                            data-amount="<?= number_format($balance, 2, '.', '') ?>"
                                                            data-name="<?= 'Bill #' . $billId ?>">
                                                        <i class="fas fa-money-bill-wave"></i> Pay Now
                                                    </button>
                                                <?php else: ?>
                                                    <span class="status-badge status-paid">Paid</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No bills have been sent to you.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            document.getElementById('clientSidebarToggle').addEventListener('click', function() {
                document.getElementById('clientSidebar').classList.toggle('visible');
                document.getElementById('clientMainContent').classList.toggle('full-width');
            });

            // Toggle sent bills panel
            const summaryCard = document.getElementById('sentBillsSummaryCard');
            if (summaryCard) {
                summaryCard.addEventListener('click', function() {
                    const panel = document.getElementById('sentBillsPanel');
                    if (panel) {
                        const isShown = panel.style.display !== 'none';
                        panel.style.display = isShown ? 'none' : 'block';
                        if (!isShown) panel.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            }

            const servicesCard = document.getElementById('outstandingServicesSummaryCard');
            if (servicesCard) {
                servicesCard.addEventListener('click', function(){
                    const target = document.getElementById('outstandingServicesSection');
                    if (target) { target.scrollIntoView({ behavior: 'smooth' }); }
                });
            }
            const plansCard = document.getElementById('renewablePlansSummaryCard');
            if (plansCard) {
                plansCard.addEventListener('click', function(){
                    const target = document.getElementById('renewablePlansSection');
                    if (target) { target.scrollIntoView({ behavior: 'smooth' }); }
                });
            }

            // Handle payment button clicks
            document.querySelectorAll('.pay-button').forEach(button => {
                button.addEventListener('click', function() {
                    const type = this.dataset.type;
                    const id = this.dataset.id;
                    const amount = this.dataset.amount;
                    const name = this.dataset.name;

                    if (type === 'bill') {
                        window.location.href = 'index.php?page=client_payments&bill_id=' + encodeURIComponent(String(id));
                        return;
                    }

                    document.getElementById('paymentTypeInput').value = type;
                    document.getElementById('itemIdInput').value = id;
                    document.getElementById('amountInput').value = amount;
                    document.getElementById('paymentItemName').textContent = name;

                    // Show the payment modal
                    document.getElementById('paymentModal').style.display = 'block';
                    document.getElementById('paymentModal').scrollIntoView({ behavior: 'smooth' });
                });
            });

            // Auto-open payment modal when redirected with bill_id
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const billIdParam = urlParams.get('bill_id');
                const serviceIdParam = urlParams.get('service_id');
                const showParam = urlParams.get('show');
                const modeParam = urlParams.get('mode');
                if (billIdParam) {
                    const bills = <?php echo json_encode($data['unpaidBills'] ?? []); ?>;
                    const match = bills.find(function(b){ return String(b.id) === String(billIdParam); });
                    if (match) {
                        var amountDue = parseFloat(match.amount_due || 0);
                        var amountPaid = parseFloat(match.amount_paid || 0);
                        var balance = Math.max(amountDue - amountPaid, 0).toFixed(2);
                        document.getElementById('paymentTypeInput').value = 'bill';
                        document.getElementById('itemIdInput').value = String(match.id);
                        document.getElementById('amountInput').value = balance;
                        document.getElementById('paymentItemName').textContent = 'Bill #' + String(match.id);
                        if (modeParam !== 'view') {
                            document.getElementById('paymentModal').style.display = 'block';
                            document.getElementById('paymentModal').scrollIntoView({ behavior: 'smooth' });
                        }
                        var tracker = document.getElementById('billStatusTracker'); if (tracker) tracker.style.display = 'block';
                    } else {
                        const selectedBill = <?php echo json_encode($data['selectedBill'] ?? null); ?>;
                        if (selectedBill && String(selectedBill.id) === String(billIdParam)) {
                            var amountDue2 = parseFloat(selectedBill.amount_due || 0);
                            var amountPaid2 = parseFloat(selectedBill.amount_paid || 0);
                            var balance2 = Math.max(amountDue2 - amountPaid2, 0).toFixed(2);
                            document.getElementById('paymentTypeInput').value = 'bill';
                            document.getElementById('itemIdInput').value = String(selectedBill.id);
                            document.getElementById('amountInput').value = balance2;
                            document.getElementById('paymentItemName').textContent = 'Bill #' + String(selectedBill.id);
                            if (modeParam !== 'view') {
                                document.getElementById('paymentModal').style.display = 'block';
                                document.getElementById('paymentModal').scrollIntoView({ behavior: 'smooth' });
                            }
                            var tracker2 = document.getElementById('billStatusTracker'); if (tracker2) tracker2.style.display = 'block';
                        }
                    }
                }
                if (serviceIdParam) {
                    const services = <?php echo json_encode($data['outstandingServices'] ?? []); ?>;
                    const matchSvc = services.find(function(s){ return String(s.id) === String(serviceIdParam); });
                    if (matchSvc) {
                        var amt = parseFloat(matchSvc.cost || 0).toFixed(2);
                        document.getElementById('paymentTypeInput').value = 'service';
                        document.getElementById('itemIdInput').value = String(matchSvc.id);
                        document.getElementById('amountInput').value = amt;
                        document.getElementById('paymentItemName').textContent = String(matchSvc.service_name);
                        if (modeParam !== 'view') {
                            document.getElementById('paymentModal').style.display = 'block';
                            document.getElementById('paymentModal').scrollIntoView({ behavior: 'smooth' });
                        }
                    }
                    if (modeParam === 'view') {
                        var svcTracker = document.getElementById('serviceStatusTracker'); if (svcTracker) svcTracker.style.display = 'block';
                    }
                }
                if (showParam === 'sent') {
                    const panel = document.getElementById('sentBillsPanel');
                    if (panel) { panel.style.display = 'block'; }
                }
            } catch (e) {}

            // Handle form submission
            document.getElementById('paymentForm').addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('submitPaymentBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                
                // You could add AJAX submission here if preferred
                // Otherwise, the form will submit normally
            });
        });

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
            document.getElementById('phone_number').value = ''; // Clear phone number
        }

        function printPaymentHistory() {
            const printContent = document.getElementById('paymentHistoryTable').outerHTML;
            const originalContent = document.body.innerHTML;
            
            document.body.innerHTML = `
                <h2 style="text-align:center;margin-bottom:20px;">Payment History</h2>
                ${printContent}
                <p style="text-align:right;margin-top:30px;font-size:0.9em;">
                    Printed on: ${new Date().toLocaleDateString()}
                </p>
            `;
            
            window.print();
            document.body.innerHTML = originalContent;
        }

        function downloadReceipt(transactionId) {
            // This would typically call your backend to generate/download a receipt
            alert('Receipt download for transaction: ' + transactionId);
            // window.location.href = 'index.php?page=download_receipt&txn_id=' + transactionId;
        }
        function closeBillTracker(){
            var el = document.getElementById('billStatusTracker');
            if(el){ el.style.display = 'none'; }
            try {
                var url = new URL(window.location.href);
                url.searchParams.delete('bill_id');
                url.searchParams.delete('mode');
                history.replaceState(null, document.title, url.toString());
            } catch(e) {}
        }

        function closeServiceTracker(){
            var el = document.getElementById('serviceStatusTracker');
            if(el){ el.style.display = 'none'; }
            try {
                var url = new URL(window.location.href);
                url.searchParams.delete('service_id');
                url.searchParams.delete('mode');
                history.replaceState(null, document.title, url.toString());
            } catch(e) {}
        }
        function pollServiceTracker(serviceId){
            try {
                fetch('index.php?page=client_payments&action=tracker_status&type=service&id=' + encodeURIComponent(serviceId))
                    .then(function(r){ return r.json(); })
                    .then(function(json){
                        if(!json || !json.ok){ return; }
                        var svc = json.service || {}; var payments = json.payments || [];
                        var tracker = document.getElementById('serviceStatusTracker');
                        if(!tracker) return;
                        var status = String(svc.status || '').toLowerCase();
                        var initiated = payments.some(function(p){ return String(p.status||'').toLowerCase()==='pending'; });
                        var completed = payments.some(function(p){ var s=String(p.status||'').toLowerCase(); return s==='completed' || s==='confirmed_and_verified'; });
                        var paid = (status==='paid') || completed;
                        var tableBody = tracker.querySelector('tbody');
                        if (tableBody) {
                            var rows = [];
                            rows.push('<tr><td>Request Created</td><td>' + (svc.service_name||'Service') + ' applied</td><td>' + (svc.application_date||'-') + '</td></tr>');
                            if (svc.updated_at) { rows.push('<tr><td>Status</td><td>' + (status||'-') + '</td><td>' + svc.updated_at + '</td></tr>'); }
                            payments.forEach(function(p){ rows.push('<tr><td>Payment</td><td>' + (p.status||'pending') + ' via ' + (p.payment_method||'M-Pesa') + ' (' + (p.transaction_id||'N/A') + ')</td><td>' + (p.payment_date||'-') + '</td></tr>'); });
                            tableBody.innerHTML = rows.join('');
                        }
                        var row = document.querySelector('tr[data-service-id="' + String(svc.id) + '"]');
                        if (row) {
                            var statusCell = row.children[2];
                            var actionCell = row.children[4];
                            if (paid) {
                                statusCell.innerHTML = '<span class="status-badge status-paid">Paid</span>';
                                actionCell.innerHTML = '<span class="text-muted">N/A</span>';
                            } else if (initiated) {
                                var last = payments[0] ? String(payments[0].transaction_id||'') : '';
                                var last4 = last ? last.slice(-4) : '';
                                statusCell.innerHTML = '<span class="status-badge status-pending">Payment Initiated' + (last4 ? ' • Ref ' + last4 : '') + '</span>';
                                actionCell.innerHTML = '<a href="index.php?page=client_payments&service_id=' + String(svc.id) + '&mode=view#serviceStatusTracker" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> View Tracker' + (last4 ? ' • Ref ' + last4 : '') + '</a>';
                            }
                        }
                    });
            } catch(e) { }
        }
        var currentServiceId = document.getElementById('serviceStatusTracker')?.getAttribute('data-service-id');
        if (currentServiceId) {
            pollServiceTracker(currentServiceId);
            window.__svcPollTimer && clearInterval(window.__svcPollTimer);
            window.__svcPollTimer = setInterval(function(){ pollServiceTracker(currentServiceId); }, 5000);
        }
    </script>
</body>
</html>
