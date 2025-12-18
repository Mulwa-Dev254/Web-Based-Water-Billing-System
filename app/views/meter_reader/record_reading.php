<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Meter Reading</title>
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
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .meter-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .camera-preview {
            width: 100%;
            height: 300px;
            background-color: #f3f4f6;
            border: 2px dashed #d1d5db;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            overflow: hidden;
        }
        .camera-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
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
                <h1 class="text-2xl font-bold text-gray-800">Record Meter Reading</h1>
                <p class="text-gray-600">Submit new meter readings for assigned meters</p>
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

            <!-- Meter Selection Card -->
            <?php if (empty($data['selected_meter'])): ?>
                <div class="card mb-6">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold text-gray-800">Select Meter</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($data['meters'])): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-exclamation-circle text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500">No meters assigned to you</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial Number</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Reading</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($data['meters'] as $meter): ?>
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
                                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($meter['last_reading'] ?? 'N/A') ?></div>
                                                    <?php if (!empty($meter['last_reading_date'])): ?>
                                                        <div class="text-xs text-gray-500"><?= htmlspecialchars(date('M d, Y', strtotime($meter['last_reading_date']))) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php 
                                                    $statusClass = '';
                                                    switch ($meter['status']) {
                                                        case 'installed':
                                                            $statusClass = 'badge-success';
                                                            break;
                                                        case 'assigned_to_collector':
                                                            $statusClass = 'badge-info';
                                                            break;
                                                        case 'pending_installation':
                                                            $statusClass = 'badge-warning';
                                                            break;
                                                        default:
                                                            $statusClass = 'badge-info';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>">
                                                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $meter['status']))) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <?php if ($meter['status'] === 'pending_installation'): ?>
                                                        <a href="index.php?page=meter_reader_installations&meter_id=<?= $meter['id'] ?>" class="btn btn-outline text-xs mr-2">
                                                            <i class="fas fa-tools mr-1"></i> Install
                                                        </a>
                                                    <?php else: ?>
                                                        <?php if (!empty($meter['last_reading_date'])): ?>
                                                            <span class="text-gray-600 text-xs mr-2">
                                                                <i class="fas fa-check-circle"></i> Recorded
                                                                <span class="block text-[10px] text-gray-500"><?= htmlspecialchars(date('M d, Y H:i', strtotime($meter['last_reading_date']))) ?></span>
                                                            </span>
                                                        <?php endif; ?>
                                                        <a href="index.php?page=meter_reader_record_reading&meter_id=<?= $meter['id'] ?>" class="btn btn-primary text-xs">
                                                            <i class="fas fa-camera mr-1"></i> Record Reading
                                                        </a>
                                                    <?php endif; ?>
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
                <!-- Record Reading Form -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Meter Details Card -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-800">Meter Details</h2>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Serial Number</h3>
                                    <p class="text-base font-semibold text-gray-900"><?= htmlspecialchars($data['selected_meter']['serial_number']) ?></p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Meter Type</h3>
                                    <p class="text-base text-gray-900"><?= htmlspecialchars(ucfirst($data['selected_meter']['meter_type'])) ?></p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Last Reading</h3>
                                    <p class="text-base text-gray-900"><?= htmlspecialchars($data['last_reading']['reading_value'] ?? 'N/A') ?></p>
                                    <?php if (!empty($data['last_reading']['reading_date'])): ?>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars(date('M d, Y H:i', strtotime($data['last_reading']['reading_date']))) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Status</h3>
                                    <p>
                                        <?php 
                                        $statusClass = '';
                                        switch ($data['selected_meter']['status']) {
                                            case 'installed':
                                                $statusClass = 'badge-success';
                                                break;
                                            case 'assigned_to_collector':
                                                $statusClass = 'badge-info';
                                                break;
                                            case 'pending_installation':
                                                $statusClass = 'badge-warning';
                                                break;
                                            default:
                                                $statusClass = 'badge-info';
                                        }
                                        ?>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $data['selected_meter']['status']))) ?>
                                        </span>
                                    </p>
                                </div>
                                <?php if ($data['selected_meter']['client_id']): ?>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500">Client</h3>
                                        <p class="text-base text-gray-900"><?= htmlspecialchars($data['selected_meter']['client_name'] ?? $data['selected_meter']['client_username']) ?></p>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($data['selected_meter']['client_email'] ?? '') ?></p>
                                        <?php if (!empty($data['selected_meter']['client_phone'])): ?>
                                            <p class="text-sm text-gray-500"><?= htmlspecialchars($data['selected_meter']['client_phone']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($data['selected_meter']['installation_date'])): ?>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500">Installation Date</h3>
                                        <p class="text-base text-gray-900"><?= htmlspecialchars(date('M d, Y', strtotime($data['selected_meter']['installation_date']))) ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($data['selected_meter']['gps_location'])): ?>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500">GPS Location</h3>
                                        <p class="text-base text-gray-900"><?= htmlspecialchars($data['selected_meter']['gps_location']) ?></p>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Initial Reading</h3>
                                    <p class="text-base text-gray-900"><?= htmlspecialchars($data['selected_meter']['initial_reading']) ?></p>
                                </div>
                                <?php if (!empty($data['last_reading'])): ?>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500">Last Reading</h3>
                                        <p class="text-base text-gray-900"><?= htmlspecialchars($data['last_reading']['reading_value']) ?></p>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars(date('M d, Y', strtotime($data['last_reading']['reading_date']))) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Reading Form Card -->
                    <div class="card col-span-2">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-800">Record New Reading</h2>
                            <a href="index.php?page=meter_reader_record_reading" class="btn btn-outline text-sm">
                                <i class="fas fa-arrow-left mr-2"></i> Back to Meters
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="index.php?page=meter_reader_record_reading" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="meter_id" value="<?= $data['selected_meter']['id'] ?>">
                                <input type="hidden" id="photo_data" name="photo_data">
                                <input type="hidden" id="gps_location" name="gps_location">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div class="form-group">
                                        <label for="reading_value" class="form-label">Reading Value <span class="text-red-500">*</span></label>
                                        <input type="number" id="reading_value" name="reading_value" class="form-control" step="0.01" min="<?= !empty($data['last_reading']) ? $data['last_reading']['reading_value'] : $data['selected_meter']['initial_reading'] ?>" required>
                                        <?php if (!empty($data['last_reading'])): ?>
                                            <small class="text-gray-500">Last reading: <?= htmlspecialchars($data['last_reading']['reading_value']) ?></small>
                                        <?php else: ?>
                                            <small class="text-gray-500">Initial reading: <?= htmlspecialchars($data['selected_meter']['initial_reading']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="reading_date" class="form-label">Reading Date <span class="text-red-500">*</span></label>
                                        <input type="date" id="reading_date" name="reading_date" class="form-control" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="reading_photo" class="form-label">Meter Photo</label>
                                    <div class="camera-preview" id="preview">
                                        <i class="fas fa-camera text-gray-400 text-5xl"></i>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <button type="button" id="capture-btn" class="btn btn-outline">
                                            <i class="fas fa-camera mr-2"></i> Capture Photo
                                        </button>
                                        <input type="file" id="reading_photo" name="reading_photo" class="form-control" accept="image/*" capture="environment">
                                    </div>
                                    <small class="text-gray-500">Take a clear photo of the meter reading</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Any observations or issues with the meter..."></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Meter Condition</label>
                                    <div class="flex space-x-4">
                                        <div class="flex items-center">
                                            <input type="radio" id="condition_normal" name="meter_condition" value="normal" class="mr-2" checked>
                                            <label for="condition_normal">Normal</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" id="condition_damaged" name="meter_condition" value="damaged" class="mr-2">
                                            <label for="condition_damaged">Damaged</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" id="condition_tampered" name="meter_condition" value="tampered" class="mr-2">
                                            <label for="condition_tampered">Tampered</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group" id="damage_details_container" style="display: none;">
                                    <label for="damage_details" class="form-label">Damage/Tampering Details</label>
                                    <textarea id="damage_details" name="damage_details" class="form-control" rows="3" placeholder="Describe the damage or tampering..."></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="confirm_accuracy" name="confirm_accuracy" class="mr-2" required>
                                        <label for="confirm_accuracy">I confirm that this reading is accurate and truthful</label>
                                    </div>
                                </div>
                                
                                <div class="mt-6">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-2"></i> Submit Reading
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Handle file input change to show preview
        document.getElementById('reading_photo').addEventListener('change', function(e) {
            const preview = document.getElementById('preview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Meter Reading">`;
                    // Also store base64 in hidden photo_data to match controller expectations
                    const photoDataInput = document.getElementById('photo_data');
                    photoDataInput.value = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Handle camera capture button
        document.getElementById('capture-btn').addEventListener('click', function() {
            document.getElementById('reading_photo').click();
        });
        
        // Show/hide damage details based on meter condition selection
        document.querySelectorAll('input[name="meter_condition"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const damageContainer = document.getElementById('damage_details_container');
                if (this.value === 'damaged' || this.value === 'tampered') {
                    damageContainer.style.display = 'block';
                } else {
                    damageContainer.style.display = 'none';
                }
            });
        });

        // Acquire GPS location and store in hidden input
        (function() {
            const gpsInput = document.getElementById('gps_location');
            const preview = document.getElementById('preview');
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        gpsInput.value = `${lat},${lon}`;
                    },
                    function(error) {
                        console.warn('GPS error:', error);
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            }
        })();

        // Basic client-side validation on submit to reduce server errors
        (function() {
            const form = document.querySelector('form[action*="meter_reader_record_reading"]');
            if (!form) return;
            form.addEventListener('submit', function(e) {
                const readingValue = document.getElementById('reading_value').value;
                const photoData = document.getElementById('photo_data').value;
                const gpsLocation = document.getElementById('gps_location').value;
                if (!readingValue || parseFloat(readingValue) <= 0) {
                    e.preventDefault();
                    alert('Please enter a valid reading value greater than zero.');
                    return;
                }
                if (!photoData) {
                    e.preventDefault();
                    alert('Please add a photo of the meter reading.');
                    return;
                }
                if (!gpsLocation) {
                    e.preventDefault();
                    alert('GPS location is required. Please allow location access.');
                    return;
                }
            });
        })();
    </script>
</body>
</html>
