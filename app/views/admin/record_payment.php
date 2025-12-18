<?php
$bill = $data['bill'] ?? null;
$success = $data['success'] ?? '';
$error = $data['error'] ?? '';
if (!$bill) { echo "<script>window.location.href='index.php?page=view_bills';</script>"; exit; }
$remainingBalance = max((float)($bill['amount_due'] ?? 0) - (float)($bill['amount_paid'] ?? 0), 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • Record Payment</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--primary:#ff4757;--primary-dark:#e84118;--dark-bg:#1e1e2d;--sidebar-bg:#1a1a27;--card-bg:#2a2a3c;--text-light:#f8f9fa;--text-muted:#a1a5b7;--border-color:#2d2d3a;--success:#1dd1a1;--danger:#ee5253}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background-color:var(--dark-bg);color:var(--text-light);line-height:1.6;display:flex;min-height:100vh;overflow-x:hidden}
        .dashboard-layout{display:flex;width:100%;min-height:100vh}
        .main-content{margin-left:280px;flex-grow:1;min-height:100vh;min-width:0}
        .header-bar{background-color:var(--sidebar-bg);padding:1.25rem 2rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border-color)}
        .card{background-color:var(--card-bg);border:1px solid var(--border-color);border-radius:.75rem;box-shadow:0 0 20px rgba(0,0,0,.1);margin:1rem}
        .card-header{padding:1rem 1.25rem;border-bottom:1px solid var(--border-color);display:flex;align-items:center;gap:.5rem;color:var(--text-light);font-weight:700}
        .card-body{padding:1rem 1.25rem}
        .grid-2{display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:1rem;margin:1rem}
        .info-row{display:grid;grid-template-columns:180px 1fr;gap:.5rem;padding:.4rem 0;color:var(--text-muted)}
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem .9rem;border-radius:.5rem;border:1px solid var(--border-color);background:transparent;color:var(--text-light);cursor:pointer}
        .btn-primary{background-color:var(--primary);border-color:var(--primary)}
        .btn-outline{background:transparent;border-color:var(--border-color)}
        .form-control,.form-select{width:100%;padding:.6rem .7rem;border-radius:.5rem;border:1px solid var(--border-color);background-color:#1f1f2e;color:var(--text-light)}
        @media(max-width:992px){.main-content{margin-left:0}.dashboard-layout{flex-direction:column}}
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php $page = 'view_bills'; include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="main-content" id="mainContent">
            <div class="header-bar">
                <div style="display:flex;align-items:center;gap:1rem"><h1 style="font-size:1.5rem;font-weight:700">Record Payment</h1></div>
                <div><a href="index.php?page=view_bills" class="btn btn-outline"><i class="fas fa-list"></i> View Bills</a></div>
            </div>
            <?php if (!empty($error)): ?><div class="card"><div class="card-body" style="color:#f87171"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div></div><?php endif; ?>
            <?php if (!empty($success)): ?><div class="card"><div class="card-body" style="color:#34d399"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div></div><?php endif; ?>
            <div class="grid-2">
                <div class="card">
                    <div class="card-header"><i class="fas fa-file-invoice"></i><span>Bill Information</span></div>
                    <div class="card-body">
                        <div class="info-row"><div>Bill Number</div><div>#<?= (int)$bill['id'] ?></div></div>
                        <div class="info-row"><div>Client</div><div><?= htmlspecialchars($bill['client_name'] ?? ($bill['client'] ?? '')) ?></div></div>
                        <div class="info-row"><div>Bill Date</div><div><?= isset($bill['bill_date']) ? date('d M Y', strtotime($bill['bill_date'])) : '' ?></div></div>
                        <div class="info-row"><div>Due Date</div><div><?= isset($bill['due_date']) ? date('d M Y', strtotime($bill['due_date'])) : '' ?></div></div>
                        <div class="info-row"><div>Amount Due</div><div>KES <?= number_format((float)($bill['amount_due'] ?? 0), 2) ?></div></div>
                        <div class="info-row"><div>Amount Paid</div><div>KES <?= number_format((float)($bill['amount_paid'] ?? 0), 2) ?></div></div>
                        <div class="info-row"><div>Remaining Balance</div><div style="color:<?= $remainingBalance>0?'#f87171':'#34d399' ?>">KES <?= number_format($remainingBalance, 2) ?></div></div>
                        <div class="info-row"><div>Status</div><div><?= htmlspecialchars(ucfirst(str_replace('_',' ', ($bill['payment_status'] ?? ($bill['status'] ?? 'pending'))))) ?></div></div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header"><i class="fas fa-money-bill-wave"></i><span>Record Payment</span></div>
                    <div class="card-body">
                        <?php if (strtolower($bill['payment_status'] ?? ($bill['status'] ?? 'pending')) === 'paid'): ?>
                            <div style="color:var(--text-muted)"><i class="fas fa-info-circle"></i> This bill has been fully paid.</div>
                        <?php else: ?>
                        <form method="post" action="index.php?page=record_payment&bill_id=<?= (int)$bill['id'] ?>" style="display:grid;gap:1rem;max-width:480px">
                            <div>
                                <label>Payment Amount (KES)</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" max="<?= $remainingBalance ?>" value="<?= $remainingBalance ?>" required>
                                <div style="margin-top:.35rem;color:var(--text-muted);font-size:.875rem">Maximum: KES <?= number_format($remainingBalance, 2) ?></div>
                            </div>
                            <div>
                                <label>Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select payment method</option>
                                    <option value="cash">Cash</option>
                                    <option value="mpesa">M-Pesa</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div id="transactionIdContainer" style="display:none">
                                <label>Transaction ID/Reference</label>
                                <input type="text" class="form-control" id="transaction_id" name="transaction_id">
                            </div>
                            <div>
                                <label>Payment Date</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div>
                                <label>Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>
                            <div style="display:flex;gap:.5rem">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle"></i> Record Payment</button>
                                <a href="index.php?page=view_bill_details&bill_id=<?= (int)$bill['id'] ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Bill</a>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const method=document.getElementById('payment_method');
        const txCont=document.getElementById('transactionIdContainer');
        const tx=document.getElementById('transaction_id');
        method?.addEventListener('change', function(){
            const v=this.value;
            if(v==='mpesa'||v==='bank_transfer'||v==='cheque'){ txCont.style.display='block'; tx.required=true; } else { txCont.style.display='none'; tx.required=false; }
        });
    });
    </script>
</body>
</html>
