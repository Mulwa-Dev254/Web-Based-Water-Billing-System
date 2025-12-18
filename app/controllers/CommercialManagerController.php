<?php
// app/controllers/CommercialManagerController.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Meter.php';
require_once __DIR__ . '/../models/User.php'; // For fetching clients and collectors
require_once __DIR__ . '/../models/ServiceRequest.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Client.php';

class CommercialManagerController {
    private Database $db;
    private Auth $auth;
    private Meter $meterModel;
    private User $userModel;
    private ServiceRequest $serviceRequestModel;
    private MeterApplication $meterApplicationModel;
    private Client $clientModel;
    private Service $serviceModel;

    public function __construct(Database $database_instance, Auth $auth_instance) {
        $this->db = $database_instance;
        $this->auth = $auth_instance;
        $this->meterModel = new Meter($this->db);
        $this->userModel = new User($this->db);
        $this->serviceRequestModel = new ServiceRequest($this->db);
        $this->meterApplicationModel = new MeterApplication($this->db);
        $this->clientModel = new Client($this->db);
        $this->serviceModel = new Service($this->db);
    }

    /**
     * Helper to check if the user is a commercial manager.
     * Redirects to login if not.
     */
    private function checkCommercialManagerAuth(): void {
        if (!$this->auth->isLoggedIn() || $this->auth->getUserRole() !== 'commercial_manager') {
            $_SESSION['error_message'] = "Access denied. You must be logged in as a Commercial Manager.";
            header('Location: index.php?page=login');
            exit();
        }
    }

