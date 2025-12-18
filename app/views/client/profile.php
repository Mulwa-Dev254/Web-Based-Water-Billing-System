<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Client Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* General Styling - Matched with apply_service.php */
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

        /* Sidebar Styling - Exactly matched with apply_service.php */
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

        /* Main Content Styling - Matched with apply_service.php */
        .client-main-content {
            flex: 1;
            transition: all 0.3s ease;
            padding: 15px;
        }

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

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
        }

        .user-info a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 500;
        }

        .user-info a:hover {
            text-decoration: underline;
        }

        .client-sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.1rem;
            cursor: pointer;
            color: #2c3e50;
            display: none;
        }

        /* Content Sections - Matched with apply_service.php */
        .client-content-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .client-content-section {
            background: white;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .client-content-section h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #2c3e50;
            font-size: 1.1rem;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Grid Layout - Matched with apply_service.php */
        .client-grid {
            display: grid;
            gap: 15px;
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .grid-cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        /* Profile Info Styling */
        .profile-info {
            display: grid;
            gap: 15px;
        }

        .info-group {
            display: flex;
            flex-direction: column;
        }

        .info-group label {
            font-size: 0.8rem;
            color: #7f8c8d;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .info-group p {
            margin: 0;
            font-size: 0.9rem;
            color: #2c3e50;
            font-weight: 500;
            padding: 8px 12px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        /* Security Cards */
        .security-card {
            background: white;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .security-card:hover {
            transform: translateY(-3px);
        }

        .security-card i {
            font-size: 1.5rem;
            color: #3498db;
            margin-bottom: 10px;
        }

        .security-card h4 {
            margin: 0 0 8px 0;
            font-size: 0.95rem;
            color: #2c3e50;
        }

        .security-card p {
            margin: 0;
            font-size: 0.8rem;
            color: #7f8c8d;
        }

        /* Button Styling - Matched with apply_service.php */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 0.85rem;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }

        /* Alert Styling - Matched with apply_service.php */
        .alert {
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-info {
            background-color: #e3f2fd;
            color: #1976d2;
            border-left: 4px solid #1976d2;
        }

        /* Responsive Design - Matched with apply_service.php */
        @media (max-width: 992px) {
            .grid-cols-2, .grid-cols-3 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .client-sidebar {
                position: fixed;
                left: -220px;
                top: 0;
                bottom: 0;
                z-index: 1000;
            }
            
            .client-sidebar.visible {
                left: 0;
            }
            
            .client-main-content {
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .client-main-content.full-width {
                margin-left: 0;
            }
            
            .client-sidebar-toggle {
                display: block;
            }
            
            .client-header-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .user-info {
                width: 100%;
                justify-content: space-between;
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
<body class="client-theme">
    <div id='loader' class='loader-overlay'><div class='spinner'></div></div>
    <div class="client-dashboard-layout">
        <!-- Sidebar Navigation -->
        <?php include_once dirname(__DIR__) . '/includes/client_sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="client-main-content" id="clientMainContent">
            <!-- Header Bar -->
            <div class="client-header-bar">
                <button class="client-sidebar-toggle" id="clientSidebarToggle"><i class="fas fa-bars"></i></button>
                <h1><i class="fas fa-user"></i> My Profile</h1>
                <div class="user-info">
                    <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <!-- Page Content -->
            <div class="client-content-container">
                <?php if (!empty($data['error'])): ?>
                    <div class="alert alert-info"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($data['error']); ?></div>
                <?php endif; ?>
                <?php if (!empty($data['success'])): ?>
                    <div class="alert alert-info" style="background:#eafaf1;color:#2e7d32;border-left-color:#2e7d32"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($data['success']); ?></div>
                <?php endif; ?>
                <div class="client-grid grid-cols-2">
                    <!-- Account Information -->
                    <div class="client-content-section">
                        <h3><i class="fas fa-user-circle"></i> Account Information</h3>
                        <form id="accountForm" action="index.php?page=client_profile" method="POST" style="margin-top:.5rem">
                            <input type="hidden" name="action" value="update_profile">
                            <input type="hidden" name="form_section" value="account">
                            <div class="profile-info">
                                <div class="info-group">
                                    <label>Username</label>
                                    <input type="text" name="username" value="<?php echo htmlspecialchars($data['userInfo']['username'] ?? ''); ?>" style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;" disabled />
                                </div>
                                <div class="info-group">
                                    <label>Email</label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($data['userInfo']['email'] ?? ''); ?>" style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;" disabled />
                                </div>
                                <div class="info-group">
                                    <label>Account Created</label>
                                    <p><?php echo htmlspecialchars(isset($data['userInfo']['created_at']) ? date('M d, Y', strtotime($data['userInfo']['created_at'])) : 'N/A'); ?></p>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary mt-3" id="accountEditSaveBtn">Edit</button>
                        </form>
                    </div>

                    <!-- Client Details -->
                    <div class="client-content-section">
                        <h3><i class="fas fa-id-card"></i> Client Details</h3>
                        <form id="clientForm" action="index.php?page=client_profile" method="POST" style="margin-top:.5rem">
                            <input type="hidden" name="action" value="update_profile">
                            <input type="hidden" name="form_section" value="client">
                            <div class="profile-info">
                                <div class="info-group">
                                    <label>Full Name</label>
                                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($data['clientInfo']['full_name'] ?? ''); ?>" style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;" disabled />
                                </div>
                                <div class="info-group">
                                    <label>Address</label>
                                    <input type="text" name="address" value="<?php echo htmlspecialchars($data['clientInfo']['address'] ?? ''); ?>" style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;" disabled />
                                </div>
                                <div class="info-group">
                                    <label>Phone</label>
                                    <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($data['clientInfo']['contact_phone'] ?? ''); ?>" style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;" disabled />
                                </div>
                                <div class="info-group">
                                    <label>Client ID</label>
                                    <p><?php echo htmlspecialchars($data['clientInfo']['id'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary mt-3" id="clientEditSaveBtn">Edit</button>
                        </form>
                    </div>
                </div>

                <!-- Account Security -->
                <div class="client-content-section mt-4">
                    <h3><i class="fas fa-shield-alt"></i> Account Security</h3>
                    <div class="alert alert-info">
                        <i class="fas fa-shield-alt"></i> For your security, please keep your login credentials confidential.
                    </div>
                    <div class="client-grid grid-cols-3">
                        <div class="security-card">
                            <i class="fas fa-lock"></i>
                            <h4>Strong Password</h4>
                            <p>Use a combination of letters, numbers, and symbols</p>
                        </div>
                        <div class="security-card">
                            <i class="fas fa-sign-out-alt"></i>
                            <h4>Logout Reminder</h4>
                            <p>Always log out after your session</p>
                        </div>
                        <div class="security-card">
                            <i class="fas fa-envelope"></i>
                            <h4>Secure Email</h4>
                            <p>Use a secure and private email address</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const clientSidebarToggle = document.getElementById('clientSidebarToggle');
            const clientSidebar = document.getElementById('clientSidebar');
            const clientMainContent = document.getElementById('clientMainContent');

            // Toggle sidebar visibility
            clientSidebarToggle.addEventListener('click', function() {
                clientSidebar.classList.toggle('visible');
                clientMainContent.classList.toggle('full-width');
            });

            // Hide sidebar on larger screens if it was toggled on mobile and then resized
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    clientSidebar.classList.remove('visible');
                    clientMainContent.classList.remove('full-width');
                }
            });

            // Highlight active navigation link
            const currentPath = window.location.search;
            const navLinks = clientSidebar.querySelectorAll('a');
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.href.includes(currentPath) && currentPath !== '') {
                    link.classList.add('active');
                } else if (currentPath === '' && link.href.includes('client_dashboard')) {
                    link.classList.add('active');
                }
            });

            const updateSuccess = <?php echo !empty($data['success']) ? 'true' : 'false'; ?>;
            const updateError = <?php echo !empty($data['error']) ? 'true' : 'false'; ?>;
            const submittedSection = <?php echo json_encode($data['submittedSection'] ?? null); ?>;
            const accountForm = document.getElementById('accountForm');
            const clientForm = document.getElementById('clientForm');
            const accountBtn = document.getElementById('accountEditSaveBtn');
            const clientBtn = document.getElementById('clientEditSaveBtn');
            const accountInputs = accountForm.querySelectorAll('input[name="username"], input[name="email"]');
            const clientInputs = clientForm.querySelectorAll('input[name="full_name"], input[name="address"], input[name="contact_phone"]');

            function setEditing(form, inputs, btn, editing, saveText) {
                inputs.forEach(inp => { inp.disabled = !editing; });
                btn.textContent = editing ? saveText : 'Edit';
                btn.dataset.mode = editing ? 'save' : 'edit';
            }

            setEditing(accountForm, accountInputs, accountBtn, false, 'Save Account Changes');
            setEditing(clientForm, clientInputs, clientBtn, false, 'Save Client Changes');

            if (updateSuccess) {
                setEditing(accountForm, accountInputs, accountBtn, false, 'Save Account Changes');
                setEditing(clientForm, clientInputs, clientBtn, false, 'Save Client Changes');
            } else if (updateError && submittedSection === 'account') {
                setEditing(accountForm, accountInputs, accountBtn, true, 'Save Account Changes');
                setEditing(clientForm, clientInputs, clientBtn, false, 'Save Client Changes');
            } else if (updateError && submittedSection === 'client') {
                setEditing(accountForm, accountInputs, accountBtn, false, 'Save Account Changes');
                setEditing(clientForm, clientInputs, clientBtn, true, 'Save Client Changes');
            }

            accountBtn.addEventListener('click', function() {
                if (this.dataset.mode === 'edit') {
                    setEditing(accountForm, accountInputs, accountBtn, true, 'Save Account Changes');
                } else {
                    accountForm.submit();
                }
            });

            clientBtn.addEventListener('click', function() {
                if (this.dataset.mode === 'edit') {
                    setEditing(clientForm, clientInputs, clientBtn, true, 'Save Client Changes');
                } else {
                    clientForm.submit();
                }
            });
        });
    </script>
</body>
</html>
