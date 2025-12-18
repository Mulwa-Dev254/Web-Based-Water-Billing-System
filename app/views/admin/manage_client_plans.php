<?php
if (!isset($_SESSION['user']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'finance_manager')) {
    echo "<script>window.location.href = 'index.php?page=login';</script>";
    exit;
}
$plans = $data['plans'] ?? [];
$message = $data['message'] ?? '';
$error = $data['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Client Plans</title>
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

        .sidebar.visible {
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
            margin-bottom: 1rem;
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

        /* Alerts */
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
            color: var(--success);
            border: 1px solid rgba(29, 209, 161, 0.3);
        }

        .alert-error {
            background-color: rgba(238, 82, 83, 0.1);
            color: var(--danger);
            border: 1px solid rgba(238, 82, 83, 0.3);
        }

        /* Table Styles */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
        }

        table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
        }

        th {
            background-color: rgba(0, 0, 0, 0.2);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-light);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: rgba(255, 71, 87, 0.03);
        }

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-pending {
            background-color: rgba(255, 159, 67, 0.1);
            color: var(--warning);
            border: 1px solid rgba(255, 159, 67, 0.3);
        }

        .badge-active {
            background-color: rgba(29, 209, 161, 0.1);
            color: var(--success);
            border: 1px solid rgba(29, 209, 161, 0.3);
        }

        .badge-rejected {
            background-color: rgba(238, 82, 83, 0.1);
            color: var(--danger);
            border: 1px solid rgba(238, 82, 83, 0.3);
        }

        .badge-cancelled {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }

        /* Action Buttons */
        .actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.875rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 71, 87, 0.3);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #d63031;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(238, 82, 83, 0.3);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text-light);
            transform: translateY(-2px);
        }

        /* Form Styles */
        .input-dark, .select-dark {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            background-color: rgba(0, 0, 0, 0.2);
            color: var(--text-light);
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .input-dark:focus, .select-dark:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 71, 87, 0.2);
        }

        .select-wrapper {
            position: relative;
        }

        .select-caret {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--text-muted);
        }

        .select-dark {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            color-scheme: dark;
        }

        /* Edit Form Section */
        .edit-form-section {
            border-left: 4px solid var(--primary);
            margin-bottom: 2rem;
        }

        .edit-form-section .section-header {
            border-bottom: none;
            margin-bottom: 1.5rem;
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
            .dashboard-container {
                padding: 1rem;
            }
            
            .content-section {
                padding: 1.5rem;
            }
            
            .header-bar {
                padding: 1rem;
            }
            
            .user-greeting {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
    <style>
        .loader-overlay{position:fixed;inset:0;z-index:2000;background:linear-gradient(120deg,rgba(255,255,255,.8),rgba(255,255,255,.6));backdrop-filter:saturate(180%) blur(8px);display:flex;align-items:center;justify-content:center;transition:opacity .4s ease}
        .loader-overlay.loader-hidden{opacity:0;pointer-events:none}
        .spinner{position:relative;width:10em;height:10em}
        .spinner:before{transform:rotateX(60deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateBefore infinite linear reverse}
        .spinner:after{transform:rotateX(240deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateAfter infinite linear}
        .spinner:before,.spinner:after{box-sizing:border-box;content:'';display:block;position:absolute;top:50%;left:50%;margin-top:-5em;margin-left:-5em;width:10em;height:10em;transform-style:preserve-3d;transform-origin:50%;perspective-origin:50% 50%;perspective:340px;background-size:10em 10em;background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjI2NnB4IiBoZWlnaHQ9IjI5N3B4IiB2aWV3Qm94PSIwIDAgMjY2IDI5NyIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWxuczpza2V0Y2g9Imh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaC9ucyI+CiAgICA8dGl0bGU+c3Bpbm5lcjwvdGl0bGU+CiAgICA8ZGVzY3JpcHRpb24+Q3JlYXRlZCB3aXRoIFNrZXRjaCAoaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoKTwvZGVzY3JpcHRpb24+CiAgICA8ZGVmcz48L2RlZnM+CiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBza2V0Y2g6dHlwZT0iTVNQYWdlIj4KICAgICAgICA8cGF0aCBkPSJNMTcxLjUwNzgxMywzLjI1MDAwMDM4IEMyMjYuMjA4MTgzLDEyLjg1NzcxMTEgMjk3LjExMjcyMiw3MS40OTEyODIzIDI1MC44OTU1OTksMTA4LjQxMDE1NSBDMjE2LjU4MjAyNCwxMzUuODIwMzEgMTg2LjUyODQwNSw5Ny4wNjI0OTY0IDE1Ni44MDA3NzQsODUuNzczNDM0NiBDMTI3LjA3MzE0Myw3NC40ODQzNzIxIDc2Ljg4ODQ2MzIsODQuMjE2MTQ2MiA2MC4xMjg5MDY1LDEwOC40MTAxNTMgQy0xNS45ODA0Njg1LDIxOC4yODEyNDcgMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IDE0NS4yNzczNDQsMjk2LjY2Nzk2OCBDMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IC0yNS40NDkyMTg3LDI1Ny4yNDIxOTggMy4zOTg0Mzc1LDEwOC40MTAxNTUgQzE2LjMwNzA2NjEsNDEuODExNDE3NCA4NC43Mjc1ODI5LC0xMS45OTIyOTg1IDE3MS41MDc4MTMsMy4yNTAwMDAzOCBaIiBpZD0iUGF0aC0xIiBmaWxsPSIjMDAwMDAwIiBza2V0Y2g6dHlwZT0iTVNTaGFwZUdyb3VwIj48L3BhdGg+CiAgICA8L2c+Cjwvc3ZnPg==)}
        @keyframes rotateBefore{from{transform:rotateX(60deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(60deg) rotateY(45deg) rotateZ(-360deg)}}
        @keyframes rotateAfter{from{transform:rotateX(240deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(240deg) rotateY(45deg) rotateZ(360deg)}}
    </style>
    <script>window.addEventListener('load',()=>{const l=document.getElementById('loader');if(l){setTimeout(()=>{l.classList.add('loader-hidden');setTimeout(()=>{try{l.remove()}catch(e){}},600);},1500)}});</script>
</head>
<body>
    <div id='loader' class='loader-overlay'><div class='spinner'></div></div>
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
                    <li><a href="index.php?page=admin_manage_client_plans" class="active"><i class="fas fa-layer-group"></i> Client Plans</a></li>
                    <li><a href="index.php?page=admin_manage_requests"><i class="fas fa-clipboard-list"></i> Service Requests</a></li>
                    <li><a href="index.php?page=admin_manage_meters"><i class="fas fa-tachometer-alt"></i> Manage Meters</a></li>
                    <li><a href="index.php?page=generate_bills"><i class="fas fa-file-invoice-dollar"></i> Generate Bills</a></li>
                    <li><a href="index.php?page=view_bills"><i class="fas fa-list"></i> View Bills</a></li>
                    <li><a href="index.php?page=finance_manager_reports"><i class="fas fa-chart-pie"></i> Financial Reports</a></li>
                    <li><a href="index.php?page=billing_reports"><i class="fas fa-chart-line"></i> Billing Reports</a></li>
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
                    <h1>Client Plans Management</h1>
                </div>
                <div class="user-info">
                    <div class="user-greeting">Welcome, <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span></div>
                    <a href="index.php?page=logout" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-container">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($data['editing'])): 
                    $ed = $data['editing']; 
                ?>
                <div class="content-section edit-form-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-pen-to-square"></i> Edit Client Plan #<?php echo (int)$ed['id']; ?></h2>
                    </div>
                    <form method="POST" action="index.php?page=admin_manage_client_plans">
                        <input type="hidden" name="client_plan_id" value="<?php echo (int)$ed['id']; ?>">
                        <input type="hidden" name="action" value="edit">
                        
                        <div class="table-responsive" style="border: none; background: transparent;">
                            <table style="min-width: auto;">
                                <tr>
                                    <th style="width: 200px;">Client</th>
                                    <td><?php echo htmlspecialchars($ed['full_name'] ?: $ed['username']); ?></td>
                                </tr>
                                <tr>
                                    <th>Plan</th>
                                    <td>
                                        <div class="select-wrapper">
                                            <select name="new_plan_id" class="select-dark" required>
                                                <option value="">Select Plan</option>
                                                <?php foreach (($data['availablePlans'] ?? []) as $bp): ?>
                                                    <option value="<?php echo (int)$bp['id']; ?>" <?php echo ((int)$bp['id']===(int)$ed['plan_id'])?'selected':''; ?>>
                                                        <?php echo htmlspecialchars($bp['plan_name']); ?> (<?php echo htmlspecialchars(ucfirst($bp['billing_cycle'])); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <i class="fas fa-chevron-down select-caret"></i>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Next Billing Date</th>
                                    <td>
                                        <input type="date" name="next_billing_date" 
                                               value="<?php echo $ed['next_billing_date'] ? htmlspecialchars(date('Y-m-d', strtotime($ed['next_billing_date']))) : ''; ?>" 
                                               class="input-dark">
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="actions" style="margin-top: 1.5rem; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="index.php?page=admin_manage_client_plans" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-layer-group"></i> Client Plan Applications</h2>
                    </div>
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Client</th>
                                    <th>Plan</th>
                                    <th>Base Rate (KSh)</th>
                                    <th>Billing Cycle</th>
                                    <th>Status</th>
                                    <th>Applied At</th>
                                    <th>Next Bill Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($plans)): ?>
                                    <tr>
                                        <td colspan="9" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.5;"></i>
                                            No client plan applications found.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($plans as $row): 
                                        $st = strtolower((string)($row['status'] ?? 'pending'));
                                        $badge = 'badge-' . ($st ?: 'pending');
                                    ?>
                                    <tr>
                                        <td><?php echo (int)$row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['full_name'] ?: $row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['plan_name']); ?></td>
                                        <td><?php echo number_format((float)($row['base_rate'] ?? 0), 2); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($row['billing_cycle'] ?? 'monthly')); ?></td>
                                        <td><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars(ucfirst($row['status'] ?? 'Pending')); ?></span></td>
                                        <td><?php echo htmlspecialchars($row['created_at'] ?? ''); ?></td>
                                        <td><?php echo ($row['next_billing_date'] ?? false) ? date('M d, Y', strtotime($row['next_billing_date'])) : 'N/A'; ?></td>
                                        <td>
                                            <div class="actions">
                                                <?php if ($st === 'pending'): ?>
                                                    <form method="POST" action="index.php?page=admin_manage_client_plans" style="margin: 0;">
                                                        <input type="hidden" name="client_plan_id" value="<?php echo (int)$row['id']; ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                        <button type="submit" class="btn btn-primary" style="margin: 0;">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="index.php?page=admin_manage_client_plans" style="margin: 0;">
                                                        <input type="hidden" name="client_plan_id" value="<?php echo (int)$row['id']; ?>">
                                                        <input type="hidden" name="action" value="reject">
                                                        <button type="submit" class="btn btn-danger" style="margin: 0;">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    </form>
                                                <?php elseif ($st === 'active'): ?>
                                                    <a class="btn btn-primary" href="index.php?page=admin_manage_client_plans&edit=<?php echo (int)$row['id']; ?>" style="margin: 0;">
                                                        <i class="fas fa-pen-to-square"></i> Edit
                                                    </a>
                                                    <form method="POST" action="index.php?page=admin_manage_client_plans" style="margin: 0;">
                                                        <input type="hidden" name="client_plan_id" value="<?php echo (int)$row['id']; ?>">
                                                        <input type="hidden" name="action" value="discontinue">
                                                        <button type="submit" class="btn btn-danger" style="margin: 0;" onclick="return confirm('Are you sure you want to discontinue this plan?')">
                                                            <i class="fas fa-ban"></i> Discontinue
                                                        </button>
                                                    </form>
                                                <?php elseif ($st === 'cancelled'): ?>
                                                    <span class="badge badge-cancelled" style="margin-right: 0.5rem;">Discontinued</span>
                                                    <a class="btn btn-secondary" href="index.php?page=admin_manage_client_plans&edit=<?php echo (int)$row['id']; ?>" style="margin: 0;">
                                                        <i class="fas fa-pen-to-square"></i> Edit
                                                    </a>
                                                    <form method="POST" action="index.php?page=admin_manage_client_plans" style="margin: 0;">
                                                        <input type="hidden" name="client_plan_id" value="<?php echo (int)$row['id']; ?>">
                                                        <input type="hidden" name="action" value="reinstate">
                                                        <button type="submit" class="btn btn-primary" style="margin: 0;" onclick="return confirm('Are you sure you want to reinstate this plan?')">
                                                            <i class="fas fa-rotate"></i> Reinstate
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" action="index.php?page=admin_manage_client_plans" style="margin: 0;">
                                                        <input type="hidden" name="client_plan_id" value="<?php echo (int)$row['id']; ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                        <button type="submit" class="btn btn-primary" style="margin: 0;">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
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
                </div>
            </div>
        </div>
    </div>

    <!-- FontAwesome for icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            // Toggle sidebar visibility
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('visible');
                    mainContent.classList.toggle('full-width');
                });
            }

            // Auto-hide sidebar on mobile when clicking outside
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 992 && 
                    sidebar && 
                    !sidebar.contains(e.target) && 
                    e.target !== sidebarToggle && 
                    !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('visible');
                    mainContent.classList.remove('full-width');
                }
            });

            // Highlight active navigation link
            const currentPath = window.location.search;
            const navLinks = sidebar.querySelectorAll('a');
            navLinks.forEach(link => {
                if (link.href.includes(currentPath)) {
                    link.classList.add('active');
                } else if (currentPath === '' && link.href.includes('admin_dashboard')) {
                    link.classList.add('active');
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (window.innerWidth > 992 && sidebar && mainContent) {
                sidebar.classList.remove('visible');
                mainContent.classList.remove('full-width');
            }
        });
    </script>
</body>
</html>
