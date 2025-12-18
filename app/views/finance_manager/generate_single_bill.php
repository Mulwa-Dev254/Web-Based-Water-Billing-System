<?php
// app/views/finance_manager/generate_single_bill.php

// Ensure this page is only accessible to finance managers and admins
if (!isset($_SESSION['user_id']) || (!in_array($_SESSION['role'], ['finance_manager', 'admin']))) {
    // Cannot use header redirect here as headers are already sent
    echo "<script>window.location.href = 'index.php?page=login';</script>";
    exit;
}

// Extract data from controller
$meters = $data['meters'] ?? [];
$success = $data['success'] ?? '';
$error = $data['error'] ?? '';

// Get meter ID from URL if present (for pre-selection)
$selectedMeterId = isset($_GET['meter_id']) ? (int)$_GET['meter_id'] : 0;
$pending_generation_meters = $data['pending_generation_meters'] ?? [];
$pending_generation_count = $data['pending_generation_count'] ?? 0;
$bills_generated_today = $data['bills_generated_today'] ?? 0;
$overdue_bills_count = $data['overdue_bills_count'] ?? 0;
$recent_bills = $data['recent_bills'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Single Bill - Water Billing System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #64748b;
            --success: #10b981;
            --info: #3b82f6;
            --warning: #f59e0b;
            --danger: #ef4444;
            --light: #f9fafb;
            --dark: #1f2937;
            --text-light: #f9fafb;
            --text-dark: #111827;
            --text-muted: #6b7280;
            --bg-light: #ffffff;
            --bg-dark: #111827;
            --border-color: #e5e7eb;
            --dark-bg: #1e1e2d;
            --sidebar-bg: #1a1a27;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        .app-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
            height: 60px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .app-header .logo {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .app-header .logo i {
            font-size: 1.5rem;
        }

        .app-header .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        /* --- FIXED LAYOUT FOR MAIN CONTENT--- */
        .dashboard-container {
            min-height: 100vh;
            background: #f3f4f6;
            width: 100%;
        }
        
        .main-content {
            padding: 1.5rem 1.75rem;
            box-sizing: border-box;
            margin-left: 16rem;
            width: calc(100% - 16rem);
            overflow-y: auto;
            background: #f3f4f6;
            min-height: 100vh;
        }
        
        @media (max-width: 768px) {
            .main-content { 
                margin-left: 0; 
                width: 100%; 
                padding: 1rem; 
            }
        }
        /* --- END FIXED LAYOUT --- */

        .form-card {
            margin-bottom: 20px;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: box-shadow 0.3s ease;
        }

        .form-card:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .reading-details {
            margin-top: 20px;
            display: none;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            background: white;
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }

        .btn-outline {
            color: var(--primary);
            background-color: transparent;
            border: 1px solid var(--primary);
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-outline:hover {
            color: #fff;
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-group {
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            border-radius: 0.375rem;
            overflow: hidden;
        }
        
        .form-group { 
            margin-bottom: 1rem; 
        }
        
        .form-label { 
            font-weight: 600; 
            margin-bottom: 0.5rem; 
            display: block; 
            color: var(--dark);
        }
        
        .form-control, select, input[type="text"], input[type="date"], input[type="number"], input[type="checkbox"] {
            width: 100%; 
            padding: 0.6rem 0.75rem; 
            border: 1px solid var(--border-color);
            border-radius: 0.5rem; 
            background: #fff; 
            color: var(--text-dark); 
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            font-size: 0.875rem;
        }
        
        .form-control:focus, select:focus, input:focus {
            border-color: var(--primary); 
            box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
        }
        
        .form-check { 
            display: flex; 
            align-items: center; 
            gap: 0.5rem; 
            margin: 0.75rem 0; 
        }
        
        .form-check-input { 
            width: 1.1rem; 
            height: 1.1rem; 
        }
        
        .breadcrumb { 
            list-style: none; 
            display: flex; 
            gap: 0.5rem; 
            padding: 0; 
            margin: 0 0 1rem; 
            color: var(--text-muted); 
            font-size: 0.875rem;
        }
        
        .breadcrumb-item a { 
            color: var(--primary); 
            text-decoration: none; 
        }
        
        .breadcrumb-item.active { 
            color: var(--text-muted); 
        }
        
        .alert { 
            padding: 0.75rem 1rem; 
            border-radius: 0.5rem; 
            margin-bottom: 1rem; 
            display: flex; 
            align-items: center; 
            gap: 0.5rem; 
        }
        
        .alert-success { 
            background: #ecfdf5; 
            color: #065f46; 
            border: 1px solid #10b981; 
        }
        
        .alert-danger { 
            background: #fef2f2; 
            color: #7f1d1d; 
            border: 1px solid #ef4444; 
        }
        
        .alert-info { 
            background: #eff6ff; 
            color: #1e40af; 
            border: 1px solid #3b82f6; 
        }
        
        .fade-out { 
            opacity: 0; 
            transition: opacity 400ms ease; 
        }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat { display: flex; align-items: center; gap: 0.75rem; }
        .stat .icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(79,70,229,.1); color: #4f46e5; }
        .stat .info { display: flex; flex-direction: column; }
        .stat .label { font-size: .85rem; color: #6b7280; }
        .stat .value { font-size: 1.5rem; font-weight: 700; }
        .table-responsive { overflow: auto; }
        .modern-table { width: 100%; border-collapse: collapse; }
        .modern-table th, .modern-table td { padding: .85rem; border-bottom: 1px solid #e5e7eb; text-align: left; }
        .modern-table th { font-weight: 600; color: #111827; background: #f9fafb; }
        .modern-table tbody tr { transition: background .2s ease, transform .1s ease; }
        .modern-table tbody tr:hover { background: #f5f7ff; transform: translateY(-1px); }
        .btn { display: inline-flex; align-items: center; gap: .5rem; padding: .55rem 1rem; border: 1px solid #4f46e5; border-radius: 10px; color: #4f46e5; background: #fff; text-decoration: none; position: relative; overflow: hidden; transition: transform .12s ease, box-shadow .2s ease; }
        .btn-primary { background: #4f46e5; color: #fff; }
        .btn:hover { box-shadow: 0 8px 18px rgba(79,70,229,.18); transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }
        .btn::after { content: ""; position: absolute; left: 50%; top: 50%; width: 0; height: 0; background: rgba(255,255,255,.35); transform: translate(-50%,-50%); border-radius: 50%; transition: width .35s ease, height .35s ease; }
        .btn:active::after { width: 220%; height: 220%; }

        /* --- Professional card + form UI matching dashboard theme --- */
        .card-header { 
            display: flex; 
            align-items: center; 
            gap: 0.75rem; 
            padding: 1rem 1.25rem; 
            border-bottom: 1px solid var(--border-color); 
            background: #ffffff; 
            color: var(--dark); 
        }
        
        .card-header-primary { 
            border-left: 3px solid var(--primary); 
        }
        
        .card-header-secondary { 
            border-left: 3px solid var(--secondary); 
        }
        
        .card-title { 
            font-size: 1.125rem; 
            font-weight: 600; 
            margin: 0; 
        }
        
        .card-body { 
            padding: 1.25rem; 
            background: #ffffff; 
            color: var(--text-dark); 
        }
        
        .breadcrumb-nav { 
            margin-bottom: 0.75rem; 
        }

        /* Input + select UI */
        .select-wrapper { 
            position: relative; 
        }
        
        .select-wrapper select { 
            appearance: none; 
            padding-right: 2rem; 
        }
        
        .select-icon { 
            position: absolute; 
            right: 12px; 
            top: 50%; 
            transform: translateY(-50%); 
            color: var(--text-muted); 
            pointer-events: none; 
        }

        /* Form layout helpers */
        .form-row { 
            display: flex; 
            gap: 1rem; 
            flex-wrap: wrap; 
        }
        
        .form-group-half { 
            flex: 1; 
            min-width: 240px; 
        }
        
        .form-actions { 
            display: flex; 
            justify-content: flex-end; 
            margin-top: 0.75rem; 
        }

        /* Reading details */
        .reading-details-card { 
            margin-top: 0.5rem; 
        }
        
        .reading-details-grid { 
            display: flex; 
            gap: 1rem; 
            flex-wrap: wrap; 
        }
        
        .reading-details-column { 
            flex: 1; 
            min-width: 240px; 
            background: #ffffff; 
            border: 1px solid var(--border-color); 
            border-radius: 0.5rem; 
            padding: 1rem; 
        }
        
        .reading-title { 
            font-weight: 600; 
            color: var(--dark); 
            margin: 0 0 0.5rem; 
            font-size: 1rem;
        }
        
        .reading-info { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 0.375rem 0; 
            border-bottom: 1px dashed #e5e7eb; 
        }
        
        .reading-info:last-child { 
            border-bottom: none; 
        }
        
        .reading-label { 
            color: var(--text-muted); 
            font-size: 0.875rem;
        }
        
        .reading-value { 
            font-weight: 600; 
            color: var(--dark); 
            font-size: 0.875rem;
        }
        
        .consumption-info { 
            margin-top: 1rem; 
        }

        @media (max-width: 768px) {
            .dashboard-container { 
                grid-template-columns: 1fr; 
            }
            .form-row { 
                flex-direction: column; 
            }
            .form-group-half { 
                min-width: 100%; 
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Generate Single Bill</h1>
            </div>

            <nav class="breadcrumb-nav">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php?page=billing_dashboard">Billing Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?page=generate_bills">Generate Bills</a></li>
                    <li class="breadcrumb-item active">Generate Single Bill</li>
                </ol>
            </nav>

            <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success ?>
            </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="card">
                    <div class="card-body">
                        <div class="stat">
                            <div class="icon"><i class="fas fa-clock"></i></div>
                            <div class="info">
                                <div class="label">Meters Pending Generation</div>
                                <div class="value"><?= number_format($pending_generation_count) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="stat">
                            <div class="icon"><i class="fas fa-file-invoice"></i></div>
                            <div class="info">
                                <div class="label">Bills Generated Today</div>
                                <div class="value"><?= number_format($bills_generated_today) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="stat">
                            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="info">
                                <div class="label">Overdue Bills</div>
                                <div class="value"><?= number_format($overdue_bills_count) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list"></i>
                    <h3 class="card-title">Pending Bill Generation</h3>
                </div>
                <div class="card-body">
                    <?php $pendingRows = $data['pending_rows'] ?? []; ?>
                    <?php if (empty($pendingRows)): ?>
                        <div class="form-label">No meters currently require bill generation.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Meter Serial</th>
                                        <th>Meter Image</th>
                                        <th>Client</th>
                                        <th>Last Reading</th>
                                        <th>Current Reading</th>
                                        <th>Consumption</th>
                                        <th>Estimated Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingRows as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['serial_number']) ?></td>
                                            <td>
                                                <?php if (!empty($row['meter_image'])): ?>
                                                    <img src="<?= htmlspecialchars($row['meter_image']) ?>" alt="meter" style="width:48px;height:48px;border-radius:6px;object-fit:cover;" />
                                                <?php else: ?>
                                                    <span class="text-muted">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($row['client_name']) ?></td>
                                            <td>
                                                <?php if (!empty($row['previous_reading_value'])): ?>
                                                    <?= htmlspecialchars($row['previous_reading_value']) ?> units
                                                    <div class="text-muted" style="font-size:.85rem;">
                                                        <?= !empty($row['previous_reading_date']) ? date('d M Y, H:i', strtotime($row['previous_reading_date'])) : '' ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['latest_reading_value'])): ?>
                                                    <?= htmlspecialchars($row['latest_reading_value']) ?> units
                                                    <div class="text-muted" style="font-size:.85rem;">
                                                        <?= !empty($row['latest_reading_date']) ? date('d M Y, H:i', strtotime($row['latest_reading_date'])) : '' ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= isset($row['consumption']) ? number_format((float)$row['consumption'], 2) : '-' ?> units</td>
                                            <td><?php if (isset($row['estimated_amount'])): ?>KSH <?= number_format((float)$row['estimated_amount'], 2) ?><?php else: ?>-<?php endif; ?></td>
                                            <td class="actions">
                                                <a class="btn btn-outline" href="index.php?page=generate_single_bill_now&meter_id=<?= (int)$row['meter_id'] ?>"><i class="fas fa-bolt"></i> Single</a>
                                                <a class="btn btn-primary" href="index.php?page=generate_bills"><i class="fas fa-cogs"></i> Generate All</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header card-header-secondary">
                    <i class="fas fa-chart-line"></i>
                    <h3 class="card-title">Recent Generation Activity</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_bills)): ?>
                        <div class="form-label">No recent bills.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Bill #</th>
                                        <th>Client</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Bill Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bills as $bill): ?>
                                        <tr>
                                            <td><?= (int)$bill['id'] ?></td>
                                            <td><?= htmlspecialchars($bill['client_name'] ?? (string)$bill['client_id'] ?? '') ?></td>
                                            <td>KSH <?= number_format((float)($bill['amount_due'] ?? 0), 2) ?></td>
                                            <td><?= htmlspecialchars($bill['payment_status'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($bill['bill_date'] ?? '') ?></td>
                                            <td>
                                                <a href="index.php?page=view_bill_details&bill_id=<?= (int)$bill['id'] ?>" class="btn btn-primary"><i class="fas fa-eye"></i> View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header card-header-primary">
                    <i class="fas fa-file-invoice"></i>
                    <h3 class="card-title">Generate Bill for Specific Meter</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="index.php?page=generate_single_bill" id="generateBillForm">
                        <div class="form-group">
                            <label for="meter_id" class="form-label">Select Meter</label>
                            <div class="select-wrapper">
                                <select class="form-control" id="meter_id" name="meter_id" required>
                                    <option value="">Select a meter</option>
                                    <?php $pendingRows = $data['pending_rows'] ?? []; ?>
                                    <?php if (!empty($pendingRows)): ?>
                                        <?php foreach ($pendingRows as $row): ?>
                                            <option value="<?= (int)$row['meter_id'] ?>" <?= $selectedMeterId == $row['meter_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($row['serial_number']) ?> — <?= htmlspecialchars($row['client_name'] ?? '') ?>
                                                <?php if (isset($row['consumption'])): ?> • <?= number_format((float)$row['consumption'], 2) ?> units<?php endif; ?>
                                                <?php if (isset($row['estimated_amount'])): ?> • KSH <?= number_format((float)$row['estimated_amount'], 2) ?><?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <?php foreach ($meters as $meter): ?>
                                            <option value="<?= $meter['id'] ?>" <?= $selectedMeterId == $meter['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($meter['serial_number']) ?> — <?= htmlspecialchars($meter['client_name'] ?? $meter['client_username'] ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <i class="fas fa-chevron-down select-icon"></i>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="auto_generate">
                            <label class="form-check-label" for="auto_generate">Auto-select the last two readings</label>
                        </div>
                        
                        <div id="readingsContainer" class="form-row" style="display: none;">
                            <div class="form-group form-group-half">
                                <label for="reading_id_start" class="form-label">Start Reading</label>
                                <div class="select-wrapper">
                                    <select class="form-control" id="reading_id_start" name="reading_id_start" required disabled>
                                        <option value="">Select start reading</option>
                                    </select>
                                    <i class="fas fa-chevron-down select-icon"></i>
                                </div>
                            </div>
                            
                            <div class="form-group form-group-half">
                                <label for="reading_id_end" class="form-label">End Reading</label>
                                <div class="select-wrapper">
                                    <select class="form-control" id="reading_id_end" name="reading_id_end" required disabled>
                                        <option value="">Select end reading</option>
                                    </select>
                                    <i class="fas fa-chevron-down select-icon"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div id="readingDetails" class="card reading-details-card" style="display: none;">
                            <div class="card-header card-header-secondary">
                                <i class="fas fa-tachometer-alt"></i>
                                <h3 class="card-title">Reading Details</h3>
                            </div>
                            <div class="card-body">
                                <div class="reading-details-grid">
                                    <div class="reading-details-column">
                                        <h4 class="reading-title">Start Reading</h4>
                                        <div class="reading-info">
                                            <div class="reading-label">Value:</div>
                                            <div class="reading-value" id="startReadingValue"><span>-</span></div>
                                        </div>
                                        <div class="reading-info">
                                            <div class="reading-label">Date:</div>
                                            <div class="reading-value" id="startReadingDate"><span>-</span></div>
                                        </div>
                                    </div>
                                    <div class="reading-details-column">
                                        <h4 class="reading-title">End Reading</h4>
                                        <div class="reading-info">
                                            <div class="reading-label">Value:</div>
                                            <div class="reading-value" id="endReadingValue"><span>-</span></div>
                                        </div>
                                        <div class="reading-info">
                                            <div class="reading-label">Date:</div>
                                            <div class="reading-value" id="endReadingDate"><span>-</span></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="consumption-info">
                                    <div class="alert alert-info">
                                        <i class="fas fa-water"></i>
                                        <span class="consumption-label">Consumption:</span>
                                        <span class="consumption-value" id="consumptionValue">-</span> units
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="generate_single_bill" class="btn btn-primary" id="generateButton" disabled>
                                <i class="fas fa-file-invoice-dollar"></i> Generate Bill
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const meterSelect = document.getElementById('meter_id');
        const startReadingSelect = document.getElementById('reading_id_start');
        const endReadingSelect = document.getElementById('reading_id_end');
        const readingsContainer = document.getElementById('readingsContainer');
        const readingDetails = document.getElementById('readingDetails');
        const generateButton = document.getElementById('generateButton');
        const autoGenerate = document.getElementById('auto_generate');
        
        // Store readings data
        let readingsData = [];
        
        // When meter is selected, fetch readings
        meterSelect.addEventListener('change', function() {
            const meterId = this.value;
            
            if (meterId) {
                // Reset selections
                startReadingSelect.innerHTML = '<option value="">Select start reading</option>';
                endReadingSelect.innerHTML = '<option value="">Select end reading</option>';
                startReadingSelect.disabled = true;
                endReadingSelect.disabled = true;
                generateButton.disabled = true;
                readingDetails.style.display = 'none';
                
                // Show readings container
                readingsContainer.style.display = 'flex';
                
                // Fetch readings for this meter
                fetch(`index.php?page=billing_get_readings_for_meter&meter_id=${meterId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }
                        
                        if (data.readings && data.readings.length > 0) {
                            readingsData = data.readings;
                            
                            // Populate start reading dropdown
                            data.readings.forEach(reading => {
                                const option = document.createElement('option');
                                option.value = reading.id;
                                const date = new Date(reading.reading_date);
                                option.textContent = `${reading.reading_value} units - ${date.toLocaleDateString()}`;
                                startReadingSelect.appendChild(option);
                            });
                            
                            startReadingSelect.disabled = false;

                            // Auto-select the last two readings if available
                            if (readingsData.length >= 2) {
                                const last = readingsData[readingsData.length - 1];
                                const prev = readingsData[readingsData.length - 2];
                                startReadingSelect.value = prev.id;

                                // Populate end dropdown with later readings from start
                                endReadingSelect.innerHTML = '<option value="">Select end reading</option>';
                                const startDate = new Date(prev.reading_date);
                                const laterReadings = readingsData.filter(r => new Date(r.reading_date) > startDate);
                                laterReadings.forEach(reading => {
                                    const option = document.createElement('option');
                                    option.value = reading.id;
                                    const d = new Date(reading.reading_date);
                                    option.textContent = `${reading.reading_value} units - ${d.toLocaleDateString()}`;
                                    endReadingSelect.appendChild(option);
                                });
                                endReadingSelect.disabled = false;
                                endReadingSelect.value = last.id;

                                // Show details and enable button
                                updateReadingDetails(prev, last);
                                const consumption = parseFloat(last.reading_value) - parseFloat(prev.reading_value);
                                if (consumption > 0) {
                                    generateButton.disabled = false;
                                    if (autoGenerate && autoGenerate.checked) {
                                        startReadingSelect.value = prev.id;
                                        endReadingSelect.value = last.id;
                                    }
                                } else {
                                    alert('Warning: Consumption is zero or negative.');
                                    generateButton.disabled = true;
                                }
                            }
                        } else {
                            alert('No readings found for this meter.');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching readings:', error);
                        alert('Error fetching readings. Please try again.');
                    });
            } else {
                readingsContainer.style.display = 'none';
                readingDetails.style.display = 'none';
            }
        });
        
        // When start reading is selected, populate end reading dropdown
        startReadingSelect.addEventListener('change', function() {
            const startReadingId = parseInt(this.value);
            
            if (startReadingId) {
                // Reset end reading selection
                endReadingSelect.innerHTML = '<option value="">Select end reading</option>';
                endReadingSelect.disabled = true;
                generateButton.disabled = true;
                readingDetails.style.display = 'none';
                
                // Find the selected reading
                const selectedReading = readingsData.find(r => parseInt(r.id) === startReadingId);
                if (!selectedReading) return;
                
                // Find readings that are newer than the selected start reading
                const startDate = new Date(selectedReading.reading_date);
                const laterReadings = readingsData.filter(r => {
                    const readingDate = new Date(r.reading_date);
                    return readingDate > startDate;
                });
                
                if (laterReadings.length > 0) {
                    // Populate end reading dropdown
                    laterReadings.forEach(reading => {
                        const option = document.createElement('option');
                        option.value = reading.id;
                        const date = new Date(reading.reading_date);
                        option.textContent = `${reading.reading_value} units - ${date.toLocaleDateString()}`;
                        endReadingSelect.appendChild(option);
                    });
                    
                    endReadingSelect.disabled = false;
                } else {
                    alert('No later readings found. Cannot generate bill.');
                }
            }
        });
        
        // When end reading is selected, show reading details and enable generate button
        endReadingSelect.addEventListener('change', function() {
            const endReadingId = parseInt(this.value);
            
            if (endReadingId) {
                const startReadingId = parseInt(startReadingSelect.value);
                
                // Find the selected readings
                const startReading = readingsData.find(r => parseInt(r.id) === startReadingId);
                const endReading = readingsData.find(r => parseInt(r.id) === endReadingId);
                
                if (startReading && endReading) {
                    // Calculate consumption
                    const startValue = parseFloat(startReading.reading_value);
                    const endValue = parseFloat(endReading.reading_value);
                    const consumption = endValue - startValue;
                    
                    // Update reading details
                    document.querySelector('#startReadingValue span').textContent = `${startValue} units`;
                    document.querySelector('#startReadingDate span').textContent = new Date(startReading.reading_date).toLocaleDateString();
                    document.querySelector('#endReadingValue span').textContent = `${endValue} units`;
                    document.querySelector('#endReadingDate span').textContent = new Date(endReading.reading_date).toLocaleDateString();
                    document.querySelector('#consumptionValue').textContent = consumption.toFixed(2);
                    
                    // Show reading details
                    readingDetails.style.display = 'block';
                    
                    // Enable generate button if consumption is positive
                    if (consumption > 0) {
                        generateButton.disabled = false;
                    } else {
                        alert('Warning: Consumption is zero or negative. Please check your readings.');
                        generateButton.disabled = true;
                    }
                }
            } else {
                readingDetails.style.display = 'none';
                generateButton.disabled = true;
            }
        });
        
        // Auto-dismiss alerts after 5 seconds (vanilla)
        setTimeout(function() {
            document.querySelectorAll('.alert-success, .alert-danger').forEach(function(alert) {
                alert.classList.add('fade-out');
                setTimeout(function(){ alert.remove(); }, 400);
            });
        }, 5000);
        
        // If meter_id is pre-selected from URL, trigger
        if (meterSelect.value) {
            meterSelect.dispatchEvent(new Event('change'));
        } else if (meterSelect && meterSelect.options.length > 1) {
            meterSelect.selectedIndex = 1;
            meterSelect.dispatchEvent(new Event('change'));
        }
        
        function updateReadingDetails(startReading, endReading) {
            const startValue = parseFloat(startReading.reading_value);
            const endValue = parseFloat(endReading.reading_value);
            const consumption = endValue - startValue;

            document.querySelector('#startReadingValue span').textContent = `${startValue} units`;
            document.querySelector('#startReadingDate span').textContent = new Date(startReading.reading_date).toLocaleDateString();
            document.querySelector('#endReadingValue span').textContent = `${endValue} units`;
            document.querySelector('#endReadingDate span').textContent = new Date(endReading.reading_date).toLocaleDateString();
            document.querySelector('#consumptionValue').textContent = consumption.toFixed(2);

            readingDetails.style.display = 'block';
        }
    });
    </script>
</body>
</html>
