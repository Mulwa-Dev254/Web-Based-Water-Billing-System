<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Records - AquaBill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .record-section {
            background-color: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .table-header {
            background-color: #edf2f7;
            color: #4a5568;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        .table-row:nth-child(even) {
            background-color: #f7fafc;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            #printable-area, #printable-area * {
                visibility: visible;
            }
            #printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 1rem;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="flex h-screen bg-gray-100">
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="flex justify-between items-center p-6 bg-white border-b border-gray-200 no-print">
            <h1 class="text-3xl font-bold text-gray-900">My Records</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700">Welcome, <span class="font-semibold text-blue-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span></span>
                <a href="index.php?page=logout" class="text-red-600 hover:text-red-800 flex items-center">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="max-w-6xl mx-auto" id="printable-area">
                <div class="mb-6 flex justify-between items-center no-print">
                    <div>
                        <form method="GET" action="index.php" class="flex space-x-4">
                            <input type="hidden" name="page" value="meter_reader_records">
                            <div class="flex flex-col">
                                <label for="date_from" class="text-sm text-gray-600 mb-1">From Date</label>
                                <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($data['dateFrom']); ?>" class="border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div class="flex flex-col">
                                <label for="date_to" class="text-sm text-gray-600 mb-1">To Date</label>
                                <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($data['dateTo']); ?>" class="border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div class="flex flex-col">
                                <label for="filter_type" class="text-sm text-gray-600 mb-1">Filter Type</label>
                                <select id="filter_type" name="filter_type" class="border border-gray-300 rounded-md px-3 py-2">
                                    <option value="all" <?php echo $data['filterType'] == 'all' ? 'selected' : ''; ?>>All Records</option>
                                    <option value="readings" <?php echo $data['filterType'] == 'readings' ? 'selected' : ''; ?>>Meter Readings Only</option>
                                    <option value="services" <?php echo $data['filterType'] == 'services' ? 'selected' : ''; ?>>Service Attendances Only</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md">
                                    <i class="fas fa-filter mr-2"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center">
                        <i class="fas fa-print mr-2"></i> Print Records
                    </button>
                </div>

                <?php if ($data['filterType'] == 'all' || $data['filterType'] == 'readings'): ?>
                <div class="record-section">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Meter Readings</h2>
                    <?php if (!empty($data['meterReadings'])): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead>
                                    <tr class="table-header">
                                        <th class="py-3 px-4 text-left">Reading ID</th>
                                        <th class="py-3 px-4 text-left">Meter SN</th>
                                        <th class="py-3 px-4 text-left">Client</th>
                                        <th class="py-3 px-4 text-left">Reading Value</th>
                                        <th class="py-3 px-4 text-left">Date</th>
                                        <th class="py-3 px-4 text-left">GPS</th>
                                        <th class="py-3 px-4 text-center no-print">Photo</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700 text-sm">
                                    <?php foreach ($data['meterReadings'] as $reading): ?>
                                        <tr class="border-b border-gray-200 table-row">
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($reading['id']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($reading['serial_number']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($reading['client_username']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($reading['reading_value']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($reading['reading_date']))); ?></td>
                                            <td class="py-3 px-4"><?php
                                                $gps = '';
                                                if (!empty($reading['gps_location'])) {
                                                    $gps = $reading['gps_location'];
                                                } elseif (isset($reading['latitude']) && isset($reading['longitude']) && $reading['latitude'] !== null && $reading['longitude'] !== null) {
                                                    $gps = $reading['latitude'] . ',' . $reading['longitude'];
                                                } else {
                                                    $gps = 'N/A';
                                                }
                                                echo htmlspecialchars($gps);
                                            ?></td>
                                            <td class="py-3 px-4 text-center no-print">
                                                <?php if (!empty($reading['photo_url'])): ?>
                                                    <img src="<?php echo htmlspecialchars($reading['photo_url']); ?>" alt="Meter Photo" class="w-16 h-16 object-cover rounded-md cursor-pointer" onclick="window.open(this.src)">
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600">No meter readings recorded yet.</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($data['filterType'] == 'all' || $data['filterType'] == 'services'): ?>
                <div class="record-section mt-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Service Attendances</h2>
                    <?php if (!empty($data['serviceAttendances'])): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead>
                                    <tr class="table-header">
                                        <th class="py-3 px-4 text-left">Attendance ID</th>
                                        <th class="py-3 px-4 text-left">Request ID</th>
                                        <th class="py-3 px-4 text-left">Client</th>
                                        <th class="py-3 px-4 text-left">Service</th>
                                        <th class="py-3 px-4 text-left">Date</th>
                                        <th class="py-3 px-4 text-left">Status After</th>
                                        <th class="py-3 px-4 text-left">Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700 text-sm">
                                    <?php foreach ($data['serviceAttendances'] as $attendance): ?>
                                        <tr class="border-b border-gray-200 table-row">
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($attendance['id']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($attendance['service_request_id']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($attendance['client_username']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($attendance['service_name']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($attendance['attendance_date']))); ?></td>
                                            <td class="py-3 px-4">
                                                <span class="relative inline-block px-3 py-1 font-semibold leading-tight">
                                                    <span aria-hidden="true" class="absolute inset-0 <?php
                                                        if (($attendance['status_update'] ?? '') == 'serviced') echo 'bg-green-200';
                                                        else if (($attendance['status_update'] ?? '') == 'confirmed') echo 'bg-blue-200';
                                                        else if (($attendance['status_update'] ?? '') == 'unable to complete') echo 'bg-red-200';
                                                        else echo 'bg-gray-200';
                                                    ?> opacity-50 rounded-full"></span>
                                                    <span class="relative text-xs <?php
                                                        if (($attendance['status_update'] ?? '') == 'serviced') echo 'text-green-900';
                                                        else if (($attendance['status_update'] ?? '') == 'confirmed') echo 'text-blue-900';
                                                        else if (($attendance['status_update'] ?? '') == 'unable to complete') echo 'text-red-900';
                                                        else echo 'text-gray-900';
                                                    ?>"><?php echo htmlspecialchars(ucfirst($attendance['status_update'] ?? '')); ?></span>
                                                </span>
                                            </td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($attendance['notes']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600">No service attendances recorded yet.</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
