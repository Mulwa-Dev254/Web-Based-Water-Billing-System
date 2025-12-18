<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Manager Profile</title>
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

        /* Card Styles */
        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
            margin-bottom: 0.5rem;
        }

        .form-control {
            display: block;
            width: 100%;
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 400;
            line-height: 1.5;
            color: #1f2937;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #93c5fd;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }

        .form-control:disabled {
            background-color: #f3f4f6;
            opacity: 1;
        }

        /* Profile Section */
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 6rem;
            height: 6rem;
            border-radius: 9999px;
            background-color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            font-size: 2rem;
            color: #6b7280;
            overflow: hidden;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.25rem;
        }

        .profile-info p {
            font-size: 0.875rem;
            color: #6b7280;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: 1px solid var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid #d1d5db;
            color: #4b5563;
        }

        .btn-outline:hover {
            background-color: #f3f4f6;
        }

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

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
        }

        .tab {
            padding: 0.75rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }

        .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab:hover {
            color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .profile-avatar {
                margin-right: 0;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include_once __DIR__ . '/partials/sidebar.php'; ?>

        <div class="main-content">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">My Profile</h1>
                <p class="text-gray-600">View and update your profile information</p>
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

            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php if (!empty($data['user']['profile_image'])): ?>
                        <img src="uploads/profiles/<?= htmlspecialchars($data['user']['profile_image']) ?>" alt="Profile Image">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h3><?= htmlspecialchars($data['user']['full_name']) ?></h3>
                    <p><?= htmlspecialchars($data['user']['email']) ?></p>
                    <p class="text-sm text-blue-600 mt-1">Finance Manager</p>
                </div>
            </div>

            <!-- Profile Tabs -->
            <div class="card">
                <div class="card-header">
                    <div class="tabs">
                        <div class="tab active" data-tab="personal-info">Personal Information</div>
                        <div class="tab" data-tab="change-password">Change Password</div>
                        <div class="tab" data-tab="account-settings">Account Settings</div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Personal Information Tab -->
                    <div class="tab-content active" id="personal-info">
                        <form action="index.php?page=finance_manager_update_profile" method="POST" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($data['user']['full_name']) ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($data['user']['email']) ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($data['user']['phone'] ?? '') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <input type="text" id="address" name="address" class="form-control" value="<?= htmlspecialchars($data['user']['address'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="profile_image">Profile Image</label>
                                <input type="file" id="profile_image" name="profile_image" class="form-control" accept="image/*">
                                <small class="text-gray-500">Leave empty to keep current image</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="bio">Bio</label>
                                <textarea id="bio" name="bio" class="form-control" rows="4"><?= htmlspecialchars($data['user']['bio'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Change Password Tab -->
                    <div class="tab-content" id="change-password">
                        <form action="index.php?page=finance_manager_change_password" method="POST">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                                <small class="text-gray-500">Password must be at least 8 characters long</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key mr-2"></i> Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Account Settings Tab -->
                    <div class="tab-content" id="account-settings">
                        <div class="form-group">
                            <label>Email Notifications</label>
                            <div class="mt-2">
                                <div class="flex items-center mb-2">
                                    <input type="checkbox" id="notify_transactions" name="notify_transactions" class="mr-2" <?= isset($data['user']['settings']['notify_transactions']) && $data['user']['settings']['notify_transactions'] ? 'checked' : '' ?>>
                                    <label for="notify_transactions" class="text-sm">Receive notifications for new transactions</label>
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="checkbox" id="notify_reports" name="notify_reports" class="mr-2" <?= isset($data['user']['settings']['notify_reports']) && $data['user']['settings']['notify_reports'] ? 'checked' : '' ?>>
                                    <label for="notify_reports" class="text-sm">Receive notifications for new reports</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="notify_flagged" name="notify_flagged" class="mr-2" <?= isset($data['user']['settings']['notify_flagged']) && $data['user']['settings']['notify_flagged'] ? 'checked' : '' ?>>
                                    <label for="notify_flagged" class="text-sm">Receive notifications for flagged transactions</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Account Security</label>
                            <div class="mt-2">
                                <div class="flex items-center mb-2">
                                    <input type="checkbox" id="two_factor_auth" name="two_factor_auth" class="mr-2" <?= isset($data['user']['settings']['two_factor_auth']) && $data['user']['settings']['two_factor_auth'] ? 'checked' : '' ?>>
                                    <label for="two_factor_auth" class="text-sm">Enable two-factor authentication</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="login_alerts" name="login_alerts" class="mr-2" <?= isset($data['user']['settings']['login_alerts']) && $data['user']['settings']['login_alerts'] ? 'checked' : '' ?>>
                                    <label for="login_alerts" class="text-sm">Receive alerts for new login attempts</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary" id="save-settings">
                                <i class="fas fa-save mr-2"></i> Save Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Activity Card -->
            <div class="card">
                <div class="card-header">
                    <h2>Recent Account Activity</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['activity_logs'])): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase text-gray-500 border-b">
                                    <th class="px-4 py-2">Activity</th>
                                    <th class="px-4 py-2">IP Address</th>
                                    <th class="px-4 py-2">Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['activity_logs'] as $log): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <span class="w-8 text-center">
                                                <i class="fas <?= $log['activity_type'] === 'login' ? 'fa-sign-in-alt text-green-500' : ($log['activity_type'] === 'logout' ? 'fa-sign-out-alt text-red-500' : 'fa-user-edit text-blue-500') ?>"></i>
                                            </span>
                                            <span><?= htmlspecialchars($log['description']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($log['ip_address']) ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars(date('M d, Y h:i A', strtotime($log['created_at']))) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4 text-gray-500">
                        <p>No recent activity found</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Tab switching functionality
            $('.tab').click(function() {
                const tabId = $(this).data('tab');
                
                // Update active tab
                $('.tab').removeClass('active');
                $(this).addClass('active');
                
                // Show corresponding tab content
                $('.tab-content').removeClass('active');
                $('#' + tabId).addClass('active');
            });
            
            // Save account settings
            $('#save-settings').click(function(e) {
                e.preventDefault();
                
                const settings = {
                    notify_transactions: $('#notify_transactions').is(':checked'),
                    notify_reports: $('#notify_reports').is(':checked'),
                    notify_flagged: $('#notify_flagged').is(':checked'),
                    two_factor_auth: $('#two_factor_auth').is(':checked'),
                    login_alerts: $('#login_alerts').is(':checked')
                };
                
                // Send AJAX request to save settings
                $.ajax({
                    url: 'index.php?page=finance_manager_save_settings',
                    type: 'POST',
                    data: { settings: JSON.stringify(settings) },
                    success: function(response) {
                        alert('Settings saved successfully!');
                    },
                    error: function() {
                        alert('An error occurred while saving settings.');
                    }
                });
            });
        });
    </script>
</body>
</html>