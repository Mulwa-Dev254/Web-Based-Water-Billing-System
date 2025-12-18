<?php
// app/views/meter_reader/update_gps_location.php

// Check if user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'meter_reader') {
    header('Location: index.php?page=login');
    exit();
}

$pageTitle = "Update Meter GPS Location";
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - AquaBill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Update Meter GPS Location</h1>
            <div class="flex items-center text-sm text-gray-500 mt-2">
                <a href="index.php?page=meter_reader_dashboard" class="hover:text-blue-600">Dashboard</a>
                <span class="mx-2">/</span>
                <span>Update GPS Location</span>
            </div>
        </div>

    <!-- Main Content Area -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <?php if (!empty($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700"><?php echo htmlspecialchars($success); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="p-6">
            <form id="updateGpsForm" method="POST" action="index.php?page=meter_reader/update_gps_location" class="space-y-6">
                <!-- Meter Selection -->
                <div>
                    <label for="meter_id" class="block text-sm font-medium text-gray-700 mb-1">Select Meter</label>
                    <select id="meter_id" name="meter_id" required 
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">-- Select Meter --</option>
                        <?php foreach ($assignedMeters as $meter): ?>
                            <option value="<?php echo htmlspecialchars($meter['id']); ?>">
                                <?php echo htmlspecialchars($meter['serial_number']); ?> 
                                (<?php echo htmlspecialchars($meter['client_username'] ?? 'Unassigned'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- GPS Location Input -->
                <div>
                    <label for="gps_location" class="block text-sm font-medium text-gray-700 mb-1">GPS Location</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="text" name="gps_location" id="gps_location" required readonly
                               class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-l-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                               placeholder="latitude,longitude">
                        <button type="button" id="getLocationBtn" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-r-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-map-marker-alt mr-2"></i> Get Current Location
                        </button>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Click the button to automatically get your current GPS location.</p>
                </div>

                <!-- Notes Input -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="3" 
                              class="shadow-sm focus:ring-blue-500 focus:border-blue-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md" 
                              placeholder="Enter any notes about this location update"></textarea>
                </div>

                <!-- Map Display -->
                <div>
                    <div id="map" class="h-96 w-full rounded-lg border border-gray-300 shadow-sm"></div>
                    <p class="mt-2 text-sm text-gray-500">The map shows the current location. You can also click on the map to set a location.</p>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-2"></i> Update GPS Location
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Map initialization script is now included below with Leaflet -->

<!-- Include MapLibre GL JS instead of Leaflet -->
<link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
<script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>

<script>
// Replace Leaflet implementation with MapLibre GL JS
let map;
let marker;

// Initialize the map
function initMap() {
    // Default location (can be set to a central location in your service area)
    const defaultLocation = [36.821945, -1.292066]; // Nairobi, Kenya as an example (note: MapLibre uses [lng, lat] order)
    
    // Create a custom style using OpenStreetMap raster tiles
    const style = {
        version: 8,
        sources: {
            'osm-tiles': {
                type: 'raster',
                tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
                tileSize: 256,
                attribution: '© OpenStreetMap contributors'
            }
        },
        layers: [
            {
                id: 'osm-tiles',
                type: 'raster',
                source: 'osm-tiles',
                minzoom: 0,
                maxzoom: 19
            }
        ]
    };
    
    // Create the map with MapLibre GL JS
    map = new maplibregl.Map({
        container: 'map',
        style: style, // Using custom style with OpenStreetMap tiles
        center: defaultLocation,
        zoom: 15
    });
    
    // Add navigation controls (zoom in/out, compass)
    map.addControl(new maplibregl.NavigationControl());
    
    // Add scale control
    map.addControl(new maplibregl.ScaleControl({
        maxWidth: 100,
        unit: 'metric'
    }));
    
    // Create a marker that will be updated with the user's location
    marker = new maplibregl.Marker({
        draggable: true,
        color: '#3FB1CE'
    })
    .setLngLat(defaultLocation)
    .addTo(map);
    
    // Update GPS input when marker is dragged
    marker.on('dragend', function() {
        const lngLat = marker.getLngLat();
        document.getElementById('gps_location').value = lngLat.lat + ',' + lngLat.lng;
    });
    
    // Allow clicking on map to set marker
    map.on('click', function(event) {
        marker.setLngLat(event.lngLat);
        document.getElementById('gps_location').value = event.lngLat.lat + ',' + event.lngLat.lng;
    });
    
    // Try to get user's current location when the page loads
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const pos = [
                    position.coords.longitude, // MapLibre uses [lng, lat] order
                    position.coords.latitude
                ];
                map.setCenter(pos);
                marker.setLngLat(pos);
                document.getElementById('gps_location').value = position.coords.latitude + ',' + position.coords.longitude;
            },
            () => {
                // Handle location error
                console.log("Error: The Geolocation service failed.");
            }
        );
    }
}

// Call initMap when the page is loaded
document.addEventListener('DOMContentLoaded', initMap);

// Get current location button handler
document.getElementById('getLocationBtn').addEventListener('click', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const pos = [
                    position.coords.longitude, // MapLibre uses [lng, lat] order
                    position.coords.latitude
                ];
                map.setCenter(pos);
                marker.setLngLat(pos);
                document.getElementById('gps_location').value = position.coords.latitude + ',' + position.coords.longitude;
            },
            () => {
                alert("Error: Unable to retrieve your location. Please check your browser settings.");
            }
        );
    } else {
        alert("Error: Your browser doesn't support geolocation.");
    }
});
</script>
<!-- Using MapLibre GL JS with free vector tiles that don't require an API key -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>