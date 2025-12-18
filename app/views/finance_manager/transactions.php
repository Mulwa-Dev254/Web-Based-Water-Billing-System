<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Manager - Transactions</title>
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

        /* Transactions Card */
        .transactions-card {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .transactions-card h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #111827;
        }

        /* Filter Controls */
        .filter-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background-color: #f9fafb;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .filter-group select,
        .filter-group input {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .filter-buttons {
            display: flex;
            align-items: flex-end;
            gap: 0.5rem;
        }

        /* Transactions Table */
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
        }

        .transactions-table th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        .transactions-table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .transactions-table tr:last-child td {
            border-bottom: none;
        }

        .transactions-table .status {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .transactions-table .status.completed {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .transactions-table .status.pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .transactions-table .status.failed {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .transactions-table .status.flagged {
            background-color: rgba(139, 92, 246, 0.1);
            color: var(--purple);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .pagination-item {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            color: #4b5563;
            border: 1px solid #d1d5db;
            cursor: pointer;
            transition: all 0.2s;
        }

        .pagination-item:hover {
            background-color: #f3f4f6;
        }

        .pagination-item.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
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

        /* Buttons */
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

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .filter-controls {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include_once __DIR__ . '/partials/sidebar.php'; ?>

        <div class="main-content">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Transactions Management</h1>
                <p class="text-gray-600">View and manage all financial transactions</p>
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

            <!-- Transactions Card -->
            <div class="transactions-card">
                <h2>All Transactions</h2>
                
                <!-- Filter Controls -->
                <form action="index.php?page=finance_manager_transactions" method="GET" class="filter-controls">
                    <input type="hidden" name="page" value="finance_manager_transactions">
                    
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="w-40">
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
                        <input type="text" name="search" id="search" placeholder="Client name or ID" value="<?= $_GET['search'] ?? '' ?>" class="w-48">
                    </div>
                    
                    <div class="filter-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter mr-2"></i> Filter
                        </button>
                        <a href="index.php?page=finance_manager_transactions" class="btn btn-outline">
                            <i class="fas fa-redo mr-2"></i> Reset
                        </a>
                    </div>
                </form>
                
                <!-- Transactions Table -->
                <div class="overflow-x-auto">
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
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-gray-500">No transactions found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data['transactions'] as $transaction): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($transaction['payment_id'] ?? $transaction['id'] ?? 'N/A') ?></td>
                                        <td>
                                            <div class="font-medium text-gray-900"><?= htmlspecialchars($transaction['client_name'] ?? 'N/A') ?></div>
                                        </td>
                                        <td class="font-medium">KSH <?= number_format($transaction['amount'] ?? 0, 2) ?></td>
                                        <td><?= isset($transaction['payment_date']) ? htmlspecialchars(date('M d, Y', strtotime($transaction['payment_date']))) : 'N/A' ?></td>
                                        <td>
                                            <?php 
                                            $status = strtolower($transaction['status'] ?? 'pending');
                                            $statusClass = 'pending';
                                            if ($status === 'completed' || $status === 'confirmed_and_verified') {
                                                $statusClass = 'completed';
                                            } elseif ($status === 'failed') {
                                                $statusClass = 'failed';
                                            } elseif ($status === 'flagged') {
                                                $statusClass = 'flagged';
                                            }
                                            ?>
                                            <span class="status <?= $statusClass ?>">
                                                <?php $label = ($status === 'confirmed_and_verified') ? 'Confirmed & Verified' : ucfirst($status); ?>
                                                <?= htmlspecialchars($label) ?>
                                                <?php if ($status === 'confirmed_and_verified'): ?><i class="fas fa-check-circle" style="color:#10b981"></i><?php endif; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex space-x-2">
                                                <a href="index.php?page=finance_manager_transaction_details&id=<?= $transaction['payment_id'] ?? '' ?>" class="text-blue-600 hover:underline">View</a>
                                                
                                                <?php if (($transaction['status'] ?? '') !== 'flagged'): ?>
                                                <form action="index.php?page=finance_manager_flag_transaction" method="POST" class="inline">
                                                    <input type="hidden" name="payment_id" value="<?= $transaction['payment_id'] ?? '' ?>">
                                                    <button type="submit" class="text-purple-600 hover:underline">Flag</button>
                                                </form>
                                                <?php else: ?>
                                                <form action="index.php?page=finance_manager_unflag_transaction" method="POST" class="inline">
                                                    <input type="hidden" name="payment_id" value="<?= $transaction['payment_id'] ?? '' ?>">
                                                    <button type="submit" class="text-green-600 hover:underline">Unflag</button>
                                                </form>
                                                <?php endif; ?>
                                                
                                                <?php if (($transaction['status'] ?? '') === 'pending'): ?>
                                                <form action="index.php?page=finance_manager_transactions" method="POST" class="inline">
                                                    <input type="hidden" name="action" value="verify_transaction">
                                                    <input type="hidden" name="payment_id" value="<?= htmlspecialchars($transaction['payment_id'] ?? '') ?>">
                                                    <input type="hidden" name="verification_status" value="approved">
                                                    <button type="submit" class="text-green-600 hover:underline">Approve</button>
                                                </form>
                                                <form action="index.php?page=finance_manager_transactions" method="POST" class="inline">
                                                    <input type="hidden" name="action" value="verify_transaction">
                                                    <input type="hidden" name="payment_id" value="<?= htmlspecialchars($transaction['payment_id'] ?? '') ?>">
                                                    <input type="hidden" name="verification_status" value="rejected">
                                                    <button type="submit" class="text-red-600 hover:underline">Reject</button>
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
                
                <!-- Pagination -->
                <?php if (!empty($data['pagination'])): ?>
                <div class="pagination">
                    <?php if ($data['pagination']['current_page'] > 1): ?>
                        <a href="index.php?page=finance_manager_transactions&p=<?= $data['pagination']['current_page'] - 1 ?><?= $data['pagination']['query_string'] ?>" class="pagination-item">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $data['pagination']['total_pages']; $i++): ?>
                        <a href="index.php?page=finance_manager_transactions&p=<?= $i ?><?= $data['pagination']['query_string'] ?>" 
                           class="pagination-item <?= $i === $data['pagination']['current_page'] ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($data['pagination']['current_page'] < $data['pagination']['total_pages']): ?>
                        <a href="index.php?page=finance_manager_transactions&p=<?= $data['pagination']['current_page'] + 1 ?><?= $data['pagination']['query_string'] ?>" class="pagination-item">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Any transactions page specific JavaScript can go here
        });
    </script>
</body>
</html>
