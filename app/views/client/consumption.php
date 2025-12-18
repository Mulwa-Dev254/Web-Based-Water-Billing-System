<?php
// app/views/client/consumption.php
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
    header('Location: index.php?page=login');
    exit;
}
// Data from controller
$meters = $data['meters'] ?? [];
$aggregate = $data['aggregate'] ?? ['daily'=>[],'monthly'=>[],'annual'=>[]];
$years = $data['years'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consumption - AquaBill</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .client-sidebar { width: 220px; background: linear-gradient(135deg, #2c3e50, #3498db); color: white; height: 100vh; position: fixed; left: 0; top: 0; padding: 20px 0; box-shadow: 2px 0 10px rgba(0,0,0,0.1); z-index: 1000; }
        .client-sidebar h3 { text-align: center; margin-bottom: 25px; font-weight: 600; font-size: 1.1rem; padding: 0 15px; }
        .client-sidebar ul { list-style: none; padding: 0; margin: 0; }
        .client-sidebar li a { display: block; padding: 10px 15px; color: white; text-decoration: none; transition: all 0.3s ease; border-left: 3px solid transparent; font-size: 0.9rem; }
        .client-sidebar li a:hover { background-color: rgba(255, 255, 255, 0.1); border-left: 3px solid #fff; }
        .client-sidebar li a.active { background-color: rgba(255,255,255,0.2); border-left: 3px solid #fff; font-weight: 500; }
        .client-sidebar li a i { margin-right: 8px; width: 18px; text-align: center; font-size: 0.9rem; }
        .main-content { margin-left: 240px; padding: 15px; }
        .client-header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0; }
        .client-header-bar h1 { font-size: 1.5rem; color: #2c3e50; margin: 0; font-weight: 600; }
        .user-info { display: flex; align-items: center; gap: 12px; font-size: 0.9rem; }
        .user-info a { color: #e74c3c; text-decoration: none; font-weight: 500; }
        .user-info a:hover { text-decoration: underline; }
        .meter-card { cursor: pointer; transition: transform .15s ease, box-shadow .15s ease; }
        .meter-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.08); }
        .overlay-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.35); backdrop-filter: blur(2px); display: none; z-index: 1000; }
        .overlay-card { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 92%; max-width: 860px; background: #fff; border-radius: 10px; box-shadow: 0 12px 24px rgba(0,0,0,0.2); z-index: 1001; display: none; }
        .overlay-card .card-header { display:flex; justify-content:space-between; align-items:center; background: linear-gradient(135deg, #3498db, #2980b9); color:#fff; }
        .overlay-close { border:none; background:transparent; color:#fff; font-size:1.2rem; cursor:pointer; }
        .toolbar { display:flex; gap:10px; align-items:center; margin-bottom:8px; }
        .toolbar .btn { padding:6px 10px; }
    </style>
</head>
<body class="client-theme">
<?php include_once dirname(__DIR__) . '/includes/client_sidebar.php'; ?>
<div class="main-content">
    <div class="client-header-bar">
        <h1><i class="fas fa-chart-line me-2"></i> Consumption</h1>
        <div class="user-info">
            <span>Hello, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-chart-bar me-2"></i> Combined Consumption</span>
            <div class="toolbar">
                <div class="btn-group" role="group" aria-label="Timeframe">
                    <button type="button" class="btn btn-outline-primary" id="tfDaily">Daily</button>
                    <button type="button" class="btn btn-outline-primary" id="tfMonthly">Monthly</button>
                    <button type="button" class="btn btn-outline-primary" id="tfAnnual">Annual</button>
                </div>
                <select class="form-select form-select-sm" id="yearSelect" style="width:120px;">
                    <option value="">All Years</option>
                    <?php foreach ($years as $y): ?>
                        <option value="<?= (int)$y ?>"><?= (int)$y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="card-body">
            <canvas id="combinedChart" height="120"></canvas>
        </div>
    </div>

    <div class="row">
        <?php foreach ($meters as $m): ?>
        <div class="col-md-4 mb-3">
            <div class="card meter-card" data-id="<?= (int)$m['id'] ?>">
                <div class="card-header bg-light"><strong><i class="fas fa-tint me-2 text-primary"></i><?= htmlspecialchars($m['serial_number']) ?></strong></div>
                <div class="card-body">
                    <div><small>Installed</small> <strong><?= !empty($m['installed_at']) ? date('d M Y', strtotime($m['installed_at'])) : 'N/A' ?></strong></div>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary open-meter-chart" data-id="<?= (int)$m['id'] ?>"><i class="fas fa-chart-line me-2"></i> View Consumption</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Meter Overlay -->
<div class="overlay-backdrop" id="overlayBackdrop"></div>
<div class="overlay-card" id="overlayCard">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-tint me-2"></i><span id="ovTitle">Meter</span></h5>
        <button class="overlay-close" id="ovClose"><i class="fas fa-times"></i></button>
    </div>
    <div class="card-body">
        <div class="toolbar">
            <div class="btn-group" role="group" aria-label="Timeframe Meter">
                <button type="button" class="btn btn-outline-primary" id="mDaily">Daily</button>
                <button type="button" class="btn btn-outline-primary" id="mMonthly">Monthly</button>
                <button type="button" class="btn btn-outline-primary" id="mAnnual">Annual</button>
            </div>
            <select class="form-select form-select-sm" id="mYearSelect" style="width:120px;"><option value="">All Years</option></select>
        </div>
        <canvas id="meterChart" height="120"></canvas>
        <div class="mt-3">
            <a href="index.php?page=client_meters" class="btn btn-secondary"><i class="fas fa-map me-2"></i> View Full Information</a>
        </div>
    </div>
    <div class="card-footer text-end">
        <button class="btn btn-light" id="ovClose2"><i class="fas fa-times me-2"></i> Close</button>
    </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const aggregate = <?= json_encode($aggregate) ?>;
const meters = <?= json_encode($meters) ?>;

function groupByYear(keys){
    const set = new Set();
    keys.forEach(k => { const y = String(k).slice(0,4); if (y) set.add(y); });
    return Array.from(set).sort();
}

function filterSeriesByYear(series, year){
    if (!year) return series;
    const out = {};
    Object.keys(series).forEach(k => { if (String(k).startsWith(String(year))) out[k] = series[k]; });
    return out;
}

function toSortedArrays(series){
    const labels = Object.keys(series).sort();
    const values = labels.map(k => Number(series[k]));
    return { labels, values };
}

// Combined chart
let combinedChart;
function renderCombined(type, year){
    function getSeries(m){
        const base = type === 'daily' ? m.series.daily : type === 'monthly' ? m.series.monthly : m.series.annual;
        return type === 'annual' ? base : filterSeriesByYear(base, year);
    }
    function unionLabels(seriesArr){
        const set = new Set();
        seriesArr.forEach(s => { Object.keys(s).forEach(k => set.add(k)); });
        return Array.from(set).sort();
    }
    const seriesList = meters.map(m => ({ m, s: getSeries(m) }));
    const labels = unionLabels(seriesList.map(x => x.s));
    const palette = ['#3498db','#2ecc71','#e74c3c','#9b59b6','#f1c40f','#1abc9c','#e67e22','#34495e','#16a085','#27ae60','#2980b9','#8e44ad','#d35400','#2c3e50'];
    const datasets = seriesList.map((x, i) => ({
        label: (x.m.serial_number || ('Meter ' + x.m.id)),
        data: labels.map(k => Number(x.s[k] || 0)),
        backgroundColor: palette[i % palette.length]
    }));
    const ctx = document.getElementById('combinedChart');
    if (combinedChart) combinedChart.destroy();
    combinedChart = new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets },
        options: { responsive: true, plugins: { legend: { display: true } }, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } }
    });
}

// Meter chart
let meterChart;
function renderMeterChart(meterId, type, year){
    const m = meters.find(x => Number(x.id) === Number(meterId));
    if (!m || !m.series) return;
    const base = type === 'daily' ? m.series.daily : type === 'monthly' ? m.series.monthly : m.series.annual;
    const s = (type === 'annual') ? base : filterSeriesByYear(base, year);
    const { labels, values } = toSortedArrays(s);
    const ctx = document.getElementById('meterChart');
    if (meterChart) meterChart.destroy();
    meterChart = new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Consumption (units)', data: values, backgroundColor: '#2ecc71' }] },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // Set active link
    document.querySelectorAll('.client-sidebar li a').forEach(a => { if (a.href.includes('page=client_consumption')) a.classList.add('active'); });

    // Combined timeframe buttons
    let currentTF = 'monthly';
    let currentYear = '';
    renderCombined(currentTF, currentYear);
    document.getElementById('tfDaily').addEventListener('click', () => { currentTF = 'daily'; renderCombined(currentTF, currentYear); });
    document.getElementById('tfMonthly').addEventListener('click', () => { currentTF = 'monthly'; renderCombined(currentTF, currentYear); });
    document.getElementById('tfAnnual').addEventListener('click', () => { currentTF = 'annual'; renderCombined(currentTF, ''); });
    document.getElementById('yearSelect').addEventListener('change', (e) => { currentYear = e.target.value; renderCombined(currentTF, currentYear); });

    // Meter overlay interactions
    const backdrop = document.getElementById('overlayBackdrop');
    const card = document.getElementById('overlayCard');
    function openOverlay(title){ document.getElementById('ovTitle').textContent = title; backdrop.style.display='block'; card.style.display='block'; }
    function closeOverlay(){ backdrop.style.display='none'; card.style.display='none'; }
    document.getElementById('ovClose').addEventListener('click', closeOverlay);
    document.getElementById('ovClose2').addEventListener('click', closeOverlay);
    backdrop.addEventListener('click', closeOverlay);

    // Populate meter year select when opening
    let currentMeterId = null; let mTF = 'monthly'; let mYear = '';
    document.querySelectorAll('.open-meter-chart').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id; currentMeterId = id; mTF = 'monthly'; mYear = '';
            const m = meters.find(x => Number(x.id) === Number(id));
            const keys = Object.keys(m.series.daily).concat(Object.keys(m.series.monthly));
            const years = groupByYear(keys);
            const select = document.getElementById('mYearSelect');
            select.innerHTML = '<option value="">All Years</option>' + years.map(y => `<option value="${y}">${y}</option>`).join('');
            openOverlay('Meter ' + (m.serial_number || id));
            renderMeterChart(id, mTF, mYear);
        });
    });
    document.getElementById('mDaily').addEventListener('click', () => { mTF='daily'; renderMeterChart(currentMeterId, mTF, mYear); });
    document.getElementById('mMonthly').addEventListener('click', () => { mTF='monthly'; renderMeterChart(currentMeterId, mTF, mYear); });
    document.getElementById('mAnnual').addEventListener('click', () => { mTF='annual'; renderMeterChart(currentMeterId, mTF, ''); });
    document.getElementById('mYearSelect').addEventListener('change', (e) => { mYear = e.target.value; renderMeterChart(currentMeterId, mTF, mYear); });
});
</script>
</body>
</html>
