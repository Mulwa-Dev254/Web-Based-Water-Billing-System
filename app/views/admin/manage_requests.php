<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Service Requests</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff4757;
            --primary-dark: #e84118;
            --dark-bg: #1e1e2d;
            --darker-bg: #151521;
            --sidebar-bg: #1a1a27;
            --card-bg: #2a2a3c;
            --text-light: #f8f9fa;
            --text-muted: #a1a5b7;
            --border-color: #2d2d3a;
            --success: #1dd1a1;
            --info: #2e86de;
            --warning: #ff9f43;
            --danger: #ee5253;
            --purple: #5f27cd;
        }

        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Dashboard Layout */
        .dashboard-layout {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background-color: var(--sidebar-bg);
            padding: 1.5rem 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid var(--border-color);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar.hidden {
            transform: translateX(-280px);
        }

        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
        }

        .sidebar-header h3 {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-header h3 i {
            font-size: 1.75rem;
        }

        .sidebar-nav {
            flex-grow: 1;
            overflow-y: auto;
            padding: 0 1rem;
        }

        .sidebar-nav ul {
            list-style: none;
        }

        .sidebar-nav li {
            margin-bottom: 0.5rem;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            color: var(--text-muted);
            transition: all 0.3s ease;
        }

        .sidebar-nav a:hover {
            background-color: rgba(255, 71, 87, 0.1);
            color: var(--text-light);
        }

        .sidebar-nav a.active {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 71, 87, 0.3);
        }

        .sidebar-nav a i {
            width: 1.5rem;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Main Content Area */
        .main-content {
            margin-left: 280px;
            flex-grow: 1;
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content.full-width {
            margin-left: 0;
        }

        /* Header Bar */
        .header-bar {
            background-color: var(--sidebar-bg);
            padding: 1.25rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-title h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-light);
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-greeting {
            font-weight: 500;
            color: var(--text-light);
        }

        .user-greeting span {
            color: var(--primary);
            font-weight: 600;
        }

        .logout-btn {
            background-color: var(--primary);
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 71, 87, 0.3);
        }

        /* Dashboard Content */
        .dashboard-container {
            padding: 2rem;
        }

        /* Summary Cards */
        .dashboard-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background-color: var(--card-bg);
            padding: 1.5rem;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .summary-card.total::before { background-color: var(--primary); }
        .summary-card.pending::before { background-color: var(--warning); }
        .summary-card.assigned::before { background-color: var(--info); }
        .summary-card.completed::before { background-color: var(--success); }

        .summary-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .summary-card.total .summary-card-icon {
            background-color: rgba(255, 71, 87, 0.1);
            color: var(--primary);
        }

        .summary-card.pending .summary-card-icon {
            background-color: rgba(255, 159, 67, 0.1);
            color: var(--warning);
        }

        .summary-card.assigned .summary-card-icon {
            background-color: rgba(46, 134, 222, 0.1);
            color: var(--info);
        }

        .summary-card.completed .summary-card-icon {
            background-color: rgba(29, 209, 161, 0.1);
            color: var(--success);
        }

        .summary-card h4 {
            color: var(--text-muted);
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .summary-card p {
            font-size: 2.25rem;
            font-weight: 700;
            margin: 0;
        }

        .summary-card.total p { color: var(--primary); }
        .summary-card.pending p { color: var(--warning); }
        .summary-card.assigned p { color: var(--info); }
        .summary-card.completed p { color: var(--success); }

        /* Content Section */
        .content-section {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .section-title {
            color: var(--primary);
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }

        /* Filter Options */
        .filter-options {
            background-color: var(--card-bg);
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }

        .filter-options .form-group {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-options label {
            font-weight: 500;
            margin-right: 0.5rem;
            white-space: nowrap;
        }

        .filter-options select,
        .filter-options input[type="date"] {
            background-color: var(--dark-bg);
            border: 1px solid var(--border-color);
            color: var(--text-light);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            min-width: 150px;
        }

        .filter-options .btn-primary {
            background-color: var(--primary);
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            color: white;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-options .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: var(--darker-bg);
            color: var(--text-light);
            font-weight: 600;
        }

        tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: rgba(255, 159, 67, 0.1);
            color: var(--warning);
        }

        .status-assigned {
            background-color: rgba(46, 134, 222, 0.1);
            color: var(--info);
        }

        .status-completed {
            background-color: rgba(29, 209, 161, 0.1);
            color: var(--success);
        }

        .status-cancelled {
            background-color: rgba(238, 82, 83, 0.1);
            color: var(--danger);
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 0.5rem;
            background-color: var(--dark-bg);
            color: var(--text-muted);
            margin-right: 0.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }

        .action-btn:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 71, 87, 0.2);
        }

        .action-btn.view:hover {
            background-color: var(--info);
        }

        .action-btn.edit:hover {
            background-color: var(--warning);
        }

        .action-btn.delete:hover {
            background-color: var(--danger);
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-280px);
            }
            
            .sidebar.visible {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar-toggle {
                display: flex;
            }
        }

        @media (max-width: 768px) {
            .dashboard-summary {
                grid-template-columns: 1fr 1fr;
            }
            
            .header-bar {
                padding: 1rem;
            }
            
            .dashboard-container {
                padding: 1rem;
            }
            
            .content-section {
                padding: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .dashboard-summary {
                grid-template-columns: 1fr;
            }
            
            .user-info {
                gap: 1rem;
            }
            
            .user-greeting {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar Navigation -->
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
                    <li><a href="index.php?page=admin_manage_client_plans"><i class="fas fa-layer-group"></i> Client Plans</a></li>
                    <li><a href="index.php?page=admin_manage_requests" class="active"><i class="fas fa-clipboard-list"></i> Service Requests</a></li>
                    <li><a href="index.php?page=admin_manage_meters"><i class="fas fa-tachometer-alt"></i> Manage Meters</a></li>
                    <li><a href="index.php?page=generate_bills"><i class="fas fa-file-invoice-dollar"></i> Generate Bills</a></li>
                    <li><a href="index.php?page=view_bills"><i class="fas fa-list"></i> View Bills</a></li>
                    <li><a href="index.php?page=admin_transactions"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
                    <li><a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content Area -->
        <div class="main-content" id="mainContent">
            <!-- Header Bar -->
            <div class="header-bar">
                <div class="header-title">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Manage Service Requests</h1>
                </div>
                <div class="user-info">
                    <div class="user-greeting">Welcome back, <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span></div>
                    <a href="index.php?page=logout" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-container">
                <?php if (!empty($data['error'])): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($data['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($data['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($data['success']); ?>
                    </div>
                <?php endif; ?>

                <!-- Summary Cards -->
                <div class="dashboard-summary">
                    <!-- Total Requests Card -->
                    <div class="summary-card total">
                        <div class="summary-card-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h4>Total Requests</h4>
                        <p><?php echo count($data['serviceRequests']); ?></p>
                    </div>

                    <!-- Pending Requests Card -->
                    <div class="summary-card pending">
                        <div class="summary-card-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4>Pending Requests</h4>
                        <p>
                                        <?php 
                                        $pendingCount = 0;
                                        foreach ($data['serviceRequests'] as $request) {
                                            if ($request['status'] === 'pending') {
                                                $pendingCount++;
                                            }
                                        }
                                        echo $pendingCount;
                                        ?>
                                    </p>
                                </div>

                                <!-- Assigned Requests Card -->
                                <div class="summary-card assigned">
                                    <div class="summary-card-icon">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <h4>Assigned Requests</h4>
                                    <p>
                                        <?php 
                                        $assignedCount = 0;
                                        foreach ($data['serviceRequests'] as $request) {
                                            if ($request['status'] === 'assigned') {
                                                $assignedCount++;
                                            }
                                        }
                                        echo $assignedCount;
                                        ?>
                                    </p>
                                </div>

                                <!-- Completed Requests Card -->
                                <div class="summary-card completed">
                                    <div class="summary-card-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <h4>Completed Requests</h4>
                                    <p>
                                        <?php 
                                        $completedCount = 0;
                                        foreach ($data['serviceRequests'] as $request) {
                                            if ($request['status'] === 'completed') {
                                                $completedCount++;
                                            }
                                        }
                                        echo $completedCount;
                                        ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Filter Options -->
                            <div class="filter-options">
                                <div class="section-header">
                                    <h3 class="section-title"><i class="fas fa-filter"></i> Filter Options</h3>
                                </div>
                                <form action="index.php" method="GET" class="form-group">
                                    <input type="hidden" name="page" value="admin_manage_requests">
                                    
                                    <!-- Status Filter -->
                                    <div>
                                        <label for="status_filter">Status:</label>
                                        <select name="status" id="status_filter">
                                            <option value="">All Statuses</option>
                                            <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="assigned" <?php echo isset($_GET['status']) && $_GET['status'] === 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                                            <option value="serviced" <?php echo isset($_GET['status']) && $_GET['status'] === 'serviced' ? 'selected' : ''; ?>>Serviced</option>
                                            <option value="completed" <?php echo isset($_GET['status']) && $_GET['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo isset($_GET['status']) && $_GET['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Date Range Filter -->
                                    <div>
                                        <label for="date_from">Date From:</label>
                                        <input type="date" name="date_from" id="date_from" value="<?php echo $_GET['date_from'] ?? ''; ?>">
                                    </div>
                                    
                                    <div>
                                        <label for="date_to">Date To:</label>
                                        <input type="date" name="date_to" id="date_to" value="<?php echo $_GET['date_to'] ?? ''; ?>">
                                    </div>
                                    
                                    <!-- Filter Button -->
                                    <div>
                                        <button type="submit" class="btn-primary">Apply Filters</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Service Requests Table -->
                            <div class="content-section">
                                <div class="section-header">
                                    <h3 class="section-title"><i class="fas fa-clipboard-list"></i> Service Requests</h3>
                                    <div>
                                        <button class="action-btn" id="refreshTable" title="Refresh">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <button class="action-btn" id="exportCSV" title="Export to CSV">
                                            <i class="fas fa-file-csv"></i>
                                        </button>
                                        <button class="action-btn" id="printTable" title="Print">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th class="text-center">ID</th>
                                                <th>Client</th>
                                                <th>Service</th>
                                                <th>Description</th>
                                                <th>Request Date</th>
                                                <th>Status</th>
                                                <th>Assigned To</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                <?php if (!empty($data['serviceRequests'])): ?>
                                    <?php foreach ($data['serviceRequests'] as $request): ?>
                                        <tr>
                                            <td class="text-center"><?php echo htmlspecialchars($request['id']); ?></td>
                                            <td><?php echo htmlspecialchars($request['client_username']); ?></td>
                                            <td><?php echo htmlspecialchars($request['service_name']); ?></td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($request['description']); ?>">
                                                    <?php echo htmlspecialchars($request['description']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($request['request_date']))); ?></td>
                                            <td>
                                                <?php 
                                                $statusClass = '';
                                                switch($request['status']) {
                                                    case 'pending':
                                                        $statusClass = 'status-pending';
                                                        break;
                                                    case 'assigned':
                                                        $statusClass = 'status-assigned';
                                                        break;
                                                    case 'serviced':
                                                        $statusClass = 'status-assigned';
                                                        break;
                                                    case 'completed':
                                                        $statusClass = 'status-completed';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'status-cancelled';
                                                        break;
                                                    default:
                                                        $statusClass = 'status-pending';
                                                }
                                                ?>
                                                <span class="status-badge <?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars(ucfirst($request['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($request['assigned_collector_username'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if ($request['status'] === 'pending'): ?>
                                                    <form action="index.php?page=admin_manage_requests" method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                                        <input type="hidden" name="action" value="assign_request">
                                                        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                                                        <select name="assignee_id" required style="background-color: var(--dark-bg); border: 1px solid var(--border-color); color: var(--text-light); padding: 0.25rem 0.5rem; border-radius: 0.25rem; min-width: 180px;">
                                                            <option value="">Select Staff (Meter Reader or Collector)</option>
                                                            <?php foreach ($data['staff'] as $user): ?>
                                                                <option value="<?php echo htmlspecialchars($user['id']); ?>">
                                                                    <?php echo htmlspecialchars($user['username'] . (isset($user['role']) ? ' (' . $user['role'] . ')' : '')); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" class="action-btn"><i class="fas fa-user-plus"></i></button>
                                                    </form>
                                                <?php elseif ($request['status'] === 'assigned'): ?>
                                                    <form action="index.php?page=admin_manage_requests" method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                                                        <select name="new_status" required style="background-color: var(--dark-bg); border: 1px solid var(--border-color); color: var(--text-light); padding: 0.25rem 0.5rem; border-radius: 0.25rem; min-width: 140px;">
                                                            <option value="serviced">Serviced</option>
                                                            <option value="completed">Completed</option>
                                                            <option value="cancelled">Cancelled</option>
                                                        </select>
                                                        <button type="submit" class="action-btn"><i class="fas fa-sync-alt"></i></button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="status-badge status-cancelled">No actions available</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div style="padding: 1rem; color: var(--info); background-color: rgba(46, 134, 222, 0.1); border-radius: 0.5rem; margin: 1rem 0;">
                                                <i class="fas fa-info-circle"></i> No service requests found.
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- JavaScript for Table Functionality -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize tooltips if Bootstrap is available
                if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })
                }
                
                // Sidebar toggle functionality
                const sidebarToggle = document.getElementById('sidebarToggle');
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');
                
                if (sidebarToggle) {
                    sidebarToggle.addEventListener('click', function() {
                        sidebar.classList.toggle('hidden');
                        mainContent.classList.toggle('full-width');
                    });
                }
                
                // Refresh button functionality
                document.getElementById('refreshTable').addEventListener('click', function() {
                    window.location.reload();
                });
                
                // Print table functionality
                document.getElementById('printTable').addEventListener('click', function(e) {
                    e.preventDefault();
                    window.print();
                });
                
                // Export to CSV functionality
                document.getElementById('exportCSV').addEventListener('click', function(e) {
                    e.preventDefault();
                    exportTableToCSV('service_requests.csv');
                });
                
                function exportTableToCSV(filename) {
                    var csv = [];
                    var rows = document.querySelectorAll('#dataTable tr');
                    
                    for (var i = 0; i < rows.length; i++) {
                        var row = [], cols = rows[i].querySelectorAll('td, th');
                        
                        for (var j = 0; j < cols.length; j++) {
                            // Get the text content and clean it
                            var text = cols[j].innerText.replace(/\r?\n/g, ' ').trim();
                            // Remove any commas to avoid CSV issues
                            text = text.replace(/,/g, ' ');
                            row.push('"' + text + '"');
                        }
                        
                        csv.push(row.join(','));
                    }
                    
                    // Download CSV file
                    downloadCSV(csv.join('\n'), filename);
                }
                
                function downloadCSV(csv, filename) {
                    var csvFile;
                    var downloadLink;
                    
                    // Create CSV file
                    csvFile = new Blob([csv], {type: 'text/csv'});
                    
                    // Create download link
                    downloadLink = document.createElement('a');
                    
                    // Set file name
                    downloadLink.download = filename;
                    
                    // Create link to file
                    downloadLink.href = window.URL.createObjectURL(csvFile);
                    
                    // Hide download link
                    downloadLink.style.display = 'none';
                    
                    // Add link to DOM
                    document.body.appendChild(downloadLink);
                    
                    // Click download link
                    downloadLink.click();
                    
                    // Remove link from DOM
                    document.body.removeChild(downloadLink);
                }
            });
        </script>
    </body>
</html>
