<?php
// app/controllers/ApiController.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/MeterReading.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Bill.php';
require_once __DIR__ . '/../models/MeterApplication.php';
require_once __DIR__ . '/../models/ServiceRequest.php';
require_once __DIR__ . '/../models/PlanUpgradePrompt.php';
require_once __DIR__ . '/../models/Meter.php';

class ApiController {
    private Database $db;
    private Auth $auth;
    private User $userModel;
    private Client $clientModel;
    private MeterReading $meterReadingModel;
    private Payment $paymentModel;
    private Bill $billModel;
    private MeterApplication $meterApplicationModel;
    private ServiceRequest $serviceRequestModel;
    private PlanUpgradePrompt $planUpgradePromptModel;
    private Meter $meterModel;

    public function __construct(Database $database_instance, Auth $auth_instance) {
        $this->db = $database_instance;
        $this->auth = $auth_instance;
        $this->userModel = new User($database_instance);
        $this->clientModel = new Client($database_instance);
        $this->meterReadingModel = new MeterReading($database_instance);
        $this->paymentModel = new Payment($database_instance);
        $this->billModel = new Bill($database_instance);
        $this->meterApplicationModel = new MeterApplication($database_instance);
        $this->serviceRequestModel = new ServiceRequest($database_instance);
        $this->planUpgradePromptModel = new PlanUpgradePrompt($database_instance);
        $this->meterModel = new Meter($database_instance);
    }

    /**
     * Returns data for the admin dashboard
     */
    public function getAdminDashboardData() {
        // Ensure user is authenticated and has admin role
        if (!$this->auth->isLoggedIn() || !$this->auth->hasRole('admin')) {
            $this->sendJsonResponse(['error' => 'Unauthorized access'], 401);
            return;
        }

        // Get counts for dashboard widgets
        $totalClients = $this->clientModel->getTotalClientCount();
        $totalUsers = $this->userModel->getTotalUserCount();
        $totalPayments = $this->paymentModel->getTotalPaymentsAmount();
        $pendingBills = count($this->billModel->getBillsByStatus('unpaid'));

        // Get recent activities
        $recentPayments = $this->paymentModel->getRecentPayments(5);
        $recentReadings = $this->meterReadingModel->getRecentReadings(5);

        $data = [
            'counts' => [
                'total_clients' => $totalClients,
                'total_users' => $totalUsers,
                'total_payments' => $totalPayments,
                'pending_bills' => $pendingBills
            ],
            'recent_activities' => [
                'payments' => $recentPayments,
                'readings' => $recentReadings
            ]
        ];

        $this->sendJsonResponse($data);
    }

    /**
     * Returns data for the meter reader dashboard
     */
    public function getMeterReaderDashboardData() {
        // Ensure user is authenticated and has meter_reader role
        if (!$this->auth->isLoggedIn() || !$this->auth->hasRole('meter_reader')) {
            $this->sendJsonResponse(['error' => 'Unauthorized access'], 401);
            return;
        }

        $userId = $_SESSION['user_id'];
        
        // Get assigned clients
        $assignedClients = $this->clientModel->getClientsByAssignedUser($userId);
        
        // Get recent readings by this meter reader
        $recentReadings = $this->meterReadingModel->getRecentReadingsByCollector($userId, 5);
        
        // Get pending readings (clients that need readings)
        $pendingReadings = $this->clientModel->getClientsNeedingReadings();
        
        $data = [
            'counts' => [
                'assigned_clients' => count($assignedClients),
                'pending_readings' => count($pendingReadings)
            ],
            'recent_readings' => $recentReadings,
            'pending_readings' => $pendingReadings
        ];

        $this->sendJsonResponse($data);
    }

