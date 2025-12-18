<?php
// app/views/finance_manager/record_payment.php

// Ensure this page is only accessible to finance managers and admins
if (!isset($_SESSION['user']) || ($_SESSION['role'] !== 'finance_manager' && $_SESSION['role'] !== 'admin')) {
    // Cannot use header redirect here as headers are already sent
    echo "<script>window.location.href = 'index.php?page=login';</script>";
    exit;
}

// Extract data from controller
$bill = $data['bill'] ?? null;
$success = $data['success'] ?? '';
$error = $data['error'] ?? '';

// If no bill is provided, redirect to bills page
if (!$bill) {
    // Cannot use header redirect here as headers are already sent
    echo "<script>window.location.href = 'index.php?page=billing_view_bills';</script>";
    exit;
}

// Calculate remaining balance
$remainingBalance = $bill['amount_due'] - $bill['amount_paid'];
?>

<div class="container-fluid p-4">
    <h1 class="mb-4">Record Payment</h1>
    
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?page=billing_dashboard">Billing Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=billing_view_bills">View Bills</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=billing_view_bill_details&bill_id=<?= $bill['id'] ?>">Bill Details</a></li>
            <li class="breadcrumb-item active" aria-current="page">Record Payment</li>
        </ol>
    </nav>
    
    <?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Bill Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Bill Number:</th>
                            <td><?= $bill['id'] ?></td>
                        </tr>
                        <tr>
                            <th>Client:</th>
                            <td><?= htmlspecialchars($bill['client_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Bill Date:</th>
                            <td><?= date('d M Y', strtotime($bill['bill_date'])) ?></td>
                        </tr>
                        <tr>
                            <th>Due Date:</th>
                            <td><?= date('d M Y', strtotime($bill['due_date'])) ?></td>
                        </tr>
                        <tr>
                            <th>Amount Due:</th>
                            <td>KES <?= number_format($bill['amount_due'], 2) ?></td>
                        </tr>
                        <tr>
                            <th>Amount Paid:</th>
                            <td>KES <?= number_format($bill['amount_paid'], 2) ?></td>
                        </tr>
                        <tr>
                            <th>Remaining Balance:</th>
                            <td class="<?= $remainingBalance > 0 ? 'text-danger' : 'text-success' ?>">
                                KES <?= number_format($remainingBalance, 2) ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <?php if ($bill['status'] == 'paid'): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php elseif ($bill['status'] == 'partial'): ?>
                                    <span class="badge bg-warning">Partially Paid</span>
                                <?php elseif ($bill['status'] == 'overdue'): ?>
                                    <span class="badge bg-danger">Overdue</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Record Payment</h5>
                </div>
                <div class="card-body">
                    <?php if ($bill['status'] == 'paid'): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> This bill has been fully paid.
                        </div>
                    <?php else: ?>
                        <form method="post" action="">
                            <input type="hidden" name="bill_id" value="<?= $bill['id'] ?>">
                            
                            <div class="mb-3">
                                <label for="amount" class="form-label">Payment Amount (KES)</label>
                                <input type="number" class="form-control" id="amount" name="amount" 
                                       step="0.01" min="0.01" max="<?= $remainingBalance ?>" 
                                       value="<?= $remainingBalance ?>" required>
                                <div class="form-text">Maximum payment: KES <?= number_format($remainingBalance, 2) ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select payment method</option>
                                    <option value="cash">Cash</option>
                                    <option value="mpesa">M-Pesa</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-3" id="transactionIdContainer" style="display: none;">
                                <label for="transaction_id" class="form-label">Transaction ID/Reference</label>
                                <input type="text" class="form-control" id="transaction_id" name="transaction_id">
                            </div>
                            
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">Payment Date</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" name="record_payment" class="btn btn-primary">
                                <i class="fas fa-money-bill-wave me-2"></i> Record Payment
                            </button>
                            
                            <a href="index.php?page=billing_view_bill_details&bill_id=<?= $bill['id'] ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Back to Bill Details
                            </a>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelect = document.getElementById('payment_method');
    const transactionIdContainer = document.getElementById('transactionIdContainer');
    
    // Show/hide transaction ID field based on payment method
    paymentMethodSelect.addEventListener('change', function() {
        const method = this.value;
        if (method === 'mpesa' || method === 'bank_transfer' || method === 'cheque') {
            transactionIdContainer.style.display = 'block';
            document.getElementById('transaction_id').required = true;
        } else {
            transactionIdContainer.style.display = 'none';
            document.getElementById('transaction_id').required = false;
        }
    });
    
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-success, .alert-danger');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>
