<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Service</title>
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
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
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
        .btn-success {
            background-color: #10b981;
            color: white;
            border: 1px solid #10b981;
        }
        .btn-success:hover {
            background-color: #059669;
            border-color: #059669;
        }
        .btn-danger {
            background-color: #ef4444;
            color: white;
            border: 1px solid #ef4444;
        }
        .btn-danger:hover {
            background-color: #dc2626;
            border-color: #dc2626;
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
        .service-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .service-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border-left: 4px solid #10b981;
            color: #065f46;
        }
        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            color: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include_once __DIR__ . '/sidebar.php'; ?>

        <div class="main-content">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Update Service</h1>
                <p class="text-gray-600">Manage and update service requests for assigned meters</p>
            </div>

            <!-- Alert Messages -->
            <?php if (!empty($data['error'])): ?>
                <div class="alert alert-error">
                    <p><?= htmlspecialchars($data['error']) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($data['success'])): ?>
                <div class="alert alert-success">
                    <p><?= htmlspecialchars($data['success']) ?></p>
                </div>
            <?php endif; ?>

            <!-- Service Requests List -->
            <?php if (empty($data['selected_request'])): ?>
                <div class="card mb-6">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold text-gray-800">Pending Service Requests</h2>
                        <div class="flex space-x-2">
                            <div class="relative">
                                <input type="text" id="search-input" placeholder="Search requests..." class="form-control pl-10 py-2 text-sm">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                            <select id="status-filter" class="form-control py-2 text-sm">
                                <option value="all">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="assigned">Assigned</option>
                                <option value="serviced">Serviced</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($data['service_requests'])): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-clipboard-check text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500">No pending service requests</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meter</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="service-requests-table">
                                        <?php foreach ($data['service_requests'] as $request): ?>
                                            <tr class="hover:bg-gray-50 service-card" data-status="<?= htmlspecialchars($request['status']) ?>">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">#<?= htmlspecialchars($request['id']) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($request['client_name'] ?? $request['client_username']) ?></div>
                                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($request['client_email'] ?? '') ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($request['meter_serial_number']) ?></div>
                                                    <div class="text-xs text-gray-500"><?= htmlspecialchars(ucfirst($request['meter_type'])) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $request['service_type']))) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?= htmlspecialchars(date('M d, Y', strtotime($request['request_date']))) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php 
                                                    $statusClass = '';
                                                    switch ($request['status']) {
                                                        case 'pending':
                                                            $statusClass = 'badge-warning';
                                                            break;
                                                        case 'in_progress':
                                                            $statusClass = 'badge-info';
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
                                                        <?= htmlspecialchars(ucfirst($request['status'])) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="index.php?page=meter_reader_update_service&request_id=<?= $request['id'] ?>" class="btn btn-primary text-xs">
                                                        <i class="fas fa-tools mr-1"></i> Update
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Update Service Request Form -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Request Details Card -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-800">Request Details</h2>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Request ID</h3>
                                    <p class="text-base font-semibold text-gray-900">#<?= htmlspecialchars($data['selected_request']['id']) ?></p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Service Type</h3>
                                    <p class="text-base text-gray-900"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $data['selected_request']['service_type']))) ?></p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Status</h3>
                                    <p>
                                        <?php 
                                        $statusClass = '';
                                        switch ($data['selected_request']['status']) {
                                            case 'pending':
                                                $statusClass = 'badge-warning';
                                                break;
                                            case 'in_progress':
                                                $statusClass = 'badge-info';
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
                                            <?= htmlspecialchars(ucfirst($data['selected_request']['status'])) ?>
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Request Date</h3>
                                    <p class="text-base text-gray-900"><?= htmlspecialchars(date('M d, Y', strtotime($data['selected_request']['request_date']))) ?></p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Client</h3>
                                    <p class="text-base text-gray-900"><?= htmlspecialchars($data['selected_request']['client_name'] ?? $data['selected_request']['client_username']) ?></p>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($data['selected_request']['client_email'] ?? '') ?></p>
                                    <?php if (!empty($data['selected_request']['client_phone'])): ?>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($data['selected_request']['client_phone']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Meter</h3>
                                    <p class="text-base text-gray-900"><?= htmlspecialchars($data['selected_request']['meter_serial_number']) ?></p>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars(ucfirst($data['selected_request']['meter_type'])) ?></p>
                                </div>
                                <?php if (!empty($data['selected_request']['description'])): ?>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500">Description</h3>
                                        <p class="text-base text-gray-900"><?= nl2br(htmlspecialchars($data['selected_request']['description'])) ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($data['selected_request']['assigned_date'])): ?>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500">Assigned Date</h3>
                                        <p class="text-base text-gray-900"><?= htmlspecialchars(date('M d, Y', strtotime($data['selected_request']['assigned_date']))) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Update Form Card -->
                    <div class="card col-span-2">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-800">Update Service Status</h2>
                            <a href="index.php?page=meter_reader_update_service" class="btn btn-outline text-sm">
                                <i class="fas fa-arrow-left mr-2"></i> Back to Requests
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="index.php?page=meter_reader_update_service" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="request_id" value="<?= $data['selected_request']['id'] ?>">
                                <input type="hidden" id="gps_location" name="gps_location">
                                <input type="hidden" id="photo_data" name="photo_data">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div class="form-group">
                                        <label for="new_status" class="form-label">Update Status <span class="text-red-500">*</span></label>
                                        <select id="new_status" name="new_status" class="form-control" required>
                                            <option value="serviced" <?= $data['selected_request']['status'] == 'serviced' ? 'selected' : '' ?>>Serviced</option>
                                            <option value="completed" <?= $data['selected_request']['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                            <option value="cancelled" <?= $data['selected_request']['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="update_date" class="form-label">Update Date <span class="text-red-500">*</span></label>
                                        <input type="date" id="update_date" name="update_date" class="form-control" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="work_performed" class="form-label">Work Performed <span class="text-red-500">*</span></label>
                                    <textarea id="work_performed" name="work_performed" class="form-control" rows="3" placeholder="Describe the work performed or actions taken..." required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="service_photo" class="form-label">Service Photo</label>
                                    <input type="file" id="service_photo" name="service_photo" class="form-control" accept="image/*">
                                    <small class="text-gray-500">Upload a photo of the completed service (if applicable)</small>
                                </div>
                                
                                <div class="form-group" id="completion_details_container">
                                    <label for="completion_details" class="form-label">Completion Details</label>
                                    <textarea id="completion_details" name="completion_details" class="form-control" rows="3" placeholder="Additional details about the service completion..."></textarea>
                                </div>
                                
                                <div class="form-group" id="cancellation_reason_container" style="display: none;">
                                    <label for="cancellation_reason" class="form-label">Cancellation Reason <span class="text-red-500">*</span></label>
                                    <textarea id="cancellation_reason" name="cancellation_reason" class="form-control" rows="3" placeholder="Reason for cancelling this service request..."></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="next_steps" class="form-label">Next Steps / Recommendations</label>
                                    <textarea id="next_steps" name="next_steps" class="form-control" rows="3" placeholder="Recommended next steps or future maintenance..."></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="confirm_update" name="confirm_update" class="mr-2" required>
                                        <label for="confirm_update">I confirm that this update is accurate and truthful</label>
                                    </div>
                                </div>
                                
                                <div class="mt-6 flex space-x-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-2"></i> Submit Update
                                    </button>
                                    <?php if ($data['selected_request']['status'] != 'completed'): ?>
                                        <button type="button" id="mark-completed-btn" class="btn btn-success">
                                            <i class="fas fa-check-circle mr-2"></i> Mark as Completed
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Handle status filter
        document.getElementById('status-filter').addEventListener('change', function() {
            const selectedStatus = this.value;
            const rows = document.querySelectorAll('#service-requests-table tr');
            
            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                if (selectedStatus === 'all' || rowStatus === selectedStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Handle search filter
        document.getElementById('search-input').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#service-requests-table tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Show/hide cancellation reason based on status selection
        const statusSelect = document.getElementById('new_status');
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                const cancellationContainer = document.getElementById('cancellation_reason_container');
                const completionContainer = document.getElementById('completion_details_container');
                
                if (this.value === 'cancelled') {
                    cancellationContainer.style.display = 'block';
                    document.getElementById('cancellation_reason').setAttribute('required', 'required');
                    completionContainer.style.display = 'none';
                } else if (this.value === 'completed') {
                    cancellationContainer.style.display = 'none';
                    document.getElementById('cancellation_reason').removeAttribute('required');
                    completionContainer.style.display = 'block';
                } else {
                    cancellationContainer.style.display = 'none';
                    document.getElementById('cancellation_reason').removeAttribute('required');
                    completionContainer.style.display = 'block';
                }
            });
        }
        
        // Handle mark as completed button
        const markCompletedBtn = document.getElementById('mark-completed-btn');
        if (markCompletedBtn) {
            markCompletedBtn.addEventListener('click', function() {
                const selectEl = document.getElementById('new_status');
                selectEl.value = 'completed';
                // Trigger the change event to update form fields
                const event = new Event('change');
                selectEl.dispatchEvent(event);
            });
        }

        // Capture GPS location
        (function captureGPS() {
            const gpsInput = document.getElementById('gps_location');
            if (!gpsInput) return;
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    pos => {
                        const coords = `${pos.coords.latitude},${pos.coords.longitude}`;
                        gpsInput.value = coords;
                    },
                    err => {
                        console.warn('GPS Error:', err.message);
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            }
        })();

        // Convert uploaded service photo to Base64 and store in hidden field
        const servicePhotoInput = document.getElementById('service_photo');
        if (servicePhotoInput) {
            servicePhotoInput.addEventListener('change', function(event) {
                const file = event.target.files && event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function(e) {
                    const base64 = e.target.result;
                    const hiddenPhoto = document.getElementById('photo_data');
                    if (hiddenPhoto) hiddenPhoto.value = base64;
                };
                reader.readAsDataURL(file);
            });
        }

        // Client-side validation before submit
        const formEl = document.querySelector('form[action="index.php?page=meter_reader_update_service"]');
        if (formEl) {
            formEl.addEventListener('submit', function(e) {
                const gpsVal = document.getElementById('gps_location')?.value || '';
                const photoVal = document.getElementById('photo_data')?.value || '';
                const statusVal = document.getElementById('new_status')?.value || '';
                const workPerformed = document.getElementById('work_performed')?.value || '';

                const errors = [];
                if (!gpsVal) errors.push('GPS location is required. Enable location.');
                if (!photoVal) errors.push('Service photo evidence is required.');
                if (!statusVal) errors.push('Update status is required.');
                if (!workPerformed.trim()) errors.push('Work performed is required.');

                if (errors.length) {
                    e.preventDefault();
                    alert(errors.join('\n'));
                }
            });
        }
    </script>
</body>
</html>
