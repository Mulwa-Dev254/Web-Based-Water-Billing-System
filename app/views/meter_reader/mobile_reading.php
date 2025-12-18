<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Meter Reading</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }
        
        .mobile-container {
            max-width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .mobile-header {
            background-color: #2563eb;
            color: white;
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .mobile-content {
            flex: 1;
            padding: 1rem;
        }
        
        .mobile-footer {
            background-color: white;
            padding: 0.75rem;
            position: sticky;
            bottom: 0;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-around;
            z-index: 10;
        }
        
        .footer-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #6b7280;
            font-size: 0.75rem;
        }
        
        .footer-icon.active {
            color: #2563eb;
        }
        
        .footer-icon i {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }
        
        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            overflow: hidden;
        }
        
        .card-header {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
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
            padding: 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #1f2937;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            -webkit-appearance: none;
            appearance: none;
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
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
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
        
        .camera-container {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 75%;
            background-color: #f3f4f6;
            border: 2px dashed #d1d5db;
            border-radius: 0.5rem;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        
        .camera-preview {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .camera-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            padding: 0.5rem;
            background-color: rgba(0, 0, 0, 0.3);
        }
        
        .camera-btn {
            background-color: white;
            border: none;
            border-radius: 50%;
            width: 3rem;
            height: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
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
        
        .meter-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .meter-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .meter-card:hover, .meter-card:focus {
            transform: translateY(-3px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .meter-icon {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            font-size: 1.25rem;
        }
        
        .meter-number {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        
        .meter-address {
            font-size: 0.75rem;
            color: #6b7280;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-top: 0.5rem;
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
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
            flex-direction: column;
        }
        
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top: 4px solid #2563eb;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive adjustments */
        @media (min-width: 640px) {
            .mobile-container {
                max-width: 640px;
                margin: 0 auto;
                border-left: 1px solid #e5e7eb;
                border-right: 1px solid #e5e7eb;
            }
        }
    </style>
</head>
<body>
    <div class="mobile-container">
        <div class="mobile-header">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-bold">Mobile Meter Reading</h1>
                <div class="flex items-center">
                    <span class="mr-2 text-sm"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
                    <i class="fas fa-user-circle text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="mobile-content">
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
            
            <!-- Meter Selection View -->
            <div id="meter-selection-view" class="<?= isset($_GET['meter_id']) ? 'hidden' : '' ?>">
                <div class="card mb-4">
                    <div class="card-header flex items-center justify-between">
                        <h2>Select Meter</h2>
                        <div class="relative">
                            <input type="text" id="meter-search" placeholder="Search meters..." class="form-control py-2 pl-8 pr-4 text-sm" style="width: 200px;">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($data['assignedMeters'])): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-exclamation-circle text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500">No meters assigned to you</p>
                            </div>
                        <?php else: ?>
                            <div class="meter-list" id="meter-list">
                                <?php foreach ($data['assignedMeters'] as $meter): ?>
                                    <a href="index.php?page=meter_reader_mobile_reading&meter_id=<?= $meter['id'] ?>" class="meter-card">
                                        <div class="meter-icon">
                                            <i class="fas fa-tachometer-alt"></i>
                                        </div>
                                        <div class="meter-number"><?= htmlspecialchars($meter['meter_number']) ?></div>
                                        <div class="meter-address"><?= htmlspecialchars($meter['address']) ?></div>
                                        <div class="badge badge-info">Client #<?= htmlspecialchars($meter['client_id']) ?></div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Meter Reading Form View -->
            <div id="meter-reading-view" class="<?= isset($_GET['meter_id']) ? '' : 'hidden' ?>">
                <?php if (isset($_GET['meter_id'])): ?>
                    <?php 
                    $meterId = intval($_GET['meter_id']);
                    $selectedMeter = null;
                    foreach ($data['assignedMeters'] as $meter) {
                        if ($meter['id'] == $meterId) {
                            $selectedMeter = $meter;
                            break;
                        }
                    }
                    ?>
                    
                    <?php if ($selectedMeter): ?>
                        <div class="flex items-center mb-4">
                            <a href="index.php?page=meter_reader_mobile_reading" class="mr-2">
                                <i class="fas fa-arrow-left text-gray-600"></i>
                            </a>
                            <h2 class="text-xl font-semibold">Record Reading</h2>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold">Meter Details</h3>
                                    <div class="badge badge-info">Client #<?= htmlspecialchars($selectedMeter['client_id']) ?></div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Meter Number</p>
                                        <p class="font-semibold"><?= htmlspecialchars($selectedMeter['meter_number']) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Serial Number</p>
                                        <p class="font-semibold"><?= htmlspecialchars($selectedMeter['serial_number']) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Location</p>
                                        <p class="font-semibold"><?= htmlspecialchars($selectedMeter['address']) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Last Reading</p>
                                        <p class="font-semibold"><?= htmlspecialchars($selectedMeter['last_reading'] ?? 'N/A') ?> m³</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <form id="reading-form" method="POST" action="index.php?page=meter_reader_mobile_reading" class="card">
                            <div class="card-header">
                                <h3 class="font-semibold">New Reading</h3>
                            </div>
                            <div class="card-body">
                                <input type="hidden" name="meter_id" value="<?= $meterId ?>">
                                
                                <div class="form-group">
                                    <label for="reading_value" class="form-label">Reading Value (m³)</label>
                                    <input type="number" id="reading_value" name="reading_value" class="form-control" step="0.01" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="meter_condition" class="form-label">Meter Condition</label>
                                    <select id="meter_condition" name="meter_condition" class="form-control" required>
                                        <option value="normal">Normal - Working Properly</option>
                                        <option value="leaking">Leaking</option>
                                        <option value="damaged">Damaged</option>
                                        <option value="tampered">Tampered</option>
                                        <option value="inaccessible">Inaccessible</option>
                                        <option value="other">Other Issue</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Enter any additional notes or observations"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Meter Photo</label>
                                    <div class="camera-container">
                                        <video id="camera-preview" class="camera-preview hidden"></video>
                                        <img id="captured-image" class="camera-preview hidden" alt="Captured meter image">
                                        <div id="camera-placeholder" class="flex items-center justify-center h-full">
                                            <div class="text-center text-gray-400">
                                                <i class="fas fa-camera text-4xl mb-2"></i>
                                                <p>Tap to take photo</p>
                                            </div>
                                        </div>
                                        <div class="camera-controls">
                                            <button type="button" id="capture-btn" class="camera-btn">
                                                <i class="fas fa-camera"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" id="photo_data" name="photo_data">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">GPS Location</label>
                                    <div class="flex items-center mb-2">
                                        <div id="gps-status" class="mr-2">
                                            <i class="fas fa-map-marker-alt text-gray-400"></i>
                                        </div>
                                        <span id="gps-coordinates" class="text-sm">Waiting for GPS...</span>
                                    </div>
                                    <input type="hidden" id="gps_location" name="gps_location">
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i> Submit Reading
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-error">
                            <p>Invalid meter selected. Please go back and select a valid meter.</p>
                        </div>
                        <a href="index.php?page=meter_reader_mobile_reading" class="btn btn-outline mb-4">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Meter List
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mobile-footer">
            <a href="index.php?page=meter_reader_dashboard" class="footer-icon">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="index.php?page=meter_reader_mobile_reading" class="footer-icon active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Readings</span>
            </a>
            <a href="index.php?page=meter_reader_update_service" class="footer-icon">
                <i class="fas fa-tools"></i>
                <span>Services</span>
            </a>
            <a href="index.php?page=meter_reader_records" class="footer-icon">
                <i class="fas fa-history"></i>
                <span>History</span>
            </a>
            <a href="index.php?page=meter_reader_profile" class="footer-icon">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </div>
    </div>
    
    <div id="loading-overlay" class="loading-overlay hidden">
        <div class="spinner"></div>
        <p>Processing...</p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Camera functionality
            const cameraPreview = document.getElementById('camera-preview');
            const capturedImage = document.getElementById('captured-image');
            const cameraPlaceholder = document.getElementById('camera-placeholder');
            const captureBtn = document.getElementById('capture-btn');
            const photoDataInput = document.getElementById('photo_data');
            const cameraContainer = document.querySelector('.camera-container');
            
            let stream = null;
            let cameraActive = false;
            
            // Initialize camera when clicking on the container
            cameraContainer.addEventListener('click', function() {
                if (!cameraActive) {
                    initCamera();
                }
            });
            
            // Capture button functionality
            captureBtn.addEventListener('click', function() {
                if (cameraActive) {
                    // If camera is active, take a photo
                    const canvas = document.createElement('canvas');
                    canvas.width = cameraPreview.videoWidth;
                    canvas.height = cameraPreview.videoHeight;
                    canvas.getContext('2d').drawImage(cameraPreview, 0, 0, canvas.width, canvas.height);
                    
                    // Convert to base64 and store in hidden input
                    const imageData = canvas.toDataURL('image/jpeg');
                    photoDataInput.value = imageData;
                    
                    // Display the captured image
                    capturedImage.src = imageData;
                    cameraPreview.classList.add('hidden');
                    capturedImage.classList.remove('hidden');
                    cameraPlaceholder.classList.add('hidden');
                    
                    // Change capture button to retake
                    captureBtn.innerHTML = '<i class="fas fa-redo"></i>';
                    
                    // Stop the camera stream
                    stopCamera();
                } else {
                    // If camera is not active, restart it for a new photo
                    initCamera();
                    captureBtn.innerHTML = '<i class="fas fa-camera"></i>';
                    capturedImage.classList.add('hidden');
                }
            });
            
            function initCamera() {
                // Request camera access
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
                    .then(function(mediaStream) {
                        stream = mediaStream;
                        cameraPreview.srcObject = mediaStream;
                        cameraPreview.play();
                        cameraPreview.classList.remove('hidden');
                        cameraPlaceholder.classList.add('hidden');
                        cameraActive = true;
                    })
                    .catch(function(error) {
                        console.error('Camera error:', error);
                        alert('Unable to access camera. Please ensure you have granted camera permissions.');
                    });
            }
            
            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                    cameraActive = false;
                }
            }
            
            // GPS functionality
            const gpsStatus = document.getElementById('gps-status');
            const gpsCoordinates = document.getElementById('gps-coordinates');
            const gpsLocationInput = document.getElementById('gps_location');
            
            function getLocation() {
                if (navigator.geolocation) {
                    gpsStatus.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-500"></i>';
                    gpsCoordinates.textContent = 'Getting location...';
                    
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const lat = position.coords.latitude;
                            const lon = position.coords.longitude;
                            
                            gpsStatus.innerHTML = '<i class="fas fa-map-marker-alt text-green-500"></i>';
                            gpsCoordinates.textContent = `${lat.toFixed(6)}, ${lon.toFixed(6)}`;
                            gpsLocationInput.value = `${lat},${lon}`;
                        },
                        function(error) {
                            gpsStatus.innerHTML = '<i class="fas fa-exclamation-triangle text-red-500"></i>';
                            gpsCoordinates.textContent = 'Location error: ' + getLocationErrorMessage(error);
                            console.error('Geolocation error:', error);
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                } else {
                    gpsStatus.innerHTML = '<i class="fas fa-times-circle text-red-500"></i>';
                    gpsCoordinates.textContent = 'Geolocation not supported by this browser';
                }
            }
            
            function getLocationErrorMessage(error) {
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        return "Location permission denied";
                    case error.POSITION_UNAVAILABLE:
                        return "Location information unavailable";
                    case error.TIMEOUT:
                        return "Location request timed out";
                    default:
                        return "Unknown location error";
                }
            }
            
            // Get location when page loads
            getLocation();
            
            // Refresh location button
            document.getElementById('gps-status').addEventListener('click', function() {
                getLocation();
            });
            
            // Form submission
            const readingForm = document.getElementById('reading-form');
            const loadingOverlay = document.getElementById('loading-overlay');
            
            if (readingForm) {
                readingForm.addEventListener('submit', function(e) {
                    // Validate form
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
                        alert('Please take a photo of the meter.');
                        return;
                    }
                    
                    if (!gpsLocation) {
                        e.preventDefault();
                        alert('GPS location is required. Please wait for GPS coordinates or try refreshing location.');
                        return;
                    }
                    
                    // Show loading overlay
                    loadingOverlay.classList.remove('hidden');
                });
            }
            
            // Meter search functionality
            const meterSearch = document.getElementById('meter-search');
            const meterList = document.getElementById('meter-list');
            
            if (meterSearch && meterList) {
                meterSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const meterCards = meterList.querySelectorAll('.meter-card');
                    
                    meterCards.forEach(function(card) {
                        const meterNumber = card.querySelector('.meter-number').textContent.toLowerCase();
                        const meterAddress = card.querySelector('.meter-address').textContent.toLowerCase();
                        
                        if (meterNumber.includes(searchTerm) || meterAddress.includes(searchTerm)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
