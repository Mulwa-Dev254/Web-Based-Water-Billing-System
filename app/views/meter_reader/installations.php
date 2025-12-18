<?php
// Meter Reader Installations - aligned with dashboard theme and sidebar, with map support
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installations - Meter Reader</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .dashboard-container { display:flex; min-height:100vh; }
        .main-content { flex:1; padding:1.5rem; }
    </style>
 </head>
<body>
<div class="dashboard-container">
    <?php include_once __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Submit Installation</h1>
            <div class="flex items-center text-sm text-gray-500 mt-2">
                <a href="index.php?page=meter_reader_dashboard" class="hover:text-blue-600">Dashboard</a>
                <span class="mx-2">/</span>
                <span>Installations</span>
            </div>
        </div>

        <?php if (!empty($data['error'])): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <p class="text-sm text-red-700"><?php echo htmlspecialchars($data['error']); ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($data['success'])): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                <p class="text-sm text-green-700"><?php echo htmlspecialchars($data['success']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Assigned meters waiting installation -->
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Meters Waiting Installation</h3>
                <a href="index.php?page=meter_reader_record_reading" class="text-sm text-blue-600 hover:text-blue-700">
                    <i class="fas fa-list mr-1"></i> Record Readings
                </a>
            </div>
            <div class="p-6 overflow-x-auto">
                <?php 
                $pending = array_filter($data['assignedMeters'] ?? [], function($m){ 
                    $s = strtolower(trim($m['status'] ?? ''));
                    return in_array($s, ['pending_installation','waiting_installation','waiting installation'], true);
                }); 
                ?>
                <?php if (empty($pending)): ?>
                    <p class="text-sm text-gray-500">No meters currently waiting installation.</p>
                <?php else: ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($pending as $m): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($m['serial_number']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars(ucfirst($m['meter_type'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (!empty($m['client_id'])): ?>
                                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($m['client_name'] ?? $m['client_username']); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($m['client_email'] ?? ''); ?></div>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-500">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="index.php?page=meter_reader_installations&meter_id=<?php echo htmlspecialchars($m['id']); ?>" class="inline-flex items-center px-3 py-1 border border-gray-300 text-xs rounded-md hover:bg-gray-50">
                                            <i class="fas fa-tools mr-1"></i> Prefill to Install
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="p-6">
                <form method="post" enctype="multipart/form-data" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="meter_id" class="block text-sm font-medium text-gray-700 mb-1">Select Meter</label>
                            <select name="meter_id" id="meter_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">Choose meter</option>
                                <?php foreach ($data['assignedMeters'] as $m): ?>
                                    <option value="<?php echo htmlspecialchars($m['id']); ?>" <?php echo (($data['prefill_meter_id'] ?? 0) == $m['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['serial_number']); ?></option>
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Installation Photo</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-2 text-gray-600">
                                        <i class="fas fa-image"></i>
                                        <span class="text-sm">Add a photo for verification</span>
                                    </div>
                                    <div class="space-x-2">
                                        <button type="button" id="btnChoosePhoto" class="inline-flex items-center px-3 py-1.5 text-sm rounded-md bg-white border border-gray-300 hover:bg-gray-100">
                                            <i class="fas fa-upload mr-2"></i> Choose from Device
                                        </button>
                                        <button type="button" id="btnTakePhoto" class="inline-flex items-center px-3 py-1.5 text-sm rounded-md bg-blue-600 text-white hover:bg-blue-700">
                                            <i class="fas fa-camera mr-2"></i> Take Live Photo
                                        </button>
                                    </div>
                                </div>
                                <input type="file" name="install_photo" id="install_photo" accept="image/*" class="hidden">
                                <div id="photoPreview" class="hidden mt-3">
                                    <div class="relative inline-block">
                                        <img id="previewImg" src="" alt="Preview" class="max-h-56 rounded-md shadow-sm border border-gray-200">
                                        <button type="button" id="btnChangePhoto" class="absolute bottom-2 right-2 inline-flex items-center px-2 py-1 text-xs rounded-md bg-white/90 border border-gray-300 hover:bg-white">
                                            <i class="fas fa-sync-alt mr-1"></i> Change
                                        </button>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Supported: JPEG, PNG. On mobile, "Take Live Photo" opens the camera.</p>
                            </div>
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

        <div class="bg-white rounded-lg shadow-sm">
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
</div>

<script>
let map; let marker;
function initMap() {
    const defaultLocation = [36.821945, -1.292066];
    const style = { version: 8, sources: { 'osm-tiles': { type: 'raster', tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'], tileSize: 256, attribution: '© OpenStreetMap contributors' } }, layers: [{ id: 'osm-tiles', type: 'raster', source: 'osm-tiles', minzoom: 0, maxzoom: 19 }] };
    if (typeof maplibregl === 'undefined') {
        console.error('MapLibre GL failed to load.');
        return;
    }
    map = new maplibregl.Map({ container: 'map', style, center: defaultLocation, zoom: 15 });
    map.addControl(new maplibregl.NavigationControl());
    map.addControl(new maplibregl.ScaleControl({ maxWidth: 100, unit: 'metric' }));
    marker = new maplibregl.Marker({ draggable: true, color: '#3FB1CE' }).setLngLat(defaultLocation).addTo(map);
    marker.on('dragend', function(){ const lngLat = marker.getLngLat(); document.getElementById('gps').value = lngLat.lat + ',' + lngLat.lng; });
    map.on('click', function(event){ marker.setLngLat(event.lngLat); document.getElementById('gps').value = event.lngLat.lat + ',' + event.lngLat.lng; });
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position){
                const pos=[position.coords.longitude, position.coords.latitude];
                map.setCenter(pos);
                marker.setLngLat(pos);
                document.getElementById('gps').value = position.coords.latitude + ',' + position.coords.longitude;
            },
            function(){ /* silent on load; user can use button */ },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 5000 }
        );
    }
}
document.addEventListener('DOMContentLoaded', initMap);
// Camera modal (custom getUserMedia to guarantee camera instead of file picker)
const CameraUI = {
    modal: null, overlay: null, video: null, canvas: null, captureBtn: null, closeBtn: null,
    stream: null,
    build() {
        if (this.modal) return;
        const overlay = document.createElement('div');
        overlay.id = 'cameraOverlay';
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.6);display:none;z-index:1000;';

        const modal = document.createElement('div');
        modal.id = 'cameraModal';
        modal.style.cssText = 'position:fixed;inset:0;display:none;align-items:center;justify-content:center;z-index:1001;';

        const box = document.createElement('div');
        box.style.cssText = 'background:#fff;border-radius:0.5rem;box-shadow:0 10px 30px rgba(0,0,0,0.3);width:90%;max-width:640px;overflow:hidden;';

        const header = document.createElement('div');
        header.style.cssText = 'padding:0.75rem 1rem;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;';
        header.innerHTML = '<span style="font-weight:600;color:#1f2937">Live Camera</span>';
        const closeBtn = document.createElement('button');
        closeBtn.textContent = 'Close';
        closeBtn.style.cssText = 'padding:0.25rem 0.5rem;border:1px solid #d1d5db;border-radius:0.375rem;background:#fff;color:#374151;';
        header.appendChild(closeBtn);

        const body = document.createElement('div');
        body.style.cssText = 'padding:1rem;display:flex;flex-direction:column;gap:0.75rem;';

        const video = document.createElement('video');
        video.id = 'cameraVideo';
        video.style.cssText = 'width:100%;max-height:60vh;background:#000;border-radius:0.375rem;';
        video.setAttribute('playsinline', '');

        const canvas = document.createElement('canvas');
        canvas.id = 'cameraCanvas';
        canvas.style.cssText = 'display:none';

        const actions = document.createElement('div');
        actions.style.cssText = 'display:flex;gap:0.5rem;justify-content:flex-end;';
        const captureBtn = document.createElement('button');
        captureBtn.textContent = 'Capture Photo';
        captureBtn.style.cssText = 'padding:0.5rem 0.75rem;border-radius:0.375rem;background:#2563eb;color:#fff;';
        actions.appendChild(captureBtn);

        body.appendChild(video);
        body.appendChild(canvas);
        body.appendChild(actions);
        box.appendChild(header);
        box.appendChild(body);
        modal.appendChild(box);

        document.body.appendChild(overlay);
        document.body.appendChild(modal);

        this.modal = modal; this.overlay = overlay; this.video = video; this.canvas = canvas; this.captureBtn = captureBtn; this.closeBtn = closeBtn;

        closeBtn.addEventListener('click', ()=> this.close());
        overlay.addEventListener('click', ()=> this.close());
        captureBtn.addEventListener('click', ()=> this.capture());
    },
    async open() {
        this.build();
        try {
            const constraints = { video: { facingMode: { ideal: 'environment' } } };
            this.stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.video.srcObject = this.stream;
            await this.video.play();
            this.overlay.style.display = 'block';
            this.modal.style.display = 'flex';
        } catch (err) {
            // Fallback: try native file input with camera hint
            const fileInput = document.getElementById('install_photo');
            if (fileInput) {
                fileInput.setAttribute('accept', 'image/*;capture=camera');
                fileInput.setAttribute('capture', 'environment');
                fileInput.click();
            }
        }
    },
    close() {
        this.overlay.style.display = 'none';
        this.modal.style.display = 'none';
        if (this.stream) {
            this.stream.getTracks().forEach(t => t.stop());
            this.stream = null;
        }
        if (this.video) {
            this.video.srcObject = null;
        }
    },
    capture() {
        const fileInput = document.getElementById('install_photo');
        const previewWrap = document.getElementById('photoPreview');
        const previewImg = document.getElementById('previewImg');
        if (!this.video || !fileInput) return;
        const width = this.video.videoWidth || 640;
        const height = this.video.videoHeight || 480;
        this.canvas.width = width; this.canvas.height = height;
        const ctx = this.canvas.getContext('2d');
        ctx.drawImage(this.video, 0, 0, width, height);
        this.canvas.toBlob((blob)=>{
            if (!blob) return;
            const file = new File([blob], `installation_${Date.now()}.jpg`, { type: 'image/jpeg' });
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            const url = URL.createObjectURL(file);
            if (previewImg && previewWrap) {
                previewImg.src = url;
                previewWrap.classList.remove('hidden');
            }
            this.close();
        }, 'image/jpeg', 0.92);
    }
};

document.addEventListener('DOMContentLoaded', function(){ 
    const btn=document.getElementById('getLocationBtn'); 
    const gpsInput = document.getElementById('gps');
    if(btn){ 
        btn.addEventListener('click', function(){ 
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Locating...';
            const fallbackError = function(message){
                btn.disabled = false;
                btn.innerHTML = originalHTML;
                alert(message || 'Unable to retrieve your location. Please ensure location is enabled and access is allowed.');
            };
            if(!navigator.geolocation){
                fallbackError("Your browser doesn't support geolocation.");
                return;
            }
            navigator.geolocation.getCurrentPosition(
                function(position){
                    const pos=[position.coords.longitude, position.coords.latitude];
                    try {
                        if (map) { map.setCenter(pos); }
                        if (marker) { marker.setLngLat(pos); }
                    } catch(e) { /* noop */ }
                    if (gpsInput) { gpsInput.value = position.coords.latitude + ',' + position.coords.longitude; }
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                },
                function(err){
                    let msg = 'Unable to retrieve your location.';
                    if (err && err.code === 1) msg = 'Location permission denied. Please allow location access.';
                    else if (err && err.code === 2) msg = 'Position unavailable. Try moving to an open area.';
                    else if (err && err.code === 3) msg = 'Location request timed out. Please try again.';
                    // Insecure context hint
                    if (!window.isSecureContext && location.hostname !== 'localhost') {
                        msg += ' Hint: Geolocation requires HTTPS or localhost.';
                    }
                    fallbackError(msg);
                },
                { enableHighAccuracy: true, timeout: 12000, maximumAge: 5000 }
            );
        }); 
    }

    // Photo capture / choose & preview
    const fileInput = document.getElementById('install_photo');
    const btnChoose = document.getElementById('btnChoosePhoto');
    const btnTake = document.getElementById('btnTakePhoto');
    const btnChange = document.getElementById('btnChangePhoto');
    const previewWrap = document.getElementById('photoPreview');
    const previewImg = document.getElementById('previewImg');

    if(btnChoose){ btnChoose.addEventListener('click', ()=> fileInput && fileInput.click()); }
    if(btnTake){ btnTake.addEventListener('click', ()=> CameraUI.open()); }
    if(btnChange){ btnChange.addEventListener('click', ()=> CameraUI.open()); }

    if(fileInput){
        fileInput.addEventListener('change', function(){
            if(this.files && this.files[0]){
                const url = URL.createObjectURL(this.files[0]);
                previewImg.src = url;
                previewWrap.classList.remove('hidden');
            }
        });
    }
});
</script>
</body>
</html>
