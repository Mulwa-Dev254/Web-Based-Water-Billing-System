<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collector Dashboard - AquaBill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .dashboard-card {
            background-color: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.2s ease-in-out;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .dashboard-card .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .dashboard-card .value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .dashboard-card .label {
            font-size: 1rem;
            color: #64748b;
        }
    </style>
</head>
<body class="flex h-screen bg-gray-100">
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="flex justify-between items-center p-6 bg-white border-b border-gray-200">
            <h1 class="text-3xl font-bold text-gray-900">Collector Dashboard</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700">Welcome, <span class="font-semibold text-blue-700"><?php echo htmlspecialchars($data['username']); ?></span></span>
                <a href="index.php?page=logout" class="text-red-600 hover:text-red-800 flex items-center">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="dashboard-card text-blue-600">
                    <div class="icon"><i class="fas fa-water"></i></div>
                    <div class="value"><?php echo count($data['assignedMeters']); ?></div>
                    <div class="label">Assigned Meters</div>
                </div>
                <div class="dashboard-card text-yellow-600">
                    <div class="icon"><i class="fas fa-tasks"></i></div>
                    <div class="value"><?php echo count($data['pendingServiceRequests']); ?></div>
                    <div class="label">Pending Service Requests</div>
                </div>
                <div class="dashboard-card text-green-600">
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                    <div class="value"><?php echo (int)($data['totalReadingsToday'] ?? 0); ?></div>
                    <div class="label">Readings Today</div>
                </div>
                <div class="dashboard-card text-indigo-600">
                    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                    <div class="value"><?php echo (int)($data['totalReadingsThisMonth'] ?? 0); ?></div>
                    <div class="label">Readings This Month</div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Assigned Meters Overview</h2>
                <?php if (!empty($data['assignedMeters'])): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                    <th class="py-3 px-6 text-left">Serial Number</th>
                                    <th class="py-3 px-6 text-left">Client</th>
                                    <th class="py-3 px-6 text-left">Status</th>
                                    <th class="py-3 px-6 text-left">Next Update</th>
                                    <th class="py-3 px-6 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm font-light">
                                <?php foreach ($data['assignedMeters'] as $meter): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                                        <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo htmlspecialchars($meter['serial_number']); ?></td>
                                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($meter['client_username']); ?></td>
                                        <td class="py-3 px-6 text-left">
                                            <span class="relative inline-block px-3 py-1 font-semibold leading-tight">
                                                <span aria-hidden="true" class="absolute inset-0 <?php echo ($meter['status'] == 'active' ? 'bg-green-200' : 'bg-red-200'); ?> opacity-50 rounded-full"></span>
                                                <span class="relative text-xs <?php echo ($meter['status'] == 'active' ? 'text-green-900' : 'text-red-900'); ?>"><?php echo htmlspecialchars(ucfirst($meter['status'])); ?></span>
                                            </span>
                                        </td>
                                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($meter['next_update_date'] ?? 'N/A'); ?></td>
                                        <td class="py-3 px-6 text-center">
                                            <a href="index.php?page=collector_record_reading&meter_id=<?php echo $meter['id']; ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-xs">Record Reading</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600">No meters currently assigned to you.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Pending Service Requests</h2>
                <?php if (!empty($data['pendingServiceRequests'])): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                    <th class="py-3 px-6 text-left">Request ID</th>
                                    <th class="py-3 px-6 text-left">Client</th>
                                    <th class="py-3 px-6 text-left">Service</th>
                                    <th class="py-3 px-6 text-left">Description</th>
                                    <th class="py-3 px-6 text-left">Status</th>
                                    <th class="py-3 px-6 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm font-light">
                                <?php foreach ($data['pendingServiceRequests'] as $request): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($request['id']); ?></td>
                                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($request['client_username']); ?></td>
                                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($request['service_name']); ?></td>
                                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($request['description']); ?></td>
                                        <td class="py-3 px-6 text-left">
                                            <span class="relative inline-block px-3 py-1 font-semibold leading-tight">
                                                <span aria-hidden="true" class="absolute inset-0 <?php echo ($request['status'] == 'assigned' ? 'bg-yellow-200' : 'bg-gray-200'); ?> opacity-50 rounded-full"></span>
                                                <span class="relative text-xs <?php echo ($request['status'] == 'assigned' ? 'text-yellow-900' : 'text-gray-900'); ?>"><?php echo htmlspecialchars(ucfirst($request['status'])); ?></span>
                                            </span>
                                        </td>
                                        <td class="py-3 px-6 text-center">
                                            <a href="index.php?page=collector_update_service&request_id=<?php echo $request['id']; ?>" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-xs">Update Status</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600">No pending service requests assigned to you.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