    /**
     * Returns data for the finance manager dashboard
     */
    public function getFinanceManagerDashboardData() {
        // Ensure user is authenticated and has finance_manager role
        if (!$this->auth->isLoggedIn() || !$this->auth->hasRole('finance_manager')) {
            $this->sendJsonResponse(['error' => 'Unauthorized access'], 401);
            return;
        }
        
        // Get payment statistics
        $totalPayments = $this->paymentModel->getTotalPaymentsAmount();
        $monthlyPayments = $this->paymentModel->getMonthlyPaymentsTotal();
        $unpaidBills = $this->billModel->getBillsByStatus('unpaid');
        $overdueBills = $this->billModel->getOverdueBills();
        
        // Get recent payments
        $recentPayments = $this->paymentModel->getRecentPayments(5);
        
        $data = [
            'stats' => [
                'total_payments' => $totalPayments,
                'monthly_payments' => $monthlyPayments,
                'unpaid_bills' => count($unpaidBills),
                'overdue_bills' => count($overdueBills)
            ],
            'recent_payments' => $recentPayments,
            'overdue_bills' => array_slice($overdueBills, 0, 5) // Get first 5 overdue bills
        ];

        $this->sendJsonResponse($data);
    }

    /**
     * Returns data for the commercial manager dashboard
     */
    public function getCommercialManagerDashboardData() {
        // Ensure user is authenticated and has commercial_manager role
        if (!$this->auth->isLoggedIn() || !$this->auth->hasRole('commercial_manager')) {
            $this->sendJsonResponse(['error' => 'Unauthorized access'], 401);
            return;
        }
        
        // Get client statistics
        $totalClients = $this->clientModel->getTotalClientCount();
        $activeClients = $this->clientModel->getClientCountByStatus('active');
        $inactiveClients = $this->clientModel->getClientCountByStatus('inactive');
        
        // Get recent clients
        $recentClients = $this->clientModel->getRecentClients(5);
        
        $data = [
            'stats' => [
                'total_clients' => $totalClients,
                'active_clients' => $activeClients,
                'inactive_clients' => $inactiveClients
            ],
            'recent_clients' => $recentClients
        ];

        $this->sendJsonResponse($data);
    }

