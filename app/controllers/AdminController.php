<?php
// app/controllers/AdminController.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/BillingPlan.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/User.php'; // Include User model for managing users
require_once __DIR__ . '/../models/ServiceRequest.php'; // Include ServiceRequest model
require_once __DIR__ . '/../models/Client.php'; // Include Client model for new client registration by admin
require_once __DIR__ . '/../models/Meter.php'; // Include Meter model for commercial manager
require_once __DIR__ . '/../models/MeterReading.php'; // Include MeterReading model for collector
require_once __DIR__ . '/../models/Payment.php'; // Include Payment model for finance manager
require_once __DIR__ . '/../models/ServiceAttendance.php'; // Include ServiceAttendance model
require_once __DIR__ . '/../models/MeterApplication.php';
require_once __DIR__ . '/../models/VerifiedMeter.php';
require_once __DIR__ . '/../models/MeterInstallation.php';
require_once __DIR__ . '/../models/ClientPlan.php';
require_once __DIR__ . '/../models/SmsNotification.php';

class AdminController {
    private Database $db;
    private BillingPlan $billingPlanModel;
    private Service $serviceModel;
    private Auth $auth;
    private User $userModel; // Declare User model property
    private ServiceRequest $serviceRequestModel; // Declare ServiceRequest model property
    private Client $clientModel; // Declare Client model property
    private Meter $meterModel; // Declare Meter model property
    private MeterReading $meterReadingModel; // Declare MeterReading model property
    private Payment $paymentModel; // Declare Payment model property
    private ServiceAttendance $serviceAttendanceModel; // Declare ServiceAttendance model property
    private MeterApplication $meterApplicationModel;
    private VerifiedMeter $verifiedMeterModel;
    private MeterInstallation $meterInstallationModel;
    private ClientPlan $clientPlanModel;

    public function __construct(Database $database_instance, Auth $auth_instance) {
        $this->db = $database_instance;
        $this->auth = $auth_instance;
        $this->billingPlanModel = new BillingPlan($this->db);
        $this->serviceModel = new Service($this->db);
        $this->userModel = new User($this->db); // Initialize User model
        $this->serviceRequestModel = new ServiceRequest($this->db); // Initialize ServiceRequest model
        $this->clientModel = new Client($this->db); // Initialize Client model
        $this->meterModel = new Meter($this->db); // Initialize Meter model
        $this->meterReadingModel = new MeterReading($this->db); // Initialize MeterReading model
        $this->paymentModel = new Payment($this->db); // Initialize Payment model
        $this->serviceAttendanceModel = new ServiceAttendance($this->db); // Initialize ServiceAttendance model
        $this->meterApplicationModel = new MeterApplication($this->db);
        $this->verifiedMeterModel = new VerifiedMeter($this->db);
        $this->meterInstallationModel = new MeterInstallation($this->db);
        $this->clientPlanModel = new ClientPlan($this->db);
    }

    /**
     * Helper to check if the user is an admin.
     * Redirects to login if not.
     */
    private function checkAdminAuth(): void {
        if (!$this->auth->isLoggedIn() || $this->auth->getUserRole() !== 'admin') {
            $_SESSION['error_message'] = "Access denied. You must be logged in as an Admin.";
            header('Location: index.php?page=login');
            exit();
        }
    }

