<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Manager Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --dark-bg: #1e1e2d;
            --darker-bg: #151521;
            --sidebar-bg: #1a1a27;
            --card-bg: #2a2a3c;
            --text-light: #f8f9fa;
            --text-muted: #a1a5b7;
            --border-color: #2d2d3a;
            --success: #10b981;
            --info: #3b82f6;
            --warning: #f59e0b;
            --danger: #ef4444;
            --purple: #8b5cf6;
        }

        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Dashboard Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 16rem; /* Width of sidebar */
            padding: 1.5rem;
        }

        /* Dashboard Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-card .icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .stat-card .title {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .stat-card .value {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .stat-card .change {
            font-size: 0.75rem;
            display: flex;
            align-items: center;
        }

        .stat-card .change.positive {
            color: var(--success);
        }

        .stat-card .change.negative {
            color: var(--danger);
        }

        /* Recent Transactions */
        .transactions-card {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .transactions-card h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #111827;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
        }

        .transactions-table th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        .transactions-table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .transactions-table tr:last-child td {
            border-bottom: none;
        }

        .transactions-table .status {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .transactions-table .status.completed { background: linear-gradient(135deg, #ecfdf5, #d1fae5); color: #065f46; border: 1px solid #10b981; }
        .transactions-table .status.paid { background: linear-gradient(135deg, #e8fff5, #c6f7e2); color: #0f5132; border: 1px solid #2ecc71; }
        .transactions-table .status.confirmed { background: linear-gradient(135deg, #e6fffa, #b2f5ea); color: #0e7490; border: 1px solid #14b8a6; }
        .transactions-table .status.pending { background: linear-gradient(135deg, #fff7ed, #fde68a); color: #b45309; border: 1px solid #f59e0b; }
        .transactions-table .status.processing { background: linear-gradient(135deg, #eff6ff, #bfdbfe); color: #1d4ed8; border: 1px solid #3b82f6; }
        .transactions-table .status.failed { background: linear-gradient(135deg, #fef2f2, #fee2e2); color: #b91c1c; border: 1px solid #ef4444; }
        .transactions-table .status.rejected { background: linear-gradient(135deg, #ffe4e6, #fecdd3); color: #9f1239; border: 1px solid #f43f5e; }
        .transactions-table .status.flagged { background: linear-gradient(135deg, #fdf2f8, #fbcfe8); color: #9d174d; border: 1px solid #db2777; }
        .transactions-table .status.cancelled { background: linear-gradient(135deg, #f3f4f6, #e5e7eb); color: #374151; border: 1px solid #9ca3af; }
        .transactions-table .status.refunded { background: linear-gradient(135deg, #ecfeff, #d1fae5); color: #0e7490; border: 1px solid #14b8a6; }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border-left: 4px solid var(--success);
            color: var(--success);
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--danger);
            color: var(--danger);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <style>
        .loader-overlay{position:fixed;inset:0;z-index:2000;background:linear-gradient(120deg,rgba(255,255,255,.8),rgba(255,255,255,.6));backdrop-filter:saturate(180%) blur(8px);display:flex;align-items:center;justify-content:center;transition:opacity .4s ease}
        .loader-overlay.loader-hidden{opacity:0;pointer-events:none}
        .spinner{position:relative;width:10em;height:10em}
        .spinner:before{transform:rotateX(60deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateBefore infinite linear reverse}
        .spinner:after{transform:rotateX(240deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateAfter infinite linear}
        .spinner:before,.spinner:after{top:50%;left:50%;}
        .spinner:before,.spinner:after{box-sizing:border-box;content:'';display:block;position:absolute;margin-top:-5em;margin-left:-5em;width:10em;height:10em;transform-style:preserve-3d;transform-origin:50%;perspective-origin:50% 50%;perspective:340px;background-size:10em 10em;background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjI2NnB4IiBoZWlnaHQ9IjI5N3B4IiB2aWV3Qm94PSIwIDAgMjY2IDI5NyIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWxuczpza2V0Y2g9Imh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaC9ucyI+CiAgICA8dGl0bGU+c3Bpbm5lcjwvdGl0bGU+CiAgICA8ZGVzY3JpcHRpb24+Q3JlYXRlZCB3aXRoIFNrZXRjaCAoaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoKTwvZGVzY3JpcHRpb24+CiAgICA8ZGVmcz48L2RlZnM+CiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBza2V0Y2g6dHlwZT0iTVNQYWdlIj4KICAgICAgICA8cGF0aCBkPSJNMTcxLjUwNzgxMywzLjI1MDAwMDM4IEMyMjYuMjA4MTgzLDEyLjg1NzcxMTEgMjk3LjExMjcyMiw3MS40OTEyODIzIDI1MC44OTU1OTksMTA4LjQxMDE1NSBDMjE2LjU4MjAyNCwxMzUuODIwMzEgMTg2LjUyODQwNSw5Ny4wNjI0OTY0IDE1Ni44MDA3NzQsODUuNzczNDM0NiBDMTI3LjA3MzE0Myw3NC40ODQzNzIxIDc2Ljg4ODQ2MzIsODQuMjE2MTQ2MiA2MC4xMjg5MDY1LDEwOC40MTAxNTMgQy0xNS45ODA0Njg1LDIxOC4yODEyNDcgMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IDE0NS4yNzczNDQsMjk2LjY2Nzk2OCBDMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IC0yNS40NDkyMTg3LDI1Ny4yNDIxOTggMy4zOTg0Mzc1LDEwOC40MTAxNTUgQzE2LjMwNzA2NjEsNDEuODExNDE3NCA4NC43Mjc1ODI5LC0xMS45OTIyOTg1IDE3MS41MDc4MTMsMy4yNTAwMDAzOCBaIiBpZD0iUGF0aC0xIiBmaWxsPSIjMDAwMDAwIiBza2V0Y2g6dHlwZT0iTVNTaGFwZUdyb3VwIj48L3BhdGg+CiAgICA8L2c+Cjwvc3ZnPg==)}
        @keyframes rotateBefore{from{transform:rotateX(60deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(60deg) rotateY(45deg) rotateZ(-360deg)}}
        @keyframes rotateAfter{from{transform:rotateX(240deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(240deg) rotateY(45deg) rotateZ(360deg)}}
    </style>
    <script>window.addEventListener('load',()=>{const l=document.getElementById('loader');if(l){setTimeout(()=>{l.classList.add('loader-hidden');setTimeout(()=>{try{l.remove()}catch(e){}},600);},1500)}});</script>
</head>
<body>
    <div id='loader' class='loader-overlay'><div class='spinner'></div></div>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include_once __DIR__ . '/partials/sidebar.php'; ?>

        <div class="main-content">
            <div class="mb-6" style="display:flex;justify-content:space-between;align-items:center;position:relative;">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Finance Manager Dashboard</h1>
                    <p class="text-gray-600">Welcome back, <?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? 'User') ?>!</p>
                </div>
                <div style="position:relative;">
                    <button id="fmNotifBtn" style="position:relative;display:inline-flex;align-items:center;gap:.5rem;background:#fff;border:1px solid #e5e7eb;border-radius:9999px;padding:8px 12px;box-shadow:0 4px 10px rgba(0,0,0,.06);">
                        <i class="fas fa-bell" style="color:#1f2937"></i>
                        <span style="font-weight:600;color:#1f2937">Notifications</span>
                    </button>
                    <div id="fmNotifPanel" style="position:absolute;right:0;top:48px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 10px 20px rgba(0,0,0,.08);width:320px;z-index:1100;display:none;">
                        <div style="padding:12px 14px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-weight:600;color:#111827;">Notifications</span>
                            <span id="fmNotifHeaderCount" style="background:#2563eb;color:#fff;border-radius:9999px;padding:2px 8px;font-size:.8rem;">0</span>
                        </div>
                        <div id="fmNotifPanelContent"><div style="padding:12px 14px;" class="text-gray-500">No notifications</div></div>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (!empty($data['error'])): ?>
                <div class="alert alert-error">
                    <p><?= htmlspecialchars($data['error']) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($data['success'])): ?>
                <div class="alert alert-success">
                    <p><?= htmlspecialchars($data['success']) ?></p>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <!-- Total Payments -->
                <div class="stat-card">
                    <div class="icon bg-blue-100 text-blue-600">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="title">Total Payments</div>
                    <div class="value">KSH <?= number_format($data['totalPayments'], 2) ?></div>
                    <div class="change positive">
                        <i class="fas fa-arrow-up mr-1"></i> Financial Summary
                    </div>
                </div>

                <!-- Pending Bills -->
                <div class="stat-card">
                    <div class="icon bg-yellow-100 text-yellow-600">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="title">Pending Bills</div>
                    <div class="value"><?= $data['pendingBillsCount'] ?></div>
                    <div class="change">
                        <a href="index.php?page=finance_manager_transactions" class="text-blue-600 hover:underline">View Details</a>
                    </div>
                </div>

                <!-- Overdue Bills -->
                <div class="stat-card">
                    <div class="icon bg-red-100 text-red-600">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="title">Overdue Bills</div>
                    <div class="value"><?= $data['overdueBillsCount'] ?></div>
                    <div class="change negative">
                        <i class="fas fa-arrow-up mr-1"></i> Requires Attention
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="transactions-card">
                <h2>Recent Transactions</h2>
                <div class="overflow-x-auto">
                    <table class="transactions-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['recentTransactions'])): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-gray-500">No recent transactions found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data['recentTransactions'] as $transaction): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($transaction['payment_id'] ?? 'N/A') ?></td>
                                        <td>
                                            <div class="font-medium text-gray-900"><?= htmlspecialchars($transaction['client_name'] ?? 'N/A') ?></div>
                                        </td>
                                        <td class="font-medium">KSH <?= number_format($transaction['amount'] ?? 0, 2) ?></td>
                                        <td><?= isset($transaction['payment_date']) ? htmlspecialchars(date('M d, Y', strtotime($transaction['payment_date']))) : 'N/A' ?></td>
                                        <td>
                                            <?php 
                                            $statusRaw = strtolower(trim($transaction['status'] ?? 'pending'));
                                            $map = [
                                                'completed' => 'completed',
                                                'paid' => 'paid',
                                                'confirmed_and_verified' => 'confirmed',
                                                'verified' => 'confirmed',
                                                'processing' => 'processing',
                                                'pending' => 'pending',
                                                'pending_payment' => 'pending',
                                                'failed' => 'failed',
                                                'rejected' => 'rejected',
                                                'flagged' => 'flagged',
                                                'cancelled' => 'cancelled',
                                                'canceled' => 'cancelled',
                                                'refunded' => 'refunded'
                                            ];
                                            $statusClass = $map[$statusRaw] ?? 'pending';
                                            $label = ucwords(str_replace('_', ' ', $statusRaw));
                                            ?>
                                            <span class="status <?= $statusClass ?>"><?= htmlspecialchars($label) ?></span>
                                        </td>
                                        <td>
                                            <a href="index.php?page=finance_manager_transaction_details&id=<?= $transaction['payment_id'] ?? '' ?>" class="text-blue-600 hover:underline">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-right">
                    <a href="index.php?page=finance_manager_transactions" class="text-blue-600 hover:underline">View All Transactions</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        (function(){
            var btn=document.getElementById('fmNotifBtn');
            var panel=document.getElementById('fmNotifPanel');
            if(btn){
                btn.addEventListener('click',function(e){ e.stopPropagation(); if(panel){ panel.style.display = (panel.style.display==='none'||panel.style.display==='')?'block':'none'; }});
                document.addEventListener('click',function(){ if(panel){ panel.style.display='none'; }});
            }
            function pollFM(){
                fetch('index.php?page=api_finance_manager_notifications').then(function(r){return r.json()}).then(function(d){
                    if(d && !d.error){
                        var b=btn?btn.querySelector('.badge'):null;
                        if(d.notificationsCount>0){
                            if(!b){ b=document.createElement('span'); b.className='badge'; b.style.cssText='position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;border-radius:9999px;font-size:0.75rem;line-height:1;padding:4px 6px;min-width:20px;text-align:center;'; btn.appendChild(b);} 
                            b.textContent=d.notificationsCount;
                        } else if(b){ b.remove(); }
                        var hc=document.getElementById('fmNotifHeaderCount'); if(hc){ hc.textContent=d.notificationsCount||0; }
                        var pc=document.getElementById('fmNotifPanelContent'); if(pc){ pc.innerHTML=''; if(d.notifications && d.notifications.length){ d.notifications.forEach(function(n){ var item=document.createElement('div'); item.style.cssText='padding:12px 14px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;'; var left=document.createElement('div'); var title=document.createElement('div'); title.style.cssText='font-weight:600;color:#111827;'; title.textContent=n.title; var link=document.createElement('a'); link.style.cssText='color:#2563eb;font-size:.85rem;text-decoration:none;'; link.href=n.url; link.textContent='Open'; left.appendChild(title); left.appendChild(link); var count=document.createElement('span'); count.style.cssText='background:#2563eb;color:#fff;border-radius:9999px;padding:2px 8px;font-size:.8rem;'; count.textContent=n.count; item.appendChild(left); item.appendChild(count); pc.appendChild(item); }); } else { var empty=document.createElement('div'); empty.className='text-gray-500'; empty.style.cssText='padding:12px 14px;'; empty.textContent='No notifications'; pc.appendChild(empty);} }
                    }
                }).catch(function(){});
            }
            setInterval(pollFM,30000); pollFM();
        })();
    </script>
</body>
</html>
