<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* General Styling */
        .client-theme {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .client-dashboard-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling - Adjusted to match reviews.php */
        .client-sidebar {
            width: 220px; /* Matched width */
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 20px 0;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .client-sidebar h3 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 0 15px;
        }

        .client-sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .client-sidebar li a {
            display: block;
            padding: 10px 15px; /* Adjusted padding */
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-size: 0.9rem; /* Adjusted font size */
        }

        .client-sidebar li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid #fff;
        }

        .client-sidebar li a.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 3px solid #fff;
            font-weight: 500;
        }

        .client-sidebar li a i {
            margin-right: 8px; /* Adjusted spacing */
            width: 18px; /* Adjusted width */
            text-align: center;
            font-size: 0.9rem; /* Adjusted icon size */
        }

        /* Main Content Styling */
        .client-main-content {
            flex: 1;
            transition: all 0.3s ease;
            padding: 15px; /* Adjusted padding */
        }

        .client-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px; /* Adjusted margin */
            padding-bottom: 10px; /* Adjusted padding */
            border-bottom: 1px solid #e0e0e0;
        }

        .client-header-bar h1 {
            font-size: 1.5rem; /* Adjusted font size */
            color: #2c3e50;
            margin: 0;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px; /* Adjusted gap */
            font-size: 0.9rem; /* Adjusted font size */
        }

        .user-info a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 500;
        }

        .user-info a:hover {
            text-decoration: underline;
        }

        .client-sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.1rem; /* Adjusted size */
            cursor: pointer;
            color: #2c3e50;
            display: none;
        }

        /* Content Sections */
        .client-content-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .client-content-section {
            background: white;
            border-radius: 6px; /* Adjusted radius */
            padding: 20px; /* Adjusted padding */
            margin-bottom: 20px; /* Adjusted margin */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); /* Adjusted shadow */
        }

        .client-content-section h3 {
            margin-top: 0;
            margin-bottom: 15px; /* Adjusted margin */
            color: #2c3e50;
            font-size: 1.1rem; /* Adjusted size */
            padding-bottom: 8px; /* Adjusted padding */
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }

        .welcome-section {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
        }

        .welcome-section h3 {
            color: white;
            border-bottom-color: rgba(255,255,255,0.2);
            font-size: 1.2rem; /* Adjusted size */
        }

        .welcome-section p {
            color: rgba(255,255,255,0.9);
            font-size: 0.9rem; /* Adjusted size */
            margin-bottom: 0;
        }

        /* Grid Layout */
        .client-grid {
            display: grid;
            gap: 15px; /* Adjusted gap */
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .grid-cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .gap-3 {
            gap: 12px; /* Adjusted gap */
        }

        .gap-4 {
            gap: 15px; /* Adjusted gap */
        }

        .mt-3 {
            margin-top: 12px; /* Adjusted margin */
        }

        .mt-4 {
            margin-top: 15px; /* Adjusted margin */
        }

        /* Summary Cards */
        .client-summary-card {
            background: white;
            border-radius: 6px; /* Adjusted radius */
            padding: 15px; /* Adjusted padding */
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); /* Adjusted shadow */
            transition: transform 0.3s ease;
        }

        .client-summary-card:hover {
            transform: translateY(-3px); /* Adjusted transform */
        }

        .card-icon {
            font-size: 1.5rem; /* Adjusted size */
            color: #3498db;
            margin-bottom: 10px; /* Adjusted margin */
        }

        .client-summary-card h4 {
            margin: 0 0 8px; /* Adjusted margin */
            color: #2c3e50;
            font-size: 0.95rem; /* Adjusted size */
        }

        .client-summary-card p {
            margin: 0;
            font-size: 1rem; /* Adjusted size */
            font-weight: 600;
            color: #3498db;
        }

        /* Action Cards */
        .client-action-card {
            background: white;
            border-radius: 6px; /* Adjusted radius */
            padding: 15px; /* Adjusted padding */
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); /* Adjusted shadow */
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
        }

        .client-action-card:hover {
            background: #3498db;
            color: white;
            transform: translateY(-3px); /* Adjusted transform */
        }

        .client-action-card:hover .client-action-icon {
            color: white;
        }

        .client-action-icon {
            font-size: 1.4rem; /* Adjusted size */
            color: #3498db;
            margin-bottom: 10px; /* Adjusted margin */
        }

        .client-action-card h4 {
            margin: 0;
            font-size: 0.95rem; /* Adjusted size */
        }

        /* Table Styling */
        .table-responsive {
            overflow-x: auto;
        }

        .client-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem; /* Adjusted size */
        }

        .client-table th, .client-table td {
            padding: 10px 12px; /* Adjusted padding */
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        /* Status badge styling */
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        .status-pending {
            background-color: #fff8e1;
            color: #f57c00;
            border: 1px solid #ffcc80;
        }
        
        .status-approved {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        
        .status-rejected {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        .client-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .client-table tr:hover {
            background-color: #f8f9fa;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 3px 8px; /* Adjusted padding */
            border-radius: 20px;
            font-size: 0.75rem; /* Adjusted size */
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3e0;
            color: #e65100;
        }

        .status-approved {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .status-cancelled {
            background-color: #ffebee;
            color: #d32f2f;
        }

        .status-completed {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        /* Button Styling */
        .btn {
            display: inline-block;
            padding: 8px 16px; /* Adjusted padding */
            border: none;
            border-radius: 4px;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 0.85rem; /* Adjusted size */
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .text-primary {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.85rem; /* Adjusted size */
        }

        .text-primary:hover {
            text-decoration: underline;
        }

        .text-center {
            text-align: center;
        }

        /* Alert Styling */
        .alert {
            padding: 12px 15px; /* Adjusted padding */
            margin-bottom: 15px; /* Adjusted margin */
            border-radius: 4px;
            font-size: 0.85rem; /* Adjusted size */
        }

        .alert-error {
            background-color: #fdecea;
            color: #d32f2f;
            border-left: 4px solid #d32f2f;
        }

        .alert-success {
            background-color: #e8f5e9;
            color: #388e3c;
            border-left: 4px solid #388e3c;
        }

        .alert-info {
            background-color: #e3f2fd;
            color: #1976d2;
            border-left: 4px solid #1976d2;
        }

        .alert i {
            margin-right: 6px; /* Adjusted margin */
            font-size: 0.9rem; /* Adjusted size */
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .client-grid.grid-cols-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .client-sidebar {
                position: fixed;
                left: -220px;
                top: 0;
                bottom: 0;
                z-index: 1000;
            }
            
            .client-sidebar.visible {
                left: 0;
            }
            
            .client-main-content {
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .client-main-content.full-width {
                margin-left: 0;
            }
            
            .client-sidebar-toggle {
                display: block;
            }
            
            .client-header-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .user-info {
                width: 100%;
                justify-content: space-between;
            }
            
            .client-grid.grid-cols-2,
            .client-grid.grid-cols-3 {
                grid-template-columns: 1fr;
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
<body class="client-theme">
    <div id='loader' class='loader-overlay'><div class='spinner'></div></div>
    <div class="client-dashboard-layout">
        <!-- Include Sidebar -->
        <?php include_once dirname(__DIR__) . '/includes/client_sidebar.php'; ?>

        <div class="client-main-content" id="clientMainContent">
            <div class="client-header-bar">
                <button class="client-sidebar-toggle" id="clientSidebarToggle"><i class="fas fa-bars"></i></button>
                <h1>Client Dashboard</h1>
                <div class="user-info" style="position:relative;display:flex;align-items:center;gap:12px;">
                    <div class="notif" style="position:relative;display:inline-block;">
                        <button id="clientNotifBtn" title="Notifications" style="background:#f3f4f6;border:1px solid #e5e7eb;padding:8px 10px;border-radius:8px;">
                            <i class="fas fa-bell"></i>
                            <?php $cnc = (int)($data['notificationsCount'] ?? 0); if ($cnc > 0): ?>
                                <span style="position:absolute;top:-6px;right:-6px;background:#e74c3c;color:#fff;border-radius:9999px;font-size:0.75rem;line-height:1;padding:4px 6px;min-width:20px;text-align:center;"><?php echo $cnc; ?></span>
                            <?php endif; ?>
                        </button>
                    </div>
                    <div id="clientNotifPanel" style="position:absolute;right:0;top:48px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 10px 20px rgba(0,0,0,.08);width:320px;z-index:1100;display:none;">
                        <div style="padding:12px 14px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-weight:600;color:#111827;">Notifications</span>
                            <span id="clientNotifHeaderCount" style="background:#3498db;color:#fff;border-radius:9999px;padding:2px 8px;font-size:.8rem;"><?php echo (int)($data['notificationsCount'] ?? 0); ?></span>
                        </div>
                        <div id="clientNotifPanelContent">
                            <?php if (!empty($data['notifications'])): foreach ($data['notifications'] as $n): ?>
                                <div style="padding:12px 14px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
                                    <div>
                                        <div style="font-weight:600;color:#111827;"><?php echo htmlspecialchars($n['title']); ?></div>
                                        <a style="color:#3498db;font-size:.85rem;text-decoration:none;" href="<?php echo htmlspecialchars($n['url']); ?>">Open</a>
                                    </div>
                                    <span style="background:#3498db;color:#fff;border-radius:9999px;padding:2px 8px;font-size:.8rem;"><?php echo (int)$n['count']; ?></span>
                                </div>
                            <?php endforeach; else: ?>
                                <div style="padding:12px 14px;" class="text-gray-500">No notifications</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <div class="client-content-container">
                <?php if (isset($data['error']) && $data['error'] != ''): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($data['error']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($data['message']) && $data['message'] != ''): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($data['message']); ?>
                    </div>
                <?php endif; ?>

                

                <?php if (!empty($data['upgradePrompt'])): ?>
                    <?php $up = $data['upgradePrompt']; ?>
                    <div class="client-content-section" style="border-left: 4px solid #3498db; margin-top:12px;">
                        <h3><i class="fas fa-arrow-up"></i> Plan Upgrade Suggestion</h3>
                        <p style="margin-bottom: 12px;">
                            <?php echo htmlspecialchars($up['message'] ?? 'We found a better plan based on your recent usage.'); ?>
                        </p>
                        <div>
                            <form method="post" action="index.php?page=client_my_plans" style="display:inline-block; margin-right:10px;">
                                <input type="hidden" name="accept_upgrade_prompt" value="1">
                                <input type="hidden" name="prompt_id" value="<?php echo (int)$up['id']; ?>">
                                <input type="hidden" name="plan_id" value="<?php echo (int)$up['recommended_plan_id']; ?>">
                                <button type="submit" style="background:#3498db; color:#fff; border:none; padding:8px 12px; border-radius:4px; cursor:pointer;">
                                    <i class="fas fa-check"></i> Accept & Apply
                                </button>
                            </form>
                            <form method="post" action="index.php?page=client_my_plans" style="display:inline-block;">
                                <input type="hidden" name="decline_upgrade_prompt" value="1">
                                <input type="hidden" name="prompt_id" value="<?php echo (int)$up['id']; ?>">
                                <button type="submit" style="background:#95a5a6; color:#fff; border:none; padding:8px 12px; border-radius:4px; cursor:pointer;">
                                    <i class="fas fa-times"></i> Decline
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="client-content-section welcome-section">
                    <h3>Welcome Back, <?php echo htmlspecialchars($data['clientInfo']['full_name'] ?? $_SESSION['username']); ?>!</h3>
                    <p>Here's an overview of your water services and account information.</p>
                </div>

                <?php if (!empty($data['recentNotifications'])): ?>
                <div class="client-content-section">
                    <h3><i class="fas fa-sms"></i> Recent Notifications</h3>
                    <div class="table-responsive">
                        <table class="client-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['recentNotifications'] as $n): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($n['type'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($n['message'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($n['status'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y H:i', strtotime($n['created_at'] ?? ''))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                    $activePlan = null;
                    if (!empty($data['clientPlans'])) {
                        foreach ($data['clientPlans'] as $p) { if (($p['status'] ?? '') === 'active') { $activePlan = $p; break; } }
                    }
                ?>
                <?php if ($activePlan): ?>
                    <div class="client-grid grid-cols-3 gap-4">
                        <div class="client-summary-card">
                            <div class="card-icon"><i class="fas fa-tint"></i></div>
                            <h4>Current Plan</h4>
                            <p><?php echo htmlspecialchars($activePlan['plan_name']); ?></p>
                        </div>
                        <div class="client-summary-card">
                            <div class="card-icon"><i class="fas fa-money-bill-wave"></i></div>
                            <h4>Base Rate</h4>
                            <p>Ksh<?php echo htmlspecialchars(number_format($activePlan['base_rate'], 2)); ?></p>
                        </div>
                        <div class="client-summary-card">
                            <div class="card-icon"><i class="fas fa-calendar-alt"></i></div>
                            <h4>Next Billing</h4>
                            <p><?php echo $activePlan['next_billing_date'] ? date('M d, Y', strtotime($activePlan['next_billing_date'])) : 'N/A'; ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Meter reading is paused because you have no active plan or your plan was discontinued.
                        <a href="index.php?page=client_my_plans" class="text-primary">Select a new plan</a> to resume services.
                    </div>
                <?php endif; ?>

                <div class="client-grid grid-cols-2 gap-4 mt-4">
                    <div class="client-content-section">
                        <h3>Recent Service Requests</h3>
                        <?php if (!empty($data['serviceRequests'])): ?>
                            <div class="table-responsive">
                                <table class="client-table">
                                    <thead>
                                        <tr>
                                            <th>Service</th>
                                            <th>Description</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Assigned To</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['serviceRequests'] as $request): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($request['service_name']); ?></td>
                                                <td><?php echo htmlspecialchars($request['description']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo htmlspecialchars($request['status']); ?>">
                                                        <?php echo htmlspecialchars(ucfirst($request['status'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($request['assigned_collector_username'] ?? 'N/A'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="index.php?page=client_apply_service" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Request New Service
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> You have no recent service requests. 
                                <a href="index.php?page=client_apply_service" class="text-primary">Request a service now!</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="client-content-section">
                        <h3>Quick Actions</h3>
                        <div class="client-grid grid-cols-2 gap-3">
                            <a href="index.php?page=client_my_plans" class="client-action-card">
                                <i class="fas fa-clipboard-list client-action-icon"></i>
                                <h4>View My Plans</h4>
                            </a>
                            <a href="index.php?page=client_apply_service" class="client-action-card">
                                <i class="fas fa-tools client-action-icon"></i>
                                <h4>Apply for Service</h4>
                            </a>
                            <a href="index.php?page=client_apply_meter" class="client-action-card">
                                <i class="fas fa-tachometer-alt client-action-icon"></i>
                                <h4>Apply for Meter</h4>
                            </a>
                            <a href="index.php?page=client_payments" class="client-action-card">
                                <i class="fas fa-credit-card client-action-icon"></i>
                                <h4>Make a Payment</h4>
                            </a>
                            <a href="index.php?page=client_profile" class="client-action-card">
                                <i class="fas fa-user client-action-icon"></i>
                                <h4>Update Profile</h4>
                            </a>
                        </div>
                    </div>
                    
                    <?php if (!empty($data['myMeters'])): ?>
                    <div class="client-content-section mt-4">
                        <h3>My Meters</h3>
                        <div class="table-responsive">
                            <table class="client-table">
                                <thead>
                                    <tr>
                                        <th>Serial</th>
                                        <th>Status</th>
                                        <th>Installation Date</th>
                                        <th>Initial Reading</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['myMeters'] as $meter): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($meter['serial_number']); ?></td>
                                        <td><span class="status-badge <?php echo ($meter['status']==='installed' || $meter['status']==='assigned') ? 'status-approved' : 'status-pending'; ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_',' ',$meter['status']))); ?></span></td>
                                        <td><?php echo htmlspecialchars($meter['installation_date'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($meter['initial_reading'] ?? '0'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Meter Applications -->
                    <?php if (!empty($data['meterApplications'])): ?>
                    <div class="client-content-section mt-4">
                        <h3>Meter Applications</h3>
                        <div class="table-responsive">
                            <table class="client-table">
                                <thead>
                                    <tr>
                                        <th>Meter Serial</th>
                                        <th>Application Date</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['meterApplications'] as $application): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($application['meter_serial'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($application['application_date']))); ?></td>
                                        <td>
                                            <?php 
                                                $statusClass = 'status-pending';
                                                if ($application['status'] === 'approved' || $application['status'] === 'admin_verified' || $application['status'] === 'confirmed') {
                                                    $statusClass = 'status-approved';
                                                } elseif ($application['status'] === 'rejected') {
                                                    $statusClass = 'status-rejected';
                                                } else {
                                                    $statusClass = 'status-pending';
                                                }
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars(ucfirst($application['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($application['notes'] ?? 'No notes'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const clientSidebarToggle = document.getElementById('clientSidebarToggle');
            const clientSidebar = document.getElementById('clientSidebar');
            const clientMainContent = document.getElementById('clientMainContent');

            // Toggle sidebar visibility
            clientSidebarToggle.addEventListener('click', function() {
                clientSidebar.classList.toggle('visible');
                clientMainContent.classList.toggle('full-width');
            });

            // Hide sidebar on larger screens if it was toggled on mobile and then resized
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    clientSidebar.classList.remove('visible');
                    clientMainContent.classList.remove('full-width');
                }
            });

            // Highlight active navigation link
            const currentPath = window.location.search;
            const navLinks = clientSidebar.querySelectorAll('a');
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.href.includes(currentPath) && currentPath !== '') {
                    link.classList.add('active');
                } else if (currentPath === '' && link.href.includes('client_dashboard')) {
                    link.classList.add('active');
                }
            });

            // Notification toggle
            const nb = document.getElementById('clientNotifBtn');
            const np = document.getElementById('clientNotifPanel');
            if (nb && np) {
                nb.addEventListener('click', function(e) {
                    e.stopPropagation();
                    np.style.display = (np.style.display === 'block') ? 'none' : 'block';
                });
                document.addEventListener('click', function(e) {
                    if (!np.contains(e.target) && !nb.contains(e.target)) {
                        np.style.display = 'none';
                    }
                });
            }
        });
    </script>
    <script>
        function pollClientNotifications(){
            fetch('index.php?page=api_client_notifications').then(r=>r.json()).then(d=>{
                if(d && !d.error){
                    var btn=document.getElementById('clientNotifBtn');
                    if(btn){
                        var b=btn.querySelector('.badge');
                        if(d.notificationsCount>0){
                            if(!b){
                                b=document.createElement('span');
                                b.className='badge';
                                b.style.cssText='position:absolute;top:-6px;right:-6px;background:#e74c3c;color:#fff;border-radius:9999px;font-size:0.75rem;line-height:1;padding:4px 6px;min-width:20px;text-align:center;';
                                btn.appendChild(b);
                            }
                            b.textContent=d.notificationsCount;
                        } else { if(b){ b.remove(); } }
                    }
                    var hc=document.getElementById('clientNotifHeaderCount');
                    if(hc){ hc.textContent=d.notificationsCount||0; }
                    var pc=document.getElementById('clientNotifPanelContent');
                    if(pc){
                        pc.innerHTML='';
                        if(d.notifications && d.notifications.length){
                            d.notifications.forEach(function(n){
                                var item=document.createElement('div');
                                item.style.cssText='padding:12px 14px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;';
                                var left=document.createElement('div');
                                var title=document.createElement('div');
                                title.style.cssText='font-weight:600;color:#111827;';
                                title.textContent=n.title;
                                var link=document.createElement('a');
                                link.style.cssText='color:#3498db;font-size:.85rem;text-decoration:none;';
                                link.href=n.url; link.textContent='Open';
                                left.appendChild(title); left.appendChild(link);
                                var count=document.createElement('span');
                                count.style.cssText='background:#3498db;color:#fff;border-radius:9999px;padding:2px 8px;font-size:.8rem;';
                                count.textContent=n.count;
                                item.appendChild(left); item.appendChild(count);
                                pc.appendChild(item);
                            });
                        } else {
                            var empty=document.createElement('div');
                            empty.className='text-gray-500';
                            empty.style.cssText='padding:12px 14px;';
                            empty.textContent='No notifications';
                            pc.appendChild(empty);
                        }
                    }
                }
            }).catch(function(){});
        }
        setInterval(pollClientNotifications,30000);
        pollClientNotifications();
    </script>
</body>
</html>
