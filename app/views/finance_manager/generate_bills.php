<?php
// app/views/finance_manager/generate_bills.php

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'finance_manager'])) {
    // Cannot use header redirect here as headers are already sent
    echo "<script>window.location.href = 'index.php?page=login';</script>";
    exit();
}

// Get the current page for sidebar highlighting
$currentPage = 'generate_bills';

// Initialize variables if not set
if (!isset($total_payments)) {
    $total_payments = 0;
}

if (!isset($total_balance)) {
    $total_balance = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Bills - Water Billing System</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --light: #f3f4f6;
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
            --header-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: var(--text-dark);
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        .main-content {
            margin-left: 16rem;
            padding: 2rem;
            width: calc(100% - 16rem);
            overflow-x: hidden;
            min-height: 100vh;
            position: relative;
            box-sizing: border-box;
        }
        
        @media (max-width: 1024px) {
            .main-content {
                padding: 1.5rem;
            }
            
            .grid-layout {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
        
        /* App Header */
        .app-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            margin-bottom: 2rem;
            background-color: var(--header-bg);
            border-bottom: 1px solid var(--border-color);
        }
        
        .app-header-left {
            display: flex;
            align-items: center;
        }
        
        .app-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }
        
        .app-header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-welcome {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
            position: relative;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: -0.5rem;
            left: 0;
            width: 3rem;
            height: 0.25rem;
            background-color: var(--primary);
            border-radius: 0.25rem;
        }

        .card {
            background-color: white;
            border-radius: 1rem;
            padding: 1.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
            margin-bottom: 2rem;
            border: 1px solid rgba(229, 231, 235, 0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .card-header {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
        }

        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
        }

        .card-header-primary {
            color: var(--primary);
        }

        .card-header-secondary {
            background-color: rgba(71, 85, 105, 0.1);
            border-bottom: 1px solid rgba(71, 85, 105, 0.2);
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0;
            color: var(--text-dark);
        }

        .card-header i {
            margin-right: 0.75rem;
            color: var(--primary);
            font-size: 1.25rem;
        }

        .card-body {
            padding: 1rem;
        }

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

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--danger);
            color: var(--danger);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        .btn:active {
            transform: scale(0.95);
        }
        
        .btn i {
            margin-right: 0.5rem;
        }

        .btn-primary {
            color: #fff;
            background-color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2), 0 2px 4px -1px rgba(79, 70, 229, 0.1);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }
        
        .btn-outline:hover {
            background-color: rgba(79, 70, 229, 0.05);
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.1), 0 2px 4px -1px rgba(79, 70, 229, 0.05);
            transform: translateY(-2px);
        }
        
        .btn-group {
            display: flex;
            gap: 0.75rem;
        }
        
        .grid-layout {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .summary-item {
            margin-bottom: 0.75rem;
        }

        .summary-label {
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
        }

        .summary-value {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .interactive-card {
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }

        .interactive-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        .interactive-card:hover .card-overlay {
            opacity: 1;
        }

        .card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(37, 99, 235, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .card-overlay-content {
            background-color: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.25);
        }

        .card-overlay-content i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <?php include_once '../app/views/finance_manager/partials/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="app-header animate__animated animate__fadeIn">
            <div class="app-header-left">
                <h1 class="app-title">Water Billing System</h1>
            </div>
            <div class="app-header-right">
                <div class="user-welcome">
                    <span>Welcome, <?php echo isset($_SESSION['name']) ? $_SESSION['name'] : 'User'; ?></span>
                </div>
                <a href="index.php?page=profile" class="btn btn-outline">
                    <i class="fas fa-user-circle"></i>
                </a>
            </div>
        </div>
        
        <div class="page-header animate__animated animate__fadeIn">
            <h1 class="page-title">Generate Bills</h1>
            <div class="btn-group">
                <a href="index.php?page=billing_dashboard" class="btn btn-outline">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="index.php?page=billing_reports" class="btn btn-outline">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </div>
        </div>
        <?php if (isset($success_message) && !empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message) && !empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="grid-layout" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
            <!-- Billing Summary Card -->
            <div class="card">
                <div class="card-header card-header-primary">
                    <i class="fas fa-chart-pie"></i>
                    <h3 class="card-title">Billing Summary</h3>
                </div>
                <div class="card-body">
                    <div class="summary-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div class="summary-item" style="background-color: #f9fafb; border-radius: 0.5rem; padding: 1rem; text-align: center;">
                            <div class="summary-label" style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Total Bills</div>
                            <div class="summary-value" style="font-size: 1.5rem; font-weight: 600; color: var(--text-dark);"><?php echo $total_bills; ?></div>
                        </div>
                        <div class="summary-item" style="background-color: #f9fafb; border-radius: 0.5rem; padding: 1rem; text-align: center;">
                            <div class="summary-label" style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Pending Bills</div>
                            <div class="summary-value" style="font-size: 1.5rem; font-weight: 600; color: var(--text-dark);"><?php echo $pending_bills; ?></div>
                        </div>
                        <div class="summary-item" style="background-color: #f9fafb; border-radius: 0.5rem; padding: 1rem; text-align: center;">
                            <div class="summary-label" style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Total Payments</div>
                            <div class="summary-value" style="font-size: 1.5rem; font-weight: 600; color: var(--text-dark);">KSH <?php echo number_format($total_payments, 2); ?></div>
                        </div>
                        <div class="summary-item" style="background-color: #f9fafb; border-radius: 0.5rem; padding: 1rem; text-align: center;">
                            <div class="summary-label" style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Outstanding Balance</div>
                            <div class="summary-value" style="font-size: 1.5rem; font-weight: 600; color: var(--text-dark);">KSH <?php echo number_format($total_balance, 2); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Generate All Bills Card -->
            <div class="card">
                <div class="card-header card-header-primary">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <h3 class="card-title">Generate All Pending Bills</h3>
                </div>
                <div class="card-body">
                    <p>This will generate bills for all meters with new readings that haven't been billed yet.</p>
                    <form action="index.php?page=generate_bills" method="post" class="mt-4">
                        <button type="submit" name="generate_all" class="btn btn-primary">
                            <i class="fas fa-cogs"></i> Generate All Pending Bills
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Generate Single Bill Card -->
        <div class="card interactive-card" style="margin-top: 1.5rem;" onclick="window.location.href='index.php?page=generate_single_bill'">
            <div class="card-header card-header-secondary">
                <i class="fas fa-file-invoice"></i>
                <h3 class="card-title">Generate Single Bill</h3>
            </div>
            <div class="card-body">
                <p>Need to generate a bill for a specific customer? Click here to create a single bill for any meter in the system.</p>
                <div class="card-overlay">
                    <div class="card-overlay-content">
                        <i class="fas fa-arrow-right"></i> Generate Single Bill
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($generation_results) && !empty($generation_results)): ?>
                        <div class="mt-4">
                            <h5>Bill Generation Results</h5>
                            <div class="alert alert-info">
                                <p>Successfully generated: <?php echo $generation_results['success']; ?> bills</p>
                                <p>Failed to generate: <?php echo $generation_results['failed']; ?> bills</p>
                            </div>
                            
                            <?php if (!empty($generation_results['details'])): ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Client ID</th>
                                            <th>Meter ID</th>
                                            <th>Status</th>
                                            <th>Bill ID</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($generation_results['details'] as $result): ?>
                                            <tr>
                                                <td><?php echo $result['client_id']; ?></td>
                                                <td><?php echo $result['meter_id']; ?></td>
                                                <td>
                                                    <?php if ($result['status'] === 'success'): ?>
                                                        <span class="status-badge status-success">Success</span>
                                                    <?php else: ?>
                                                        <span class="status-badge status-danger">Failed</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (isset($result['bill_id'])): ?>
                                                        <?php echo $result['bill_id']; ?>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (isset($result['bill_id'])): ?>
                                                        <a href="index.php?page=view_bill&id=<?php echo $result['bill_id']; ?>" class="btn btn-primary"><i class="fas fa-eye"></i> View Bill</a>
                                                    <?php else: ?>
                                                        <?php if (isset($result['reason'])): ?>
                                                            <span class="text-danger"><?php echo $result['reason']; ?></span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

        <!-- Pending Bill Generation List -->
        <?php if (isset($pending_generation_meters) && is_array($pending_generation_meters)): ?>
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header card-header-secondary">
                    <i class="fas fa-clock"></i>
                    <h3 class="card-title">Meters Pending Bill Generation</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_generation_meters)): ?>
                        <div class="alert alert-info d-flex align-items-center" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <div>No meters currently require bill generation.</div>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Meter ID</th>
                                        <th>Client ID</th>
                                        <th>Last Billed Reading ID</th>
                                        <th>Latest Reading ID</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_generation_meters as $pm): ?>
                                        <tr>
                                            <td><?php echo (int)$pm['meter_id']; ?></td>
                                            <td><?php echo (int)$pm['client_id']; ?></td>
                                            <td><?php echo $pm['last_billed_reading_id'] !== null ? (int)$pm['last_billed_reading_id'] : '-'; ?></td>
                                            <td><?php echo (int)$pm['latest_reading_id']; ?></td>
                                            <td>
                                                <a href="index.php?page=generate_single_bill&meter_id=<?php echo (int)$pm['meter_id']; ?>" class="btn btn-outline">
                                                    <i class="fas fa-file-invoice"></i> Generate Single Bill
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
