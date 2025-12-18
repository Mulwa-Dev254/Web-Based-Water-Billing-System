<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meter History</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .meter-card {
            transition: all 0.2s;
            cursor: pointer;
        }
        .meter-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .meter-card.selected {
            border: 2px solid #2563eb;
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
                <h1 class="text-2xl font-bold text-gray-800">Meter History</h1>
                <p class="text-gray-600">View historical data and trends for your assigned meters</p>
            </div>

            <!-- Meter Selection -->
            <div class="card mb-6">
                <div class="card-header">
                    <h2 class="text-lg font-semibold text-gray-800">Select Meter</h2>
                    <div class="relative">
                        <input type="text" id="meter-search" placeholder="Search meters..." class="form-control pl-10 py-2 text-sm">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($data['meters'])): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-tachometer-alt text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500">No meters assigned to you</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="meters-container">
                            <?php foreach ($data['meters'] as $meter): ?>
                                <div class="meter-card card p-4 border border-gray-200" data-meter-id="<?= $meter['id'] ?>" data-serial="<?= htmlspecialchars($meter['serial_number']) ?>" data-client="<?= htmlspecialchars($meter['client_name'] ?? $meter['client_username']) ?>">
                                    <div class="flex items-center mb-3">
                                        <div class="flex-shrink-0 bg-blue-100 rounded-full p-2">
                                            <i class="fas fa-tachometer-alt text-blue-500"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-gray-900"><?= htmlspecialchars($meter['serial_number']) ?></h3>
                                            <p class="text-xs text-gray-500"><?= htmlspecialchars(ucfirst($meter['meter_type'])) ?></p>
                                        </div>
                                    </div>
                                    <div class="border-t border-gray-200 pt-3">
                                        <p class="text-xs text-gray-500">Client</p>
                                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($meter['client_name'] ?? $meter['client_username']) ?></p>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-xs text-gray-500">Location</p>
                                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($meter['location'] ?? 'Not specified') ?></p>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-xs text-gray-500">Last Reading</p>
                                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($meter['last_reading_value'] ?? 'No readings') ?> (<?= htmlspecialchars($meter['last_reading_date'] ? date('M d, Y', strtotime($meter['last_reading_date'])) : 'N/A') ?>)</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Meter History Details (Initially Hidden) -->
            <div id="meter-history-details" class="hidden">
                <div class="card mb-6">
                    <div class="card-header">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800" id="selected-meter-title">Meter Details</h2>
                            <p class="text-sm text-gray-600" id="selected-meter-client">Client: </p>
                        </div>
                        <div class="flex space-x-2">
                            <select id="time-period" class="form-control py-2 text-sm">
                                <option value="3">Last 3 Months</option>
                                <option value="6">Last 6 Months</option>
                                <option value="12" selected>Last 12 Months</option>
                                <option value="all">All Time</option>
                            </select>
                            <button id="export-data" class="btn btn-outline">
                                <i class="fas fa-download mr-1"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-6">
                            <h3 class="text-md font-semibold text-gray-800 mb-3">Consumption Trend</h3>
                            <div class="bg-white p-4 rounded-lg">
                                <canvas id="consumption-chart" height="250"></canvas>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h3 class="text-sm font-medium text-blue-800">Average Consumption</h3>
                                <p class="text-2xl font-bold text-blue-600" id="avg-consumption">0</p>
                                <p class="text-xs text-blue-600">units per month</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h3 class="text-sm font-medium text-green-800">Highest Reading</h3>
                                <p class="text-2xl font-bold text-green-600" id="highest-reading">0</p>
                                <p class="text-xs text-green-600" id="highest-reading-date">on --/--/----</p>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <h3 class="text-sm font-medium text-purple-800">Total Readings</h3>
                                <p class="text-2xl font-bold text-purple-600" id="total-readings">0</p>
                                <p class="text-xs text-purple-600">recorded readings</p>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-md font-semibold text-gray-800 mb-3">Reading History</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reading Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reading Value</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consumption</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recorded By</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="readings-history-table">
                                        <!-- Readings will be loaded dynamically -->
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
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-800">Meter Information</h2>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500">Serial Number</p>
                                        <p class="text-sm font-medium text-gray-900" id="info-serial">--</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Meter Type</p>
                                        <p class="text-sm font-medium text-gray-900" id="info-type">--</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Installation Date</p>
                                        <p class="text-sm font-medium text-gray-900" id="info-install-date">--</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Last Maintenance</p>
                                        <p class="text-sm font-medium text-gray-900" id="info-maintenance">--</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Meter Status</p>
                                        <p class="text-sm font-medium text-gray-900" id="info-status">--</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Billing Plan</p>
                                        <p class="text-sm font-medium text-gray-900" id="info-plan">--</p>
                                    </div>
                                </div>
                                
                                <div>
                                    <p class="text-xs text-gray-500">Location</p>
                                    <p class="text-sm font-medium text-gray-900" id="info-location">--</p>
                                </div>
                                
                                <div>
                                    <p class="text-xs text-gray-500">GPS Coordinates</p>
                                    <p class="text-sm font-medium text-gray-900" id="info-gps">--</p>
                                </div>
                                
                                <div>
                                    <p class="text-xs text-gray-500">Notes</p>
                                    <p class="text-sm font-medium text-gray-900" id="info-notes">--</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-800">Service History</h2>
                        </div>
                        <div class="card-body">
                            <div id="service-history-container">
                                <!-- Service history will be loaded dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Meter search functionality
        document.getElementById('meter-search').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const meterCards = document.querySelectorAll('.meter-card');
            
            meterCards.forEach(card => {
                const serial = card.getAttribute('data-serial').toLowerCase();
                const client = card.getAttribute('data-client').toLowerCase();
                
                if (serial.includes(searchTerm) || client.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Meter selection functionality
        const meterCards = document.querySelectorAll('.meter-card');
        let selectedMeterId = null;
        let consumptionChart = null;
        
        meterCards.forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected class from all cards
                meterCards.forEach(c => c.classList.remove('selected'));
                
                // Add selected class to clicked card
                this.classList.add('selected');
                
                // Get meter ID and update selected meter details
                selectedMeterId = this.getAttribute('data-meter-id');
                const meterSerial = this.getAttribute('data-serial');
                const meterClient = this.getAttribute('data-client');
                
                // Update meter details section
                document.getElementById('selected-meter-title').textContent = `Meter: ${meterSerial}`;
                document.getElementById('selected-meter-client').textContent = `Client: ${meterClient}`;
                
                // Show meter history details section
                document.getElementById('meter-history-details').classList.remove('hidden');
                
                // Load meter data
                loadMeterData(selectedMeterId);
            });
        });
        
        // Time period change handler
        document.getElementById('time-period').addEventListener('change', function() {
            if (selectedMeterId) {
                loadMeterData(selectedMeterId);
            }
        });
        
        // Export button handler
        document.getElementById('export-data').addEventListener('click', function() {
            if (selectedMeterId) {
                // In a real implementation, this would trigger a download
                alert('Exporting data for meter ID: ' + selectedMeterId);
            }
        });
        
        // Function to load meter data
        function loadMeterData(meterId) {
            // In a real implementation, this would fetch data from the server
            // For this example, we'll use dummy data
            
            // Simulate API call delay
            setTimeout(() => {
                // Update meter information
                document.getElementById('info-serial').textContent = 'M' + Math.floor(1000000 + Math.random() * 9000000);
                document.getElementById('info-type').textContent = ['Analog', 'Digital', 'Smart'][Math.floor(Math.random() * 3)];
                document.getElementById('info-install-date').textContent = '2022-' + (Math.floor(Math.random() * 12) + 1).toString().padStart(2, '0') + '-' + (Math.floor(Math.random() * 28) + 1).toString().padStart(2, '0');
                document.getElementById('info-maintenance').textContent = '2023-' + (Math.floor(Math.random() * 12) + 1).toString().padStart(2, '0') + '-' + (Math.floor(Math.random() * 28) + 1).toString().padStart(2, '0');
                document.getElementById('info-status').textContent = ['Active', 'Needs Maintenance', 'Recently Serviced'][Math.floor(Math.random() * 3)];
                document.getElementById('info-plan').textContent = ['Standard', 'Premium', 'Business'][Math.floor(Math.random() * 3)];
                document.getElementById('info-location').textContent = ['123 Main St, Apartment 4B', '456 Oak Avenue', '789 Pine Road, Suite 101'][Math.floor(Math.random() * 3)];
                document.getElementById('info-gps').textContent = (Math.random() * 90).toFixed(6) + ', ' + (Math.random() * 180).toFixed(6);
                document.getElementById('info-notes').textContent = ['Meter installed in basement', 'Accessible from outside', 'Requires key for access'][Math.floor(Math.random() * 3)];
                
                // Generate reading history data
                const timePeriod = document.getElementById('time-period').value;
                const months = timePeriod === 'all' ? 24 : parseInt(timePeriod);
                const readings = generateReadingData(months);
                
                // Update statistics
                document.getElementById('avg-consumption').textContent = Math.floor(readings.reduce((sum, r) => sum + r.consumption, 0) / readings.length);
                const highestReading = readings.reduce((max, r) => r.value > max.value ? r : max, { value: 0 });
                document.getElementById('highest-reading').textContent = highestReading.value;
                document.getElementById('highest-reading-date').textContent = 'on ' + highestReading.date;
                document.getElementById('total-readings').textContent = readings.length;
                
                // Update readings table
                const tableBody = document.getElementById('readings-history-table');
                tableBody.innerHTML = '';
                
                readings.forEach(reading => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50';
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${reading.date}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${reading.value}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${reading.consumption}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge ${reading.statusClass}">
                                ${reading.status}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${reading.recorder}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="#" class="btn btn-outline text-xs">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
                
                // Update service history
                const serviceContainer = document.getElementById('service-history-container');
                serviceContainer.innerHTML = '';
                
                if (Math.random() > 0.3) { // 70% chance to have service history
                    const serviceCount = Math.floor(Math.random() * 4) + 1;
                    const services = [];
                    
                    for (let i = 0; i < serviceCount; i++) {
                        const serviceDate = new Date();
                        serviceDate.setMonth(serviceDate.getMonth() - Math.floor(Math.random() * 12));
                        
                        services.push({
                            date: serviceDate.toISOString().split('T')[0],
                            type: ['Maintenance', 'Repair', 'Inspection', 'Replacement'][Math.floor(Math.random() * 4)],
                            description: ['Routine maintenance check', 'Fixed leaking connection', 'Replaced damaged parts', 'Calibration adjustment'][Math.floor(Math.random() * 4)],
                            technician: ['John Doe', 'Jane Smith', 'Robert Johnson'][Math.floor(Math.random() * 3)]
                        });
                    }
                    
                    // Sort by date (newest first)
                    services.sort((a, b) => new Date(b.date) - new Date(a.date));
                    
                    services.forEach(service => {
                        const serviceItem = document.createElement('div');
                        serviceItem.className = 'border-b border-gray-200 pb-4 mb-4 last:border-0 last:pb-0 last:mb-0';
                        serviceItem.innerHTML = `
                            <div class="flex items-start">
                                <div class="flex-shrink-0 bg-blue-100 rounded-full p-2">
                                    <i class="fas fa-tools text-blue-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">${service.type}</p>
                                    <p class="text-xs text-gray-500">${service.date}</p>
                                    <p class="text-sm text-gray-700 mt-1">${service.description}</p>
                                    <p class="text-xs text-gray-500 mt-1">Technician: ${service.technician}</p>
                                </div>
                            </div>
                        `;
                        serviceContainer.appendChild(serviceItem);
                    });
                } else {
                    serviceContainer.innerHTML = `
                        <div class="text-center py-4">
                            <p class="text-gray-500">No service history available</p>
                        </div>
                    `;
                }
                
                // Update consumption chart
                updateConsumptionChart(readings);
                
            }, 500); // Simulate loading delay
        }
        
        // Function to generate reading data
        function generateReadingData(months) {
            const readings = [];
            const today = new Date();
            let currentValue = 1000 + Math.floor(Math.random() * 1000);
            
            for (let i = months - 1; i >= 0; i--) {
                const readingDate = new Date(today);
                readingDate.setMonth(today.getMonth() - i);
                
                // Add some randomness to the day
                readingDate.setDate(Math.floor(Math.random() * 28) + 1);
                
                const consumption = Math.floor(Math.random() * 100) + 50;
                currentValue += consumption;
                
                const statusOptions = [
                    { status: 'Verified', class: 'badge-success' },
                    { status: 'Pending', class: 'badge-warning' },
                    { status: 'Flagged', class: 'badge-danger' }
                ];
                
                // Most readings should be verified
                const statusIndex = Math.random() < 0.8 ? 0 : (Math.random() < 0.5 ? 1 : 2);
                const status = statusOptions[statusIndex];
                
                readings.push({
                    date: readingDate.toISOString().split('T')[0],
                    value: currentValue,
                    consumption: consumption,
                    status: status.status,
                    statusClass: status.class,
                    recorder: ['John Doe', 'Jane Smith', 'Robert Johnson'][Math.floor(Math.random() * 3)]
                });
            }
            
            return readings.sort((a, b) => new Date(b.date) - new Date(a.date)); // Sort by date (newest first)
        }
        
        // Function to update consumption chart
        function updateConsumptionChart(readings) {
            // Sort readings by date (oldest first for the chart)
            const sortedReadings = [...readings].sort((a, b) => new Date(a.date) - new Date(b.date));
            
            const labels = sortedReadings.map(r => r.date);
            const consumptionData = sortedReadings.map(r => r.consumption);
            const readingData = sortedReadings.map(r => r.value);
            
            const ctx = document.getElementById('consumption-chart').getContext('2d');
            
            // Destroy previous chart if it exists
            if (consumptionChart) {
                consumptionChart.destroy();
            }
            
            // Create new chart
            consumptionChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Consumption',
                            data: consumptionData,
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Meter Reading',
                            data: readingData,
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: false,
                            hidden: true // Hidden by default
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            padding: 10,
                            cornerRadius: 4,
                            boxPadding: 3
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>