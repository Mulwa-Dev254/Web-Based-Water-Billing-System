<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Plans - Client Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* General Styling - Matched with dashboard.php */
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

        /* Sidebar Styling - Exactly matched with dashboard.php */
        .client-sidebar {
            width: 220px;
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
            padding: 10px 15px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-size: 0.9rem;
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
            margin-right: 8px;
            width: 18px;
            text-align: center;
            font-size: 0.9rem;
        }

        /* Main Content Styling - Matched with dashboard.php */
        .client-main-content {
            flex: 1;
            transition: all 0.3s ease;
            padding: 15px;
        }

        .client-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .client-header-bar h1 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin: 0;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
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
            font-size: 1.1rem;
            cursor: pointer;
            color: #2c3e50;
            display: none;
        }

        /* Content Sections - Matched with dashboard.php */
        .client-content-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .client-content-section {
            background: white;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .client-content-section h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #2c3e50;
            font-size: 1.1rem;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Grid Layout - Matched with dashboard.php */
        .client-grid {
            display: grid;
            gap: 15px;
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        /* Table Styling - Matched with dashboard.php */
        .table-responsive {
            overflow-x: auto;
        }

        .client-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .client-table th, .client-table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .client-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .client-table tr:hover {
            background-color: #f8f9fa;
        }

        /* Status Badges - Matched with dashboard.php */
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-active {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .status-pending {
            background-color: #fff3e0;
            color: #e65100;
        }

        .status-cancelled {
            background-color: #ffebee;
            color: #d32f2f;
        }

        /* Button Styling - Matched with dashboard.php */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 0.85rem;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        /* Form Styling - Matched with dashboard.php */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
            font-size: 0.85rem;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
        }

        /* Alert Styling - Matched with dashboard.php */
        .alert {
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
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

        /* Text Utilities - Matched with dashboard.php */
        .text-primary {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .text-primary:hover {
            text-decoration: underline;
        }

        /* Responsive Design - Matched with dashboard.php */
        @media (max-width: 992px) {
            .client-grid.grid-cols-2 {
                grid-template-columns: 1fr;
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
        }
    </style>
    <style>
        .loader-overlay{position:fixed;inset:0;z-index:2000;background:linear-gradient(120deg,rgba(255,255,255,.8),rgba(255,255,255,.6));backdrop-filter:saturate(180%) blur(8px);display:flex;align-items:center;justify-content:center;transition:opacity .4s ease}
        .loader-overlay.loader-hidden{opacity:0;pointer-events:none}
        .spinner{position:relative;width:10em;height:10em}
        .spinner:before{transform:rotateX(60deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateBefore infinite linear reverse}
        .spinner:after{transform:rotateX(240deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateAfter infinite linear}
        .spinner:before,.spinner:after{top:50%;left:50%;}
        .spinner:before,.spinner:after{box-sizing:border-box;content:'';display:block;position:absolute;margin-top:-5em;margin-left:-5em;width:10em;height:10em;transform-style:preserve-3d;transform-origin:50%;perspective-origin:50% 50%;perspective:340px;background-size:10em 10em;background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjI2NnB4IiBoZWlnaHQ9IjI5N3B4IiB2aWV3Qm94PSIwIDAgMjY2IDI5NyIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWxuczpza2V0Y2g9Imh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaC9ucyI+CiAgICA8dGl0bGU+c3Bpbm5lcjwvdGl0bGU+CiAgICA8ZGVzY3JpcHRpb24+Q3JlYXRlZCB3aXRoIFNrZXRjaCAoaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoKTwvZGVzY3JpcHRpb24+CiAgICA8ZGVmcz48L2RlZnM+CiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBza2V0Y2g6dHlwZT0iTVNQYWdlIj4KICAgICAgICA8cGF0aCBkPSJNMTcxLjUwNzgxMywzLjI1MDAwMDM4IEMyMjYuMjA4MTgzLDEyLjg1NzcxMTEgMjk3LjExMjcyMiw3MS40OTEyODIzIDI1MC44OTU1OTksMTA4LjQxMDE1NSBDMjE2LjU4MjAyNCwxMzUuODIwMzEgMTg2LjUyODQwNSw5Ny4wNjI0OTY0IDE1Ni44MDA3NzQsODUuNzczNDM0NiBDMTI3LjA3MzE0Myw3NC40ODQzNzIxIDc2Ljg4ODQ2MzIsODQuMjE2MTQ2MiA2MC4xMjg5MDY1LDEwOC40MTAxNTMgQy0xNS45ODA0Njg1LDIxOC4yODEyNDcgMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IDE0NS4yNzczNDQsMjk2LjY2Nzk2OCBDMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IC0yNS40NDkyMTg3LDI1Ny4yNDIxOTggMy4zOTg0Mzc1LDEwOC40MTAxNTUgQzE2LjMwNzA2NjEsNDEuODExNDE3NCA4NC43Mjc1ODI5LC0xMS45OTIyOTg1IDE3MS41MDc4MTMsMy4yNTAwMDAzOCBaIiBpZD0iUGF0aC0xIiBmaWxsPSIjMDAwMDAwIiBza2V0Y2g6dHlwZT0iTVNTaGFwZUdyb3VwIj48L3BhdGg+CiAgICA8L2c+Cjwvc3ZnPg==)}
        @keyframes rotateBefore{from{transform:rotateX(60deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(60deg) rotateY(45deg) rotateZ(-360deg)}}
        @keyframes rotateAfter{from{transform:rotateX(240deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(240deg) rotateY(45deg) rotateZ(360deg)}}
    </style>
    <script>window.addEventListener('load',()=>{const l=document.getElementById('loader');if(l){setTimeout(()=>{l.classList.add('loader-hidden');setTimeout(()=>{try{l.remove()}catch(e){}},600);},1500)}});</script>
</head>
<body class="client-theme">
    <div id='loader' class='loader-overlay'><div class='spinner'></div></div>
    <div class="client-dashboard-layout">
        <?php include_once dirname(__DIR__) . '/includes/client_sidebar.php'; ?>

        <div class="client-main-content" id="clientMainContent">
            <div class="client-header-bar">
                <button class="client-sidebar-toggle" id="clientSidebarToggle"><i class="fas fa-bars"></i></button>
                <h1>My Water Plans</h1>
                <div class="user-info">
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
            <div class="client-content-section" style="border-left: 4px solid #3498db;">
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

        <div class="client-grid grid-cols-2">
            <div class="client-content-section">
                <h3><i class="fas fa-list-check"></i> My Subscribed Plans</h3>
                <?php
                    $hasActive = false; $hasCancelled = false;
                    foreach (($data['clientPlans'] ?? []) as $p) { if (($p['status'] ?? '')==='active') { $hasActive = true; } if (($p['status'] ?? '')==='cancelled') { $hasCancelled = true; } }
                ?>
                <?php if (!$hasActive && $hasCancelled): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Your previous plan was discontinued. Please select a new plan below to resume meter readings.
                    </div>
                <?php endif; ?>
                <?php if (!empty($data['clientPlans'])): ?>
                            <div class="table-responsive">
                                <table class="client-table">
                                    <thead>
                                        <tr>
                                            <th>Plan Name</th>
                                            <th>Base Rate</th>
                                            <th>Billing Cycle</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['clientPlans'] as $plan): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($plan['plan_name']); ?></td>
                                                <td>Ksh<?php echo htmlspecialchars(number_format($plan['base_rate'], 2)); ?></td>
                                                <td><?php echo htmlspecialchars(ucfirst($plan['billing_cycle'])); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo htmlspecialchars($plan['status']); ?>">
                                                        <?php echo htmlspecialchars(ucfirst($plan['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($plan['status'] === 'active' || $plan['status'] === 'pending'): ?>
                                                        <form action="index.php?page=client_my_plans" method="POST" style="display:inline-block;">
                                                            <input type="hidden" name="client_plan_id" value="<?php echo htmlspecialchars($plan['id']); ?>">
                                                            <button type="submit" name="cancel_plan" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to drop this plan? This action cannot be undone.');">
                                                                <i class="fas fa-minus-circle"></i> Drop Plan
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-secondary" disabled>No Action</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> You are not currently subscribed to any water plans.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="client-content-section">
                        <h3><i class="fas fa-plus-circle"></i> Subscribe to a New Plan</h3>
                        <?php if (!empty($data['availablePlans'])): ?>
                            <form action="index.php?page=client_my_plans" method="POST">
                                <input type="hidden" name="subscribe_plan" value="1">
                                <div class="form-group">
                                    <label for="plan_id">Select a Plan</label>
                                    <select class="form-control" id="plan_id" name="plan_id" required>
                                        <option value="">-- Select a Plan --</option>
                                        <?php foreach ($data['availablePlans'] as $plan): ?>
                                            <?php if ($plan['is_active']): ?>
                                                <option value="<?php echo htmlspecialchars($plan['id']); ?>">
                                                    <?php echo htmlspecialchars($plan['plan_name']); ?>
                                                    (Ksh<?php echo htmlspecialchars(number_format($plan['base_rate'], 2)); ?> + 
                                                    Ksh<?php echo htmlspecialchars(number_format($plan['unit_rate'], 4)); ?>/unit)
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-check-circle"></i> Subscribe Now
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No billing plans are currently available for subscription. Please check back later.
                            </div>
                        <?php endif; ?>
                    </div>
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
        });
    </script>
</body>
</html>