    /**
     * Returns data for the client dashboard
     */
    public function getClientDashboardData() {
        // Ensure user is authenticated and has client role
        if (!$this->auth->isLoggedIn() || !$this->auth->hasRole('client')) {
            $this->sendJsonResponse(['error' => 'Unauthorized access'], 401);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $clientInfo = $this->clientModel->getClientByUserId($userId);
        
        if (!$clientInfo) {
            $this->sendJsonResponse(['error' => 'Client information not found'], 404);
            return;
        }
        
        $clientId = $clientInfo['id'];
        
        // Get billing information
        $unpaidBills = $this->billModel->getUnpaidBillsByClientId($clientId);
        $recentPayments = $this->paymentModel->getRecentPaymentsByClientId($clientId, 5);
        $recentReadings = $this->meterReadingModel->getRecentReadingsByClientId($clientId, 5);
        
        $data = [
            'client_info' => $clientInfo,
            'billing' => [
                'unpaid_bills' => $unpaidBills,
                'unpaid_total' => array_sum(array_column($unpaidBills, 'amount'))
            ],
            'recent_activities' => [
                'payments' => $recentPayments,
                'readings' => $recentReadings
            ]
        ];

        $this->sendJsonResponse($data);
    }

    public function getClientNotifications() {
        if (!$this->auth->isLoggedIn() || !$this->auth->hasRole('client')) {
            $this->sendJsonResponse(['error' => 'Unauthorized access'], 401);
            return;
        }
        $userId = $_SESSION['user_id'];
        $clientInfo = $this->clientModel->getClientByUserId($userId);
        if (!$clientInfo) {
            $this->sendJsonResponse(['error' => 'Client information not found'], 404);
            return;
        }
        $clientId = (int)$clientInfo['id'];

        $notifications = [];

        $meterApplications = $this->meterApplicationModel->getApplicationsByClientId($userId);
        $pendingApps = count(array_filter($meterApplications, function($app) { return strtolower($app['status'] ?? '') === 'pending'; }));
        if ($pendingApps > 0) {
            $notifications[] = [ 'title' => 'Your pending meter applications', 'count' => $pendingApps, 'url' => 'index.php?page=client_apply_meter' ];
        }

        $serviceRequests = $this->serviceRequestModel->getServiceRequestsByClientId($userId);
        $pendingSR = count(array_filter($serviceRequests, function($sr) { return strtolower($sr['status'] ?? '') === 'pending'; }));
        if ($pendingSR > 0) {
            $notifications[] = [ 'title' => 'Pending service requests', 'count' => $pendingSR, 'url' => 'index.php?page=client_apply_service' ];
        }

        $overdueBills = method_exists($this->billModel, 'getOverdueBillsByClientId') ? $this->billModel->getOverdueBillsByClientId($clientId) : [];
        if (!empty($overdueBills)) {
            $notifications[] = [ 'title' => 'Overdue bills needing payment', 'count' => count($overdueBills), 'url' => 'index.php?page=client_payments' ];
        }

        $upgradePending = $this->planUpgradePromptModel->getPendingByClient($userId);
        if (!empty($upgradePending)) {
            $notifications[] = [ 'title' => 'Plan upgrade suggestion', 'count' => 1, 'url' => 'index.php?page=client_my_plans' ];
        }
        $clientMeters = $this->meterModel->getMetersAssignedToClient($userId);
        $flaggedCount = 0;
        foreach ($clientMeters as $m) { if (strtolower(trim($m['status'] ?? '')) === 'flagged') { $flaggedCount++; } }
        if ($flaggedCount > 0) {
            $notifications[] = [ 'title' => 'Your flagged meters', 'count' => $flaggedCount, 'url' => 'index.php?page=client_meters' ];
        }

        $notificationsCount = 0; foreach ($notifications as $n) { $notificationsCount += (int)$n['count']; }

        $this->sendJsonResponse(['notifications' => $notifications, 'notificationsCount' => $notificationsCount]);
    }

    public function getCommercialManagerNotifications() {
        if (!$this->auth->isLoggedIn() || !$this->auth->hasRole('commercial_manager')) {
            $this->sendJsonResponse(['error' => 'Unauthorized access'], 401);
            return;
        }

        $notifications = [];

        $pendingApplications = $this->meterApplicationModel->getApplicationsByStatus('pending');
        $paCount = is_array($pendingApplications) ? count($pendingApplications) : 0;
        if ($paCount > 0) {
            $notifications[] = [ 'title' => 'Pending meter applications', 'count' => $paCount, 'url' => 'index.php?page=commercial_manager_review_applications' ];
        }

        $pendingRequests = $this->serviceRequestModel->getServiceRequests(null, 'pending');
        $prCount = is_array($pendingRequests) ? count($pendingRequests) : 0;
        if ($prCount > 0) {
            $notifications[] = [ 'title' => 'Pending service requests', 'count' => $prCount, 'url' => 'index.php?page=commercial_manager_service_requests' ];
        }

        $availableMeters = $this->meterModel->getAllMeters('available');
        $amCount = is_array($availableMeters) ? count($availableMeters) : 0;
        if ($amCount > 0) {
            $notifications[] = [ 'title' => 'Meters ready for assignment', 'count' => $amCount, 'url' => 'index.php?page=commercial_manager_manage_meters' ];
        }
        $flaggedMeters = $this->meterModel->getAllMeters('flagged');
        $fmCount = is_array($flaggedMeters) ? count($flaggedMeters) : 0;
        if ($fmCount > 0) {
            $notifications[] = [ 'title' => 'Flagged meters', 'count' => $fmCount, 'url' => 'index.php?page=commercial_manager_manage_meters' ];
        }

        $notificationsCount = 0; foreach ($notifications as $n) { $notificationsCount += (int)$n['count']; }

        $this->sendJsonResponse(['notifications' => $notifications, 'notificationsCount' => $notificationsCount]);
    }

    public function getFinanceManagerNotifications() {
        if (!$this->auth->isLoggedIn() || !$this->auth->hasRole('finance_manager')) {
            $this->sendJsonResponse(['error' => 'Unauthorized access'], 401);
            return;
        }

        $notifications = [];

        $allPayments = $this->paymentModel->getAllPayments();
        $pendingTx = 0; $flaggedTx = 0; $failedTx = 0;
        foreach ($allPayments as $p) {
            $st = strtolower(trim($p['status'] ?? ''));
            if ($st === 'pending') { $pendingTx++; }
            elseif ($st === 'flagged') { $flaggedTx++; }
            elseif ($st === 'failed') { $failedTx++; }
        }
        if ($pendingTx > 0) {
            $notifications[] = [ 'title' => 'Transactions pending verification', 'count' => $pendingTx, 'url' => 'index.php?page=finance_manager_transactions' ];
        }
        if ($flaggedTx > 0) {
            $notifications[] = [ 'title' => 'Flagged transactions', 'count' => $flaggedTx, 'url' => 'index.php?page=finance_manager_transactions' ];
        }
        if ($failedTx > 0) {
            $notifications[] = [ 'title' => 'Failed transactions', 'count' => $failedTx, 'url' => 'index.php?page=finance_manager_transactions' ];
        }

        $overdueBills = $this->billModel->getOverdueBills();
        if (is_array($overdueBills) && count($overdueBills) > 0) {
            $notifications[] = [ 'title' => 'Overdue bills', 'count' => count($overdueBills), 'url' => 'index.php?page=finance_manager_transactions' ];
        }

        $notificationsCount = 0; foreach ($notifications as $n) { $notificationsCount += (int)$n['count']; }

        $this->sendJsonResponse(['notifications' => $notifications, 'notificationsCount' => $notificationsCount]);
    }

    public function getAdminNotifications() {
        if (!$this->auth->isLoggedIn() || !$this->auth->hasRole('admin')) {
            $this->sendJsonResponse(['error' => 'Unauthorized access'], 401);
            return;
        }

        $groups = [];

        // Users group
        $usersItems = [];
        $allUsers = $this->userModel->getAllUsers();
        $today = date('Y-m-d');
        $newToday = 0;
        foreach ($allUsers as $u) {
            $created = isset($u['created_at']) ? substr((string)$u['created_at'], 0, 10) : null;
            if ($created === $today) { $newToday++; }
        }
        if ($newToday > 0) {
            $usersItems[] = [ 'title' => 'New registrations today', 'count' => $newToday, 'url' => 'index.php?page=admin_manage_users' ];
        }
        $requiredRoles = [ 'admin' => 1, 'commercial_manager' => 1, 'finance_manager' => 1, 'meter_reader' => 1, 'collector' => 1 ];
        $missingRoles = [];
        foreach ($requiredRoles as $role => $min) {
            $cnt = (int)$this->userModel->getUserCountByRole($role);
            if ($cnt < (int)$min) {
                $missingRoles[] = ucwords(str_replace('_',' ', $role));
            }
        }
        if (!empty($missingRoles)) {
            $usersItems[] = [
                'title' => 'System users missing: '.implode(', ', $missingRoles),
                'count' => count($missingRoles),
                'url' => 'index.php?page=admin_manage_users&highlight=' . strtolower(str_replace(' ', '_', $missingRoles[0])),
                'roles' => $missingRoles
            ];
        }
        // Applications awaiting admin verification
        $submittedApplications = $this->meterApplicationModel->getSubmittedToAdminApplications();
        $pendingVerifications = is_array($submittedApplications) ? count($submittedApplications) : 0;
        if ($pendingVerifications > 0) {
            $usersItems[] = [
                'title' => 'Applications Awaiting Verification',
                'count' => $pendingVerifications,
                'url' => 'index.php?page=admin_manage_meters'
            ];
        }
        $usersCount = 0; foreach ($usersItems as $it) { $usersCount += (int)$it['count']; }
        $groups[] = [ 'group' => 'Users', 'items' => $usersItems, 'count' => $usersCount ];

        // Bills group
        $billsItems = [];
        $overdueBills = $this->billModel->getOverdueBills();
        if (!empty($overdueBills)) {
            $billsItems[] = [ 'title' => 'Overdue bills', 'count' => count($overdueBills), 'url' => 'index.php?page=view_bills' ];
        }
        $pendingBillsCount = method_exists($this->billModel,'getPendingBillsCount') ? (int)$this->billModel->getPendingBillsCount() : 0;
        if ($pendingBillsCount > 0) {
            $billsItems[] = [ 'title' => 'Pending bills', 'count' => $pendingBillsCount, 'url' => 'index.php?page=view_bills' ];
        }
        $billsCount = 0; foreach ($billsItems as $it) { $billsCount += (int)$it['count']; }
        $groups[] = [ 'group' => 'Bills', 'items' => $billsItems, 'count' => $billsCount ];

        // Services group
        $servicesItems = [];
        $pendingSR = $this->serviceRequestModel->getServiceRequests(null, 'pending');
        if (!empty($pendingSR)) {
            $servicesItems[] = [ 'title' => 'Pending service requests', 'count' => count($pendingSR), 'url' => 'index.php?page=admin_manage_requests' ];
        }
        $servicedSR = $this->serviceRequestModel->getServiceRequests(null, 'serviced');
        if (!empty($servicedSR)) {
            $servicesItems[] = [ 'title' => 'Serviced requests awaiting confirmation', 'count' => count($servicedSR), 'url' => 'index.php?page=admin_manage_requests' ];
        }
        $servicesCount = 0; foreach ($servicesItems as $it) { $servicesCount += (int)$it['count']; }
        $groups[] = [ 'group' => 'Services', 'items' => $servicesItems, 'count' => $servicesCount ];

        // Payments group
        $paymentsItems = [];
        $allPayments = $this->paymentModel->getAllPayments();
        $pendingTx = 0; $flaggedTx = 0; $failedTx = 0;
        foreach ($allPayments as $p) {
            $st = strtolower(trim($p['status'] ?? ''));
            if ($st === 'pending') { $pendingTx++; }
            elseif ($st === 'flagged') { $flaggedTx++; }
            elseif ($st === 'failed') { $failedTx++; }
        }
        if ($pendingTx > 0) { $paymentsItems[] = [ 'title' => 'Transactions pending verification', 'count' => $pendingTx, 'url' => 'index.php?page=admin_transactions' ]; }
        if ($flaggedTx > 0) { $paymentsItems[] = [ 'title' => 'Flagged transactions', 'count' => $flaggedTx, 'url' => 'index.php?page=admin_transactions' ]; }
        if ($failedTx > 0) { $paymentsItems[] = [ 'title' => 'Failed transactions', 'count' => $failedTx, 'url' => 'index.php?page=admin_transactions' ]; }
        $paymentsCount = 0; foreach ($paymentsItems as $it) { $paymentsCount += (int)$it['count']; }
        $groups[] = [ 'group' => 'Payments', 'items' => $paymentsItems, 'count' => $paymentsCount ];

        // Meters group
        $metersItems = [];
        $pendingApps = $this->meterApplicationModel->getApplicationsByStatus('pending');
        if (!empty($pendingApps)) {
            $metersItems[] = [ 'title' => 'Pending meter applications', 'count' => count($pendingApps), 'url' => 'index.php?page=admin_manage_meters' ];
        }
        $availableMeters = $this->meterModel->getAllMeters('available');
        if (!empty($availableMeters)) {
            $metersItems[] = [ 'title' => 'Meters ready for assignment', 'count' => count($availableMeters), 'url' => 'index.php?page=admin_manage_meters' ];
        }
        $metersCount = 0; foreach ($metersItems as $it) { $metersCount += (int)$it['count']; }
        $groups[] = [ 'group' => 'Meters', 'items' => $metersItems, 'count' => $metersCount ];

        $notificationsCount = 0; foreach ($groups as $g) { $notificationsCount += (int)$g['count']; }
        $this->sendJsonResponse([ 'groups' => $groups, 'notificationsCount' => $notificationsCount ]);
    }

    /**
     * Helper method to send JSON responses
     */
    private function sendJsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
