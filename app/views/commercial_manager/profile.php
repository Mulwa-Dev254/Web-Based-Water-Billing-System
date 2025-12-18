<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commercial Manager Profile - AquaBill</title>
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
            text-decoration: none;
            border-radius: 0.5rem;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .sidebar ul li a:hover, .sidebar ul li a.active {
            background-color: #2d3748;
            color: #63b3ed;
        }
        .sidebar ul li a i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .main-content.full-width {
            margin-left: 0;
        }
        .navbar {
            background-color: #ffffff;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 0.5rem;
            margin-bottom: 20px;
        }
        .navbar .menu-toggle {
            display: none; /* Hidden on desktop */
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #4a5568;
        }
        .profile-card {
            background-color: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 2rem;
            max-width: 700px;
            margin: 20px auto;
        }
        .profile-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.75rem 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .profile-item:last-child {
            border-bottom: none;
        }
        .profile-item .label {
            font-weight: 600;
            color: #4a5568;
            width: 150px; /* Fixed width for labels */
            flex-shrink: 0;
        }
        .profile-item .value {
            color: #2d3748;
            flex-grow: 1;
        }
        .profile-item i {
            margin-right: 10px;
            color: #63b3ed;
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">AquaBill CM</div>
        <ul>
            <li><a href="index.php?page=commercial_manager_dashboard"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="index.php?page=commercial_manager_manage_meters"><i class="fas fa-tachometer-alt"></i> Manage Meters</a></li>
            <li><a href="index.php?page=commercial_manager_review_applications"><i class="fas fa-clipboard-list"></i> Review Applications</a></li>
            <li><a href="index.php?page=commercial_manager_reports"><i class="fas fa-chart-pie"></i> Reports</a></li>
            <li><a href="index.php?page=commercial_manager_profile" class="active"><i class="fas fa-user-circle"></i> Profile</a></li>
            <li><a href="index.php?page=logout" class="text-red-400 hover:text-red-200"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content" id="mainContent">
        <div class="navbar">
            <button class="menu-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="text-2xl font-semibold text-gray-800">My Profile</h1>
            <div class="user-info flex items-center space-x-2">
                <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
                <span class="text-sm bg-blue-500 text-white px-3 py-1 rounded-full"><?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Commercial Manager')); ?></span>
            </div>
        </div>

        <?php if (!empty($data['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($data['error']); ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($data['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($data['success']); ?></span>
            </div>
        <?php endif; ?>

        <div class="profile-card">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Commercial Manager Details</h2>
            <?php if (!empty($data['user'])): ?>
                <div class="profile-item">
                    <i class="fas fa-id-badge"></i><span class="label">User ID:</span><span class="value"><?php echo htmlspecialchars($data['user']['id']); ?></span>
                </div>
                <div class="profile-item">
                    <i class="fas fa-user"></i><span class="label">Username:</span><span class="value"><?php echo htmlspecialchars($data['user']['username']); ?></span>
                </div>
                <div class="profile-item">
                    <i class="fas fa-envelope"></i><span class="label">Email:</span><span class="value"><?php echo htmlspecialchars($data['user']['email']); ?></span>
                </div>
                <div class="profile-item">
                    <i class="fas fa-briefcase"></i><span class="label">Role:</span><span class="value"><?php echo htmlspecialchars(ucfirst($data['user']['role'])); ?></span>
                </div>
                <div class="profile-item">
                    <i class="fas fa-signature"></i><span class="label">Full Name:</span><span class="value"><?php echo htmlspecialchars($data['user']['full_name'] ?? 'N/A'); ?></span>
                </div>
                <div class="profile-item">
                    <i class="fas fa-map-marker-alt"></i><span class="label">Address:</span><span class="value"><?php echo htmlspecialchars($data['user']['address'] ?? 'N/A'); ?></span>
                </div>
                <div class="profile-item">
                    <i class="fas fa-phone"></i><span class="label">Contact Phone:</span><span class="value"><?php echo htmlspecialchars($data['user']['contact_phone'] ?? 'N/A'); ?></span>
                </div>
                <div class="profile-item">
                    <i class="fas fa-info-circle"></i><span class="label">Account Status:</span><span class="value"><?php echo htmlspecialchars(ucfirst($data['user']['status'])); ?></span>
                </div>
                <div class="profile-item">
                    <i class="fas fa-calendar-alt"></i><span class="label">Member Since:</span><span class="value"><?php echo htmlspecialchars(date('Y-m-d', strtotime($data['user']['created_at']))); ?></span>
                </div>
                <div class="profile-item">
                    <i class="fas fa-sign-in-alt"></i><span class="label">Last Login:</span><span class="value"><?php echo htmlspecialchars($data['user']['last_login_at'] ? date('Y-m-d H:i:s', strtotime($data['user']['last_login_at'])) : 'Never'); ?></span>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-600">User profile data not found.</p>
            <?php endif; ?>
            <div class="text-center mt-6">
                <a href="index.php?page=admin_manage_users" class="btn bg-blue-500 hover:bg-blue-600 text-white"><i class="fas fa-edit mr-2"></i> Edit Profile (Admin Only)</a>
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
        });
    </script>
</body>
</html>