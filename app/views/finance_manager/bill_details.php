<?php
// app/views/finance_manager/bill_details.php

// Ensure this page is only accessible to finance managers and admins
if (!isset($_SESSION['user']) || ($_SESSION['role'] !== 'finance_manager' && $_SESSION['role'] !== 'admin')) {
    // Cannot use header redirect here as headers are already sent
    echo "<script>window.location.href = 'index.php?page=login';</script>";
    exit;
}

// Extract data from controller
$bill = $data['bill'] ?? null;
$payments = $data['payments'] ?? [];
$hasVerifiedPayment = false; foreach ($payments as $pp) { if (strtolower($pp['status'] ?? '') === 'confirmed_and_verified') { $hasVerifiedPayment = true; break; } }

// Check if bill exists
if (!$bill) {
    // Cannot use header redirect here as headers are already sent
    echo "<script>window.location.href = 'index.php?page=billing_view_bills';</script>";
    exit;
}

// Format currency function
function formatCurrency($amount) {
    return 'KES ' . number_format($amount, 2);
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

// Get success/error messages from URL if present
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Details - Water Billing System</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; color: var(--text-dark); margin: 0; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .main-content { margin-left: 16rem; padding: 2rem; width: calc(100% - 16rem); min-height: 100vh; box-sizing: border-box; }
        @media (max-width: 768px) { .main-content { margin-left: 0; width: 100%; padding: 1rem; } }
        .app-header { background-color: var(--header-bg); box-shadow: var(--header-shadow); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-radius: 0.75rem; }
        .app-title { font-size: 1.5rem; font-weight: 600; color: var(--text-dark); margin: 0; }
        .btn-group { display: flex; gap: 0.5rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; font-weight: 500; padding: 0.75rem 1.5rem; font-size: 0.875rem; border-radius: 0.5rem; transition: all 0.2s ease; cursor: pointer; border: 1px solid var(--border-color); background-color: #fff; color: var(--text-dark); }
        .btn i { margin-right: 0.5rem; }
        .btn-primary { color: #fff; background-color: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background-color: var(--primary-dark); border-color: var(--primary-dark); }
        .btn-outline { background-color: transparent; border: 1px solid var(--border-color); color: var(--text-dark); }
        .btn-outline:hover { background-color: rgba(79, 70, 229, 0.05); border-color: var(--primary); color: var(--primary); }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .page-title { font-size: 1.75rem; font-weight: 700; color: var(--text-dark); margin: 0; position: relative; }
        .page-title:after { content: ''; position: absolute; bottom: -8px; left: 0; width: 40px; height: 4px; background-color: var(--primary); border-radius: 2px; }
        .card { background-color: #fff; border: 1px solid var(--border-color); border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
        .card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
        .card-body { padding: 1.25rem; }

        /* Invoice (A4-like) */
        .invoice-wrapper { display: flex; justify-content: center; }
        .invoice { width: 210mm; max-width: 100%; background: #ffffff; color: #111827; border: 1px solid #e5e7eb; box-shadow: 0 6px 20px rgba(0,0,0,0.08); border-radius: 12px; overflow: hidden; }
        .invoice-header { display: flex; justify-content: space-between; align-items: center; padding: 24px 32px; background: linear-gradient(90deg, #1f2937 0%, #0f172a 100%); color: #f9fafb; }
        .invoice-title { display: flex; align-items: center; gap: 12px; }
        .invoice-title .logo { width: 40px; height: 40px; border-radius: 50%; background: #2563eb; display: flex; align-items: center; justify-content: center; }
        .invoice-title .logo i { color: #fff; font-size: 20px; }
        .invoice-title .name { font-size: 20px; font-weight: 700; }
        .invoice-meta { text-align: right; font-size: 13px; }
        .invoice-meta .badge { display: inline-block; padding: 6px 10px; border-radius: 999px; background: #2563eb; color: #fff; font-weight: 600; margin-bottom: 6px; }
        .invoice-section { padding: 24px 32px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .info-card { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; }
        .info-card h4 { margin: 0 0 8px 0; font-size: 14px; color: #334155; font-weight: 600; }
        .info-row { display: grid; grid-template-columns: 160px 1fr; gap: 8px; padding: 6px 0; font-size: 14px; color: #1f2937; }
        .info-row .label { color: #64748b; }
        .breakdown-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .breakdown-table thead th { background: #1f2937; color: #fff; font-weight: 600; padding: 10px; text-align: left; font-size: 13px; }
        .breakdown-table tbody td { border-bottom: 1px solid #e5e7eb; padding: 10px; font-size: 14px; }
        .breakdown-table tfoot td { padding: 10px; font-weight: 700; }
        .amount { text-align: right; }
        .highlight { color: #2563eb; font-weight: 700; }
        .totals { margin-top: 12px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .totals .total-card { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px; font-weight: 600; display: flex; justify-content: space-between; }
        .chart-section { padding: 0 32px 24px 32px; }
        .chart-card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; }
        .chart-title { font-size: 14px; font-weight: 600; color: #334155; margin-bottom: 8px; }
        @page { size: A4; margin: 12mm; }
        @media print {
            body { margin: 0; background: #fff; }
            body * { visibility: hidden; }
            .invoice, .invoice * { visibility: visible; }
            .invoice { position: absolute; left: 0; top: 0; width: 210mm; box-shadow: none; border: none; }
            /* Preserve exact colors and backgrounds when printing */
            .invoice, .invoice * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .invoice-header { background: linear-gradient(90deg, #1f2937 0%, #0f172a 100%) !important; color: #f9fafb !important; }
            .invoice-title .logo { background: #2563eb !important; }
            .invoice-meta .badge { background: #2563eb !important; color: #fff !important; }
            .breakdown-table thead th { background: #1f2937 !important; color: #fff !important; }
            .info-card { background: #f8fafc !important; border-color: #e5e7eb !important; }
            .totals .total-card { background: #f8fafc !important; border-color: #e5e7eb !important; }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

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
            <h1 class="page-title">Bill Details</h1>
            <div class="btn-group">
                <a href="index.php?page=billing_view_bills" class="btn btn-primary"><i class="fas fa-list"></i> View Bills</a>
                <a href="index.php?page=billing_reports" class="btn btn-outline"><i class="fas fa-chart-bar"></i> Reports</a>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div id="successOverlay" style="position:fixed;inset:0;background:rgba(0,0,0,0.55);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:2000;">
                <div style="width:420px;max-width:90vw;background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.3);overflow:hidden;">
                    <div style="padding:16px 18px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;">
                        <div style="font-weight:600;color:#0f766e;">Payment Recorded</div>
                        <button id="closeSuccessDialog" style="background:none;border:none;font-size:18px;cursor:pointer;color:#4b5563;">×</button>
                    </div>
                    <div style="padding:16px 18px;">
                        <div style="font-size:14px;color:#374151;"><strong>Success!</strong> <?= htmlspecialchars($success) ?></div>
                    </div>
                    <div style="padding:12px 18px;border-top:1px solid var(--border-color);display:flex;justify-content:flex-end;gap:10px;">
                        <a class="btn btn-outline" href="index.php?page=billing_view_bills">Close</a>
                        <a class="btn btn-primary" href="index.php?page=finance_manager_reports&report_type=clients&date_from=<?= date('Y-m-01') ?>&date_to=<?= date('Y-m-d') ?>">View Financial Reports</a>
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

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <nav aria-label="breadcrumb" style="margin-bottom: 1rem;">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?page=billing_dashboard">Billing Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php?page=billing_view_bills">View Bills</a></li>
                <li class="breadcrumb-item active" aria-current="page">Bill #<?= $bill['id'] ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-12">
                <div class="invoice-wrapper">
                    <div class="invoice">
                        <!-- Header -->
                        <div class="invoice-header">
                            <div class="invoice-title">
                                <div class="logo"><i class="fas fa-tint"></i></div>
                                <div class="name">Water Billing System</div>
                            </div>
                            <div class="invoice-meta">
                                <div class="badge">Bill #<?= $bill['id'] ?></div>
                                <div>Date: <?= date('d M Y', strtotime($bill['bill_date'])) ?></div>
                                <div>Due: <?= date('d M Y', strtotime($bill['due_date'])) ?></div>
                                <div>Status: <span id="billStatusLabel"><?= ucfirst(str_replace('_',' ',$bill['payment_status'])) ?></span><?php if ($hasVerifiedPayment): ?> &nbsp;<span style="display:inline-flex;align-items:center;gap:6px;background:#10b981;color:#fff;padding:4px 8px;border-radius:999px;">Verified <i class="fas fa-check-circle"></i></span><?php endif; ?></div>
                            </div>
                        </div>

                        <!-- Info Section -->
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
                                    <div class="info-row"><div class="label">Status</div><div class="highlight"><?= ucfirst(str_replace('_',' ',$bill['payment_status'])) ?><?php if ($hasVerifiedPayment): ?> &nbsp;<span style="display:inline-flex;align-items:center;gap:6px;color:#10b981;">Verified <i class="fas fa-check-circle"></i></span><?php endif; ?></div></div>
                                </div>
                            </div>

                            <!-- Breakdown Table -->
                            <table class="breakdown-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40%">Description</th>
                                        <th style="width: 20%">Units</th>
                                        <th style="width: 20%">Rate</th>
                                        <th style="width: 20%" class="amount">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Base Rate</td>
                                        <td>-</td>
                                        <td><?= formatCurrency($planBaseRate ?? 0) ?></td>
                                        <td class="amount"><?= formatCurrency($planBaseRate ?? 0) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Consumption Charge</td>
                                        <td><?= $bill['consumption_units'] ?> units</td>
                                        <td><?= formatCurrency($planUnitRate ?? 0) ?>/unit</td>
                                        <td class="amount"><?= formatCurrency(($planUnitRate ?? 0) * ($bill['consumption_units'] ?? 0)) ?></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" style="text-align:right">Subtotal (Amount Due)</td>
                                        <td class="amount"><?= formatCurrency($bill['amount_due']) ?></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <!-- Totals -->
                            <div class="totals">
                                <div class="total-card"><span>Amount Paid</span><span class="amount text-success" id="amountPaidValue"><?= formatCurrency($bill['amount_paid']) ?></span></div>
                                <div class="total-card"><span>Outstanding Balance</span><span class="amount <?= ($bill['balance']>0) ? 'text-danger' : 'text-success' ?>" id="balanceValueTop"><?= formatCurrency($bill['balance']) ?></span></div>
                            </div>
                        </div>

                        <!-- Chart -->
                        <div class="chart-section">
                            <div class="chart-card">
                                <div class="chart-title">Consumption Trend</div>
                                <canvas id="consumptionTrend" height="120"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                (function(){
                    const history = <?= json_encode($consumptionHistory ?? []) ?>;
                    if (!Array.isArray(history) || history.length === 0) return;
                    const labels = history.map(r => r.bill_date);
                    const data = history.map(r => parseFloat(r.consumption_units));
                    const ctx = document.getElementById('consumptionTrend').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Units',
                                data,
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37, 99, 235, 0.15)',
                                tension: 0.3,
                                fill: true,
                                pointRadius: 2,
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { grid: { display: false } },
                                y: { beginAtZero: true }
                            }
                        }
                    });
                })();
            </script>

                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Payment History</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($payments)): ?>
                            <p class="text-muted">No payments recorded for this bill.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Payment ID</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Transaction ID</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $payment): ?>
                                            <tr>
                                                <td><?= $payment['id'] ?></td>
                                                <td><?= date('d M Y H:i', strtotime($payment['payment_date'])) ?></td>
                                                <td class="text-success fw-bold"><?= formatCurrency($payment['amount']) ?></td>
                                                <td><?= ucfirst($payment['payment_method']) ?></td>
                                                <td><?= $payment['transaction_id'] ?? 'N/A' ?></td>
                                                <td><?= htmlspecialchars($payment['notes'] ?? '') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <?php if ($bill['payment_status'] !== 'paid'): ?>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white" style="display:flex;justify-content:space-between;align-items:center;gap:8px">
                        <h5 class="mb-0">Record Payment</h5>
                        <div style="display:flex;align-items:center;gap:8px">
                            <select id="prefillMode" class="form-select form-select-sm" style="width:auto;background:#eafaf1;color:#2e7d32;border-color:#2e7d32">
                                <option value="balance">Remaining Balance</option>
                                <option value="total_paid">Total Paid</option>
                            </select>
                            <button type="button" id="prefillPaymentBtn" class="btn btn-light btn-sm" style="color:#2e7d32;">
                                <i class="fas fa-magic me-1"></i> Prefill
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="post" action="index.php?page=record_payment&bill_id=<?= (int)$bill['id'] ?>" id="recordPaymentForm">
                            <input type="hidden" name="bill_id" value="<?= (int)$bill['id'] ?>">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount Paid by Client</label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" value="<?= (float)($bill['balance'] ?? (($bill['amount_due'] ?? 0) - ($bill['amount_paid'] ?? 0))) ?>" required>
                                </div>
                                <div class="form-text">Maximum payment (info): <?= formatCurrency((float)($bill['balance'] ?? (($bill['amount_due'] ?? 0) - ($bill['amount_paid'] ?? 0)))) ?></div>
                            </div>
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select payment method</option>
                                    <option value="cash">Cash</option>
                                    <option value="mpesa">M-Pesa STK-Push</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="transaction_id" class="form-label">Transaction ID</label>
                                <input type="text" class="form-control" id="transaction_id" name="transaction_id" placeholder="Optional">
                                <div class="form-text">Required for M-Pesa, bank transfers, etc.</div>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Optional"></textarea>
                            </div>
                            <button type="submit" name="record_payment" class="btn btn-success w-100">
                                <i class="fas fa-money-bill-wave me-2"></i> Record Payment
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="#" class="btn btn-outline-primary" onclick="printBill(); return false;">
                                <i class="fas fa-print me-2"></i> Print Bill
                            </a>
                            <a href="#" class="btn btn-outline-primary" onclick="downloadPdf(); return false;">
                                <i class="fas fa-file-pdf me-2"></i> Download PDF
                            </a>
                            <a href="#" class="btn btn-outline-primary" onclick="savePdfToStorage(); return false;">
                                <i class="fas fa-cloud-download-alt me-2"></i> Save to Storage
                            </a>
                            <a href="#" class="btn btn-outline-info" onclick="emailBill(); return false;">
                                <i class="fas fa-envelope me-2"></i> Email to Client
                            </a>
                            <a href="#" class="btn btn-outline-warning" onclick="sendReminder(); return false;">
                                <i class="fas fa-bell me-2"></i> Send Payment Reminder
                            </a>
                            <div class="actions-sep"></div>
                            <a href="#" class="btn send-client-btn w-100" onclick="confirmSendToClient(); return false;">
                                <i class="fas fa-paper-plane shimmer" aria-hidden="true"></i>
                                <span class="shimmer" style="margin-left:8px;">Send To Client</span>
                            </a>
                </div>
                </div>
                </div>

                <?php if (strtolower($bill['payment_status']) !== 'confirmed_and_verified'): ?>
                <div class="card mb-4" id="verifyCard">
                    <div class="card-body">
                        <button type="button" class="btn verify-bill-btn w-100" id="openVerifyBillDialog" data-bill-id="<?= (int)$bill['id'] ?>">
                            <i class="fas fa-check-circle shimmer" aria-hidden="true"></i>
                            <span class="shimmer" style="margin-left:8px;">Verify Bill</span>
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Bill Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Base Rate:</span>
                            <span><?= formatCurrency($bill['base_rate'] ?? 0) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Consumption (<?= $bill['consumption_units'] ?> units):</span>
                            <span><?= formatCurrency(($bill['unit_rate'] ?? 0) * $bill['consumption_units']) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3 fw-bold">
                            <span>Total Amount:</span>
                            <span><?= formatCurrency($bill['amount_due']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-success">
                            <span>Amount Paid:</span>
                            <span><?= formatCurrency($bill['amount_paid']) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Balance:</span>
                            <span class="<?= $bill['balance'] > 0 ? 'text-danger' : 'text-success' ?>" id="balanceValueSummary">
                                <?= formatCurrency($bill['balance']) ?>
                            </span>
                </div>
                </div>
                </div>

                <script>
                (function(){
                    const btn = document.getElementById('prefillPaymentBtn');
                    if (!btn) return;
                    btn.addEventListener('click', function(){
                        const amountEl = document.getElementById('amount');
                        const methodEl = document.getElementById('payment_method');
                        const txEl = document.getElementById('transaction_id');
                        const notesEl = document.getElementById('notes');
                        const amtPaid = <?= json_encode((float)($bill['amount_paid'] ?? 0)) ?>;
                        const remaining = <?= json_encode((float)(($bill['amount_due'] ?? 0) - ($bill['amount_paid'] ?? 0))) ?>;
                        const modeEl = document.getElementById('prefillMode');
                        const mode = modeEl ? modeEl.value : 'balance';
                        const fillVal = mode === 'total_paid' ? amtPaid : remaining;
                        amountEl.value = (isNaN(fillVal) ? 0 : fillVal).toFixed(2);
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
                })();
                </script>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
function printBill() { window.print(); }
function emailBill() { alert('Email functionality will be implemented in a future update.'); }
function sendReminder() { alert('Reminder functionality will be implemented in a future update.'); }
async function downloadPdf(){
    const billId = <?= (int)$bill['id'] ?>;
    const el = document.querySelector('.invoice');
    const canvas = await html2canvas(el, { scale: 2 });
    const imgData = canvas.toDataURL('image/png');
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('p','mm','a4');
    const pageW = pdf.internal.pageSize.getWidth();
    const pageH = pdf.internal.pageSize.getHeight();
    const margin = 10;
    let imgW = pageW - margin*2;
    let imgH = canvas.height * imgW / canvas.width;
    let x = margin; let y = margin;
    if (imgH > pageH - margin*2) {
        const scale = (pageH - margin*2) / imgH;
        imgW = imgW * scale;
        imgH = imgH * scale;
        x = (pageW - imgW)/2;
        y = margin;
    }
    pdf.addImage(imgData, 'PNG', x, y, imgW, imgH);
    pdf.save('bill-' + billId + '.pdf');
}
async function savePdfToStorage(){
    const billId = <?= (int)$bill['id'] ?>;
    const el = document.querySelector('.invoice');
    const canvas = await html2canvas(el, { scale: 2 });
    const imgData = canvas.toDataURL('image/png');
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('p','mm','a4');
    const pageW = pdf.internal.pageSize.getWidth();
    const pageH = pdf.internal.pageSize.getHeight();
    const margin = 10;
    let imgW = pageW - margin*2;
    let imgH = canvas.height * imgW / canvas.width;
    let x = margin; let y = margin;
    if (imgH > pageH - margin*2) {
        const scale = (pageH - margin*2) / imgH;
        imgW = imgW * scale;
        imgH = imgH * scale;
        x = (pageW - imgW)/2;
        y = margin;
    }
    pdf.addImage(imgData, 'PNG', x, y, imgW, imgH);
    const dataUri = pdf.output('datauristring');
    const base64 = dataUri.split(',')[1];
    const res = await fetch('index.php?page=billing_store_bill_pdf', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ bill_id: billId, pdf_base64: base64 })
    });
    try {
        const json = await res.json();
        alert(json.message || 'Saved');
    } catch(e){
        alert('Save failed');
    }
}
async function savePdfToStorageAndReturnPath(){
    const billId = <?= (int)$bill['id'] ?>;
    const el = document.querySelector('.invoice');
    const canvas = await html2canvas(el, { scale: 2 });
    const imgData = canvas.toDataURL('image/png');
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('p','mm','a4');
    const pageW = pdf.internal.pageSize.getWidth();
    const pageH = pdf.internal.pageSize.getHeight();
    const margin = 10;
    let imgW = pageW - margin*2;
    let imgH = canvas.height * imgW / canvas.width;
    let x = margin; let y = margin;
    if (imgH > pageH - margin*2) {
        const scale = (pageH - margin*2) / imgH;
        imgW = imgW * scale;
        imgH = imgH * scale;
        x = (pageW - imgW)/2;
        y = margin;
    }
    pdf.addImage(imgData, 'PNG', x, y, imgW, imgH);
    const dataUri = pdf.output('datauristring');
    const base64 = dataUri.split(',')[1];
    const res = await fetch('index.php?page=billing_store_bill_pdf', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ bill_id: billId, pdf_base64: base64 })
    });
    const json = await res.json();
    if (!json || !json.path) throw new Error('Save failed');
    return json.path;
}
async function saveImageToStorageAndReturnPath(){
    const billId = <?= (int)$bill['id'] ?>;
    const el = document.querySelector('.invoice');
    const canvas = await html2canvas(el, { scale: 2 });
    const png = canvas.toDataURL('image/png');
    const base64 = png.split(',')[1];
    const res = await fetch('index.php?page=billing_store_bill_image', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ bill_id: billId, image_base64: base64 })
    });
    const json = await res.json();
    if (!json || !json.path) throw new Error('Save failed');
    return json.path;
}
function confirmSendToClient(){
    const dlg = document.createElement('div');
    dlg.style.position = 'fixed';
    dlg.style.inset = '0';
    dlg.style.background = 'rgba(0,0,0,0.4)';
    dlg.style.display = 'flex';
    dlg.style.alignItems = 'center';
    dlg.style.justifyContent = 'center';
    const box = document.createElement('div');
    box.style.background = '#fff';
    box.style.width = '380px';
    box.style.borderRadius = '12px';
    box.style.boxShadow = '0 10px 30px rgba(0,0,0,0.2)';
    box.style.padding = '20px';
    box.innerHTML = '<h5 style="margin:0 0 8px 0">Send Bill</h5><p>Send this bill to the client?</p>';
    const actions = document.createElement('div');
    actions.style.display = 'flex';
    actions.style.justifyContent = 'flex-end';
    actions.style.gap = '8px';
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn btn-outline';
    cancelBtn.textContent = 'Cancel';
    const okBtn = document.createElement('button');
    okBtn.className = 'btn btn-primary';
    okBtn.textContent = 'Send';
    actions.appendChild(cancelBtn);
    actions.appendChild(okBtn);
    box.appendChild(actions);
    dlg.appendChild(box);
    document.body.appendChild(dlg);
    cancelBtn.onclick = () => document.body.removeChild(dlg);
    okBtn.onclick = async () => {
        okBtn.disabled = true;
        try {
            const pdfPath = await savePdfToStorageAndReturnPath();
            const imagePath = await saveImageToStorageAndReturnPath();
            const res = await fetch('index.php?page=billing_send_bill_to_client', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ bill_id: <?= (int)$bill['id'] ?>, pdf_path: pdfPath, image_path: imagePath })
            });
            const json = await res.json();
            alert(json.message || 'Sent');
            document.body.removeChild(dlg);
        } catch(e) {
            alert('Failed');
            okBtn.disabled = false;
        }
    };
}
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            if (window.bootstrap && bootstrap.Alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
    <div id="verifyBillOverlay" style="position:fixed;inset:0;background:rgba(0,0,0,0.55);backdrop-filter:blur(2px);display:none;align-items:center;justify-content:center;z-index:2000;">
        <div style="width:420px;max-width:90vw;background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.3);overflow:hidden;">
            <div style="padding:16px 18px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
                <div style="font-weight:600;">Confirm Bill Verification</div>
                <button id="closeVerifyBillDialog" style="background:none;border:none;font-size:18px;cursor:pointer;color:#4b5563;">×</button>
            </div>
            <div style="padding:16px 18px;">
                <div id="verifyBillMessage" style="font-size:14px;color:#374151;">Has the client paid the full amount?</div>
                <div style="margin-top:10px;display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
                    <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" id="fullPaidYes" />
                        <span>Yes</span>
                    </label>
                    <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" id="fullPaidNo" />
                        <span>No</span>
                    </label>
                </div>
                <div id="partialAmountRow" style="margin-top:10px;display:none;">
                    <label for="partialPaidAmount" style="display:block;font-size:13px;color:#374151;margin-bottom:6px;">Enter amount paid</label>
                    <input type="number" id="partialPaidAmount" min="0.01" step="0.01" class="form-control" placeholder="Enter amount" />
                    <div id="partialAmountError" style="margin-top:6px;color:#dc2626;font-size:13px;display:none;">Please enter a valid amount not exceeding total due.</div>
                </div>
                <div id="verifyBillProgress" style="margin-top:12px;display:none;align-items:center;gap:10px;">
                    <div style="width:22px;height:22px;border:3px solid #93c5fd;border-top-color:#2563eb;border-radius:50%;animation:spin 0.9s linear infinite;"></div>
                    <div style="font-size:14px;color:#2563eb;">Processing…</div>
                </div>
                <div id="verifyBillSuccess" style="margin-top:12px;display:none;align-items:center;gap:10px;color:#10b981;font-weight:600;">
                    <i class="fas fa-check-circle"></i>
                    <span>Bill verified successfully</span>
                </div>
            </div>
            <div style="padding:12px 18px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:10px;">
                <button id="cancelVerifyBillBtn" class="btn btn-outline">Cancel</button>
                <button id="confirmVerifyBillBtn" class="btn btn-primary">Confirm</button>
            </div>
        </div>
    </div>
    <style>
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .verify-bill-btn { width: 100%; padding: 14px 18px; font-size: 1.15rem; font-weight: 600; border: none; border-radius: 10px; background: linear-gradient(135deg, #10b981, #22c55e); color: #fff; box-shadow: 0 0 0 0 rgba(16,185,129,0.45), 0 0 0 0 rgba(34,197,94,0.35); position: relative; overflow: hidden; animation: bounceY 2.2s ease-in-out infinite, breatheGlow 2.6s ease-in-out infinite; }
        .verify-bill-btn .fas { font-size: 1.35rem; }
        .shimmer { background: linear-gradient(90deg, #ffffff, #eafff5, #ffffff); background-size: 200% auto; -webkit-background-clip: text; background-clip: text; color: transparent; -webkit-text-fill-color: transparent; animation: shimmerMove 2.5s linear infinite; }
        .verify-bill-btn::after { content: ''; position: absolute; top: 0; left: -50%; width: 50%; height: 100%; background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,0.35), rgba(255,255,255,0)); transform: skewX(-20deg); animation: shineSweep 3s ease-in-out infinite; }
        @keyframes bounceY { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }
        @keyframes breatheGlow { 0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.45), 0 0 0 0 rgba(34,197,94,0.35); } 50% { box-shadow: 0 0 18px 6px rgba(16,185,129,0.45), 0 0 30px 10px rgba(34,197,94,0.30); } }
        @keyframes shimmerMove { 0% { background-position: 0% 50%; } 100% { background-position: 200% 50%; } }
        @keyframes shineSweep { 0% { left: -50%; } 50% { left: 120%; } 100% { left: 120%; } }
        .actions-sep { margin: 8px 0 12px; padding-top: 8px; border-top: 1px dashed #e5e7eb; }
        .send-client-btn { width: 100%; padding: 12px 16px; font-size: 1.05rem; font-weight: 600; border: none; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; position: relative; overflow: hidden; animation: bounceY 2.2s ease-in-out infinite, breatheGlowIndigo 2.6s ease-in-out infinite; }
        .send-client-btn .fas { font-size: 1.25rem; }
        .send-client-btn::after { content: ''; position: absolute; top: 0; left: -50%; width: 50%; height: 100%; background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,0.30), rgba(255,255,255,0)); transform: skewX(-20deg); animation: shineSweep 3s ease-in-out infinite; }
        @keyframes breatheGlowIndigo { 0%, 100% { box-shadow: 0 0 0 0 rgba(99,102,241,0.40), 0 0 0 0 rgba(139,92,246,0.30); } 50% { box-shadow: 0 0 18px 6px rgba(99,102,241,0.40), 0 0 30px 10px rgba(139,92,246,0.25); } }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function(){
            var overlay = $('#verifyBillOverlay');
            var openBtn = $('#openVerifyBillDialog');
            var closeBtn = $('#closeVerifyBillDialog');
            var cancelBtn = $('#cancelVerifyBillBtn');
            var confirmBtn = $('#confirmVerifyBillBtn');
            var progress = $('#verifyBillProgress');
            var success = $('#verifyBillSuccess');
            var message = $('#verifyBillMessage');
            function openDialog(){ overlay.css('display','flex'); success.hide(); progress.hide(); message.text('Has the client paid the full amount?'); $('#fullPaidYes').prop('checked', false); $('#fullPaidNo').prop('checked', false); $('#partialPaidAmount').val(''); $('#partialAmountRow').hide(); $('#partialAmountError').hide(); confirmBtn.prop('disabled', false).removeClass('btn-success').addClass('btn-primary').text('Confirm'); }
            function closeDialog(){ overlay.hide(); }
            openBtn.on('click', function(){ openDialog(); });
            closeBtn.on('click', function(){ closeDialog(); });
            cancelBtn.on('click', function(){ closeDialog(); });
            overlay.on('click', function(e){ if (e.target === overlay.get(0)) { closeDialog(); } });
            $('#fullPaidYes').on('change', function(){ if (this.checked) { $('#fullPaidNo').prop('checked', false); $('#partialAmountRow').hide(); } });
            $('#fullPaidNo').on('change', function(){ if (this.checked) { $('#fullPaidYes').prop('checked', false); $('#partialAmountRow').show(); } else { $('#partialAmountRow').hide(); } });
            confirmBtn.on('click', function(){
                var bid = openBtn.data('bill-id');
                var yes = $('#fullPaidYes').is(':checked');
                var no = $('#fullPaidNo').is(':checked');
                var paidAmount = null;
                if (!yes && !no) { $('#partialAmountError').text('Please select Yes or No.').show(); return; }
                if (no) {
                    var val = parseFloat($('#partialPaidAmount').val());
                    var totalDue = parseFloat(<?= json_encode((float)$bill['amount_due']) ?>);
                    if (isNaN(val) || val <= 0 || val > totalDue) { $('#partialAmountError').text('Please enter a valid amount not exceeding total due.').show(); return; }
                    $('#partialAmountError').hide();
                    paidAmount = val;
                }
                progress.css('display','inline-flex');
                confirmBtn.prop('disabled', true);
                $.ajax({
                    url: 'index.php?page=finance_manager_verify_bill',
                    method: 'POST',
                    dataType: 'json',
                    data: { bill_id: bid, ajax: 1, full_paid: yes ? 'yes' : 'no', paid_amount: paidAmount }
                }).done(function(resp){
                    progress.hide();
                    success.css('display','inline-flex');
                    setTimeout(function(){
                        window.location.reload();
                    }, 1500);
                }).fail(function(){
                    progress.hide();
                    message.text('Verification failed. Try again.');
                    confirmBtn.prop('disabled', false);
                });
            });
        });
    </script>
