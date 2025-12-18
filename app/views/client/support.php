<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php?page=login');
    exit;
}
$availableServices = $data['availableServices'] ?? [];
$requests = $data['clientServiceApplications'] ?? [];
$success = $data['success'] ?? '';
$error = $data['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - AquaBill</title>
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

        /* Sidebar Styling - Matched with dashboard.php */
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

        /* Content Container */
        .client-content-container {
            max-width: 1200px;
            margin: 0 auto;
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

        .mt-3 {
            margin-top: 12px;
        }

        .mt-4 {
            margin-top: 15px;
        }

        /* Content Sections */
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

        /* Form Styling */
        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        /* Button Styling */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 0.85rem;
            text-decoration: none;
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

        .btn-outline {
            background-color: white;
            color: #2c3e50;
            border: 1px solid #ddd;
        }

        .btn-outline:hover {
            background-color: #f8f9fa;
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

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-pending {
            background-color: #fff3e0;
            color: #e65100;
        }

        .status-assigned {
            background-color: #e0f2fe;
            color: #0288d1;
        }

        .status-serviced {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .status-confirmed {
            background-color: #ede7f6;
            color: #5e35b1;
        }

        .status-completed {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .status-cancelled {
            background-color: #f5f5f5;
            color: #757575;
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

        /* Actions */
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
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
            
            .actions {
                flex-direction: column;
            }
            
            .actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body class="client-theme">
    <div class="client-dashboard-layout">
        <!-- Include Sidebar -->
        <?php 
        // Determine which page is active for sidebar highlighting
        $currentPage = 'client_support';
        ?>
        <!-- Sidebar -->
        <div class="client-sidebar" id="clientSidebar">
            <h3><i class="fas fa-tint"></i> AquaBill</h3>
            <ul>
                <li>
                    <a href="index.php?page=client_dashboard" class="<?= $currentPage === 'client_dashboard' ? 'active' : '' ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                
                <!-- Billing Section -->
                <li>
                    <a href="index.php?page=client_billing_dashboard" class="<?= $currentPage === 'client_billing_dashboard' ? 'active' : '' ?>">
                        <i class="fas fa-file-invoice-dollar"></i> Billing Dashboard
                    </a>
                </li>
                <li>
                    <a href="index.php?page=client_view_bills" class="<?= $currentPage === 'client_view_bills' ? 'active' : '' ?>">
                        <i class="fas fa-list-alt"></i> My Bills
                    </a>
                </li>
                
                <!-- Existing Client Links -->
                <li>
                    <a href="index.php?page=client_meters" class="<?= $currentPage === 'client_meters' ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt"></i> My Meters
                    </a>
                </li>
                <li>
                    <a href="index.php?page=client_consumption" class="<?= $currentPage === 'client_consumption' ? 'active' : '' ?>">
                        <i class="fas fa-chart-line"></i> Consumption
                    </a>
                </li>
                <li>
                    <a href="index.php?page=client_payments" class="<?= $currentPage === 'client_payments' ? 'active' : '' ?>">
                        <i class="fas fa-money-bill-wave"></i> Payments
                    </a>
                </li>
                <li>
                    <a href="index.php?page=client_support" class="<?= $currentPage === 'client_support' ? 'active' : '' ?>">
                        <i class="fas fa-headset"></i> Support
                    </a>
                </li>
                <li>
                    <a href="index.php?page=client_profile" class="<?= $currentPage === 'client_profile' ? 'active' : '' ?>">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="index.php?page=logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <div class="client-main-content" id="clientMainContent">
            <div class="client-header-bar">
                <button class="client-sidebar-toggle" id="clientSidebarToggle"><i class="fas fa-bars"></i></button>
                <h1><i class="fas fa-headset"></i> Support</h1>
                <div class="user-info">
                    <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <div class="client-content-container">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <div class="client-grid grid-cols-2 gap-4">
                    <!-- New Support Request Card -->
                    <div class="client-content-section">
                        <h3><i class="fas fa-paper-plane"></i> New Support Request</h3>
                        <form method="post" action="index.php?page=client_support">
                            <div class="form-group">
                                <label class="form-label">Service</label>
                                <select name="service_id" class="form-control" required>
                                    <option value="">Select a service</option>
                                    <?php foreach ($availableServices as $svc): ?>
                                        <option value="<?php echo (int)($svc['id'] ?? 0); ?>">
                                            <?php echo htmlspecialchars($svc['service_name'] ?? ($svc['name'] ?? '')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" required></textarea>
                            </div>
                            <button type="submit" name="submit_support_request" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Request
                            </button>
                        </form>
                    </div>

                    <!-- My Support Requests Card -->
                    <div class="client-content-section">
                        <h3><i class="fas fa-list-check"></i> My Support Requests</h3>
                        <?php if (!empty($requests)): ?>
                            <div class="table-responsive">
                                <table class="client-table">
                                    <thead>
                                        <tr>
                                            <th>Service</th>
                                            <th>Requested</th>
                                            <th>Status</th>
                                            <th>Assigned To</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($requests as $r): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($r['service_name'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars(date('d M Y', strtotime($r['request_date'] ?? ''))); ?></td>
                                                <td>
                                                    <?php 
                                                    $status = strtolower($r['status'] ?? 'pending');
                                                    $statusClass = 'status-pending';
                                                    
                                                    switch ($status) {
                                                        case 'assigned':
                                                            $statusClass = 'status-assigned';
                                                            break;
                                                        case 'serviced':
                                                            $statusClass = 'status-serviced';
                                                            break;
                                                        case 'confirmed':
                                                            $statusClass = 'status-confirmed';
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
                                                        <?php echo ucfirst($status); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($r['assigned_collector_username'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <div class="actions">
                                                        <?php if ($status === 'serviced'): ?>
                                                            <form method="post" action="index.php?page=client_support" style="display: inline;">
                                                                <input type="hidden" name="request_id" value="<?php echo (int)($r['id'] ?? 0); ?>">
                                                                <button type="submit" name="confirm_service" class="btn btn-primary">
                                                                    <i class="fas fa-check-circle"></i> Confirm
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (in_array($status, ['serviced', 'completed'])): ?>
                                                            <form method="post" action="index.php?page=client_support" style="display: inline;">
                                                                <input type="hidden" name="request_id" value="<?php echo (int)($r['id'] ?? 0); ?>">
                                                                <button type="submit" name="pay_service" class="btn btn-primary">
                                                                    <i class="fas fa-money-bill-wave"></i> Pay
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($status === 'pending'): ?>
                                                            <form method="post" action="index.php?page=client_support" style="display: inline;">
                                                                <input type="hidden" name="request_id" value="<?php echo (int)($r['id'] ?? 0); ?>">
                                                                <button type="submit" name="cancel_service_request" class="btn btn-danger">
                                                                    <i class="fas fa-ban"></i> Cancel
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> You have no support requests yet. Submit a request above to get started.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions Section -->
                <div class="client-content-section mt-4">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    <div class="client-grid grid-cols-2 gap-4">
                        <a href="index.php?page=client_dashboard" class="btn btn-outline">
                            <i class="fas fa-home"></i> Back to Dashboard
                        </a>
                        <a href="index.php?page=client_apply_service" class="btn btn-outline">
                            <i class="fas fa-tools"></i> Apply for New Service
                        </a>
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