<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commercial Manager - Manage Meters</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            color: #333;
        }
        .sidebar {
            width: 250px;
            background: linear-gradient(to bottom, #4a5568, #2d3748);
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            transition: all 0.3s ease;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .sidebar.hidden {
            left: -250px;
        }
        .sidebar-header {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin-bottom: 10px;
        }
        .sidebar ul li a {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #e2e8f0;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .sidebar ul li a:hover, .sidebar ul li a.active {
            background-color: #2d3748;
            color: #63b3ed;
        }
        .sidebar ul li a i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .main-content.full-width {
            margin-left: 0;
        }
        .navbar {
            background-color: #ffffff;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 0.5rem;
            margin-bottom: 20px;
        }
        .navbar .menu-toggle {
            display: none; /* Hidden on desktop */
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #4a5568;
        }
        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 2000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 600px;
            position: relative;
        }
        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-sizing: border-box;
        }
        .btn {
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-primary {
            background-color: #4299e1;
            color: white;
        }
        .btn-primary:hover {
            background-color: #3182ce;
        }
        .btn-green {
            background-color: #48bb78;
            color: white;
        }
        .btn-green:hover {
            background-color: #38a169;
        }
        .btn-red {
            background-color: #ef4444;
            color: white;
        }
        .btn-red:hover {
            background-color: #dc2626;
        }
        .btn-yellow {
            background-color: #f6e05e;
            color: #333;
        }
        .btn-yellow:hover {
            background-color: #edc838;
        }
        .table-auto {
            width: 100%;
            border-collapse: collapse;
        }
        .table-auto th, .table-auto td {
            padding: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        .table-auto th {
            background-color: #edf2f7;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            color: #4a5568;
        }
        .table-auto tbody tr:hover {
            background-color: #f7fafc;
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">AquaBill CM</div>
        <ul>
            <li><a href="index.php?page=commercial_manager_dashboard"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="index.php?page=commercial_manager_manage_meters" class="active"><i class="fas fa-tachometer-alt"></i> Manage Meters</a></li>
            <li><a href="index.php?page=commercial_manager_review_applications"><i class="fas fa-clipboard-list"></i> Review Applications</a></li>
            <li><a href="index.php?page=commercial_manager_reports"><i class="fas fa-chart-pie"></i> Reports</a></li>
            <li><a href="index.php?page=commercial_manager_profile"><i class="fas fa-user-circle"></i> Profile</a></li>
            <li><a href="index.php?page=logout" class="text-red-400 hover:text-red-200"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content" id="mainContent">
        <div class="navbar">
            <button class="menu-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="text-2xl font-semibold text-gray-800">Manage Meters</h1>
            <div class="user-info flex items-center space-x-2">
                <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
                <span class="text-sm bg-blue-500 text-white px-3 py-1 rounded-full"><?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Commercial Manager')); ?></span>
            </div>
        </div>

        <?php if (!empty($data['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($data['error']); ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($data['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($data['success']); ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Add New Meter</h2>
            <form action="index.php?page=commercial_manager_manage_meters" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_meter">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="serial_number">Serial Number:</label>
                        <input type="text" id="serial_number" name="serial_number" required placeholder="Enter meter serial number">
                    </div>
                    <div class="form-group">
                        <label for="meter_type">Meter Type:</label>
                        <select id="meter_type" name="meter_type" required class="bg-white">
                            <option value="">Select Type</option>
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="industrial">Industrial</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="initial_reading">Initial Reading:</label>
                        <input type="number" id="initial_reading" name="initial_reading" step="0.01" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status" required class="bg-white">
                            <option value="">Select Status</option>
                            <option value="functional">Functional</option>
                            <option value="under maintenance">Under Maintenance</option>
                            <option value="available soon">Available Soon</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="meter_image">Meter Image:</label>
                        <div class="flex items-center space-x-2">
                            <input type="file" id="meter_image" name="meter_image" accept="image/*" class="hidden">
                            <button type="button" id="capture_image" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                <i class="fas fa-camera mr-2"></i> Capture Image
                            </button>
                            <button type="button" id="select_image" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                                <i class="fas fa-image mr-2"></i> Select Image
                            </button>
                        </div>
                        <div id="image_preview" class="mt-2 hidden">
                            <img id="preview" src="#" alt="Meter Image Preview" class="max-w-xs max-h-40 rounded border">
                            <button type="button" id="remove_image" class="mt-2 px-2 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600 transition">
                                <i class="fas fa-times mr-1"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-4"><i class="fas fa-plus-circle mr-2"></i> Add Meter</button>
            </form>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const fileInput = document.getElementById('meter_image');
                    const captureBtn = document.getElementById('capture_image');
                    const selectBtn = document.getElementById('select_image');
                    const removeBtn = document.getElementById('remove_image');
                    const preview = document.getElementById('preview');
                    const previewContainer = document.getElementById('image_preview');
                    
                    // Handle file selection with validation
                    fileInput.addEventListener('change', function() {
                        if (this.files && this.files[0]) {
                            // Validate file type
                            const fileType = this.files[0].type;
                            if (!fileType.match('image.*')) {
                                alert('Please select an image file');
                                this.value = '';
                                return;
                            }
                            
                            // Validate file size (max 5MB)
                            const fileSize = this.files[0].size / 1024 / 1024; // in MB
                            if (fileSize > 5) {
                                alert('File size exceeds 5MB. Please select a smaller image.');
                                this.value = '';
                                return;
                            }
                            
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                preview.src = e.target.result;
                                previewContainer.classList.remove('hidden');
                            };
                            reader.readAsDataURL(this.files[0]);
                        }
                    });
                    
                    // Capture image using camera
                    captureBtn.addEventListener('click', function() {
                        // Create a video element for the camera stream
                        const videoModal = document.createElement('div');
                        videoModal.style.position = 'fixed';
                        videoModal.style.top = '0';
                        videoModal.style.left = '0';
                        videoModal.style.width = '100%';
                        videoModal.style.height = '100%';
                        videoModal.style.backgroundColor = 'rgba(0,0,0,0.8)';
                        videoModal.style.zIndex = '9999';
                        videoModal.style.display = 'flex';
                        videoModal.style.flexDirection = 'column';
                        videoModal.style.alignItems = 'center';
                        videoModal.style.justifyContent = 'center';
                        
                        const videoContainer = document.createElement('div');
                        videoContainer.style.position = 'relative';
                        videoContainer.style.width = '80%';
                        videoContainer.style.maxWidth = '640px';
                        
                        const video = document.createElement('video');
                        video.style.width = '100%';
                        video.style.maxHeight = '70vh';
                        video.style.backgroundColor = '#000';
                        video.style.borderRadius = '8px';
                        video.autoplay = true;
                        
                        const captureButton = document.createElement('button');
                        captureButton.innerHTML = '<i class="fas fa-camera"></i> Take Photo';
                        captureButton.style.position = 'absolute';
                        captureButton.style.bottom = '20px';
                        captureButton.style.left = '50%';
                        captureButton.style.transform = 'translateX(-50%)';
                        captureButton.style.padding = '10px 20px';
                        captureButton.style.backgroundColor = '#3498db';
                        captureButton.style.color = 'white';
                        captureButton.style.border = 'none';
                        captureButton.style.borderRadius = '4px';
                        captureButton.style.cursor = 'pointer';
                        
                        const closeButton = document.createElement('button');
                        closeButton.innerHTML = '<i class="fas fa-times"></i>';
                        closeButton.style.position = 'absolute';
                        closeButton.style.top = '10px';
                        closeButton.style.right = '10px';
                        closeButton.style.backgroundColor = '#e74c3c';
                        closeButton.style.color = 'white';
                        closeButton.style.border = 'none';
                        closeButton.style.borderRadius = '50%';
                        closeButton.style.width = '30px';
                        closeButton.style.height = '30px';
                        closeButton.style.cursor = 'pointer';
                        
                        videoContainer.appendChild(video);
                        videoContainer.appendChild(captureButton);
                        videoContainer.appendChild(closeButton);
                        videoModal.appendChild(videoContainer);
                        document.body.appendChild(videoModal);
                        
                        // Access the camera
                        navigator.mediaDevices.getUserMedia({ video: true })
                            .then(function(stream) {
                                video.srcObject = stream;
                                
                                // Take photo when button is clicked
                                captureButton.addEventListener('click', function() {
                                    const canvas = document.createElement('canvas');
                                    canvas.width = video.videoWidth;
                                    canvas.height = video.videoHeight;
                                    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                                    
                                    // Convert canvas to blob
                                    canvas.toBlob(function(blob) {
                                        // Create a File object from the blob
                                        const file = new File([blob], "camera_capture.jpg", { type: "image/jpeg" });
                                        
                                        // Create a FileList-like object
                                        const dataTransfer = new DataTransfer();
                                        dataTransfer.items.add(file);
                                        fileInput.files = dataTransfer.files;
                                        
                                        // Trigger change event to update preview
                                        const event = new Event('change', { bubbles: true });
                                        fileInput.dispatchEvent(event);
                                        
                                        // Stop all video streams
                                        stream.getTracks().forEach(track => track.stop());
                                        
                                        // Remove the video modal
                                        document.body.removeChild(videoModal);
                                    }, 'image/jpeg');
                                });
                                
                                // Close button event
                                closeButton.addEventListener('click', function() {
                                    // Stop all video streams
                                    stream.getTracks().forEach(track => track.stop());
                                    
                                    // Remove the video modal
                                    document.body.removeChild(videoModal);
                                });
                            })
                            .catch(function(error) {
                                console.error("Camera error: ", error);
                                alert("Could not access camera. Please check permissions.");
                                document.body.removeChild(videoModal);
                            });
                    });
                    
                    // Select image from gallery
                    selectBtn.addEventListener('click', function() {
                        fileInput.setAttribute('accept', 'image/*');
                        fileInput.removeAttribute('capture');
                        fileInput.click();
                    });
                    
                    // Remove selected image
                    removeBtn.addEventListener('click', function() {
                        fileInput.value = '';
                        preview.src = '#';
                        previewContainer.classList.add('hidden');
                    });
                });
            </script>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Existing Meters</h2>
            <?php if (empty($data['meters'])): ?>
                <p class="text-gray-600">No meters found in the system.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="table-auto min-w-full">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Serial Number</th>
                                <th>Meter Type</th>
                                <th>Type</th>
                                <th>Initial Reading</th>
                                <th>Status</th>
                                <th>Client</th>
                                <th>Collector</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['meters'] as $meter): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($meter['photo_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($meter['photo_url']); ?>" 
                                                 alt="Meter Image" 
                                                 class="w-12 h-12 object-cover rounded cursor-pointer"
                                                 onclick="openImageModal('<?php echo htmlspecialchars($meter['photo_url']); ?>')">
                                        <?php else: ?>
                                            <span class="text-gray-400"><i class="fas fa-camera-slash"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($meter['serial_number']); ?></td>
                                    <td>
                                        <?php $src = strtolower($meter['source'] ?? 'company'); ?>
                                        <?php echo htmlspecialchars(ucfirst($src === 'client' ? 'personal' : 'company')); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(ucfirst($meter['meter_type'] ?? 'N/A')); ?></td>
                                    <td><?php echo htmlspecialchars($meter['initial_reading']); ?></td>
                                    <td>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            <?php
                                                if ($meter['status'] == 'available' || $meter['status'] == 'in_stock') echo 'bg-green-100 text-green-800';
                                                elseif ($meter['status'] == 'assigned_to_collector') echo 'bg-yellow-100 text-yellow-800';
                                                elseif ($meter['status'] == 'installed') echo 'bg-blue-100 text-blue-800';
                                                elseif ($meter['status'] == 'flagged') echo 'bg-red-100 text-red-800';
                                                else echo 'bg-gray-100 text-gray-800';
                                            ?>">
                                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $meter['status']))); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($meter['client_username'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($meter['collector_username'] ?? 'N/A'); ?></td>
                                    <td class="whitespace-nowrap">
                                        <button onclick="openViewMeterModal(<?php echo htmlspecialchars(json_encode($meter)); ?>)" class="btn btn-primary text-sm mr-2 mb-2 md:mb-0"><i class="fas fa-eye"></i> View</button>
                                        <button onclick="openEditMeterModal(<?php echo htmlspecialchars(json_encode($meter)); ?>)" class="btn btn-yellow text-sm mr-2 mb-2 md:mb-0"><i class="fas fa-edit"></i> Edit</button>
                                        <?php 
                                            $st = strtolower($meter['status'] ?? '');
                                            $canApply = empty($meter['client_username']) && in_array($st, ['available','in_stock','functional','available soon']);
                                        ?>
                                        <?php if ($canApply): ?>
                                            <?php 
                                                $pendingIds = $data['metersWithPendingApp'] ?? [];
                                                $approvedIds = $data['metersWithApprovedApp'] ?? [];
                                                $mid = (int)$meter['id'];
                                                $isPending = in_array($mid, array_map('intval', $pendingIds), true);
                                                $isApproved = in_array($mid, array_map('intval', $approvedIds), true);
                                            ?>
                                            <?php if ($isApproved): ?>
                                                <?php /* hide Apply when application is approved */ ?>
                                            <?php elseif ($isPending): ?>
                                                <button class="btn btn-gray text-sm mr-2 mb-2 md:mb-0" style="opacity:.6;cursor:not-allowed;" disabled title="Application submitted, awaiting approval"><i class="fas fa-user-plus"></i> Apply</button>
                                            <?php else: ?>
                                                <button onclick="openApplyMeterModal(<?php echo htmlspecialchars($meter['id']); ?>, '<?php echo htmlspecialchars($meter['status']); ?>', '<?php echo htmlspecialchars($meter['serial_number']); ?>')" class="btn btn-green text-sm mr-2 mb-2 md:mb-0"><i class="fas fa-user-plus"></i> Apply</button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <form action="index.php?page=commercial_manager_manage_meters" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this meter?');">
                                            <input type="hidden" name="action" value="delete_meter">
                                            <input type="hidden" name="meter_id" value="<?php echo htmlspecialchars($meter['id']); ?>">
                                            <button type="submit" class="btn btn-red text-sm"><i class="fas fa-trash"></i> Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Meter Modal -->
    <div id="editMeterModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeEditMeterModal()">&times;</span>
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Edit Meter</h2>
            <form action="index.php?page=commercial_manager_manage_meters" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_meter">
                <input type="hidden" id="edit_meter_id" name="meter_id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="edit_serial_number">Serial Number:</label>
                        <input type="text" id="edit_serial_number" name="serial_number" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_meter_type">Meter Type:</label>
                        <select id="edit_meter_type" name="meter_type" required>
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="industrial">Industrial</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_initial_reading">Initial Reading:</label>
                        <input type="number" id="edit_initial_reading" name="initial_reading" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status:</label>
                        <select id="edit_status" name="status" required>
                            <option value="functional">Functional</option>
                            <option value="under maintenance">Under Maintenance</option>
                            <option value="available soon">Available Soon</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_meter_image">Meter Image (Optional):</label>
                        <input type="file" id="edit_meter_image" name="meter_image" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="edit_gps_location">GPS Location (e.g., lat,long):</label>
                        <div class="flex items-center space-x-2">
                            <input type="text" id="edit_gps_location" name="gps_location" placeholder="e.g., -1.2921, 36.8219">
                            <button type="button" class="btn btn-gray" onclick="getCurrentLocation()"><i class="fas fa-location-arrow mr-1"></i> Get Current Location</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_client_id">Assigned Client (Optional):</label>
                        <select id="edit_client_id" name="client_id">
                            <option value="">None</option>
                            <?php foreach ($data['clients'] as $client): ?>
                                <option value="<?php echo htmlspecialchars($client['id']); ?>"><?php echo htmlspecialchars($client['username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_installation_date">Installation Date (Optional):</label>
                        <input type="date" id="edit_installation_date" name="installation_date">
                    </div>
                    <div class="form-group">
                        <label for="edit_assigned_collector_id">Assigned Collector (Optional):</label>
                        <select id="edit_assigned_collector_id" name="assigned_collector_id">
                            <option value="">None</option>
                            <?php foreach ($data['collectors'] as $collector): ?>
                                <option value="<?php echo htmlspecialchars($collector['id']); ?>"><?php echo htmlspecialchars($collector['username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-4"><i class="fas fa-save mr-2"></i> Update Meter</button>
                <div id="unassignContainer" style="display:none;margin-top:0.5rem;">
                    <form action="index.php?page=commercial_manager_manage_meters" method="POST" onsubmit="return confirm('Unassign this meter from the client?');">
                        <input type="hidden" name="action" value="unassign_meter_from_client">
                        <input type="hidden" id="unassign_meter_id" name="meter_id" value="">
                        <button type="submit" class="btn btn-red"><i class="fas fa-unlink mr-2"></i> Unassign from Client</button>
                    </form>
                </div>
                
            </form>
        </div>
    </div>

    <!-- Apply Meter Modal -->
    <div id="applyMeterModal" class="modal">
        <div class="modal-content max-w-xl">
            <div class="modal-header flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800">Apply Meter to Client</h2>
                <span class="close" onclick="closeApplyMeterModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-3">
                    <div class="text-sm text-blue-800"><i class="fas fa-info-circle mr-1"></i> Select a client to submit an application for this meter.</div>
                </div>
                <form id="applyForm" action="index.php?page=commercial_manager_manage_meters" method="POST">
                    <input type="hidden" name="action" value="assign_meter_to_client">
                    <input type="hidden" id="apply_meter_id" name="meter_id">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label>Meter Serial:</label>
                            <input type="text" id="apply_meter_serial" readonly class="bg-gray-100">
                        </div>
                        <div class="form-group">
                            <label for="apply_client_id">Client:</label>
                            <select id="apply_client_id" name="client_id" required>
                                <option value="">Select Client</option>
                                <?php foreach ($data['clients'] as $client): ?>
                                    <option value="<?php echo htmlspecialchars($client['id']); ?>"><?php echo htmlspecialchars($client['username']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="apply_installation_date">Preferred Installation Date (optional):</label>
                            <input type="date" id="apply_installation_date" name="installation_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" class="btn btn-gray" onclick="closeApplyMeterModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="applySubmitBtn"><i class="fas fa-paper-plane mr-2"></i> Apply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Dialog -->
    <div id="successDialog" class="modal" style="display:none;">
        <div class="modal-content max-w-md">
            <div class="modal-header">
                <h2 class="text-xl font-bold text-green-700"><i class="fas fa-check-circle mr-2"></i> Application Submitted</h2>
            </div>
            <div class="modal-body">
                <p class="text-gray-700">Your application was submitted successfully.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="document.getElementById('successDialog').style.display='none'">OK</button>
            </div>
        </div>
    </div>

    <!-- View Meter Modal -->
    <div id="viewMeterModal" class="modal">
        <div class="modal-content max-w-2xl">
            <div class="modal-header">
                <h2 class="text-xl font-bold">Meter Details</h2>
                <span class="close" onclick="closeViewMeterModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-1">
                        <div id="viewMeterImage" class="mb-4 h-48 bg-gray-100 flex items-center justify-center rounded">
                            <img id="meterDetailImage" src="" alt="Meter Image" class="max-h-full max-w-full object-contain hidden">
                            <span id="noImagePlaceholder" class="text-gray-400 text-lg"><i class="fas fa-camera-slash mr-2"></i>No Image Available</span>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg shadow-sm">
                            <h3 class="font-semibold text-blue-800 mb-2">Meter Information</h3>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="text-sm font-medium text-gray-600">Serial Number:</div>
                                <div id="viewSerialNumber" class="text-sm"></div>
                                <div class="text-sm font-medium text-gray-600">Meter Type:</div>
                                <div id="viewMeterType" class="text-sm"></div>
                                <div class="text-sm font-medium text-gray-600">Initial Reading:</div>
                                <div id="viewInitialReading" class="text-sm"></div>
                                <div class="text-sm font-medium text-gray-600">Status:</div>
                                <div id="viewStatus" class="text-sm"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-1">
                        <div class="bg-green-50 p-4 rounded-lg shadow-sm mb-4">
                            <h3 class="font-semibold text-green-800 mb-2">Assignment Information</h3>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="text-sm font-medium text-gray-600">Client:</div>
                                <div id="viewClient" class="text-sm"></div>
                                <div class="text-sm font-medium text-gray-600">Collector:</div>
                                <div id="viewCollector" class="text-sm"></div>
                            </div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg shadow-sm">
                            <h3 class="font-semibold text-purple-800 mb-2">Additional Information</h3>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="text-sm font-medium text-gray-600">Installation Date:</div>
                                <div id="viewInstallationDate" class="text-sm"></div>
                                <div class="text-sm font-medium text-gray-600">Location:</div>
                                <div id="viewLocation" class="text-sm"></div>
                                <div class="text-sm font-medium text-gray-600">Last Reading:</div>
                                <div id="viewLastReading" class="text-sm"></div>
                                <div class="text-sm font-medium text-gray-600">Last Reading Date:</div>
                                <div id="viewLastReadingDate" class="text-sm"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-gray" onclick="closeViewMeterModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-content max-w-xl">
            <div class="modal-header">
                <h2 class="text-xl font-bold">Meter Image</h2>
                <span class="close" onclick="closeImageModal()">&times;</span>
            </div>
            <div class="modal-body flex justify-center">
                <img id="enlargedImage" src="" alt="Enlarged Meter Image" class="max-h-96 max-w-full">
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('hidden');
                mainContent.classList.toggle('full-width');
            });

            // Highlight active navigation link
            const currentPath = window.location.search;
            const navLinks = sidebar.querySelectorAll('a');
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.href.includes(currentPath) && currentPath !== '') {
                    link.classList.add('active');
                } else if (currentPath === '' && link.href.includes('commercial_manager_dashboard')) {
                    link.classList.add('active');
                }
            });

            // Adjust sidebar visibility on resize for desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('hidden');
                    mainContent.classList.remove('full-width');
                } else {
                    sidebar.classList.add('hidden');
                    mainContent.classList.add('full-width');
                }
            });

            // --- Modal Functions ---
            const editMeterModal = document.getElementById('editMeterModal');
            const applyMeterModal = document.getElementById('applyMeterModal');

            window.onclick = function(event) {
                if (event.target == editMeterModal) {
                    closeEditMeterModal();
                }
                if (event.target == applyMeterModal) {
                    closeApplyMeterModal();
                }
            }
        });

        function openEditMeterModal(meter) {
            document.getElementById('edit_meter_id').value = meter.id;
            document.getElementById('edit_serial_number').value = meter.serial_number;
            document.getElementById('edit_meter_type').value = meter.meter_type;
            document.getElementById('edit_initial_reading').value = meter.initial_reading;
            document.getElementById('edit_status').value = meter.status;
            // optional file upload
            document.getElementById('edit_gps_location').value = meter.gps_location || '';
            document.getElementById('edit_client_id').value = meter.client_id || '';
            document.getElementById('edit_installation_date').value = meter.installation_date || '';
            document.getElementById('edit_assigned_collector_id').value = meter.assigned_collector_id || '';
            const un = document.getElementById('unassignContainer');
            const um = document.getElementById('unassign_meter_id');
            if (meter.client_id) { un.style.display = 'block'; um.value = meter.id; } else { un.style.display = 'none'; um.value = ''; }
            editMeterModal.style.display = 'flex';
        }

        function getCurrentLocation(){
            if(!navigator.geolocation){ alert('Geolocation not supported'); return; }
            const input = document.getElementById('edit_gps_location');
            navigator.geolocation.getCurrentPosition(function(pos){
                const lat = pos.coords.latitude.toFixed(6);
                const lon = pos.coords.longitude.toFixed(6);
                input.value = lat + ', ' + lon;
            }, function(err){ alert('Unable to fetch location'); });
        }

        // Application modal open/close
        function openApplyMeterModal(meterId, status, serial){
            const st = (status||'').toLowerCase();
            const ok = ['available','in_stock','functional','available soon'].indexOf(st) !== -1;
            if(!ok){ alert('This meter is not available for application.'); return; }
            document.getElementById('apply_meter_id').value = meterId;
            document.getElementById('apply_meter_serial').value = serial || '';
            document.getElementById('apply_client_id').value = '';
            document.getElementById('apply_installation_date').value = '<?php echo date('Y-m-d'); ?>';
            document.getElementById('applyMeterModal').style.display = 'flex';
        }
        function closeApplyMeterModal(){ document.getElementById('applyMeterModal').style.display = 'none'; }

        function closeEditMeterModal() {
            document.getElementById('editMeterModal').style.display = 'none';
        }

        // remove legacy assign modal functions
        
        function openViewMeterModal(meter) {
            // Set meter details in the modal
            document.getElementById('viewSerialNumber').textContent = meter.serial_number;
            document.getElementById('viewMeterType').textContent = meter.meter_type ? meter.meter_type.charAt(0).toUpperCase() + meter.meter_type.slice(1) : 'N/A';
            document.getElementById('viewInitialReading').textContent = meter.initial_reading;
            
            // Set status with appropriate styling
            const statusElement = document.getElementById('viewStatus');
            statusElement.textContent = meter.status ? meter.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A';
            
            // Set client and collector information
            document.getElementById('viewClient').textContent = meter.client_username || 'Not Assigned';
            document.getElementById('viewCollector').textContent = meter.collector_username || 'Not Assigned';
            
            // Set additional information
            document.getElementById('viewInstallationDate').textContent = meter.installation_date || 'Not Installed';
            document.getElementById('viewLocation').textContent = meter.gps_location || 'Not Available';
            document.getElementById('viewLastReading').textContent = meter.last_reading || 'No Reading';
            document.getElementById('viewLastReadingDate').textContent = meter.last_reading_date || 'No Reading Date';
            
            // Handle meter image
            const meterImage = document.getElementById('meterDetailImage');
            const noImagePlaceholder = document.getElementById('noImagePlaceholder');
            
            if (meter.photo_url && meter.photo_url !== '') {
                meterImage.src = meter.photo_url;
                meterImage.classList.remove('hidden');
                noImagePlaceholder.classList.add('hidden');
            } else {
                meterImage.classList.add('hidden');
                noImagePlaceholder.classList.remove('hidden');
            }
            
            // Display the modal
            document.getElementById('viewMeterModal').style.display = 'flex';
        }
        
        function closeViewMeterModal() {
            document.getElementById('viewMeterModal').style.display = 'none';
        }
        
        function openImageModal(imageUrl) {
            document.getElementById('enlargedImage').src = imageUrl;
            document.getElementById('imageModal').style.display = 'flex';
        }
        
        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // success dialog on server-side success message
        (function(){
            const successFlag = <?php echo !empty($data['success']) ? 'true' : 'false'; ?>;
            if(successFlag){ const dlg = document.getElementById('successDialog'); if(dlg){ dlg.style.display='flex'; } }
        })();
    </script>
</body>
</html>
