<?php
// app/views/admin/reports.php

// Ensure this page is only accessible to admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?page=login');
    exit();
}

// Extract data passed from the controller
$reportType = $data['report_type'] ?? 'revenue';
$startDate = $data['start_date'] ?? date('Y-m-01');
$endDate = $data['end_date'] ?? date('Y-m-t');
$error = $data['error'] ?? '';
$success = $data['success'] ?? '';
// Prepare a normalized dataset for client-side rendering
$normalized = [
    'overview' => $data['overview'] ?? [
        'total_users' => 0,
        'total_meters' => 0,
        'active_requests' => 0,
        'total_revenue' => 0,
        'user_counts' => ['client'=>0,'collector'=>0,'finance_manager'=>0,'admin'=>0],
        'service_request_counts' => ['pending'=>0,'serviced'=>0,'completed'=>0,'cancelled'=>0]
    ],
    'revenue' => $data['revenue'] ?? [],
    'users' => $data['users'] ?? [],
    'meters' => $data['meters'] ?? [],
    'service_requests' => $data['service_requests'] ?? []
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Reports - Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Include the navigation sidebar -->
        <div class="sidebar" style="width:280px;background-color:#1a1a27;color:#f8f9fa;position:fixed;height:100vh;top:0;left:0;padding:1.5rem 0;border-right:1px solid #2d2d3a;">
            <div class="sidebar-header" style="padding:0 1.5rem 1.5rem;border-bottom:1px solid #2d2d3a;margin-bottom:1.5rem;">
                <h3 style="color:#ff4757;font-size:1.5rem;font-weight:700;display:flex;align-items:center;gap:.75rem;"><i class="fas fa-shield-alt"></i> Admin Panel</h3>
            </div>
            <nav class="sidebar-nav" style="padding:0 1rem;">
                <ul style="list-style:none;">
                    <li><a href="index.php?page=admin_dashboard" style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.5rem;color:#a1a5b7;"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="index.php?page=admin_manage_users" style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.5rem;color:#a1a5b7;"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="index.php?page=admin_manage_billing_plans" style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.5rem;color:#a1a5b7;"><i class="fas fa-file-invoice"></i> Billing Plans</a></li>
                    <li><a href="index.php?page=admin_manage_services" style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.5rem;color:#a1a5b7;"><i class="fas fa-cogs"></i> Manage Services</a></li>
                    <li><a href="index.php?page=admin_manage_client_plans" style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.5rem;color:#a1a5b7;"><i class="fas fa-layer-group"></i> Client Plans</a></li>
                    <li><a href="index.php?page=admin_manage_requests" style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.5rem;color:#a1a5b7;"><i class="fas fa-clipboard-list"></i> Service Requests</a></li>
                    <li><a href="index.php?page=admin_manage_meters" style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.5rem;color:#a1a5b7;"><i class="fas fa-tachometer-alt"></i> Manage Meters</a></li>
                    <li><a href="index.php?page=generate_bills" style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.5rem;color:#a1a5b7;"><i class="fas fa-file-invoice-dollar"></i> Generate Bills</a></li>
                    <li><a href="index.php?page=billing_reports" style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.5rem;color:#a1a5b7;"><i class="fas fa-chart-line"></i> Billing Reports</a></li>
                    <li><a href="index.php?page=finance_manager_reports" style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.5rem;color:#a1a5b7;"><i class="fas fa-chart-pie"></i> Financial Reports</a></li>
                    <li><a href="index.php?page=view_bills" style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.5rem;color:#a1a5b7;"><i class="fas fa-list"></i> View Bills</a></li>
                    <li><a href="index.php?page=admin_transactions" style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.5rem;color:#a1a5b7;"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
                    <li><a href="index.php?page=logout" style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.5rem;color:#a1a5b7;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <div class="flex-1 ml-64 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">System Reports</h1>
                <p class="text-gray-600">Generate and analyze system-wide reports</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p><?= htmlspecialchars($success) ?></p>
                </div>
            <?php endif; ?>

            <!-- Report Generation Controls -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Generate Report</h2>
                <form id="reportForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="reportType" class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                        <select id="reportType" name="report_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <option value="overview" <?= $reportType === 'overview' ? 'selected' : '' ?>>System Overview</option>
                            <option value="users" <?= $reportType === 'users' ? 'selected' : '' ?>>User Statistics</option>
                            <option value="meters" <?= $reportType === 'meters' ? 'selected' : '' ?>>Meter Inventory</option>
                            <option value="service_requests" <?= $reportType === 'service_requests' ? 'selected' : '' ?>>Service Requests</option>
                            <option value="revenue" <?= $reportType === 'revenue' ? 'selected' : '' ?>>Revenue Analysis</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" id="startDate" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                    
                    <div>
                        <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" id="endDate" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" id="generateReportBtn" formaction="index.php?page=admin_reports" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                            <i class="fas fa-search mr-2"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="hidden bg-white rounded-lg shadow-md p-6 mb-6 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-2"></div>
                <p class="text-gray-600">Loading report data...</p>
            </div>

            <!-- System Overview Section -->
            <div id="reportResults" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- System Statistics Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">System Statistics</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="text-sm text-blue-600 font-medium">Total Users</p>
                    <p id="totalUsersValue" class="text-2xl font-bold text-blue-800"><?= number_format($normalized['overview']['total_users'] ?? 0) ?></p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <p class="text-sm text-green-600 font-medium">Total Meters</p>
                    <p id="totalMetersValue" class="text-2xl font-bold text-green-800"><?= number_format($normalized['overview']['total_meters'] ?? 0) ?></p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <p class="text-sm text-purple-600 font-medium">Active Service Requests</p>
                    <p id="activeRequestsValue" class="text-2xl font-bold text-purple-800"><?= number_format($normalized['overview']['active_requests'] ?? 0) ?></p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <p class="text-sm text-yellow-600 font-medium">Total Revenue</p>
                    <p id="totalRevenueValue" class="text-2xl font-bold text-yellow-800">KES <?= number_format($normalized['overview']['total_revenue'] ?? 0, 2) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Export Report Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Export Report</h2>
                    <p class="text-gray-600 mb-4">Download the current report in your preferred format</p>
                    <div class="flex space-x-4">
                        <button onclick="exportReport('pdf')" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-md transition duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
                            <i class="fas fa-file-pdf mr-2"></i> Export as PDF
                        </button>
                        <button onclick="exportReport('csv')" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-md transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                            <i class="fas fa-file-csv mr-2"></i> Export as CSV
                        </button>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- User Distribution Chart -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">User Distribution</h2>
                    <canvas id="userDistributionChart" width="400" height="300"></canvas>
                </div>

                <!-- Service Request Status Chart -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Service Request Status</h2>
                    <canvas id="serviceRequestChart" width="400" height="300"></canvas>
                </div>
            </div>

            <!-- Report Results Table -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Report Results</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <!-- User Report Columns -->
                                <th scope="col" class="users-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="users-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th scope="col" class="users-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th scope="col" class="users-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th scope="col" class="users-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                                <th scope="col" class="users-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="users-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                
                                <!-- Meters Report Columns -->
                                <th scope="col" class="meters-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="meters-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial Number</th>
                                <th scope="col" class="meters-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="meters-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th scope="col" class="meters-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Collector</th>
                                <th scope="col" class="meters-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Installation Date</th>
                                
                                <!-- Service Requests Report Columns -->
                                <th scope="col" class="service_requests-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="service_requests-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                <th scope="col" class="service_requests-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="service_requests-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th scope="col" class="service_requests-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Collector</th>
                                <th scope="col" class="service_requests-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Date</th>
                                <th scope="col" class="service_requests-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completion Date</th>
                                
                                <!-- Revenue Report Columns -->
                                <th scope="col" class="revenue-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="revenue-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Type</th>
                                <th scope="col" class="revenue-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th scope="col" class="revenue-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="revenue-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th scope="col" class="revenue-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Date</th>
                                
                                <!-- Overview Report Columns -->
                                <th scope="col" class="overview-column report-column hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Summary</th>
                            </tr>
                        </thead>
                        <tbody id="reportTableBody" class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No report data available. Please generate a report using the filters above.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Global chart variables
    let userDistributionChart;
    let serviceRequestChart;
    
    // Initialize Charts with data
    function initializeCharts(data) {
        const userDistributionCtx = document.getElementById('userDistributionChart').getContext('2d');
        const serviceRequestCtx = document.getElementById('serviceRequestChart').getContext('2d');
        
        // Destroy existing charts if they exist
        if (userDistributionChart) userDistributionChart.destroy();
        if (serviceRequestChart) serviceRequestChart.destroy();
        
        // User Distribution Chart
        userDistributionChart = new Chart(userDistributionCtx, {
            type: 'pie',
            data: {
                labels: ['Clients', 'Collectors', 'Finance Managers', 'Admins'],
                datasets: [{
                    data: [
                        data.overview.user_counts.client || 0,
                        data.overview.user_counts.collector || 0,
                        data.overview.user_counts.finance_manager || 0,
                        data.overview.user_counts.admin || 0
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'User Distribution by Role'
                    }
                }
            }
        });
        
        // Service Request Chart
        serviceRequestChart = new Chart(serviceRequestCtx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Serviced', 'Completed', 'Cancelled'],
                datasets: [{
                    label: 'Service Requests by Status',
                    data: [
                        data.overview.service_request_counts.pending || 0,
                        (data.overview.service_request_counts.serviced || data.overview.service_request_counts.in_progress || 0),
                        data.overview.service_request_counts.completed || 0,
                        data.overview.service_request_counts.cancelled || 0
                    ],
                    backgroundColor: [
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(255, 99, 132, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 206, 86, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Service Requests by Status'
                    }
                }
            }
        });
    }
    
    // Use server-provided dataset instead of remote API
    function fetchReportData() {
        const reportType = document.getElementById('reportType').value;
        const data = window.__REPORT_DATA__ || { overview: {}, users: [], meters: [], service_requests: [], revenue: [] };
        updateStatisticsCards(data.overview || {});
        updateCharts(data);
        updateReportTable(reportType, data);
    }
    
    // Update statistics cards with data
    function updateStatisticsCards(overviewData) {
        document.getElementById('totalUsersValue').textContent = overviewData.total_users || 0;
        document.getElementById('totalMetersValue').textContent = overviewData.total_meters || 0;
        document.getElementById('activeRequestsValue').textContent = overviewData.active_requests || 0;
        document.getElementById('totalRevenueValue').textContent = (overviewData.total_revenue || 0).toLocaleString();
    }
    
    // Update charts with data
    function updateCharts(data) {
        initializeCharts(data);
    }
    
    // Update report table with data
    function updateReportTable(reportType, data) {
        const tableBody = document.getElementById('reportTableBody');
        tableBody.innerHTML = '';
        
        // Hide all column headers first
        document.querySelectorAll('.report-column').forEach(col => {
            col.classList.add('hidden');
        });
        
        // Show only the relevant columns for this report type
        document.querySelectorAll(`.${reportType}-column`).forEach(col => {
            col.classList.remove('hidden');
        });
        
        // If no data, show message
        if (!data[reportType] || data[reportType].length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.setAttribute('colspan', '7');
            cell.className = 'px-6 py-4 text-center text-gray-500';
            cell.textContent = 'No report data available for the selected criteria.';
            row.appendChild(cell);
            tableBody.appendChild(row);
            return;
        }
        
        // Add data rows
        data[reportType].forEach(item => {
            const row = document.createElement('tr');
            
            if (reportType === 'users') {
                addCell(row, item.id);
                addCell(row, item.username);
                addCell(row, item.email);
                addCell(row, item.role);
                addCell(row, item.full_name);
                addStatusCell(row, item.status, {
                    'active': 'bg-green-100 text-green-800',
                    'inactive': 'bg-red-100 text-red-800'
                });
                addCell(row, item.created_at);
            } else if (reportType === 'meters') {
                addCell(row, item.id);
                addCell(row, item.serial_number);
                addStatusCell(row, item.status, {
                    'active': 'bg-green-100 text-green-800',
                    'inactive': 'bg-red-100 text-red-800',
                    'maintenance': 'bg-yellow-100 text-yellow-800'
                });
                addCell(row, item.client_name);
                addCell(row, item.collector_name);
                addCell(row, item.installation_date);
            } else if (reportType === 'service_requests') {
                addCell(row, item.id);
                addCell(row, item.service_type);
                addStatusCell(row, item.status, {
                    'completed': 'bg-green-100 text-green-800',
                    'pending': 'bg-yellow-100 text-yellow-800',
                    'serviced': 'bg-blue-100 text-blue-800',
                    'in_progress': 'bg-blue-100 text-blue-800',
                    'cancelled': 'bg-red-100 text-red-800'
                });
                addCell(row, item.client_name);
                addCell(row, item.collector_name);
                addCell(row, item.request_date);
                addCell(row, item.completion_date);
            } else if (reportType === 'revenue') {
                addCell(row, item.id);
                addCell(row, item.payment_type);
                addCell(row, `KES ${parseFloat(item.amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
                addStatusCell(row, item.status, {
                    'paid': 'bg-green-100 text-green-800',
                    'pending': 'bg-yellow-100 text-yellow-800',
                    'overdue': 'bg-red-100 text-red-800'
                });
                addCell(row, item.client_name);
                addCell(row, item.payment_date);
            } else if (reportType === 'overview') {
                addCell(row, item.summary);
            }
            
            tableBody.appendChild(row);
        });
    }
    
    // Helper function to add a cell to a row
    function addCell(row, content) {
        const cell = document.createElement('td');
        cell.className = 'px-6 py-4 whitespace-nowrap';
        cell.textContent = content || 'N/A';
        row.appendChild(cell);
    }
    
    // Helper function to add a status cell with appropriate styling
    function addStatusCell(row, status, colorMap) {
        const cell = document.createElement('td');
        cell.className = 'px-6 py-4 whitespace-nowrap';
        
        const span = document.createElement('span');
        span.className = `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${colorMap[status] || 'bg-gray-100 text-gray-800'}`;
        span.textContent = status || 'Unknown';
        
        cell.appendChild(span);
        row.appendChild(cell);
    }
    
    // Export Report Function
    function exportReport(format) {
        const reportType = document.getElementById('reportType').value;
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        
        // Validate dates
        if (!startDate || !endDate) {
            alert('Please select both start and end dates');
            return;
        }
        
        // Show loading indicator
        document.getElementById('loadingIndicator').classList.remove('hidden');
        
        try {
            window.location.href = `/api/export-report?type=${reportType}&format=${format}&start_date=${startDate}&end_date=${endDate}`;
        } catch (error) {
            console.error('Error exporting report:', error);
            alert('An error occurred while exporting the report. Please try again.');
        } finally {
            // Hide loading indicator after a short delay to allow download to start
            setTimeout(() => {
                document.getElementById('loadingIndicator').classList.add('hidden');
            }, 1000);
        }
    }
    
    // Add event listener to the generate report button
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize with server data
        window.__REPORT_DATA__ = <?= json_encode($normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        fetchReportData();
    });
</script>
</body>
</html>
