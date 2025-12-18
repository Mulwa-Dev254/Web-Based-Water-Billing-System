<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • Transactions</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:#ff4757;
            --primary-dark:#e84118;
            --dark-bg:#1e1e2d;
            --darker-bg:#151521;
            --sidebar-bg:#1a1a27;
            --card-bg:#2a2a3c;
            --text-light:#f8f9fa;
            --text-muted:#a1a5b7;
            --border-color:#2d2d3a;
            --success:#1dd1a1;
            --info:#2e86de;
            --warning:#ff9f43;
            --danger:#ee5253;
            --purple:#5f27cd;
        }

        * { box-sizing: border-box; }
        body { font-family:'Inter',sans-serif; background-color:var(--dark-bg); color:var(--text-light); line-height:1.6; margin:0; }
        a { text-decoration:none; color:inherit; }

        .dashboard-layout { display:flex; width:100%; min-height:100vh; }
        .sidebar { width:280px; background-color:var(--sidebar-bg); padding:1.5rem 0; position:fixed; top:0; left:0; height:100vh; border-right:1px solid var(--border-color); box-shadow:0 0 15px rgba(0,0,0,.1); }
        .sidebar-header { padding:0 1.5rem 1.5rem; border-bottom:1px solid var(--border-color); margin-bottom:1.5rem; }
        .sidebar-header h3 { color:var(--primary); font-size:1.5rem; font-weight:700; display:flex; align-items:center; gap:.75rem; }
        .sidebar-nav { height:calc(100vh - 120px); overflow-y:auto; padding:0 1rem; }
        .sidebar-nav ul { list-style:none; margin:0; padding:0; }
        .sidebar-nav a { display:flex; align-items:center; gap:.75rem; padding:.875rem 1rem; border-radius:.5rem; font-weight:500; color:var(--text-muted); transition:all .3s ease; }
        .sidebar-nav a:hover { background-color:rgba(255,71,87,.1); color:var(--text-light); }
        .sidebar-nav a.active { background-color:var(--primary); color:#fff; box-shadow:0 4px 15px rgba(255,71,87,.3); }
        .sidebar-nav a i { width:1.5rem; text-align:center; font-size:1.1rem; }

        .main-content { margin-left:280px; flex-grow:1; min-height:100vh; }
        .header-bar { background-color:var(--sidebar-bg); padding:1.25rem 2rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-color); position:sticky; top:0; z-index:100; }
        .header-title h1 { font-size:1.4rem; font-weight:600; margin:0; }
        .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .9rem; border-radius:.5rem; border:1px solid var(--border-color); background:transparent; color:var(--text-light); cursor:pointer; }
        .btn-primary { background-color:var(--primary); border-color:var(--primary); }
        .btn-outline { background:transparent; }

        .dashboard-container { padding:2rem; }
        .card { background-color:var(--card-bg); border:1px solid var(--border-color); border-radius:.75rem; box-shadow:0 0 20px rgba(0,0,0,.1); padding:1.5rem; }
        .card h2 { font-size:1.25rem; font-weight:600; margin:0 0 1rem 0; color:var(--text-light); }

        .filter-controls { display:flex; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem; padding:1rem; background-color:#1f1f2e; border-radius:.5rem; border:1px solid var(--border-color); }
        .filter-group { display:flex; flex-direction:column; }
        .filter-group label { font-size:.75rem; font-weight:500; color:var(--text-muted); margin-bottom:.25rem; }
        .filter-group select, .filter-group input { padding:.5rem; border:1px solid var(--border-color); border-radius:.375rem; font-size:.875rem; background-color:#1f1f2e; color:var(--text-light); }
        .filter-buttons { display:flex; align-items:flex-end; gap:.5rem; }

        .transactions-table { width:100%; border-collapse:collapse; }
        .transactions-table th { text-align:left; padding:.75rem 1rem; font-size:.75rem; text-transform:uppercase; color:var(--text-muted); border-bottom:1px solid var(--border-color); background:#232336; }
        .transactions-table td { padding:1rem; border-bottom:1px solid var(--border-color); }
        .status { display:inline-flex; align-items:center; padding:.25rem .75rem; border-radius:9999px; font-size:.75rem; font-weight:600; }
        .status.completed { background-color:rgba(29,209,161,.12); color:var(--success); }
        .status.pending { background-color:rgba(255,159,67,.12); color:var(--warning); }
        .status.failed { background-color:rgba(238,82,83,.12); color:var(--danger); }
        .status.flagged { background-color:rgba(95,39,205,.12); color:var(--purple); }

        .alert { padding:.75rem 1rem; border-radius:.5rem; border:1px solid var(--border-color); background-color:#232336; color:var(--text-light); margin-bottom:1rem; }
        .alert-success { border-left:4px solid var(--success); color:var(--success); }
        .alert-error { border-left:4px solid var(--danger); color:var(--danger); }

        .pagination { display:flex; justify-content:center; gap:.5rem; margin-top:1.5rem; }
        .pagination-item { display:flex; align-items:center; justify-content:center; width:2rem; height:2rem; border-radius:.375rem; font-size:.875rem; color:var(--text-muted); border:1px solid var(--border-color); cursor:pointer; }
        .pagination-item.active { background-color:var(--primary); color:#fff; border-color:var(--primary); }

        @media (max-width: 992px){ .main-content{ margin-left:0; } .sidebar{ position:relative; width:100%; height:auto; } .dashboard-layout{ flex-direction:column; } }
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
                    <li><a href="index.php?page=admin_manage_client_plans"><i class="fas fa-layer-group"></i> Client Plans</a></li>
                    <li><a href="index.php?page=generate_bills"><i class="fas fa-file-invoice-dollar"></i> Generate Bills</a></li>
            <li><a href="index.php?page=view_bills"><i class="fas fa-list"></i> View Bills</a></li>
            <li><a href="index.php?page=finance_manager_reports"><i class="fas fa-chart-pie"></i> Financial Reports</a></li>
            <li><a href="index.php?page=billing_reports"><i class="fas fa-chart-line"></i> Billing Reports</a></li>
                    <li><a href="index.php?page=admin_transactions" class="active"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
                    <li><a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <div class="main-content">
            <div class="header-bar">
                <div class="header-title"><h1>Transactions Management</h1></div>
                <div class="user-info"><a href="index.php?page=logout" class="btn btn-primary"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
            </div>

            <div class="dashboard-container">
                <?php if (!empty($data['error'])): ?>
                    <div class="alert alert-error"><p><?= htmlspecialchars($data['error']) ?></p></div>
                <?php endif; ?>
                <?php if (!empty($data['success'])): ?>
                    <div class="alert alert-success"><p><?= htmlspecialchars($data['success']) ?></p></div>
                <?php endif; ?>

                <div class="card">
                    <h2>All Transactions</h2>
                    <form action="index.php?page=admin_transactions" method="GET" class="filter-controls">
                        <input type="hidden" name="page" value="admin_transactions">
                        <div class="filter-group">
                            <label for="status">Status</label>
                            <select name="status" id="status">
                                <option value="">All Statuses</option>
                                <option value="completed" <?= isset($_GET['status']) && $_GET['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="pending" <?= isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="failed" <?= isset($_GET['status']) && $_GET['status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                                <option value="flagged" <?= isset($_GET['status']) && $_GET['status'] === 'flagged' ? 'selected' : '' ?>>Flagged</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="date_from">Date From</label>
                            <input type="date" name="date_from" id="date_from" value="<?= $_GET['date_from'] ?? '' ?>">
                        </div>
                        <div class="filter-group">
                            <label for="date_to">Date To</label>
                            <input type="date" name="date_to" id="date_to" value="<?= $_GET['date_to'] ?? '' ?>">
                        </div>
                        <div class="filter-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" placeholder="Client name or ID" value="<?= $_GET['search'] ?? '' ?>">
                        </div>
                        <div class="filter-buttons">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                            <a href="index.php?page=admin_transactions" class="btn btn-outline"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="transactions-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($data['transactions'])): ?>
                                <tr><td colspan="6">No transactions found</td></tr>
                            <?php else: ?>
                                <?php foreach ($data['transactions'] as $transaction): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($transaction['payment_id'] ?? $transaction['id'] ?? 'N/A') ?></td>
                                        <td><div class="font-medium"><?= htmlspecialchars($transaction['client_name'] ?? 'N/A') ?></div></td>
                                        <td class="font-medium">KSH <?= number_format($transaction['amount'] ?? 0, 2) ?></td>
                                        <td><?= isset($transaction['payment_date']) ? htmlspecialchars(date('M d, Y', strtotime($transaction['payment_date']))) : 'N/A' ?></td>
                                        <td>
                                            <?php $status = strtolower($transaction['status'] ?? 'pending'); $statusClass = ($status==='failed'?'failed':($status==='flagged'?'flagged':(($status==='completed' || $status==='confirmed_and_verified')?'completed':'pending'))); ?>
                                            <span class="status <?= $statusClass ?>">
                                                <?php $label = ($status==='confirmed_and_verified')?'Confirmed & Verified':ucfirst($status); echo htmlspecialchars($label); ?>
                                                <?php if ($status==='confirmed_and_verified'): ?><i class="fas fa-check-circle" style="color:var(--success)"></i><?php endif; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display:flex;gap:.5rem;align-items:center;">
                                                <a href="index.php?page=finance_manager_transaction_details&id=<?= $transaction['payment_id'] ?? '' ?>" class="btn btn-outline"><i class="fas fa-eye"></i> View</a>
                                                <?php if (($transaction['status'] ?? '') !== 'flagged'): ?>
                                                    <form action="index.php?page=finance_manager_flag_transaction" method="POST" style="display:inline-flex;">
                                                        <input type="hidden" name="payment_id" value="<?= $transaction['payment_id'] ?? '' ?>">
                                                        <button type="submit" class="btn btn-outline" style="color:var(--purple)"><i class="fas fa-flag"></i> Flag</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form action="index.php?page=finance_manager_unflag_transaction" method="POST" style="display:inline-flex;">
                                                        <input type="hidden" name="payment_id" value="<?= $transaction['payment_id'] ?? '' ?>">
                                                        <button type="submit" class="btn btn-outline" style="color:var(--success)"><i class="fas fa-flag"></i> Unflag</button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if (($transaction['status'] ?? '') === 'pending'): ?>
                                                    <form action="index.php?page=finance_manager_transactions" method="POST" style="display:inline-flex;">
                                                        <input type="hidden" name="action" value="verify_transaction">
                                                        <input type="hidden" name="payment_id" value="<?= htmlspecialchars($transaction['payment_id'] ?? '') ?>">
                                                        <input type="hidden" name="verification_status" value="approved">
                                                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Approve</button>
                                                    </form>
                                                    <form action="index.php?page=finance_manager_transactions" method="POST" style="display:inline-flex;">
                                                        <input type="hidden" name="action" value="verify_transaction">
                                                        <input type="hidden" name="payment_id" value="<?= htmlspecialchars($transaction['payment_id'] ?? '') ?>">
                                                        <input type="hidden" name="verification_status" value="rejected">
                                                        <button type="submit" class="btn btn-outline" style="color:var(--danger)"><i class="fas fa-times"></i> Reject</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!empty($data['pagination'])): ?>
                    <div class="pagination">
                        <?php if ($data['pagination']['current_page'] > 1): ?>
                            <a href="index.php?page=admin_transactions&p=<?= $data['pagination']['current_page'] - 1 ?><?= $data['pagination']['query_string'] ?>" class="pagination-item"><i class="fas fa-chevron-left"></i></a>
                        <?php endif; ?>
                        <?php for ($i=1; $i <= $data['pagination']['total_pages']; $i++): ?>
                            <a href="index.php?page=admin_transactions&p=<?= $i ?><?= $data['pagination']['query_string'] ?>" class="pagination-item <?= $i === $data['pagination']['current_page'] ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($data['pagination']['current_page'] < $data['pagination']['total_pages']): ?>
                            <a href="index.php?page=admin_transactions&p=<?= $data['pagination']['current_page'] + 1 ?><?= $data['pagination']['query_string'] ?>" class="pagination-item"><i class="fas fa-chevron-right"></i></a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
