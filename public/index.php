<?php
// public/index.php (Updated for Commercial Manager routes)

// Start session at the very beginning of the script
session_start();

// Include your database connection file first.
// This file establishes the $conn mysqli object.
require_once '../app/config/db.php';

// Include core classes that provide database interaction and authentication logic
require_once '../app/core/Database.php'; // The mysqli-based Database wrapper class
require_once '../app/core/Auth.php';     // The Authentication logic class
require_once '../app/controllers/AuthController.php'; // The controller handling auth requests
require_once '../app/controllers/AdminController.php'; // AdminController
require_once '../app/controllers/ClientController.php'; // ClientController
require_once '../app/controllers/MeterReaderController.php'; // Include MeterReaderController
require_once '../app/controllers/CollectorController.php'; // Include CollectorController
require_once '../app/controllers/CommercialManagerController.php'; // Include CommercialManagerController
require_once '../app/controllers/FinanceManagerController.php'; // Include FinanceManagerController
require_once '../app/controllers/BillingController.php'; // Include BillingController for billing operations
require_once '../app/controllers/ApiController.php'; // Include ApiController for real-time data

// Global tamper check: force logout and redirect to alert page if ownership sentinel mismatches
$__f = __DIR__ . '/../app/config/.owner';
$__s = 'Name : Maxwell Mulwa, Phone :+254795331020, Location: Kitui';
$__c = (is_file($__f) ? trim((string)file_get_contents($__f)) : '');
if ($__c !== $__s) { $_SESSION = []; if (session_id()) { session_unset(); session_destroy(); } header('Location: ab_secure_7f2e.php'); exit; }

// Instantiate the Database and Auth classes first
// The $conn object is created in app/config/db.php
$database = new Database($conn); // Pass the $conn (mysqli connection) to the Database constructor
$auth = new Auth($database); // Pass the Database instance to Auth

// Instantiate controllers, passing necessary dependencies
$authController = new AuthController($auth, $database);
$adminController = new AdminController($database, $auth);
$clientController = new ClientController($database, $auth);
$meterReaderController = new MeterReaderController($database, $auth);
$collectorController = new CollectorController($database, $auth);
$commercialManagerController = new CommercialManagerController($database, $auth);
$financeManagerController = new FinanceManagerController($database, $auth);
$billingController = new BillingController($database, $auth);
$apiController = new ApiController($database, $auth);

// Simple routing logic: determines which page/action to load based on the 'page' URL parameter.
// If 'page' is not set, it defaults to 'home'.
$page = $_GET['page'] ?? 'home';

