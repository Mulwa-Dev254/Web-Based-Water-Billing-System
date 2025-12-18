<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Your Meter - Client</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* Scope fixes to this page */
        .client-addmeter *, .client-addmeter *::before, .client-addmeter *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f7f8fb; color: #1f2937; margin: 0; }
        .layout { display: flex; min-height: 100vh; }
        .sidebar { width: 240px; background: linear-gradient(135deg, #1f2a44, #2563eb); color: #fff; padding: 20px 0; box-shadow: 2px 0 14px rgba(0,0,0,0.08); }
        .sidebar h3 { text-align: center; margin: 0 0 20px; font-weight: 700; font-size: 1.1rem; letter-spacing: 0.2px; }
        .sidebar ul { list-style: none; margin: 0; padding: 0; }
        .sidebar a { display: block; padding: 12px 18px; color: #fff; text-decoration: none; border-left: 3px solid transparent; font-size: 0.95rem; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.12); border-left-color: #fff; }
        .main { flex: 1; padding: 18px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid #e5e7eb; padding-bottom: 12px; }
        .header h1 { font-size: 1.45rem; color: #111827; margin: 0; font-weight: 700; letter-spacing: 0.2px; }
        .user-info { display: flex; gap: 12px; align-items: center; font-size: 0.92rem; }
        .user-info a { color: #ef4444; text-decoration: none; font-weight: 600; }
        .container { max-width: 1000px; margin: 0 auto; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 12px 24px rgba(17,24,39,0.06); padding: 20px; margin-bottom: 18px; border: 1px solid #eef2f7; overflow: hidden; }
        .card h3 { margin: 0 0 12px; font-size: 1.1rem; color: #111827; font-weight: 700; }
        .alert { padding: 12px 15px; border-radius: 10px; font-size: 0.92rem; margin-bottom: 12px; border: 1px solid transparent; }
        .alert-danger { background: #fef2f2; color: #991b1b; border-color: #fee2e2; }
        .alert-success { background: #ecfdf5; color: #065f46; border-color: #d1fae5; }
        .form-grid { display: grid; grid-template-columns: minmax(0,1fr) minmax(0,1fr); gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
        .form-label { font-weight: 600; color: #374151; }
        .form-control { display: block; width: 100%; max-width: 100%; padding: 12px 12px; font-size: 0.95rem; border: 1px solid #e5e7eb; border-radius: 10px; background: #fafafa; }
        select.form-control { appearance: none; }
        .input-readonly { background: #f9fafb; color: #6b7280; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 16px; border-radius: 10px; font-size: 0.95rem; border: 1px solid transparent; cursor: pointer; transition: transform .15s ease, box-shadow .15s ease; white-space: nowrap; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 8px 16px rgba(17,24,39,0.08); }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-secondary { background: #f3f4f6; color: #111827; border-color: #e5e7eb; }
        .btn-danger { background: #ef4444; color: #fff; }
        .helper { font-size: 0.85rem; color: #6b7280; }
        .map-wrap { border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        #map { width: 100%; height: 320px; }
        .map-actions { display: flex; gap: 10px; margin-top: 10px; }
        .search-actions { display: grid; grid-template-columns: 1fr auto; gap: 10px; margin-top: 10px; }
        .search-results { margin-top: 8px; border: 1px solid #e5e7eb; border-radius: 10px; max-height: 160px; overflow: auto; }
        .search-results div { padding: 8px 10px; cursor: pointer; border-bottom: 1px solid #f3f4f6; }
        .search-results div:hover { background: #f9fafb; }
        .image-actions { display: flex; gap: 10px; align-items: center; }
        .image-preview { width: 160px; height: 120px; border: 1px solid #e5e7eb; border-radius: 10px; object-fit: cover; background: #f9fafb; }
        .error-text { font-size: 0.82rem; color: #b91c1c; }
        .modal { position: fixed; inset: 0; background: rgba(17,24,39,0.55); display: none; align-items: center; justify-content: center; z-index: 1000; }
        .modal-content { background: #fff; border-radius: 14px; box-shadow: 0 16px 32px rgba(17,24,39,0.18); width: 640px; max-width: 94vw; padding: 16px; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .modal-title { margin: 0; font-size: 1rem; font-weight: 700; }
        .video-wrap { border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        #cameraVideo { width: 100%; max-height: 360px; background: #000; }
        #captureCanvas { display: none; width: 100%; max-height: 360px; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px; }
        @media (max-width: 1024px) { .form-grid { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .sidebar { position: fixed; left: -240px; top:0; bottom:0; z-index: 1000; } }
    </style>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <div class="client-addmeter">
    <div class="layout">
        <div class="sidebar" id="clientSidebar">
            <h3>Client Panel</h3>
            <ul>
                <li><a href="index.php?page=client_dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="index.php?page=client_meters"><i class="fas fa-tachometer-alt"></i> My Meters</a></li>
                <li><a href="index.php?page=client_apply_meter" class="active"><i class="fas fa-list"></i> Apply for Meter</a></li>
                <li><a href="index.php?page=client_my_plans"><i class="fas fa-clipboard-list"></i> My Plans</a></li>
                <li><a href="index.php?page=client_apply_service"><i class="fas fa-tools"></i> Apply for Service</a></li>
                <li><a href="index.php?page=client_profile"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main" id="clientMainContent">
            <div class="header">
                <h1><i class="fas fa-plus-circle"></i> Add Your Meter</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Client'); ?></span>
                    <a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <div class="container">
                <?php if (!empty($data['error'])): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($data['error']); ?></div>
                <?php endif; ?>
                <?php if (!empty($data['message'])): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($data['message']); ?></div>
                <?php endif; ?>

                <div class="card">
                    <h3><i class="fas fa-tachometer-alt"></i> Meter Details</h3>
                    <form action="index.php?page=client_add_meter" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_client_meter" />
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="serial_number">Serial Number</label>
                                <input type="text" id="serial_number" name="serial_number" class="form-control" placeholder="e.g., SN-123456" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="meter_type">Meter Type</label>
                                <select id="meter_type" name="meter_type" class="form-control">
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="industrial">Industrial</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="initial_reading">Initial Reading</label>
                                <input type="number" step="0.01" id="initial_reading" name="initial_reading" class="form-control" placeholder="0" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="functional">Functional</option>
                                    <option value="available">Available</option>
                                    <option value="in_stock">In Stock</option>
                                </select>
                            </div>
                        </div>

                        <div class="card" style="margin-top:12px;">
                            <h3><i class="fas fa-map-location-dot"></i> Location</h3>
                            <div class="map-wrap"><div id="map"></div></div>
                            <div class="map-actions">
                                <button type="button" class="btn btn-secondary" id="btnUseLocation"><i class="fas fa-location-crosshairs"></i> Use Current Location</button>
                                <input type="text" id="gps_location" name="gps_location" class="form-control input-readonly" placeholder="lat,long" readonly>
                            </div>
                            <div class="search-actions">
                                <input type="text" id="location_search" class="form-control" placeholder="Search address or place (e.g., Avenue, Town)">
                                <button type="button" class="btn btn-secondary" id="btnSearch"><i class="fas fa-magnifying-glass"></i> Search</button>
                            </div>
                            <div id="searchResults" class="search-results" style="display:none;"></div>
                            <div class="form-group" style="margin-top:8px;">
                                <label class="form-label" for="location_name">Location Name</label>
                                <input type="text" id="location_name" name="location_name" class="form-control input-readonly" placeholder="Address will appear here" readonly>
                            </div>
                            <div class="helper">Use search or current location. Drag marker to refine; address updates automatically.</div>
                        </div>

                        <div class="card" style="margin-top:12px;">
                            <h3><i class="fas fa-camera"></i> Meter Image</h3>
                            <div class="image-actions">
                                <input type="file" id="meter_image" name="meter_image" accept="image/*" capture="environment" style="display:none;" />
                                <button type="button" class="btn btn-secondary" id="btnBrowseImage"><i class="fas fa-folder-open"></i> Browse Image</button>
                                <button type="button" class="btn btn-secondary" id="btnTakePhoto"><i class="fas fa-camera"></i> Take Live Photo</button>
                                <img id="imagePreview" class="image-preview" alt="Preview" />
                            </div>
                            <div class="helper">Accepted formats: JPG, PNG. Max size 2MB. On mobile, camera opens automatically.</div>
                        </div>

                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Add Meter & Submit Application</button>
                    </form>
                </div>

                <div class="card">
                    <h3>What happens next?</h3>
                    <p class="helper">After adding, we submit a standard application for verification and installation. You can track it under <a href="index.php?page=client_apply_meter">Apply for Meter</a>.</p>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="modal" id="cameraModal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-camera"></i> Capture Photo</h4>
                <button type="button" class="btn btn-danger" id="closeCameraModal"><i class="fas fa-times"></i> Close</button>
            </div>
            <div class="video-wrap">
                <video id="cameraVideo" autoplay playsinline></video>
                <canvas id="captureCanvas"></canvas>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" id="startCamera"><i class="fas fa-video"></i> Start Camera</button>
                <button type="button" class="btn btn-primary" id="capturePhoto"><i class="fas fa-circle"></i> Capture</button>
                <button type="button" class="btn btn-primary" id="useCaptured"><i class="fas fa-check"></i> Use Photo</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const clientSidebarToggle = document.getElementById('clientSidebarToggle');
            const clientSidebar = document.getElementById('clientSidebar');
            const clientMainContent = document.getElementById('clientMainContent');
            if (clientSidebarToggle) {
                clientSidebarToggle.addEventListener('click', function() {
                    clientSidebar.classList.toggle('visible');
                    clientMainContent.classList.toggle('full-width');
                });
            }

            // Leaflet map init with fallback
            let map, marker;
            const gpsInput = document.getElementById('gps_location');
            const locationNameInput = document.getElementById('location_name');
            const searchInput = document.getElementById('location_search');
            const searchBtn = document.getElementById('btnSearch');
            const searchResults = document.getElementById('searchResults');
            const defaultLatLng = [ -1.286389, 36.817223 ]; // Nairobi CBD as default

            function initMap() {
                if (typeof L === 'undefined') { return false; }
                map = L.map('map');
                map.setView(defaultLatLng, 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
                marker = L.marker(defaultLatLng, { draggable: true }).addTo(map);
                gpsInput.value = defaultLatLng[0].toFixed(6) + ',' + defaultLatLng[1].toFixed(6);
                reverseGeocode(defaultLatLng[0], defaultLatLng[1]);
                marker.on('dragend', function(){
                    const p = marker.getLatLng();
                    gpsInput.value = p.lat.toFixed(6) + ',' + p.lng.toFixed(6);
                    reverseGeocode(p.lat, p.lng);
                });
                // Ensure proper sizing
                setTimeout(function(){ if (map) map.invalidateSize(); }, 200);
                window.addEventListener('resize', function(){ if (map) map.invalidateSize(); });
                return true;
            }

            if (!initMap()) {
                // Fallback: load Leaflet from jsDelivr if unpkg failed or SRI blocked
                const altCss = document.createElement('link');
                altCss.rel = 'stylesheet';
                altCss.href = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(altCss);
                const altScript = document.createElement('script');
                altScript.src = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js';
                altScript.onload = initMap;
                document.head.appendChild(altScript);
            }

            const btnUseLocation = document.getElementById('btnUseLocation');
            btnUseLocation.addEventListener('click', function(){
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported on this device.');
                    return;
                }
                navigator.geolocation.getCurrentPosition(function(pos){
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    if (!map || !marker) { return; }
                    map.setView([lat, lng], 16);
                    marker.setLatLng([lat, lng]);
                    gpsInput.value = lat.toFixed(6) + ',' + lng.toFixed(6);
                    reverseGeocode(lat, lng);
                }, function(err){
                    alert('Unable to get location: ' + (err.message || 'Unknown error'));
                }, { enableHighAccuracy: true, timeout: 10000 });
            });

            // Address search via Nominatim
            searchBtn.addEventListener('click', function(){
                const q = (searchInput.value || '').trim();
                if (!q) { searchResults.style.display = 'none'; searchResults.innerHTML = ''; return; }
                const url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q);
                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(items => {
                        if (!items || items.length === 0) { searchResults.style.display = 'none'; searchResults.innerHTML = '<div>No results found</div>'; return; }
                        searchResults.innerHTML = '';
                        items.slice(0, 6).forEach(item => {
                            const div = document.createElement('div');
                            div.textContent = item.display_name;
                            div.addEventListener('click', function(){
                                const lat = parseFloat(item.lat), lng = parseFloat(item.lon);
                                if (!map || !marker) { return; }
                                map.setView([lat, lng], 16);
                                marker.setLatLng([lat, lng]);
                                gpsInput.value = lat.toFixed(6) + ',' + lng.toFixed(6);
                                locationNameInput.value = item.display_name;
                                searchResults.style.display = 'none';
                                searchResults.innerHTML = '';
                            });
                            searchResults.appendChild(div);
                        });
                        searchResults.style.display = 'block';
                    })
                    .catch(() => { searchResults.style.display = 'none'; searchResults.innerHTML = ''; });
            });

            function reverseGeocode(lat, lng){
                const url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng);
                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(data => { locationNameInput.value = (data && data.display_name) ? data.display_name : ''; })
                    .catch(() => { /* silently ignore */ });
            }

            // Image actions
            const meterInput = document.getElementById('meter_image');
            const btnBrowse = document.getElementById('btnBrowseImage');
            const btnTake = document.getElementById('btnTakePhoto');
            const preview = document.getElementById('imagePreview');
            btnBrowse.addEventListener('click', () => meterInput.click());
            meterInput.addEventListener('change', function(){
                const file = meterInput.files && meterInput.files[0];
                if (file) { preview.src = URL.createObjectURL(file); }
            });

            // Camera modal & capture
            const cameraModal = document.getElementById('cameraModal');
            const startCameraBtn = document.getElementById('startCamera');
            const captureBtn = document.getElementById('capturePhoto');
            const useCapturedBtn = document.getElementById('useCaptured');
            const closeModalBtn = document.getElementById('closeCameraModal');
            const videoEl = document.getElementById('cameraVideo');
            const canvasEl = document.getElementById('captureCanvas');
            let stream = null; let capturedBlob = null;

            btnTake.addEventListener('click', function(){
                cameraModal.style.display = 'flex';
                // On mobile, input capture attribute can auto-open camera
                // Fallback to getUserMedia for desktops
                if (!stream) { startCamera(); }
            });
            closeModalBtn.addEventListener('click', function(){ stopCamera(); cameraModal.style.display = 'none'; });
            startCameraBtn.addEventListener('click', startCamera);
            captureBtn.addEventListener('click', function(){
                if (!videoEl.videoWidth) return;
                canvasEl.width = videoEl.videoWidth; canvasEl.height = videoEl.videoHeight;
                const ctx = canvasEl.getContext('2d');
                ctx.drawImage(videoEl, 0, 0);
                canvasEl.style.display = 'block';
                canvasEl.toBlob(function(blob){ capturedBlob = blob; }, 'image/jpeg', 0.9);
            });
            useCapturedBtn.addEventListener('click', function(){
                if (!capturedBlob) { alert('Capture a photo first.'); return; }
                const file = new File([capturedBlob], 'camera_capture.jpg', { type: 'image/jpeg' });
                const dt = new DataTransfer(); dt.items.add(file);
                meterInput.files = dt.files;
                preview.src = URL.createObjectURL(file);
                stopCamera(); cameraModal.style.display = 'none';
            });

            async function startCamera(){
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
                    videoEl.srcObject = stream; await videoEl.play();
                    canvasEl.style.display = 'none'; capturedBlob = null;
                } catch (e) {
                    alert('Unable to access camera: ' + (e.message || e));
                }
            }
            function stopCamera(){
                if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
                videoEl.srcObject = null; canvasEl.style.display = 'none';
            }

            // Inline validation hints
            const serialEl = document.getElementById('serial_number');
            const initialEl = document.getElementById('initial_reading');
            const formEl = document.querySelector('form');
            const makeError = (el, msg) => {
                let s = el.nextElementSibling; if (!s || !s.classList || !s.classList.contains('error-text')) { s = document.createElement('div'); s.className = 'error-text'; el.parentNode.appendChild(s); }
                s.textContent = msg || '';
            };
            const clearError = el => {
                let s = el.nextElementSibling; if (s && s.classList && s.classList.contains('error-text')) { s.textContent = ''; }
            };
            serialEl.addEventListener('input', function(){
                const v = serialEl.value.trim();
                if (v.length < 5) makeError(serialEl, 'Serial should be at least 5 characters.'); else clearError(serialEl);
            });
            initialEl.addEventListener('input', function(){
                const n = parseFloat(initialEl.value);
                if (isNaN(n) || n < 0) makeError(initialEl, 'Initial reading must be 0 or greater.'); else clearError(initialEl);
            });
            formEl.addEventListener('submit', function(){
                serialEl.dispatchEvent(new Event('input')); initialEl.dispatchEvent(new Event('input'));
            });
        });
    </script>
</body>
</html>
