<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meter Reader Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
            padding: 1.5rem;
        }
        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-body {
            padding: 1.5rem;
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
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }
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
            background-color: #2563eb;
            color: white;
            border: 1px solid #2563eb;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        .btn-success {
            background-color: #10b981;
            color: white;
            border: 1px solid #10b981;
        }
        .btn-success:hover {
            background-color: #059669;
            border-color: #059669;
        }
        .btn-danger {
            background-color: #ef4444;
            color: white;
            border: 1px solid #ef4444;
        }
        .btn-danger:hover {
            background-color: #dc2626;
            border-color: #dc2626;
        }
        .btn-outline {
            background-color: transparent;
            border: 1px solid #d1d5db;
            color: #4b5563;
        }
        .btn-outline:hover {
            background-color: #f3f4f6;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        .badge-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        .badge-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        .badge-info {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        .nav-tabs {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
        }
        .nav-tabs .nav-link {
            padding: 0.75rem 1rem;
            font-weight: 500;
            color: #6b7280;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
        }
        .nav-tabs .nav-link:hover {
            color: #4b5563;
        }
        .nav-tabs .nav-link.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }
        .tab-content {
            padding: 1.5rem 0;
        }
        .tab-pane {
            display: none;
        }
        .tab-pane.active {
            display: block;
        }
        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .avatar-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .activity-item {
            padding: 1rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include_once __DIR__ . '/sidebar.php'; ?>

        <div class="main-content">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">My Profile</h1>
                <p class="text-gray-600">Manage your account details</p>
            </div>

            <?php if (!empty($data['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($data['error']) ?></p>
                </div>
            <?php endif; ?>
            <?php if (!empty($data['success'])): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                    <p class="text-sm text-green-700"><?= htmlspecialchars($data['success']) ?></p>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-800">Summary</h2>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-500">Username</p>
                                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($data['userDetails']['username'] ?? '') ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Role</p>
                                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($data['userDetails']['role'] ?? '') ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Status</p>
                                    <span class="badge <?= (($data['userDetails']['status'] ?? 'active') === 'active') ? 'badge-success' : 'badge-danger' ?>">
                                        <?= htmlspecialchars(ucfirst($data['userDetails']['status'] ?? 'active')) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="card mb-6">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-800">Edit Profile</h2>
                        </div>
                        <div class="card-body">
                            <form action="index.php?page=meter_reader_profile" method="POST">
                                <input type="hidden" name="action" value="update_profile">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="full_name" class="form-label">Full Name</label>
                                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?= htmlspecialchars($data['userDetails']['full_name'] ?? '') ?>" required>
                                    </div>
                                    <div>
                                        <label for="contact_phone" class="form-label">Contact Phone</label>
                                        <input type="tel" id="contact_phone" name="contact_phone" class="form-control" value="<?= htmlspecialchars($data['userDetails']['contact_phone'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($data['userDetails']['email'] ?? '') ?>" required>
                                    </div>
                                    <div>
                                        <label for="address" class="form-label">Address</label>
                                        <input type="text" id="address" name="address" class="form-control" value="<?= htmlspecialchars($data['userDetails']['address'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-800">Change Password</h2>
                        </div>
                        <div class="card-body">
                            <form action="index.php?page=meter_reader_profile" method="POST">
                                <input type="hidden" name="action" value="change_password">
                                <div class="mb-6">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>
                                <div class="mb-6">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                                </div>
                                <div class="mb-6">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key mr-1"></i> Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script></script>
</body>
</html>