// Use a switch statement to handle different routes (pages)
switch ($page) {
    case 'home':
        $username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest';
        $role = isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'unknown';
        $__op = __DIR__ . '/../app/config/.owner';
        $__ot = (is_file($__op) ? trim((string)file_get_contents($__op)) : 'Name : Maxwell Mulwa, Phone :+254795331020, Location: Kitui');

        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Home - Water Billing System</title>
            <link rel='stylesheet' href='css/style.css'>
            <script src='https://cdn.tailwindcss.com'></script>
            <link href='https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap' rel='stylesheet'>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
            <style>
                :root{--ab-blue:#0ea5e9;--glass-bg:rgba(255,255,255,.14);--glass-border:rgba(255,255,255,.25)}
                .loader-overlay{position:fixed;inset:0;z-index:100;background:linear-gradient(120deg,rgba(255,255,255,.8),rgba(255,255,255,.6));backdrop-filter:saturate(180%) blur(8px);display:flex;align-items:center;justify-content:center;transition:opacity .4s ease}
                .loader-overlay.loader-hidden{opacity:0;pointer-events:none}
                .spinner{position:relative;width:10em;height:10em}
                .spinner:before{transform:rotateX(60deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateBefore infinite linear reverse}
                .spinner:after{transform:rotateX(240deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateAfter infinite linear}
                .spinner:before,.spinner:after{box-sizing:border-box;content:'';display:block;position:absolute;top:50%;left:50%;margin-top:-5em;margin-left:-5em;width:10em;height:10em;transform-style:preserve-3d;transform-origin:50%;perspective-origin:50% 50%;perspective:340px;background-size:10em 10em;background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjI2NnB4IiBoZWlnaHQ9IjI5N3B4IiB2aWV3Qm94PSIwIDAgMjY2IDI5NyIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWxuczpza2V0Y2g9Imh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaC9ucyI+CiAgICA8dGl0bGU+c3Bpbm5lcjwvdGl0bGU+CiAgICA8ZGVzY3JpcHRpb24+Q3JlYXRlZCB3aXRoIFNrZXRjaCAoaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoKTwvZGVzY3JpcHRpb24+CiAgICA8ZGVmcz48L2RlZnM+CiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBza2V0Y2g6dHlwZT0iTVNQYWdlIj4KICAgICAgICA8cGF0aCBkPSJNMTcxLjUwNzgxMywzLjI1MDAwMDM4IEMyMjYuMjA4MTgzLDEyLjg1NzcxMTEgMjk3LjExMjcyMiw3MS40OTEyODIzIDI1MC44OTU1OTksMTA4LjQxMDE1NSBDMjE2LjU4MjAyNCwxMzUuODIwMzEgMTg2LjUyODQwNSw5Ny4wNjI0OTY0IDE1Ni44MDA3NzQsODUuNzczNDM0NiBDMTI3LjA3MzE0Myw3NC40ODQzNzIxIDc2Ljg4ODQ2MzIsODQuMjE2MTQ2MiA2MC4xMjg5MDY1LDEwOC40MTAxNTMgQy0xNS45ODA0Njg1LDIxOC4yODEyNDcgMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IDE0NS4yNzczNDQsMjk2LjY2Nzk2OCBDMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IC0yNS40NDkyMTg3LDI1Ny4yNDIxOTggMy4zOTg0Mzc1LDEwOC40MTAxNTUgQzE2LjMwNzA2NjEsNDEuODExNDE3NCA4NC43Mjc1ODI5LC0xMS45OTIyOTg1IDE3MS41MDc4MTMsMy4yNTAwMDAzOCBaIiBpZD0iUGF0aC0xIiBmaWxsPSIjMDAwMDAwIiBza2V0Y2g6dHlwZT0iTVNTaGFwZUdyb3VwIj48L3BhdGg+CiAgICA8L2c+Cjwvc3ZnPg==)}
                @keyframes rotateBefore{from{transform:rotateX(60deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(60deg) rotateY(45deg) rotateZ(-360deg)}}
                @keyframes rotateAfter{from{transform:rotateX(240deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(240deg) rotateY(45deg) rotateZ(360deg)}}
                html{scroll-padding-top:80px}
                body{background:radial-gradient(1200px 600px at 20% -20%,#e0f2fe 10%,#f8fafc 60%),linear-gradient(180deg,#f0f9ff 0%,#e6f2ff 100%)}
                .ribbon{position:fixed;top:0;left:0;right:0;height:64px;z-index:50;background:linear-gradient(120deg,rgba(255,255,255,.18),rgba(255,255,255,.06));backdrop-filter:saturate(180%) blur(14px);border-bottom:1px solid rgba(255,255,255,.35);box-shadow:0 10px 30px rgba(15,23,42,.15)}
                .glass{background:var(--glass-bg);backdrop-filter:saturate(180%) blur(16px);border:1px solid var(--glass-border);box-shadow:0 8px 28px rgba(15,23,42,.15)}
                .reveal{opacity:0;transform:translateY(24px);transition:opacity .8s ease,transform .8s ease}
                .reveal.show{opacity:1;transform:none}
                .bubbles{position:fixed;inset:0;pointer-events:none;z-index:5}
                .bubble{position:absolute;bottom:-100px;background:radial-gradient(circle at 30% 30%,rgba(255,255,255,.9),rgba(255,255,255,.3));border:1px solid rgba(255,255,255,.4);border-radius:9999px;filter:blur(.5px);animation:floatUp linear infinite}
                @keyframes floatUp{0%{transform:translateY(0) translateX(0);opacity:0}10%{opacity:.7}100%{transform:translateY(-110vh) translateX(30px);opacity:0}}
                .carousel-container{position:relative;width:100%;max-width:1100px;height:520px;overflow:hidden;border-radius:1rem;border:1px solid rgba(255,255,255,.25);box-shadow:0 10px 25px rgba(15,23,42,.15);margin-bottom:4rem}
                .carousel-slide{width:100%;height:100%;position:absolute;top:0;left:0;opacity:0;transition:opacity 1s ease-in-out;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;padding:2rem;background-size:cover;background-position:center;color:white}
                .carousel-slide::before{content:'';position:absolute;top:0;left:0;width:100%;height:100%;background-color:rgba(10,30,70,.35);z-index:1}
                .carousel-slide.active{opacity:1}
                .slide-content{position:relative;z-index:2;max-width:80%;padding:1.5rem 2.5rem;border-radius:.75rem}
                .slide-content h2{font-size:2.5rem;font-weight:700;margin-bottom:1rem;text-shadow:0 2px 4px rgba(0,0,0,.5)}
                .slide-content p{font-size:1.2rem;line-height:1.6;text-shadow:0 1px 3px rgba(0,0,0,.5)}
                .nav-dots{position:absolute;bottom:1.5rem;left:50%;transform:translateX(-50%);display:flex;gap:.75rem;z-index:10}
                .dot{width:12px;height:12px;background-color:rgba(255,255,255,.6);border-radius:50%;cursor:pointer;transition:background-color .3s ease,transform .3s ease}
                .dot.active{background-color:white;transform:scale(1.2)}
                @media (max-width:768px){.carousel-container{height:360px}.slide-content h2{font-size:2rem}.slide-content p{font-size:1rem}}
                @media (max-width:480px){.carousel-container{height:300px}.slide-content{padding:1rem 1.5rem}.slide-content h2{font-size:1.75rem}.slide-content p{font-size:.95rem}}
            </style>
        </head>
        <body class='flex flex-col min-h-screen font-sans text-gray-800'>
            <div id='loader' class='loader-overlay'>
                <div class='spinner'></div>
            </div>
            <div class='bubbles'></div>
            <header class='ribbon'>
                <div class='max-w-6xl mx-auto flex justify-between items-center h-16 px-6'>
                    <a href='index.php?page=home' class='flex items-center text-blue-700 hover:text-blue-900 transition duration-300'>
                        <i class='fas fa-tint text-3xl mr-2'></i>
                        <span class='text-2xl font-bold'>AquaBill</span>
                    </a>
                    <nav class='flex items-center space-x-6'>";
                        if ($auth->isLoggedIn()) {
                            echo "<span class='text-gray-700'>Hello, <span class='font-semibold text-blue-700'>$username</span></span>
                            <span class='px-3 py-1 bg-blue-600 text-white rounded-full text-sm font-semibold'>".ucfirst($role)."</span>
                            <a href='index.php?page=logout' class='text-red-600 hover:text-red-800 transition duration-300 flex items-center'>
                                <i class='fas fa-sign-out-alt mr-1'></i> Logout
                            </a>";
                        } else {
                            echo "<a href='index.php?page=login' class='text-blue-600 hover:text-blue-800 transition duration-300 flex items-center'>
                                <i class='fas fa-sign-in-alt mr-1'></i> Login
                            </a>
                            <a href='index.php?page=register' class='px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-300 flex items-center'>
                                <i class='fas fa-user-plus mr-1'></i> Register
                            </a>";
                        }
                    echo "</nav>
                </div>
            </header>

            <!-- Main Content -->
            <main class='flex-grow container mx-auto px-4 pt-36 md:pt-40'>
                <!-- Hero Section -->
                <section class='text-center mb-12 reveal'>
                    <h1 class='text-5xl md:text-6xl font-extrabold bg-gradient-to-r from-sky-700 to-sky-500 bg-clip-text text-transparent mb-4'>Smart Water Billing Solutions</h1>
                    <p class='text-xl text-gray-600 max-w-3xl mx-auto mb-8'>
                        Streamline your water utility management with our comprehensive billing system designed for efficiency and transparency.
                    </p>";
                    if (!$auth->isLoggedIn()) {
                        echo "<div class='flex justify-center gap-4'>
                            <a href='index.php?page=login' class='px-8 py-4 bg-blue-600 text-white rounded-lg shadow-lg hover:bg-blue-700 transition duration-300 text-lg font-semibold flex items-center justify-center'>
                                <i class='fas fa-sign-in-alt mr-2'></i> Login
                            </a>
                            <a href='index.php?page=register' class='px-8 py-4 border-2 border-blue-600 text-blue-600 rounded-lg shadow-lg hover:bg-blue-50 transition duration-300 text-lg font-semibold flex items-center justify-center'>
                                <i class='fas fa-user-plus mr-2'></i> Register
                            </a>
                        </div>";
                    }
                echo "</section>

                <!-- Carousel Section -->
                <div class='carousel-container mx-auto reveal'>
                    <div class='carousel-slide active' style='background-image: url(\"../images/Comprehensive Water Management.jpg\");'>
                        <div class='slide-content glass'>
                            <h2>Comprehensive Water Management</h2>
                            <p>End-to-end solutions from meter reading to billing and customer management.</p>
                        </div>
                    </div>
                    <div class='carousel-slide' style='background-image: url(\"../images/Real-Time Monitoring.jpg\");'>
                        <div class='slide-content glass'>
                            <h2>Real-Time Monitoring</h2>
                            <p>Track consumption in real-time and provide up-to-date information.</p>
                        </div>
                    </div>
                    <div class='carousel-slide' style='background-image: url(\"../images/Automated Billing.png\");'>
                        <div class='slide-content glass'>
                            <h2>Automated Billing</h2>
                            <p>Reduce errors and save time with automated calculations.</p>
                        </div>
                    </div>
                    <div class='carousel-slide' style='background-image: url(\"../images/Customer Self-Service.jpg\");'>
                        <div class='slide-content glass'>
                            <h2>Customer Self-Service</h2>
                            <p>Enable payments, consumption tracking, and service requests.</p>
                        </div>
                    </div>
                    <div class='carousel-slide' style='background-image: url(\"../images/Analytics & Reporting_2.png\"); background-position: center top;'>
                        <div class='slide-content glass'>
                            <h2>Analytics & Reporting</h2>
                            <p>Insights that help drive data-informed decisions.</p>
                        </div>
                    </div>
                    <div class='nav-dots'></div>
                </div>";

                if ($auth->isLoggedIn()) {
                    echo "<section class='bg-white p-8 rounded-xl shadow-lg text-center max-w-2xl mx-auto mt-12'>
                        <h3 class='text-3xl font-bold text-blue-700 mb-4'>Your ".ucfirst($role)." Dashboard</h3>
                        <p class='text-lg text-gray-600 mb-6'>Access your personalized dashboard to manage your ".($role == 'admin' ? 'utility operations' : ($role == 'client' ? 'water services' : 'meter readings')).".</p>
                        <div class='flex justify-center gap-4'>";
                            if ($role == 'admin') {
                                echo "<a href='index.php?page=admin_dashboard' class='px-6 py-3 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition duration-300 flex items-center'>
                                    <i class='fas fa-cogs mr-2'></i> Admin Dashboard
                                </a>";
                            } elseif ($role == 'client') {
                                echo "<a href='index.php?page=client_dashboard' class='px-6 py-3 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 transition duration-300 flex items-center'>
                                    <i class='fas fa-user-circle mr-2'></i> Client Dashboard
                                </a>";
                            } elseif ($role == 'collector') {
                                echo "<a href='index.php?page=collector_dashboard' class='px-6 py-3 bg-purple-600 text-white rounded-lg shadow-md hover:bg-purple-700 transition duration-300 flex items-center'>
                                    <i class='fas fa-truck-moving mr-2'></i> Collector Dashboard
                                </a>";
                            }
                            // Add links for Commercial Manager and Finance Manager dashboards
                            elseif ($role == 'commercial_manager') {
                                echo "<a href='index.php?page=commercial_manager_dashboard' class='px-6 py-3 bg-orange-600 text-white rounded-lg shadow-md hover:bg-orange-700 transition duration-300 flex items-center'>
                                    <i class='fas fa-briefcase mr-2'></i> Commercial Dashboard
                                </a>";
                            } elseif ($role == 'finance_manager') {
                                echo "<a href='index.php?page=finance_manager_dashboard' class='px-6 py-3 bg-teal-600 text-white rounded-lg shadow-md hover:bg-teal-700 transition duration-300 flex items-center'>
                                    <i class='fas fa-chart-pie mr-2'></i> Finance Dashboard
                                </a>";
                            } elseif ($role == 'meter_reader') {
                                echo "<a href='index.php?page=meter_reader_dashboard' class='px-6 py-3 bg-indigo-600 text-white rounded-lg shadow-md hover:bg-indigo-700 transition duration-300 flex items-center'>
                                    <i class='fas fa-tachometer-alt mr-2'></i> Meter Reader Dashboard
                                </a>";
                            }
                        echo "</div>
                    </section>";
                }

                echo "<section class='mt-16 reveal'>
                    <div class='text-center mb-10'>
                        <h2 class='text-4xl font-bold text-sky-800 mb-4'>Scope</h2>
                        <p class='text-lg text-slate-600 max-w-3xl mx-auto'>Covers client onboarding, meter lifecycle, consumption capture, billing, payments, and reporting.</p>
                    </div>
                    <div class='grid grid-cols-1 md:grid-cols-3 gap-6'>
                        <div class='glass p-6 rounded-xl'>
                            <h3 class='text-xl font-semibold text-slate-800 mb-2'>Client Management</h3>
                            <p class='text-slate-700'>Registration, profiles, plans, and notifications.</p>
                        </div>
                        <div class='glass p-6 rounded-xl'>
                            <h3 class='text-xl font-semibold text-slate-800 mb-2'>Meter Operations</h3>
                            <p class='text-slate-700'>Installation scheduling, readings, and maintenance.</p>
                        </div>
                        <div class='glass p-6 rounded-xl'>
                            <h3 class='text-xl font-semibold text-slate-800 mb-2'>Billing & Payments</h3>
                            <p class='text-slate-700'>Automated bill runs, receipts, and reconciliation.</p>
                        </div>
                    </div>
                </section>

                <section class='mt-16 grid grid-cols-1 md:grid-cols-2 gap-8 reveal'>
                    <div class='glass p-8 rounded-xl'>
                        <h2 class='text-3xl font-bold text-sky-800 mb-3'>Why</h2>
                        <p class='text-slate-700'>Deliver transparency and efficiency for utilities and customers with accurate metering, fair billing, and self-service.</p>
                    </div>
                    <div class='glass p-8 rounded-xl'>
                        <h2 class='text-3xl font-bold text-sky-800 mb-3'>How It Helps</h2>
                        <p class='text-slate-700'>Automates workflows, reduces errors, and provides actionable insights through analytics and notifications.</p>
                    </div>
                </section>

                <section class='mt-16 reveal'>
                    <div class='text-center mb-10'>
                        <h2 class='text-4xl font-bold text-sky-800 mb-4'>Modules</h2>
                        <p class='text-lg text-slate-600 max-w-3xl mx-auto'>Role-based access for each team.</p>
                    </div>
                    <div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8'>
                        <div class='p-6 rounded-xl shadow-lg bg-gradient-to-br from-red-600 to-gray-900 text-white'>
                            <div class='text-2xl mb-2'><i class='fas fa-cogs'></i></div>
                            <h3 class='text-xl font-semibold mb-2'>Admin</h3>
                            <p class='opacity-90'>Users, services, tariffs, and governance.</p>
                        </div>
                        <div class='p-6 rounded-xl shadow-lg bg-gradient-to-br from-sky-600 via-sky-300 to-white text-slate-900'>
                            <div class='text-2xl mb-2 text-sky-800'><i class='fas fa-user-circle'></i></div>
                            <h3 class='text-xl font-semibold mb-2'>Client</h3>
                            <p class='opacity-90'>Bills, payments, consumption, and requests.</p>
                        </div>
                        <div class='p-6 rounded-xl shadow-lg bg-gradient-to-br from-indigo-600 to-indigo-900 text-white'>
                            <div class='text-2xl mb-2'><i class='fas fa-tachometer-alt'></i></div>
                            <h3 class='text-xl font-semibold mb-2'>Meter Reader</h3>
                            <p class='opacity-90'>Readings capture, GPS updates, and installations.</p>
                        </div>
                        <div class='p-6 rounded-xl shadow-lg bg-gradient-to-br from-green-600 via-green-300 to-white text-slate-900'>
                            <div class='text-2xl mb-2 text-green-800'><i class='fas fa-truck-moving'></i></div>
                            <h3 class='text-xl font-semibold mb-2'>Collector</h3>
                            <p class='opacity-90'>Meter support and field operations.</p>
                        </div>
                        <div class='p-6 rounded-xl shadow-lg bg-gradient-to-br from-orange-600 to-orange-900 text-white'>
                            <div class='text-2xl mb-2'><i class='fas fa-briefcase'></i></div>
                            <h3 class='text-xl font-semibold mb-2'>Commercial Manager</h3>
                            <p class='opacity-90'>Applications review and service workflows.</p>
                        </div>
                        <div class='p-6 rounded-xl shadow-lg bg-gradient-to-br from-teal-600 to-teal-900 text-white'>
                            <div class='text-2xl mb-2'><i class='fas fa-chart-pie'></i></div>
                            <h3 class='text-xl font-semibold mb-2'>Finance Manager</h3>
                            <p class='opacity-90'>Transactions, receipts, verification, and reports.</p>
                        </div>
                    </div>
                </section>";

                echo "<!-- Features Section -->
                <section class='mt-16'>
                    <div class='text-center mb-10'>
                        <h2 class='text-4xl font-bold text-blue-800 mb-4'>Key Features</h2>
                        <p class='text-lg text-gray-600 max-w-3xl mx-auto'>
                            Discover how our water billing system can transform your utility management.
                        </p>
                    </div>
                    <div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8'>
                        <div class='glass p-6 rounded-xl text-center transform hover:scale-105 transition duration-300'>
                            <div class='w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl'>
                                <i class='fas fa-tachometer-alt'></i>
                            </div>
                            <h3 class='xl:text-xl font-semibold text-gray-800 mb-2'>Real-Time Monitoring</h3>
                            <p class='text-gray-600'>Track water consumption patterns and detect anomalies in real-time to prevent water loss and improve efficiency.</p>
                        </div>
                        <div class='glass p-6 rounded-xl text-center transform hover:scale-105 transition duration-300'>
                            <div class='w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl'>
                                <i class='fas fa-file-invoice-dollar'></i>
                            </div>
                            <h3 class='xl:text-xl font-semibold text-gray-800 mb-2'>Automated Billing</h3>
                            <p class='text-gray-600'>Generate accurate bills automatically based on consumption data, tariff plans, and billing cycles.</p>
                        </div>
                        <div class='glass p-6 rounded-xl text-center transform hover:scale-105 transition duration-300'>
                            <div class='w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl'>
                                <i class='fas fa-mobile-alt'></i>
                            </div>
                            <h3 class='xl:text-xl font-semibold text-gray-800 mb-2'>Mobile Payments</h3>
                            <p class='text-gray-600'>Customers can pay bills conveniently through multiple payment channels including mobile money and online banking.</p>
                        </div>
                        <div class='glass p-6 rounded-xl text-center transform hover:scale-105 transition duration-300'>
                            <div class='w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl'>
                                <i class='fas fa-chart-line'></i>
                            </div>
                            <h3 class='xl:text-xl font-semibold text-gray-800 mb-2'>Analytics Dashboard</h3>
                            <p class='text-gray-600'>Comprehensive reports and visualizations to help you understand consumption patterns and revenue streams.</p>
                        </div>
                        <div class='glass p-6 rounded-xl text-center transform hover:scale-105 transition duration-300'>
                            <div class='w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl'>
                                <i class='fas fa-tools'></i>
                            </div>
                            <h3 class='xl:text-xl font-semibold text-gray-800 mb-2'>Service Management</h3>
                            <p class='text-gray-600'>Efficiently manage service requests, meter installations, and maintenance activities.</p>
                        </div>
                        <div class='glass p-6 rounded-xl text-center transform hover:scale-105 transition duration-300'>
                            <div class='w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl'>
                                <i class='fas fa-bell'></i>
                            </div>
                            <h3 class='xl:text-xl font-semibold text-gray-800 mb-2'>Alerts & Notifications</h3>
                            <p class='text-gray-600'>Automated alerts for bill payments, leak detection, and important announcements.</p>
                        </div>
                    </div>
                </section>
            </main>

            <!-- Footer -->
            <footer class='glass text-slate-100 py-8 px-6 md:px-8 mt-16'>
                <div class='max-w-6xl mx-auto text-center'>
                    <div class='flex justify-center space-x-6 mb-4'>
                        <a href='#' class='text-slate-800 hover:text-sky-700 transition duration-300'>About Us</a>
                        <a href='#' class='text-slate-800 hover:text-sky-700 transition duration-300'>Contact</a>
                        <a href='#' class='text-slate-800 hover:text-sky-700 transition duration-300'>Privacy Policy</a>
                        <a href='#' class='text-slate-800 hover:text-sky-700 transition duration-300'>Terms of Service</a>
                        <a href='#' class='text-slate-800 hover:text-sky-700 transition duration-300'>FAQ</a>
                    </div>
                    <p class='text-slate-700 text-sm'>&copy; ".date('Y')." AquaBill Water Billing System. All rights reserved.</p>
                    <div class='text-slate-700 text-sm font-medium mt-1'>" . htmlspecialchars($__ot) . "</div>
                </div>
            </footer>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const slides = document.querySelectorAll('.carousel-slide');
                    const dotsContainer = document.querySelector('.nav-dots');
                    let currentSlide = 0;
                    let slideInterval;
                    const intervalTime = 6000; // 6 seconds

                    // Create navigation dots
                    function createDots() {
                        slides.forEach((_, index) => {
                            const dot = document.createElement('div');
                            dot.classList.add('dot');
                            dot.addEventListener('click', () => {
                                goToSlide(index);
                            });
                            dotsContainer.appendChild(dot);
                        });
                        updateDots();
                    }

                    // Go to specific slide
                    function goToSlide(index) {
                        slides.forEach(slide => slide.classList.remove('active'));
                        currentSlide = index;
                        slides[currentSlide].classList.add('active');
                        updateDots();
                        resetInterval();
                    }

                    // Update dot indicators
                    function updateDots() {
                        const dots = document.querySelectorAll('.dot');
                        dots.forEach((dot, index) => {
                            dot.classList.remove('active');
                            if (index === currentSlide) {
                                dot.classList.add('active');
                            }
                        });
                    }

                    // Next slide
                    function nextSlide() {
                        goToSlide((currentSlide + 1) % slides.length);
                    }

                    // Start autoplay
                    function startInterval() {
                        slideInterval = setInterval(nextSlide, intervalTime);
                    }

                    // Reset interval
                    function resetInterval() {
                        clearInterval(slideInterval);
                        startInterval();
                    }

                    // Initialize
                    createDots();
                    startInterval();

                    // Pause on hover
                    const carousel = document.querySelector('.carousel-container');
                    carousel.addEventListener('mouseenter', () => {
                        clearInterval(slideInterval);
                    });
                    carousel.addEventListener('mouseleave', startInterval);
                    const observer=new IntersectionObserver((entries)=>{entries.forEach((entry)=>{if(entry.isIntersecting){entry.target.classList.add('show')}})},{threshold:0.15});
                    document.querySelectorAll('.reveal').forEach(el=>observer.observe(el));
                    const bubbles=document.querySelector('.bubbles');
                    function createBubbles(n){
                        for(let i=0;i<n;i++){
                            const b=document.createElement('div');
                            b.className='bubble';
                            const size=8+Math.random()*22;
                            const dur=12+Math.random()*18;
                            const delay=Math.random()*8;
                            b.style.width=size+'px';
                            b.style.height=size+'px';
                            b.style.left=Math.random()*100+'%';
                            b.style.animationDuration=dur+'s';
                            b.style.animationDelay=delay+'s';
                            b.style.opacity=0.6;
                            bubbles.appendChild(b);
                            setTimeout(()=>{b.remove()}, (dur+delay)*1000*1.1);
                        }
                    }
                    createBubbles(18);
                    setInterval(()=>createBubbles(6),8000);
window.addEventListener('load',()=>{const l=document.getElementById('loader');if(l){setTimeout(()=>{l.classList.add('loader-hidden');setTimeout(()=>{try{l.remove()}catch(e){}},600);},1500)}});
                });
            </script>
        </body>
        </html>";
        break;

    case 'login':
        $authController->login();
        break;

    case 'register':
        $authController->register();
        break;

    case 'logout':
        $authController->logout();
        break;

    // Admin Routes
    case 'admin_dashboard':
        $adminController->dashboard();
        break;
    case 'admin_manage_billing_plans':
        $adminController->manageBillingPlans();
        break;
    case 'admin_manage_users':
        $adminController->manageUsers();
        break;
    case 'admin_manage_services':
        $adminController->manageServices();
        break;
    case 'admin_manage_requests':
        $adminController->manageRequests();
        break;
    case 'admin_manage_meters':
        $adminController->manageMeters();
        break;
    case 'admin_manage_client_plans':
        $adminController->manageClientPlans();
        break;
    case 'admin_reports':
        $adminController->reports();
        break;

    // Client Routes (NEW)
    case 'client_dashboard':
        $clientController->dashboard();
        break;
    case 'client_my_plans':
        $clientController->myPlans();
        break;
    case 'client_apply_service':
        $clientController->applyService();
        break;
    case 'client_payments':
        $clientController->payments(); // This now handles payment processing too
        break;
    case 'client_support':
        $clientController->support();
        break;
    case 'client_consumption':
        $clientController->consumption();
        break;
    case 'client_profile':
        $clientController->profile();
        break;
    case 'client_reviews':
        $clientController->reviews();
        break;
        
    case 'client_meters':
        $clientController->meters();
        break;
        
    case 'client_apply_meter':
        $clientController->applyMeter();
        break;
    case 'client_add_meter':
        $clientController->addClientMeter();
        break;

    // Meter Reader Routes
    case 'meter_reader_dashboard':
        $meterReaderController->dashboard();
        break;
    case 'meter_reader_record_reading':
        $meterReaderController->recordReading();
        break;
    case 'meter_reader_mobile_reading':
        $meterReaderController->mobileReading();
        break;
    case 'meter_reader_update_service':
        $meterReaderController->updateServiceStatus();
        break;
    case 'meter_reader_records':
        $meterReaderController->records();
        break;
    case 'meter_reader_view_meter_history':
        $meterReaderController->viewMeterHistory();
        break;
    case 'meter_reader_profile':
        $meterReaderController->profile();
        break;
    case 'meter_reader_update_gps_location':
        $meterReaderController->updateMeterGpsLocation();
        break;
    case 'meter_reader_installations':
        $meterReaderController->installations();
        break;
        
    // Collector Routes
    case 'collector_dashboard':
        $collectorController->dashboard();
        break;
    case 'collector_record_reading':
        $collectorController->recordReading();
        break;
    case 'collector_update_service':
        $collectorController->updateServiceStatus();
        break;
    case 'collector_records':
        $collectorController->records();
        break;
    case 'collector_profile':
        $collectorController->profile();
        break;
    case 'collector_installations':
        $collectorController->installations();
        break;

    // NEW: Commercial Manager Routes
    case 'commercial_manager_dashboard':
        $commercialManagerController->dashboard();
        break;
    case 'commercial_manager_manage_meters':
        $commercialManagerController->manageMeters();
        break;
    case 'commercial_manager_review_applications':
        $commercialManagerController->reviewApplications();
        break;
    case 'commercial_manager_profile':
        $commercialManagerController->profile();
        break;
    case 'commercial_manager_reports':
        $commercialManagerController->reports();
        break;

    // Finance Manager Routes
    case 'finance_manager_dashboard':
        $financeManagerController->dashboard();
        break;
    case 'finance_manager_transactions':
        $financeManagerController->manageTransactions();
        break;
    case 'admin_transactions':
        $financeManagerController->manageTransactions();
        break;
    case 'finance_manager_transaction_details':
        $financeManagerController->transactionDetails();
        break;
    case 'admin_transaction_details':
        $financeManagerController->transactionDetails();
        break;
    case 'finance_manager_verify_transaction':
        $financeManagerController->verifyTransaction();
        break;
    case 'finance_manager_flag_transaction':
        $financeManagerController->flagTransaction();
        break;
    case 'finance_manager_unflag_transaction':
        $financeManagerController->unflagTransaction();
        break;
    case 'finance_manager_add_transaction_note':
        $financeManagerController->addTransactionNote();
        break;
    case 'finance_manager_generate_receipt':
        $financeManagerController->generateReceipt();
        break;
    case 'finance_manager_verify_bill':
        $financeManagerController->verifyBill();
        break;
    case 'client_generate_receipt':
        $clientController->generateReceipt();
        break;
    case 'finance_manager_reports':
        $financeManagerController->generateReports();
        break;
    case 'finance_manager_billing_plans':
        $financeManagerController->manageBillingPlans();
        break;
    case 'finance_manager_profile':
        $financeManagerController->profile();
        break;
        
    // Billing Routes
    case 'billing_dashboard':
        $billingController->dashboard();
        break;
    case 'generate_bills':
        $billingController->generateBills();
        break;
    case 'view_bills':
        $billingController->viewBills();
        break;
    case 'view_bill_details':
        $billingController->viewBillDetails();
        break;
    case 'record_payment':
        $billingController->recordPayment();
        break;
    case 'generate_single_bill':
        $billingController->generateSingleBill();
        break;
    case 'generate_single_bill_now':
        $billingController->generateSingleBillNow();
        break;
    case 'billing_get_readings_for_meter':
        $billingController->getReadingsForMeterJson();
        break;
    case 'billing_reports':
        $billingController->billingReports();
        break;
    case 'get_readings_for_meter':
        $billingController->getReadingsForMeter();
        break;
    case 'billing_store_bill_pdf':
        $billingController->storeBillPdf();
        break;
    case 'billing_store_bill_image':
        $billingController->storeBillImage();
        break;
    case 'billing_send_bill_to_client':
        $billingController->sendBillToClient();
        break;
    case 'client_bill_update_status':
        $billingController->clientUpdateBillStatus();
        break;
    case 'client_store_bill_image':
        $billingController->clientStoreBillImage();
        break;
        
    // Client Billing Routes
    case 'client_billing_dashboard':
        $billingController->clientBillingDashboard();
        break;
    case 'client_view_bills':
        $billingController->clientViewBills();
        break;
    case 'client_download_bill':
        $clientController->downloadBill();
        break;
    case 'client_bill_details':
        $billingController->clientBillDetails();
        break;
    case 'client_pay_bill':
        $billingController->clientPayBill();
        break;
    case 'mpesa_callback':
        $billingController->mpesaCallback();
        break;


    default:
        // Animated 404 Page Not Found for Water Billing System
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8' />
            <meta name='viewport' content='width=device-width, initial-scale=1' />
            <title>404 Not Found - Water Billing System</title>
            <link rel='stylesheet' href='css/style.css' />
            <style>
                body, html {
                    height: 100%;
                    margin: 0;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(to bottom, #a0d8ef, #0077be);
                    overflow: hidden;
                    color: #fff;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    flex-direction: column;
                    text-align: center;
                }
                .water-container {
                    position: relative;
                    width: 300px;
                    height: 300px;
                    margin-bottom: 30px;
                }
                .water {
                    position: absolute;
                    bottom: 0;
                    width: 100%;
                    height: 100%;
                    background: #00aaff;
                    border-radius: 50% 50% 45% 45% / 60% 60% 40% 40%;
                    animation: wave 4s infinite linear;
                    opacity: 0.6;
                    filter: drop-shadow(0 0 10px #00cfff);
                }
                .water:nth-child(2) {
                    background: #0099dd;
                    animation-delay: -2s;
                    opacity: 0.4;
                }
                .water:nth-child(3) {
                    background: #0088cc;
                    animation-delay: -1s;
                    opacity: 0.3;
                }
                @keyframes wave {
                    0% {
                        transform: translateX(0) translateY(0);
                    }
                    50% {
                        transform: translateX(20px) translateY(-10px);
                    }
                    100% {
                        transform: translateX(0) translateY(0);
                    }
                }
                h1 {
                    font-size: 3rem;
                    margin: 0 0 10px 0;
                    text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
                }
                p {
                    font-size: 1.2rem;
                    margin: 0 0 20px 0;
                    text-shadow: 1px 1px 5px rgba(0,0,0,0.3);
                }
                a.home-link {
                    display: inline-block;
                    padding: 12px 25px;
                    background: #005f99;
                    color: #fff;
                    text-decoration: none;
                    font-weight: bold;
                    border-radius: 25px;
                    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
                    transition: background 0.3s ease;
                }
                a.home-link:hover {
                    background: #004466;
                }
            </style>
        </head>
        <body>
            <div class='water-container' aria-label='Animated water waves'>
                <div class='water'></div>
                <div class='water'></div>
                <div class='water'></div>
            </div>
            <h1>404 - Page Not Found</h1>
            <p>Oops! The page you are looking for does not exist in the Water Billing System.</p>
            <a href='index.php?page=home' class='home-link'>Go to Home</a>
        </body>
        </html>";
        break;
        
    // API Routes for real-time dashboard data
    case 'api_admin_dashboard_data':
        $apiController->getAdminDashboardData();
        break;
    case 'api_meter_reader_dashboard_data':
        $apiController->getMeterReaderDashboardData();
        break;
    case 'api_finance_manager_dashboard_data':
        $apiController->getFinanceManagerDashboardData();
        break;
    case 'api_commercial_manager_dashboard_data':
        $apiController->getCommercialManagerDashboardData();
        break;
    case 'api_client_dashboard_data':
        $apiController->getClientDashboardData();
        break;
    case 'api_client_notifications':
        $apiController->getClientNotifications();
        break;
    case 'api_commercial_manager_notifications':
        $apiController->getCommercialManagerNotifications();
        break;
    case 'api_finance_manager_notifications':
        $apiController->getFinanceManagerNotifications();
        break;
    case 'api_admin_notifications':
        $apiController->getAdminNotifications();
        break;
}

// Close the main mysqli connection when the script finishes executing.
$conn->close();
?>
