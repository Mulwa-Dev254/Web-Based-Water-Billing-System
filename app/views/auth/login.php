<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Water Billing System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0077b6;
            --primary-dark: #023e8a;
            --primary-light: #90e0ef;
            --accent-color: #00b4d8;
            --text-color: #333;
            --text-light: #666;
            --bg-color: #f8f9fa;
            --white: #ffffff;
            --shadow-sm: 0 2px 5px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.12);
            --shadow-lg: 0 10px 30px rgba(0,0,0,0.15);
            --rounded-sm: 6px;
            --rounded-md: 12px;
            --rounded-lg: 20px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
            background-color: var(--bg-color);
            position: relative;
        }

        /* Background and Water Animation */
        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 60%; /* Reduced width to create angled effect */
            height: 100%;
            background-image: url('/water_billing_system/images/loginbg.jpg');
            background-size: cover;
            background-position: center;
            filter: brightness(0.7) saturate(1.2);
            clip-path: polygon(0 0, 100% 0, 85% 100%, 0 100%); /* Creates angled edge */
            animation: backgroundPulse 15s ease-in-out infinite;
        }
        
        @keyframes backgroundPulse {
            0%, 100% {
                filter: brightness(0.7) saturate(1.2);
            }
            50% {
                filter: brightness(0.8) saturate(1.4);
            }
        }
        
        /* Floating bubbles in background */
        .background-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 25%),
                        radial-gradient(circle at 80% 30%, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 25%),
                        radial-gradient(circle at 40% 80%, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 25%);
            animation: bubbleFloat 20s ease-in-out infinite alternate;
            z-index: 2;
            pointer-events: none;
        }
        
        @keyframes bubbleFloat {
            0% {
                background-position: 0% 0%, 0% 0%, 0% 0%;
            }
            100% {
                background-position: 10% 20%, -15% 10%, 5% -10%;
            }
        }
        
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 60%; /* Match background image width */
            height: 100%;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.8) 0%, rgba(76, 201, 240, 0.7) 100%);
            z-index: 1;
            clip-path: polygon(0 0, 100% 0, 85% 100%, 0 100%); /* Match background image clip path */
        }
        
        /* System Title Styles */
        .system-title {
            position: absolute;
            top: 30%;
            left: 5%;
            z-index: 10;
            color: white;
            max-width: 45%;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .system-title h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }
        
        .system-title p {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .water-drop {
            position: absolute;
            width: 15px;
            height: 15px;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
            animation: fall linear infinite;
            z-index: 2;
        }

        @keyframes fall {
            0% {
                transform: translateY(-100px) scale(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            95% {
                opacity: 0.8;
            }
            100% {
                transform: translateY(100vh) scale(1);
                opacity: 0;
            }
        }

        /* Navigation Ribbon */
        .nav-ribbon {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: var(--shadow-md);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            color: var(--white);
            text-decoration: none;
            font-size: 1.1rem;
        }

        .nav-logo i {
            font-size: 1.25rem;
        }

        .nav-actions {
            display: flex;
            gap: 1rem;
        }

        .nav-btn {
            padding: 0.5rem 1rem;
            border-radius: var(--rounded-sm);
            font-weight: 500;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            text-decoration: none;
        }

        .nav-btn-primary {
            background-color: var(--white);
            color: var(--primary);
        }

        .nav-btn-primary:hover {
            background-color: rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
        }

        .nav-btn-outline {
            border: 1px solid rgba(255, 255, 255, 0.7);
            color: var(--white);
            background-color: rgba(67, 97, 238, 0.7);
            position: relative;
            overflow: hidden;
        }

        .nav-btn-outline:hover {
            background-color: rgba(76, 201, 240, 0.7);
        }
        
        /* Water droplet effect for buttons */
        .nav-btn-outline::before,
        .btn-primary::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -20%;
            width: 140%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0) 70%);
            transform: rotate(20deg);
            animation: waterShimmer 3s linear infinite;
        }
        
        @keyframes waterShimmer {
            0% {
                transform: translateY(-50%) rotate(20deg);
                opacity: 0;
            }
            20% {
                opacity: 0.2;
            }
            50% {
                opacity: 0.3;
            }
            80% {
                opacity: 0.2;
            }
            100% {
                transform: translateY(50%) rotate(20deg);
                opacity: 0;
            }
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 2rem 4rem;
            position: relative;
            z-index: 10;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: var(--rounded-lg);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 420px;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .login-header {
            margin-bottom: 2rem;
            position: relative;
        }

        .login-header h2 {
            color: var(--primary);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .login-header p {
            color: var(--gray);
            font-size: 1rem;
        }

        .water-icon {
            font-size: 2.5rem;
            color: var(--secondary);
            margin-bottom: 1rem;
            display: inline-block;
            position: relative;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1rem 1rem 2.8rem;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: var(--rounded-md);
            font-size: 1rem;
            transition: var(--transition);
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: var(--shadow-sm);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            background-color: var(--white);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 1.5rem;
            border-radius: var(--rounded-md);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: 'Poppins', sans-serif;
            width: 100%;
            font-size: 1rem;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.9) 0%, rgba(76, 201, 240, 0.9) 100%);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, rgba(76, 201, 240, 0.9) 0%, rgba(67, 97, 238, 0.9) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(67, 97, 238, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        
        /* Additional animation for login button */
        .btn-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: buttonWave 2s infinite;
        }
        
        @keyframes buttonWave {
            0% {
                left: -100%;
            }
            50%, 100% {
                left: 100%;
            }
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .alert {
            padding: 1rem;
            border-radius: var(--rounded-md);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        .alert-error {
            color: var(--danger);
            background-color: rgba(239, 71, 111, 0.1);
            border-left: 4px solid var(--danger);
        }

        .alert-success {
            color: var(--success);
            background-color: rgba(6, 214, 160, 0.1);
            border-left: 4px solid var(--success);
        }

        .login-footer {
            margin-top: 2rem;
            font-size: 0.95rem;
            color: var(--dark);
        }

        .login-footer a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s ease;
            position: relative;
        }

        .login-footer a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }

        .login-footer a:hover::after {
            width: 100%;
        }

        .login-footer a:hover {
            color: var(--primary-dark);
        }

        @media (max-width: 1024px) {
            .main-content {
                justify-content: center;
                padding: 2rem 1rem;
            }
            
            .background-image, .overlay {
                width: 50%;
            }
            
            .system-title {
                max-width: 40%;
            }
            
            .system-title h1 {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .background-image, .overlay {
                width: 40%;
            }
            
            .system-title {
                max-width: 35%;
                left: 2%;
            }
            
            .system-title h1 {
                font-size: 1.8rem;
            }
            
            .system-title p {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem;
                margin: 0.5rem;
            }
            
            .login-header h2 {
                font-size: 1.5rem;
            }

            .nav-ribbon {
                padding: 1rem;
                flex-direction: column;
                gap: 0.75rem;
            }

            .nav-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .background-image, .overlay {
                width: 100%;
                clip-path: polygon(0 0, 100% 0, 100% 40%, 0 60%);
                height: 50%;
            }
            
            .system-title {
                max-width: 90%;
                top: 15%;
                left: 5%;
                text-align: center;
            }
            
            .system-title h1 {
                font-size: 1.5rem;
            }
        }

            .nav-btn {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
 
    <div id="loader" class="loader-overlay"><div class="spinner"></div></div>
    <style>
        .loader-overlay{position:fixed;inset:0;z-index:2000;background:linear-gradient(120deg,rgba(255,255,255,.85),rgba(255,255,255,.6));backdrop-filter:saturate(180%) blur(8px);display:flex;align-items:center;justify-content:center;transition:opacity .4s ease}
        .loader-overlay.loader-hidden{opacity:0;pointer-events:none}
        .spinner{position:relative;width:10em;height:10em}
        .spinner:before{transform:rotateX(60deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateBefore infinite linear reverse}
        .spinner:after{transform:rotateX(240deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateAfter infinite linear}
        .spinner:before,.spinner:after{box-sizing:border-box;content:'';display:block;position:absolute;top:50%;left:50%;margin-top:-5em;margin-left:-5em;width:10em;height:10em;transform-style:preserve-3d;transform-origin:50%;perspective-origin:50% 50%;perspective:340px;background-size:10em 10em;background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjI2NnB4IiBoZWlnaHQ9IjI5N3B4IiB2aWV3Qm94PSIwIDAgMjY2IDI5NyIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWxuczpza2V0Y2g9Imh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaC9ucyI+CiAgICA8dGl0bGU+c3Bpbm5lcjwvdGl0bGU+CiAgICA8ZGVzY3JpcHRpb24+Q3JlYXRlZCB3aXRoIFNrZXRjaCAoaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoKTwvZGVzY3JpcHRpb24+CiAgICA8ZGVmcz48L2RlZnM+CiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBza2V0Y2g6dHlwZT0iTVNQYWdlIj4KICAgICAgICA8cGF0aCBkPSJNMTcxLjUwNzgxMywzLjI1MDAwMDM4IEMyMjYuMjA4MTgzLDEyLjg1NzcxMTEgMjk3LjExMjcyMiw3MS40OTEyODIzIDI1MC44OTU1OTksMTA4LjQxMDE1NSBDMjE2LjU4MjAyNCwxMzUuODIwMzEgMTg2LjUyODQwNSw5Ny4wNjI0OTY0IDE1Ni44MDA3NzQsODUuNzczNDM0NiBDMTI3LjA3MzE0Myw3NC40ODQzNzIxIDc2Ljg4ODQ2MzIsODQuMjE2MTQ2MiA2MC4xMjg5MDY1LDEwOC40MTAxNTMgQy0xNS45ODA0Njg1LDIxOC4yODEyNDcgMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IDE0NS4yNzczNDQsMjk2LjY2Nzk2OCBDMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IC0yNS40NDkyMTg3LDI1Ny4yNDIxOTggMy4zOTg0Mzc1LDEwOC40MTAxNTUgQzE2LjMwNzA2NjEsNDEuODExNDE3NCA4NC43Mjc1ODI5LC0xMS45OTIyOTg1IDE3MS41MDc4MTMsMy4yNTAwMDAzOCBaIiBpZD0iUGF0aC0xIiBmaWxsPSIjMDAwMDAwIiBza2V0Y2g6dHlwZT0iTVNTaGFwZUdyb3VwIj48L3BhdGg+CiAgICA8L2c+Cjwvc3ZnPg==)}
        @keyframes rotateBefore{from{transform:rotateX(60deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(60deg) rotateY(45deg) rotateZ(-360deg)}}
        @keyframes rotateAfter{from{transform:rotateX(240deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(240deg) rotateY(45deg) rotateZ(360deg)}}
    </style>
    <script>
        window.addEventListener('load',()=>{const l=document.getElementById('loader');if(l){setTimeout(()=>{l.classList.add('loader-hidden');setTimeout(()=>{try{l.remove()}catch(e){}},600);},1500)}});
    </script>
    
    <!-- Background Container with Water Animation -->
    <div class="background-container">
        <div class="background-image"></div>
        <div class="overlay"></div>
        <!-- Water drops will be added here via JavaScript -->
    </div>

    <!-- Navigation Ribbon -->
    <nav class="nav-ribbon">
        <a href="index.php" class="nav-logo">
            <i class="fas fa-tint"></i>
            <span>Water Billing System</span>
        </a>
        <div class="nav-actions">
            <a href="index.php" class="nav-btn nav-btn-outline">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="#" class="nav-btn nav-btn-primary" id="reportIssueBtn">
                <i class="fas fa-exclamation-circle"></i>
                <span>Report Issue</span>
            </a>
        </div>
    </nav>

    <!-- System Title -->
    <div class="system-title">
        <h1>Automated Water Management System</h1>
        <p>Efficient Billing & Resource Management</p>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="login-container">
            <div class="login-header">
                <div class="water-icon">
                    <i class="fas fa-tint"></i>
                </div>
                <h2>Welcome Back</h2>
                <p>Sign in to your water services account</p>
            </div>

            <?php if (isset($error) && $error != ''): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($success) && $success != ''): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form action="index.php?page=login" method="POST">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username or email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="login-footer">
                Don't have an account? <a href="index.php?page=register">Register here</a>
            </div>
            <div style="margin-top:0.75rem;text-align:center;color:#6b7280;font-size:0.82rem;">&copy; <?php echo date('Y'); ?> AquaBill Water Billing System. All rights reserved.</div>
            <div style="margin-top:0.25rem;text-align:center;color:#94a3b8;font-size:0.74rem;font-weight:500;">
                <?php
                    $p = __DIR__ . '/../../config/.owner';
                    $t = is_file($p) ? trim((string)file_get_contents($p)) : '';
                    echo htmlspecialchars($t !== '' ? $t : '');
                ?>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        // Report Issue Button Event
        document.getElementById('reportIssueBtn').addEventListener('click', function(e) {
            e.preventDefault();
            alert('Issue reporting feature will be implemented soon. Thank you for your patience!');
        });

        // Water Droplet Animation
        document.addEventListener('DOMContentLoaded', function() {
            const backgroundContainer = document.querySelector('.background-container');
            const numberOfDrops = 20;
            
            // Create water drops
            for (let i = 0; i < numberOfDrops; i++) {
                createWaterDrop(backgroundContainer);
            }
            
            // Set interval to continuously create new drops
            setInterval(() => {
                createWaterDrop(backgroundContainer);
            }, 1000);
        });
        
        function createWaterDrop(container) {
            const drop = document.createElement('div');
            drop.classList.add('water-drop');
            
            // Random position, size, and animation duration
            const size = Math.random() * 10 + 5; // 5-15px
            const posX = Math.random() * 100; // 0-100%
            const duration = Math.random() * 5 + 3; // 3-8s
            const delay = Math.random() * 2; // 0-2s
            
            drop.style.width = `${size}px`;
            drop.style.height = `${size}px`;
            drop.style.left = `${posX}%`;
            drop.style.animationDuration = `${duration}s`;
            drop.style.animationDelay = `${delay}s`;
            drop.style.opacity = Math.random() * 0.5 + 0.3; // 0.3-0.8 opacity
            
            container.appendChild(drop);
            
            // Remove drop after animation completes
            setTimeout(() => {
                drop.remove();
            }, (duration + delay) * 1000);
        }
    </script>
</body>
</html>
