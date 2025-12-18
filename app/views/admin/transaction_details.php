<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • Transaction Details</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#ff4757; --primary-dark:#e84118; --dark-bg:#1e1e2d; --sidebar-bg:#1a1a27; --card-bg:#2a2a3c; --text-light:#f8f9fa; --text-muted:#a1a5b7; --border-color:#2d2d3a; --success:#1dd1a1; --warning:#ff9f43; --danger:#ee5253; --purple:#5f27cd; }
        *{ box-sizing:border-box; }
        body{ font-family:'Inter',sans-serif; background-color:var(--dark-bg); color:var(--text-light); margin:0; }
        .dashboard-layout{ display:flex; width:100%; min-height:100vh; }
        .sidebar{ width:280px; background-color:var(--sidebar-bg); padding:1.5rem 0; position:fixed; top:0; left:0; height:100vh; border-right:1px solid var(--border-color); }
        .sidebar-header{ padding:0 1.5rem 1.5rem; border-bottom:1px solid var(--border-color); margin-bottom:1.5rem; }
        .sidebar-header h3{ color:var(--primary); font-size:1.5rem; font-weight:700; display:flex; align-items:center; gap:.75rem; }
        .sidebar-nav{ height:calc(100vh - 120px); overflow-y:auto; padding:0 1rem; }
        .sidebar-nav ul{ list-style:none; margin:0; padding:0; }
        .sidebar-nav a{ display:flex; align-items:center; gap:.75rem; padding:.875rem 1rem; border-radius:.5rem; font-weight:500; color:var(--text-muted); }
        .sidebar-nav a:hover{ background-color:rgba(255,71,87,.1); color:var(--text-light); }
        .main-content{ margin-left:280px; flex-grow:1; min-height:100vh; }
        .header-bar{ background-color:var(--sidebar-bg); padding:1.25rem 2rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-color); position:sticky; top:0; z-index:100; }
        .btn{ display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .9rem; border-radius:.5rem; border:1px solid var(--border-color); background:transparent; color:var(--text-light); cursor:pointer; }
        .btn-primary{ background-color:var(--primary); border-color:var(--primary); }
        .btn-outline{ background:transparent; }
        .btn-danger{ background-color:var(--danger); border-color:var(--danger); color:#fff; }
        .btn-success{ background-color:var(--success); border-color:var(--success); color:#000; }
        .card{ background-color:var(--card-bg); border:1px solid var(--border-color); border-radius:.75rem; box-shadow:0 0 20px rgba(0,0,0,.1); margin:1.5rem 2rem; }
        .card-header{ padding:1rem 1.25rem; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; }
        .card-body{ padding:1.25rem; }
        .status-badge{ display:inline-flex; align-items:center; padding:.25rem .75rem; border-radius:9999px; font-size:.75rem; font-weight:600; }
        .status-badge.completed{ background-color:rgba(29,209,161,.12); color:var(--success); }
        .status-badge.pending{ background-color:rgba(255,159,67,.12); color:var(--warning); }
        .status-badge.failed{ background-color:rgba(238,82,83,.12); color:var(--danger); }
        .status-badge.flagged{ background-color:rgba(95,39,205,.12); color:var(--purple); }
        .client-info{ display:flex; align-items:center; padding:1rem; background:#1f1f2e; border-radius:.5rem; margin-bottom:1.5rem; }
        .client-avatar{ width:3rem; height:3rem; border-radius:9999px; background:#232336; display:flex; align-items:center; justify-content:center; color:var(--text-muted); margin-right:1rem; }
        .transaction-info{ display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem; }
        .info-group label{ display:block; font-size:.75rem; font-weight:500; color:var(--text-muted); margin-bottom:.25rem; text-transform:uppercase; }
        .info-group .value{ font-size:.9rem; font-weight:600; }
        .notes-section textarea{ width:100%; padding:.75rem; border:1px solid var(--border-color); border-radius:.375rem; background:#1f1f2e; color:var(--text-light); min-height:6rem; margin-bottom:1rem; }
        .alert{ padding:.75rem 1rem; border-radius:.5rem; border:1px solid var(--border-color); background:#232336; color:var(--text-light); margin:1rem 2rem 0 2rem; }
        .alert-success{ border-left:4px solid var(--success); color:var(--success); }
        .alert-error{ border-left:4px solid var(--danger); color:var(--danger); }
        .table{ width:100%; border-collapse:collapse; }
        .table th, .table td{ padding:.6rem .8rem; border-bottom:1px solid var(--border-color); text-align:left; }
        @media (max-width:992px){ .main-content{ margin-left:0; } .sidebar{ position:relative; width:100%; height:auto; } .dashboard-layout{ flex-direction:column; } .card{ margin:1rem; } .alert{ margin:1rem; } }
        @keyframes spin{ from{ transform:rotate(0deg);} to{ transform:rotate(360deg);} }
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
                    <li><a href="index.php?page=generate_bills"><i class="fas fa-file-invoice-dollar"></i> Generate Bills</a></li>
                    <li><a href="index.php?page=view_bills"><i class="fas fa-list"></i> View Bills</a></li>
                    <li><a href="index.php?page=admin_transactions"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
                    <li><a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <div class="main-content" id="mainContent">
            <div class="header-bar">
                <div class="header-title">
                    <h1>Transaction Details</h1>
                </div>
                <a href="index.php?page=admin_transactions" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
            </div>

            <?php if (!empty($data['error'])): ?>
                <div class="alert alert-error"><p><?= htmlspecialchars($data['error']) ?></p></div>
            <?php endif; ?>
            <?php if (!empty($data['success'])): ?>
                <div class="alert alert-success"><p><?= htmlspecialchars($data['success']) ?></p></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>Transaction Information</h2>
                    <?php $statusVal = strtolower($data['transaction']['status']); $statusClass = ($statusVal==='failed'?'failed':($statusVal==='flagged'?'flagged':(($statusVal==='completed' || $statusVal==='confirmed_and_verified')?'completed':'pending'))); ?>
                    <span class="status-badge <?= $statusClass ?>">
                        <?php $label = ($statusVal==='confirmed_and_verified')?'Confirmed & Verified':ucfirst($data['transaction']['status']); echo htmlspecialchars($label); if ($statusVal==='confirmed_and_verified'){ echo ' <i class=\"fas fa-check-circle\" style=\"color:var(--success)\"></i>'; } ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="client-info">
                        <div class="client-avatar"><i class="fas fa-user"></i></div>
                        <div class="client-details">
                            <h3><?= htmlspecialchars($data['transaction']['client_name']) ?></h3>
                            <p><?= htmlspecialchars($data['transaction']['client_email']) ?></p>
                            <p class="text-sm">Client ID: <?= htmlspecialchars($data['transaction']['client_id']) ?></p>
                        </div>
                    </div>

                    <div class="transaction-info">
                        <div>
                            <div class="info-group"><label>Transaction ID</label><div class="value">#<?= htmlspecialchars($data['transaction']['payment_id']) ?></div></div>
                            <div class="info-group"><label>Amount</label><div class="value">KSH <?= number_format($data['transaction']['amount'], 2) ?></div></div>
                            <div class="info-group"><label>Payment Method</label><div class="value"><?= htmlspecialchars($data['transaction']['payment_method'] ?? 'Not specified') ?></div></div>
                        </div>
                        <div>
                            <div class="info-group"><label>Payment Date</label><div class="value"><?= htmlspecialchars(date('F d, Y', strtotime($data['transaction']['payment_date']))) ?></div></div>
                            <div class="info-group"><label>Payment Time</label><div class="value"><?= htmlspecialchars(date('h:i A', strtotime($data['transaction']['payment_date']))) ?></div></div>
                            <div class="info-group"><label>Reference Number</label><div class="value"><?= htmlspecialchars($data['transaction']['reference_number'] ?? 'N/A') ?></div></div>
                        </div>
                        <div>
                            <div class="info-group"><label>Bill ID</label><div class="value"><?= htmlspecialchars($data['transaction']['bill_id'] ?? 'N/A') ?></div></div>
                            <div class="info-group"><label>Payment Type</label><div class="value"><?= htmlspecialchars(($data['transaction']['payment_type'] === 'bill_payment') ? 'Billing Payment' : 'Service Payment') ?></div></div>
                            <div class="info-group"><label>Verified By</label><div class="value"><?= htmlspecialchars($data['transaction']['verified_by'] ?? 'Not verified') ?></div></div>
                        </div>
                    </div>

                    <?php if (!empty($data['transaction']['notes'])): ?>
                        <div class="mt-4">
                            <h3 class="text-sm" style="text-transform:uppercase;color:var(--text-muted);font-weight:600;">Notes</h3>
                            <div class="p-3" style="background:#1f1f2e;border-radius:.5rem;"><?= nl2br(htmlspecialchars($data['transaction']['notes'])) ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="action-buttons" style="display:flex;gap:.75rem;margin-top:1rem;">
                        <?php if ($data['transaction']['status'] === 'pending'): ?>
                            <button type="button" class="btn btn-primary" id="openVerifyDialog" data-payment-id="<?= $data['transaction']['payment_id'] ?>"><i class="fas fa-check-circle"></i> Verify</button>
                        <?php endif; ?>
                        <?php if ($data['transaction']['status'] !== 'flagged'): ?>
                            <form action="index.php?page=finance_manager_flag_transaction" method="POST">
                                <input type="hidden" name="payment_id" value="<?= $data['transaction']['payment_id'] ?>">
                                <input type="hidden" name="redirect" value="details">
                                <button type="submit" class="btn btn-outline" style="color:var(--purple)"><i class="fas fa-flag"></i> Flag</button>
                            </form>
                        <?php else: ?>
                            <form action="index.php?page=finance_manager_unflag_transaction" method="POST">
                                <input type="hidden" name="payment_id" value="<?= $data['transaction']['payment_id'] ?>">
                                <input type="hidden" name="redirect" value="details">
                                <button type="submit" class="btn btn-success"><i class="fas fa-flag"></i> Remove Flag</button>
                            </form>
                        <?php endif; ?>
                        <a href="index.php?page=finance_manager_generate_receipt&id=<?= $data['transaction']['payment_id'] ?>" class="btn btn-outline" target="_blank"><i class="fas fa-file-invoice"></i> Receipt</a>
                    </div>

                    <div class="notes-section" style="margin-top:1.25rem;">
                        <h3 class="text-sm" style="text-transform:uppercase;color:var(--text-muted);font-weight:600;">Add Notes</h3>
                        <form action="index.php?page=finance_manager_add_transaction_note" method="POST">
                            <input type="hidden" name="payment_id" value="<?= $data['transaction']['payment_id'] ?>">
                            <textarea name="notes" placeholder="Add notes about this transaction..."><?= htmlspecialchars($data['transaction']['notes'] ?? '') ?></textarea>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Notes</button>
                        </form>
                    </div>
                </div>
            </div>

            <?php if (!empty($data['related_transactions'])): ?>
            <div class="card">
                <div class="card-header"><h2>Related Transactions</h2></div>
                <div class="card-body">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr style="text-transform:uppercase;color:var(--text-muted);font-size:.8rem;">
                                    <th>ID</th><th>Date</th><th>Amount</th><th>Status</th><th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['related_transactions'] as $related): ?>
                                <?php $rc = ($related['status']==='failed'?'failed':($related['status']==='flagged'?'flagged':($related['status']==='completed'?'completed':'pending'))); ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($related['payment_id']) ?></td>
                                    <td><?= htmlspecialchars(date('M d, Y', strtotime($related['payment_date']))) ?></td>
                                    <td>KSH <?= number_format($related['amount'], 2) ?></td>
                                    <td><span class="status-badge <?= $rc ?>"><?= ucfirst(htmlspecialchars($related['status'])) ?></span></td>
                                    <td><a class="btn btn-outline" href="index.php?page=finance_manager_transaction_details&id=<?= $related['payment_id'] ?>"><i class="fas fa-eye"></i> View</a></td>
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

    <div id="verifyOverlay" style="position:fixed;inset:0;background:rgba(0,0,0,0.55);display:none;align-items:center;justify-content:center;z-index:2000;">
        <div style="width:420px;max-width:90vw;background:#1f1f2e;color:var(--text-light);border:1px solid var(--border-color);border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.3);overflow:hidden;">
            <div style="padding:16px 18px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;">
                <div style="font-weight:600;">Confirm Verification</div>
                <button id="closeVerifyDialog" style="background:none;border:none;font-size:18px;cursor:pointer;color:var(--text-muted);">×</button>
            </div>
            <div style="padding:16px 18px;">
                <div id="verifyMessage" style="font-size:14px;color:var(--text-muted);">Proceed to confirm and verify this transaction?</div>
                <div id="verifyProgress" style="margin-top:12px;display:none;align-items:center;gap:10px;">
                    <div style="width:22px;height:22px;border:3px solid #93c5fd;border-top-color:#6366f1;border-radius:50%;animation:spin .9s linear infinite"></div>
                    <div style="font-size:14px;color:#6366f1">Verifying…</div>
                </div>
                <div id="verifySuccess" style="margin-top:12px;display:none;align-items:center;gap:10px;color:var(--success);font-weight:600;">
                    <i class="fas fa-check-circle"></i>
                    <span>Verified successfully</span>
                </div>
            </div>
            <div style="padding:12px 18px;border-top:1px solid var(--border-color);display:flex;justify-content:flex-end;gap:10px;">
                <button id="cancelVerifyBtn" class="btn btn-outline">Cancel</button>
                <button id="confirmVerifyBtn" class="btn btn-primary">Confirm</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(function(){
            var overlay = $('#verifyOverlay');
            var openBtn = $('#openVerifyDialog');
            var closeBtn = $('#closeVerifyDialog');
            var cancelBtn = $('#cancelVerifyBtn');
            var confirmBtn = $('#confirmVerifyBtn');
            var progress = $('#verifyProgress');
            var success = $('#verifySuccess');
            var message = $('#verifyMessage');
            function openDialog(){ overlay.css('display','flex'); success.hide(); progress.hide(); message.text('Proceed to confirm and verify this transaction?'); confirmBtn.prop('disabled', false).text('Confirm'); }
            function closeDialog(){ overlay.hide(); }
            openBtn.on('click', function(){ openDialog(); });
            closeBtn.on('click', function(){ closeDialog(); });
            cancelBtn.on('click', function(){ closeDialog(); });
            overlay.on('click', function(e){ if (e.target === overlay.get(0)) { closeDialog(); } });
            confirmBtn.on('click', function(){
                var pid = openBtn.data('payment-id');
                progress.css('display','inline-flex');
                confirmBtn.prop('disabled', true);
                $.ajax({ url:'index.php?page=finance_manager_verify_transaction', method:'POST', dataType:'json', data:{ payment_id: pid, redirect:'details', ajax:1 } })
                .done(function(resp){
                    progress.hide(); success.css('display','inline-flex'); setTimeout(function(){ closeDialog(); var sb=$('.status-badge'); sb.removeClass('pending failed flagged').addClass('completed'); sb.html('Confirmed & Verified <i class="fas fa-check-circle" style="color:var(--success)"></i>'); openBtn.prop('disabled', true).removeClass('btn-primary').addClass('btn-outline').text('Verified'); }, 700);
                }).fail(function(){ progress.hide(); message.text('Verification failed. Try again.'); confirmBtn.prop('disabled', false); });
            });
        });
    </script>
</body>
</html>
