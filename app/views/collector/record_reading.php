<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Meter Reading - AquaBill</title>
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
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.2s ease-in-out;
        }
        .form-group input:focus,
        .form-group select:focus {
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
        .video-container {
            width: 100%;
            max-width: 640px;
            margin: 0 auto 1.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            overflow: hidden;
            position: relative;
            background-color: #000;
        }
        .video-container video {
            width: 100%;
            height: auto;
            display: block;
        }
        .video-container canvas {
            display: none; /* Hidden by default, used for capturing image */
        }
        .map-container {
            width: 100%;
            height: 300px;
            border-radius: 0.5rem;
            overflow: hidden;
            margin-top: 1.5rem;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body class="flex h-screen bg-gray-100">
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="flex justify-between items-center p-6 bg-white border-b border-gray-200">
            <h1 class="text-3xl font-bold text-gray-900">Record Meter Reading</h1>
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

                <form action="index.php?page=collector_record_reading" method="POST" class="space-y-6">
                    <div class="form-group">
                        <label for="meter_id">Select Meter:</label>
                        <select id="meter_id" name="meter_id" class="mt-1 block w-full" required>
                            <option value="">-- Select an assigned meter --</option>
                            <?php foreach ($data['assignedMeters'] as $meter): ?>
                                <option value="<?php echo htmlspecialchars($meter['id']); ?>" <?php echo (isset($_GET['meter_id']) && $_GET['meter_id'] == $meter['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($meter['serial_number'] . ' (Client: ' . $meter['client_username'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="reading_value">Meter Reading Value:</label>
                        <input type="number" id="reading_value" name="reading_value" step="0.01" min="0" required class="mt-1 block w-full" placeholder="e.g., 123.45">
                    </div>

                    <div class="form-group">
                        <label>Meter Photo:</label>
                        <div class="video-container">
                            <video id="webcamVideo" autoplay playsinline></video>
                            <canvas id="photoCanvas"></canvas>
                        </div>
                        <button type="button" id="captureButton" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md mb-2">Capture Photo</button>
                        <input type="hidden" id="photo_data" name="photo_data" required>
                        <p class="text-sm text-gray-500 mt-1">Click 'Capture Photo' to take an image from your webcam.</p>
                        <p class="text-sm text-red-500 mt-1" id="photoError" style="display:none;">Please capture a photo before submitting.</p>
                    </div>

                    <div class="form-group">
                        <label>GPS Location:</label>
                        <input type="text" id="gps_location" name="gps_location" readonly required class="mt-1 block w-full bg-gray-50" placeholder="Fetching GPS...">
                        <button type="button" id="getGpsButton" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md mt-2">Get Current GPS</button>
                        <p class="text-sm text-gray-500 mt-1">Click 'Get Current GPS' to automatically fill your location.</p>
                        <p class="text-sm text-red-500 mt-1" id="gpsError" style="display:none;">Please get GPS location before submitting.</p>
                        <div id="map" class="map-container"></div>
                    </div>

                    <button type="submit" class="btn-primary w-full">Submit Reading</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        // GPS and Camera functionality
        const webcamVideo = document.getElementById('webcamVideo');
        const photoCanvas = document.getElementById('photoCanvas');
        const captureButton = document.getElementById('captureButton');
        const photoDataInput = document.getElementById('photo_data');
        const photoError = document.getElementById('photoError');

        const getGpsButton = document.getElementById('getGpsButton');
        const gpsLocationInput = document.getElementById('gps_location');
        const gpsError = document.getElementById('gpsError');
        const mapDiv = document.getElementById('map');

        let stream; // To hold the camera stream

        // Initialize camera
        async function initCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                webcamVideo.srcObject = stream;
                webcamVideo.play();
                captureButton.disabled = false;
            } catch (err) {
                console.error("Error accessing camera: ", err);
                captureButton.disabled = true;
                alert("Could not access camera. Please ensure you have a webcam and grant permission.");
            }
        }

        // Capture photo
        captureButton.addEventListener('click', () => {
            if (webcamVideo.srcObject) {
                const context = photoCanvas.getContext('2d');
                photoCanvas.width = webcamVideo.videoWidth;
                photoCanvas.height = webcamVideo.videoHeight;
                context.drawImage(webcamVideo, 0, 0, photoCanvas.width, photoCanvas.height);
                const imageData = photoCanvas.toDataURL('image/png'); // Get Base64 image data
                photoDataInput.value = imageData;
                photoError.style.display = 'none'; // Hide error if photo is captured
                alert('Photo captured successfully!');
            } else {
                alert('Camera not active. Please ensure camera is enabled and try again.');
            }
        });

        // Get GPS Location
        getGpsButton.addEventListener('click', () => {
            if (navigator.geolocation) {
                gpsLocationInput.value = 'Fetching...';
                getGpsButton.disabled = true;
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        const gps = `${lat},${lon}`;
                        gpsLocationInput.value = gps;
                        gpsError.style.display = 'none'; // Hide error if GPS is captured
                        getGpsButton.disabled = false;
                        loadGoogleMap(lat, lon); // Load map with fetched coordinates
                    },
                    (error) => {
                        console.error("Error getting GPS location: ", error);
                        gpsLocationInput.value = 'Failed to get GPS';
                        gpsError.style.display = 'block'; // Show error
                        alert("Could not get GPS location. Please ensure location services are enabled and grant permission.");
                        getGpsButton.disabled = false;
                    }
                );
            } else {
                gpsLocationInput.value = 'Geolocation not supported';
                gpsError.style.display = 'block'; // Show error
                alert("Geolocation is not supported by your browser.");
            }
        });

        // Load Google Map (using static map for simplicity, or embed iframe)
        function loadGoogleMap(lat, lon) {
            // Using Google Maps Embed API for a simple display
            // Replace 'YOUR_GOOGLE_MAPS_API_KEY' with your actual API key if you want interactive maps
            // For a static map image, you might use:
            // const staticMapUrl = `https://maps.googleapis.com/maps/api/staticmap?center=${lat},${lon}&zoom=14&size=600x300&markers=color:red%7C${lat},${lon}&key=YOUR_GOOGLE_MAPS_API_KEY`;
            // mapDiv.innerHTML = `<img src="${staticMapUrl}" alt="Map location" class="w-full h-full object-cover">`;

            // Using an iframe for a more interactive (but still basic) map
            const embedMapUrl = `https://maps.google.com/maps?q=${lat},${lon}&hl=en&z=14&output=embed`;
            mapDiv.innerHTML = `<iframe width="100%" height="100%" frameborder="0" style="border:0" src="${embedMapUrl}" allowfullscreen></iframe>`;
        }

        // Form submission validation
        document.querySelector('form').addEventListener('submit', function(event) {
            if (photoDataInput.value === '') {
                photoError.style.display = 'block';
                event.preventDefault();
            }
            if (gpsLocationInput.value === '' || gpsLocationInput.value.startsWith('Failed')) {
                gpsError.style.display = 'block';
                event.preventDefault();
            }
        });

        // Initialize camera when page loads
        window.addEventListener('load', initCamera);
    </script>
</body>
</html>