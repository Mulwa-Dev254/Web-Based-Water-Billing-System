<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Details</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --dark-bg: #1e1e2d;
            --darker-bg: #151521;
            --sidebar-bg: #1a1a27;
            --card-bg: #2a2a3c;
            --text-light: #f8f9fa;
            --text-muted: #a1a5b7;
            --border-color: #2d2d3a;
            --success: #10b981;
            --info: #3b82f6;
            --warning: #f59e0b;
            --danger: #ef4444;
            --purple: #8b5cf6;
        }

        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Dashboard Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 16rem; /* Width of sidebar */
            padding: 1.5rem;
        }

        /* Card Styles */
        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Transaction Details */
        .transaction-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-group {
            margin-bottom: 1rem;
        }

        .info-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
        }

        .info-group .value {
            font-size: 0.875rem;
            color: #111827;
            font-weight: 500;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-badge.completed {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-badge.pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .status-badge.failed {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .status-badge.flagged {
            background-color: rgba(139, 92, 246, 0.1);
            color: var(--purple);
        }

        /* Client Info */
        .client-info {
            display: flex;
            align-items: center;
            padding: 1rem;
            background-color: #f9fafb;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .client-avatar {
            width: 3rem;
            height: 3rem;
            border-radius: 9999px;
            background-color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.25rem;
            color: #6b7280;
        }

        .client-details h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.25rem;
        }

        .client-details p {
            font-size: 0.875rem;
            color: #6b7280;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: 1px solid var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid #d1d5db;
            color: #4b5563;
        }

        .btn-outline:hover {
            background-color: #f3f4f6;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
            border: 1px solid var(--danger);
        }

        .btn-danger:hover {
            background-color: #dc2626;
            border-color: #dc2626;
        }

        .btn-success {
            background-color: var(--success);
            color: white;
            border: 1px solid var(--success);
        }

        .btn-success:hover {
            background-color: #059669;
            border-color: #059669;
        }

        /* Notes Section */
        .notes-section {
            margin-top: 1.5rem;
        }

        .notes-section textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            min-height: 6rem;
            margin-bottom: 1rem;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border-left: 4px solid var(--success);
            color: var(--success);
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--danger);
            color: var(--danger);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .transaction-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include_once __DIR__ . '/partials/sidebar.php'; ?>

        <div class="main-content">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Transaction Details</h1>
                    <p class="text-gray-600">Viewing transaction #<?= htmlspecialchars($data['transaction']['payment_id']) ?></p>
                </div>
                <a href="index.php?page=finance_manager_transactions" class="btn btn-outline">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Transactions
                </a>
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

            <!-- Transaction Details Card -->
            <div class="card">
                <div class="card-header">
                    <h2>Transaction Information</h2>
                    <?php 
                    $statusVal = strtolower($data['transaction']['status']);
                    $statusClass = 'pending';
                    if ($statusVal === 'completed' || $statusVal === 'confirmed_and_verified') {
                        $statusClass = 'completed';
                    } elseif ($statusVal === 'failed') {
                        $statusClass = 'failed';
                    } elseif ($statusVal === 'flagged') {
                        $statusClass = 'flagged';
                    }
                    ?>
                    <span class="status-badge <?= $statusClass ?>">
                        <?php 
                            $label = ($statusVal === 'confirmed_and_verified') ? 'Confirmed & Verified' : ucfirst($data['transaction']['status']);
                            echo htmlspecialchars($label);
                            if ($statusVal === 'confirmed_and_verified') { echo ' <i class="fas fa-check-circle" style="color:#10b981"></i>'; }
                        ?>
                    </span>
                </div>
                <div class="card-body">
                    <!-- Client Information -->
                    <div class="client-info">
                        <div class="client-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="client-details">
                            <h3><?= htmlspecialchars($data['transaction']['client_name']) ?></h3>
                            <p><?= htmlspecialchars($data['transaction']['client_email']) ?></p>
                            <p class="text-sm text-gray-500">Client ID: <?= htmlspecialchars($data['transaction']['client_id']) ?></p>
                        </div>
                    </div>

                    <!-- Transaction Details -->
                    <div class="transaction-info">
                        <div>
                            <div class="info-group">
                                <label>Transaction ID</label>
                                <div class="value">#<?= htmlspecialchars($data['transaction']['payment_id']) ?></div>
                            </div>
                            <div class="info-group">
                                <label>Amount</label>
                                <div class="value text-lg font-semibold">KSH <?= number_format($data['transaction']['amount'], 2) ?></div>
                            </div>
                            <div class="info-group">
                                <label>Payment Method</label>
                                <div class="value"><?= htmlspecialchars($data['transaction']['payment_method'] ?? 'Not specified') ?></div>
                            </div>
                        </div>

                        <div>
                            <div class="info-group">
                                <label>Payment Date</label>
                                <div class="value"><?= htmlspecialchars(date('F d, Y', strtotime($data['transaction']['payment_date']))) ?></div>
                            </div>
                            <div class="info-group">
                                <label>Payment Time</label>
                                <div class="value"><?= htmlspecialchars(date('h:i A', strtotime($data['transaction']['payment_date']))) ?></div>
                            </div>
                            <div class="info-group">
                                <label>Reference Number</label>
                                <div class="value"><?= htmlspecialchars($data['transaction']['reference_number'] ?? 'N/A') ?></div>
                            </div>
                        </div>

                        <div>
                            <div class="info-group">
                                <label>Bill ID</label>
                                <div class="value"><?= htmlspecialchars($data['transaction']['bill_id'] ?? 'N/A') ?></div>
                            </div>
                            <div class="info-group">
                                <label>Payment Type</label>
                                <div class="value"><?= htmlspecialchars(
                                    ($data['transaction']['payment_type'] === 'bill_payment') ? 'Billing Payment' : 'Service Payment'
                                ) ?></div>
                            </div>
                            <div class="info-group">
                                <label>Verified By</label>
                                <div class="value"><?= htmlspecialchars($data['transaction']['verified_by'] ?? 'Not verified') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Description/Notes -->
                    <?php if (!empty($data['transaction']['notes'])): ?>
                    <div class="mt-4">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase mb-2">Notes</h3>
                        <div class="p-3 bg-gray-50 rounded-md text-sm">
                            <?= nl2br(htmlspecialchars($data['transaction']['notes'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <?php if ($data['transaction']['status'] === 'pending'): ?>
                        <button type="button" class="btn btn-success" id="openVerifyDialog" data-payment-id="<?= $data['transaction']['payment_id'] ?>">
                            <i class="fas fa-check-circle mr-2"></i> Verify Transaction
                        </button>
                        <?php endif; ?>

                        <?php if ($data['transaction']['status'] !== 'flagged'): ?>
                        <form action="index.php?page=finance_manager_flag_transaction" method="POST">
                            <input type="hidden" name="payment_id" value="<?= $data['transaction']['payment_id'] ?>">
                            <input type="hidden" name="redirect" value="details">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-flag mr-2"></i> Flag Transaction
                            </button>
                        </form>
                        <?php else: ?>
                        <form action="index.php?page=finance_manager_unflag_transaction" method="POST">
                            <input type="hidden" name="payment_id" value="<?= $data['transaction']['payment_id'] ?>">
                            <input type="hidden" name="redirect" value="details">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-flag mr-2"></i> Remove Flag
                            </button>
                        </form>
                        <?php endif; ?>

                        <a href="index.php?page=finance_manager_generate_receipt&id=<?= $data['transaction']['payment_id'] ?>" class="btn btn-outline" target="_blank">
                            <i class="fas fa-file-invoice mr-2"></i> Generate Receipt
                        </a>
                    </div>

                    <!-- Add Notes Form -->
                    <div class="notes-section">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase mb-2">Add Notes</h3>
                        <form action="index.php?page=finance_manager_add_transaction_note" method="POST">
                            <input type="hidden" name="payment_id" value="<?= $data['transaction']['payment_id'] ?>">
                            <textarea name="notes" placeholder="Add notes about this transaction..."><?= htmlspecialchars($data['transaction']['notes'] ?? '') ?></textarea>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i> Save Notes
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Related Transactions (if any) -->
            <?php if (!empty($data['related_transactions'])): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Related Transactions</h2>
                </div>
                <div class="card-body">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase text-gray-500 border-b">
                                    <th class="px-4 py-2">ID</th>
                                    <th class="px-4 py-2">Date</th>
                                    <th class="px-4 py-2">Amount</th>
                                    <th class="px-4 py-2">Status</th>
                                    <th class="px-4 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['related_transactions'] as $related): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3">#<?= htmlspecialchars($related['payment_id']) ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars(date('M d, Y', strtotime($related['payment_date']))) ?></td>
                                    <td class="px-4 py-3 font-medium">KSH <?= number_format($related['amount'], 2) ?></td>
                                    <td class="px-4 py-3">
                                        <?php 
                                        $relatedStatusClass = 'pending';
                                        if ($related['status'] === 'completed') {
                                            $relatedStatusClass = 'completed';
                                        } elseif ($related['status'] === 'failed') {
                                            $relatedStatusClass = 'failed';
                                        } elseif ($related['status'] === 'flagged') {
                                            $relatedStatusClass = 'flagged';
                                        }
                                        ?>
                                        <span class="status-badge <?= $relatedStatusClass ?>">
                                            <?= ucfirst(htmlspecialchars($related['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="index.php?page=finance_manager_transaction_details&id=<?= $related['payment_id'] ?>" class="text-blue-600 hover:underline">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="verifyOverlay" style="position:fixed;inset:0;background:rgba(0,0,0,0.55);backdrop-filter:blur(2px);display:none;align-items:center;justify-content:center;z-index:2000;">
        <div style="width:420px;max-width:90vw;background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.3);overflow:hidden;">
            <div style="padding:16px 18px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
                <div style="font-weight:600;">Confirm Verification</div>
                <button id="closeVerifyDialog" style="background:none;border:none;font-size:18px;cursor:pointer;color:#4b5563;">×</button>
            </div>
            <div style="padding:16px 18px;">
                <div id="verifyMessage" style="font-size:14px;color:#374151;">Proceed to confirm and verify this transaction?</div>
                <div id="verifyProgress" style="margin-top:12px;display:none;align-items:center;gap:10px;">
                    <div style="width:22px;height:22px;border:3px solid #93c5fd;border-top-color:#2563eb;border-radius:50%;animation:spin 0.9s linear infinite;"></div>
                    <div style="font-size:14px;color:#2563eb;">Verifying…</div>
                </div>
                <div id="verifySuccess" style="margin-top:12px;display:none;align-items:center;gap:10px;color:#10b981;font-weight:600;">
                    <i class="fas fa-check-circle"></i>
                    <span>Verified successfully</span>
                </div>
            </div>
            <div style="padding:12px 18px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:10px;">
                <button id="cancelVerifyBtn" class="btn btn-outline">Cancel</button>
                <button id="confirmVerifyBtn" class="btn btn-primary">Confirm</button>
            </div>
        </div>
    </div>
    <style>
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            var overlay = $('#verifyOverlay');
            var openBtn = $('#openVerifyDialog');
            var closeBtn = $('#closeVerifyDialog');
            var cancelBtn = $('#cancelVerifyBtn');
            var confirmBtn = $('#confirmVerifyBtn');
            var progress = $('#verifyProgress');
            var success = $('#verifySuccess');
            var message = $('#verifyMessage');
            function openDialog(){ overlay.css('display','flex'); success.hide(); progress.hide(); message.text('Proceed to confirm and verify this transaction?'); confirmBtn.prop('disabled', false).removeClass('btn-success').addClass('btn-primary').text('Confirm'); }
            function closeDialog(){ overlay.hide(); }
            openBtn.on('click', function(){ openDialog(); });
            closeBtn.on('click', function(){ closeDialog(); });
            cancelBtn.on('click', function(){ closeDialog(); });
            overlay.on('click', function(e){ if (e.target === overlay.get(0)) { closeDialog(); } });
            confirmBtn.on('click', function(){
                var pid = openBtn.data('payment-id');
                progress.css('display','inline-flex');
                confirmBtn.prop('disabled', true);
                $.ajax({
                    url: 'index.php?page=finance_manager_verify_transaction',
                    method: 'POST',
                    dataType: 'json',
                    data: { payment_id: pid, redirect: 'details', ajax: 1 }
                }).done(function(resp){
                    progress.hide();
                    success.css('display','inline-flex');
                    setTimeout(function(){
                        closeDialog();
                        var sb = $('.status-badge');
                        sb.removeClass('pending failed flagged').addClass('completed');
                        sb.html('Confirmed & Verified <i class="fas fa-check-circle" style="color:#10b981"></i>');
                        openBtn.prop('disabled', true).removeClass('btn-success').addClass('btn-outline').text('Verified');
                    }, 700);
                }).fail(function(){
                    progress.hide();
                    message.text('Verification failed. Try again.');
                    confirmBtn.prop('disabled', false);
                });
            });
        });
    </script>
</body>
</html>
