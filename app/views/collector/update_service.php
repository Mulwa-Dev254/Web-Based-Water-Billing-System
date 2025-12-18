<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Service Request - AquaBill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .form-section {
            background-color: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 2rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #334155;
            font-size: 0.95rem;
        }
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.2s ease-in-out;
        }
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .btn-primary {
            background-color: #2563eb;
            color: white;
            padding: 0.875rem 1.75rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.2s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body class="flex h-screen bg-gray-100">
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="flex justify-between items-center p-6 bg-white border-b border-gray-200">
            <h1 class="text-3xl font-bold text-gray-900">Update Service Request Status</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700">Welcome, <span class="font-semibold text-blue-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span></span>
                <a href="index.php?page=logout" class="text-red-600 hover:text-red-800 flex items-center">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="max-w-4xl mx-auto form-section">
                <?php if (!empty($data['error'])): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($data['error']); ?></div>
                <?php endif; ?>
                <?php if (!empty($data['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($data['success']); ?></div>
                <?php endif; ?>

                <form action="index.php?page=collector_update_service" method="POST" class="space-y-6" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" id="photo_data" name="photo_data">

                    <div class="form-group">
                        <label for="request_id">Select Service Request:</label>
                        <select id="request_id" name="request_id" class="mt-1 block w-full" required>
                            <option value="">-- Select a service request --</option>
                            <?php foreach ($data['serviceRequests'] as $request): ?>
                                <option value="<?php echo htmlspecialchars($request['id']); ?>" <?php echo (isset($_GET['request_id']) && $_GET['request_id'] == $request['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars('ID: ' . $request['id'] . ' - Client: ' . $request['client_username'] . ' - Service: ' . $request['service_name'] . ' (Status: ' . ucfirst($request['status']) . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($data['serviceRequests'])): ?>
                            <p class="text-sm text-gray-500 mt-2">No service requests assigned to you.</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="new_status">Update Status To:</label>
                        <select id="new_status" name="new_status" class="mt-1 block w-full" required>
                            <option value="">-- Select new status --</option>
                            <option value="serviced">Serviced</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes (Optional):</label>
                        <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full" placeholder="Add any relevant notes about the service attendance."></textarea>
                    </div>

                    <div class="form-group">
                        <label>GPS Location:</label>
                        <input type="text" id="gps_location" name="gps_location" readonly class="mt-1 block w-full bg-gray-50" placeholder="Click to fetch GPS">
                        <button type="button" id="getGpsButton" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-md mt-2">Get Current GPS</button>
                    </div>

                    <div class="form-group">
                        <label for="service_photo">Service Photo Evidence:</label>
                        <input type="file" id="service_photo" name="service_photo" class="mt-1 block w-full" accept="image/*">
                        <small class="text-gray-500">Upload a photo of the service work completed</small>
                    </div>

                    <button type="submit" class="btn-primary w-full">Update Service Status</button>
                </form>
            </div>
        </main>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var btn=document.getElementById('getGpsButton');
        var input=document.getElementById('gps_location');
        btn&&btn.addEventListener('click', function(){
            if(navigator.geolocation){
                input.value='Fetching...';
                btn.disabled=true;
                navigator.geolocation.getCurrentPosition(function(pos){
                    var lat=pos.coords.latitude; var lon=pos.coords.longitude; input.value=lat+','+lon; btn.disabled=false;
                }, function(err){ input.value=''; btn.disabled=false; alert('Failed to get GPS'); });
            } else { alert('Geolocation not supported'); }
        });

        var fileInput = document.getElementById('service_photo');
        var photoHidden = document.getElementById('photo_data');
        if (fileInput) {
            fileInput.addEventListener('change', function(e){
                var file = e.target.files && e.target.files[0];
                if (!file) { photoHidden.value=''; return; }
                var reader = new FileReader();
                reader.onload = function(ev){
                    photoHidden.value = ev.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        var formEl = document.querySelector('form[action="index.php?page=collector_update_service"]');
        if (formEl) {
            formEl.addEventListener('submit', function(e){
                var gpsVal = document.getElementById('gps_location')?.value || '';
                var photoVal = document.getElementById('photo_data')?.value || '';
                var errors = [];
                if (!gpsVal) errors.push('GPS location is required.');
                if (!photoVal) errors.push('Service photo evidence is required.');
                if (errors.length) { e.preventDefault(); alert(errors.join('\n')); }
            });
        }
    });
    </script>
</body>
</html>
