<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Service - Client Dashboard</title>
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

        /* Sidebar Styling */
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

        /* Main Content Styling */
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

        /* Content Sections */
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
        }

        .client-content-section h3 i {
            margin-right: 8px;
            color: #3498db;
        }

        /* Grid Layout */
        .client-grid {
            display: grid;
            gap: 15px;
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .gap-4 {
            gap: 15px;
        }

        .mt-4 {
            margin-top: 15px;
        }

        /* Service Cards Styling */
        .service-card {
            background: white;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #3498db;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .service-card h4 {
            margin: 0 0 8px 0;
            color: #2c3e50;
            font-size: 1rem;
            font-weight: 600;
        }

        .service-card p {
            margin: 0 0 8px 0;
            color: #7f8c8d;
            font-size: 0.85rem;
        }

        .service-card .price {
            font-weight: 700;
            color: #3498db;
            font-size: 1rem;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
        }

        /* Button Styling */
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

        .btn-block {
            display: block;
            width: 100%;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
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

        .btn-secondary:hover {
            background-color: #7f8c8d;
        }

        .btn-pay {
            background-color: #28a745;
            color: #fff;
            box-shadow: 0 0 0 0 rgba(40,167,69, 0.6);
            animation: pulseGlow 2s infinite, bounceSoft 2s infinite;
        }
        .btn-pay:hover { background-color: #218838; }
        @keyframes pulseGlow {
            0% { box-shadow: 0 0 0 0 rgba(40,167,69, 0.6); }
            50% { box-shadow: 0 0 0 12px rgba(40,167,69, 0.0); }
            100% { box-shadow: 0 0 0 0 rgba(40,167,69, 0.0); }
        }
        @keyframes bounceSoft {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-2px); }
        }

        /* Table Styling */
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

        .client-table small.text-muted {
            color: #95a5a6;
            font-size: 0.8rem;
        }

        /* Request Status Badges */
        .request-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3e0;
            color: #e65100;
        }

        .status-assigned {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .status-serviced {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .status-confirmed {
            background-color: #ede7f6;
            color: #5e35b1;
        }

        .status-cancelled {
            background-color: #ffebee;
            color: #d32f2f;
        }

        /* Service Request Timeline */
        .timeline {
            position: relative;
            padding-left: 30px;
            margin: 20px 0;
        }

        .timeline:before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e0e0e0;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 15px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -30px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.6rem;
        }

        .timeline-content {
            background: white;
            padding: 10px 15px;
            border-radius: 6px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        .timeline-date {
            font-size: 0.75rem;
            color: #95a5a6;
            margin-top: 5px;
        }

        /* Alert Styling */
        .alert {
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 0.85rem;
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
            margin-right: 6px;
            font-size: 0.9rem;
        }

        /* Responsive Design */
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
            
            .client-grid.grid-cols-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="client-theme">
    <div class="client-dashboard-layout">
        <?php include_once dirname(__DIR__) . '/includes/client_sidebar.php'; ?>

        <div class="client-main-content" id="clientMainContent">
            <div class="client-header-bar">
                <button class="client-sidebar-toggle" id="clientSidebarToggle"><i class="fas fa-bars"></i></button>
                <h1>Apply for New Service</h1>
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
                
                <?php if (isset($data['success']) && $data['success'] != ''): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($data['success']); ?>
                    </div>
                <?php endif; ?>

                <div class="client-grid grid-cols-2 gap-4">
                    <div class="client-content-section">
                        <h3><i class="fas fa-tools"></i> Available Services</h3>
                        
                        <?php if (!empty($data['availableServices'])): ?>
                            <div class="grid grid-cols-1 gap-4 mb-6">
                                <?php foreach ($data['availableServices'] as $service): ?>
                                    <div class="service-card">
                                        <h4><?php echo htmlspecialchars($service['service_name']); ?></h4>
                                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                                        <div class="price">Ksh<?php echo htmlspecialchars(number_format($service['cost'], 2)); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <h4><i class="fas fa-paper-plane"></i> Submit Service Request</h4>
                            <form action="index.php?page=client_apply_service" method="POST">
                                <!-- Changed name from 'apply_service' to 'submit_service_request' -->
                                <input type="hidden" name="submit_service_request" value="1">
                                <div class="form-group">
                                    <label for="service_id">Select Service</label>
                                    <select class="form-control" id="service_id" name="service_id" required>
                                        <option value="">-- Choose a Service --</option>
                                        <?php foreach ($data['availableServices'] as $service): ?>
                                            <?php if ($service['is_active']): ?>
                                                <option value="<?php echo htmlspecialchars($service['id']); ?>">
                                                    <?php echo htmlspecialchars($service['service_name']); ?>
                                                    (Ksh<?php echo htmlspecialchars(number_format($service['cost'], 2)); ?>)
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="description">Request Details</label>
                                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Provide specific details about your service request..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-paper-plane"></i> Submit Application
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No services are currently available for application. Please check back later.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="client-content-section">
                        <h3><i class="fas fa-list-check"></i> My Service Requests</h3>
                        <?php if (!empty($data['clientServiceApplications'])): ?>
                            <div class="table-responsive">
                                <table class="client-table">
                                    <thead>
                                        <tr>
                                            <th>Service</th>
                                            <th>Request Date</th>
                                            <th>Status</th>
                                            <th>Assigned To</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['clientServiceApplications'] as $request): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($request['service_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($request['description']); ?></small>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                                <td>
                                                    <span class="request-status status-<?php echo htmlspecialchars($request['status']); ?>">
                                                        <?php echo htmlspecialchars(ucfirst($request['status'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($request['assigned_collector_username'] ?? 'Not assigned'); ?></td>
                                                <td>
                                                    <?php if ($request['status'] === 'pending' || $request['status'] === 'assigned'): ?>
                                                        <form action="index.php?page=client_apply_service" method="POST" style="display:inline-block;">
                                                            <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                                                            <button type="submit" name="cancel_request" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this service request?');">
                                                                <i class="fas fa-times-circle"></i> Cancel
                                                            </button>
                                                        </form>
                                                    <?php elseif (($request['status'] === 'serviced' || $request['status'] === 'completed') && !empty($request['has_pending_payment'])): ?>
                                                        <a href="index.php?page=client_payments&service_id=<?= htmlspecialchars($request['client_service_id'] ?? '') ?>&mode=view#serviceStatusTracker" class="btn btn-sm btn-secondary" style="margin-right:8px;">
                                                            <i class="fas fa-eye"></i> View Tracker<?php if (!empty($request['latest_transaction_short'])): ?> • Ref <?php echo htmlspecialchars($request['latest_transaction_short']); ?><?php endif; ?>
                                                        </a>
                                                        <button class="btn btn-sm btn-secondary" disabled>
                                                            <i class="fas fa-hourglass-half"></i> Awaiting Verification<?php if (!empty($request['latest_transaction_short'])): ?> • Ref <?php echo htmlspecialchars($request['latest_transaction_short']); ?><?php endif; ?>
                                                        </button>
                                                    <?php elseif ($request['status'] === 'serviced' || $request['status'] === 'completed'): ?>
                                                        <form action="index.php?page=client_apply_service" method="POST" style="display:inline-block;">
                                                            <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                                                            <button type="submit" name="pay_service" class="btn btn-sm btn-pay">
                                                                <i class="fas fa-money-bill-wave"></i> Pay
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
                            
                            <h4 class="mt-4"><i class="fas fa-history"></i> Recent Activity</h4>
                            <div class="timeline">
                                <?php foreach ($data['clientServiceApplications'] as $request): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-dot">
                                            <i class="fas fa-<?php 
                                                echo $request['status'] === 'pending' ? 'clock' : 
                                                     ($request['status'] === 'assigned' ? 'user-tie' : 
                                                     ($request['status'] === 'serviced' ? 'check' : 
                                                     ($request['status'] === 'confirmed' ? 'thumbs-up' : 'times'))); 
                                            ?>"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <strong><?php echo htmlspecialchars($request['service_name']); ?></strong> - 
                                            <span class="request-status status-<?php echo htmlspecialchars($request['status']); ?>">
                                                <?php echo htmlspecialchars(ucfirst($request['status'])); ?>
                                            </span>
                                            <div class="timeline-date">
                                                <?php echo date('M d, Y H:i', strtotime($request['request_date'])); ?>
                                                <?php if ($request['status'] === 'assigned' && !empty($request['assigned_collector_username'])): ?>
                                                    • Assigned to: <?php echo htmlspecialchars($request['assigned_collector_username']); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> You have not submitted any service requests yet.
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
                } else if (currentPath === '' && link.href.includes('client_apply_service')) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
