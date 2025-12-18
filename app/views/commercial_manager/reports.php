<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commercial Manager Reports - AquaBill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            color: #333;
        }
        .sidebar {
            width: 250px;
            background: linear-gradient(to bottom, #4a5568, #2d3748);
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            transition: all 0.3s ease;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .sidebar.hidden {
            left: -250px;
        }
        .sidebar-header {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin-bottom: 10px;
        }
        .sidebar ul li a {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #e2e8f0;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .sidebar ul li a:hover, .sidebar ul li a.active {
            background-color: #2d3748;
            color: #63b3ed;
        }
        .sidebar ul li a i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .main-content.full-width {
            margin-left: 0;
        }
        .navbar {
            background-color: #ffffff;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 0.5rem;
            margin-bottom: 20px;
        }
        .navbar .menu-toggle {
            display: none; /* Hidden on desktop */
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #4a5568;
        }
        .report-section {
            background-color: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">AquaBill CM</div>
        <ul>
            <li><a href="index.php?page=commercial_manager_dashboard"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="index.php?page=commercial_manager_manage_meters"><i class="fas fa-tachometer-alt"></i> Manage Meters</a></li>
            <li><a href="index.php?page=commercial_manager_review_applications"><i class="fas fa-clipboard-list"></i> Review Applications</a></li>
            <li><a href="index.php?page=commercial_manager_reports" class="active"><i class="fas fa-chart-pie"></i> Reports</a></li>
            <li><a href="index.php?page=commercial_manager_profile"><i class="fas fa-user-circle"></i> Profile</a></li>
            <li><a href="index.php?page=logout" class="text-red-400 hover:text-red-200"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content" id="mainContent">
        <div class="navbar">
            <button class="menu-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="text-2xl font-semibold text-gray-800">Reports</h1>
            <div class="user-info flex items-center space-x-2">
                <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
                <span class="text-sm bg-blue-500 text-white px-3 py-1 rounded-full"><?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Commercial Manager')); ?></span>
            </div>
        </div>

        <?php if (!empty($data['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($data['error']); ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($data['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($data['success']); ?></span>
            </div>
        <?php endif; ?>

        <div class="report-section">
            <form method="get" action="index.php" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <input type="hidden" name="page" value="commercial_manager_reports" />
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($data['start_date'] ?? ''); ?>" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">End Date</label>
                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($data['end_date'] ?? ''); ?>" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Report Type</label>
                    <select name="report_type" class="w-full border rounded px-3 py-2">
                        <option value="overview" <?php echo (($data['report_type'] ?? 'overview') === 'overview') ? 'selected' : ''; ?>>Overview</option>
                        <option value="meters" <?php echo (($data['report_type'] ?? '') === 'meters') ? 'selected' : ''; ?>>Meters</option>
                        <option value="applications" <?php echo (($data['report_type'] ?? '') === 'applications') ? 'selected' : ''; ?>>Applications</option>
                        <option value="service_requests" <?php echo (($data['report_type'] ?? '') === 'service_requests') ? 'selected' : ''; ?>>Service Requests</option>
                        <option value="collectors" <?php echo (($data['report_type'] ?? '') === 'collectors') ? 'selected' : ''; ?>>Collectors</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Apply</button>
                </div>
            </form>

            <h2 class="text-2xl font-bold text-gray-800 mb-4">Key Metrics</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border rounded p-4">
                    <div class="text-gray-500 text-sm">Total Meters</div>
                    <div class="text-2xl font-semibold"><?php echo (int)($data['kpis']['total_meters'] ?? 0); ?></div>
                </div>
                <div class="bg-white border rounded p-4">
                    <div class="text-gray-500 text-sm">Available</div>
                    <div class="text-2xl font-semibold"><?php echo (int)($data['kpis']['available_meters'] ?? 0); ?></div>
                </div>
                <div class="bg-white border rounded p-4">
                    <div class="text-gray-500 text-sm">Assigned To Client</div>
                    <div class="text-2xl font-semibold"><?php echo (int)($data['kpis']['assigned_meters'] ?? 0); ?></div>
                </div>
                <div class="bg-white border rounded p-4">
                    <div class="text-gray-500 text-sm">Assigned To Collector</div>
                    <div class="text-2xl font-semibold"><?php echo (int)($data['kpis']['assigned_to_collector_meters'] ?? 0); ?></div>
                </div>
                <div class="bg-white border rounded p-4">
                    <div class="text-gray-500 text-sm">Waiting Installation</div>
                    <div class="text-2xl font-semibold"><?php echo (int)($data['kpis']['waiting_installation_meters'] ?? 0); ?></div>
                </div>
                <div class="bg-white border rounded p-4">
                    <div class="text-gray-500 text-sm">Installed</div>
                    <div class="text-2xl font-semibold"><?php echo (int)($data['kpis']['installed_meters'] ?? 0); ?></div>
                </div>
                <div class="bg-white border rounded p-4">
                    <div class="text-gray-500 text-sm">Verified</div>
                    <div class="text-2xl font-semibold"><?php echo (int)($data['kpis']['verified_meters'] ?? 0); ?></div>
                </div>
                <div class="bg-white border rounded p-4">
                    <div class="text-gray-500 text-sm">Pending Applications</div>
                    <div class="text-2xl font-semibold"><?php echo (int)($data['kpis']['pending_applications'] ?? 0); ?></div>
                </div>
                <div class="bg-white border rounded p-4">
                    <div class="text-gray-500 text-sm">Approved Applications</div>
                    <div class="text-2xl font-semibold"><?php echo (int)($data['kpis']['approved_applications'] ?? 0); ?></div>
                </div>
                <div class="bg-white border rounded p-4">
                    <div class="text-gray-500 text-sm">Rejected Applications</div>
                    <div class="text-2xl font-semibold"><?php echo (int)($data['kpis']['rejected_applications'] ?? 0); ?></div>
                </div>
                <div class="bg-white border rounded p-4">
                    <div class="text-gray-500 text-sm">Pending Requests</div>
                    <div class="text-2xl font-semibold"><?php echo (int)($data['kpis']['pending_service_requests'] ?? 0); ?></div>
                </div>
                <div class="bg-white border rounded p-4">
                    <div class="text-gray-500 text-sm">Assigned Requests</div>
                    <div class="text-2xl font-semibold"><?php echo (int)($data['kpis']['assigned_service_requests'] ?? 0); ?></div>
                </div>
                <div class="bg-white border rounded p-4">
                    <div class="text-gray-500 text-sm">Serviced Requests</div>
                    <div class="text-2xl font-semibold"><?php echo (int)($data['kpis']['serviced_service_requests'] ?? 0); ?></div>
                </div>
                <div class="bg-white border rounded p-4">
                    <div class="text-gray-500 text-sm">Completed Requests</div>
                    <div class="text-2xl font-semibold"><?php echo (int)($data['kpis']['completed_service_requests'] ?? 0); ?></div>
                </div>
                <div class="bg-white border rounded p-4">
                    <div class="text-gray-500 text-sm">Cancelled Requests</div>
                    <div class="text-2xl font-semibold"><?php echo (int)($data['kpis']['cancelled_service_requests'] ?? 0); ?></div>
                </div>
            </div>

            <h3 class="text-xl font-semibold text-gray-800 mb-3">Recent Applications</h3>
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full bg-white border rounded">
                    <thead>
                        <tr class="text-left">
                            <th class="px-4 py-2 border-b">ID</th>
                            <th class="px-4 py-2 border-b">Client</th>
                            <th class="px-4 py-2 border-b">Meter</th>
                            <th class="px-4 py-2 border-b">Status</th>
                            <th class="px-4 py-2 border-b">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($data['recent_applications'] ?? []) as $row): ?>
                        <tr>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($row['id']); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($row['client_name']); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($row['meter_serial']); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($row['application_date']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['recent_applications'])): ?>
                        <tr><td colspan="5" class="px-4 py-3 text-center text-gray-500">No recent applications</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h3 class="text-xl font-semibold text-gray-800 mb-3">Recent Service Requests</h3>
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full bg-white border rounded">
                    <thead>
                        <tr class="text-left">
                            <th class="px-4 py-2 border-b">ID</th>
                            <th class="px-4 py-2 border-b">Client</th>
                            <th class="px-4 py-2 border-b">Service</th>
                            <th class="px-4 py-2 border-b">Collector</th>
                            <th class="px-4 py-2 border-b">Status</th>
                            <th class="px-4 py-2 border-b">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($data['recent_service_requests'] ?? []) as $row): ?>
                        <tr>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($row['id']); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($row['client_name']); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($row['service_name'] ?? ''); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($row['collector_name'] ?? '—'); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($row['request_date']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['recent_service_requests'])): ?>
                        <tr><td colspan="6" class="px-4 py-3 text-center text-gray-500">No recent service requests</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h3 class="text-xl font-semibold text-gray-800 mb-3">Collector Performance</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded">
                    <thead>
                        <tr class="text-left">
                            <th class="px-4 py-2 border-b">Collector ID</th>
                            <th class="px-4 py-2 border-b">Assigned</th>
                            <th class="px-4 py-2 border-b">Completed/Serviced</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($data['collector_performance'] ?? []) as $row): ?>
                        <tr>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($row['collector_id']); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($row['total_assigned']); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($row['completed_count']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['collector_performance'])): ?>
                        <tr><td colspan="3" class="px-4 py-3 text-center text-gray-500">No assignments</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('hidden');
                mainContent.classList.toggle('full-width');
            });

            // Highlight active navigation link
            const currentPath = window.location.search;
            const navLinks = sidebar.querySelectorAll('a');
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.href.includes(currentPath) && currentPath !== '') {
                    link.classList.add('active');
                } else if (currentPath === '' && link.href.includes('commercial_manager_dashboard')) {
                    link.classList.add('active');
                }
            });

            // Adjust sidebar visibility on resize for desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('hidden');
                    mainContent.classList.remove('full-width');
                } else {
                    sidebar.classList.add('hidden');
                    mainContent.classList.add('full-width');
                }
            });
        });
    </script>
</body>
</html>
