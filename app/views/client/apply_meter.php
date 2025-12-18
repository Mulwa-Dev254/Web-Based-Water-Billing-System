<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Meter - Client Dashboard</title>
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
        }

        .status-pending {
            background-color: #fff3e0;
            color: #e65100;
        }

        .status-approved {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .status-rejected {
            background-color: #ffebee;
            color: #c62828;
        }

        /* Button Styling */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-success {
            background-color: #2ecc71;
            color: white;
        }

        .btn-success:hover {
            background-color: #27ae60;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        /* Alert Styling */
        .alert {
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .alert-danger {
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

        /* Form Styling */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 0.9rem;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            font-size: 0.9rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: #3498db;
            outline: none;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
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
        }
    </style>
</head>
<body class="client-theme">
    <div class="client-dashboard-layout">
        <!-- Sidebar Navigation -->
        <?php include_once dirname(__DIR__) . '/includes/client_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="client-main-content" id="clientMainContent">
            <div class="client-header-bar">
                <button class="client-sidebar-toggle" id="clientSidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1><i class="fas fa-tachometer-alt"></i> Apply for Meter</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Client'); ?></span>
                    <a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <div class="client-content-container">
                <?php if (!empty($data['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($data['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($data['message'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($data['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="client-content-section">
                    <h3>Add Your Own Meter</h3>
                    <p>Have your own meter? Add it and we’ll create a standard application for verification and installation.</p>
                    <a href="index.php?page=client_add_meter" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add Meter</a>
                </div>

                <div class="client-content-section">
                    <h3>Available Meters</h3>
                    <p>Below are the available meters that you can apply for. Select a meter and submit your application.</p>
                    
                    <?php if (empty($data['availableMeters'])): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> There are no meters available for application at this time. Please check back later or contact support.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                    <table class="client-table">
                        <thead>
                            <tr>
                                <th>Meter Image</th>
                                <th>Serial Number</th>
                                <th>Meter Type</th>
                                <th>Initial Reading</th>
                                <th>Status</th>
                                <th>Source</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['availableMeters'] as $meter): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($meter['photo_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($meter['photo_url']); ?>" alt="Meter Image" style="width: 80px; height: auto;">
                                        <?php else: ?>
                                            <span>No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($meter['serial_number']); ?></td>
                                    <td><?php echo htmlspecialchars($meter['meter_type'] ?? 'Standard'); ?></td>
                                    <td><?php echo htmlspecialchars($meter['initial_reading'] ?? '0'); ?></td>
                                    <td>
                                        <span class="status-badge status-approved"><?php echo htmlspecialchars($meter['status'] ?? 'Available'); ?></span>
                                    </td>
                                    <td>
                                        <?php $src = isset($meter['source']) ? $meter['source'] : 'company'; ?>
                                        <span class="status-badge <?php echo $src === 'client' ? 'status-pending' : 'status-approved'; ?>">
                                            <?php echo htmlspecialchars(ucfirst($src)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form action="index.php?page=client_apply_meter" method="post">
                                            <input type="hidden" name="meter_id" value="<?php echo htmlspecialchars($meter['id']); ?>">
                                            <button type="submit" name="apply_meter" class="btn btn-primary btn-sm">Apply for this Meter</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($data['pendingApplications'])): ?>
                <div class="client-content-section">
                    <h3>My Meter Applications</h3>
                    <div class="table-responsive">
                        <table class="client-table">
                            <thead>
                                <tr>
                                    <th>Meter Image</th>
                                    <th>Meter Serial</th>
                                    <th>Application Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['pendingApplications'] as $application): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($application['meter_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($application['meter_image']); ?>" alt="Meter Image" style="width: 80px; height: auto;">
                                            <?php else: ?>
                                                <span>No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($application['meter_serial']); ?></td>
                                        <td><?php echo htmlspecialchars($application['application_date']); ?></td>
                                        <td>
                                            <span class="status-badge 
                                        <?php if ($application['status'] === 'approved'): ?>
                                            status-approved
                                        <?php elseif ($application['status'] === 'pending'): ?>
                                            status-pending
                                        <?php elseif ($application['status'] === 'rejected'): ?>
                                            status-rejected
                                        <?php elseif ($application['status'] === 'submitted_to_admin'): ?>
                                            status-pending
                                        <?php elseif ($application['status'] === 'admin_verified'): ?>
                                            status-approved
                                        <?php elseif ($application['status'] === 'confirmed'): ?>
                                            status-approved
                                        <?php else: ?>
                                            status-pending
                                        <?php endif; ?>">
                                        <?php echo htmlspecialchars($application['status']); ?>
                                    </span>
                                        </td>
                                        <td>
                                            <?php if ($application['status'] === 'pending'): ?>
                                                <form action="index.php?page=client_apply_meter" method="post">
                                                    <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($application['id']); ?>">
                                                    <button type="submit" name="cancel_application" class="btn btn-danger btn-sm">Cancel Application</button>
                                                </form>
                                            <?php else: ?>
                                                <span>No actions available</span>
                                            <?php endif; ?>
                                        </td>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const clientSidebarToggle = document.getElementById('clientSidebarToggle');
            const clientSidebar = document.getElementById('clientSidebar');
            const clientMainContent = document.getElementById('clientMainContent');
            
            clientSidebarToggle.addEventListener('click', function() {
                clientSidebar.classList.toggle('visible');
                clientMainContent.classList.toggle('full-width');
            });
        });
    </script>
</body>
</html>