    /**
     * Displays the admin dashboard.
     */
    public function dashboard(): void {
        $this->checkAdminAuth();
        // Fetch summary data for the dashboard
        $totalUsers = $this->userModel->getUserCount();
        $totalClients = $this->userModel->getUserCountByRole('client');
        $totalCollectors = $this->userModel->getUserCountByRole('collector');
        $totalMeterReaders = $this->userModel->getUserCountByRole('meter_reader');
        $totalCommercialManagers = $this->userModel->getUserCountByRole('commercial_manager');
        $totalFinanceManagers = $this->userModel->getUserCountByRole('finance_manager');
        $totalServices = count($this->serviceModel->getAllServices());
        $totalBillingPlans = count($this->billingPlanModel->getAllPlans());
        $pendingServiceRequests = count($this->serviceRequestModel->getServiceRequests(null, 'pending'));
        $servicedServiceRequests = count($this->serviceRequestModel->getServiceRequests(null, 'serviced'));
        $completedServiceRequests = count($this->serviceRequestModel->getServiceRequests(null, 'completed'));
        $this->db->query('SELECT COUNT(*) AS cnt FROM client_plans WHERE status = "pending"');
        $pendingPlansRow = $this->db->single();
        $this->db->closeStmt();
        $pendingClientPlans = (int)($pendingPlansRow['cnt'] ?? 0);

        $requiredRoles = [
            'commercial_manager' => 1,
            'meter_reader' => 1,
            'collector' => 1,
            'finance_manager' => 1,
        ];
        $roleCounts = [
            'commercial_manager' => (int)$totalCommercialManagers,
            'meter_reader' => (int)$totalMeterReaders,
            'collector' => (int)$totalCollectors,
            'finance_manager' => (int)$totalFinanceManagers,
        ];
        $roleStatus = [];
        $missingRoles = [];
        foreach ($requiredRoles as $role => $min) {
            $cnt = (int)($roleCounts[$role] ?? 0);
            $ok = ($cnt >= (int)$min);
            $roleStatus[$role] = ['count' => $cnt, 'required' => (int)$min, 'ok' => $ok];
            if (!$ok) { $missingRoles[] = $role; }
        }

        $data = [
            'totalUsers' => $totalUsers,
            'totalClients' => $totalClients,
            'totalCollectors' => $totalCollectors,
            'totalMeterReaders' => $totalMeterReaders,
            'totalCommercialManagers' => $totalCommercialManagers,
            'totalFinanceManagers' => $totalFinanceManagers,
            'totalServices' => $totalServices,
            'totalBillingPlans' => $totalBillingPlans,
            'pendingServiceRequests' => $pendingServiceRequests,
            'servicedServiceRequests' => $servicedServiceRequests,
            'completedServiceRequests' => $completedServiceRequests,
            'pendingClientPlans' => $pendingClientPlans,
            'roleAlerts' => $missingRoles,
            'roleAlertsCount' => count($missingRoles),
            'roleRequired' => $requiredRoles,
            'roleStatus' => $roleStatus
        ];

        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    /**
     * Manages users (view, add, edit, delete).
     */
    public function manageUsers(): void {
        $this->checkAdminAuth();

        $error = '';
        $success = '';

        // Handle POST requests for user management
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['add_user'])) {
                $username = trim($_POST['username'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = trim($_POST['password'] ?? '');
                $full_name = trim($_POST['full_name'] ?? '');
                $address = trim($_POST['address'] ?? '');
                $contact_phone = trim($_POST['contact_phone'] ?? '');
                $role = trim($_POST['role'] ?? '');

                // Initialize admin_key to null. It will only be set if the role is privileged.
                $admin_key_to_pass = null;

                // Basic validation
                if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($address) || empty($contact_phone) || empty($role)) {
                    $error = "All fields are required.";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = "Invalid email format.";
                } elseif ($this->auth->userExists($username, $email)) {
                    $error = "Username or Email already exists.";
                } else {
                    // Validation for privileged roles requiring an admin key
                    if ($role === 'admin' || $role === 'commercial_manager' || $role === 'finance_manager' || $role === 'collector' || $role === 'meter_reader') {
                        $admin_key_input = trim($_POST['admin_key'] ?? '');
                        $expected_admin_key = defined('ADMIN_REGISTRATION_KEY') ? ADMIN_REGISTRATION_KEY : 'ADMIN_KEY_123';
                        
                        if (empty($admin_key_input)) {
                            $error = "Privileged role key is required for " . htmlspecialchars($role) . " role.";
                        } elseif (strcasecmp($admin_key_input, $expected_admin_key) !== 0) {
                            $error = "Invalid privileged role key for " . htmlspecialchars($role) . " registration.";
                        } else {
                            $admin_key_to_pass = $admin_key_input;
                        }
                    }

                    // Proceed with registration only if no errors so far
                    if (empty($error)) {
                        if ($this->auth->register($username, $email, $password, $full_name, $address, $contact_phone, $role, $admin_key_to_pass)) {
                            $success = "User '" . htmlspecialchars($username) . "' registered successfully as " . htmlspecialchars($role) . ".";

                            if ($role === 'client') {
                                $newUserId = $this->auth->lastInsertId();
                                if (!$this->clientModel->registerClient($newUserId, $full_name, $address, $contact_phone)) {
                                    error_log("AdminController: Failed to register client details for user ID: " . $newUserId);
                                    $error .= " However, client specific details could not be saved.";
                                }
                            }
                        } else {
                            $error = "Failed to register user. Please try again.";
                        }
                    }
                }
            } elseif (isset($_POST['update_user'])) {
                $userId = intval($_POST['user_id'] ?? 0);
                $username = trim($_POST['username'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $full_name = trim($_POST['full_name'] ?? '');
                $address = trim($_POST['address'] ?? '');
                $contact_phone = trim($_POST['contact_phone'] ?? '');
                $role = trim($_POST['role'] ?? '');
                $status = trim($_POST['status'] ?? '');

                if ($userId > 0 && !empty($username) && !empty($email) && !empty($full_name) && !empty($address) && !empty($contact_phone) && !empty($role) && !empty($status)) {
                    if ($this->userModel->updateUser($userId, $username, $email, $role, $full_name, $address, $contact_phone, $status)) {
                        $success = "User updated successfully!";
                    } else {
                        $error = "Failed to update user.";
                    }
                } else {
                    $error = "Invalid user data for update.";
                }
            } elseif (isset($_POST['delete_user'])) {
                $userId = intval($_POST['user_id'] ?? 0);
                if ($userId > 0) {
                    if ($this->userModel->deleteUser($userId)) {
                        $success = "User deleted successfully!";
                    } else {
                        $error = "Failed to delete user.";
                    }
                } else {
                    $error = "Invalid user ID for deletion.";
                }
            }
        }

        // Fetch all users to display them
        $users = $this->userModel->getAllUsers();
        // Fetch all collectors for assignment dropdown
        $collectors = $this->userModel->getUsersByRole('collector');
        // Fetch all service requests
        $serviceRequests = $this->serviceRequestModel->getServiceRequests(); // Get all service requests

        // Pass data to the view
        $data = [
            'users' => $users,
            'collectors' => $collectors,
            'serviceRequests' => $serviceRequests, // Pass all service requests to the view
            'error' => $error,
            'success' => $success
        ];

        require_once __DIR__ . '/../views/admin/manage_users.php';
    }

    /**
     * Manages billing plans (view, add, edit, delete).
     */
    public function manageBillingPlans(): void {
        $this->checkAdminAuth();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $action = $_POST['action'] ?? '';

            switch ($action) {
                case 'add_plan':
                    $planName = trim($_POST['plan_name'] ?? '');
                    $description = trim($_POST['description'] ?? '');
                    $baseRate = filter_var($_POST['base_rate'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $unitRate = filter_var($_POST['unit_rate'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $minConsumption = filter_var($_POST['min_consumption'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $maxConsumption = isset($_POST['max_consumption']) && $_POST['max_consumption'] !== '' ? filter_var($_POST['max_consumption'], FILTER_VALIDATE_FLOAT) : null;
                    $billingCycle = trim($_POST['billing_cycle'] ?? '');
                    $isActive = isset($_POST['is_active']) ? 1 : 0;

                    $fixedServiceFee = filter_var($_POST['fixed_service_fee'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $sewerCharge = filter_var($_POST['sewer_charge'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $taxPercent = filter_var($_POST['tax_percent'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $taxInclusive = isset($_POST['tax_inclusive']) ? 1 : 0;
                    $tiersJson = $_POST['tiers_json'] ?? null;

                    if (empty($planName) || $baseRate === false || $unitRate === false || $minConsumption === false || empty($billingCycle)) {
                        $error = "Please fill all required fields for adding a billing plan.";
                    } else {
                        if ($this->billingPlanModel->addPlan($planName, $description, $baseRate, $unitRate, $minConsumption, $maxConsumption, $billingCycle, $isActive, $fixedServiceFee ?: 0, $sewerCharge ?: 0, $taxPercent ?: 0, $taxInclusive, $tiersJson)) {
                            $success = "Billing plan added successfully!";
                        } else {
                            $error = "Failed to add billing plan.";
                        }
                    }
                    break;

                case 'update_plan':
                    $planId = intval($_POST['plan_id'] ?? 0);
                    $planName = trim($_POST['plan_name'] ?? '');
                    $description = trim($_POST['description'] ?? '');
                    $baseRate = filter_var($_POST['base_rate'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $unitRate = filter_var($_POST['unit_rate'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $minConsumption = filter_var($_POST['min_consumption'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $maxConsumption = isset($_POST['max_consumption']) && $_POST['max_consumption'] !== '' ? filter_var($_POST['max_consumption'], FILTER_VALIDATE_FLOAT) : null;
                    $billingCycle = trim($_POST['billing_cycle'] ?? '');
                    $isActive = isset($_POST['is_active']) ? 1 : 0;

                    $fixedServiceFee = filter_var($_POST['fixed_service_fee'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $sewerCharge = filter_var($_POST['sewer_charge'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $taxPercent = filter_var($_POST['tax_percent'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $taxInclusive = isset($_POST['tax_inclusive']) ? 1 : 0;
                    $tiersJson = $_POST['tiers_json'] ?? null;

                    if ($planId <= 0 || empty($planName) || $baseRate === false || $unitRate === false || $minConsumption === false || empty($billingCycle)) {
                        $error = "Invalid billing plan data for update.";
                    } else {
                        if ($this->billingPlanModel->updatePlan($planId, $planName, $description, $baseRate, $unitRate, $minConsumption, $maxConsumption, $billingCycle, $isActive, $fixedServiceFee ?: 0, $sewerCharge ?: 0, $taxPercent ?: 0, $taxInclusive, $tiersJson)) {
                            $success = "Billing plan updated successfully!";
                        } else {
                            $error = "Failed to update billing plan.";
                        }
                    }
                    break;

                case 'delete_plan':
                    $planId = intval($_POST['plan_id'] ?? 0);
                    if ($planId > 0) {
                        if ($this->billingPlanModel->deletePlan($planId)) {
                            $success = "Billing plan deleted successfully!";
                        } else {
                            $error = "Failed to delete billing plan.";
                        }
                    } else {
                        $error = "Invalid plan ID for deletion.";
                    }
                    break;
            }
        }

        $billingPlans = $this->billingPlanModel->getAllPlans();
        $data = [
            'billingPlans' => $billingPlans,
            'error' => $error,
            'success' => $success
        ];
        require_once __DIR__ . '/../views/admin/billing_plans.php';
    }

    /**
     * Manages services (add, update, delete).
     */
    public function manageServices(): void {
        $this->checkAdminAuth();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $action = $_POST['action'] ?? '';

            switch ($action) {
                case 'add_service':
                    $service_name = trim($_POST['service_name'] ?? '');
                    $description = trim($_POST['description'] ?? '');
                    $cost = filter_var($_POST['cost'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $is_active = isset($_POST['is_active']) ? 1 : 0;

                    if (empty($service_name) || empty($description) || $cost === false) {
                        $error = "Please fill all required fields for adding a service.";
                    } else {
                        if ($this->serviceModel->addService($service_name, $description, $cost, $is_active)) {
                            $success = "Service added successfully!";
                        } else {
                            $error = "Failed to add service.";
                        }
                    }
                    break;
                case 'update_service':
                    $service_id = intval($_POST['service_id'] ?? 0);
                    $service_name = trim($_POST['service_name'] ?? '');
                    $description = trim($_POST['description'] ?? '');
                    $cost = filter_var($_POST['cost'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $is_active = isset($_POST['is_active']) ? 1 : 0;

                    if ($service_id > 0 && !empty($service_name) && !empty($description) && $cost !== false) {
                        if ($this->serviceModel->updateService($service_id, $service_name, $description, $cost, $is_active)) {
                            $success = "Service updated successfully!";
                        } else {
                            $error = "Failed to update service.";
                        }
                    } else {
                        $error = "Invalid service data for update.";
                    }
                    break;
                case 'delete_service':
                    $service_id = intval($_POST['service_id'] ?? 0);
                    if ($service_id > 0) {
                        if ($this->serviceModel->deleteService($service_id)) {
                            $success = "Service deleted successfully!";
                        } else {
                            $error = "Failed to delete service.";
                        }
                    } else {
                        $error = "Invalid service ID for deletion.";
                    }
                    break;
            }
        }

        // Fetch all services to display them
        $services = $this->serviceModel->getAllServices();

        $data = [
            'services' => $services,
            'error' => $error,
            'success' => $success
        ];

        $manageServicesViewPath = __DIR__ . '/../views/admin/manage_services.php';

        if (!file_exists($manageServicesViewPath)) {
            $error = "Critical Error: The 'manage_services.php' view file is missing or inaccessible. Please ensure it's located at: " . $manageServicesViewPath;
            error_log("Fatal Error: manage_services.php not found at: " . $manageServicesViewPath);
        } else {
            require_once $manageServicesViewPath;
        }
    }

    /**
     * Manages service requests (view, assign, update status).
     */
    public function manageRequests(): void {
        $this->checkAdminAuth();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $action = $_POST['action'] ?? '';
            $requestId = filter_var($_POST['request_id'] ?? 0, FILTER_VALIDATE_INT);

            if ($requestId <= 0) {
                $error = "Invalid request ID.";
            } else {
                switch ($action) {
                    case 'assign_request':
                        $assigneeId = filter_var($_POST['assignee_id'] ?? ($_POST['collector_id'] ?? 0), FILTER_VALIDATE_INT);
                        if ($assigneeId <= 0) {
                            $error = "Please select a staff member to assign.";
                        } else {
                            if ($this->serviceRequestModel->assignServiceRequest($requestId, $assigneeId)) {
                                $success = "Service request assigned successfully!";
                            } else {
                                $error = "Failed to assign service request.";
                            }
                        }
                        break;
                    case 'update_status':
                        $newStatus = trim($_POST['new_status'] ?? '');
                        if (empty($newStatus)) {
                            $error = "New status cannot be empty.";
                        } else {
                            if ($this->serviceRequestModel->updateServiceRequestStatus($requestId, $newStatus)) {
                                $success = "Service request status updated successfully!";
                            } else {
                                $error = "Failed to update service request status.";
                            }
                        }
                        break;
                }
            }
        }

        // Fetch all service requests
        $serviceRequests = $this->serviceRequestModel->getServiceRequests();
        // Fetch both meter readers and collectors for assignment dropdown
        $meterReaders = $this->userModel->getUsersByRole('meter_reader');
        $collectors = $this->userModel->getUsersByRole('collector');
        $staff = array_merge($meterReaders, $collectors);

        $data = [
            'serviceRequests' => $serviceRequests,
            'staff' => $staff,
            'error' => $error,
            'success' => $success
        ];

        require_once __DIR__ . '/../views/admin/manage_requests.php';
    }

    public function manageClientPlans(): void {
        if (!$this->auth->isLoggedIn() || ($this->auth->getUserRole() !== 'admin' && $this->auth->getUserRole() !== 'finance_manager')) {
            header('Location: index.php?page=login');
            exit;
        }

        $message = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $clientPlanId = filter_var($_POST['client_plan_id'] ?? 0, FILTER_VALIDATE_INT);
            if ($clientPlanId > 0) {
                $planRow = $this->clientPlanModel->getClientPlanById($clientPlanId);
                if ($planRow) {
                    if ($action === 'approve') {
                        $ok = $this->clientPlanModel->updateClientPlanStatus($clientPlanId, 'active');
                        if ($ok) {
                            $userId = (int)($planRow['user_id'] ?? 0);
                            $this->db->query('UPDATE client_plans SET status = "cancelled", updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND id <> ? AND status = "active"');
                            $this->db->bind([$userId, $clientPlanId]);
                            $this->db->execute();
                            $this->db->closeStmt();

                            $bp = $this->billingPlanModel->getPlanById((int)$planRow['plan_id']);
                            $cycle = $bp['billing_cycle'] ?? 'monthly';
                            $nextBillingDate = date('Y-m-d', strtotime('+1 ' . $cycle));
                            $this->clientPlanModel->updateClientPlanDetails($clientPlanId, null, $nextBillingDate);

                            try {
                                $this->db->query('SELECT phone, name FROM clients WHERE user_id = ? LIMIT 1');
                                $this->db->bind([$userId]);
                                $cl = $this->db->single();
                                $this->db->closeStmt();
                                if ($cl && !empty($cl['phone'])) {
                                    $msg = 'Your plan has been approved and activated: ' . ($bp['plan_name'] ?? ('Plan #' . (int)$planRow['plan_id'])) . '.';
                                    $notifier = new SmsNotification();
                                    $notifier->recordNotification($cl['phone'], $msg, 'plan_change', 'sent', (int)$clientPlanId);
                                }
                            } catch (\Throwable $e) { }
                            $message = 'Plan approved and activated.';
                        } else { $error = 'Failed to approve plan.'; }
                    } elseif ($action === 'reject') {
                        $ok = $this->clientPlanModel->updateClientPlanStatus($clientPlanId, 'rejected');
                        if ($ok) {
                            try {
                                $userId = (int)($planRow['user_id'] ?? 0);
                                $this->db->query('SELECT phone, name FROM clients WHERE user_id = ? LIMIT 1');
                                $this->db->bind([$userId]);
                                $cl = $this->db->single();
                                $this->db->closeStmt();
                                if ($cl && !empty($cl['phone'])) {
                                    $bp = $this->billingPlanModel->getPlanById((int)$planRow['plan_id']);
                                    $msg = 'Your plan application was rejected: ' . ($bp['plan_name'] ?? ('Plan #' . (int)$planRow['plan_id'])) . '.';
                                    $notifier = new SmsNotification();
                                    $notifier->recordNotification($cl['phone'], $msg, 'plan_change', 'sent', (int)$clientPlanId);
                                }
                            } catch (\Throwable $e) { }
                            $message = 'Plan application rejected.';
                        } else { $error = 'Failed to reject plan.'; }
                    } elseif ($action === 'discontinue') {
                        $ok = $this->clientPlanModel->updateClientPlanStatus($clientPlanId, 'cancelled');
                        if ($ok) {
                            try {
                                $userId = (int)($planRow['user_id'] ?? 0);
                                $this->db->query('SELECT phone, name FROM clients WHERE user_id = ? LIMIT 1');
                                $this->db->bind([$userId]);
                                $cl = $this->db->single();
                                $this->db->closeStmt();
                                if ($cl && !empty($cl['phone'])) {
                                    $bp = $this->billingPlanModel->getPlanById((int)$planRow['plan_id']);
                                    $msg = 'Your plan was discontinued: ' . ($bp['plan_name'] ?? ('Plan #' . (int)$planRow['plan_id'])) . '.';
                                    $notifier = new SmsNotification();
                                    $notifier->recordNotification($cl['phone'], $msg, 'plan_change', 'sent', (int)$clientPlanId);
                                }
                            } catch (\Throwable $e) { }
                            $message = 'Plan discontinued successfully.';
                        } else { $error = 'Failed to discontinue plan.'; }
                    } elseif ($action === 'reinstate') {
                        $ok = $this->clientPlanModel->updateClientPlanStatus($clientPlanId, 'active');
                        if ($ok) {
                            $userId = (int)($planRow['user_id'] ?? 0);
                            $this->db->query('UPDATE client_plans SET status = "cancelled", updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND id <> ? AND status = "active"');
                            $this->db->bind([$userId, $clientPlanId]);
                            $this->db->execute();
                            $this->db->closeStmt();
                            try {
                                $this->db->query('SELECT phone, name FROM clients WHERE user_id = ? LIMIT 1');
                                $this->db->bind([$userId]);
                                $cl = $this->db->single();
                                $this->db->closeStmt();
                                if ($cl && !empty($cl['phone'])) {
                                    $bp = $this->billingPlanModel->getPlanById((int)$planRow['plan_id']);
                                    $msg = 'Your plan was reinstated: ' . ($bp['plan_name'] ?? ('Plan #' . (int)$planRow['plan_id'])) . '.';
                                    $notifier = new SmsNotification();
                                    $notifier->recordNotification($cl['phone'], $msg, 'plan_change', 'sent', (int)$clientPlanId);
                                }
                            } catch (\Throwable $e) { }
                            $message = 'Plan reinstated successfully.';
                        } else { $error = 'Failed to reinstate plan.'; }
                    } elseif ($action === 'edit') {
                        $newPlanId = isset($_POST['new_plan_id']) ? (int)$_POST['new_plan_id'] : null;
                        $nextBillingDate = $_POST['next_billing_date'] ?? null;
                        if ($nextBillingDate) { $nextBillingDate = date('Y-m-d', strtotime($nextBillingDate)); }
                        if ($newPlanId || $nextBillingDate) {
                            $ok = $this->clientPlanModel->updateClientPlanDetails($clientPlanId, $newPlanId ?: null, $nextBillingDate ?: null);
                            if ($ok) { $message = 'Client plan details updated.'; } else { $error = 'Failed to update plan details.'; }
                        } else {
                            $error = 'No changes submitted.';
                        }
                    }
                } else {
                    $error = 'Plan not found.';
                }
            } else {
                $error = 'Invalid plan selection.';
            }
            $_SESSION['message'] = $message;
            $_SESSION['error'] = $error;
            header('Location: index.php?page=admin_manage_client_plans');
            exit;
        }

        // Fetch table data
        $this->db->query('SELECT cp.*, u.username, u.full_name, bp.plan_name, bp.base_rate, bp.billing_cycle FROM client_plans cp INNER JOIN users u ON cp.user_id = u.id INNER JOIN billing_plans bp ON cp.plan_id = bp.id ORDER BY cp.created_at DESC');
        $plans = $this->db->resultSet();
        $this->db->closeStmt();

        // Optional edit context
        $editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
        $editing = null;
        if ($editId > 0) {
            foreach ($plans as $p) { if ((int)$p['id'] === $editId) { $editing = $p; break; } }
        }
        $availablePlans = $this->billingPlanModel->getAllPlans();

        $data = [
            'plans' => $plans,
            'message' => $_SESSION['message'] ?? '',
            'error' => $_SESSION['error'] ?? '',
            'editing' => $editing,
            'availablePlans' => $availablePlans
        ];
        unset($_SESSION['message'], $_SESSION['error']);
        require_once __DIR__ . '/../views/admin/manage_client_plans.php';
    }

    public function profile(): void {
        $this->checkAdminAuth();
        $userId = $_SESSION['user_id'] ?? null;
        $data = [];
        if ($userId) {
            $data['user'] = $this->auth->getUserById($userId); // Get full user details
        }
        require_once __DIR__ . '/../views/admin/profile.php';
    }

    public function reports(): void {
        $this->checkAdminAuth();
        $error = $_SESSION['error_message'] ?? '';
        $success = $_SESSION['success_message'] ?? '';
        unset($_SESSION['error_message'], $_SESSION['success_message']);

        $reportType = $_GET['report_type'] ?? 'revenue';
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $totalUsers = $this->userModel->getUserCount();
        $totalMeters = count($this->meterModel->getAllMeters());
        $activeRequests = count($this->serviceRequestModel->getServiceRequests(null, 'pending'));
        $userCounts = [
            'client' => $this->userModel->getUserCountByRole('client'),
            'collector' => $this->userModel->getUserCountByRole('collector'),
            'finance_manager' => $this->userModel->getUserCountByRole('finance_manager'),
            'admin' => $this->userModel->getUserCountByRole('admin'),
        ];

        $payments = $this->paymentModel->getAllClientsPaymentReport($startDate, $endDate) ?? [];
        $totalRevenue = 0.0;
        $revenueRows = [];
        foreach ($payments as $p) {
            $totalRevenue += (float)($p['amount'] ?? 0);
            $revenueRows[] = [
                'id' => (int)($p['id'] ?? 0),
                'payment_type' => $p['type'] ?? 'bill_payment',
                'amount' => (float)($p['amount'] ?? 0),
                'status' => $p['status'] ?? '',
                'client_name' => $p['client_name'] ?? ($p['client_email'] ?? ''),
                'payment_date' => $p['payment_date'] ?? '',
            ];
        }

        $usersRows = array_map(function($u){
            return [
                'id' => (int)($u['id'] ?? 0),
                'username' => $u['username'] ?? '',
                'email' => $u['email'] ?? '',
                'role' => $u['role'] ?? '',
                'full_name' => $u['full_name'] ?? '',
                'status' => $u['status'] ?? '',
                'created_at' => $u['created_at'] ?? ''
            ];
        }, $this->userModel->getAllUsers());

        $metersRows = array_map(function($m){
            return [
                'id' => (int)($m['id'] ?? 0),
                'serial_number' => $m['serial_number'] ?? '',
                'status' => $m['status'] ?? '',
                'client_name' => $m['client_username'] ?? '',
                'collector_name' => $m['collector_username'] ?? '',
                'installation_date' => $m['installation_date'] ?? ''
            ];
        }, $this->meterModel->getAllMeters());

        $serviceRequestsRows = array_map(function($sr){
            return [
                'id' => (int)($sr['id'] ?? 0),
                'service_type' => $sr['service_name'] ?? '',
                'status' => $sr['status'] ?? '',
                'client_name' => $sr['client_username'] ?? '',
                'collector_name' => $sr['assigned_collector_username'] ?? '',
                'request_date' => $sr['request_date'] ?? '',
                'completion_date' => $sr['completion_date'] ?? ''
            ];
        }, $this->serviceRequestModel->getServiceRequests());

        $overview = [
            'total_users' => $totalUsers,
            'total_meters' => $totalMeters,
            'active_requests' => $activeRequests,
            'total_revenue' => $totalRevenue,
            'user_counts' => $userCounts,
            'service_request_counts' => [
                'pending' => count($this->serviceRequestModel->getServiceRequests(null, 'pending')),
                'serviced' => count($this->serviceRequestModel->getServiceRequests(null, 'serviced')),
                'completed' => count($this->serviceRequestModel->getServiceRequests(null, 'completed')),
                'cancelled' => count($this->serviceRequestModel->getServiceRequests(null, 'cancelled')),
            ]
        ];

        $data = [
            'report_type' => $reportType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'overview' => $overview,
            'revenue' => $revenueRows,
            'users' => $usersRows,
            'meters' => $metersRows,
            'service_requests' => $serviceRequestsRows,
            'error' => $error,
            'success' => $success
        ];

        require_once __DIR__ . '/../views/admin/reports.php';
    }
    
    /**
     * Manages meters (view, add, edit, delete, assign, verify).
     */
    public function manageMeters(): void {
        $this->checkAdminAuth();
        
        $error = '';
        $success = '';
        
        // Handle POST actions for meter management
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'add_meter':
                    $serialNumber = trim($_POST['serial_number'] ?? '');
                    $meterType = trim($_POST['meter_type'] ?? '');
                    $initialReading = filter_var($_POST['initial_reading'] ?? 0, FILTER_VALIDATE_FLOAT);
                    
                    if (empty($serialNumber) || empty($meterType) || $initialReading === false) {
                        $error = "Please fill all required fields for adding a meter.";
                    } else {
                        // Handle image upload if present
                        $photoUrl = null;
                        if (isset($_FILES['meter_image']) && $_FILES['meter_image']['error'] == 0) {
                            $uploadDir = 'uploads/meters/';
                            
                            // Create directory if it doesn't exist
                            if (!file_exists($uploadDir)) {
                                mkdir($uploadDir, 0777, true);
                            }
                            
                            $fileName = time() . '_' . basename($_FILES['meter_image']['name']);
                            $targetFilePath = $uploadDir . $fileName;
                            
                            // Check if image file is an actual image
                            $check = getimagesize($_FILES['meter_image']['tmp_name']);
                            if ($check !== false) {
                                // Upload file
                                if (move_uploaded_file($_FILES['meter_image']['tmp_name'], $targetFilePath)) {
                                    $photoUrl = $targetFilePath;
                                } else {
                                    $error = "Sorry, there was an error uploading your file.";
                                }
                            } else {
                                $error = "File is not an image.";
                            }
                        }
                        
                        if (empty($error)) {
                            if ($this->meterModel->addMeter($serialNumber, $meterType, $initialReading, $photoUrl)) {
                                $success = "Meter added successfully!";
                            } else {
                                $error = "Failed to add meter.";
                            }
                        }
                    }
                    break;
                    
                case 'update_meter':
                    $meterId = intval($_POST['meter_id'] ?? 0);
                    $serialNumber = trim($_POST['serial_number'] ?? '');
                    $meterType = trim($_POST['meter_type'] ?? '');
                    $initialReading = filter_var($_POST['initial_reading'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $status = trim($_POST['status'] ?? '');
                    
                    if ($meterId <= 0 || empty($serialNumber) || empty($meterType) || $initialReading === false || empty($status)) {
                        $error = "Please fill all required fields for updating a meter.";
                    } else {
                        // Get current meter data to preserve photo_url if not updated
                        $currentMeter = $this->meterModel->getMeterById($meterId);
                        $photoUrl = $currentMeter['photo_url'] ?? null;
                        
                        // Handle image upload if present
                        if (isset($_FILES['meter_image']) && $_FILES['meter_image']['error'] == 0) {
                            $uploadDir = 'uploads/meters/';
                            
                            // Create directory if it doesn't exist
                            if (!file_exists($uploadDir)) {
                                mkdir($uploadDir, 0777, true);
                            }
                            
                            $fileName = time() . '_' . basename($_FILES['meter_image']['name']);
                            $targetFilePath = $uploadDir . $fileName;
                            
                            // Check if image file is an actual image
                            $check = getimagesize($_FILES['meter_image']['tmp_name']);
                            if ($check !== false) {
                                // Upload file
                                if (move_uploaded_file($_FILES['meter_image']['tmp_name'], $targetFilePath)) {
                                    // Delete old image if exists
                                    if (!empty($currentMeter['photo_url']) && file_exists($currentMeter['photo_url'])) {
                                        unlink($currentMeter['photo_url']);
                                    }
                                    $photoUrl = $targetFilePath;
                                } else {
                                    $error = "Sorry, there was an error uploading your file.";
                                }
                            } else {
                                $error = "File is not an image.";
                            }
                        }
                        
                        if (empty($error)) {
                            if ($this->meterModel->updateMeter($meterId, $serialNumber, $meterType, $initialReading, $status, $photoUrl)) {
                                $success = "Meter updated successfully!";
                            } else {
                                $error = "Failed to update meter.";
                            }
                        }
                    }
                    break;
                    
                case 'delete_meter':
                    $meterId = intval($_POST['meter_id'] ?? 0);
                    if ($meterId <= 0) {
                        $error = "Invalid meter ID for deletion.";
                    } else {
                        // Get meter to check if it can be deleted (only available meters can be deleted)
                        $meter = $this->meterModel->getMeterById($meterId);
                        if ($meter && ($meter['status'] == 'available' || $meter['status'] == 'in_stock')) {
                            // Delete meter image if exists
                            if (!empty($meter['photo_url']) && file_exists($meter['photo_url'])) {
                                unlink($meter['photo_url']);
                            }
                            
                            if ($this->meterModel->deleteMeter($meterId)) {
                                $success = "Meter deleted successfully!";
                            } else {
                                $error = "Failed to delete meter.";
                            }
                        } else {
                            $error = "Only available meters can be deleted.";
                        }
                    }
                    break;
                    
                case 'assign_meter_to_client':
                    $meterId = intval($_POST['meter_id'] ?? 0);
                    $clientId = intval($_POST['client_id'] ?? 0);
                    $installationDate = trim($_POST['installation_date'] ?? '');
                    
                    if ($meterId <= 0 || $clientId <= 0 || empty($installationDate)) {
                        $error = "Please fill all required fields for assigning a meter.";
                    } else {
                        if ($this->meterModel->assignMeterToClient($meterId, $clientId, $installationDate)) {
                            $success = "Meter assigned to client successfully!";
                        } else {
                            $error = "Failed to assign meter to client.";
                        }
                    }
                    break;
                
                case 'flag_meter':
                    $meterId = intval($_POST['meter_id'] ?? 0);
                    if ($meterId > 0) {
                        if ($this->meterModel->flagMeter($meterId)) {
                            $success = 'Meter flagged successfully.';
                        } else {
                            $error = 'Failed to flag meter.';
                        }
                    } else {
                        $error = 'Invalid meter ID.';
                    }
                    break;

                case 'unflag_meter':
                    $meterId = intval($_POST['meter_id'] ?? 0);
                    if ($meterId > 0) {
                        if ($this->meterModel->unflagMeter($meterId)) {
                            $success = 'Meter unflagged successfully.';
                        } else {
                            $error = 'Failed to unflag meter. Ensure it is not assigned to a client.';
                        }
                    } else {
                        $error = 'Invalid meter ID.';
                    }
                    break;
                    
                case 'verify_meter':
                    $meterId = intval($_POST['meter_id'] ?? 0);
                    $verificationStatus = trim($_POST['verification_status'] ?? '');
                    $verificationNotes = trim($_POST['verification_notes'] ?? '');
                    
                    if ($meterId <= 0 || empty($verificationStatus)) {
                        $error = "Invalid verification data.";
                    } else {
                        // Update meter verification status
                        if ($this->meterModel->updateMeterVerification($meterId, $verificationStatus, $verificationNotes)) {
                            $success = "Meter verification updated successfully!";
                        } else {
                            $error = "Failed to update meter verification.";
                        }
                    }
                    break;

                case 'verify_application':
                    $applicationId = intval($_POST['application_id'] ?? 0);
                    $decision = trim($_POST['decision'] ?? '');
                    $notes = trim($_POST['notes'] ?? '');
                    $adminId = intval($_SESSION['user_id'] ?? 0);
                    if ($applicationId <= 0 || ($decision !== 'approve' && $decision !== 'reject')) {
                        $error = 'Invalid application verification data.';
                    } else {
                        if ($this->meterApplicationModel->adminVerifyApplication($applicationId, $adminId, $decision, $notes)) {
                            $success = $decision === 'approve' ? 'Application admin-verified.' : 'Application rejected by admin.';
                            if ($decision === 'approve') {
                                // Insert into verified_meters table for client view
                                $app = $this->meterApplicationModel->getApplicationById($applicationId);
                                if ($app) {
                                    $meter = $this->meterModel->getMeterById((int)$app['meter_id']);
                                    $adminName = $_SESSION['username'] ?? 'Admin';
                                    $this->verifiedMeterModel->create([
                                        'client_id' => (int)$app['client_id'],
                                        'client_name' => $app['client_name'] ?? ('User #'.$app['client_id']),
                                        'meter_id' => (int)$app['meter_id'],
                                        'meter_serial' => $meter['serial_number'] ?? 'N/A',
                                        'meter_status' => $meter['status'] ?? 'installed',
                                        'initial_reading' => (float)($meter['initial_reading'] ?? 0),
                                        'current_reading' => (float)($meter['initial_reading'] ?? 0),
                                        'verification_date' => date('Y-m-d H:i:s'),
                                        'admin_id' => $adminId,
                                        'admin_name' => $adminName,
                                    ]);
                                }
                            }
                        } else {
                            $error = 'Failed to update application verification.';
                        }
                    }
                    break;

                case 'assign_installer':
                    $meterId = intval($_POST['meter_id'] ?? 0);
                    $installerId = intval($_POST['installer_user_id'] ?? 0);
                    if ($meterId <= 0 || $installerId <= 0) {
                        $error = 'Invalid installer assignment data.';
                    } else {
                        if ($this->meterModel->markWaitingForInstallation($meterId, $installerId)) {
                            $success = 'Installer assigned. Meter status set to waiting for installation.';
                            // reflect status to verified_meters table
                            $this->verifiedMeterModel->updateStatusByMeterId($meterId, 'waiting_installation');
                            // create or upsert a waiting installation task for installer visibility
                            $meterRow = $this->meterModel->getMeterById($meterId);
                            if ($meterRow && !empty($meterRow['client_id'])) {
                                // create a waiting installation record if none exists yet
                                $this->meterInstallationModel->create([
                                    'meter_id' => (int)$meterId,
                                    'client_id' => (int)$meterRow['client_id'],
                                    'installer_user_id' => (int)$installerId,
                                    'initial_reading' => 0,
                                    'photo_url' => null,
                                    'gps_location' => null,
                                    'notes' => 'Installer assigned by admin',
                                    'status' => 'waiting_installation'
                                ]);
                            }
                        } else {
                            $error = 'Failed to assign installer.';
                        }
                    }
                    break;
                
                case 'update_installation_status':
                    $meterId = intval($_POST['meter_id'] ?? 0);
                    $newStatus = trim($_POST['new_status'] ?? '');
                    if ($meterId <= 0 || !in_array($newStatus, ['waiting_installation','assigned','cancelled'], true)) {
                        $error = 'Invalid installation status update.';
                    } else {
                        if ($newStatus === 'assigned') {
                            // move back to assignment queue view
                            $this->verifiedMeterModel->updateStatusByMeterId($meterId, 'assigned');
                            $success = 'Moved back to assignment queue.';
                        } else {
                            $this->verifiedMeterModel->updateStatusByMeterId($meterId, $newStatus);
                            $success = 'Installation status updated.';
                        }
                    }
                    break;

                case 'review_installation_submission':
                    $submissionId = intval($_POST['submission_id'] ?? 0);
                    $decision = trim($_POST['decision'] ?? '');
                    $notes = trim($_POST['notes'] ?? '');
                    $adminId = intval($_SESSION['user_id'] ?? 0);
                    if ($submissionId > 0 && in_array($decision, ['approve','reject'], true)) {
                        if ($this->meterInstallationModel->review($submissionId, $adminId, $decision, $notes)) {
                            if ($decision === 'approve') {
                                // Fetch submission to get meter/client and reading
                                $submission = $this->meterInstallationModel->getById($submissionId);
                                if ($submission) {
                                    $meterIdForStatus = (int)$submission['meter_id'];
                                    $initialReading = (float)($submission['initial_reading'] ?? 0);
                                    $meter = $this->meterModel->getMeterById($meterIdForStatus);
                                    if ($meter) {
                                        // mark installed on meters
                                        $this->meterModel->updateMeter(
                                            $meterIdForStatus,
                                            $meter['serial_number'],
                                            $meter['meter_type'],
                                            (float)$meter['initial_reading'],
                                            'installed',
                                            $meter['photo_url'],
                                            $meter['gps_location'],
                                            $meter['client_id'],
                                            date('Y-m-d'),
                                            $meter['assigned_collector_id']
                                        );
                                        // mirror verified_meters
                                        $this->verifiedMeterModel->updateStatusByMeterId($meterIdForStatus, 'installed');
                                        // create first reading
                                        $readingModel = new MeterReading($this->db);
                                        $readingModel->recordReading(
                                            $meterIdForStatus,
                                            $initialReading,
                                            (int)$meter['assigned_collector_id'],
                                            (int)$meter['client_id'],
                                            '',
                                            0.0,
                                            0.0
                                        );
                                    }
                                }
                            } else { // reject: optionally notify client with reason
                                $submission = $this->meterInstallationModel->getById($submissionId);
                                if ($submission) {
                                    $meterRow = $this->meterModel->getMeterById((int)$submission['meter_id']);
                                    $clientRow = $this->clientModel->getClientById((int)$submission['client_id']);
                                    $serial = $meterRow['serial_number'] ?? 'meter';
                                    $clientPhone = $clientRow['contact_phone'] ?? '';
                                    $reason = !empty($notes) ? $notes : 'No reason provided';
                                    $message = "Your installation submission for serial {$serial} was rejected: {$reason}. Please contact support or resubmit.";
                                    if (!empty($clientPhone)) {
                                        try {
                                            $sms = new \SmsNotification();
                                            // record as sent for now; integration can update delivery status later
                                            $sms->recordNotification($clientPhone, $message, 'installation_rejection', 'sent', $submissionId);
                                        } catch (\Throwable $e) {
                                            error_log('Failed to record SMS notification: ' . $e->getMessage());
                                        }
                                    }
                                }
                            }
                            $success = 'Submission reviewed.';
                        } else {
                            $error = 'Failed to review submission.';
                        }
                    } else {
                        $error = 'Invalid review data.';
                    }
                    break;

                case 'notify_installation_rejection':
                    $submissionId = intval($_POST['submission_id'] ?? 0);
                    $reason = trim($_POST['reason'] ?? '');
                    if ($submissionId <= 0 || $reason === '') {
                        $error = 'Please provide a valid submission and reason.';
                    } else {
                        $submission = $this->meterInstallationModel->getById($submissionId);
                        if ($submission) {
                            $meterRow = $this->meterModel->getMeterById((int)$submission['meter_id']);
                            $clientRow = $this->clientModel->getClientById((int)$submission['client_id']);
                            $serial = $meterRow['serial_number'] ?? 'meter';
                            $clientPhone = $clientRow['contact_phone'] ?? '';
                            $message = "Your installation submission for serial {$serial} was rejected: {$reason}. Please contact support or resubmit.";
                            if (!empty($clientPhone)) {
                                try {
                                    $sms = new \SmsNotification();
                                    $sms->recordNotification($clientPhone, $message, 'installation_rejection', 'sent', $submissionId);
                                    $success = 'Message sent to client.';
                                } catch (\Throwable $e) {
                                    error_log('Failed to record SMS notification: ' . $e->getMessage());
                                    $error = 'Could not send message at this time.';
                                }
                            } else {
                                $error = 'Client phone number is unavailable.';
                            }
                        } else {
                            $error = 'Submission not found.';
                        }
                    }
                    break;
            }
        }
        
        // Fetch all meters with client and collector information
        $meters = $this->meterModel->getAllMetersWithDetails();

        // Fetch applications awaiting admin verification (approved + admin_approval=1)
        $submittedApplications = $this->meterApplicationModel->getSubmittedToAdminApplications();
        // Fetch admin-verified applications for assignment
        $verifiedApplications = $this->meterApplicationModel->getAdminVerifiedApplications();
        // Fetch records waiting for installation from verified_meters
        $waitingInstallations = $this->verifiedMeterModel->getWaitingInstallations();
        // Exclude any apps whose meter is already waiting for installation
        if (!empty($waitingInstallations) && !empty($verifiedApplications)) {
            $waitingMeterIds = array_column($waitingInstallations, 'meter_id');
            $verifiedApplications = array_values(array_filter($verifiedApplications, function($row) use ($waitingMeterIds) {
                return !in_array((int)($row['meter_id'] ?? 0), array_map('intval', $waitingMeterIds), true);
            }));
        }
        
        // Get meter statistics
        $totalMeters = count($meters);
        $availableMeters = 0;
        $assignedMeters = 0;
        foreach ($meters as $meter) {
            if ($meter['status'] === 'available' || $meter['status'] === 'in_stock') {
                $availableMeters++;
            }
            // Consider assigned to be any meter linked to a client or explicitly in assigned/installed state
            if (!empty($meter['client_id']) || $meter['status'] === 'assigned' || $meter['status'] === 'installed') {
                $assignedMeters++;
            }
        }
        // Pending verifications equals applications submitted to admin
        $pendingVerifications = is_array($submittedApplications) ? count($submittedApplications) : 0;
        
        // Fetch all clients for assignment dropdown
        $clients = $this->userModel->getUsersByRole('client');
        $installers = array_merge($this->userModel->getUsersByRole('meter_reader'), $this->userModel->getUsersByRole('collector'));
        // Installation submissions (from installers)
        $installationSubmissions = $this->meterInstallationModel->listSubmitted();
        
        $data = [
            'meters' => $meters,
            'submittedApplications' => $submittedApplications,
            'verifiedApplications' => $verifiedApplications,
            'waitingInstallations' => $waitingInstallations,
            'clients' => $clients,
            'installers' => $installers,
            'installationSubmissions' => $installationSubmissions,
            'totalMeters' => $totalMeters,
            'availableMeters' => $availableMeters,
            'assignedMeters' => $assignedMeters,
            'pendingVerifications' => $pendingVerifications,
            'error' => $error,
            'success' => $success
        ];
        
        require_once __DIR__ . '/../views/admin/manage_meters.php';
    }
}
