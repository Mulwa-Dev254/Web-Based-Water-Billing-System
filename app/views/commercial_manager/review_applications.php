<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commercial Manager - Review Applications</title>
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
        .sidebar-header {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin-bottom: 10px;
        }
        .sidebar ul li a {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #e2e8f0;
            /* Added responsive hover effect for better user experience */
            transition: background-color 0.2s ease-in-out, transform 0.1s ease;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .sidebar ul li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        .sidebar ul li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .content.expanded {
            margin-left: 0;
        }
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2563eb;
        }
        .btn-success {
            background-color: #10b981;
            color: white;
        }
        .btn-success:hover {
            background-color: #059669;
        }
        .btn-danger {
            background-color: #ef4444;
            color: white;
        }
        .btn-danger:hover {
            background-color: #dc2626;
        }
        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-pending {
            background-color: #fbbf24;
            color: #92400e;
        }
        .badge-approved {
            background-color: #34d399;
            color: #065f46;
        }
        .badge-rejected {
            background-color: #f87171;
            color: #991b1b;
        }
        .badge-submitted {
            background-color: #60a5fa;
            color: #1e40af;
        }
        .badge-admin-verified {
            background-color: #34d399;
            color: #065f46;
        }
        .badge-confirmed {
            background-color: #22c55e;
            color: #064e3b;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 80%;
            max-width: 600px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .tab-btn {
            padding: 10px 20px;
            background-color: #f3f4f6;
            border: none;
            border-radius: 5px 5px 0 0;
            cursor: pointer;
        }
        .tab-btn.active {
            background-color: white;
            border-bottom: 3px solid #3b82f6;
            font-weight: 600;
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
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-tint mr-2"></i> Water Billing
        </div>
        <ul>
            <li><a href="index.php?page=commercial_manager_dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="index.php?page=commercial_manager_manage_meters"><i class="fas fa-tachometer-alt"></i> Manage Meters</a></li>
            <li><a href="index.php?page=commercial_manager_review_applications" class="bg-blue-700 text-white"><i class="fas fa-clipboard-check"></i> Review Applications</a></li>
            <li><a href="index.php?page=commercial_manager_service_requests"><i class="fas fa-tools"></i> Service Requests</a></li>
            <li><a href="index.php?page=commercial_manager_reports"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="content" id="content">
        <div class="flex justify-between items-center mb-6">
            <button id="sidebarToggle" class="btn btn-secondary">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="text-2xl font-bold">Meter Applications Review</h1>
            <div class="text-right">
                <span class="font-semibold"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Commercial Manager'); ?></span>
                <p class="text-sm text-gray-600">Commercial Manager</p>
            </div>
        </div>

        <?php if (!empty($data['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p><?php echo htmlspecialchars($data['error']); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($data['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p><?php echo htmlspecialchars($data['success']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Tab Navigation -->
        <div class="mb-4">
            <button class="tab-btn active" data-tab="pending">Pending Applications</button>
            <button class="tab-btn" data-tab="processed">Processed Applications</button>
        </div>

        <!-- Pending Applications Tab -->
        <div id="pending-tab" class="tab-content active">
            <div class="card">
                <h2 class="text-xl font-semibold mb-4">Pending Applications</h2>
                
                <?php if (empty($data['pendingApplications'])): ?>
                    <p class="text-gray-500">No pending applications found.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 border-b text-left">Meter Image</th>
                                    <th class="py-2 px-4 border-b text-left">Client Name</th>
                                    <th class="py-2 px-4 border-b text-left">Meter Serial</th>
                                    <th class="py-2 px-4 border-b text-left">Status</th>
                                    <th class="py-2 px-4 border-b text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['pendingApplications'] as $application): ?>
                                    <tr>
                                        <td class="py-2 px-4 border-b">
                                            <?php if (!empty($application['meter_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($application['meter_image']); ?>" alt="Meter Image" class="w-16 h-16 object-cover rounded">
                                            <?php else: ?>
                                                <div class="w-16 h-16 bg-gray-200 flex items-center justify-center rounded">
                                                    <i class="fas fa-tachometer-alt text-gray-400 text-xl"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($application['client_name'] ?? 'Unknown'); ?></td>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($application['meter_serial'] ?? 'Unknown'); ?></td>
                                        <td class="py-2 px-4 border-b">
                                            <span class="badge badge-pending">Pending</span>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <button class="btn btn-primary btn-sm view-application" data-id="<?php echo $application['id']; ?>" 
                                                    data-client="<?php echo htmlspecialchars($application['client_name'] ?? 'Unknown'); ?>"
                                                    data-meter="<?php echo htmlspecialchars($application['meter_serial'] ?? 'Unknown'); ?>"
                                                    data-date="<?php echo htmlspecialchars(date('Y-m-d', strtotime($application['application_date']))); ?>"
                                                    data-notes="<?php echo htmlspecialchars($application['notes'] ?? 'No additional notes'); ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="btn btn-success btn-sm review-application" data-id="<?php echo $application['id']; ?>">
                                                <i class="fas fa-check-circle"></i> Review
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Processed Applications Tab -->
        <div id="processed-tab" class="tab-content">
            <div class="card">
                <h2 class="text-xl font-semibold mb-4">Processed Applications</h2>
                
                <?php if (empty($data['processedApplications'])): ?>
                    <p class="text-gray-500">No processed applications found.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 border-b text-left">Meter Image</th>
                                    <th class="py-2 px-4 border-b text-left">Client Name</th>
                                    <th class="py-2 px-4 border-b text-left">Meter Serial</th>
                                    <th class="py-2 px-4 border-b text-left">Status</th>
                                    <th class="py-2 px-4 border-b text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['processedApplications'] as $application): ?>
                                    <tr>
                                        <td class="py-2 px-4 border-b">
                                            <?php if (!empty($application['meter_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($application['meter_image']); ?>" alt="Meter Image" class="w-16 h-16 object-cover rounded">
                                            <?php else: ?>
                                                <div class="w-16 h-16 bg-gray-200 flex items-center justify-center rounded">
                                                    <i class="fas fa-tachometer-alt text-gray-400 text-xl"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($application['client_name'] ?? 'Unknown'); ?></td>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($application['meter_serial'] ?? 'Unknown'); ?></td>
                                        <td class="py-2 px-4 border-b">
                                            <?php if ($application['status'] === 'rejected'): ?>
                                                <span class="badge badge-rejected">Rejected</span>
                                            <?php elseif ($application['status'] === 'approved' && (int)($application['admin_approval'] ?? 0) === 0): ?>
                                                <span class="badge badge-approved">Approved</span>
                                            <?php elseif ($application['status'] === 'approved' && (int)($application['admin_approval'] ?? 0) === 1): ?>
                                                <span class="badge badge-submitted">Submitted to Admin</span>
                                            <?php elseif ($application['status'] === 'approved' && (int)($application['admin_approval'] ?? 0) === 2): ?>
                                                <span class="badge badge-admin-verified">Admin Verified</span>
                                            <?php elseif ($application['status'] === 'approved' && (int)($application['admin_approval'] ?? 0) === 3): ?>
                                                <span class="badge badge-confirmed">Confirmed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <button class="btn btn-primary btn-sm view-application" data-id="<?php echo $application['id']; ?>"
                                                    data-client="<?php echo htmlspecialchars($application['client_name'] ?? 'Unknown'); ?>"
                                                    data-meter="<?php echo htmlspecialchars($application['meter_serial'] ?? 'Unknown'); ?>"
                                                    data-date="<?php echo htmlspecialchars(date('Y-m-d', strtotime($application['application_date']))); ?>"
                                                    data-notes="<?php echo htmlspecialchars($application['notes'] ?? 'No additional notes'); ?>"
                                                    data-status="<?php echo htmlspecialchars($application['status']); ?>"
                                                    data-reason="<?php echo htmlspecialchars($application['rejection_reason'] ?? ''); ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            
                                            <?php if ($application['status'] === 'approved' && (int)($application['admin_approval'] ?? 0) === 0): ?>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="action" value="submit_to_admin">
                                                    <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                                    <button type="submit" class="btn btn-secondary btn-sm">
                                                        <i class="fas fa-paper-plane"></i> Submit to Admin
                                                    </button>
                                                </form>
                                            <?php elseif ($application['status'] === 'approved' && (int)($application['admin_approval'] ?? 0) === 1): ?>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="action" value="cancel_admin_submission">
                                                    <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-times"></i> Cancel Submission
                                                    </button>
                                                </form>
                                                <span class="text-xs text-gray-500 block mt-1">Submitted: <?php echo date('Y-m-d', strtotime($application['admin_approval_date'])); ?></span>
                                            <?php elseif ($application['status'] === 'approved' && (int)($application['admin_approval'] ?? 0) === 2): ?>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="action" value="confirm_to_client">
                                                    <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check"></i> Confirm to Client
                                                    </button>
                                                </form>
                                            <?php elseif ($application['status'] === 'approved' && (int)($application['admin_approval'] ?? 0) === 3): ?>
                                                <span class="text-sm text-gray-600">Confirmation sent</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- View Application Modal -->
        <div id="viewApplicationModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 class="text-xl font-semibold mb-4">Application Details</h2>
                <div class="mb-4">
                    <p><strong>Application ID:</strong> <span id="view-app-id"></span></p>
                    <p><strong>Client Name:</strong> <span id="view-client-name"></span></p>
                    <p><strong>Meter Serial Number:</strong> <span id="view-meter-serial"></span></p>
                    <p><strong>Application Date:</strong> <span id="view-app-date"></span></p>
                    <p><strong>Status:</strong> <span id="view-app-status"></span></p>
                    <p><strong>Notes:</strong> <span id="view-app-notes"></span></p>
                    <div id="rejection-reason-container" style="display: none;">
                        <p><strong>Rejection Reason:</strong> <span id="view-rejection-reason"></span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Application Modal -->
        <div id="reviewApplicationModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 class="text-xl font-semibold mb-4">Review Application</h2>
                <form method="POST">
                    <input type="hidden" name="application_id" id="review-app-id">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Decision:
                        </label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="decision" value="approve" class="form-radio" checked>
                                <span class="ml-2">Approve</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="decision" value="reject" class="form-radio">
                                <span class="ml-2">Reject</span>
                            </label>
                        </div>
                    </div>
                    
                    <div id="rejection-reason" class="mb-4" style="display: none;">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="rejection_reason">
                            Rejection Reason:
                        </label>
                        <textarea name="rejection_reason" id="rejection_reason" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" class="btn btn-secondary mr-2 close-modal">Cancel</button>
                        <button type="submit" id="approve-btn" name="action" value="approve_application" class="btn btn-success">Approve Application</button>
                        <button type="submit" id="reject-btn" name="action" value="reject_application" class="btn btn-danger" style="display: none;">Reject Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('hidden');
            document.getElementById('content').classList.toggle('expanded');
        });
        
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons and content
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked button and corresponding content
                this.classList.add('active');
                document.getElementById(this.dataset.tab + '-tab').classList.add('active');
            });
        });
        
        // Modal functionality
        const viewModal = document.getElementById('viewApplicationModal');
        const reviewModal = document.getElementById('reviewApplicationModal');
        
        // View application buttons
        document.querySelectorAll('.view-application').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const client = this.dataset.client;
                const meter = this.dataset.meter;
                const date = this.dataset.date;
                const notes = this.dataset.notes;
                const status = this.dataset.status;
                const reason = this.dataset.reason;
                
                document.getElementById('view-app-id').textContent = id;
                document.getElementById('view-client-name').textContent = client;
                document.getElementById('view-meter-serial').textContent = meter;
                document.getElementById('view-app-date').textContent = date;
                document.getElementById('view-app-notes').textContent = notes;
                
                if (status) {
                    let statusText = '';
                    let statusClass = '';
                    
                    if (status === 'approved') {
                        statusText = 'Approved';
                        statusClass = 'badge-approved';
                    } else if (status === 'rejected') {
                        statusText = 'Rejected';
                        statusClass = 'badge-rejected';
                    } else if (status === 'submitted_to_admin') {
                        statusText = 'Submitted to Admin';
                        statusClass = 'badge-submitted';
                    } else {
                        statusText = 'Pending';
                        statusClass = 'badge-pending';
                    }
                    
                    document.getElementById('view-app-status').innerHTML = `<span class="badge ${statusClass}">${statusText}</span>`;
                    
                    if (status === 'rejected' && reason) {
                        document.getElementById('rejection-reason-container').style.display = 'block';
                        document.getElementById('view-rejection-reason').textContent = reason;
                    } else {
                        document.getElementById('rejection-reason-container').style.display = 'none';
                    }
                } else {
                    document.getElementById('view-app-status').innerHTML = '<span class="badge badge-pending">Pending</span>';
                    document.getElementById('rejection-reason-container').style.display = 'none';
                }
                
                viewModal.style.display = 'block';
            });
        });
        
        // Review application buttons
        document.querySelectorAll('.review-application').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('review-app-id').value = this.dataset.id;
                reviewModal.style.display = 'block';
            });
        });
        
        // Decision radio buttons
        document.querySelectorAll('input[name="decision"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'approve') {
                    document.getElementById('rejection-reason').style.display = 'none';
                    document.getElementById('approve-btn').style.display = 'block';
                    document.getElementById('reject-btn').style.display = 'none';
                } else {
                    document.getElementById('rejection-reason').style.display = 'block';
                    document.getElementById('approve-btn').style.display = 'none';
                    document.getElementById('reject-btn').style.display = 'block';
                }
            });
        });
        
        // Close buttons
        document.querySelectorAll('.close, .close-modal').forEach(element => {
            element.addEventListener('click', function() {
                viewModal.style.display = 'none';
                reviewModal.style.display = 'none';
            });
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === viewModal) {
                viewModal.style.display = 'none';
            }
            if (event.target === reviewModal) {
                reviewModal.style.display = 'none';
            }
        });
    </script>
</body>
</html>
