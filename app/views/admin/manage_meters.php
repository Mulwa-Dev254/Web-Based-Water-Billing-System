<?php
// app/views/admin/manage_meters.php
// Admin Meter Management Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Meters</title>
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
            /* allow internal containers to manage horizontal scrolling */
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
            /* ensure content fits the viewport minus sidebar */
            width: calc(100vw - 280px);
            max-width: calc(100vw - 280px);
            box-sizing: border-box;
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

        /* Toggle Button */
        .sidebar-toggle {
            background-color: var(--primary);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            display: none;
        }

        .sidebar-toggle:hover {
            background-color: var(--primary-dark);
            transform: scale(1.05);
        }

        /* Dashboard Content */
        .dashboard-container {
            padding: 2rem;
        }

        /* Content Sections */
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
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .section-title {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            font-size: 1.25rem;
        }

        /* Summary Cards */
        .dashboard-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
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
            background-color: var(--primary);
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .summary-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            color: white;
        }

        .summary-card h4 {
            color: var(--text-muted);
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .summary-card p {
            font-size: 2.25rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-light);
        }

        /* Table Styles */
        .table-container {
            background-color: var(--card-bg);
            border-radius: 0.75rem;
            /* enable horizontal scroll inside card for wide tables */
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
            max-width: 100%;
        }

        .table-header {
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }

        .table-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .table-filters {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            gap: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            /* ensure table can be wider than container to trigger scroll */
            min-width: 800px;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }

        th {
            font-weight: 600;
            color: var(--text-muted);
            background-color: rgba(0, 0, 0, 0.1);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.875rem;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 71, 87, 0.3);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: #1bc29c;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(29, 209, 161, 0.3);
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background-color: #ff8f1f;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 159, 67, 0.3);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #ec3d3e;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(238, 82, 83, 0.3);
        }

        .btn-info {
            background-color: var(--info);
            color: white;
        }

        .btn-info:hover {
            background-color: #1c7ed6;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(46, 134, 222, 0.3);
        }

        .btn-purple {
            background-color: var(--purple);
            color: white;
        }

        .btn-purple:hover {
            background-color: #5423b8;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(95, 39, 205, 0.3);
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
        }

        /* Badge Styles */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: rgba(29, 209, 161, 0.2);
            color: var(--success);
        }

        .badge-warning {
            background-color: rgba(255, 159, 67, 0.2);
            color: var(--warning);
        }

        .badge-danger {
            background-color: rgba(238, 82, 83, 0.2);
            color: var(--danger);
        }

        .badge-info {
            background-color: rgba(46, 134, 222, 0.2);
            color: var(--info);
        }

        .badge-purple {
            background-color: rgba(95, 39, 205, 0.2);
            color: var(--purple);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-light);
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            background-color: rgba(0, 0, 0, 0.2);
            color: var(--text-light);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 71, 87, 0.3);
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background-color: rgba(29, 209, 161, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }

        .alert-error {
            background-color: rgba(238, 82, 83, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: var(--card-bg);
            border-radius: 0.75rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            position: relative;
            border: 1px solid var(--border-color);
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.3s ease;
        }

        .modal-close:hover {
            color: var(--text-light);
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-title i {
            font-size: 1.1rem;
        }

        /* Image Preview */
        .meter-image-preview {
            max-width: 100px;
            max-height: 100px;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .meter-image-preview:hover {
            transform: scale(1.05);
        }

        .image-modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.9);
        }

        .image-modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            max-height: 80%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .image-modal-close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
        }

        .image-modal-close:hover,
        .image-modal-close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }

        /* Coming Soon Badge */
        .coming-soon {
            background-color: rgba(255, 71, 87, 0.1);
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            margin-left: auto;
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
                width: 100vw;
                max-width: 100vw;
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
            
            .btn-group {
                flex-direction: column;
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
            
            .table-filters {
                flex-direction: column;
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
                    <li><a href="index.php?page=admin_manage_requests"><i class="fas fa-clipboard-list"></i> Service Requests</a></li>
                    <li><a href="index.php?page=admin_manage_client_plans"><i class="fas fa-layer-group"></i> Client Plans</a></li>
                    <li><a href="index.php?page=admin_manage_meters" class="active"><i class="fas fa-tachometer-alt"></i> Manage Meters</a></li>
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
                    <h1>Manage Meters</h1>
                </div>
                <div class="user-info">
                    <div class="user-greeting">Welcome back, <span><?php echo htmlspecialchars($_SESSION['username']); ?></span></div>
                    <a href="index.php?page=logout" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-container">
                <!-- Error/Success Messages -->
                <?php if (!empty($data['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($data['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($data['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($data['success']); ?>
                    </div>
                <?php endif; ?>

                <!-- Meter Statistics Cards -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-chart-pie"></i> Meter Statistics</h2>
                    </div>
                    <div class="dashboard-summary">
                        <div class="summary-card">
                            <div class="summary-card-icon" style="background-color: rgba(29, 209, 161, 0.1); color: var(--success);">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <h4>Total Meters</h4>
                            <p><?php echo $totalMeters; ?></p>
                        </div>
                        <div class="summary-card">
                            <div class="summary-card-icon" style="background-color: rgba(46, 134, 222, 0.1); color: var(--info);">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h4>Available Meters</h4>
                            <p><?php echo $availableMeters; ?></p>
                        </div>
                        <div class="summary-card">
                            <div class="summary-card-icon" style="background-color: rgba(95, 39, 205, 0.1); color: var(--purple);">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <h4>Assigned Meters</h4>
                            <p><?php echo $assignedMeters; ?></p>
                        </div>
                        <div class="summary-card">
                            <div class="summary-card-icon" style="background-color: rgba(255, 159, 67, 0.1); color: var(--warning);">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h4>Pending Verifications</h4>
                            <p><?php echo $pendingVerifications; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Applications Awaiting Admin Verification -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-clipboard-check"></i> Applications Awaiting Verification</h2>
                    </div>
                    <?php if (!empty($data['submittedApplications'])): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Meter Serial</th>
                                        <th>Submitted Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['submittedApplications'] as $app): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($app['id']); ?></td>
                                            <td><?php echo htmlspecialchars($app['client_name'] ?? ('User #'.$app['client_id'])); ?></td>
                                            <td><?php echo htmlspecialchars($app['meter_serial'] ?? ('#'.$app['meter_id'])); ?></td>
                                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($app['admin_approval_date'] ?? $app['application_date']))); ?></td>
                                            <td>
                                                <form action="index.php?page=admin_manage_meters" method="POST" style="display:inline-block; margin-right:6px;">
                                                    <input type="hidden" name="action" value="verify_application">
                                                    <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($app['id']); ?>">
                                                    <input type="hidden" name="decision" value="approve">
                                                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Approve</button>
                                                </form>
                                                <form action="index.php?page=admin_manage_meters" method="POST" style="display:inline-block;">
                                                    <input type="hidden" name="action" value="verify_application">
                                                    <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($app['id']); ?>">
                                                    <input type="hidden" name="decision" value="reject">
                                                    <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Reject</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info"><i class="fas fa-info-circle"></i> No applications waiting for verification.</div>
                    <?php endif; ?>
                </div>

                <!-- Admin-Verified Applications: Assign Installer -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-user-hard-hat"></i> Assign Installer for Verified Applications</h2>
                    </div>
                    <?php if (!empty($data['verifiedApplications'])): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>App ID</th>
                                    <th>Client</th>
                                    <th>Meter Serial</th>
                                    <th>Verified Date</th>
                                    <th>Assign To</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($data['verifiedApplications'] as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['client_name'] ?? ('User #'.$row['client_id'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['meter_serial'] ?? ('#'.$row['meter_id'])); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['admin_approval_date'] ?? date('Y-m-d')))); ?></td>
                                    <td>
                                        <form action="index.php?page=admin_manage_meters" method="POST" style="display:flex; gap:8px; align-items:center;">
                                            <input type="hidden" name="action" value="assign_installer">
                                            <input type="hidden" name="meter_id" value="<?php echo htmlspecialchars($row['meter_id']); ?>">
                                            <select name="installer_user_id" class="form-select" required>
                                                <option value="">Select Installer (Reader/Collector)</option>
                                                <?php foreach ($data['installers'] as $u): ?>
                                                    <option value="<?php echo htmlspecialchars($u['id']); ?>"><?php echo htmlspecialchars(($u['username'] ?? 'user').' - '.($u['role'] ?? '')); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-success"><i class="fas fa-user-check"></i> Assign</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info"><i class="fas fa-info-circle"></i> No verified applications awaiting installer assignment.</div>
                    <?php endif; ?>
                </div>

                <!-- Waiting Installation - Edit -->
                <?php if (!empty($data['waitingInstallations'])): ?>
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-tools"></i> Installation for Verified Applications</h2>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Meter Serial</th>
                                    <th>Client</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($data['waitingInstallations'] as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['meter_serial'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['client_username'] ?? ($row['client_name'] ?? '')); ?></td>
                                    <td><span class="badge badge-warning">Waiting installation</span></td>
                                    <td>
                                        <form action="index.php?page=admin_manage_meters" method="POST" style="display:flex; gap:8px; align-items:center;">
                                            <input type="hidden" name="action" value="update_installation_status">
                                            <input type="hidden" name="meter_id" value="<?php echo htmlspecialchars($row['meter_id']); ?>">
                                            <select name="new_status" class="form-select" required>
                                                <option value="waiting_installation">Waiting installation</option>
                                                <option value="assigned">Assigned</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                            <button type="submit" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Installation Submissions (from installers) -->
                <?php if (!empty($data['installationSubmissions']) || !empty($data['waitingInstallations']) || !empty($data['verifiedApplications'])): ?>
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-clipboard-list"></i> Installation Submissions</h2>
                    </div>
                    <?php if (!empty($data['waitingInstallations'])): ?>
                        <div class="alert alert-info"><i class="fas fa-info-circle"></i> Submissions may arrive as meters move from assignment to installation.</div>
                    <?php endif; ?>
                    <?php if (!empty($data['installationSubmissions'])): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Serial</th>
                                        <th>Client</th>
                                        <th>Initial Reading</th>
                                        <th>GPS</th>
                                        <th>Photo</th>
                                        <th>Notes</th>
                                        <th>Submitted</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['installationSubmissions'] as $sub): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sub['serial_number'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($sub['client_username'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($sub['initial_reading'] ?? '0'); ?></td>
                                        <td><?php echo htmlspecialchars($sub['gps_location'] ?? ''); ?></td>
                                        <td>
                                            <?php if (!empty($sub['photo_url'])): ?>
                                                <a href="<?php echo htmlspecialchars($sub['photo_url']); ?>" target="_blank" title="View photo">
                                                    <img src="<?php echo htmlspecialchars($sub['photo_url']); ?>" alt="Photo" style="height:40px;border-radius:4px;object-fit:cover;border:1px solid #444;" />
                                                </a>
                                            <?php else: ?>
                                                <span class="badge badge-muted">No photo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($sub['notes'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($sub['submitted_at'] ?? ''); ?></td>
                                        <td>
                                            <form action="index.php?page=admin_manage_meters" method="POST" style="display:inline-block; margin-right:6px;">
                                                <input type="hidden" name="action" value="review_installation_submission">
                                                <input type="hidden" name="submission_id" value="<?php echo htmlspecialchars($sub['id']); ?>">
                                                <input type="hidden" name="decision" value="approve">
                                                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Approve</button>
                                            </form>
                                            <button type="button" class="btn btn-danger" onclick="toggleRejectForm(<?php echo (int)$sub['id']; ?>)"><i class="fas fa-times"></i> Reject</button>
                                            <div id="reject-form-<?php echo (int)$sub['id']; ?>" class="reject-form" style="display:none; margin-top:8px;">
                                                <input type="text" name="reason_input_<?php echo (int)$sub['id']; ?>" id="reason_input_<?php echo (int)$sub['id']; ?>" placeholder="Reason for rejection" class="form-input" style="width:240px; margin-right:6px;" />
                                                <form action="index.php?page=admin_manage_meters" method="POST" style="display:inline-block; margin-right:6px;">
                                                    <input type="hidden" name="action" value="review_installation_submission">
                                                    <input type="hidden" name="submission_id" value="<?php echo htmlspecialchars($sub['id']); ?>">
                                                    <input type="hidden" name="decision" value="reject">
                                                    <input type="hidden" name="notes" id="reject_notes_<?php echo (int)$sub['id']; ?>">
                                                    <button type="submit" class="btn btn-danger" onclick="syncRejectNotes(<?php echo (int)$sub['id']; ?>)"><i class="fas fa-ban"></i> Confirm Reject</button>
                                                </form>
                                                <form action="index.php?page=admin_manage_meters" method="POST" style="display:inline-block;">
                                                    <input type="hidden" name="action" value="notify_installation_rejection">
                                                    <input type="hidden" name="submission_id" value="<?php echo htmlspecialchars($sub['id']); ?>">
                                                    <input type="hidden" name="reason" id="message_reason_<?php echo (int)$sub['id']; ?>">
                                                    <button type="submit" class="btn btn-secondary" onclick="syncMessageReason(<?php echo (int)$sub['id']; ?>)"><i class="fas fa-paper-plane"></i> Send Message</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info"><i class="fas fa-info-circle"></i> No installation submissions yet.</div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Meters Table -->
                <div class="content-section">
                    <div class="table-container">
                        <div class="table-header">
                            <h2>All Meters</h2>
                            <button class="btn btn-primary" onclick="openAddMeterModal()">
                                <i class="fas fa-plus"></i> Add New Meter
                            </button>
                        </div>
                        <div class="table-filters">
                            <button class="btn btn-primary" onclick="filterMeters('all')">All</button>
                            <button class="btn btn-success" onclick="filterMeters('available')">Available</button>
                            <button class="btn btn-warning" onclick="filterMeters('assigned')">Assigned</button>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Serial Number</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Client</th>
                                    <th>Installation Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="metersTableBody">
                                <?php foreach ($data['meters'] as $meter): ?>
                                    <tr data-status="<?php echo htmlspecialchars($meter['status']); ?>">
                                        <td><?php echo htmlspecialchars($meter['id']); ?></td>
                                        <td>
                                            <?php if (!empty($meter['photo_url'])): ?>
                                                <img src="<?php echo htmlspecialchars($meter['photo_url']); ?>" alt="Meter Image" class="meter-image-preview" onclick="openImageModal('<?php echo htmlspecialchars($meter['photo_url']); ?>')">
                                            <?php else: ?>
                                                <span style="color: var(--text-muted);"><i class="fas fa-camera-slash"></i></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($meter['serial_number']); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($meter['meter_type'] ?? 'N/A')); ?></td>
                                        <td>
                                            <span class="badge <?php 
                                                if ($meter['status'] == 'available' || $meter['status'] == 'in_stock') echo 'badge-success';
                                                elseif ($meter['status'] == 'assigned_to_collector') echo 'badge-warning';
                                                elseif ($meter['status'] == 'installed') echo 'badge-info';
                                                else echo 'badge-danger';
                                            ?>">
                                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $meter['status']))); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($meter['client_username'])): ?>
                                                <a href="index.php?page=admin_view_client&id=<?php echo htmlspecialchars($meter['client_id']); ?>" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($meter['client_username']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted);">Not Assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo !empty($meter['installation_date']) ? htmlspecialchars($meter['installation_date']) : '<span style="color: var(--text-muted);">Not Installed</span>'; ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-info" onclick="openViewMeterModal(<?php echo htmlspecialchars(json_encode($meter)); ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning" onclick="openEditMeterModal(<?php echo htmlspecialchars(json_encode($meter)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($meter['status'] == 'available' || $meter['status'] == 'in_stock'): ?>
                                                    <button class="btn btn-success" onclick="openAssignMeterModal(<?php echo htmlspecialchars($meter['id']); ?>)">
                                                        <i class="fas fa-user-plus"></i>
                                                    </button>
                                                    <form action="index.php?page=admin_manage_meters" method="POST" style="display:inline-block;">
                                                        <input type="hidden" name="action" value="flag_meter">
                                                        <input type="hidden" name="meter_id" value="<?php echo htmlspecialchars($meter['id']); ?>">
                                                        <button type="submit" class="btn btn-danger"><i class="fas fa-flag"></i></button>
                                                    </form>
                                                    <form action="index.php?page=admin_manage_meters" method="POST" style="display:inline-block;">
                                                        <input type="hidden" name="action" value="delete_meter">
                                                        <input type="hidden" name="meter_id" value="<?php echo htmlspecialchars($meter['id']); ?>">
                                                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i></button>
                                                    </form>
                                                <?php elseif ($meter['status'] == 'installed'): ?>
                                                    <button class="btn btn-purple" onclick="openVerifyMeterModal(<?php echo htmlspecialchars($meter['id']); ?>)">
                                                        <i class="fas fa-check-double"></i>
                                                    </button>
                                                <?php elseif ($meter['status'] == 'assigned' || !empty($meter['client_id'])): ?>
                                                    <form action="index.php?page=admin_manage_meters" method="POST" style="display:inline-block;">
                                                        <input type="hidden" name="action" value="flag_meter">
                                                        <input type="hidden" name="meter_id" value="<?php echo htmlspecialchars($meter['id']); ?>">
                                                        <button type="submit" class="btn btn-danger"><i class="fas fa-flag"></i></button>
                                                    </form>
                                                <?php elseif ($meter['status'] == 'flagged'): ?>
                                                    <form action="index.php?page=admin_manage_meters" method="POST" style="display:inline-block;">
                                                        <input type="hidden" name="action" value="unflag_meter">
                                                        <input type="hidden" name="meter_id" value="<?php echo htmlspecialchars($meter['id']); ?>">
                                                        <button type="submit" class="btn btn-success"><i class="fas fa-flag-checkered"></i></button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Meter Modal -->
    <div id="addMeterModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeAddMeterModal()">&times;</span>
            <h2 class="modal-title"><i class="fas fa-plus-circle"></i> Add New Meter</h2>
            <form action="index.php?page=admin_manage_meters" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_meter">
                <div class="form-group">
                    <label class="form-label" for="serial_number">Serial Number:</label>
                    <input type="text" id="serial_number" name="serial_number" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="meter_type">Meter Type:</label>
                    <select id="meter_type" name="meter_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="residential">Residential</option>
                        <option value="commercial">Commercial</option>
                        <option value="industrial">Industrial</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="initial_reading">Initial Reading:</label>
                    <input type="number" id="initial_reading" name="initial_reading" class="form-input" step="0.01" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="meter_image">Meter Image:</label>
                    <input type="file" id="meter_image" name="meter_image" class="form-input" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-plus-circle"></i> Add Meter
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Meter Modal -->
    <div id="editMeterModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeEditMeterModal()">&times;</span>
            <h2 class="modal-title"><i class="fas fa-edit"></i> Edit Meter</h2>
            <form action="index.php?page=admin_manage_meters" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_meter">
                <input type="hidden" id="edit_meter_id" name="meter_id">
                <div class="form-group">
                    <label class="form-label" for="edit_serial_number">Serial Number:</label>
                    <input type="text" id="edit_serial_number" name="serial_number" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_meter_type">Meter Type:</label>
                    <select id="edit_meter_type" name="meter_type" class="form-select" required>
                        <option value="residential">Residential</option>
                        <option value="commercial">Commercial</option>
                        <option value="industrial">Industrial</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_initial_reading">Initial Reading:</label>
                    <input type="number" id="edit_initial_reading" name="initial_reading" class="form-input" step="0.01" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_status">Status:</label>
                    <select id="edit_status" name="status" class="form-select" required>
                        <option value="available">Available</option>
                        <option value="installed">Installed</option>
                        <option value="assigned_to_collector">Assigned to Collector</option>
                        <option value="faulty">Faulty</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_meter_image">Update Meter Image:</label>
                    <input type="file" id="edit_meter_image" name="meter_image" class="form-input" accept="image/*">
                    <div id="current_image_preview" style="margin-top: 0.5rem; display: none;">
                        <p style="color: var(--text-muted); font-size: 0.875rem;">Current Image:</p>
                        <img id="edit_current_image" src="" alt="Current Meter Image" style="max-width: 100px; max-height: 100px; border-radius: 4px;">
                    </div>
                </div>
                <button type="submit" class="btn btn-warning" style="width: 100%;">
                    <i class="fas fa-save"></i> Update Meter
                </button>
            </form>
        </div>
    </div>

    <!-- Assign Meter Modal -->
    <div id="assignMeterModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeAssignMeterModal()">&times;</span>
            <h2 class="modal-title"><i class="fas fa-user-plus"></i> Assign Meter to Client</h2>
            <form action="index.php?page=admin_manage_meters" method="POST">
                <input type="hidden" name="action" value="assign_meter">
                <input type="hidden" id="assign_meter_id" name="meter_id">
                <div class="form-group">
                    <label class="form-label" for="client_id">Select Client:</label>
                    <select id="client_id" name="client_id" class="form-select" required>
                        <option value="">Select Client</option>
                        <?php foreach ($data['clients'] as $client): ?>
                            <option value="<?php echo htmlspecialchars($client['id']); ?>">
                                <?php echo htmlspecialchars($client['username']); ?> (<?php echo htmlspecialchars($client['email']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="installation_date">Installation Date:</label>
                    <input type="date" id="installation_date" name="installation_date" class="form-input" required>
                </div>
                <button type="submit" class="btn btn-success" style="width: 100%;">
                    <i class="fas fa-user-plus"></i> Assign Meter
                </button>
            </form>
        </div>
    </div>

    <!-- View Meter Modal -->
    <div id="viewMeterModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeViewMeterModal()">&times;</span>
            <h2 class="modal-title"><i class="fas fa-eye"></i> Meter Details</h2>
            <div id="viewMeterContent">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="image-modal">
        <span class="image-modal-close" onclick="closeImageModal()">&times;</span>
        <img class="image-modal-content" id="modalImage">
    </div>

    <script>
        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');

        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('full-width');
        });

        // Modal functions
        function openAddMeterModal() {
            document.getElementById('addMeterModal').style.display = 'flex';
        }

        function closeAddMeterModal() {
            document.getElementById('addMeterModal').style.display = 'none';
        }

        function openEditMeterModal(meter) {
            document.getElementById('edit_meter_id').value = meter.id;
            document.getElementById('edit_serial_number').value = meter.serial_number;
            document.getElementById('edit_meter_type').value = meter.meter_type || 'residential';
            document.getElementById('edit_initial_reading').value = meter.initial_reading || 0;
            document.getElementById('edit_status').value = meter.status || 'available';
            
            // Handle current image preview
            const currentImagePreview = document.getElementById('current_image_preview');
            const currentImage = document.getElementById('edit_current_image');
            
            if (meter.photo_url) {
                currentImage.src = meter.photo_url;
                currentImagePreview.style.display = 'block';
            } else {
                currentImagePreview.style.display = 'none';
            }
            
            document.getElementById('editMeterModal').style.display = 'flex';
        }

        function closeEditMeterModal() {
            document.getElementById('editMeterModal').style.display = 'none';
        }

        function openAssignMeterModal(meterId) {
            document.getElementById('assign_meter_id').value = meterId;
            document.getElementById('installation_date').valueAsDate = new Date();
            document.getElementById('assignMeterModal').style.display = 'flex';
        }

        function closeAssignMeterModal() {
            document.getElementById('assignMeterModal').style.display = 'none';
        }

        function openViewMeterModal(meter) {
            const content = document.getElementById('viewMeterContent');
            content.innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <p><strong>Serial Number:</strong> ${meter.serial_number}</p>
                        <p><strong>Type:</strong> ${meter.meter_type ? meter.meter_type.charAt(0).toUpperCase() + meter.meter_type.slice(1) : 'N/A'}</p>
                        <p><strong>Status:</strong> <span class="badge ${getStatusBadgeClass(meter.status)}">${meter.status ? meter.status.replace('_', ' ').charAt(0).toUpperCase() + meter.status.replace('_', ' ').slice(1) : 'N/A'}</span></p>
                    </div>
                    <div>
                        <p><strong>Initial Reading:</strong> ${meter.initial_reading || 0}</p>
                        <p><strong>Installation Date:</strong> ${meter.installation_date || 'Not installed'}</p>
                        <p><strong>Client:</strong> ${meter.client_username || 'Not assigned'}</p>
                    </div>
                </div>
                ${meter.photo_url ? `<div style="margin-top: 1rem;"><strong>Image:</strong><br><img src="${meter.photo_url}" alt="Meter Image" style="max-width: 100%; max-height: 200px; border-radius: 4px; margin-top: 0.5rem;"></div>` : ''}
            `;
            document.getElementById('viewMeterModal').style.display = 'flex';
        }

        function closeViewMeterModal() {
            document.getElementById('viewMeterModal').style.display = 'none';
        }

        function openImageModal(imageUrl) {
            document.getElementById('modalImage').src = imageUrl;
            document.getElementById('imageModal').style.display = 'block';
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        function getStatusBadgeClass(status) {
            if (status === 'available' || status === 'in_stock') return 'badge-success';
            if (status === 'assigned_to_collector') return 'badge-warning';
            if (status === 'installed') return 'badge-info';
            return 'badge-danger';
        }

        // Inline reject form helpers for installation submissions
        function toggleRejectForm(id) {
            var el = document.getElementById('reject-form-' + id);
            if (!el) return;
            el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
        }
        function syncRejectNotes(id) {
            var input = document.getElementById('reason_input_' + id);
            var hidden = document.getElementById('reject_notes_' + id);
            if (input && hidden) hidden.value = input.value;
        }
        function syncMessageReason(id) {
            var input = document.getElementById('reason_input_' + id);
            var hidden = document.getElementById('message_reason_' + id);
            if (input && hidden) hidden.value = input.value;
        }

        // Filter meters by status
        function filterMeters(status) {
            const rows = document.querySelectorAll('#metersTableBody tr');
            rows.forEach(row => {
                if (status === 'all' || row.getAttribute('data-status') === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Confirm meter deletion
        function confirmDeleteMeter(meterId) {
            if (confirm('Are you sure you want to delete this meter? This action cannot be undone.')) {
                window.location.href = `index.php?page=admin_manage_meters&action=delete_meter&id=${meterId}`;
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Close image modal when clicking outside
        document.getElementById('imageModal').onclick = function(event) {
            if (event.target === this) {
                closeImageModal();
            }
        }
    </script>
</body>
</html>
