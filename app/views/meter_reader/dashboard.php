<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meter Reader Dashboard</title>
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
        .stat-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        .meter-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .meter-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
    <style>
        .loader-overlay{position:fixed;inset:0;z-index:2000;background:linear-gradient(120deg,rgba(255,255,255,.8),rgba(255,255,255,.6));backdrop-filter:saturate(180%) blur(8px);display:flex;align-items:center;justify-content:center;transition:opacity .4s ease}
        .loader-overlay.loader-hidden{opacity:0;pointer-events:none}
        .spinner{position:relative;width:10em;height:10em}
        .spinner:before{transform:rotateX(60deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateBefore infinite linear reverse}
        .spinner:after{transform:rotateX(240deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateAfter infinite linear}
        .spinner:before,.spinner:after{box-sizing:border-box;content:'';display:block;position:absolute;top:50%;left:50%;margin-top:-5em;margin-left:-5em;width:10em;height:10em;transform-style:preserve-3d;transform-origin:50%;perspective-origin:50% 50%;perspective:340px;background-size:10em 10em;background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjI2NnB4IiBoZWlnaHQ9IjI5N3B4IiB2aWV3Qm94PSIwIDAgMjY2IDI5NyIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWxuczpza2V0Y2g9Imh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaC9ucyI+CiAgICA8dGl0bGU+c3Bpbm5lcjwvdGl0bGU+CiAgICA8ZGVzY3JpcHRpb24+Q3JlYXRlZCB3aXRoIFNrZXRjaCAoaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoKTwvZGVzY3JpcHRpb24+CiAgICA8ZGVmcz48L2RlZnM+CiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBza2V0Y2g6dHlwZT0iTVNQYWdlIj4KICAgICAgICA8cGF0aCBkPSJNMTcxLjUwNzgxMywzLjI1MDAwMDM4IEMyMjYuMjA4MTgzLDEyLjg1NzcxMTEgMjk3LjExMjcyMiw3MS40OTEyODIzIDI1MC44OTU1OTksMTA4LjQxMDE1NSBDMjE2LjU4MjAyNCwxMzUuODIwMzEgMTg2LjUyODQwNSw5Ny4wNjI0OTY0IDE1Ni44MDA3NzQsODUuNzczNDM0NiBDMTI3LjA3MzE0Myw3NC40ODQzNzIxIDc2Ljg4ODQ2MzIsODQuMjE2MTQ2MiA2MC4xMjg5MDY1LDEwOC40MTAxNTMgQy0xNS45ODA0Njg1LDIxOC4yODEyNDcgMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IDE0NS4yNzczNDQsMjk2LjY2Nzk2OCBDMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IC0yNS40NDkyMTg3LDI1Ny4yNDIxOTggMy4zOTg0Mzc1LDEwOC40MTAxNTUgQzE2LjMwNzA2NjEsNDEuODExNDE3NCA4NC43Mjc1ODI5LC0xMS45OTIyOTg1IDE3MS41MDc4MTMsMy4yNTAwMDAzOCBaIiBpZD0iUGF0aC0xIiBmaWxsPSIjMDAwMDAwIiBza2V0Y2g6dHlwZT0iTVNTaGFwZUdyb3VwIj48L3BhdGg+CiAgICA8L2c+Cjwvc3ZnPg==)}
        @keyframes rotateBefore{from{transform:rotateX(60deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(60deg) rotateY(45deg) rotateZ(-360deg)}}
        @keyframes rotateAfter{from{transform:rotateX(240deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(240deg) rotateY(45deg) rotateZ(360deg)}}
    </style>
    <script>window.addEventListener('load',()=>{const l=document.getElementById('loader');if(l){setTimeout(()=>{l.classList.add('loader-hidden');setTimeout(()=>{try{l.remove()}catch(e){}},600);},1500)}});</script>
</head>
<body>
    <div id='loader' class='loader-overlay'><div class='spinner'></div></div>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include_once __DIR__ . '/sidebar.php'; ?>

        <div class="main-content">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Welcome, <?= htmlspecialchars($data['username']) ?>!</h1>
                <p class="text-gray-600">Here's your activity summary for today</p>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="stat-card p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-blue-100 p-3 mr-4">
                            <i class="fas fa-tachometer-alt text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Assigned Meters</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= count($data['assignedMeters']) ?></h3>
                        </div>
                    </div>
                </div>

                <div class="stat-card p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-green-100 p-3 mr-4">
                            <i class="fas fa-clipboard-check text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Pending Requests</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= count($data['pendingServiceRequests']) ?></h3>
                        </div>
                    </div>
                </div>

                <div class="stat-card p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-purple-100 p-3 mr-4">
                            <i class="fas fa-calendar-day text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Readings Today</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $data['totalReadingsToday'] ?></h3>
                        </div>
                    </div>
                </div>

                <div class="stat-card p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-yellow-100 p-3 mr-4">
                            <i class="fas fa-calendar-alt text-yellow-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Readings This Month</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $data['totalReadingsThisMonth'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Assigned Meters -->
                <div class="card col-span-2">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold text-gray-800">Assigned Meters</h2>
                        <a href="index.php?page=meter_reader_record_reading" class="btn btn-primary text-sm">
                            <i class="fas fa-plus mr-2"></i> Record Reading
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($data['assignedMeters'])): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-clipboard-list text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500">No meters assigned to you yet</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial Number</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach (array_slice($data['assignedMeters'], 0, 5) as $meter): ?>
                                            <tr class="hover:bg-gray-50 meter-card">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($meter['serial_number']) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500"><?= htmlspecialchars(ucfirst($meter['meter_type'])) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php if ($meter['client_id']): ?>
                                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($meter['client_name'] ?? $meter['client_username']) ?></div>
                                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($meter['client_email'] ?? '') ?></div>
                                                    <?php else: ?>
                                                        <span class="text-sm text-gray-500">Not assigned</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php 
                                                    // Normalize and determine composite status for display
                                                    $rawStatus = strtolower(trim($meter['status'] ?? ''));
                                                    $isWaiting = in_array($rawStatus, ['pending_installation', 'waiting_installation', 'waiting installation'], true);
                                                    $isRecorded = !empty($meter['last_reading_date']);
                                                    $isInstalled = !$isWaiting && !$isRecorded; // treat as installed when not waiting and not recorded

                                                    if ($isWaiting) {
                                                        $statusClass = 'badge-warning';
                                                        $statusIcon = 'fas fa-tools';
                                                        $statusText = 'Waiting installation';
                                                    } elseif ($isRecorded) {
                                                        $statusClass = 'badge-info';
                                                        $statusIcon = 'fas fa-check-circle';
                                                        $statusText = 'Recorded';
                                                    } else { // installed (default)
                                                        $statusClass = 'badge-success';
                                                        $statusIcon = 'fas fa-check';
                                                        $statusText = 'Installed';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $statusClass ?> flex items-center space-x-2">
                                                        <i class="<?= $statusIcon ?>"></i>
                                                        <span><?= htmlspecialchars($statusText) ?></span>
                                                    </span>
                                                    <?php if ($isRecorded && !empty($meter['last_reading_date'])): ?>
                                                        <div class="text-xs text-gray-500 mt-1">
                                                            <?= htmlspecialchars(date('M d, Y H:i', strtotime($meter['last_reading_date']))) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <?php if ($isWaiting): ?>
                                                        <a href="index.php?page=meter_reader_installations&meter_id=<?= $meter['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                            <i class="fas fa-tools"></i> Install
                                                        </a>
                                                    <?php elseif ($isInstalled): ?>
                                                        <a href="index.php?page=meter_reader_record_reading&meter_id=<?= $meter['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                                            <i class="fas fa-camera"></i> Record
                                                        </a>
                                                    <?php else: // recorded ?>
                                                        <span class="text-gray-600 mr-3">
                                                            <i class="fas fa-check-circle"></i> Recorded
                                                            <span class="block text-xs text-gray-500"><?= htmlspecialchars(date('M d, Y H:i', strtotime($meter['last_reading_date']))) ?></span>
                                                        </span>
                                                    <?php endif; ?>
                                                    <a href="index.php?page=meter_reader_view_meter_history&meter_id=<?= $meter['id'] ?>" class="text-gray-600 hover:text-gray-900">
                                                        <i class="fas fa-history"></i> History
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (count($data['assignedMeters']) > 5): ?>
                                <div class="mt-4 text-center">
                                    <a href="index.php?page=meter_reader_view_meters" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View all <?= count($data['assignedMeters']) ?> meters <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Service Requests -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold text-gray-800">Pending Requests</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($data['pendingServiceRequests'])): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-check-circle text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500">No pending service requests</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach (array_slice($data['pendingServiceRequests'], 0, 5) as $request): ?>
                                    <div class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-150">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="text-sm font-medium text-gray-900"><?= htmlspecialchars($request['service_type']) ?></h3>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <i class="fas fa-user mr-1"></i> <?= htmlspecialchars($request['client_name'] ?? $request['client_username'] ?? 'Unknown Client') ?>
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <i class="fas fa-calendar mr-1"></i> <?= htmlspecialchars(date('M d, Y', strtotime($request['created_at']))) ?>
                                                </p>
                                            </div>
                                            <span class="badge badge-warning"><?= htmlspecialchars(ucfirst($request['status'])) ?></span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-2"><?= htmlspecialchars($request['description']) ?></p>
                                        <div class="mt-3">
                                            <a href="index.php?page=meter_reader_update_service&request_id=<?= $request['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                Update Status <i class="fas fa-arrow-right ml-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($data['pendingServiceRequests']) > 5): ?>
                                <div class="mt-4 text-center">
                                    <a href="index.php?page=meter_reader_service_requests" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View all <?= count($data['pendingServiceRequests']) ?> requests <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Reading Statistics Chart -->
            <div class="card mt-6">
                <div class="card-header" style="padding: 0.75rem 1rem;">
                    <h2 class="text-lg font-semibold text-gray-800">Reading Statistics</h2>
                    <div class="flex items-center space-x-2">
                        <label for="chartMode" class="text-sm text-gray-600">View:</label>
                        <select id="chartMode" class="form-control py-2 text-sm">
                            <option value="monthly" selected>Monthly</option>
                            <option value="daily">Daily</option>
                            <option value="annual">Annual</option>
                        </select>
                    </div>
                </div>
                <div class="card-body" style="padding: 0.75rem 1rem;">
                    <canvas id="readingsChart" height="180"></canvas>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="index.php?page=meter_reader_record_reading" class="card p-6 text-center hover:bg-blue-50 transition duration-200">
                        <div class="rounded-full bg-blue-100 w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-camera text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="text-gray-800 font-medium">Record Reading</h3>
                        <p class="text-gray-500 text-sm mt-2">Submit new meter readings</p>
                    </a>
                    
                    <a href="index.php?page=meter_reader_update_service" class="card p-6 text-center hover:bg-green-50 transition duration-200">
                        <div class="rounded-full bg-green-100 w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clipboard-check text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="text-gray-800 font-medium">Update Service</h3>
                        <p class="text-gray-500 text-sm mt-2">Update service request status</p>
                    </a>
                    
                    <a href="index.php?page=meter_reader_update_gps_location" class="card p-6 text-center hover:bg-yellow-50 transition duration-200">
                        <div class="rounded-full bg-yellow-100 w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-map-marker-alt text-yellow-600 text-2xl"></i>
                        </div>
                        <h3 class="text-gray-800 font-medium">Update GPS</h3>
                        <p class="text-gray-500 text-sm mt-2">Update meter GPS locations</p>
                    </a>
                    
                    <a href="index.php?page=meter_reader_records" class="card p-6 text-center hover:bg-purple-50 transition duration-200">
                        <div class="rounded-full bg-purple-100 w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-file-alt text-purple-600 text-2xl"></i>
                        </div>
                        <h3 class="text-gray-800 font-medium">View Records</h3>
                        <p class="text-gray-500 text-sm mt-2">View your reading history</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Accurate data provided by the backend
        const chartDailyLabels = <?= json_encode($data['chartDailyLabels'] ?? []) ?>;
        const chartDailyCounts = <?= json_encode($data['chartDailyCounts'] ?? []) ?>;
        const chartMonthlyLabels = <?= json_encode($data['chartMonthlyLabels'] ?? []) ?>;
        const chartMonthlyCounts = <?= json_encode($data['chartMonthlyCounts'] ?? []) ?>;
        const chartAnnualLabels = <?= json_encode($data['chartAnnualLabels'] ?? []) ?>;
        const chartAnnualCounts = <?= json_encode($data['chartAnnualCounts'] ?? []) ?>;

        const ctx = document.getElementById('readingsChart').getContext('2d');

        function buildDataset(mode) {
            if (mode === 'daily') {
                return {
                    labels: chartDailyLabels,
                    counts: chartDailyCounts,
                    bg: 'rgba(59, 130, 246, 0.5)',
                    border: 'rgba(59, 130, 246, 1)',
                    xTitle: 'Day'
                };
            }
            if (mode === 'annual') {
                return {
                    labels: chartAnnualLabels,
                    counts: chartAnnualCounts,
                    bg: 'rgba(245, 158, 11, 0.5)',
                    border: 'rgba(245, 158, 11, 1)',
                    xTitle: 'Year'
                };
            }
            return {
                labels: chartMonthlyLabels,
                counts: chartMonthlyCounts,
                bg: 'rgba(16, 185, 129, 0.5)',
                border: 'rgba(16, 185, 129, 1)',
                xTitle: 'Month'
            };
        }

        let currentMode = 'monthly';
        let ds = buildDataset(currentMode);
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ds.labels,
                datasets: [{
                    label: 'Meter Readings',
                    data: ds.counts,
                    backgroundColor: ds.bg,
                    borderColor: ds.border,
                    borderWidth: 1,
                    maxBarThickness: 14
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true }
                },
                layout: { padding: 0 },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Number of Readings', font: { size: 12 } },
                        ticks: { font: { size: 10 } }
                    },
                    x: {
                        title: { display: true, text: ds.xTitle, font: { size: 12 } },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });

        document.getElementById('chartMode').addEventListener('change', function(){
            currentMode = this.value;
            ds = buildDataset(currentMode);
            chart.data.labels = ds.labels;
            chart.data.datasets[0].data = ds.counts;
            chart.data.datasets[0].backgroundColor = ds.bg;
            chart.data.datasets[0].borderColor = ds.border;
            chart.options.scales.x.title.text = ds.xTitle;
            chart.update();
        });
    </script>
</body>
</html>
