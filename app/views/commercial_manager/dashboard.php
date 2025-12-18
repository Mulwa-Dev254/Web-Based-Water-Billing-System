<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commercial Manager Dashboard - AquaBill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            color: #333;
        }
        .sidebar {
            width: 250px;
            background: linear-gradient(to bottom, #4a5568, #2d3748);
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            transition: all 0.3s ease;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .sidebar.hidden {
            left: -250px;
        }
        .sidebar h3 {
            color: #3498db;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.4rem;
            font-weight: 700;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar ul li {
            margin-bottom: 10px;
        }
        .sidebar ul li a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        .sidebar ul li a i {
            margin-right: 10px;
            font-size: 1.1rem;
            color: #a1a5b7;
            transition: color 0.3s ease;
        }
        .sidebar ul li a:hover {
            background-color: rgba(255, 255, 255, 0.08);
            color: #3498db;
        }
        .sidebar ul li a.active {
            background-color: #3498db;
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
        }
        .sidebar ul li a.active i {
            color: white;
        }
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .main-content.full-width {
            margin-left: 0;
        }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #ffffff;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .header-bar h1 {
            color: #333;
            font-size: 1.8rem;
            margin: 0;
            font-weight: 600;
        }
        .notif {
            position: relative;
            display: inline-block;
        }
        .notif .badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #e74c3c;
            color: #fff;
            border-radius: 9999px;
            font-size: 0.75rem;
            line-height: 1;
            padding: 4px 6px;
            min-width: 20px;
            text-align: center;
        }
        .notif-panel {
            position: absolute;
            right: 20px;
            top: 60px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 10px 20px rgba(0,0,0,.08);
            width: 320px;
            z-index: 1100;
            display: none;
        }
        .notif-panel .item { padding: 12px 14px; border-bottom: 1px solid #f3f4f6; display:flex; justify-content: space-between; align-items: center; }
        .notif-panel .item:last-child { border-bottom: none; }
        .notif-panel .title { font-weight: 600; color:#111827; }
        .notif-panel .count { background:#3498db; color:#fff; border-radius:9999px; padding:2px 8px; font-size:.8rem; }
        .notif-panel .link { color:#3498db; font-size:.85rem; text-decoration:none; }
        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-profile span {
            color: #555;
            font-size: 0.95rem;
        }
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .dashboard-card {
            background-color: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.2s ease-in-out;
            cursor: pointer;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .dashboard-card .icon {
            font-size: 3rem;
            color: #3498db;
            margin-bottom: 1rem;
        }
        .dashboard-card h3 {
            font-size: 1.25rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .dashboard-card p {
            font-size: 1.5rem;
            font-weight: 600;
            color: #555;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        .alert-error {
            background-color: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }
        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid #2ecc71;
        }
        .sidebar-toggle {
            display: none; /* Hidden on desktop */
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            .sidebar.visible {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .main-content.full-width {
                margin-left: 0;
            }
            .sidebar-toggle {
                display: block; /* Show on mobile */
            }
            .header-bar {
                flex-direction: column;
                align-items: flex-start;
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
    <div class="sidebar" id="sidebar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <h3><i class="fas fa-chart-line"></i> Commercial Manager</h3>
        <ul>
            <li><a href="index.php?page=commercial_manager_dashboard" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="index.php?page=commercial_manager_manage_meters"><i class="fas fa-tachometer-alt"></i> Manage Meters</a></li>
            <li><a href="index.php?page=commercial_manager_review_applications"><i class="fas fa-clipboard-check"></i> Review Applications</a></li>
            <li><a href="index.php?page=commercial_manager_service_requests"><i class="fas fa-tools"></i> Service Requests</a></li>
            <li><a href="index.php?page=commercial_manager_reports"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content" id="mainContent">
        <div class="header-bar">
            <h1>Commercial Manager Dashboard</h1>
            <div class="user-profile" style="position:relative;">
                <div class="notif">
                    <button id="notifBtn" class="btn btn-gray" title="Notifications" style="background:#f3f4f6;border:1px solid #e5e7eb;padding:8px 10px;border-radius:8px;">
                        <i class="fas fa-bell"></i>
                        <?php $nc = (int)($data['notificationsCount'] ?? 0); if ($nc > 0): ?>
                            <span class="badge"><?php echo $nc; ?></span>
                        <?php endif; ?>
                    </button>
                </div>
                <div id="notifPanel" class="notif-panel">
                    <div class="item">
                        <span class="title">Notifications</span>
                        <span id="cmNotifHeaderCount" class="count"><?php echo (int)($data['notificationsCount'] ?? 0); ?></span>
                    </div>
                    <div id="cmNotifPanelContent">
                        <?php if (!empty($data['notifications'])): foreach ($data['notifications'] as $n): ?>
                            <div class="item">
                                <div>
                                    <div class="title"><?php echo htmlspecialchars($n['title']); ?></div>
                                    <a class="link" href="<?php echo htmlspecialchars($n['url']); ?>">Open</a>
                                </div>
                                <span class="count"><?php echo (int)$n['count']; ?></span>
                            </div>
                        <?php endforeach; else: ?>
                            <div class="item"><span class="text-gray-500">No notifications</span></div>
                        <?php endif; ?>
                    </div>
                </div>
                <span>Hello, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Commercial Manager'); ?></span>
                <a href="index.php?page=logout" class="logout-btn">Logout</a>
            </div>
        </div>

        <?php if (!empty($data['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($data['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($data['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($data['success']); ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <div class="dashboard-card" onclick="location.href='index.php?page=commercial_manager_manage_meters'">
                <div class="icon"><i class="fas fa-water"></i></div>
                <h3>Total Meters</h3>
                <p><?php echo htmlspecialchars($data['totalMeters'] ?? 0); ?></p>
            </div>
            <div class="dashboard-card" onclick="location.href='index.php?page=commercial_manager_review_applications'">
                <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                <h3>Pending Applications</h3>
                <p><?php echo htmlspecialchars($data['pendingApplications'] ?? 0); ?></p>
            </div>
            <div class="dashboard-card" onclick="location.href='index.php?page=commercial_manager_service_requests'">
                <div class="icon"><i class="fas fa-tools"></i></div>
                <h3>Service Requests</h3>
                <p><?php echo htmlspecialchars($data['serviceRequests'] ?? 0); ?></p>
            </div>
            <div class="dashboard-card" onclick="location.href='index.php?page=commercial_manager_reports'">
                <div class="icon"><i class="fas fa-chart-bar"></i></div>
                <h3>View Reports</h3>
                <p>Access financial and service reports</p>
            </div>
            <div class="dashboard-card" onclick="location.href='index.php?page=commercial_manager_profile'">
                <div class="icon"><i class="fas fa-user-circle"></i></div>
                <h3>My Profile</h3>
                <p>Manage your account details</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('hidden');
                mainContent.classList.toggle('full-width');
            });

            // Highlight active navigation link
            const currentPath = window.location.search;
            const navLinks = sidebar.querySelectorAll('a');
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.href.includes(currentPath) && currentPath !== '') {
                    link.classList.add('active');
                } else if (currentPath === '' && link.href.includes('commercial_manager_dashboard')) {
                    link.classList.add('active');
                }
            });

            // Adjust sidebar visibility on resize for desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('hidden');
                    mainContent.classList.remove('full-width');
                } else {
                    sidebar.classList.add('hidden');
                    mainContent.classList.add('full-width');
                }
            });

            // Notification toggle
            const nb = document.getElementById('notifBtn');
            const np = document.getElementById('notifPanel');
            if (nb && np) {
                nb.addEventListener('click', function(e) {
                    e.stopPropagation();
                    np.style.display = (np.style.display === 'block') ? 'none' : 'block';
                });
                document.addEventListener('click', function(e) {
                    if (!np.contains(e.target) && !nb.contains(e.target)) {
                        np.style.display = 'none';
                    }
                });
            }
        });
    </script>
    <script>
        function pollCMNotifications(){
            fetch('index.php?page=api_commercial_manager_notifications').then(r=>r.json()).then(d=>{
                if(d && !d.error){
                    var btn=document.getElementById('notifBtn');
                    if(btn){
                        var b=btn.querySelector('.badge');
                        if(d.notificationsCount>0){
                            if(!b){
                                b=document.createElement('span');
                                b.className='badge';
                                b.textContent=d.notificationsCount;
                                btn.appendChild(b);
                            } else { b.textContent=d.notificationsCount; }
                        } else { if(b){ b.remove(); } }
                    }
                    var hc=document.getElementById('cmNotifHeaderCount');
                    if(hc){ hc.textContent=d.notificationsCount||0; }
                    var pc=document.getElementById('cmNotifPanelContent');
                    if(pc){
                        pc.innerHTML='';
                        if(d.notifications && d.notifications.length){
                            d.notifications.forEach(function(n){
                                var item=document.createElement('div'); item.className='item';
                                var left=document.createElement('div');
                                var title=document.createElement('div'); title.className='title'; title.textContent=n.title;
                                var link=document.createElement('a'); link.className='link'; link.href=n.url; link.textContent='Open';
                                left.appendChild(title); left.appendChild(link);
                                var count=document.createElement('span'); count.className='count'; count.textContent=n.count;
                                item.appendChild(left); item.appendChild(count);
                                pc.appendChild(item);
                            });
                        } else {
                            var empty=document.createElement('div'); empty.className='item';
                            var t=document.createElement('span'); t.className='text-gray-500'; t.textContent='No notifications';
                            empty.appendChild(t); pc.appendChild(empty);
                        }
                    }
                }
            }).catch(function(){});
        }
        setInterval(pollCMNotifications,30000);
        pollCMNotifications();
    </script>
</body>
</html>