    /**
     * Displays the Commercial Manager dashboard.
     */
    public function dashboard(): void {
        $this->checkCommercialManagerAuth();

        $totalMeters = count($this->meterModel->getAllMeters());
        $availableMeters = count($this->meterModel->getAllMeters('available'));
        $installedMeters = count($this->meterModel->getAllMeters('installed'));
        $pendingSR = count($this->serviceRequestModel->getServiceRequests(null, 'pending'));
        $assignedSR = count($this->serviceRequestModel->getServiceRequests(null, 'assigned'));
        $pendingApps = count($this->meterApplicationModel->getApplicationsByStatus('pending'));

        // Unassigned meters that are ready to apply
        $readyStatuses = ['available','in_stock','functional','available soon'];
        $readyMetersCount = 0;
        foreach ($this->meterModel->getAllMeters() as $m) {
            $st = strtolower($m['status'] ?? '');
            if (empty($m['client_id']) && in_array($st, $readyStatuses, true)) {
                $readyMetersCount++;
            }
        }

        // Build notifications array for dashboard bell
        $notifications = [];
        if ($pendingApps > 0) {
            $notifications[] = [
                'type' => 'applications',
                'title' => 'Pending meter applications',
                'count' => $pendingApps,
                'url' => 'index.php?page=commercial_manager_review_applications'
            ];
        }
        if ($pendingSR > 0) {
            $notifications[] = [
                'type' => 'service_requests',
                'title' => 'Pending service requests',
                'count' => $pendingSR,
                'url' => 'index.php?page=commercial_manager_service_requests'
            ];
        }
        if ($readyMetersCount > 0) {
            $notifications[] = [
                'type' => 'meters',
                'title' => 'Unassigned meters ready to apply',
                'count' => $readyMetersCount,
                'url' => 'index.php?page=commercial_manager_manage_meters'
            ];
        }
        $notificationsCount = 0;
        foreach ($notifications as $n) { $notificationsCount += (int)$n['count']; }

        $data = [
            'totalMeters' => $totalMeters,
            'availableMeters' => $availableMeters,
            'installedMeters' => $installedMeters,
            'pendingServiceRequests' => $pendingSR,
            'assignedServiceRequests' => $assignedSR,
            'pendingApplications' => $pendingApps,
            'serviceRequests' => $pendingSR,
            'notifications' => $notifications,
            'notificationsCount' => $notificationsCount,
            'error' => $_SESSION['error_message'] ?? '',
            'success' => $_SESSION['success_message'] ?? ''
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']);

        require_once __DIR__ . '/../views/commercial_manager/dashboard.php';
    }
    
    /**
     * Review meter applications from clients
     */
    public function reviewApplications(): void {
        $this->checkCommercialManagerAuth();
        
        $error = '';
        $success = '';
        
        // Handle application approval/rejection
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $applicationId = (int)($_POST['application_id'] ?? 0);
            
            if ($applicationId <= 0) {
                $error = "Invalid application ID.";
            } else {
                switch ($action) {
                    case 'approve_application':
                        if ($this->meterApplicationModel->approveApplication($applicationId)) {
                            // Assign meter to client
                            $application = $this->meterApplicationModel->getApplicationById($applicationId);
                            $installationDate = date('Y-m-d'); // Use current date as installation date
                            if ($application) {
                                $clientRecord = $this->clientModel->getClientByUserId($application['client_id']);
                                if (!$clientRecord) {
                                    $error = "Client record not found for the user.";
                                } else {
                                    // meters.client_id references clients.id, map user -> client
                                    if ($this->meterModel->assignMeterToClient($application['meter_id'], (int)$clientRecord['id'], $installationDate)) {
                                        $success = "Application approved and meter assigned successfully.";
                                    } else {
                                        $error = "Application approved but failed to assign meter.";
                                    }
                                }
                            } else {
                                $error = "Application not found.";
                            }
                        } else {
                            $error = "Failed to approve application.";
                        }
                        break;
                        
                    case 'reject_application':
                        $reason = trim($_POST['rejection_reason'] ?? '');
                        if (empty($reason)) {
                            $error = "Please provide a reason for rejection.";
                        } else if ($this->meterApplicationModel->rejectApplication($applicationId, $reason)) {
                            $success = "Application rejected successfully.";
                        } else {
                            $error = "Failed to reject application.";
                        }
                        break;
                        
                    case 'submit_to_admin':
                        if ($this->meterApplicationModel->submitToAdmin($applicationId)) {
                            $success = "Application submitted to admin for review.";
                        } else {
                            $error = "Failed to submit application to admin.";
                        }
                        break;
                        
                    case 'cancel_admin_submission':
                        if ($this->meterApplicationModel->cancelAdminSubmission($applicationId)) {
                            $success = "Application submission to admin canceled.";
                        } else {
                            $error = "Failed to cancel admin submission.";
                        }
                        break;

                    case 'confirm_to_client':
                        $managerId = $_SESSION['user_id'] ?? 0;
                        $notes = trim($_POST['notes'] ?? '');
                        if ($managerId && $this->meterApplicationModel->confirmToClient($applicationId, (int)$managerId, $notes)) {
                            $application = $this->meterApplicationModel->getApplicationById($applicationId);
                            if ($application) {
                                $serviceRow = $this->serviceModel->getServiceByName('Meter Installation');
                                if (!$serviceRow) {
                                    $this->serviceModel->addService('Meter Installation', 'Schedule meter installation', 0.0, 1);
                                    $serviceRow = $this->serviceModel->getServiceByName('Meter Installation');
                                }
                                $serviceId = (int)($serviceRow['id'] ?? 0);
                                $clientId = (int)$application['client_id'];
                                $meterId = (int)$application['meter_id'];
                                $this->serviceRequestModel->createServiceRequest($clientId, $serviceId, 'Meter application confirmed. Schedule installation.', $meterId);

                                $clientUser = $this->userModel->getUserById($clientId);
                                $phone = (string)($clientUser['contact_phone'] ?? '');
                                if (!empty($phone)) {
                                    $sms = new \SmsNotification();
                                    $meterSerial = (string)($application['meter_serial'] ?? '');
                                    $msg = 'Your meter application has been confirmed. We will schedule installation for meter ' . $meterSerial . '.';
                                    $sms->recordNotification($phone, $msg, 'installation_confirmation', 'sent', (int)$applicationId);
                                }
                            }
                            $success = "Client confirmed and installation scheduling initiated.";
                        } else {
                            $error = "Failed to confirm to client. Ensure admin has verified the application.";
                        }
                        break;
                        
                    default:
                        $error = "Invalid action.";
                }
            }
        }
        
        // Get applications split cleanly to avoid duplicates
        $pendingApplications = $this->meterApplicationModel->getApplicationsByStatus('pending');
        // Processed tab should only show approved applications
        $processedApplications = $this->meterApplicationModel->getApplicationsByStatus('approved');
        
        // Get client and meter details for each application
        foreach ($pendingApplications as $idx => $application) {
            $pendingApplications[$idx]['client'] = $this->userModel->getUserById($application['client_id']);
            $pendingApplications[$idx]['meter'] = $this->meterModel->getMeterById($application['meter_id']);
        }
        
        foreach ($processedApplications as $idx => $application) {
            $processedApplications[$idx]['client'] = $this->userModel->getUserById($application['client_id']);
            $processedApplications[$idx]['meter'] = $this->meterModel->getMeterById($application['meter_id']);
        }
        
        $data = [
            'pendingApplications' => $pendingApplications,
            'processedApplications' => $processedApplications,
            'error' => $error ?: ($_SESSION['error_message'] ?? ''),
            'success' => $success ?: ($_SESSION['success_message'] ?? '')
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']);
        
        require_once __DIR__ . '/../views/commercial_manager/review_applications.php';
    }

    /**
     * Manages meters: add, update, delete, assign.
     */
    public function manageMeters(): void {
        $this->checkCommercialManagerAuth();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            switch ($action) {
                case 'add_meter':
                    $serialNumber = trim($_POST['serial_number'] ?? '');
                    $meterType = trim($_POST['meter_type'] ?? 'residential');
                    $initialReading = filter_var($_POST['initial_reading'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $status = trim($_POST['status'] ?? 'functional');
                    
                    // Ensure meter_type is not empty
                    if (empty($meterType)) {
                        $meterType = 'residential';
                    }
                    
                    // Ensure status is not empty
                    if (empty($status)) {
                        $status = 'functional';
                    }
                    
                    $photoUrl = null;

                    // Handle image upload
                    if (isset($_FILES['meter_image']) && $_FILES['meter_image']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/meters/';
                        
                        // Create directory if it doesn't exist
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        $fileName = uniqid('meter_') . '_' . basename($_FILES['meter_image']['name']);
                        $uploadFile = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['meter_image']['tmp_name'], $uploadFile)) {
                            // Use absolute URL for photo_url to ensure proper display
                            $photoUrl = '/water_billing_system/public/uploads/meters/' . $fileName;
                        } else {
                            $error = "Failed to upload meter image.";
                        }
                    }

                    if (empty($serialNumber) || empty($meterType) || $initialReading === false) {
                        $error = "Please fill all required fields for adding a meter.";
                    } else {
                        if ($this->meterModel->addMeter($serialNumber, $meterType, $initialReading, $status, $photoUrl)) {
                            $success = "Meter added successfully!";
                        } else {
                            $error = "Failed to add meter. It might already exist or there was a database error.";
                        }
                    }
                    break;

                case 'update_meter':
                    $meterId = filter_var($_POST['meter_id'] ?? 0, FILTER_VALIDATE_INT);
                    $serialNumber = trim($_POST['serial_number'] ?? '');
                    $meterType = trim($_POST['meter_type'] ?? '');
                    $initialReading = filter_var($_POST['initial_reading'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $status = trim($_POST['status'] ?? '');
                    $photoUrl = trim($_POST['photo_url'] ?? null);
                    $gpsLocation = trim($_POST['gps_location'] ?? null);
                    $clientId = filter_var($_POST['client_id'] ?? null, FILTER_VALIDATE_INT);
                    $installationDate = trim($_POST['installation_date'] ?? null);
                    $assignedCollectorId = filter_var($_POST['assigned_collector_id'] ?? null, FILTER_VALIDATE_INT);

                    if ($meterId <= 0 || empty($serialNumber) || empty($meterType) || $initialReading === false || empty($status)) {
                        $error = "Invalid meter data for update.";
                    } else {
                        if (isset($_FILES['meter_image']) && $_FILES['meter_image']['error'] === UPLOAD_ERR_OK) {
                            $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/meters/';
                            if (!file_exists($uploadDir)) { mkdir($uploadDir, 0777, true); }
                            $fileName = uniqid('meter_') . '_' . basename($_FILES['meter_image']['name']);
                            $uploadFile = $uploadDir . $fileName;
                            if (move_uploaded_file($_FILES['meter_image']['tmp_name'], $uploadFile)) {
                                $photoUrl = '/water_billing_system/public/uploads/meters/' . $fileName;
                            }
                        }
                        if ($this->meterModel->updateMeter($meterId, $serialNumber, $meterType, $initialReading, $status, $photoUrl, $gpsLocation, $clientId, $installationDate, $assignedCollectorId)) {
                            $success = "Meter updated successfully!";
                        } else {
                            $error = "Failed to update meter.";
                        }
                    }
                    break;

                case 'delete_meter':
                    $meterId = filter_var($_POST['meter_id'] ?? 0, FILTER_VALIDATE_INT);
                    if ($meterId > 0) {
                        if ($this->meterModel->deleteMeter($meterId)) {
                            $success = "Meter deleted successfully!";
                        } else {
                            $error = "Failed to delete meter. It might be assigned to a client or have associated readings.";
                        }
                    } else {
                        $error = "Invalid meter ID for deletion.";
                    }
                    break;

                case 'assign_meter_to_client':
                    $meterId = filter_var($_POST['meter_id'] ?? 0, FILTER_VALIDATE_INT);
                    $clientId = filter_var($_POST['client_id'] ?? 0, FILTER_VALIDATE_INT);
                    if ($meterId <= 0 || $clientId <= 0) {
                        $error = "Invalid data for applying meter to client.";
                    } else {
                        $client = $this->userModel->getUserById($clientId);
                        if (!$client) {
                            $error = "Client not found in the system.";
                        } else {
                            $ok = $this->meterApplicationModel->createApplication([
                                'client_id' => $clientId,
                                'meter_id' => $meterId
                            ]);
                            if ($ok) {
                                $success = "Meter application created for client. Pending review.";
                            } else {
                                $error = "Duplicate or failed application. Check pending applications.";
                            }
                        }
                    }
                    break;
                
                case 'assign_meter_to_collector':
                    $meterId = filter_var($_POST['meter_id'] ?? 0, FILTER_VALIDATE_INT);
                    $collectorId = filter_var($_POST['collector_id'] ?? 0, FILTER_VALIDATE_INT);

                    if ($meterId <= 0 || $collectorId <= 0) {
                        $error = "Invalid data for assigning meter to collector.";
                    } else {
                        if ($this->meterModel->assignMeterToCollector($meterId, $collectorId)) {
                            $success = "Meter assigned to collector successfully!";
                        } else {
                            $error = "Failed to assign meter to collector.";
                        }
                    }
                    break;

                case 'unassign_meter_from_client':
                    $meterId = filter_var($_POST['meter_id'] ?? 0, FILTER_VALIDATE_INT);
                    if ($meterId <= 0) {
                        $error = "Invalid meter ID for unassignment.";
                    } else {
                        if ($this->meterModel->unassignMeterFromClient($meterId)) {
                            $success = "Meter unassigned from client successfully.";
                        } else {
                            $error = "Failed to unassign meter from client.";
                        }
                    }
                    break;
            }
        }

        $_SESSION['error_message'] = $error;
        $_SESSION['success_message'] = $success;

        $meters = $this->meterModel->getAllMeters();
        $pendingAppsList = $this->meterApplicationModel->getApplicationsByStatus('pending');
        $approvedAppsList = $this->meterApplicationModel->getApplicationsByStatus('approved');
        $metersWithPendingApp = array_map('intval', array_column(is_array($pendingAppsList) ? $pendingAppsList : [], 'meter_id'));
        $metersWithApprovedApp = array_map('intval', array_column(is_array($approvedAppsList) ? $approvedAppsList : [], 'meter_id'));
        $clients = $this->userModel->getUsersByRole('client'); // Get all users with 'client' role
        $collectors = $this->userModel->getUsersByRole('collector'); // Get all users with 'collector' role

        $data = [
            'meters' => $meters,
            'metersWithPendingApp' => $metersWithPendingApp,
            'metersWithApprovedApp' => $metersWithApprovedApp,
            'clients' => $clients,
            'collectors' => $collectors,
            'error' => $_SESSION['error_message'] ?? '',
            'success' => $_SESSION['success_message'] ?? ''
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']);

        require_once __DIR__ . '/../views/commercial_manager/manage_meters.php';
    }

    /**
     * Reviews service requests and assigns them to collectors.
     * This method handles the service request review process.
     */
    public function reviewServiceRequests(): void {
        $this->checkCommercialManagerAuth();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $requestId = filter_var($_POST['request_id'] ?? 0, FILTER_VALIDATE_INT);

            if ($requestId <= 0) {
                $error = "Invalid request ID.";
            } else {
                switch ($action) {
                    case 'approve_request':
                        $collectorId = filter_var($_POST['collector_id'] ?? 0, FILTER_VALIDATE_INT);
                        if ($collectorId <= 0) {
                            $error = "Please select a collector to assign.";
                        } else {
                            if ($this->serviceRequestModel->updateServiceRequestStatusAndAssignCollector($requestId, 'assigned', $collectorId)) {
                                $success = "Service request approved and assigned to collector successfully!";
                            } else {
                                $error = "Failed to approve and assign service request.";
                            }
                        }
                        break;
                    case 'reject_request':
                        if ($this->serviceRequestModel->updateServiceRequestStatus($requestId, 'rejected')) {
                            $success = "Service request rejected successfully.";
                        } else {
                            $error = "Failed to reject service request.";
                        }
                        break;
                }
            }
        }

        $_SESSION['error_message'] = $error;
        $_SESSION['success_message'] = $success;

        $pendingRequests = $this->serviceRequestModel->getServiceRequests(null, 'pending');
        $collectors = $this->userModel->getUsersByRole('collector');

        $data = [
            'pendingRequests' => $pendingRequests,
            'collectors' => $collectors,
            'error' => $_SESSION['error_message'] ?? '',
            'success' => $_SESSION['success_message'] ?? ''
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']);

        require_once __DIR__ . '/../views/commercial_manager/review_service_requests.php';
    }

    // Commercial Manager Profile (can reuse User model)
    public function profile(): void {
        $this->checkCommercialManagerAuth();
        $userId = $_SESSION['user_id'] ?? null;
        $data = [];
        if ($userId) {
            $data['user'] = $this->auth->getUserById($userId); // Get full user details
        }
        require_once __DIR__ . '/../views/commercial_manager/profile.php';
    }

    public function reports(): void {
        $this->checkCommercialManagerAuth();

        $reportType = isset($_GET['report_type']) ? trim($_GET['report_type']) : 'overview';
        $startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
        $endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

        $totalMeters = count($this->meterModel->getAllMeters());
        $availableMeters = count($this->meterModel->getAllMeters('available'));
        $assignedMeters = count($this->meterModel->getAllMeters('assigned'));
        $assignedToCollectorMeters = count($this->meterModel->getAllMeters('assigned_to_collector'));
        $waitingInstallationMeters = count($this->meterModel->getAllMeters('waiting_installation'));
        $installedMeters = count($this->meterModel->getAllMeters('installed'));
        $verifiedMeters = count($this->meterModel->getAllMeters('verified'));

        $pendingApps = count($this->meterApplicationModel->getApplicationsByStatus('pending'));
        $approvedApps = count($this->meterApplicationModel->getApplicationsByStatus('approved'));
        $rejectedApps = count($this->meterApplicationModel->getApplicationsByStatus('rejected'));

        $pendingSR = count($this->serviceRequestModel->getServiceRequests(null, 'pending'));
        $assignedSR = count($this->serviceRequestModel->getServiceRequests(null, 'assigned'));
        $servicedSR = count($this->serviceRequestModel->getServiceRequests(null, 'serviced'));
        $completedSR = count($this->serviceRequestModel->getServiceRequests(null, 'completed'));
        $cancelledSR = count($this->serviceRequestModel->getServiceRequests(null, 'cancelled'));

        $recentApplications = [];
        $recentServiceRequests = [];
        $collectorPerformance = [];

        try {
            $sqlApps = 'SELECT ma.id, ma.client_id, ma.meter_id, ma.status, ma.application_date, u.username AS client_name, m.serial_number AS meter_serial
                        FROM meter_applications ma
                        JOIN users u ON ma.client_id = u.id
                        JOIN meters m ON ma.meter_id = m.id';
            $conditionsApps = [];
            $paramsApps = [];
            if ($startDate !== '' && $endDate !== '') {
                $conditionsApps[] = 'ma.application_date BETWEEN ? AND ?';
                $paramsApps[] = $startDate;
                $paramsApps[] = $endDate;
            }
            if (!empty($conditionsApps)) {
                $sqlApps .= ' WHERE ' . implode(' AND ', $conditionsApps);
            }
            $sqlApps .= ' ORDER BY ma.application_date DESC LIMIT 10';
            $this->db->query($sqlApps);
            if (!empty($paramsApps)) { $this->db->bind($paramsApps); }
            $recentApplications = $this->db->resultSet();
            $this->db->closeStmt();

            $sqlSR = 'SELECT sr.id, sr.client_id, sr.service_id, sr.status, sr.request_date, sr.assigned_to_collector_id,
                             u.username AS client_name, s.service_name, c.username AS collector_name
                      FROM service_requests sr
                      JOIN users u ON sr.client_id = u.id
                      LEFT JOIN services s ON sr.service_id = s.id
                      LEFT JOIN users c ON sr.assigned_to_collector_id = c.id';
            $conditionsSR = [];
            $paramsSR = [];
            if ($startDate !== '' && $endDate !== '') {
                $conditionsSR[] = 'sr.request_date BETWEEN ? AND ?';
                $paramsSR[] = $startDate;
                $paramsSR[] = $endDate;
            }
            if (!empty($conditionsSR)) {
                $sqlSR .= ' WHERE ' . implode(' AND ', $conditionsSR);
            }
            $sqlSR .= ' ORDER BY sr.request_date DESC LIMIT 10';
            $this->db->query($sqlSR);
            if (!empty($paramsSR)) { $this->db->bind($paramsSR); }
            $recentServiceRequests = $this->db->resultSet();
            $this->db->closeStmt();

            $sqlPerf = 'SELECT sr.assigned_to_collector_id AS collector_id,
                               COUNT(*) AS total_assigned,
                               SUM(CASE WHEN sr.status IN ("serviced","completed") THEN 1 ELSE 0 END) AS completed_count
                        FROM service_requests sr
                        WHERE sr.assigned_to_collector_id IS NOT NULL';
            $paramsPerf = [];
            if ($startDate !== '' && $endDate !== '') {
                $sqlPerf .= ' AND sr.request_date BETWEEN ? AND ?';
                $paramsPerf[] = $startDate;
                $paramsPerf[] = $endDate;
            }
            $sqlPerf .= ' GROUP BY sr.assigned_to_collector_id ORDER BY completed_count DESC';
            $this->db->query($sqlPerf);
            if (!empty($paramsPerf)) { $this->db->bind($paramsPerf); }
            $collectorPerformance = $this->db->resultSet();
            $this->db->closeStmt();
        } catch (\Exception $e) {
        }

        $data = [
            'report_type' => $reportType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'kpis' => [
                'total_meters' => $totalMeters,
                'available_meters' => $availableMeters,
                'assigned_meters' => $assignedMeters,
                'assigned_to_collector_meters' => $assignedToCollectorMeters,
                'waiting_installation_meters' => $waitingInstallationMeters,
                'installed_meters' => $installedMeters,
                'verified_meters' => $verifiedMeters,
                'pending_applications' => $pendingApps,
                'approved_applications' => $approvedApps,
                'rejected_applications' => $rejectedApps,
                'pending_service_requests' => $pendingSR,
                'assigned_service_requests' => $assignedSR,
                'serviced_service_requests' => $servicedSR,
                'completed_service_requests' => $completedSR,
                'cancelled_service_requests' => $cancelledSR
            ],
            'recent_applications' => $recentApplications,
            'recent_service_requests' => $recentServiceRequests,
            'collector_performance' => $collectorPerformance,
            'error' => $_SESSION['error_message'] ?? '',
            'success' => $_SESSION['success_message'] ?? ''
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']);
        require_once __DIR__ . '/../views/commercial_manager/reports.php';
    }
}
