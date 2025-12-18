<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Records</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
            padding: 1.5rem;
        }
        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-body {
            padding: 1.5rem;
        }
        .form-control {
            display: block;
            width: 100%;
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 400;
            line-height: 1.5;
            color: #1f2937;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-control:focus {
            border-color: #93c5fd;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background-color: #2563eb;
            color: white;
            border: 1px solid #2563eb;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        .btn-outline {
            background-color: transparent;
            border: 1px solid #d1d5db;
            color: #4b5563;
        }
        .btn-outline:hover {
            background-color: #f3f4f6;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        .badge-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        .badge-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        .badge-info {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        .tab-button {
            padding: 0.75rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
        }
        .tab-button.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 1.5rem 0 0;
            justify-content: center;
        }
        .pagination li {
            margin: 0 0.25rem;
        }
        .pagination a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            color: #4b5563;
            border: 1px solid #d1d5db;
            transition: all 0.2s;
        }
        .pagination a:hover {
            background-color: #f3f4f6;
        }
        .pagination a.active {
            background-color: #2563eb;
            color: white;
            border-color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include_once __DIR__ . '/sidebar.php'; ?>

        <div class="main-content">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">My Records</h1>
                <p class="text-gray-600">View and manage your meter reading history and service updates</p>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <div class="flex space-x-4">
                    <button class="tab-button active" data-tab="readings">Meter Readings</button>
                    <button class="tab-button" data-tab="services">Service Updates</button>
                </div>
            </div>

            <!-- Meter Readings Tab -->
            <div id="readings-tab" class="tab-content active">
                <div class="card mb-6">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold text-gray-800">Meter Reading History</h2>
                        <div class="flex space-x-2">
                            <div class="relative">
                                <input type="text" id="reading-search" placeholder="Search readings..." class="form-control pl-10 py-2 text-sm">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                            <select id="reading-filter" class="form-control py-2 text-sm">
                                <option value="all">All Meters</option>
                                <?php if (!empty($data['meters'])): ?>
                                    <?php foreach ($data['meters'] as $meter): ?>
                                        <option value="<?= $meter['id'] ?>"><?= htmlspecialchars($meter['serial_number']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <select id="reading-date-filter" class="form-control py-2 text-sm">
                                <option value="all">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="year">This Year</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($data['readings'])): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-tachometer-alt text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500">No meter readings recorded yet</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reading ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meter</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reading Value</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reading Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="readings-table">
                                        <?php foreach ($data['readings'] as $reading): ?>
                                            <tr class="hover:bg-gray-50" data-meter-id="<?= $reading['meter_id'] ?>" data-date="<?= htmlspecialchars($reading['reading_date']) ?>">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">#<?= htmlspecialchars($reading['id']) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($reading['meter_serial_number']) ?></div>
                                                    <div class="text-xs text-gray-500"><?= htmlspecialchars(ucfirst($reading['meter_type'])) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($reading['client_name'] ?? $reading['client_username']) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($reading['reading_value']) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?= htmlspecialchars(date('M d, Y', strtotime($reading['reading_date']))) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php 
                                                    $statusClass = '';
                                                    switch ($reading['status']) {
                                                        case 'verified':
                                                            $statusClass = 'badge-success';
                                                            break;
                                                        case 'pending':
                                                            $statusClass = 'badge-warning';
                                                            break;
                                                        case 'flagged':
                                                            $statusClass = 'badge-danger';
                                                            break;
                                                        default:
                                                            $statusClass = 'badge-info';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>">
                                                        <?= htmlspecialchars(ucfirst($reading['status'])) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="index.php?page=meter_reader_reading_details&reading_id=<?= $reading['id'] ?>" class="btn btn-outline text-xs">
                                                        <i class="fas fa-eye mr-1"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <ul class="pagination">
                                <li><a href="#" class="pagination-prev"><i class="fas fa-chevron-left"></i></a></li>
                                <li><a href="#" class="active">1</a></li>
                                <li><a href="#">2</a></li>
                                <li><a href="#">3</a></li>
                                <li><a href="#" class="pagination-next"><i class="fas fa-chevron-right"></i></a></li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Reading Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-800">Reading Statistics</h2>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <h3 class="text-sm font-medium text-blue-800">Today's Readings</h3>
                                    <p class="text-2xl font-bold text-blue-600"><?= $data['stats']['readings_today'] ?? 0 ?></p>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <h3 class="text-sm font-medium text-green-800">This Month</h3>
                                    <p class="text-2xl font-bold text-green-600"><?= $data['stats']['readings_month'] ?? 0 ?></p>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <h3 class="text-sm font-medium text-purple-800">Verified</h3>
                                    <p class="text-2xl font-bold text-purple-600"><?= $data['stats']['verified_readings'] ?? 0 ?></p>
                                </div>
                                <div class="bg-yellow-50 p-4 rounded-lg">
                                    <h3 class="text-sm font-medium text-yellow-800">Pending</h3>
                                    <p class="text-2xl font-bold text-yellow-600"><?= $data['stats']['pending_readings'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-800">Recent Activity</h2>
                        </div>
                        <div class="card-body">
                            <?php if (empty($data['recent_activity'])): ?>
                                <div class="text-center py-4">
                                    <p class="text-gray-500">No recent activity</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($data['recent_activity'] as $activity): ?>
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 bg-blue-100 rounded-full p-2">
                                                <i class="fas fa-<?= $activity['type'] == 'reading' ? 'tachometer-alt' : 'tools' ?> text-blue-500"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-gray-900"><?= htmlspecialchars($activity['description']) ?></p>
                                                <p class="text-xs text-gray-500"><?= htmlspecialchars($activity['date']) ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Updates Tab -->
            <div id="services-tab" class="tab-content">
                <div class="card mb-6">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold text-gray-800">Service Update History</h2>
                        <div class="flex space-x-2">
                            <div class="relative">
                                <input type="text" id="service-search" placeholder="Search services..." class="form-control pl-10 py-2 text-sm">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                            <select id="service-status-filter" class="form-control py-2 text-sm">
                                <option value="all">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="assigned">Assigned</option>
                                <option value="serviced">Serviced</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <select id="service-date-filter" class="form-control py-2 text-sm">
                                <option value="all">All Time</option>
                                <option value="month">This Month</option>
                                <option value="quarter">This Quarter</option>
                                <option value="year">This Year</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($data['service_updates'])): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-tools text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500">No service updates recorded yet</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meter</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Update Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="services-table">
                                        <?php foreach ($data['service_updates'] as $update): ?>
                                            <tr class="hover:bg-gray-50" data-status="<?= htmlspecialchars($update['status']) ?>" data-date="<?= htmlspecialchars($update['update_date']) ?>">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">#<?= htmlspecialchars($update['request_id']) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $update['service_type']))) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($update['client_name'] ?? $update['client_username']) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($update['meter_serial_number']) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?= htmlspecialchars(date('M d, Y', strtotime($update['update_date']))) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php 
                                                    $statusClass = '';
                                                    switch ($update['status']) {
                                                        case 'pending':
                                                            $statusClass = 'badge-warning';
                                                            break;
                                                        case 'assigned':
                                                            $statusClass = 'badge-info';
                                                            break;
                                                        case 'serviced':
                                                            $statusClass = 'badge-success';
                                                            break;
                                                        case 'completed':
                                                            $statusClass = 'badge-success';
                                                            break;
                                                        case 'cancelled':
                                                            $statusClass = 'badge-danger';
                                                            break;
                                                        default:
                                                            $statusClass = 'badge-info';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>">
                                                        <?= htmlspecialchars(ucfirst($update['status'])) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="index.php?page=meter_reader_service_details&update_id=<?= $update['id'] ?>" class="btn btn-outline text-xs">
                                                        <i class="fas fa-eye mr-1"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <ul class="pagination">
                                <li><a href="#" class="pagination-prev"><i class="fas fa-chevron-left"></i></a></li>
                                <li><a href="#" class="active">1</a></li>
                                <li><a href="#">2</a></li>
                                <li><a href="#">3</a></li>
                                <li><a href="#" class="pagination-next"><i class="fas fa-chevron-right"></i></a></li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Service Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-800">Service Statistics</h2>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <h3 class="text-sm font-medium text-green-800">Completed</h3>
                                    <p class="text-2xl font-bold text-green-600"><?= $data['stats']['completed_services'] ?? 0 ?></p>
                                </div>
                                <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-blue-800">Serviced</h3>
                            <p class="text-2xl font-bold text-blue-600"><?= $data['stats']['in_progress_services'] ?? 0 ?></p>
                                </div>
                                <div class="bg-yellow-50 p-4 rounded-lg">
                                    <h3 class="text-sm font-medium text-yellow-800">Pending</h3>
                                    <p class="text-2xl font-bold text-yellow-600"><?= $data['stats']['pending_services'] ?? 0 ?></p>
                                </div>
                                <div class="bg-red-50 p-4 rounded-lg">
                                    <h3 class="text-sm font-medium text-red-800">Cancelled</h3>
                                    <p class="text-2xl font-bold text-red-600"><?= $data['stats']['cancelled_services'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-800">Service Types</h2>
                        </div>
                        <div class="card-body">
                            <?php if (empty($data['service_types'])): ?>
                                <div class="text-center py-4">
                                    <p class="text-gray-500">No service data available</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($data['service_types'] as $type): ?>
                                        <div>
                                            <div class="flex justify-between mb-1">
                                                <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $type['name']))) ?></span>
                                                <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($type['count']) ?></span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: <?= htmlspecialchars($type['percentage']) ?>%"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all tabs and buttons
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked button and corresponding tab
                this.classList.add('active');
                document.getElementById(this.getAttribute('data-tab') + '-tab').classList.add('active');
            });
        });
        
        // Meter readings filtering
        document.getElementById('reading-search').addEventListener('keyup', function() {
            filterReadings();
        });
        
        document.getElementById('reading-filter').addEventListener('change', function() {
            filterReadings();
        });
        
        document.getElementById('reading-date-filter').addEventListener('change', function() {
            filterReadings();
        });
        
        function filterReadings() {
            const searchTerm = document.getElementById('reading-search').value.toLowerCase();
            const meterId = document.getElementById('reading-filter').value;
            const dateFilter = document.getElementById('reading-date-filter').value;
            
            const rows = document.querySelectorAll('#readings-table tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const rowMeterId = row.getAttribute('data-meter-id');
                const rowDate = new Date(row.getAttribute('data-date'));
                const today = new Date();
                
                let showByDate = true;
                if (dateFilter === 'today') {
                    showByDate = rowDate.toDateString() === today.toDateString();
                } else if (dateFilter === 'week') {
                    const weekStart = new Date(today);
                    weekStart.setDate(today.getDate() - today.getDay());
                    showByDate = rowDate >= weekStart;
                } else if (dateFilter === 'month') {
                    showByDate = rowDate.getMonth() === today.getMonth() && 
                                rowDate.getFullYear() === today.getFullYear();
                } else if (dateFilter === 'year') {
                    showByDate = rowDate.getFullYear() === today.getFullYear();
                }
                
                const showByMeter = meterId === 'all' || rowMeterId === meterId;
                const showBySearch = text.includes(searchTerm);
                
                if (showByMeter && showBySearch && showByDate) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // Service updates filtering
        document.getElementById('service-search').addEventListener('keyup', function() {
            filterServices();
        });
        
        document.getElementById('service-status-filter').addEventListener('change', function() {
            filterServices();
        });
        
        document.getElementById('service-date-filter').addEventListener('change', function() {
            filterServices();
        });
        
        function filterServices() {
            const searchTerm = document.getElementById('service-search').value.toLowerCase();
            const statusFilter = document.getElementById('service-status-filter').value;
            const dateFilter = document.getElementById('service-date-filter').value;
            
            const rows = document.querySelectorAll('#services-table tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const rowStatus = row.getAttribute('data-status');
                const rowDate = new Date(row.getAttribute('data-date'));
                const today = new Date();
                
                let showByDate = true;
                if (dateFilter === 'month') {
                    showByDate = rowDate.getMonth() === today.getMonth() && 
                                rowDate.getFullYear() === today.getFullYear();
                } else if (dateFilter === 'quarter') {
                    const quarter = Math.floor(today.getMonth() / 3);
                    const rowQuarter = Math.floor(rowDate.getMonth() / 3);
                    showByDate = rowQuarter === quarter && 
                                rowDate.getFullYear() === today.getFullYear();
                } else if (dateFilter === 'year') {
                    showByDate = rowDate.getFullYear() === today.getFullYear();
                }
                
                const showByStatus = statusFilter === 'all' || rowStatus === statusFilter;
                const showBySearch = text.includes(searchTerm);
                
                if (showByStatus && showBySearch && showByDate) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
