<?php
$meters = $data['meters'] ?? [];
$success = $data['success'] ?? '';
$error = $data['error'] ?? '';
$pending_generation_meters = $data['pending_generation_meters'] ?? [];
$pending_generation_count = $data['pending_generation_count'] ?? 0;
$bills_generated_today = $data['bills_generated_today'] ?? 0;
$overdue_bills_count = $data['overdue_bills_count'] ?? 0;
$recent_bills = $data['recent_bills'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Single Bill - Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--primary:#ff4757;--primary-dark:#e84118;--dark-bg:#1e1e2d;--sidebar-bg:#1a1a27;--card-bg:#2a2a3c;--text-light:#f8f9fa;--text-muted:#a1a5b7;--border-color:#2d2d3a}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background-color:var(--dark-bg);color:var(--text-light);line-height:1.6;display:flex;min-height:100vh;overflow-x:hidden}
        .dashboard-layout{display:flex;width:100%;min-height:100vh}
        .sidebar{width:280px;background-color:var(--sidebar-bg);padding:1.5rem 0;display:flex;flex-direction:column;position:fixed;height:100vh;top:0;left:0;z-index:1000;border-right:1px solid var(--border-color)}
        .main-content{margin-left:280px;flex-grow:1;min-height:100vh;min-width:0}
        .header-bar{background-color:var(--sidebar-bg);padding:1.25rem 2rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border-color)}
        .header-title{display:flex;align-items:center;gap:1rem}
        .card{background-color:var(--card-bg);border:1px solid var(--border-color);border-radius:.75rem;box-shadow:0 0 20px rgba(0,0,0,.1);margin:1rem}
        .card-header{padding:1rem 1.25rem;border-bottom:1px solid var(--border-color);display:flex;align-items:center;gap:.5rem;color:var(--text-light);font-weight:700}
        .card-body{padding:1rem 1.25rem}
        .grid-3{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin:1rem}
        .stat{background:#232336;border:1px solid var(--border-color);border-radius:.75rem;padding:1rem}
        .label{color:var(--text-muted);font-size:.85rem}
        .value{font-weight:800;font-size:1.4rem}
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem .9rem;border-radius:.5rem;border:1px solid var(--border-color);background:transparent;color:var(--text-light);cursor:pointer}
        .btn-primary{background-color:var(--primary);border-color:var(--primary)}
        .btn-outline{background:transparent;border-color:var(--border-color)}
        .form-row{display:flex;gap:1rem;flex-wrap:wrap}
        .form-group{flex:1;min-width:240px}
        .form-control,.form-select{width:100%;padding:.6rem .7rem;border-radius:.5rem;border:1px solid var(--border-color);background-color:#1f1f2e;color:var(--text-light)}
        .table-responsive{width:100%;overflow-x:auto}
        .data-table{width:100%;min-width:720px;border-collapse:collapse}
        .data-table th,.data-table td{padding:.75rem;border-bottom:1px solid var(--border-color);text-align:left;white-space:nowrap}
        .data-table th{color:var(--text-muted);font-weight:600;background-color:#232336}
        .reading-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;margin-top:1rem}
        .reading-box{background:#232336;border:1px solid var(--border-color);border-radius:.75rem;padding:1rem}
        @media(max-width:992px){.main-content{margin-left:0}.dashboard-layout{flex-direction:column}}
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="dashboard-layout">
        <?php $page = 'generate_single_bill'; include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="main-content" id="mainContent">
            <div class="header-bar">
                <div class="header-title"><button class="btn btn-primary" id="sidebarToggle"><i class="fas fa-bars"></i></button><div><h1 style="font-size:1.5rem;font-weight:700">Generate Single Bill</h1></div></div>
                <div><a href="index.php?page=view_bills" class="btn btn-outline"><i class="fas fa-list"></i> View Bills</a></div>
            </div>
            <?php if (!empty($error)): ?><div class="card"><div class="card-body" style="color:#f87171"><?= htmlspecialchars($error) ?></div></div><?php endif; ?>
            <?php if (!empty($success)): ?><div class="card"><div class="card-body" style="color:#34d399"><?= htmlspecialchars($success) ?></div></div><?php endif; ?>
            <div class="grid-3">
                <div class="stat"><div class="label">Pending Generation</div><div class="value"><?= (int)$pending_generation_count ?></div></div>
                <div class="stat"><div class="label">Bills Generated Today</div><div class="value"><?= (int)$bills_generated_today ?></div></div>
                <div class="stat"><div class="label">Overdue Bills</div><div class="value"><?= (int)$overdue_bills_count ?></div></div>
            </div>
            <div class="card">
                <div class="card-header"><i class="fas fa-file-invoice"></i><span>Generate Bill for Specific Meter</span></div>
                <div class="card-body">
                    <form method="post" action="index.php?page=generate_single_bill" id="generateBillForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Meter</label>
                                <select name="meter_id" id="meterSelect" class="form-select">
                                    <option value="">Select meter</option>
                                    <?php foreach ($meters as $m): $mid=(int)($m['id'] ?? $m['meter_id'] ?? 0); $label=$m['serial_number'] ?? ('Meter #'.$mid); $client=$m['client_name'] ?? ($m['client_username'] ?? ''); ?>
                                        <option value="<?= $mid ?>"><?= htmlspecialchars($label) ?><?= $client? ' - '.htmlspecialchars($client):'' ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Start Reading</label>
                                <select name="reading_id_start" id="startReadingSelect" class="form-select" disabled>
                                    <option value="">Select start reading</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>End Reading</label>
                                <select name="reading_id_end" id="endReadingSelect" class="form-select" disabled>
                                    <option value="">Select end reading</option>
                                </select>
                            </div>
                            <div class="form-group" style="align-self:end">
                                <button type="submit" class="btn btn-primary" id="generateButton" disabled><i class="fas fa-bolt"></i> Generate Bill</button>
                            </div>
                        </div>
                        <div class="reading-grid" id="readingDetails" style="display:none">
                            <div class="reading-box"><div class="label">Start Value</div><div class="value" id="startReadingValue"><span>-</span></div><div class="label" style="margin-top:.5rem">Date</div><div class="value" id="startReadingDate"><span>-</span></div></div>
                            <div class="reading-box"><div class="label">End Value</div><div class="value" id="endReadingValue"><span>-</span></div><div class="label" style="margin-top:.5rem">Date</div><div class="value" id="endReadingDate"><span>-</span></div></div>
                            <div class="reading-box"><div class="label">Consumption</div><div class="value" id="consumptionValue">0.00</div></div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><i class="fas fa-clock"></i><span>Pending Generation Meters</span></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead><tr><th>Meter</th><th>Client</th><th>Prev Reading</th><th>Latest Reading</th><th>Consumption</th><th>Estimated Amount</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php foreach (($data['pending_rows'] ?? []) as $pm): ?>
                                <tr>
                                    <td><?= (int)($pm['meter_id'] ?? 0) ?> <?= htmlspecialchars($pm['serial_number'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($pm['client_name'] ?? '') ?></td>
                                    <td><?= isset($pm['previous_reading_value']) ? number_format((float)$pm['previous_reading_value'],2) : '-' ?> (<?= htmlspecialchars($pm['previous_reading_date'] ?? '') ?>)</td>
                                    <td><?= isset($pm['latest_reading_value']) ? number_format((float)$pm['latest_reading_value'],2) : '-' ?> (<?= htmlspecialchars($pm['latest_reading_date'] ?? '') ?>)</td>
                                    <td><?= number_format((float)($pm['consumption'] ?? 0),2) ?></td>
                                    <td><?= $pm['estimated_amount']!==null ? 'KES '.number_format((float)$pm['estimated_amount'],2) : '—' ?></td>
                                    <td><a class="btn btn-outline" href="index.php?page=generate_single_bill&meter_id=<?= (int)($pm['meter_id'] ?? 0) ?>"><i class="fas fa-file-invoice"></i> Use Readings</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php if (!empty($recent_bills)): ?>
            <div class="card">
                <div class="card-header"><i class="fas fa-list"></i><span>Recent Bills</span></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead><tr><th>Bill #</th><th>Client</th><th>Amount Due</th><th>Status</th><th>Bill Date</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php foreach ($recent_bills as $bill): ?>
                                <tr>
                                    <td><?= (int)($bill['id'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars($bill['client_name'] ?? '') ?></td>
                                    <td>KES <?= number_format((float)($bill['amount_due'] ?? 0),2) ?></td>
                                    <td><?= htmlspecialchars($bill['payment_status'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($bill['bill_date'] ?? '') ?></td>
                                    <td><a href="index.php?page=view_bill_details&bill_id=<?= (int)($bill['id'] ?? 0) ?>" class="btn btn-primary"><i class="fas fa-eye"></i> View</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded',function(){
        const meterSelect=document.getElementById('meterSelect');
        const startReadingSelect=document.getElementById('startReadingSelect');
        const endReadingSelect=document.getElementById('endReadingSelect');
        const generateButton=document.getElementById('generateButton');
        const readingDetails=document.getElementById('readingDetails');
        let readingsData=[];
        meterSelect.addEventListener('change',function(){const meterId=this.value;if(meterId){startReadingSelect.innerHTML='<option value="">Select start reading</option>';endReadingSelect.innerHTML='<option value="">Select end reading</option>';startReadingSelect.disabled=true;endReadingSelect.disabled=true;generateButton.disabled=true;readingDetails.style.display='none';fetch(`index.php?page=billing_get_readings_for_meter&meter_id=${meterId}`).then(r=>r.json()).then(data=>{if(data.error){alert(data.error);return;}if(data.readings&&data.readings.length>0){readingsData=data.readings;data.readings.forEach(rd=>{const o=document.createElement('option');o.value=rd.id;const d=new Date(rd.reading_date);o.textContent=`${rd.reading_value} units - ${d.toLocaleDateString()}`;startReadingSelect.appendChild(o);});startReadingSelect.disabled=true;endReadingSelect.disabled=true;if(readingsData.length>=2){const last=readingsData[readingsData.length-1];const prev=readingsData[readingsData.length-2];startReadingSelect.value=prev.id;endReadingSelect.innerHTML='<option value="">Select end reading</option>';const sd=new Date(prev.reading_date);const later=readingsData.filter(r=>new Date(r.reading_date)>sd);later.forEach(rd=>{const o=document.createElement('option');o.value=rd.id;const d=new Date(rd.reading_date);o.textContent=`${rd.reading_value} units - ${d.toLocaleDateString()}`;endReadingSelect.appendChild(o);});endReadingSelect.disabled=false;endReadingSelect.value=last.id;updateReadingDetails(prev,last);const cons=parseFloat(last.reading_value)-parseFloat(prev.reading_value);if(cons>0){generateButton.disabled=false;}else{alert('Warning: Consumption is zero or negative.');generateButton.disabled=true;}}}else{alert('No readings found for this meter.');}}).catch(e=>{console.error(e);alert('Error fetching readings.');});}else{readingDetails.style.display='none';}});
        startReadingSelect.addEventListener('change',function(){const sid=parseInt(this.value);if(sid){endReadingSelect.innerHTML='<option value="">Select end reading</option>';endReadingSelect.disabled=true;generateButton.disabled=true;readingDetails.style.display='none';const sr=readingsData.find(r=>parseInt(r.id)===sid);if(!sr)return;const sd=new Date(sr.reading_date);const later=readingsData.filter(r=>new Date(r.reading_date)>sd);if(later.length>0){later.forEach(rd=>{const o=document.createElement('option');o.value=rd.id;const d=new Date(rd.reading_date);o.textContent=`${rd.reading_value} units - ${d.toLocaleDateString()}`;endReadingSelect.appendChild(o);});endReadingSelect.disabled=false;}else{alert('No later readings found.');}}});
        endReadingSelect.addEventListener('change',function(){const eid=parseInt(this.value);if(eid){const sid=parseInt(startReadingSelect.value);const sr=readingsData.find(r=>parseInt(r.id)===sid);const er=readingsData.find(r=>parseInt(r.id)===eid);if(sr&&er){const sv=parseFloat(sr.reading_value);const ev=parseFloat(er.reading_value);const cons=ev-sv;document.querySelector('#startReadingValue span').textContent=`${sv} units`;document.querySelector('#startReadingDate span').textContent=new Date(sr.reading_date).toLocaleDateString();document.querySelector('#endReadingValue span').textContent=`${ev} units`;document.querySelector('#endReadingDate span').textContent=new Date(er.reading_date).toLocaleDateString();document.getElementById('consumptionValue').textContent=cons.toFixed(2);readingDetails.style.display='block';generateButton.disabled=!(cons>0);}}else{readingDetails.style.display='none';generateButton.disabled=true;}});
        function updateReadingDetails(startReading,endReading){const sv=parseFloat(startReading.reading_value);const ev=parseFloat(endReading.reading_value);const cons=ev-sv;document.querySelector('#startReadingValue span').textContent=`${sv} units`;document.querySelector('#startReadingDate span').textContent=new Date(startReading.reading_date).toLocaleDateString();document.querySelector('#endReadingValue span').textContent=`${ev} units`;document.querySelector('#endReadingDate span').textContent=new Date(endReading.reading_date).toLocaleDateString();document.getElementById('consumptionValue').textContent=cons.toFixed(2);readingDetails.style.display='block';}
    });
    </script>
</body>
</html>
