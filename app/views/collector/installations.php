<?php
// Collector Installations - aligned with dashboard theme and sidebar, with map support
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Collector Installations - AquaBill</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
	<script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
	<style>
		body { font-family: 'Inter', sans-serif; }
	</style>
</head>
<body class="flex h-screen bg-gray-100">
	<?php include_once __DIR__ . '/sidebar.php'; ?>
	<div class="flex-1 flex flex-col overflow-hidden">
		<header class="flex justify-between items-center p-6 bg-white border-b border-gray-200">
			<h1 class="text-3xl font-bold text-gray-900">Installations</h1>
			<div class="flex items-center space-x-4">
				<a href="index.php?page=logout" class="text-red-600 hover:text-red-800 flex items-center">
					<i class="fas fa-sign-out-alt mr-1"></i> Logout
				</a>
			</div>
		</header>
		<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
			<div class="max-w-5xl mx-auto space-y-6">
				<?php if (!empty($data['error'])): ?>
					<div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
						<p class="text-sm text-red-700"><?php echo htmlspecialchars($data['error']); ?></p>
					</div>
				<?php endif; ?>
				<?php if (!empty($data['success'])): ?>
					<div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
						<p class="text-sm text-green-700"><?php echo htmlspecialchars($data['success']); ?></p>
					</div>
				<?php endif; ?>

				<div class="bg-white rounded-xl shadow-md">
					<div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
						<h2 class="text-lg font-semibold text-gray-800">Submit Installation</h2>
						<a href="index.php?page=collector_records" class="text-sm text-blue-600 hover:text-blue-800">
							<i class="fas fa-file-alt mr-2"></i> My Records
						</a>
					</div>
					<div class="p-6">
						<form method="post" enctype="multipart/form-data" action="index.php?page=collector_installations" class="space-y-6">
							<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
								<div>
									<label for="meter_id" class="block text-sm font-medium text-gray-700 mb-1">Select Meter</label>
									<select name="meter_id" id="meter_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
										<option value="">Choose meter</option>
										<?php foreach ($data['assignedMeters'] as $m): ?>
											<option value="<?php echo htmlspecialchars($m['id']); ?>"><?php echo htmlspecialchars($m['serial_number']); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div>
									<label for="initial_reading" class="block text-sm font-medium text-gray-700 mb-1">Initial Reading</label>
									<input type="number" step="0.01" name="initial_reading" id="initial_reading" placeholder="0.00" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md">
								</div>
								<div class="md:col-span-2">
									<label for="gps" class="block text-sm font-medium text-gray-700 mb-1">GPS Location</label>
									<div class="flex">
										<input type="text" name="gps_location" id="gps" placeholder="latitude,longitude" readonly class="flex-1 min-w-0 block w-full px-3 py-2 rounded-l-md border border-gray-300 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
										<button type="button" id="getLocationBtn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-r-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
											<i class="fas fa-map-marker-alt mr-2"></i> Get Current Location
										</button>
									</div>
									<p class="mt-2 text-sm text-gray-500">Drag the marker or click on the map to set the location.</p>
								</div>
								<div class="md:col-span-2">
									<div id="map" class="h-80 w-full rounded-lg border border-gray-300"></div>
								</div>
								<div class="md:col-span-2">
									<label for="install_photo" class="block text-sm font-medium text-gray-700 mb-1">Installation Photo</label>
									<input type="file" name="install_photo" id="install_photo" accept="image/*" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md">
								</div>
							</div>
							<div>
								<label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
								<textarea name="notes" id="notes" rows="3" placeholder="Any notes..." class="shadow-sm focus:ring-blue-500 focus:border-blue-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
							</div>
							<div>
								<button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
									<i class="fas fa-paper-plane mr-2"></i> Submit for Admin Review
								</button>
							</div>
						</form>
					</div>
				</div>

				<div class="bg-white rounded-xl shadow-md">
					<div class="px-6 py-4 border-b border-gray-200">
						<h3 class="text-lg font-semibold text-gray-800">My Installation Submissions</h3>
					</div>
					<div class="p-6 overflow-x-auto">
						<table class="min-w-full divide-y divide-gray-200">
							<thead class="bg-gray-50">
								<tr>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
								</tr>
							</thead>
							<tbody class="bg-white divide-y divide-gray-200">
								<?php foreach (($data['waitingInstalls'] ?? []) as $row): ?>
									<tr>
										<td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['serial_number'] ?? ''); ?></td>
										<td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['status'] ?? ''); ?></td>
										<td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['submitted_at'] ?? ''); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</main>
	</div>
	<script>
		let map;
		let marker;
		function initMap() {
			const defaultLocation = [36.821945, -1.292066];
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
				layers: [{ id: 'osm-tiles', type: 'raster', source: 'osm-tiles', minzoom: 0, maxzoom: 19 }]
			};
			map = new maplibregl.Map({ container: 'map', style, center: defaultLocation, zoom: 15 });
			map.addControl(new maplibregl.NavigationControl());
			map.addControl(new maplibregl.ScaleControl({ maxWidth: 100, unit: 'metric' }));
			marker = new maplibregl.Marker({ draggable: true, color: '#3FB1CE' }).setLngLat(defaultLocation).addTo(map);
			marker.on('dragend', function() {
				const lngLat = marker.getLngLat();
				document.getElementById('gps').value = lngLat.lat + ',' + lngLat.lng;
			});
			map.on('click', function(event) {
				marker.setLngLat(event.lngLat);
				document.getElementById('gps').value = event.lngLat.lat + ',' + event.lngLat.lng;
			});
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition((position) => {
					const pos = [position.coords.longitude, position.coords.latitude];
					map.setCenter(pos);
					marker.setLngLat(pos);
					document.getElementById('gps').value = position.coords.latitude + ',' + position.coords.longitude;
				});
			}
		}
		document.addEventListener('DOMContentLoaded', initMap);
		document.addEventListener('DOMContentLoaded', function() {
			const btn = document.getElementById('getLocationBtn');
			if (btn) {
				btn.addEventListener('click', function() {
					if (navigator.geolocation) {
						navigator.geolocation.getCurrentPosition((position) => {
							const pos = [position.coords.longitude, position.coords.latitude];
							map.setCenter(pos);
							marker.setLngLat(pos);
							document.getElementById('gps').value = position.coords.latitude + ',' + position.coords.longitude;
						}, () => { alert('Unable to retrieve your location.'); });
					} else {
						alert("Your browser doesn't support geolocation.");
					}
				});
			}
		});
	</script>
</body>
</html>
