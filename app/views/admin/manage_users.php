<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Users & Requests</title>
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

        /* Base Styles from Dashboard */
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
            /* overflow-x: hidden; */ /* Removed to allow content to overflow naturally if needed, controlled by table-responsive */
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
            position: relative; /* Added for proper stacking context */
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px; /* Consistent with dashboard */
            background-color: var(--sidebar-bg);
            padding: 1.5rem 0; /* Consistent with dashboard */
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

        .sidebar.visible { /* Used for mobile toggle */
            transform: translateX(0);
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
            margin-left: 280px; /* Space for the sidebar */
            flex-grow: 1;
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: calc(100% - 280px); /* Ensures content takes remaining space */
        }

        .main-content.full-width {
            margin-left: 0;
            width: 100%;
        }

        /* Header Bar */
        .header-bar {
            background-color: var(--sidebar-bg); /* Consistent with dashboard */
            padding: 1.25rem 2rem; /* Consistent with dashboard */
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

        .user-info { /* Renamed from .user-profile for consistency */
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-greeting { /* Added for consistency */
            font-weight: 500;
            color: var(--text-light);
        }

        .user-greeting span { /* Added for consistency */
            color: var(--primary);
            font-weight: 600;
        }

        .logout-btn {
            background-color: var(--primary);
            color: white;
            padding: 0.5rem 1.25rem; /* Consistent with dashboard */
            border-radius: 0.5rem; /* Consistent with dashboard */
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
            display: none; /* Hidden by default, shown on mobile */
            margin-right: 1rem; /* Added for spacing */
        }

        .sidebar-toggle:hover {
            background-color: var(--primary-dark);
            transform: scale(1.05);
        }

        /* Content Sections */
        .dashboard-container { /* Added wrapper for consistent padding */
            padding: 2rem;
        }

        .content-section {
            background-color: var(--card-bg);
            padding: 2rem; /* Consistent with dashboard */
            border-radius: 0.75rem; /* Consistent with dashboard */
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

        /* Table Styles */
        .table-responsive {
            overflow-x: auto; /* Allows table to scroll horizontally if content is too wide */
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem; /* Adjusted margin */
            color: var(--text-light);
        }

        .data-table th, .data-table td {
            padding: 1rem 1.25rem; /* Consistent padding */
            border-bottom: 1px solid var(--border-color);
            text-align: left;
        }

        .data-table th {
            background-color: var(--darker-bg);
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .data-table tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        /* Button Styles */
        .btn {
            padding: 0.6rem 1rem; /* Adjusted padding */
            border: none;
            border-radius: 0.375rem; /* Consistent border-radius */
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem; /* Adjusted font size */
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem; /* Consistent gap */
            position: relative;
            overflow: hidden;
        }

        .btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }

        .btn:hover:before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: #16a085;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .btn-info {
            background-color: var(--info);
            color: white;
        }

        .btn-info:hover {
            background-color: #2270b9;
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background-color: #e67e22;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem; /* Adjusted for smaller buttons */
            font-size: 0.8rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.25rem; /* Consistent spacing */
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem; /* Consistent spacing */
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem; /* Consistent padding */
            border: 1px solid var(--border-color);
            border-radius: 0.375rem; /* Consistent border-radius */
            background-color: var(--darker-bg);
            color: var(--text-light);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(255, 71, 87, 0.2);
        }

        .form-control:hover {
            border-color: rgba(255, 71, 87, 0.5);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23a1a5b7' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 12px;
            padding-right: 30px;
        }

        .form-inline {
            display: flex;
            gap: 0.75rem; /* Consistent gap */
            align-items: center;
        }

        .form-inline .form-control {
            width: auto;
            flex-grow: 1;
        }

        /* Role Badges */
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem; /* Consistent padding */
            border-radius: 0.25rem; /* Consistent border-radius */
            font-size: 0.75rem; /* Consistent font size */
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .role-admin {
            background-color: rgba(255, 71, 87, 0.2);
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .role-collector {
            background-color: rgba(46, 134, 222, 0.2);
            color: var(--info);
            border: 1px solid var(--info);
        }

        .role-client {
            background-color: rgba(29, 209, 161, 0.2);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .role-commercial_manager {
            background-color: rgba(255, 159, 67, 0.2);
            color: var(--warning);
            border: 1px solid var(--warning);
        }

        .role-finance_manager {
            background-color: rgba(95, 39, 205, 0.2);
            color: var(--purple);
            border: 1px solid var(--purple);
        }
        
        .role-meter_reader {
            background-color: rgba(169, 169, 169, 0.2);
            color: #a9a9a9;
            border: 1px solid #a9a9a9;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem; /* Consistent padding */
            border-radius: 0.25rem; /* Consistent border-radius */
            font-size: 0.75rem; /* Consistent font size */
            font-weight: 600;
        }

        .status-active {
            background-color: rgba(29, 209, 161, 0.2);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .status-inactive {
            background-color: rgba(238, 82, 83, 0.2);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .status-pending {
            background-color: rgba(255, 159, 67, 0.2);
            color: var(--warning);
            border: 1px solid var(--warning);
        }

        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1001; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.6); /* Black w/ opacity */
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: var(--card-bg);
            margin: auto;
            padding: 2rem; /* Consistent padding */
            border-radius: 0.75rem; /* Consistent border-radius */
            box-shadow: 0 5px 25px rgba(0,0,0,0.5);
            width: 90%;
            max-width: 500px;
            position: relative;
            animation: fadeIn 0.3s ease-out;
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px) rotateX(10deg); }
            to { opacity: 1; transform: translateY(0) rotateX(0); }
        }

        .close-button {
            color: var(--text-muted);
            position: absolute;
            top: 1rem; /* Adjusted position */
            right: 1.25rem; /* Adjusted position */
            font-size: 1.75rem; /* Adjusted font size */
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .close-button:hover, .close-button:focus {
            color: var(--primary);
            text-decoration: none;
            transform: rotate(90deg);
        }

        .modal-content h3 {
            color: var(--primary);
            margin-top: 0;
            margin-bottom: 1.25rem; /* Consistent spacing */
            font-size: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.75rem; /* Consistent padding */
        }

        /* Alert Styles */
        .alert {
            padding: 0.75rem 1rem; /* Consistent padding */
            border-radius: 0.375rem; /* Consistent border-radius */
            margin-bottom: 1.25rem; /* Consistent spacing */
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem; /* Consistent gap */
            transition: all 0.3s ease;
        }

        .alert:hover {
            transform: translateX(5px);
        }

        .alert-success {
            background-color: rgba(29, 209, 161, 0.2);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .alert-error {
            background-color: rgba(238, 82, 83, 0.2);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .alert i {
            font-size: 1.1rem;
        }

        /* Floating animation for action buttons */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
            100% { transform: translateY(0px); }
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem; /* Consistent gap */
        }
        .action-buttons .btn {
            animation: float 3s ease-in-out infinite;
        }
        .action-buttons .btn:nth-child(1) { animation-delay: 0.1s; }
        .action-buttons .btn:nth-child(2) { animation-delay: 0.3s; }

        /* New styles for Add User Form */
        #addUserFormContainer {
            display: none; /* Hidden by default */
            margin-top: 1.5rem; /* Consistent spacing */
            padding: 2rem; /* Consistent padding */
            background-color: var(--card-bg);
            border-radius: 0.75rem; /* Consistent border-radius */
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); /* Consistent shadow */
            border: 1px solid var(--border-color); /* Consistent border */
            animation: fadeIn 0.5s ease-out;
        }
        #addUserFormContainer h3 {
            color: var(--info);
            border-bottom-color: rgba(46, 134, 222, 0.2);
            margin-bottom: 1.25rem; /* Consistent spacing */
            padding-bottom: 0.75rem; /* Consistent padding */
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-280px); /* Consistent with dashboard */
            }
            .sidebar.visible {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                width: 100%; /* Take full width when sidebar is hidden */
            }
            .main-content.full-width {
                margin-left: 0;
            }
            .sidebar-toggle {
                display: flex; /* Show toggle button on smaller screens */
            }
        }

        @media (max-width: 768px) {
            .header-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem; /* Consistent gap */
                padding: 1rem; /* Consistent padding */
            }
            .user-info { /* Consistent with dashboard */
                width: 100%;
                justify-content: space-between;
            }
            .user-greeting { /* Consistent with dashboard */
                display: none;
            }
            .data-table td, .data-table th {
                padding: 0.75rem 1rem; /* Adjusted padding */
            }
            .btn {
                padding: 0.5rem 0.8rem; /* Adjusted padding */
                font-size: 0.8rem;
            }
            .dashboard-container { /* Consistent padding */
                padding: 1rem;
            }
            .content-section { /* Consistent padding */
                padding: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .action-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }
            .action-buttons .btn {
                width: 100%;
            }
            .form-inline {
                flex-direction: column;
                align-items: stretch;
            }
            .form-inline .form-control {
                width: 100%;
            }
        }
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
                    <li><a href="index.php?page=admin_manage_users" class="active"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="index.php?page=admin_manage_billing_plans"><i class="fas fa-file-invoice"></i> Billing Plans</a></li>
                    <li><a href="index.php?page=admin_manage_services"><i class="fas fa-cogs"></i> Manage Services</a></li>
                    <li><a href="index.php?page=admin_manage_requests"><i class="fas fa-clipboard-list"></i> Service Requests</a></li>
                    <li><a href="index.php?page=admin_manage_meters"><i class="fas fa-tachometer-alt"></i> Manage Meters</a></li>
                    <li><a href="index.php?page=admin_manage_client_plans"><i class="fas fa-layer-group"></i> Client Plans</a></li>
                    <li><a href="index.php?page=generate_bills"><i class="fas fa-file-invoice-dollar"></i> Generate Bills</a></li>
                    <li><a href="index.php?page=view_bills"><i class="fas fa-list"></i> View Bills</a></li>
                    <li><a href="index.php?page=finance_manager_reports"><i class="fas fa-chart-pie"></i> Financial Reports</a></li>
                    <li><a href="index.php?page=billing_reports"><i class="fas fa-chart-line"></i> Billing Reports</a></li>
                    <li><a href="index.php?page=admin_transactions"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
                    <li><a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <div class="main-content" id="mainContent">
            <div class="header-bar">
                <div class="header-title">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Manage Users & Service Requests</h1>
                </div>
                <div class="user-info">
                    <div class="user-greeting">Welcome back, <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span></div>
                    <a href="index.php?page=logout" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <div class="dashboard-container">
                <?php if (!empty($data['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($data['error']); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($data['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($data['success']); ?>
                    </div>
                <?php endif; ?>

                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-users"></i> System Users</h2>
                        <button id="addUserBtn" class="btn btn-primary btn-sm"><i class="fas fa-plus-circle"></i> Add New User</button>
                    </div>

                    <div id="addUserFormContainer">
                        <h3>Add New User</h3>
                        <form action="index.php?page=admin_manage_users" method="POST">
                            <input type="hidden" name="add_user" value="1">
                            <div class="form-group">
                                <label for="new_username">Username:</label>
                                <input type="text" id="new_username" name="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="new_email">Email:</label>
                                <input type="email" id="new_email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Password:</label>
                                <input type="password" id="new_password" name="password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="new_full_name">Full Name:</label>
                                <input type="text" id="new_full_name" name="full_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="new_address">Address:</label>
                                <textarea id="new_address" name="address" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="new_contact_phone">Contact Phone:</label>
                                <input type="text" id="new_contact_phone" name="contact_phone" class="form-control" placeholder="e.g., 2547XXXXXXXX" required>
                            </div>
                            <div class="form-group">
                                <label for="new_role">Role:</label>
                                <select id="new_role" name="role" class="form-control" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="client">Client</option>
                                    <option value="collector">Collector</option>
                                    <option value="meter_reader">Meter Reader</option>
                                    <option value="commercial_manager">Commercial Manager</option>
                                    <option value="finance_manager">Finance Manager</option>
                                </select>
                            </div>
                            <div class="form-group" id="privilegedRoleKeyGroup" style="display: none;">
                                    <label for="new_admin_key">Privileged Role Key:</label>
                                    <input type="password" id="new_admin_key" name="admin_key" class="form-control">
                                    <small class="text-muted">Required for privileged roles (Admin, Commercial Manager, Finance Manager, Collector, Meter Reader).</small>
                            </div>
                            <button type="submit" class="btn btn-success mt-4"><i class="fas fa-user-plus"></i> Create User</button>
                            <button type="button" id="cancelAddUserBtn" class="btn btn-danger mt-2">Cancel</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Full Name</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data['users'])): ?>
                                    <?php foreach ($data['users'] as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="role-badge role-<?php echo htmlspecialchars($user['role']); ?>">
                                                    <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $user['role']))); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['contact_phone']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo htmlspecialchars($user['status']); ?>">
                                                    <?php echo htmlspecialchars(ucfirst($user['status'])); ?>
                                                </span>
                                            </td>
                                            <td class="action-buttons whitespace-nowrap">
                                                <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)" class="btn btn-info btn-sm"><i class="fas fa-edit"></i> Edit</button>
                                                <form action="index.php?page=admin_manage_users" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');" style="display: inline-block;">
                                                    <input type="hidden" name="delete_user" value="1">
                                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">No users found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-clipboard-list"></i> Service Requests</h2>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Service</th>
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
                                            <td><?php echo htmlspecialchars($request['id']); ?></td>
                                            <td><?php echo htmlspecialchars($request['client_username']); ?></td>
                                            <td><?php echo htmlspecialchars($request['service_name']); ?></td>
                                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($request['request_date']))); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo htmlspecialchars($request['status']); ?>">
                                                    <?php echo htmlspecialchars(ucfirst($request['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($request['assigned_collector_username'] ?? 'N/A'); ?></td>
                                            <td class="action-buttons whitespace-nowrap">
                                                <?php if ($request['status'] === 'pending'): ?>
                                                    <form action="index.php?page=admin_manage_requests" method="POST" class="form-inline">
                                                        <input type="hidden" name="action" value="assign_request">
                                                        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                                                        <select name="collector_id" class="form-control" required>
                                                            <option value="">Assign Collector</option>
                                                            <?php foreach ($data['collectors'] as $collector): ?>
                                                                <option value="<?php echo htmlspecialchars($collector['id']); ?>">
                                                                    <?php echo htmlspecialchars($collector['username']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-user-plus"></i> Assign</button>
                                                    </form>
                                                <?php elseif ($request['status'] === 'assigned'): ?>
                                                    <form action="index.php?page=admin_manage_requests" method="POST" class="form-inline">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                                                        <select name="new_status" class="form-control" required>
                                                            <option value="serviced">Serviced</option>
                                                            <option value="completed">Completed</option>
                                                            <option value="cancelled">Cancelled</option>
                                                        </select>
                                                        <button type="submit" class="btn btn-info btn-sm"><i class="fas fa-sync-alt"></i> Update Status</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted text-sm">No actions available</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">No service requests found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeEditModal()">&times;</span>
            <h3>Edit User</h3>
            <form action="index.php?page=admin_manage_users" method="POST">
                <input type="hidden" name="update_user" value="1">
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="form-group">
                    <label for="edit_username">Username:</label>
                    <input type="text" id="edit_username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email:</label>
                    <input type="email" id="edit_email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_full_name">Full Name:</label>
                    <input type="text" id="edit_full_name" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_address">Address:</label>
                    <textarea id="edit_address" name="address" class="form-control" rows="2" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_contact_phone">Contact Phone:</label>
                    <input type="text" id="edit_contact_phone" name="contact_phone" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_role">Role:</label>
                    <select id="edit_role" name="role" class="form-control" required>
                        <option value="admin">Admin</option>
                        <option value="client">Client</option>
                        <option value="collector">Collector</option>
                        <option value="meter_reader">Meter Reader</option>
                        <option value="commercial_manager">Commercial Manager</option>
                        <option value="finance_manager">Finance Manager</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status:</label>
                    <select id="edit_status" name="status" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success mt-4"><i class="fas fa-save"></i> Save Changes</button>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const addUserBtn = document.getElementById('addUserBtn');
            const addUserFormContainer = document.getElementById('addUserFormContainer');
            const cancelAddUserBtn = document.getElementById('cancelAddUserBtn');
            const newRoleSelect = document.getElementById('new_role');
            const privilegedRoleKeyGroup = document.getElementById('privilegedRoleKeyGroup');

            // Sidebar toggle for smaller screens
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('visible');
                // The 'full-width' class on mainContent is primarily for
                // when the sidebar completely covers it on mobile.
                // For desktop, it ensures the main content takes up remaining space.
                // We'll let CSS handle the margin based on sidebar visibility.
            });

            // Auto-hide sidebar on mobile when clicking outside
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 992 && 
                    !sidebar.contains(e.target) && 
                    e.target !== sidebarToggle && 
                    !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('visible');
                    // No need to toggle full-width here, main-content CSS handles it
                }
            });

            // Adjust sidebar visibility on resize for desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('visible');
                    // Ensure mainContent resets its width behavior on larger screens
                    mainContent.classList.remove('full-width'); 
                }
            });

            // Highlight active navigation link
            const currentPath = window.location.search;
            const navLinks = sidebar.querySelectorAll('.sidebar-nav a'); // Select links within sidebar-nav
            navLinks.forEach(link => {
                // Remove active class from all links first
                link.classList.remove('active');

                // Check if the link's href matches the current URL query string
                if (link.href.includes(currentPath)) {
                    link.classList.add('active');
                } 
                // Special case for dashboard if no specific page is in URL
                else if (currentPath === '' && link.href.includes('admin_dashboard')) {
                    link.classList.add('active');
                }
            });

            // Show/Hide Add User Form
            addUserBtn.addEventListener('click', function() {
                addUserFormContainer.style.display = 'block';
                addUserBtn.style.display = 'none'; // Hide the add button when form is open
            });

            cancelAddUserBtn.addEventListener('click', function() {
                addUserFormContainer.style.display = 'none';
                addUserBtn.style.display = 'inline-flex'; // Show the add button
                // Clear form fields
                addUserFormContainer.querySelector('form').reset();
                privilegedRoleKeyGroup.style.display = 'none'; // Hide admin key field
            });

            // Toggle Admin Key field based on role selection in Add User form
           newRoleSelect.addEventListener('change', function() {
                const privilegedRoles = ['admin', 'commercial_manager', 'finance_manager', 'collector', 'meter_reader'];
                    if (privilegedRoles.includes(this.value)) {
                        privilegedRoleKeyGroup.style.display = 'block';
                        document.getElementById('new_admin_key').setAttribute('required', 'required');
                    } else {
                    privilegedRoleKeyGroup.style.display = 'none';
                    document.getElementById('new_admin_key').removeAttribute('required');
                    document.getElementById('new_admin_key').value = '';
                }
            });

            // Edit User Modal Functions
            function openEditModal(user) {
                document.getElementById('edit_user_id').value = user.id;
                document.getElementById('edit_username').value = user.username;
                document.getElementById('edit_email').value = user.email;
                document.getElementById('edit_full_name').value = user.full_name;
                document.getElementById('edit_address').value = user.address;
                document.getElementById('edit_contact_phone').value = user.contact_phone;
                document.getElementById('edit_role').value = user.role;
                document.getElementById('edit_status').value = user.status;
                
                document.getElementById('editUserModal').style.display = 'flex';
            }

            function closeEditModal() {
                document.getElementById('editUserModal').style.display = 'none';
            }

            // Close modal when clicking outside
            window.onclick = function(event) {
                const modal = document.getElementById('editUserModal');
                if (event.target === modal) {
                    closeEditModal();
                }
            }

            // Make openEditModal globally accessible
            window.openEditModal = openEditModal;
            window.closeEditModal = closeEditModal;
        });
    </script>
</body>
</html>
