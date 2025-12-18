<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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

        .loader-overlay{position:fixed;inset:0;z-index:2000;background:linear-gradient(120deg,rgba(255,255,255,.8),rgba(255,255,255,.6));backdrop-filter:saturate(180%) blur(8px);display:flex;align-items:center;justify-content:center;transition:opacity .4s ease}
        .loader-overlay.loader-hidden{opacity:0;pointer-events:none}
        .spinner{position:relative;width:10em;height:10em}
        .spinner:before{transform:rotateX(60deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateBefore infinite linear reverse}
        .spinner:after{transform:rotateX(240deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateAfter infinite linear}
        .spinner:before,.spinner:after{box-sizing:border-box;content:'';display:block;position:absolute;top:50%;left:50%;margin-top:-5em;margin-left:-5em;width:10em;height:10em;transform-style:preserve-3d;transform-origin:50%;perspective-origin:50% 50%;perspective:340px;background-size:10em 10em;background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjI2NnB4IiBoZWlnaHQ9IjI5N3B4IiB2aWV3Qm94PSIwIDAgMjY2IDI5NyIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWxuczpza2V0Y2g9Imh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaC9ucyI+CiAgICA8dGl0bGU+c3Bpbm5lcjwvdGl0bGU+CiAgICA8ZGVzY3JpcHRpb24+Q3JlYXRlZCB3aXRoIFNrZXRjaCAoaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoKTwvZGVzY3JpcHRpb24+CiAgICA8ZGVmcz48L2RlZnM+CiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBza2V0Y2g6dHlwZT0iTVNQYWdlIj4KICAgICAgICA8cGF0aCBkPSJNMTcxLjUwNzgxMywzLjI1MDAwMDM4IEMyMjYuMjA4MTgzLDEyLjg1NzcxMTEgMjk3LjExMjcyMiw3MS40OTEyODIzIDI1MC44OTU1OTksMTA4LjQxMDE1NSBDMjE2LjU4MjAyNCwxMzUuODIwMzEgMTg2LjUyODQwNSw5Ny4wNjI0OTY0IDE1Ni44MDA3NzQsODUuNzczNDM0NiBDMTI3LjA3MzE0Myw3NC40ODQzNzIxIDc2Ljg4ODQ2MzIsODQuMjE2MTQ2MiA2MC4xMjg5MDY1LDEwOC40MTAxNTMgQy0xNS45ODA0Njg1LDIxOC4yODEyNDcgMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IDE0NS4yNzczNDQsMjk2LjY2Nzk2OCBDMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IC0yNS40NDkyMTg3LDI1Ny4yNDIxOTggMy4zOTg0Mzc1LDEwOC40MTAxNTUgQzE2LjMwNzA2NjEsNDEuODExNDE3NCA4NC43Mjc1ODI5LC0xMS45OTIyOTg1IDE3MS41MDc4MTMsMy4yNTAwMDAzOCBaIiBpZD0iUGF0aC0xIiBmaWxsPSIjMDAwMDAwIiBza2V0Y2g6dHlwZT0iTVNTaGFwZUdyb3VwIj48L3BhdGg+CiAgICA8L2c+Cjwvc3ZnPg==)}
        @keyframes rotateBefore{from{transform:rotateX(60deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(60deg) rotateY(45deg) rotateZ(-360deg)}}
        @keyframes rotateAfter{from{transform:rotateX(240deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(240deg) rotateY(45deg) rotateZ(360deg)}}

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

        .welcome-message {
            text-align: center;
            color: var(--text-muted);
            margin-bottom: 2rem;
            font-size: 1.1rem;
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

        .summary-card.users::before { background-color: var(--success); }
        .summary-card.clients::before { background-color: var(--info); }
        .summary-card.services::before { background-color: var(--warning); }
        .summary-card.plans::before { background-color: var(--purple); }

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

        .summary-card.users .summary-card-icon {
            background-color: rgba(29, 209, 161, 0.1);
            color: var(--success);
        }

        .summary-card.clients .summary-card-icon {
            background-color: rgba(46, 134, 222, 0.1);
            color: var(--info);
        }

        .summary-card.services .summary-card-icon {
            background-color: rgba(255, 159, 67, 0.1);
            color: var(--warning);
        }

        .summary-card.plans .summary-card-icon {
            background-color: rgba(95, 39, 205, 0.1);
            color: var(--purple);
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

        .summary-card.users p { color: var(--success); }
        .summary-card.clients p { color: var(--info); }
        .summary-card.collectors p { color: var(--purple); }
        .summary-card.meter-readers p { color: #a9a9a9; }
        .summary-card.meter-readers .summary-card-icon {
            background-color: rgba(169, 169, 169, 0.1);
            color: #a9a9a9;
        }
        .summary-card.collectors::before { background-color: var(--purple); }
        .summary-card.collectors {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
        }
        
        .summary-card.meter-readers::before { background-color: #a9a9a9; }
        .summary-card.meter-readers {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
        }
        .summary-card.services p { color: var(--warning); }
        .summary-card.plans p { color: var(--purple); }

        /* Quick Actions */
        .quick-actions ul {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .quick-actions li {
            background-color: var(--card-bg);
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .quick-actions li:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .quick-actions li a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            font-weight: 500;
        }

        .quick-actions li:hover a {
            color: var(--primary);
        }

        .quick-actions li i {
            color: var(--primary);
            font-size: 1.1rem;
            width: 1.5rem;
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
            
            .quick-actions ul {
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
    <div id='loader' class='loader-overlay'>
        <div class='spinner'></div>
    </div>
    <script>window.addEventListener('load',()=>{const l=document.getElementById('loader');if(l){setTimeout(()=>{l.classList.add('loader-hidden');setTimeout(()=>{try{l.remove()}catch(e){}},600);},1500)}});</script>
    <div class="dashboard-layout">
        <!-- Sidebar Navigation -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-shield-alt"></i> Admin Panel</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php?page=admin_dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="index.php?page=admin_manage_users"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="index.php?page=admin_manage_billing_plans"><i class="fas fa-file-invoice"></i> Billing Plans</a></li>
                    <li><a href="index.php?page=admin_manage_services"><i class="fas fa-cogs"></i> Manage Services</a></li>
                    <li><a href="index.php?page=admin_manage_client_plans"><i class="fas fa-layer-group"></i> Client Plans</a></li>
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
                    <h1>Dashboard Overview</h1>
                </div>
                <div class="user-info">
                    <div class="user-greeting">Welcome back, <span><?php echo htmlspecialchars($_SESSION['username']); ?></span></div>
                    <div class="notif-wrap" style="position:relative;margin-right:0.5rem;">
                        <a href="#" id="notifBtn" style="position:relative;display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:9999px;background:#ffffff1a;color:#f8f9fa">
                            <i class="fas fa-bell"></i>
                        </a>
                        <div id="adminNotifPanel" style="display:none;position:absolute;top:56px;right:0;width:420px;background:#1a1a27;border-radius:0.75rem;box-shadow:0 12px 28px rgba(15,23,42,.35);border:1px solid #2d2d3a;z-index:1000;">
                            <div style="padding:12px;border-bottom:1px solid #2d2d3a;display:flex;justify-content:space-between;align-items:center;">
                                <span style="font-weight:700;color:#f8f9fa">Notifications</span>
                                <span id="adminNotifHeaderCount" style="background:#ef4444;color:#fff;border-radius:9999px;padding:2px 8px;font-size:.8rem;">0</span>
                            </div>
                            <div id="adminNotifPanelContent" style="color:#a1a5b7"></div>
                        </div>
                    </div>
                    <a href="index.php?page=logout" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-container">
                <p class="welcome-message">Welcome to your administrative control panel. Here's an overview of your system statistics and quick access to management tools.</p>

                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-chart-pie"></i> System Overview</h2>
                    </div>
                    <div class="dashboard-summary">
                        <div class="summary-card users">
                            <div class="summary-card-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h4>Total Users</h4>
                            <p><?php echo htmlspecialchars($data['totalUsers'] ?? 0); ?></p>
                        </div>
                        <div class="summary-card clients">
                            <div class="summary-card-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <h4>Total Clients</h4>
                            <p><?php echo htmlspecialchars($data['totalClients'] ?? 0); ?></p>
                        </div>
                        <div class="summary-card collectors">
                            <div class="summary-card-icon">
                                <i class="fas fa-user-tag"></i>
                            </div>
                            <h4>Total Collectors</h4>
                            <p><?php echo htmlspecialchars($data['totalCollectors'] ?? 0); ?></p>
                        </div>
                        <div class="summary-card meter-readers">
                            <div class="summary-card-icon">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <h4>Meter Readers</h4>
                            <p><?php echo htmlspecialchars($data['totalMeterReaders'] ?? 0); ?></p>
                        </div>
                        <div class="summary-card services">
                            <div class="summary-card-icon">
                                <i class="fas fa-cubes"></i>
                            </div>
                            <h4>Total Services</h4>
                            <p><?php echo htmlspecialchars($data['totalServices'] ?? 0); ?></p>
                        </div>
                        <div class="summary-card plans">
                            <div class="summary-card-icon">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <h4>Total Plans</h4>
                            <p><?php echo htmlspecialchars($data['totalBillingPlans'] ?? 0); ?></p>
                        </div>
                        <div class="summary-card plans">
                            <div class="summary-card-icon">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <h4>Pending Plan Applications</h4>
                            <p><?php echo htmlspecialchars($data['pendingClientPlans'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-clipboard-list"></i> Service Requests</h2>
                    </div>
                    <div class="dashboard-summary">
                        <div class="summary-card assigned">
                            <div class="summary-card-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h4>Pending Requests</h4>
                            <p><?php echo htmlspecialchars($data['pendingServiceRequests'] ?? 0); ?></p>
                        </div>
                        <div class="summary-card services">
                            <div class="summary-card-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h4>Serviced Requests</h4>
                            <p><?php echo htmlspecialchars($data['servicedServiceRequests'] ?? 0); ?></p>
                        </div>
                        <div class="summary-card completed">
                            <div class="summary-card-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h4>Completed Requests</h4>
                            <p><?php echo htmlspecialchars($data['completedServiceRequests'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div class="content-section quick-actions">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-bolt"></i> Quick Actions</h2>
                    </div>
                    <ul>
                        <li>
                            <a href="index.php?page=admin_manage_users">
                                <i class="fas fa-user-edit"></i> Manage User Accounts
                            </a>
                        </li>
                        <li>
                            <a href="index.php?page=admin_manage_billing_plans">
                                <i class="fas fa-file-invoice-dollar"></i> Define Billing Plans
                            </a>
                        </li>
                        <li>
                            <a href="index.php?page=admin_manage_services">
                                <i class="fas fa-sliders-h"></i> Manage Services
                            </a>
                        </li>
                        <li>
                            <a href="index.php?page=admin_manage_meters">
                                <i class="fas fa-tachometer-alt"></i> Manage Meters
                            </a>
                        </li>
                        <li>
                            <a href="index.php?page=admin_manage_client_plans">
                                <i class="fas fa-layer-group"></i> Client Plans
                            </a>
                        </li>
                        <li>
                            <a href="index.php?page=generate_bills">
                                <i class="fas fa-file-invoice"></i> Generate Bills
                            </a>
                        </li>
                        <li>
                            <a href="index.php?page=view_bills">
                                <i class="fas fa-money-bill-wave"></i> View Bills
                            </a>
                        </li>
                        <li>
                            <a href="index.php?page=finance_manager_reports">
                                <i class="fas fa-chart-pie"></i> Financial Reports
                            </a>
                        </li>
                        <li>
                            <a href="index.php?page=billing_reports">
                                <i class="fas fa-chart-line"></i> Billing Reports
                            </a>
                        </li>
                    </ul>
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
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('visible');
                mainContent.classList.toggle('full-width');
            });

            // Auto-hide sidebar on mobile when clicking outside
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 992 && 
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
            
            if (window.innerWidth > 992) {
                sidebar.classList.remove('visible');
                mainContent.classList.remove('full-width');
            }
        });
        const nb=document.getElementById('notifBtn');
        const np=document.getElementById('adminNotifPanel');
        if(nb){nb.addEventListener('click',function(e){e.preventDefault();np.style.display=(np.style.display==='none'||np.style.display==='')?'block':'none';});}
        document.addEventListener('click',function(e){if(np&&!np.contains(e.target)&&nb&&!nb.contains(e.target)){np.style.display='none';}});
        function renderAdminGroups(payload){
            const hc=document.getElementById('adminNotifHeaderCount');
            const pc=document.getElementById('adminNotifPanelContent');
            if(hc){ hc.textContent = payload.notificationsCount || 0; }
            if(pc){
                pc.innerHTML='';
                const groups = payload.groups || [];
                if(!groups.length){
                    const empty=document.createElement('div'); empty.style.cssText='padding:12px;color:#a1a5b7;'; empty.textContent='No notifications'; pc.appendChild(empty);
                } else {
                    groups.forEach(function(g){
                        const gh=document.createElement('div'); gh.style.cssText='padding:10px 12px;background:#151521;border-bottom:1px solid #2d2d3a;font-weight:600;color:#f8f9fa;display:flex;justify-content:space-between;align-items:center;';
                        const title=document.createElement('span'); title.textContent=g.group;
                        const gcount=document.createElement('span'); gcount.style.cssText='border-radius:9999px;padding:2px 8px;font-size:.8rem;'; gcount.textContent=g.count||0;
                        var groupColor = '#2563eb';
                        var gn = (g.group||'').toLowerCase();
                        if(gn==='users'){ groupColor = '#5f27cd'; }
                        else if(gn==='bills'){ groupColor = '#ee5253'; }
                        else if(gn==='services'){ groupColor = '#2e86de'; }
                        else if(gn==='payments'){ groupColor = '#ff9f43'; }
                        else if(gn==='meters'){ groupColor = '#8b5cf6'; }
                        gcount.style.background = groupColor; gcount.style.color = '#fff';
                        gh.appendChild(title); gh.appendChild(gcount); pc.appendChild(gh);
                        const items=g.items||[];
                        if(items.length){
                            items.forEach(function(n){
                                const item=document.createElement('div'); item.style.cssText='padding:12px 14px;border-bottom:1px solid #2d2d3a;display:flex;justify-content:space-between;align-items:center;';
                                const left=document.createElement('div');
                                const ititle=document.createElement('div'); ititle.style.cssText='font-weight:600;color:#f8f9fa;'; ititle.textContent=n.title;
                                const link=document.createElement('a'); link.style.cssText='color:#2e86de;font-size:.85rem;text-decoration:none;'; link.href=n.url; link.textContent='Open';
                                left.appendChild(ititle); left.appendChild(link);
                                if(Array.isArray(n.roles) && n.roles.length){
                                    var chips=document.createElement('div'); chips.style.cssText='margin-top:6px;display:flex;flex-wrap:wrap;gap:6px;';
                                    n.roles.forEach(function(r){ var chip=document.createElement('span'); chip.style.cssText='background:#151521;color:#a1a5b7;border:1px solid #2d2d3a;border-radius:9999px;padding:2px 8px;font-size:.75rem;'; chip.textContent=r; chips.appendChild(chip); });
                                    left.appendChild(chips);
                                }
                                const count=document.createElement('span'); count.style.cssText='background:'+groupColor+';color:#fff;border-radius:9999px;padding:2px 8px;font-size:.8rem;'; count.textContent=n.count;
                                item.appendChild(left); item.appendChild(count); pc.appendChild(item);
                            });
                        } else {
                            const emptyItem=document.createElement('div'); emptyItem.style.cssText='padding:10px 12px;color:#a1a5b7;border-bottom:1px solid #2d2d3a;'; emptyItem.textContent='No items'; pc.appendChild(emptyItem);
                        }
                    });
                }
            }
            let badge = nb.querySelector('.badge');
            const total = payload.notificationsCount || 0;
            if(total>0){ if(!badge){ badge=document.createElement('span'); badge.className='badge'; badge.style.cssText='position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;border-radius:9999px;font-size:0.75rem;line-height:1;padding:4px 6px;min-width:20px;text-align:center;box-shadow:0 0 0 2px #1a1a27;'; nb.appendChild(badge);} badge.textContent=total; } else if(badge){ badge.remove(); }
        }
        function pollAdminNotifs(){ fetch('index.php?page=api_admin_notifications').then(r=>r.json()).then(d=>{ if(d && !d.error){ renderAdminGroups(d); } }).catch(()=>{}); }
        setInterval(pollAdminNotifs,30000); pollAdminNotifs();
    </script>
</body>
</html>
