<?php
// app/views/client/meters.php
// This page displays all meters in the system for clients

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php?page=login');
    exit;
}

$clientId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Meters | Client Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Inherit client theme styles */
        .client-theme {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .client-dashboard-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .client-sidebar {
            width: 220px;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 20px 0;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .client-sidebar h3 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 0 15px;
        }

        .client-sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .client-sidebar li a {
            display: block;
            padding: 10px 15px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-size: 0.9rem;
        }

        .client-sidebar li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid #fff;
        }

        .client-sidebar li a.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 3px solid #fff;
            font-weight: 500;
        }

        .client-sidebar li a i {
            margin-right: 8px;
            width: 18px;
            text-align: center;
            font-size: 0.9rem;
        }

        /* Main Content Styling */
        .client-main-content {
            flex: 1;
            transition: all 0.3s ease;
            padding: 15px;
        }
/* Summary Cards */
.summary-grid { display:grid; grid-template-columns: repeat(4, 1fr); gap:16px; margin-bottom:14px; }
.summary-grid { display:grid; grid-template-columns: repeat(4, 1fr); gap:16px; margin-bottom:14px; }
@media (max-width: 992px) { .summary-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px) { .summary-grid { grid-template-columns: 1fr; } }
.summary-card { border-radius:12px; padding:16px; box-shadow:0 6px 16px rgba(0,0,0,0.06); display:flex; align-items:center; gap:12px; color:#fff; }
.summary-card--assigned { background: linear-gradient(135deg, #4f7bd7, #2d5bbf); }
.summary-card--waiting { background: linear-gradient(135deg, #f59e0b, #d97706); }
.summary-card--flagged { background: linear-gradient(135deg, #ef4444, #dc2626); }
.summary-card--installed { background: linear-gradient(135deg, #10b981, #059669); }
.summary-icon { width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; background: rgba(255,255,255,0.18); }
.summary-title { font-size:12px; color:rgba(255,255,255,0.9); margin:0; }
.summary-value { font-size:22px; font-weight:700; color:#fff; margin:0; }

        /* Page layout */
        .content-wrap { max-width: 1200px; margin: 0 auto; }
        .page-grid { display:grid; grid-template-columns: 2fr 1fr; gap:16px; align-items:start; }
        .sticky-sidebar { position: sticky; top: 16px; }

        /* Dashboard panels */
        .dash-grid { display:grid; grid-template-columns: repeat(12, 1fr); gap:16px; }
        .panel { background:#fff; border:1px solid #eef2f7; border-radius:12px; box-shadow:0 8px 18px rgba(0,0,0,0.06); }
        .panel-header { padding:12px 16px; border-bottom:1px solid #eef2f7; display:flex; align-items:center; justify-content:space-between; }
        .panel-header h3 { margin:0; font-size:14px; color:#374151; font-weight:700; }
        .panel-body { padding:14px 16px; }
        .col-4 { grid-column: span 4; }
        .col-8 { grid-column: span 8; }
        .col-6 { grid-column: span 6; }
        .muted { color:#6b7280; }
        .mini-stats { display:grid; grid-template-columns: repeat(3, 1fr); gap:10px; }
        .mini-stats .stat { background:#f9fafb; border:1px solid #eef2f7; border-radius:10px; padding:10px; }
        .table { width:100%; border-collapse: collapse; }
        .table th, .table td { padding:8px 10px; border-bottom:1px solid #f1f5f9; font-size:13px; }
        .table th { text-align:left; color:#6b7280; font-weight:600; }
        .badge { display:inline-block; padding:4px 8px; border-radius:9999px; font-size:12px; font-weight:600; }
        .badge.success { background:#ecfdf5; color:#059669; }
        .badge.warning { background:#fff7ed; color:#f59e0b; }
        .badge.danger { background:#fef2f2; color:#ef4444; }
        .placeholder { display:flex; align-items:center; justify-content:center; min-height:160px; color:#9ca3af; }

        .client-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .client-header-bar h1 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin: 0;
            font-weight: 600;
        }

        /* Meters Grid Styling */
.meters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
    margin-top: 10px;
        }
.cards-scroll { max-height: 520px; overflow-y: auto; padding-right: 6px; }
.cards-scroll::-webkit-scrollbar { width: 8px; }
.cards-scroll::-webkit-scrollbar-thumb { background:#d1d5db; border-radius:8px; }

        .meter-card {
            background: linear-gradient(180deg, #ffffff, #f7f9fc);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            border: 1px solid #eef2f7;
        }

        .meter-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.12);
        }

.meter-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-bottom: 1px solid #eef2f7;
        }
/* New Ribbon */
.ribbon-wrap { position:relative; }
.ribbon { position:absolute; top:12px; left:-6px; background:linear-gradient(90deg, #10b981, #34d399); color:#fff; padding:4px 10px; font-size:12px; font-weight:700; border-top-right-radius:6px; border-bottom-right-radius:6px; box-shadow:0 6px 12px rgba(16,185,129,0.35); }

        .meter-details {
            padding: 15px;
        }

        .meter-details h3 {
            margin: 0 0 6px;
            color: #1f2937;
            font-size: 1.15rem;
            font-weight: 700;
        }
        .meta-row { display:flex; align-items:center; gap:10px; margin-bottom:6px; color:#4b5563; }
        .meta-pill { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:9999px; background:#eef2f7; color:#374151; font-size:12px; font-weight:600; }

        .meter-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .meter-info-item {
            display: flex;
            align-items: center;
        }

        .meter-info-item i {
            width: 20px;
            margin-right: 8px;
            color: #3498db;
        }

        .no-meters {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .no-meters i {
            font-size: 3rem;
            color: #d1d1d1;
            margin-bottom: 15px;
        }

        .no-meters p {
            color: #777;
            font-size: 1.1rem;
        }
    </style>
    <!-- UI polish overrides -->
    <style>
        .content-wrap { padding: 0 6px; }
        .page-grid { gap: 20px; }
        .panel { border-radius: 14px; box-shadow: 0 10px 20px rgba(17,24,39,0.06); }
        .panel-header { padding: 14px 18px; }
        .panel-header h3 { font-size: 15px; color:#1f2937; letter-spacing:0.2px; }
        .panel-body { padding: 16px 18px; }

        .summary-card { box-shadow:0 6px 16px rgba(17,24,39,0.06); transition:transform .18s ease, box-shadow .18s ease; }
        .summary-card:hover { transform: translateY(-2px); box-shadow:0 12px 24px rgba(17,24,39,0.08); }

        .meters-grid { gap: 16px; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        .meter-card { border-radius: 14px; box-shadow: 0 8px 18px rgba(17,24,39,0.08); transition: transform 0.22s ease, box-shadow 0.22s ease; display:flex; flex-direction:column; }
        .meter-card:hover { transform: translateY(-4px); box-shadow: 0 16px 28px rgba(17,24,39,0.12); }
        .meter-image { height: 180px; object-position: center; }
        .meter-details { padding: 16px; }
        .meter-details h3 { margin: 0 0 8px; font-size: 1.1rem; }
        .meter-info-item i { width: 18px; color:#3b82f6; }

        .table th, .table td { padding:10px 12px; border-bottom-color:#eef2f7; }
        .badge { padding:4px 10px; border:1px solid #e5e7eb; }
        .badge.success { background:#f0fdf4; color:#065f46; }
        .badge.warning { background:#fffbeb; color:#92400e; }
        .badge.danger { background:#fef2f2; color:#991b1b; }
    </style>
</head>
<?php include_once __DIR__ . '/partials/status_badges.php'; ?>
<body class="client-theme">
    <div class="client-dashboard-layout">
        <!-- Sidebar -->
        <?php include_once dirname(__DIR__) . '/includes/client_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="client-main-content">
            <div class="client-header-bar">
                <h1><i class="fas fa-tachometer-alt"></i> My Meters</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Client'); ?></span>
                    <a href="index.php?page=logout" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <!-- Meters Content -->
            <div class="meters-container">
                <?php
                // Use data provided by controller
                $meters = $data['meters'] ?? [];
                if ($meters && count($meters) > 0) {
                    // Summary counts
                    $totalAssigned = 0; $waitingInstall = 0; $flagged = 0; $installed = 0;
                    foreach ($meters as $m) {
                        $totalAssigned++;
                        $s = $m['meter_status'] ?? ($m['meter_table_status'] ?? ($m['status'] ?? ''));
                        if ($s === 'waiting_installation') $waitingInstall++;
                        if ($s === 'flagged') $flagged++;
                        if ($s === 'installed' || $s === 'verified') $installed++;
                    }
                    ?>
                    <div class="content-wrap">
                        <div class="summary-grid">
                            <div class="summary-card summary-card--assigned">
                                <div class="summary-icon"><i class="fas fa-tachometer-alt"></i></div>
                                <div>
                                    <p class="summary-title">Total Meters Assigned</p>
                                    <p class="summary-value"><?php echo (int)$totalAssigned; ?></p>
                                </div>
                            </div>
                            <div class="summary-card summary-card--waiting">
                                <div class="summary-icon"><i class="fas fa-clock"></i></div>
                                <div>
                                    <p class="summary-title">Waiting for Installation</p>
                                    <p class="summary-value"><?php echo (int)$waitingInstall; ?></p>
                                </div>
                            </div>
                            <div class="summary-card summary-card--flagged">
                                <div class="summary-icon"><i class="fas fa-flag"></i></div>
                                <div>
                                    <p class="summary-title">Flagged Meters</p>
                                    <p class="summary-value"><?php echo (int)$flagged; ?></p>
                                </div>
                            </div>
                            <div class="summary-card summary-card--installed">
                                <div class="summary-icon"><i class="fas fa-check-circle"></i></div>
                                <div>
                                    <p class="summary-title">Installed / Verified</p>
                                    <p class="summary-value"><?php echo (int)$installed; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="page-grid">
                            <div class="col-main">
                    <div class="panel">
                        <div class="panel-header"><h3><i class="fas fa-tachometer-alt"></i> My Meters</h3></div>
                        <div class="panel-body">
                            <div class="meters-grid">
                                <?php foreach ($meters as $meter):
                                    $imagePath = !empty($meter['photo_url']) ? $meter['photo_url'] : 'public/images/default-meter.jpg';
                                    $statusNow = $meter['meter_status'] ?? ($meter['meter_table_status'] ?? ($meter['status'] ?? 'N/A'));
                                    $isNew = false;
                                    if (!empty($meter['verification_date'])) {
                                        $verTs = strtotime($meter['verification_date']);
                                        if ($verTs !== false) { $isNew = (time() - $verTs) <= (4*24*60*60); }
                                    }
                                ?>
                                <div class="meter-card ribbon-wrap">
                                    <?php if ($isNew): ?><div class="ribbon">NEW</div><?php endif; ?>
                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Meter Image" class="meter-image">
                                    <div class="meter-details">
                                        <h3>Meter #<?php echo htmlspecialchars($meter['serial_number'] ?? ($meter['meter_serial'] ?? 'N/A')); ?></h3>
                                        <div class="meta-row">
                                            <span class="meta-pill"><i class="fas fa-tag"></i><?php echo htmlspecialchars($meter['meter_type'] ?? 'N/A'); ?></span>
                                            <span class="meta-pill"><i class="fas fa-info-circle"></i><?php echo htmlspecialchars($statusNow); ?></span>
                                            <?php $src = $meter['source'] ?? null; if ($src): ?>
                                            <span class="meta-pill"><i class="fas fa-industry"></i>Source: <?php echo htmlspecialchars(ucfirst($src)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="meter-info">
                                            <div class="meter-info-item">
                                                <i class="fas fa-tag"></i>
                                                <span>Type: <?php echo htmlspecialchars($meter['meter_type'] ?? 'N/A'); ?></span>
                                            </div>
                                            <div class="meter-info-item">
                                                <i class="fas fa-tachometer-alt"></i>
                                                <span>Initial Reading: <?php echo htmlspecialchars($meter['initial_reading'] ?? $meter['initial_reading'] ?? '0'); ?></span>
                                            </div>
                                            <div class="meter-info-item">
                                                <i class="fas fa-calendar-alt"></i>
                                                <span>Verified: <?php echo htmlspecialchars($meter['verification_date'] ?? ($meter['installation_date'] ?? 'Not specified')); ?></span>
                                            </div>
                                            <div class="meter-info-item">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span>Location: <?php echo htmlspecialchars($meter['gps_location'] ?? 'Not specified'); ?></span>
                                            </div>
                                            <div class="meter-info-item">
                                                <i class="fas fa-info-circle"></i>
                                                <span>Status: <?php echo htmlspecialchars($statusNow); ?></span>
                                            </div>
                                            <?php if (isset($meter['current_reading'])): ?>
                                            <div class="meter-info-item">
                                                <i class="fas fa-bolt"></i>
                                                <span>Current Reading: <?php echo htmlspecialchars($meter['current_reading']); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (!empty($meter['current_reading_date'])): ?>
                                            <div class="meter-info-item">
                                                <i class="fas fa-clock"></i>
                                                <span>Last Read: <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($meter['current_reading_date']))); ?></span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                            </div> <!-- /col-main -->
                            <div class="col-side sticky-sidebar">
                                <!-- Billing Overview -->
                                <div class="panel">
                                    <div class="panel-header">
                                        <h3><i class="fas fa-file-invoice-dollar"></i> Billing Overview</h3>
                                    </div>
                                    <div class="panel-body">
                                        <?php $summary = $data['billingSummary'] ?? []; ?>
                                        <?php if (!empty($summary)) { ?>
                                            <div class="mini-stats" style="margin-bottom:8px;">
                                                <?php $info = $data['billingInfo'] ?? []; ?>
                                                <div class="stat">
                                                    <div class="muted">Total Bills</div>
                                                    <div style="font-weight:700; font-size:18px;"><?php echo (int)($info['total_bills'] ?? ($summary['total_bills'] ?? 0)); ?></div>
                                                </div>
                                                <div class="stat">
                                                    <div class="muted">Total Due</div>
                                                    <div style="font-weight:700; font-size:18px;">Ksh <?php echo number_format((float)($info['total_due_effective'] ?? 0), 2); ?></div>
                                                </div>
                                                <div class="stat">
                                                    <div class="muted">Outstanding</div>
                                                    <div style="font-weight:700; font-size:18px;">Ksh <?php echo number_format((float)($info['outstanding_effective'] ?? ($summary['total_balance'] ?? 0)), 2); ?></div>
                                                </div>
                                            </div>
                                            <div class="mini-stats">
                                                <div class="stat">
                                                    <div class="muted">Overdue</div>
                                                    <div style="font-weight:700; font-size:18px; display:flex; align-items:center; gap:8px;">
                                                        <span>Ksh <?php echo number_format((float)($summary['overdue_amount'] ?? 0), 2); ?></span>
                                                        <span class="badge <?php echo ((int)($summary['overdue_count'] ?? 0) > 0) ? 'danger' : 'success'; ?>"><?php echo (int)($summary['overdue_count'] ?? 0); ?></span>
                                                    </div>
                                                </div>
                                                <div class="stat">
                                                    <div class="muted">Pending Payments</div>
                                                    <div style="font-weight:700; font-size:18px; display:flex; align-items:center; gap:8px;">
                                                        <span>Ksh <?php echo number_format((float)(($info['pending_payments_amount'] ?? 0)), 2); ?></span>
                                                        <span class="badge <?php echo ((int)($info['pending_payments_count'] ?? 0) > 0) ? 'warning' : 'success'; ?>"><?php echo (int)($info['pending_payments_count'] ?? 0); ?></span>
                                                    </div>
                                                </div>
                                                <div class="stat">
                                                    <div class="muted">Total Paid</div>
                                                    <div style="font-weight:700; font-size:18px;">Ksh <?php echo number_format((float)($info['total_paid_payments'] ?? ($summary['total_paid'] ?? 0)), 2); ?></div>
                                                </div>
                                                <div class="stat">
                                                    <div class="muted">Service Due</div>
                                                    <div style="font-weight:700; font-size:18px; display:flex; align-items:center; gap:8px;">
                                                        <span>Ksh <?php echo number_format((float)($info['service_due_amount'] ?? 0), 2); ?></span>
                                                        <span class="badge <?php echo ((int)($info['service_pending_count'] ?? 0) > 0) ? 'warning' : 'success'; ?>"><?php echo (int)($info['service_pending_count'] ?? 0); ?></span>
                                                    </div>
                                                </div>
                                                <div class="stat">
                                                    <div class="muted">Service Paid</div>
                                                    <div style="font-weight:700; font-size:18px;">Ksh <?php echo number_format((float)($info['service_paid_amount'] ?? 0), 2); ?></div>
                                                </div>
                                            </div>
                                            <div style="margin-top:8px; font-size:13px; color:#4b5563; display:flex; gap:16px;">
                                                <span>Avg Consumption: <?php echo number_format((float)($summary['average_consumption'] ?? 0), 2); ?> m³</span>
                                                <span>Avg Bill: Ksh <?php echo number_format((float)($summary['average_amount'] ?? 0), 2); ?></span>
                                                <span>Last Month: <?php echo number_format((float)($summary['last_month_consumption'] ?? 0), 2); ?> m³ • Ksh <?php echo number_format((float)($summary['last_month_amount'] ?? 0), 2); ?></span>
                                            </div>
                                        <?php } else { echo '<div class="placeholder">No billing data yet</div>'; } ?>
                                    </div>
                                </div>
                                <!-- Alerts Panel -->
                                <div class="panel" style="margin-top:16px;">
                                    <div class="panel-header">
                                        <h3><i class="fas fa-bell"></i> Alerts</h3>
                                    </div>
                                    <div class="panel-body">
                                        <?php $alerts = $data['alerts'] ?? []; if ($alerts) { ?>
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Time</th>
                                                        <th>Type</th>
                                                        <th>Message</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($alerts as $a): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($a['created_at']))); ?></td>
                                                        <td><span class="badge <?php echo ($a['type']==='disconnection_notice')?'danger':(($a['type']==='payment_reminder')?'warning':'success'); ?>"><?php echo htmlspecialchars(str_replace('_',' ', $a['type'])); ?></span></td>
                                                        <td><?php echo htmlspecialchars($a['message']); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php } else { echo '<div class="placeholder">No alerts yet</div>'; } ?>
                                    </div>
                                </div>
                            </div> <!-- /col-side -->
                        </div> <!-- /page-grid -->
                    </div> <!-- /content-wrap -->

                    <!-- Row 2: Trend + Recent Bills -->
                    <div class="content-wrap" style="margin-top:16px;">
                        <div class="page-grid">
                            <div class="col-main">
                                <div class="panel">
                                    <div class="panel-header">
                                        <h3><i class="fas fa-chart-line"></i> Consumption Trend</h3>
                                    </div>
                                    <div class="panel-body">
                                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                                            <div class="btn-group" role="group" aria-label="Timeframe">
                                                <button type="button" class="btn btn-secondary" id="tfDaily">Daily</button>
                                                <button type="button" class="btn btn-secondary" id="tfMonthly">Monthly</button>
                                                <button type="button" class="btn btn-secondary" id="tfAnnual">Annual</button>
                                            </div>
                                            <label for="meterSelect" class="muted">View:</label>
                                            <select id="meterSelect" style="padding:6px 8px; border:1px solid #e5e7eb; border-radius:8px;">
                                                <option value="all">All Meters</option>
                                                <?php foreach (($data['trendMeters'] ?? []) as $m): ?>
                                                    <option value="<?php echo (int)$m['id']; ?>">Meter #<?php echo htmlspecialchars($m['serial'] ?: (string)$m['id']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <canvas id="consumptionChart" height="120"></canvas>
                                        <?php if (empty($data['trendValues'])) { echo '<div class="placeholder">No readings to plot yet</div>'; } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-side">
                                <div class="panel">
                                    <div class="panel-header">
                                        <h3><i class="fas fa-receipt"></i> Recent Activity</h3>
                                    </div>
                                    <div class="panel-body">
                                        <?php 
                                            $recentPayments = $data['recentPayments'] ?? []; 
                                            $hasPayments = !empty($recentPayments);
                                            $rows = [];
                                            if ($hasPayments) {
                                                foreach ($recentPayments as $p) {
                                                    $type = strtolower($p['type'] ?? '');
                                                    $label = ($type === 'bill_payment') ? 'Bill Payment' : (($type === 'service_payment') ? 'Service Payment' : (($type === 'plan_renewal') ? 'Plan Renewal' : 'Payment'));
                                                    $amount = (float)($p['amount'] ?? 0);
                                                    $status = strtolower($p['status'] ?? 'pending');
                                                    $cls = 'status-pending';
                                                    if (in_array($status, ['completed','confirmed_and_verified'], true)) { $cls = 'status-completed'; }
                                                    elseif ($status === 'paid') { $cls = 'status-paid'; }
                                                    elseif (in_array($status, ['failed','rejected'], true)) { $cls = 'status-failed'; }
                                                    elseif ($status === 'cancelled') { $cls = 'status-cancelled'; }
                                                    elseif ($status === 'pending_payment') { $cls = 'status-pending_payment'; }
                                                    $actionUrl = 'index.php?page=client_payments';
                                                    if ($type === 'bill_payment') { $actionUrl = 'index.php?page=client_payments&bill_id=' . (int)($p['reference_id'] ?? 0) . '&mode=view#billStatusTracker'; }
                                                    elseif ($type === 'service_payment') { $actionUrl = 'index.php?page=client_payments&service_id=' . (int)($p['reference_id'] ?? 0) . '&mode=view#serviceStatusTracker'; }
                                                    elseif ($type === 'plan_renewal') { $actionUrl = 'index.php?page=client_my_plans'; }
                                                    $rows[] = [ 'type'=>$label, 'amount'=>$amount, 'status'=>$status, 'cls'=>$cls, 'url'=>$actionUrl ];
                                                }
                                            } else {
                                                $recentBills = $data['recentBills'] ?? [];
                                                foreach ($recentBills as $b) {
                                                    $label = 'Bill';
                                                    $amount = (float)($b['amount_due'] ?? 0);
                                                    $status = strtolower($b['payment_status'] ?? 'pending');
                                                    $cls = 'status-pending';
                                                    if (in_array($status, ['confirmed_and_verified'], true)) { $cls = 'status-completed'; }
                                                    elseif ($status === 'paid') { $cls = 'status-paid'; }
                                                    elseif (in_array($status, ['failed','rejected'], true)) { $cls = 'status-failed'; }
                                                    elseif ($status === 'cancelled') { $cls = 'status-cancelled'; }
                                                    $rows[] = [ 'type'=>$label, 'amount'=>$amount, 'status'=>$status, 'cls'=>$cls, 'url'=>'index.php?page=client_bills' ];
                                                }
                                            }
                                            if (!empty($rows)) { 
                                        ?>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Bill Type</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($rows as $r): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($r['type']); ?></td>
                                                    <td>Ksh <?php echo number_format($r['amount'], 2); ?></td>
                                                    <td><span class="status-badge <?php echo htmlspecialchars($r['cls']); ?>"><?php echo htmlspecialchars(str_replace('_',' ', ucfirst($r['status']))); ?></span></td>
                                                    <td><a href="<?php echo htmlspecialchars($r['url']); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i> View</a></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <?php } else { echo '<div class="placeholder">No recent activity found</div>'; } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="no-meters">
                        <i class="fas fa-tachometer-alt"></i>
                        <p>No meters have been assigned to your account yet.</p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function(){
        var ctxEl = document.getElementById('consumptionChart');
        if (!ctxEl) return;
        var labelsD = <?php echo json_encode($data['trendLabels'] ?? []); ?>;
        var valuesD = <?php echo json_encode($data['trendValues'] ?? []); ?>;
        var seriesD = <?php echo json_encode($data['trendSeries'] ?? []); ?>;
        var labelsM = <?php echo json_encode($data['trendLabelsMonthly'] ?? []); ?>;
        var valuesM = <?php echo json_encode($data['trendValuesMonthly'] ?? []); ?>;
        var seriesM = <?php echo json_encode($data['trendSeriesMonthly'] ?? []); ?>;
        var labelsY = <?php echo json_encode($data['trendLabelsAnnual'] ?? []); ?>;
        var valuesY = <?php echo json_encode($data['trendValuesAnnual'] ?? []); ?>;
        var seriesY = <?php echo json_encode($data['trendSeriesAnnual'] ?? []); ?>;
        var select = document.getElementById('meterSelect');
        var tf = 'daily';
        var labels = labelsD;
        var allValues = valuesD;
        var series = seriesD;
        if (!labels || labels.length === 0) return;
        var chart = new Chart(ctxEl.getContext('2d'), {
            type: 'line',
            data: { labels: labels, datasets: [{
                label: 'm³', data: allValues, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.15)', tension: 0.3, fill: true
            }]},
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
        function updateDataset(mid) {
            var vals = allValues;
            if (mid && mid !== 'all') {
                vals = series[mid] || labels.map(function(){ return 0; });
            }
            chart.data.datasets[0].data = vals;
            chart.update();
        }
        function setTimeframe(newTf) {
            tf = newTf;
            if (tf === 'daily') { labels = labelsD; allValues = valuesD; series = seriesD; }
            else if (tf === 'monthly') { labels = labelsM; allValues = valuesM; series = seriesM; }
            else { labels = labelsY; allValues = valuesY; series = seriesY; }
            chart.data.labels = labels;
            updateDataset(select ? select.value : 'all');
        }
        if (select) {
            select.addEventListener('change', function(){ updateDataset(this.value); });
        }
        var bD = document.getElementById('tfDaily');
        var bM = document.getElementById('tfMonthly');
        var bY = document.getElementById('tfAnnual');
        if (bD) bD.addEventListener('click', function(){ setTimeframe('daily'); });
        if (bM) bM.addEventListener('click', function(){ setTimeframe('monthly'); });
        if (bY) bY.addEventListener('click', function(){ setTimeframe('annual'); });
    })();
    </script>
</body>
</html>
