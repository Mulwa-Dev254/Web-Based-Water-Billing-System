<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Reports</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --dark-bg: #1e1e2d;
            --darker-bg: #151521;
            --sidebar-bg: #1a1a27;
            --card-bg: #2a2a3c;
            --text-light: #f8f9fa;
            --text-muted: #a1a5b7;
            --border-color: #2d2d3a;
            --success: #10b981;
            --info: #3b82f6;
            --warning: #f59e0b;
            --danger: #ef4444;
            --purple: #8b5cf6;
        }

        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Dashboard Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 16rem; /* Width of sidebar */
            padding: 1.5rem;
        }

        /* Card Styles */
        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Filter Controls */
        .filter-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background-color: #f9fafb;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .filter-group select,
        .filter-group input {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .filter-buttons {
            display: flex;
            align-items: flex-end;
            gap: 0.5rem;
        }

        /* Buttons */
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
            background-color: var(--primary);
            color: white;
            border: 1px solid var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid #d1d5db;
            color: #4b5563;
        }

        .btn-outline:hover {
            background-color: #f3f4f6;
        }

        .btn-success {
            background-color: var(--success);
            color: white;
            border: 1px solid var(--success);
        }

        .btn-success:hover {
            background-color: #059669;
            border-color: #059669;
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 1.5rem;
        }

        /* Report Tables */
        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        .report-table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .report-table tr:last-child td {
            border-bottom: none;
        }

        /* Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-card {
            background-color: #f9fafb;
            border-radius: 0.5rem;
            padding: 1rem;
            border: 1px solid #e5e7eb;
        }

        .summary-card .title {
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .summary-card .value {
            font-size: 1.5rem;
            font-weight: 600;
            color: #111827;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border-left: 4px solid var(--success);
            color: var(--success);
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--danger);
            color: var(--danger);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .filter-controls {
                flex-direction: column;
            }

            .summary-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include_once __DIR__ . '/partials/sidebar.php'; ?>

        <div class="main-content">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Financial Reports</h1>
                <p class="text-gray-600">Generate and view financial reports</p>
            </div>

            <!-- Alert Messages -->
            <?php if (!empty($data['error'])): ?>
                <div class="alert alert-error">
                    <p><?= htmlspecialchars($data['error']) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($data['success'])): ?>
                <div id="successOverlay" style="position:fixed;inset:0;background:rgba(0,0,0,0.55);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:2000;">
                    <div style="width:420px;max-width:90vw;background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.3);overflow:hidden;">
                        <div style="padding:16px 18px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
                            <div style="font-weight:600;color:#0f766e;">Payment Recorded</div>
                            <button id="closeSuccessDialog" style="background:none;border:none;font-size:18px;cursor:pointer;color:#4b5563;">×</button>
                        </div>
                        <div style="padding:16px 18px;">
                            <div style="font-size:14px;color:#374151;">
                                <strong>Success!</strong> <?= htmlspecialchars($data['success']) ?>
                            </div>
                        </div>
                        <div style="padding:12px 18px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:10px;">
                            <a class="btn btn-outline" href="index.php?page=finance_manager_dashboard">Close</a>
                            <a class="btn btn-primary" href="index.php?page=finance_manager_reports&report_type=clients&date_from=<?= htmlspecialchars($data['startDate']) ?>&date_to=<?= htmlspecialchars($data['endDate']) ?>">View Payments</a>
                        </div>
                    </div>
                </div>
                <script>
                (function(){
                    var closeBtn = document.getElementById('closeSuccessDialog');
                    if (closeBtn) closeBtn.onclick = function(){ var ov = document.getElementById('successOverlay'); if (ov) ov.remove(); };
                    setTimeout(function(){ var ov = document.getElementById('successOverlay'); if (ov) ov.remove(); }, 7000);
                })();
                </script>
            <?php endif; ?>

            <!-- Report Generator Card -->
            <div class="card">
                <div class="card-header">
                    <h2>Generate Report</h2>
                </div>
                <div class="card-body">
                    <form action="index.php?page=finance_manager_reports" method="GET" class="filter-controls">
                        <input type="hidden" name="page" value="finance_manager_reports">
                        
                        <div class="filter-group">
                            <label for="report_type">Report Type</label>
                            <select name="report_type" id="report_type" class="w-48" required>
                                <option value="" disabled <?= !isset($_GET['report_type']) ? 'selected' : '' ?>>Select Report Type</option>
                                <option value="revenue" <?= isset($_GET['report_type']) && $_GET['report_type'] === 'revenue' ? 'selected' : '' ?>>Revenue Report</option>
                                <option value="bills" <?= isset($_GET['report_type']) && $_GET['report_type'] === 'bills' ? 'selected' : '' ?>>Bills Report</option>
                                <option value="clients" <?= isset($_GET['report_type']) && $_GET['report_type'] === 'clients' ? 'selected' : '' ?>>Client Payments</option>
                                <option value="flagged" <?= isset($_GET['report_type']) && $_GET['report_type'] === 'flagged' ? 'selected' : '' ?>>Flagged Transactions</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="date_from">Date From</label>
                            <input type="date" name="date_from" id="date_from" value="<?= $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days')) ?>" required>
                        </div>
                        
                        <div class="filter-group">
                            <label for="date_to">Date To</label>
                            <input type="date" name="date_to" id="date_to" value="<?= $_GET['date_to'] ?? date('Y-m-d') ?>" required>
                        </div>
                        
                        <div class="filter-group">
                            <label for="format">Format</label>
                            <select name="format" id="format" class="w-32">
                                <option value="web" <?= (!isset($_GET['format']) || $_GET['format'] === 'web') ? 'selected' : '' ?>>Web</option>
                                <option value="pdf" <?= isset($_GET['format']) && $_GET['format'] === 'pdf' ? 'selected' : '' ?>>PDF</option>
                                <option value="csv" <?= isset($_GET['format']) && $_GET['format'] === 'csv' ? 'selected' : '' ?>>CSV</option>
                            </select>
                        </div>
                        
                        <div class="filter-buttons">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-alt mr-2"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($data['report'])): ?>
            <!-- Report Results -->
            <div class="card">
                <div class="card-header">
                    <h2><?= htmlspecialchars($data['report_title']) ?></h2>
                    <?php if (isset($_GET['format']) && $_GET['format'] === 'web'): ?>
                    <div class="flex space-x-2">
                        <a href="<?= $_SERVER['REQUEST_URI'] ?>&format=pdf" class="btn btn-outline" target="_blank">
                            <i class="fas fa-file-pdf mr-2"></i> Export as PDF
                        </a>
                        <a href="<?= $_SERVER['REQUEST_URI'] ?>&format=csv" class="btn btn-outline">
                            <i class="fas fa-file-csv mr-2"></i> Export as CSV
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <!-- Report Period -->
                    <div class="mb-4 text-sm text-gray-500">
                        <p>Report Period: <?= htmlspecialchars(date('F d, Y', strtotime($_GET['date_from']))) ?> - <?= htmlspecialchars(date('F d, Y', strtotime($_GET['date_to']))) ?></p>
                        <p>Generated on: <?= htmlspecialchars(date('F d, Y H:i:s')) ?></p>
                    </div>

                    <!-- Summary Cards -->
                    <div class="summary-grid">
                        <?php foreach ($data['report_summary'] as $key => $value): ?>
                        <div class="summary-card">
                            <div class="title"><?= htmlspecialchars($key) ?></div>
                            <div class="value">
                                <?php if (strpos($key, 'Amount') !== false || strpos($key, 'Revenue') !== false || strpos($key, 'Total') !== false): ?>
                KSH <?= number_format($value, 2) ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($value) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Chart -->
                    <?php if (!empty($data['chart_data'])): ?>
                    <div class="chart-container">
                        <canvas id="reportChart"></canvas>
                    </div>
                    <?php endif; ?>

                    <!-- Report Table -->
                    <?php if (!empty($data['report_data'])): ?>
                    <div class="overflow-x-auto">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <?php foreach ($data['report_columns'] as $column): ?>
                                    <th><?= htmlspecialchars($column) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['report_data'] as $row): ?>
                                <tr>
                                    <?php foreach ($row as $key => $value): ?>
                                    <td>
                                        <?php if (strpos($key, 'amount') !== false || strpos($key, 'revenue') !== false || strpos($key, 'total') !== false): ?>
                KSH <?= number_format($value, 2) ?>
                                        <?php elseif (strpos($key, 'date') !== false): ?>
                                            <?= htmlspecialchars(date('M d, Y', strtotime($value))) ?>
                                        <?php elseif ($key === 'status'): ?>
                                            <span class="px-2 py-1 text-xs rounded-full <?= $value === 'completed' ? 'bg-green-100 text-green-800' : ($value === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($value === 'flagged' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800')) ?>">
                                                <?= ucfirst(htmlspecialchars($value)) ?>
                                            </span>
                                        <?php else: ?>
                                            <?= htmlspecialchars($value) ?>
                                        <?php endif; ?>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <p>No data available for the selected report criteria.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Saved Reports Card -->
            <div class="card">
                <div class="card-header">
                    <h2>Recent Reports</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['saved_reports'])): ?>
                    <div class="overflow-x-auto">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Report Name</th>
                                    <th>Type</th>
                                    <th>Date Range</th>
                                    <th>Generated On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['saved_reports'] as $report): ?>
                                <tr>
                                    <td class="font-medium"><?= htmlspecialchars($report['report_name']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($report['report_type'])) ?></td>
                                    <td><?= htmlspecialchars(date('M d, Y', strtotime($report['date_from']))) ?> - <?= htmlspecialchars(date('M d, Y', strtotime($report['date_to']))) ?></td>
                                    <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($report['created_at']))) ?></td>
                                    <td>
                                        <div class="flex space-x-2">
                                            <a href="index.php?page=finance_manager_view_report&id=<?= $report['report_id'] ?>" class="text-blue-600 hover:underline">View</a>
                                            <a href="index.php?page=finance_manager_download_report&id=<?= $report['report_id'] ?>&format=pdf" class="text-green-600 hover:underline">PDF</a>
                                            <a href="index.php?page=finance_manager_download_report&id=<?= $report['report_id'] ?>&format=csv" class="text-purple-600 hover:underline">CSV</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <p>No saved reports found. Generate a report to see it here.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            <?php if (!empty($data['chart_data'])): ?>
            // Initialize chart if data is available
            const ctx = document.getElementById('reportChart').getContext('2d');
            const reportChart = new Chart(ctx, {
                type: '<?= $data['chart_type'] ?>',
                data: {
                    labels: <?= json_encode($data['chart_data']['labels']) ?>,
                    datasets: [{
                        label: '<?= $data['chart_data']['dataset_label'] ?>',
                        data: <?= json_encode($data['chart_data']['dataset_data']) ?>,
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.5)',
                            'rgba(16, 185, 129, 0.5)',
                            'rgba(245, 158, 11, 0.5)',
                            'rgba(239, 68, 68, 0.5)',
                            'rgba(139, 92, 246, 0.5)'
                        ],
                        borderColor: [
                            'rgba(59, 130, 246, 1)',
                            'rgba(16, 185, 129, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(239, 68, 68, 1)',
                            'rgba(139, 92, 246, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'KSH ' + value;
                                }
                            }
                        }
                    }
                }
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>
