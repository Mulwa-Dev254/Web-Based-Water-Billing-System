<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collector Profile - AquaBill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .profile-card {
            background-color: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 2rem;
            text-align: center;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #e0f2fe;
            color: #2563eb;
            font-size: 4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            border: 3px solid #bfdbfe;
        }
        .info-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            font-size: 1.1rem;
            color: #334155;
        }
        .info-item i {
            color: #2563eb;
            font-size: 1.25rem;
        }
    </style>
</head>
<body class="flex h-screen bg-gray-100">
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="flex justify-between items-center p-6 bg-white border-b border-gray-200">
            <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700">Welcome, <span class="font-semibold text-blue-700"><?php echo htmlspecialchars($data['username']); ?></span></span>
                <a href="index.php?page=logout" class="text-red-600 hover:text-red-800 flex items-center">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="max-w-xl mx-auto profile-card">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($data['username']); ?></h2>
                <div class="info-item">
                    <i class="fas fa-briefcase"></i>
                    <span>Role: <span class="font-semibold"><?php echo htmlspecialchars(ucfirst($data['role'])); ?></span></span>
                </div>
                <!-- Add more profile details here if available from the user model -->
                <!-- Example: -->
                <!-- <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <span>Email: <span class="font-semibold"><?php // echo htmlspecialchars($data['email']); ?></span></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <span>Phone: <span class="font-semibold"><?php // echo htmlspecialchars($data['contact_phone']); ?></span></span>
                </div> -->
                <p class="text-gray-600 mt-4">This is your personal profile page. You can view your details here.</p>
            </div>
        </main>
    </div>
</body>
</html>