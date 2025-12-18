<?php
// app/views/client/pay_bill.php

// Ensure this page is only accessible to clients
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php?page=login');
    exit;
}

// Extract data from controller
$bill = $data['bill'] ?? null;
$client = $data['client'] ?? null;
$payment_methods = $data['payment_methods'] ?? [];
$success = $data['success'] ?? '';
$error = $data['error'] ?? '';

// If no bill is provided, redirect to bills page
if (!$bill) {
    header('Location: index.php?page=client_view_bills');
    exit;
}

// Calculate remaining balance
$remainingBalance = $bill['amount_due'] - $bill['amount_paid'];

// Check if bill is already paid
if (in_array(strtolower($bill['status'] ?? $bill['payment_status'] ?? ''), ['paid','confirmed_and_verified'], true) || $remainingBalance <= 0) {
    header('Location: index.php?page=client_view_bill_details&bill_id=' . $bill['id']);
    exit;
}
?>

<div class="container-fluid p-4">
    <h1 class="mb-4">Pay Bill</h1>
    
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?page=client_billing_dashboard">Billing Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=client_view_bills">My Bills</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=client_view_bill_details&bill_id=<?= $bill['id'] ?>">Bill Details</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pay Bill</li>
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
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Bill Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Bill Number:</th>
                            <td><?= $bill['id'] ?></td>
                        </tr>
                        <tr>
                            <th>Bill Date:</th>
                            <td><?= date('d M Y', strtotime($bill['bill_date'])) ?></td>
                        </tr>
                        <tr>
                            <th>Due Date:</th>
                            <td class="<?= strtotime($bill['due_date']) < time() ? 'text-danger' : '' ?>">
                                <?= date('d M Y', strtotime($bill['due_date'])) ?>
                                <?= strtotime($bill['due_date']) < time() ? ' (Overdue)' : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Meter:</th>
                            <td><?= htmlspecialchars($bill['meter_serial']) ?></td>
                        </tr>
                        <tr>
                            <th>Consumption:</th>
                            <td><?= number_format($bill['consumption'], 2) ?> units</td>
                        </tr>
                        <tr>
                            <th>Total Amount:</th>
                            <td>KES <?= number_format($bill['amount_due'], 2) ?></td>
                        </tr>
                        <tr>
                            <th>Amount Paid:</th>
                            <td>KES <?= number_format($bill['amount_paid'], 2) ?></td>
                        </tr>
                        <tr>
                            <th>Balance Due:</th>
                            <td class="text-danger fw-bold">KES <?= number_format($remainingBalance, 2) ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <?php if ($bill['status'] == 'partial'): ?>
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
        
        <div class="col-md-7">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Payment Options</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="paymentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="mpesa-tab" data-bs-toggle="tab" data-bs-target="#mpesa" 
                                    type="button" role="tab" aria-controls="mpesa" aria-selected="true">
                                M-Pesa
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" 
                                    type="button" role="tab" aria-controls="bank" aria-selected="false">
                                Bank Transfer
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="other-tab" data-bs-toggle="tab" data-bs-target="#other" 
                                    type="button" role="tab" aria-controls="other" aria-selected="false">
                                Other Methods
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content p-3 border border-top-0 rounded-bottom" id="paymentTabsContent">
                        <!-- M-Pesa Payment Tab -->
                        <div class="tab-pane fade show active" id="mpesa" role="tabpanel" aria-labelledby="mpesa-tab">
                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> Pay your bill easily using M-Pesa. Follow the steps below.
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="mb-3">Pay via STK Push</h6>
                                    <form method="post" action="" id="mpesaForm">
                                        <input type="hidden" name="bill_id" value="<?= $bill['id'] ?>">
                                        <input type="hidden" name="payment_method" value="mpesa">
                                        
                                        <div class="mb-3">
                                            <label for="phone_number" class="form-label">M-Pesa Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text">+254</span>
                                                <input type="text" class="form-control" id="phone_number" name="phone_number" 
                                                       placeholder="7XXXXXXXX" pattern="[7-9][0-9]{8}" 
                                                       value="<?= substr($client['phone'] ?? '', -9) ?>" required>
                                            </div>
                                            <div class="form-text">Enter your M-Pesa registered phone number</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="amount" class="form-label">Amount (KES)</label>
                                            <input type="number" class="form-control" id="amount" name="amount" 
                                                   step="0.01" min="1" max="<?= $remainingBalance ?>" 
                                                   value="<?= $remainingBalance ?>" required>
                                            <div class="form-text">Maximum payment: KES <?= number_format($remainingBalance, 2) ?></div>
                                        </div>
                                        
                                        <button type="submit" name="pay_mpesa" class="btn btn-success">
                                            <i class="fas fa-mobile-alt me-2"></i> Pay with M-Pesa
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="mb-3">Manual M-Pesa Payment</h6>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <p><strong>Pay Bill Number:</strong> <span class="fw-bold">123456</span></p>
                                            <p><strong>Account Number:</strong> <span class="fw-bold"><?= $bill['id'] ?></span></p>
                                            <p><strong>Amount:</strong> <span class="fw-bold">KES <?= number_format($remainingBalance, 2) ?></span></p>
                                            
                                            <hr>
                                            
                                            <p class="mb-0"><strong>Steps:</strong></p>
                                            <ol class="mt-2">
                                                <li>Go to M-Pesa on your phone</li>
                                                <li>Select Pay Bill</li>
                                                <li>Enter Business Number: <strong>123456</strong></li>
                                                <li>Enter Account Number: <strong><?= $bill['id'] ?></strong></li>
                                                <li>Enter Amount: <strong>KES <?= number_format($remainingBalance, 2) ?></strong></li>
                                                <li>Enter your M-Pesa PIN and confirm</li>
                                            </ol>
                                            
                                            <div class="alert alert-warning mt-3 mb-0">
                                                <i class="fas fa-exclamation-triangle me-2"></i> After making the payment, please submit the transaction details below.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <form method="post" action="" class="mt-3" id="manualMpesaForm">
                                        <input type="hidden" name="bill_id" value="<?= $bill['id'] ?>">
                                        <input type="hidden" name="payment_method" value="mpesa_manual">
                                        
                                        <div class="mb-3">
                                            <label for="transaction_id" class="form-label">M-Pesa Transaction ID</label>
                                            <input type="text" class="form-control" id="transaction_id" name="transaction_id" 
                                                   placeholder="e.g. QJI12345678" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="manual_amount" class="form-label">Amount Paid (KES)</label>
                                            <input type="number" class="form-control" id="manual_amount" name="amount" 
                                                   step="0.01" min="1" required>
                                        </div>
                                        
                                        <button type="submit" name="confirm_mpesa_payment" class="btn" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
                                            <i class="fas fa-check-circle me-2"></i> Confirm Payment
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bank Transfer Tab -->
                        <div class="tab-pane fade" id="bank" role="tabpanel" aria-labelledby="bank-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="mb-3">Bank Account Details</h6>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <p><strong>Bank Name:</strong> Example Bank</p>
                                            <p><strong>Account Name:</strong> Water Billing System</p>
                                            <p><strong>Account Number:</strong> 1234567890</p>
                                            <p><strong>Branch:</strong> Main Branch</p>
                                            <p><strong>Reference:</strong> Bill #<?= $bill['id'] ?></p>
                                            
                                            <div class="alert alert-warning mt-3 mb-0">
                                                <i class="fas fa-exclamation-triangle me-2"></i> Please use your Bill Number as the payment reference.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="mb-3">Confirm Bank Transfer</h6>
                                    <form method="post" action="">
                                        <input type="hidden" name="bill_id" value="<?= $bill['id'] ?>">
                                        <input type="hidden" name="payment_method" value="bank_transfer">
                                        
                                        <div class="mb-3">
                                            <label for="bank_name" class="form-label">Bank Name</label>
                                            <input type="text" class="form-control" id="bank_name" name="bank_name" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="bank_reference" class="form-label">Transaction Reference</label>
                                            <input type="text" class="form-control" id="bank_reference" name="transaction_id" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="bank_amount" class="form-label">Amount Paid (KES)</label>
                                            <input type="number" class="form-control" id="bank_amount" name="amount" 
                                                   step="0.01" min="1" max="<?= $remainingBalance ?>" 
                                                   value="<?= $remainingBalance ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="bank_date" class="form-label">Payment Date</label>
                                            <input type="date" class="form-control" id="bank_date" name="payment_date" 
                                                   value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="bank_notes" class="form-label">Notes</label>
                                            <textarea class="form-control" id="bank_notes" name="notes" rows="2"></textarea>
                                        </div>
                                        
                                        <button type="submit" name="confirm_bank_payment" class="btn" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
                                            <i class="fas fa-check-circle me-2"></i> Confirm Payment
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Other Payment Methods Tab -->
                        <div class="tab-pane fade" id="other" role="tabpanel" aria-labelledby="other-tab">
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i> You can also pay your bill using the following methods.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title">Pay at Our Office</h6>
                                            <p>Visit our office at:</p>
                                            <address>
                                                Water Billing System<br>
                                                123 Main Street<br>
                                                Nairobi, Kenya<br>
                                                <br>
                                                <strong>Opening Hours:</strong><br>
                                                Monday - Friday: 8:00 AM - 5:00 PM<br>
                                                Saturday: 9:00 AM - 1:00 PM
                                            </address>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">Payment Agents</h6>
                                            <p>You can pay your bill at any of our authorized payment agents:</p>
                                            <ul>
                                                <li>Agent 1 - Location 1</li>
                                                <li>Agent 2 - Location 2</li>
                                                <li>Agent 3 - Location 3</li>
                                            </ul>
                                            <p class="mb-0"><strong>Note:</strong> Please bring your bill number and ID.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="index.php?page=client_view_bill_details&bill_id=<?= $bill['id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Bill Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-success, .alert-danger');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Format phone number input
    const phoneInput = document.getElementById('phone_number');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 9) {
                value = value.substring(0, 9);
            }
            e.target.value = value;
        });
    }
});
</script>
