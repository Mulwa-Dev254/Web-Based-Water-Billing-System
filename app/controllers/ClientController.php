<?php
// app/controllers/ClientController.php

require_once '../app/core/Database.php';
require_once '../app/core/Auth.php';
require_once '../app/models/Client.php';
require_once '../app/models/ClientPlan.php';
require_once '../app/models/Payment.php';
require_once '../app/models/ClientService.php';
require_once '../app/models/Service.php';
require_once '../app/models/BillingPlan.php';
require_once '../app/models/ServiceRequest.php';
require_once '../app/models/MeterApplication.php';
require_once '../app/models/Meter.php';
require_once '../app/models/VerifiedMeter.php';
require_once '../app/models/Bill.php';
require_once '../app/models/ClientBill.php';
require_once '../app/models/SmsNotification.php';
require_once '../app/models/MeterReading.php';
require_once '../app/models/PlanUpgradePrompt.php';
require_once '../app/models/User.php';

class ClientController {
    private Database $db;
    private Auth $auth;
    private Client $clientModel;
    private ClientPlan $clientPlanModel;
    private Payment $paymentModel;
    private ClientService $clientServiceModel;
    private Service $serviceModel;
    private BillingPlan $billingPlanModel;
    private ServiceRequest $serviceRequestModel;
    private MeterApplication $meterApplicationModel;
    private Meter $meterModel;
    private VerifiedMeter $verifiedMeterModel;
    private MeterReading $meterReadingModel;
    private Bill $billModel;
    private SmsNotification $smsNotificationModel;
    private PlanUpgradePrompt $planUpgradePromptModel;
    private ClientBill $clientBillModel;
    private User $userModel;

    public function __construct(Database $database_instance, Auth $auth_instance) {
        $this->db = $database_instance;
        $this->auth = $auth_instance;
        $this->clientModel = new Client($this->db);
        $this->clientPlanModel = new ClientPlan($this->db);
        $this->paymentModel = new Payment($this->db);
        $this->clientServiceModel = new ClientService($this->db);
        $this->serviceModel = new Service($this->db);
        $this->billingPlanModel = new BillingPlan($this->db);
        $this->serviceRequestModel = new ServiceRequest($this->db);
        $this->meterApplicationModel = new MeterApplication($this->db);
        $this->meterModel = new Meter($this->db);
        $this->verifiedMeterModel = new VerifiedMeter($this->db);
        $this->meterReadingModel = new MeterReading($this->db);
        $this->billModel = new Bill($this->db);
        $this->smsNotificationModel = new SmsNotification();
        $this->planUpgradePromptModel = new PlanUpgradePrompt($this->db);
        $this->clientBillModel = new ClientBill($this->db);
        $this->userModel = new User($this->db);
    }

    private function requireClientAuth(): void {
        if (!$this->auth->isLoggedIn() || $this->auth->getUserRole() !== 'client') {
            header('Location: index.php?page=login');
            exit();
        }
    }
    private function view($view, $data = []) {
        $viewFile = '../app/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            extract($data);
            require_once $viewFile;
        } else {
            die("View {$view} not found");
        }
    }
    
    public function meters(): void {
        $this->requireClientAuth();
        $userId = $_SESSION['user_id'] ?? null;
        $data = [
            'meters' => []
        ];
        if ($userId) {
            $clientInfo = $this->clientModel->getClientByUserId($userId) ?? [];
            $data['clientInfo'] = $clientInfo;
            $verified = $this->verifiedMeterModel->getByClientId($userId);
            $meters = !empty($verified) ? $verified : $this->meterModel->getMetersAssignedToClient($userId);

            $meterIds = [];
            $normalized = [];
            foreach ($meters as $m) {
                $mid = $m['meter_id'] ?? ($m['id'] ?? null);
                if ($mid !== null) {
                    $meterIds[] = (int)$mid;
                    $normalized[] = [
                        'id' => (int)$mid,
                        'serial' => $m['serial_number'] ?? ($m['meter_serial'] ?? '')
                    ];
                }
            }
            $latestMap = !empty($meterIds) ? $this->meterReadingModel->getLastReadingsForMeters($meterIds) : [];
            foreach ($meters as &$m) {
                $mid = $m['meter_id'] ?? ($m['id'] ?? null);
                if ($mid !== null && isset($latestMap[(int)$mid])) {
                    $m['current_reading'] = $latestMap[(int)$mid]['reading_value'];
                    $m['current_reading_date'] = $latestMap[(int)$mid]['reading_date'];
                }
            }
            unset($m);
            $data['meters'] = $meters;

            $data['billingSummary'] = $this->billModel->getClientBillingSummary($userId) ?? [];
            $data['recentBills'] = $this->billModel->getRecentBillsByClient($userId, 5) ?? [];
            $payments = $this->paymentModel->getPaymentHistoryByUserId((int)$userId) ?? [];
            $data['recentPayments'] = array_slice($payments, 0, 7);

            // Build billing info influenced by payment statuses
            $billsAll = $this->billModel->getBillsByClientId((int)$userId) ?? [];
            $totalBills = !empty($data['billingSummary']['total_bills']) ? (int)$data['billingSummary']['total_bills'] : count($billsAll);
            $totalAmountAllBills = 0.0;
            foreach ($billsAll as $b) { $totalAmountAllBills += (float)($b['amount_due'] ?? 0); }
            $sumCompleted = 0.0; $sumPending = 0.0; $countPending = 0;
            foreach ($payments as $p) {
                $type = strtolower($p['type'] ?? '');
                if ($type !== 'bill_payment') { continue; }
                $status = strtolower($p['status'] ?? 'pending');
                $amt = (float)($p['amount'] ?? 0);
                if (in_array($status, ['completed','confirmed_and_verified','paid'], true)) { $sumCompleted += $amt; }
                elseif (in_array($status, ['pending','pending_payment'], true)) { $sumPending += $amt; $countPending++; }
            }
            $effectiveDue = max(0.0, $totalAmountAllBills - ($sumCompleted + $sumPending));
            $outstandingEffective = max(0.0, $totalAmountAllBills - $sumCompleted);
            $data['billingInfo'] = [
                'total_bills' => $totalBills,
                'total_due_effective' => $effectiveDue,
                'outstanding_effective' => $outstandingEffective,
                'total_paid_payments' => $sumCompleted,
                'pending_payments_amount' => $sumPending,
                'pending_payments_count' => $countPending,
                'service_due_amount' => 0.0,
                'service_paid_amount' => 0.0,
                'service_pending_count' => 0,
            ];

            $servicePaid = 0.0; $servicePending = 0.0; $servicePendingCount = 0;
            foreach ($payments as $p) {
                $type = strtolower($p['type'] ?? '');
                if ($type !== 'service_payment') { continue; }
                $status = strtolower($p['status'] ?? 'pending');
                $amt = (float)($p['amount'] ?? 0);
                if (in_array($status, ['completed','confirmed_and_verified','paid'], true)) { $servicePaid += $amt; }
                elseif (in_array($status, ['pending','pending_payment'], true)) { $servicePending += $amt; $servicePendingCount++; }
            }
            $data['billingInfo']['service_due_amount'] = $servicePending;
            $data['billingInfo']['service_paid_amount'] = $servicePaid;
            $data['billingInfo']['service_pending_count'] = $servicePendingCount;

            $aggregateDaily = [];
            $aggregateMonthly = [];
            $aggregateAnnual = [];
            $seriesDaily = [];
            $seriesMonthly = [];
            $seriesAnnual = [];
            foreach ($normalized as $nm) {
                $readings = $this->meterReadingModel->getReadingsByMeterId((int)$nm['id']);
                $daily = [];
                $monthly = [];
                $annual = [];
                for ($i = 1; $i < count($readings); $i++) {
                    $prev = $readings[$i-1];
                    $cur = $readings[$i];
                    $consumption = (float)$cur['reading_value'] - (float)$prev['reading_value'];
                    if ($consumption < 0) { $consumption = 0.0; }
                    $d = substr((string)$cur['reading_date'], 0, 10);
                    $mKey = substr((string)$cur['reading_date'], 0, 7);
                    $yKey = substr((string)$cur['reading_date'], 0, 4);
                    $daily[$d] = ($daily[$d] ?? 0) + $consumption;
                    $monthly[$mKey] = ($monthly[$mKey] ?? 0) + $consumption;
                    $annual[$yKey] = ($annual[$yKey] ?? 0) + $consumption;
                    $aggregateDaily[$d] = ($aggregateDaily[$d] ?? 0) + $consumption;
                    $aggregateMonthly[$mKey] = ($aggregateMonthly[$mKey] ?? 0) + $consumption;
                    $aggregateAnnual[$yKey] = ($aggregateAnnual[$yKey] ?? 0) + $consumption;
                }
                $seriesDaily[(string)$nm['id']] = $daily;
                $seriesMonthly[(string)$nm['id']] = $monthly;
                $seriesAnnual[(string)$nm['id']] = $annual;
            }

            $makeAligned = function(array $agg, array $byMeter, int $window, callable $labelFmt) {
                $keys = array_keys($agg);
                sort($keys);
                $slice = array_slice($keys, max(0, count($keys) - $window));
                $labels = [];
                $values = [];
                foreach ($slice as $k) {
                    $labels[] = $labelFmt($k);
                    $values[] = (float)($agg[$k] ?? 0);
                }
                $aligned = [];
                foreach ($byMeter as $mid => $series) {
                    $vals = [];
                    foreach ($slice as $k) { $vals[] = (float)($series[$k] ?? 0); }
                    $aligned[$mid] = $vals;
                }
                return [$labels, $values, $aligned, $slice];
            };

            list($labelsD, $valuesD, $alignedD) = $makeAligned($aggregateDaily, $seriesDaily, 12, function($d){ return date('M d', strtotime($d)); });
            list($labelsM, $valuesM, $alignedM) = $makeAligned($aggregateMonthly, $seriesMonthly, 12, function($m){ return date('M', strtotime($m.'-01')); });
            list($labelsY, $valuesY, $alignedY) = $makeAligned($aggregateAnnual, $seriesAnnual, 5, function($y){ return (string)$y; });

            if (empty($labelsD)) {
                $labelsD = [];
                $valuesD = [];
                $sliceD = [];
                for ($i = 5; $i >= 0; $i--) {
                    $day = date('Y-m-d', strtotime("-{$i} days"));
                    $labelsD[] = date('M d', strtotime($day));
                    $valuesD[] = 0;
                    $sliceD[] = $day;
                }
                $alignedD = [];
                foreach ($seriesDaily as $mid => $series) {
                    $vals = [];
                    foreach ($sliceD as $k) { $vals[] = 0; }
                    $alignedD[$mid] = $vals;
                }
            }

            $data['trendLabels'] = $labelsD;
            $data['trendValues'] = $valuesD;
            $data['trendSeries'] = $alignedD;
            $data['trendLabelsMonthly'] = $labelsM;
            $data['trendValuesMonthly'] = $valuesM;
            $data['trendSeriesMonthly'] = $alignedM;
            $data['trendLabelsAnnual'] = $labelsY;
            $data['trendValuesAnnual'] = $valuesY;
            $data['trendSeriesAnnual'] = $alignedY;
            $data['trendMeters'] = array_map(function($nm){ return ['id'=>$nm['id'],'serial'=>$nm['serial']]; }, $normalized);

            $phone = $clientInfo['contact_phone'] ?? '';
            $data['alerts'] = [];
            if (!empty($phone)) {
                $data['alerts'] = $this->smsNotificationModel->getNotificationsByPhoneNumber($phone, 5);
            }
        }
        require_once '../app/views/client/meters.php';
    }
    
    public function consumption(): void {
        $this->requireClientAuth();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $data = [ 'meters' => [], 'aggregate' => [ 'daily' => [], 'monthly' => [], 'annual' => [] ], 'years' => [] ];
        $clientInfo = $this->clientModel->getClientByUserId($userId) ?? [];
        $data['clientInfo'] = $clientInfo;
        // Prefer verified meters, fallback to assigned
        $verified = $this->verifiedMeterModel->getByClientId($userId);
        $meters = !empty($verified) ? $verified : $this->meterModel->getMetersAssignedToClient($userId);
        // Normalize meter list and filter installed/verified/assigned
        $normalized = [];
        foreach ($meters as $m) {
            $mid = (int)($m['meter_id'] ?? ($m['id'] ?? 0));
            $status = strtolower($m['meter_table_status'] ?? ($m['status'] ?? ''));
            if ($mid <= 0) { continue; }
            if (!in_array($status, ['installed','verified','assigned'], true)) { continue; }
            $normalized[] = [
                'id' => $mid,
                'serial_number' => $m['meter_serial'] ?? ($m['serial_number'] ?? ''),
                'installed_at' => $m['verification_date'] ?? ($m['installation_date'] ?? null),
            ];
        }
        // Build consumption series per meter from meter readings
        $aggregateDaily = [];
        $aggregateMonthly = [];
        $aggregateAnnual = [];
        foreach ($normalized as &$nm) {
            $readings = $this->meterReadingModel->getReadingsByMeterId((int)$nm['id']); // oldest to latest
            $daily = [];
            $monthly = [];
            $annual = [];
            for ($i=1; $i<count($readings); $i++) {
                $prev = $readings[$i-1];
                $cur = $readings[$i];
                $consumption = max(0.0, (float)$cur['reading_value'] - (float)$prev['reading_value']);
                $d = substr((string)$cur['reading_date'], 0, 10);
                $mKey = substr((string)$cur['reading_date'], 0, 7);
                $yKey = substr((string)$cur['reading_date'], 0, 4);
                $daily[$d] = ($daily[$d] ?? 0) + $consumption;
                $monthly[$mKey] = ($monthly[$mKey] ?? 0) + $consumption;
                $annual[$yKey] = ($annual[$yKey] ?? 0) + $consumption;
                $aggregateDaily[$d] = ($aggregateDaily[$d] ?? 0) + $consumption;
                $aggregateMonthly[$mKey] = ($aggregateMonthly[$mKey] ?? 0) + $consumption;
                $aggregateAnnual[$yKey] = ($aggregateAnnual[$yKey] ?? 0) + $consumption;
            }
            $nm['series'] = [ 'daily' => $daily, 'monthly' => $monthly, 'annual' => $annual ];
        }
        unset($nm);
        $data['meters'] = $normalized;
        $data['aggregate'] = [ 'daily' => $aggregateDaily, 'monthly' => $aggregateMonthly, 'annual' => $aggregateAnnual ];
        $data['years'] = array_values(array_unique(array_map('intval', array_keys($aggregateAnnual))));
        sort($data['years']);
        $this->view('client/consumption', $data);
    }
    
    public function applyMeter(): void {
        $this->requireClientAuth();
        $userId = $_SESSION['user_id'] ?? null;
        $data = [
            'message' => '',
            'error' => '',
            'availableMeters' => [],
            'pendingApplications' => []
        ];
        
        // Handle meter application submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_meter'])) {
            $meterId = filter_var($_POST['meter_id'] ?? 0, FILTER_VALIDATE_INT);
            
            if ($meterId > 0) {
                // Check if client has already applied for this meter
                if ($this->meterApplicationModel->checkDuplicateApplication($userId, $meterId)) {
                    $data['error'] = "You have already applied for this meter. You cannot apply for the same meter twice.";
                } else {
                    $result = $this->meterApplicationModel->createApplication([
                        'client_id' => $userId,
                        'meter_id' => $meterId,
                        'status' => 'pending',
                        'application_date' => date('Y-m-d H:i:s')
                    ]);
                    
                    if ($result) {
                        $data['message'] = "Your meter application has been submitted successfully! Status: Applied";
                    } else {
                        $data['error'] = "Failed to submit your application. Please try again.";
                    }
                }
            } else {
                $data['error'] = "Invalid meter selected.";
            }
        }
        
        // Handle application cancellation
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_application'])) {
            $applicationId = filter_var($_POST['application_id'] ?? 0, FILTER_VALIDATE_INT);
            
            if ($applicationId > 0) {
                $result = $this->meterApplicationModel->cancelApplication($applicationId, $userId);
                
                if ($result) {
                    $data['message'] = "Your meter application has been canceled successfully.";
                } else {
                    $data['error'] = "Failed to cancel your application. Please try again.";
                }
            } else {
                $data['error'] = "Invalid application selected.";
            }
        }
        
        // Get available meters (include user's own unassigned client meters)
        $data['availableMeters'] = $this->meterModel->getAvailableMetersForClient((int)$userId);
        
        // Get pending applications for this client
        if ($userId) {
            $data['pendingApplications'] = $this->meterApplicationModel->getApplicationsByClientId($userId);
        }
        
        require_once '../app/views/client/apply_meter.php';
    }

    /**
     * Allow clients to add their own meter, mirroring manager add flow.
     * After successful add, auto-create an application and redirect to apply page.
     */
    public function addClientMeter(): void {
        $this->requireClientAuth();
        $userId = $_SESSION['user_id'] ?? null;
        $data = [
            'message' => '',
            'error' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_client_meter') {
            $serialNumber = trim($_POST['serial_number'] ?? '');
            $meterType = trim($_POST['meter_type'] ?? 'residential');
            $initialReading = filter_var($_POST['initial_reading'] ?? 0, FILTER_VALIDATE_FLOAT);
            $status = trim($_POST['status'] ?? 'functional');
            $gpsLocation = trim($_POST['gps_location'] ?? null);
            $photoUrl = null;

            // Validate allowed types and statuses
            $allowedTypes = ['residential','commercial','industrial'];
            if (!in_array(strtolower($meterType), $allowedTypes, true)) {
                $meterType = 'residential';
            }
            $allowedStatuses = ['functional','available','in_stock'];
            if (!in_array(strtolower($status), $allowedStatuses, true)) {
                $status = 'functional';
            }

            // Basic validations
            if ($initialReading === false || $initialReading < 0) {
                $data['error'] = 'Initial reading must be a non-negative number.';
            }
            if (strlen($serialNumber) < 3) {
                $data['error'] = 'Serial number must be at least 3 characters.';
            }
            // Duplicate serial check
            if (empty($data['error']) && $this->meterModel->existsSerial($serialNumber)) {
                $data['error'] = 'A meter with this serial number already exists.';
            }

            if (isset($_FILES['meter_image']) && $_FILES['meter_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/meters/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                // Basic upload validation: size and extension
                $maxSize = 2 * 1024 * 1024; // 2MB
                $size = (int)($_FILES['meter_image']['size'] ?? 0);
                $ext = strtolower(pathinfo($_FILES['meter_image']['name'], PATHINFO_EXTENSION));
                $allowedExt = ['jpg','jpeg','png'];
                if ($size > $maxSize) {
                    $data['error'] = 'Image file is too large (max 2MB).';
                } elseif (!in_array($ext, $allowedExt, true)) {
                    $data['error'] = 'Invalid image format. Only JPG and PNG are allowed.';
                } else {
                    $safeBase = preg_replace('/[^a-zA-Z0-9_-]/','', pathinfo($_FILES['meter_image']['name'], PATHINFO_FILENAME));
                    $fileName = uniqid('meter_') . '_' . $safeBase . '.' . $ext;
                    $uploadFile = $uploadDir . $fileName;
                    if (move_uploaded_file($_FILES['meter_image']['tmp_name'], $uploadFile)) {
                        $photoUrl = '/water_billing_system/public/uploads/meters/' . $fileName;
                    } else {
                        $data['error'] = 'Failed to upload meter image.';
                    }
                }
            }

            if (empty($data['error'])) {
                $insertId = $this->meterModel->addClientMeter($serialNumber, $meterType, (float)$initialReading, $status, $photoUrl, $gpsLocation, (int)$userId);
                if ($insertId > 0) {
                    // Auto-create application for the newly added meter
                    $appCreated = $this->meterApplicationModel->createApplication([
                        'client_id' => (int)$userId,
                        'meter_id' => (int)$insertId,
                        'status' => 'pending'
                    ]);
                    if ($appCreated) {
                        $_SESSION['message'] = 'Meter added successfully and application submitted.';
                        header('Location: index.php?page=client_apply_meter');
                        exit();
                    } else {
                        $data['message'] = 'Meter added, but failed to submit application. Please apply manually.';
                    }
                } else {
                    $data['error'] = 'Failed to add your meter. It might already exist or there was a database error.';
                }
            }
        }

        require_once '../app/views/client/add_meter.php';
    }

    public function dashboard(): void {
        $this->requireClientAuth();

        $userId = $_SESSION['user_id'] ?? null;
        $data = [
            'clientInfo' => [],
            'clientPlans' => [],
            'clientServices' => [],
            'recentPayments' => [],
            'serviceRequests' => [],
            'meterApplications' => [],
            'myMeters' => []
        ];

        if ($userId) {
            $data['clientInfo'] = $this->clientModel->getClientByUserId($userId);
            $data['clientPlans'] = $this->clientPlanModel->getClientPlansByUserId($userId);
            $data['clientServices'] = $this->clientServiceModel->getClientServicesByUserId($userId);
            $data['recentPayments'] = $this->paymentModel->getPaymentHistoryByUserId($userId);
            $data['serviceRequests'] = $this->serviceRequestModel->getServiceRequestsByClientId($userId);
            $data['meterApplications'] = $this->meterApplicationModel->getApplicationsByClientId($userId);
            $data['myMeters'] = $this->meterModel->getMetersAssignedToClient($userId);
            $phone = $data['clientInfo']['phone'] ?? null;
            if ($phone) {
                $sms = new SmsNotification();
                $data['recentNotifications'] = $sms->getNotificationsByPhoneNumber($phone, 5);
            }

            // Build client notifications
            $notifications = [];
            // Pending meter applications by client
            $pendingApps = 0;
            foreach ($data['meterApplications'] as $app) {
                $st = strtolower($app['status'] ?? '');
                if ($st === 'pending') { $pendingApps++; }
            }
            if ($pendingApps > 0) {
                $notifications[] = [
                    'title' => 'Your pending meter applications',
                    'count' => $pendingApps,
                    'url' => 'index.php?page=client_apply_meter'
                ];
            }
            // Pending service requests by client
            $pendingSR = 0;
            foreach ($data['serviceRequests'] as $sr) {
                $st = strtolower($sr['status'] ?? '');
                if ($st === 'pending') { $pendingSR++; }
            }
            if ($pendingSR > 0) {
                $notifications[] = [
                    'title' => 'Pending service requests',
                    'count' => $pendingSR,
                    'url' => 'index.php?page=client_apply_service'
                ];
            }
            // Overdue bills from bills table
            $clientTableId = (int)($data['clientInfo']['id'] ?? 0);
            $overdueBills = [];
            if ($clientTableId > 0 && method_exists($this->billModel, 'getOverdueBillsByClientId')) {
                $overdueBills = $this->billModel->getOverdueBillsByClientId($clientTableId);
            }
            if (!empty($overdueBills)) {
                $notifications[] = [
                    'title' => 'Overdue bills needing payment',
                    'count' => count($overdueBills),
                    'url' => 'index.php?page=client_payments'
                ];
            }
            // Upgrade prompt
            $upgradePending = $this->planUpgradePromptModel->getPendingByClient((int)$userId);
            if ($upgradePending) {
                $notifications[] = [
                    'title' => 'Plan upgrade suggestion',
                    'count' => 1,
                    'url' => 'index.php?page=client_my_plans'
                ];
            }
            $nc = 0; foreach ($notifications as $n) { $nc += (int)$n['count']; }
            $data['notifications'] = $notifications;
            $data['notificationsCount'] = $nc;
        }

        if (isset($_SESSION['message'])) {
            $data['message'] = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error'])) {
            $data['error'] = $_SESSION['error'];
            unset($_SESSION['error']);
        }

        // Surface any pending upgrade prompt on the dashboard (also included in notifications)
        if ($userId) {
            $data['upgradePrompt'] = $data['notifications'] ?? [];
            // Keep existing upgradePrompt data for section rendering if needed
            $data['upgradePrompt'] = $this->planUpgradePromptModel->getPendingByClient((int)$userId);
        }

        require_once '../app/views/client/dashboard.php';
    }

    public function myPlans(): void {
        $this->requireClientAuth();

        $userId = $_SESSION['user_id'] ?? null;
        $clientPlans = [];
        $availablePlans = $this->billingPlanModel->getAllPlans();

        // Handle upgrade prompt acceptance
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_upgrade_prompt'])) {
            $promptId = filter_var($_POST['prompt_id'] ?? null, FILTER_VALIDATE_INT);
            $planId = filter_var($_POST['plan_id'] ?? null, FILTER_VALIDATE_INT);

            if ($userId === null) {
                $_SESSION['error'] = "User not logged in.";
                header('Location: index.php?page=client_my_plans');
                exit();
            }

            $pending = $this->planUpgradePromptModel->getPendingByClient($userId);
            if ($pending && (int)$pending['id'] === (int)$promptId && (int)$pending['recommended_plan_id'] === (int)$planId) {
                $this->planUpgradePromptModel->markAccepted((int)$promptId);

                $existingSubscription = $this->clientPlanModel->getClientPlanByIdAndUserId($planId, $userId);
                if ($existingSubscription) {
                    $_SESSION['message'] = "Upgrade accepted. You already have a subscription or pending request for the recommended plan.";
                } else {
                    $newPlan = $this->billingPlanModel->getPlanById((int)$planId);
                    $cycle = $newPlan['billing_cycle'] ?? 'monthly';
                    $nextBillingDate = date('Y-m-d', strtotime('+1 ' . $cycle));
                    if ($this->clientPlanModel->subscribeToPlan($userId, $planId, 'pending', $nextBillingDate)) {
                        $_SESSION['message'] = "Upgrade accepted. Subscription request submitted and awaiting admin approval.";
                    } else {
                        $_SESSION['error'] = "Upgrade accepted but failed to submit subscription request. Please try again.";
                    }
                }
            } else {
                $_SESSION['error'] = "Upgrade prompt not found or no longer valid.";
            }
            header('Location: index.php?page=client_my_plans');
            exit();
        }

        // Handle upgrade prompt decline
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['decline_upgrade_prompt'])) {
            $promptId = filter_var($_POST['prompt_id'] ?? null, FILTER_VALIDATE_INT);

            if ($userId === null) {
                $_SESSION['error'] = "User not logged in.";
            } elseif ($promptId) {
                $this->planUpgradePromptModel->markDeclined((int)$promptId);
                $_SESSION['message'] = "You declined the upgrade suggestion.";
            } else {
                $_SESSION['error'] = "Invalid upgrade prompt.";
            }
            header('Location: index.php?page=client_my_plans');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe_plan'])) {
            $plan_id = filter_var($_POST['plan_id'], FILTER_VALIDATE_INT);

            if ($plan_id) {
                $existingSubscription = $this->clientPlanModel->getClientPlanByIdAndUserId($plan_id, $userId);
                if ($existingSubscription) {
                    $_SESSION['error'] = "You are already subscribed to this plan or have a pending application.";
                } else {
                    if ($this->clientPlanModel->subscribeToPlan($userId, $plan_id, 'pending', date('Y-m-d', strtotime('+1 month')))) {
                        $_SESSION['message'] = "Plan subscription request submitted successfully. Awaiting admin approval.";
                    } else {
                        $_SESSION['error'] = "Failed to subscribe to the plan. Please try again.";
                    }
                }
            } else {
                $_SESSION['error'] = "Invalid plan selected.";
            }
            header('Location: index.php?page=client_my_plans');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_plan'])) {
            $client_plan_id = filter_var($_POST['client_plan_id'], FILTER_VALIDATE_INT);

            if ($client_plan_id) {
                $planDetails = $this->clientPlanModel->getClientPlanById($client_plan_id);
                if ($planDetails && $planDetails['user_id'] == $userId) {
                    if ($this->clientPlanModel->cancelClientPlan($client_plan_id)) {
                        $_SESSION['message'] = "Plan successfully cancelled.";
                    } else {
                        $_SESSION['error'] = "Failed to cancel the plan. Please try again.";
                    }
                } else {
                    $_SESSION['error'] = "Unauthorized or invalid plan ID.";
                }
            } else {
                $_SESSION['error'] = "Invalid plan ID for cancellation.";
            }
            header('Location: index.php?page=client_my_plans');
            exit();
        }

        if ($userId) {
            $clientPlans = $this->clientPlanModel->getClientPlansByUserId($userId);
        }

        $upgradePrompt = null;
        if ($userId) {
            $upgradePrompt = $this->planUpgradePromptModel->getPendingByClient((int)$userId);
        }

        $data = [
            'clientPlans' => $clientPlans,
            'availablePlans' => $availablePlans,
            'upgradePrompt' => $upgradePrompt
        ];

        if (isset($_SESSION['message'])) {
            $data['message'] = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error'])) {
            $data['error'] = $_SESSION['error'];
            unset($_SESSION['error']);
        }

        require_once '../app/views/client/my_plans.php';
    }

    public function applyService(): void {
        $this->requireClientAuth();

        $userId = $_SESSION['user_id'] ?? null;
        $availableServices = $this->serviceModel->getAllActiveServices();
        $clientServiceApplications = [];

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['submit_service_request'])) {
                $service_id = filter_var($_POST['service_id'], FILTER_VALIDATE_INT);
                $description = trim($_POST['description'] ?? '');

                if ($userId === null) {
                    $error = "User not logged in.";
                } elseif ($service_id <= 0 || empty($description)) {
                    $error = "Please select a service and provide a description.";
                } else {
                    if ($this->serviceRequestModel->createServiceRequest($userId, $service_id, $description)) {
                        $success = "Service request submitted successfully! An admin/collector will review it.";
                    } else {
                        $error = "Failed to submit service request. Please try again.";
                    }
                }
                $_SESSION['success'] = $success;
                $_SESSION['error'] = $error;
                header('Location: index.php?page=client_apply_service');
                exit();
            } elseif (isset($_POST['cancel_service_request'])) {
                $request_id = filter_var($_POST['request_id'], FILTER_VALIDATE_INT);

                if ($request_id) {
                    $requestDetails = $this->serviceRequestModel->getServiceRequestById($request_id);
                    if ($requestDetails && $requestDetails['client_id'] == $userId) {
                        if ($this->serviceRequestModel->updateServiceRequestStatus($request_id, 'cancelled')) {
                            $_SESSION['message'] = "Service request successfully cancelled.";
                        } else {
                            $_SESSION['error'] = "Failed to cancel the service request. Please try again.";
                        }
                    } else {
                        $_SESSION['error'] = "Unauthorized or invalid service request ID.";
                    }
                } else {
                    $_SESSION['error'] = "Invalid service request ID for cancellation.";
                }
                header('Location: index.php?page=client_apply_service');
                exit();
            } elseif (isset($_POST['confirm_service'])) {
                $request_id = filter_var($_POST['request_id'], FILTER_VALIDATE_INT);

                if ($request_id) {
                    $requestDetails = $this->serviceRequestModel->getServiceRequestById($request_id);
                    if ($requestDetails && $requestDetails['client_id'] == $userId && $requestDetails['status'] === 'serviced') {
                        if (method_exists($this->db, 'beginTransaction')) {
                            $this->db->beginTransaction();
                        }

                        try {
                            if (!$this->serviceRequestModel->updateServiceRequestStatus($request_id, 'confirmed')) {
                                throw new Exception("Failed to update service request status to 'confirmed'.");
                            }

                            $serviceDetails = $this->serviceModel->getServiceById($requestDetails['service_id']);
                            if (!$serviceDetails) {
                                throw new Exception("Service details not found for service ID: " . $requestDetails['service_id']);
                            }
                            $serviceCost = $serviceDetails['cost'];

                            if (!$this->clientServiceModel->createBillableService(
                                $userId,
                                $requestDetails['service_id'],
                                $request_id,
                                $serviceCost
                            )) {
                                throw new Exception("Failed to create billable service entry.");
                            }

                            if (method_exists($this->db, 'commit')) {
                                $this->db->commit();
                            }
                            $_SESSION['success'] = "Service confirmed! It is now ready for payment.";
                        } catch (Exception $e) {
                            if (method_exists($this->db, 'rollBack')) {
                                $this->db->rollBack();
                            }
                            $_SESSION['error'] = "Failed to confirm service: " . $e->getMessage();
                            error_log("ClientController confirm_service error: " . $e->getMessage());
                        }
                    } else {
                        $_SESSION['error'] = "Unauthorized or invalid service request ID for confirmation, or status is not 'serviced'.";
                    }
                } else {
                    $_SESSION['error'] = "Invalid service request ID for confirmation.";
                }
                header('Location: index.php?page=client_apply_service');
                exit();
            } elseif (isset($_POST['pay_service'])) {
                $request_id = filter_var($_POST['request_id'], FILTER_VALIDATE_INT);

                if ($request_id) {
                    $requestDetails = $this->serviceRequestModel->getServiceRequestById($request_id);
                    if ($requestDetails && $requestDetails['client_id'] == $userId && in_array($requestDetails['status'], ['serviced','completed'], true)) {
                        if (method_exists($this->db, 'beginTransaction')) { $this->db->beginTransaction(); }
                        try {
                            $serviceDetails = $this->serviceModel->getServiceById($requestDetails['service_id']);
                            if (!$serviceDetails) { throw new Exception('Service details not found'); }
                            $serviceCost = (float)$serviceDetails['cost'];

                            if ($requestDetails['status'] === 'serviced') {
                                if (!$this->serviceRequestModel->updateServiceRequestStatus($request_id, 'confirmed')) {
                                    throw new Exception("Failed to set request to confirmed");
                                }
                            }

                            if (!$this->clientServiceModel->createBillableService($userId, (int)$requestDetails['service_id'], $request_id, $serviceCost)) {
                                throw new Exception('Failed to create billable service');
                            }

                            $clientServiceRow = $this->clientServiceModel->getByServiceRequestId((int)$request_id);
                            if (!$clientServiceRow || empty($clientServiceRow['id'])) { throw new Exception('Could not resolve client service id'); }

                            if (method_exists($this->db, 'commit')) { $this->db->commit(); }
                            header('Location: index.php?page=client_payments&service_id=' . (int)$clientServiceRow['id'] . '&show=modal');
                            exit();
                        } catch (Exception $e) {
                            if (method_exists($this->db, 'rollBack')) { $this->db->rollBack(); }
                            $_SESSION['error'] = 'Failed to prepare service for payment: ' . $e->getMessage();
                        }
                    } else {
                        $_SESSION['error'] = "Unauthorized or invalid service request ID for payment.";
                    }
                } else {
                    $_SESSION['error'] = "Invalid service request ID for payment.";
                }
                header('Location: index.php?page=client_apply_service');
                exit();
            }
        }

        if ($userId) {
            $availableServices = $this->serviceModel->getAllActiveServices();
            $clientServiceApplications = $this->serviceRequestModel->getServiceRequestsByClientId($userId);
            foreach ($clientServiceApplications as &$req) {
                $csr = $this->clientServiceModel->getByServiceRequestId((int)($req['id'] ?? 0));
                $req['has_pending_payment'] = false;
                $req['client_service_id'] = $csr['id'] ?? null;
                if ($csr && !empty($csr['id'])) {
                    $pp = $this->paymentModel->getPaymentsByService((int)$csr['id']);
                    if (!empty($pp)) {
                        $latest = $pp[0];
                        $st = strtolower($latest['status'] ?? '');
                        if (in_array($st, ['pending','flagged','rejected'], true)) {
                            $req['has_pending_payment'] = true;
                        }
                        $txn = (string)($latest['transaction_id'] ?? '');
                        $req['latest_transaction_short'] = $txn ? substr($txn, -4) : null;
                    }
                }
            }
            unset($req);
        }

        $data = [
            'availableServices' => $availableServices,
            'clientServiceApplications' => $clientServiceApplications,
            'error' => $_SESSION['error'] ?? '',
            'success' => $_SESSION['success'] ?? ''
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        require_once '../app/views/client/apply_service.php';
    }

    public function support(): void {
        $this->requireClientAuth();

        $userId = $_SESSION['user_id'] ?? null;
        $availableServices = [];
        $clientServiceApplications = [];
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['submit_support_request'])) {
                $service_id = filter_var($_POST['service_id'], FILTER_VALIDATE_INT);
                $description = trim($_POST['description'] ?? '');

                if ($userId === null) {
                    $error = "User not logged in.";
                } elseif ($service_id <= 0 || empty($description)) {
                    $error = "Please select a service and provide a description.";
                } else {
                    if ($this->serviceRequestModel->createServiceRequest($userId, $service_id, $description)) {
                        $success = "Support request submitted successfully.";
                    } else {
                        $error = "Failed to submit support request. Please try again.";
                    }
                }
                $_SESSION['success'] = $success;
                $_SESSION['error'] = $error;
                header('Location: index.php?page=client_support');
                exit();
            } elseif (isset($_POST['cancel_service_request'])) {
                $request_id = filter_var($_POST['request_id'], FILTER_VALIDATE_INT);

                if ($request_id) {
                    $requestDetails = $this->serviceRequestModel->getServiceRequestById($request_id);
                    if ($requestDetails && $requestDetails['client_id'] == $userId) {
                        if ($this->serviceRequestModel->updateServiceRequestStatus($request_id, 'cancelled')) {
                            $_SESSION['message'] = "Service request successfully cancelled.";
                        } else {
                            $_SESSION['error'] = "Failed to cancel the service request. Please try again.";
                        }
                    } else {
                        $_SESSION['error'] = "Unauthorized or invalid service request ID.";
                    }
                } else {
                    $_SESSION['error'] = "Invalid service request ID for cancellation.";
                }
                header('Location: index.php?page=client_support');
                exit();
            } elseif (isset($_POST['confirm_service'])) {
                $request_id = filter_var($_POST['request_id'], FILTER_VALIDATE_INT);

                if ($request_id) {
                    $requestDetails = $this->serviceRequestModel->getServiceRequestById($request_id);
                    if ($requestDetails && $requestDetails['client_id'] == $userId && $requestDetails['status'] === 'serviced') {
                        if (method_exists($this->db, 'beginTransaction')) { $this->db->beginTransaction(); }
                        try {
                            if (!$this->serviceRequestModel->updateServiceRequestStatus($request_id, 'confirmed')) { throw new Exception("Failed to update service request status to 'confirmed'."); }
                            $serviceDetails = $this->serviceModel->getServiceById($requestDetails['service_id']);
                            if (!$serviceDetails) { throw new Exception("Service details not found for service ID: " . $requestDetails['service_id']); }
                            $serviceCost = $serviceDetails['cost'];
                            if (!$this->clientServiceModel->createBillableService($userId, $requestDetails['service_id'], $request_id, $serviceCost)) { throw new Exception("Failed to create billable service entry."); }
                            if (method_exists($this->db, 'commit')) { $this->db->commit(); }
                            $_SESSION['success'] = "Service confirmed. It is now ready for payment.";
                        } catch (Exception $e) {
                            if (method_exists($this->db, 'rollBack')) { $this->db->rollBack(); }
                            $_SESSION['error'] = "Failed to confirm service: " . $e->getMessage();
                        }
                    } else {
                        $_SESSION['error'] = "Unauthorized or invalid service request ID for confirmation, or status is not 'serviced'.";
                    }
                } else {
                    $_SESSION['error'] = "Invalid service request ID for confirmation.";
                }
                header('Location: index.php?page=client_support');
                exit();
            } elseif (isset($_POST['pay_service'])) {
                $request_id = filter_var($_POST['request_id'], FILTER_VALIDATE_INT);

                if ($request_id) {
                    $requestDetails = $this->serviceRequestModel->getServiceRequestById($request_id);
                    if ($requestDetails && $requestDetails['client_id'] == $userId && in_array($requestDetails['status'], ['serviced','completed'], true)) {
                        if (method_exists($this->db, 'beginTransaction')) { $this->db->beginTransaction(); }
                        try {
                            $serviceDetails = $this->serviceModel->getServiceById($requestDetails['service_id']);
                            if (!$serviceDetails) { throw new Exception('Service details not found'); }
                            $serviceCost = (float)$serviceDetails['cost'];
                            if ($requestDetails['status'] === 'serviced') {
                                if (!$this->serviceRequestModel->updateServiceRequestStatus($request_id, 'confirmed')) { throw new Exception("Failed to set request to confirmed"); }
                            }
                            if (!$this->clientServiceModel->createBillableService($userId, (int)$requestDetails['service_id'], $request_id, $serviceCost)) { throw new Exception('Failed to create billable service'); }
                            $clientServiceRow = $this->clientServiceModel->getByServiceRequestId((int)$request_id);
                            if (!$clientServiceRow || empty($clientServiceRow['id'])) { throw new Exception('Could not resolve client service id'); }
                            if (method_exists($this->db, 'commit')) { $this->db->commit(); }
                            header('Location: index.php?page=client_payments&service_id=' . (int)$clientServiceRow['id'] . '&show=modal');
                            exit();
                        } catch (Exception $e) {
                            if (method_exists($this->db, 'rollBack')) { $this->db->rollBack(); }
                            $_SESSION['error'] = 'Failed to prepare service for payment: ' . $e->getMessage();
                        }
                    } else {
                        $_SESSION['error'] = "Unauthorized or invalid service request ID for payment.";
                    }
                } else {
                    $_SESSION['error'] = "Invalid service request ID for payment.";
                }
                header('Location: index.php?page=client_support');
                exit();
            }
        }

        if ($userId) {
            $availableServices = $this->serviceModel->getAllActiveServices();
            $clientServiceApplications = $this->serviceRequestModel->getServiceRequestsByClientId($userId);
            foreach ($clientServiceApplications as &$req) {
                $csr = $this->clientServiceModel->getByServiceRequestId((int)($req['id'] ?? 0));
                $req['has_pending_payment'] = false;
                $req['client_service_id'] = $csr['id'] ?? null;
                if ($csr && !empty($csr['id'])) {
                    $pp = $this->paymentModel->getPaymentsByService((int)$csr['id']);
                    if (!empty($pp)) {
                        $latest = $pp[0];
                        $st = strtolower($latest['status'] ?? '');
                        if (in_array($st, ['pending','flagged','rejected'], true)) {
                            $req['has_pending_payment'] = true;
                        }
                        $txn = (string)($latest['transaction_id'] ?? '');
                        $req['latest_transaction_short'] = $txn ? substr($txn, -4) : null;
                    }
                }
            }
            unset($req);
        }

        $data = [
            'availableServices' => $availableServices,
            'clientServiceApplications' => $clientServiceApplications,
            'error' => $_SESSION['error'] ?? '',
            'success' => $_SESSION['success'] ?? ''
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        require_once '../app/views/client/support.php';
    }

    public function payments(): void {
        $this->requireClientAuth();
        $userId = $_SESSION['user_id'] ?? null;
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'tracker_status' && ($_GET['type'] ?? '') === 'service') {
            $sid = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
            header('Content-Type: application/json');
            if (!$sid || !$userId) { echo json_encode(['ok'=>false,'error'=>'invalid']); exit(); }
            $svc = $this->clientServiceModel->getUsersClientServiceById((int)$sid, (int)$userId);
            if (!$svc) { echo json_encode(['ok'=>false,'error'=>'not_found']); exit(); }
            $payments = $this->paymentModel->getPaymentsByService((int)$sid);
            echo json_encode([
                'ok' => true,
                'service' => $svc,
                'payments' => $payments
            ]);
            exit();
        }
        
        $data = [
            'paymentHistory' => [],
            'renewablePlans' => [],
            'outstandingServices' => [],
            'unpaidBills' => [],
            'servicesPendingConfirmation' => [],
            'sentBillsCount' => 0,
            'sentBills' => [],
            'selectedBill' => null,
            'selectedBillPayments' => [],
            'selectedClientBill' => null,
            'selectedService' => null,
            'selectedServicePayments' => [],
            'meterStats' => [
                'assigned' => 0,
                'waiting_installation' => 0,
                'flagged' => 0,
                'installed_verified' => 0,
            ],
            'error' => '',
            'success' => ''
        ];

        if ($userId) {
            // Get payment history
            $data['paymentHistory'] = $this->paymentModel->getPaymentHistoryByUserId($userId);
            
            // Get renewable plans
            $data['renewablePlans'] = $this->clientPlanModel->getClientPlansByUserId($userId);
            
            // Get outstanding services
            $allClientServices = $this->clientServiceModel->getClientServicesByUserId($userId);
            
            error_log("ClientController Debug: Total client services fetched: " . count($allClientServices));

            // Filter services that are ready for payment
            $data['outstandingServices'] = array_filter($allClientServices, function($service) {
                $isPendingOrApproved = ($service['status'] === 'pending_payment' || $service['status'] === 'approved');
                $isNotPaid = empty($service['payment_date']) || $service['payment_date'] === '0000-00-00 00:00:00';
                
                error_log("ClientController Debug: Service ID: " . ($service['id'] ?? 'N/A') . 
                         ", Status: '" . ($service['status'] ?? 'N/A') . 
                         "', Payment Date: '" . ($service['payment_date'] ?? 'NULL') . 
                         "', IsPendingOrApproved: " . ($isPendingOrApproved ? 'true' : 'false') . 
                         ", IsNotPaid: " . ($isNotPaid ? 'true' : 'false'));
                
                return $isPendingOrApproved && $isNotPaid;
            });

            error_log("ClientController Debug: Outstanding services after filter: " . count($data['outstandingServices']));

            foreach ($data['outstandingServices'] as &$svc) {
                $svc['has_pending_payment'] = false;
                $svc['latest_payment_status'] = null;
                $payments = $this->paymentModel->getPaymentsByService((int)$svc['id']);
                if (!empty($payments)) {
                    $latest = $payments[0];
                    $svc['latest_payment_status'] = strtolower($latest['status'] ?? '');
                    if (in_array($svc['latest_payment_status'], ['pending','flagged','rejected'], true)) {
                        $svc['has_pending_payment'] = true;
                    }
                    $txn = (string)($latest['transaction_id'] ?? '');
                    $svc['latest_transaction_short'] = $txn ? substr($txn, -4) : null;
                }
            }
            unset($svc);

            // Check for service requests that are 'serviced' but not yet 'confirmed'
            $clientServiceRequests = $this->serviceRequestModel->getServiceRequestsByClientId($userId);
            $data['servicesPendingConfirmation'] = array_filter($clientServiceRequests, function($request) {
                return $request['status'] === 'serviced';
            });

            // Resolve clients table id from user id
            $clientRow = $this->clientModel->getClientByUserId($userId);
            $clientTableId = (int)($clientRow['id'] ?? 0);
            // Get unpaid bills (pending status)
            if ($clientTableId > 0) {
                $data['unpaidBills'] = $this->billModel->getUnpaidBillsByClientId($clientTableId);
            }
            // Count bills sent to client by finance manager (client_bills table)
            $clientBillsRows = $this->clientBillModel->getByClientUserId((int)$userId);
            $data['sentBillsCount'] = is_array($clientBillsRows) ? count($clientBillsRows) : 0;
            // Build sent bills detail list by joining bill info
            $sentBills = [];
            foreach ($clientBillsRows as $row) {
                $billId = (int)($row['bill_id'] ?? 0);
                if ($billId > 0) {
                    $bill = $this->billModel->getBillById($billId);
                    if ($bill) {
                        $sentBills[] = array_merge($bill, [
                            'sender_username' => $row['sender_username'] ?? null,
                            'sender_full_name' => $row['sender_full_name'] ?? null,
                            'sent_at' => $row['created_at'] ?? null,
                            'client_bill_status' => $row['bill_status'] ?? null,
                        ]);
                    }
                }
            }
            $data['sentBills'] = $sentBills;
            // Prefill selection if bill_id provided in query
            $selectedBillId = filter_var($_GET['bill_id'] ?? 0, FILTER_VALIDATE_INT);
            if ($selectedBillId && $clientTableId > 0) {
                $bill = $this->billModel->getBillById($selectedBillId);
                if ($bill && (int)($bill['client_id'] ?? 0) === $clientTableId) {
                    $data['selectedBill'] = $bill;
                    $data['selectedBillPayments'] = $this->paymentModel->getPaymentsByBill($selectedBillId);
                    foreach ($clientBillsRows as $row) {
                        if ((int)($row['bill_id'] ?? 0) === (int)$selectedBillId) { $data['selectedClientBill'] = $row; break; }
                    }
                }
            }

            // Prefill selection if service_id provided in query
            $selectedServiceId = filter_var($_GET['service_id'] ?? 0, FILTER_VALIDATE_INT);
            if ($selectedServiceId) {
                $svc = $this->clientServiceModel->getUsersClientServiceById((int)$selectedServiceId, (int)$userId);
                if ($svc) {
                    $data['selectedService'] = $svc;
                    $data['selectedServicePayments'] = $this->paymentModel->getPaymentsByService((int)$selectedServiceId);
                }
            }

            // Meter stats (for top summary cards on payments page)
            try {
                $assignedMeters = $this->meterModel->getMetersAssignedToClient((int)$userId);
                $verifiedMeters = $this->verifiedMeterModel->getByClientId((int)$userId);

                $assignedCount = is_array($assignedMeters) ? count($assignedMeters) : 0;

                $waiting = 0; $flagged = 0; $installedVerified = 0;
                $seen = [];
                foreach ($assignedMeters as $m) {
                    $mid = (int)($m['id'] ?? ($m['meter_id'] ?? 0));
                    if ($mid <= 0) { continue; }
                    $seen[$mid] = true;
                    $status = strtolower((string)($m['status'] ?? ''));
                    if ($status === 'waiting_installation') { $waiting++; }
                    if ($status === 'flagged') { $flagged++; }
                    if (in_array($status, ['installed','verified'], true)) { $installedVerified++; }
                }
                foreach ($verifiedMeters as $vm) {
                    $mid = (int)($vm['meter_id'] ?? 0);
                    $status = strtolower((string)($vm['meter_status'] ?? ($vm['meter_table_status'] ?? '')));
                    if ($mid > 0 && !isset($seen[$mid])) {
                        if ($status === 'waiting_installation') { $waiting++; }
                        if ($status === 'flagged') { $flagged++; }
                        if (in_array($status, ['installed','verified'], true)) { $installedVerified++; }
                        $seen[$mid] = true;
                    } else {
                        // Even if already counted, ensure verified state reflects installed/verified
                        if (in_array($status, ['installed','verified'], true)) { $installedVerified++; }
                    }
                }

                $data['meterStats'] = [
                    'assigned' => $assignedCount,
                    'waiting_installation' => $waiting,
                    'flagged' => $flagged,
                    'installed_verified' => $installedVerified,
                ];
            } catch (Exception $e) {
                // Fail silently; stats are cosmetic and should not block payments page
                error_log('Meter stats error: ' . $e->getMessage());
            }

            // Handle M-Pesa payment submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_mpesa_payment') {
                $type = $_POST['payment_type'];
                $reference_id = filter_var($_POST['item_id'], FILTER_VALIDATE_INT);
                $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
                $phone_number = $_POST['phone_number'] ?? '';

                if ($reference_id && $amount && preg_match('/^2547\d{8}$/', $phone_number)) {
                    $paymentSuccess = false;
                    $transactionId = null;
                    $errorMessage = null;

                    if (method_exists($this->db, 'beginTransaction')) {
                        $this->db->beginTransaction();
                    }
                    
                    try {
                        $paymentType = ($type === 'bill') ? 'bill_payment' : (($type === 'plan') ? 'plan_renewal' : 'service_payment');
                        
                        $paymentRecorded = $this->paymentModel->recordPayment(
                            $userId,
                            $paymentType,
                            $reference_id,
                            $amount,
                            'mpesa',
                            null,
                            'pending'
                        );

                        if (!$paymentRecorded) {
                            throw new Exception("Failed to record payment entry.");
                        }

                        $paymentId = $this->db->lastInsertId();

                        // Initiate STK Push via Payment model (uses DarajaApi)
                        $formattedPhone = preg_match('/^254[0-9]{9}$/', $phone_number) ? $phone_number : ('254' . ltrim($phone_number, '0'));
                        $mpesaResponse = $this->paymentModel->initiateMpesaPayment([
                            'phone' => $formattedPhone,
                            'amount' => $amount,
                                'reference' => ($type === 'bill' ? ('Bill#' . (int)$reference_id) : (($type === 'plan' ? 'PlanRenewal' : 'ServicePayment') . '#' . (int)$reference_id)),
                            'description' => 'Water Billing Payment'
                        ]);

                        if ($mpesaResponse['success'] ?? false) {
                            // Store request and mark bill/service as pending; await callback for completion
                            $this->paymentModel->storeMpesaRequest([
                                'checkout_request_id' => $mpesaResponse['checkout_request_id'] ?? '',
                                'bill_id' => ($type === 'bill') ? (int)$reference_id : null,
                                'amount' => $amount,
                                'phone' => $formattedPhone,
                                'status' => 'pending'
                            ]);

                            if ($type === 'bill') {
                                $bill = $this->billModel->getBillById((int)$reference_id);
                                if ($bill && (int)$bill['client_id'] === (int)$clientTableId) {
                                    $currentPaid = (float)($bill['amount_paid'] ?? 0);
                                    $this->billModel->updateBillPayment((int)$reference_id, $currentPaid, 'pending');
                                }
                            }

                            // Persist initiation details on the pending payment row
                            if (!empty($paymentId) && !empty($mpesaResponse['checkout_request_id'])) {
                                $this->paymentModel->updatePaymentInitiation((int)$paymentId, (string)$mpesaResponse['checkout_request_id'], 'Mpesa STK-Push');
                            }

                            if (method_exists($this->db, 'commit')) {
                                $this->db->commit();
                            }
                            $paymentSuccess = true;
                            $transactionId = $mpesaResponse['checkout_request_id'] ?? null;
                        } else {
                            $this->paymentModel->updatePaymentStatus($paymentId, 'failed');
                            throw new Exception("M-Pesa payment initiation failed: " . ($mpesaResponse['message'] ?? 'unknown error'));
                        }
                    } catch (Exception $e) {
                        if (method_exists($this->db, 'rollBack')) {
                            $this->db->rollBack();
                        }
                        $errorMessage = $e->getMessage();
                        error_log("Payment processing error: " . $e->getMessage());
                    }

                    if ($paymentSuccess) {
                        $_SESSION['success'] = "Payment completed successfully! Transaction ID: " . $transactionId;
                    } else {
                        $_SESSION['error'] = $errorMessage ?? "Payment processing failed";
                    }
                } else {
                    $_SESSION['error'] = "Invalid payment details. Please check your phone number (format: 2547XXXXXXXX) and amount.";
                }
                
                header('Location: index.php?page=client_payments');
                exit();
            }
        }

        if (isset($_SESSION['error'])) {
            $data['error'] = $_SESSION['error'];
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            $data['success'] = $_SESSION['success'];
            unset($_SESSION['success']);
        }

        require_once '../app/views/client/payments.php';
    }

    public function profile(): void {
        $this->requireClientAuth();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $error = '';
        $success = '';
        $submittedSection = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
            $submittedSection = $_POST['form_section'] ?? null;
            $currentUser = $this->userModel->getUserById($userId);
            $currentClient = $this->clientModel->getClientByUserId($userId);
            if (!$currentUser || !$currentClient) {
                $error = 'User not found.';
            } else {
                $full_name = trim($_POST['full_name'] ?? ($currentUser['full_name'] ?? ''));
                $email = trim($_POST['email'] ?? ($currentUser['email'] ?? ''));
                $address = trim($_POST['address'] ?? ($currentUser['address'] ?? ''));
                $contact_phone = trim($_POST['contact_phone'] ?? ($currentUser['contact_phone'] ?? ''));
                $username = trim($_POST['username'] ?? ($currentUser['username'] ?? ''));

                if ($userId <= 0 || $full_name === '' || $email === '' || $contact_phone === '') {
                    $error = 'Please fill in all required fields.';
                } else {
                    $ok1 = $this->userModel->updateUser($userId, $username, $email, $currentUser['role'], $full_name, $address, $contact_phone, $currentUser['status']);
                    $ok2 = $this->clientModel->updateClientByUserId($userId, $full_name, $address, $contact_phone, $email);
                    if ($ok1 && $ok2) {
                        $_SESSION['username'] = $username;
                        $success = 'Profile updated successfully.';
                    } else {
                        $error = 'Failed to update profile.';
                    }
                }
            }
        }

        $data = [
            'userInfo' => $this->auth->getUserById($userId),
            'clientInfo' => $this->clientModel->getClientByUserId($userId),
            'error' => $error,
            'success' => $success,
            'submittedSection' => $submittedSection
        ];

        require_once '../app/views/client/profile.php';
    }

    private function initiateMpesaPayment(float $amount, int $userId, string $phoneNumber): array {
        return [
            'success' => true,
            'transaction_id' => 'MPESA_' . strtoupper(uniqid()),
            'message' => 'STK Push initiated to ' . $phoneNumber . '. Please complete the transaction on your phone.'
        ];
    }

    

    public function reviews(): void {
        $this->requireClientAuth();
        require_once '../app/views/client/reviews.php';
    }
    public function downloadBill() {
        $this->requireClientAuth();
        $billId = isset($_GET['bill_id']) ? (int)$_GET['bill_id'] : 0;
        if ($billId <= 0) { header('Location: index.php?page=client_view_bills'); exit; }
        $bill = $this->billModel->getBillById($billId);
        if (!$bill) { header('Location: index.php?page=client_view_bills'); exit; }
        $clientInfo = $this->clientModel->getClientByUserId((int)($_SESSION['user_id'] ?? 0));
        if (!$clientInfo || (int)($clientInfo['id'] ?? 0) !== (int)$bill['client_id']) { header('Location: index.php?page=client_view_bills'); exit; }
        $meter = $this->meterModel->getMeterById((int)$bill['meter_id']);
        $payments = $this->paymentModel->getPaymentsByBill($billId);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Bill #' . (int)$billId . '</title><style>
            body{font-family:Inter,Arial,sans-serif;background:#f5f7fb;color:#0f172a;margin:24px}
            .invoice{width:210mm;min-height:297mm;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.06);padding:16mm}
            .header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px}
            .title{font-size:22px;font-weight:700}
            .meta{color:#64748b;font-size:14px}
            h3{margin:.5rem 0}
            table{width:100%;border-collapse:collapse;margin-top:10px}
            th,td{padding:8px;border-bottom:1px solid #e5e7eb;text-align:left}
            .footer{margin-top:12px;color:#64748b;font-size:12px}
            @page{size:A4;margin:10mm}
            @media print{body{margin:0;background:#fff}.controls{display:none}}
        </style></head><body>';
        echo '<div class="controls" style="position:fixed;top:12px;right:12px;display:flex;gap:8px;z-index:9999">'
            . '<button onclick="window.print()" style="padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#0f172a;font-weight:600;cursor:pointer">Print</button>'
            . '<button onclick="downloadBillPdf()" style="padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#0f172a;font-weight:600;cursor:pointer">Download PDF</button>'
            . '</div>';
        echo '<div class="invoice">';
        $isVerified = false; foreach ($payments as $pp) { if (strtolower($pp['status'] ?? '') === 'confirmed_and_verified') { $isVerified = true; break; } }
        echo '<div class="header"><div><div class="title">Water Billing - Bill #' . (int)$billId . '</div><div class="meta">Bill Date: ' . htmlspecialchars($bill['bill_date']) . '<br>Due Date: ' . htmlspecialchars($bill['due_date']) . '</div></div>';
        if ($isVerified) { echo '<div class="meta"><span style="display:inline-flex;align-items:center;gap:6px;background:#10b981;color:#fff;padding:4px 8px;border-radius:999px;">Confirmed & Verified <span>✅</span></span></div>'; }
        echo '<div class="meta">Client: ' . htmlspecialchars($clientInfo['full_name'] ?? $clientInfo['name'] ?? '') . '<br>Meter: ' . htmlspecialchars($meter['serial_number'] ?? '') . '</div></div>';
        echo '<h3>Summary</h3><table><tr><th>Consumption (units)</th><th>Amount Due (KES)</th><th>Balance (KES)</th><th>Status</th></tr>';
        echo '<tr><td>' . number_format((float)($bill['consumption_units'] ?? 0), 2) . '</td><td>' . number_format((float)($bill['amount_due'] ?? 0), 2) . '</td><td>' . number_format((float)($bill['balance'] ?? 0), 2) . '</td><td>' . htmlspecialchars($bill['payment_status'] ?? $bill['status'] ?? 'pending') . '</td></tr></table>';
        echo '<div class="meta"><strong>Billing Period:</strong> ' . htmlspecialchars($bill['billing_period_start'] ?? '') . ' - ' . htmlspecialchars($bill['billing_period_end'] ?? '') . '</div>';
        if (!empty($payments)) {
            echo '<h3>Payments</h3><table><tr><th>Date</th><th>Method</th><th>Amount</th></tr>';
            foreach ($payments as $p) {
                echo '<tr><td>' . htmlspecialchars($p['payment_date']) . '</td><td>' . htmlspecialchars($p['payment_method_name'] ?? $p['payment_method']) . '</td><td>' . number_format((float)$p['amount'], 2) . '</td></tr>';
            }
            echo '</table>';
        }
        echo '<div class="footer">This is a system-generated bill. For inquiries, contact support.</div>';
        echo '</div>';
        echo '<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>';
        echo '<script>function downloadBillPdf(){var el=document.querySelector(".invoice");if(!el){return;}html2canvas(el,{scale:2}).then(function(canvas){var imgData=canvas.toDataURL("image/png");var pdf=new window.jspdf.jsPDF("p","mm","a4");var pageW=pdf.internal.pageSize.getWidth();var pageH=pdf.internal.pageSize.getHeight();var margin=10;var imgW=pageW-margin*2;var imgH=canvas.height*imgW/canvas.width;var x=margin;var y=margin;if(imgH>pageH-margin*2){var scale=(pageH-margin*2)/imgH;imgW=imgW*scale;imgH=imgH*scale;x=(pageW-imgW)/2;y=margin;}pdf.addImage(imgData,"PNG",x,y,imgW,imgH);pdf.save("bill-' . (int)$billId . '.pdf");});}document.addEventListener("DOMContentLoaded",function(){downloadBillPdf();});</script>';
        echo '</body></html>';
        exit;
    }

    public function generateReceipt(): void {
        $this->requireClientAuth();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $paymentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($paymentId <= 0 || $userId <= 0) { header('Location: index.php?page=client_payments'); exit; }
        $paymentModel = new Payment($this->db);
        $txn = $paymentModel->getPaymentById($paymentId);
        if (!$txn || (int)($txn['user_id'] ?? 0) !== $userId) { header('Location: index.php?page=client_payments'); exit; }
        header('Content-Type: text/html; charset=UTF-8');
        $statusLabel = (strtolower($txn['status']) === 'confirmed_and_verified') ? 'Confirmed & Verified' : ucfirst($txn['status']);
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Receipt #' . (int)$paymentId . '</title><style>
        body{font-family:Inter,Arial,sans-serif;background:#f5f7fb;color:#0f172a;margin:24px}
        .receipt{max-width:860px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden}
        .header{display:flex;justify-content:space-between;align-items:center;padding:20px 24px;background:#2563eb;color:#fff}
        .brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:20px}
        .section{padding:20px 24px}
        h3{margin:.2rem 0 .6rem 0;font-size:16px;color:#334155}
        table{width:100%;border-collapse:collapse}
        th,td{padding:10px 8px;border-bottom:1px solid #e5e7eb;text-align:left;font-size:14px}
        .badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:9999px;background:#e0f2fe;color:#2563eb;font-weight:600}
        .footer{padding:16px 24px;color:#64748b;font-size:12px}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#0f172a;font-weight:600;cursor:pointer}
        .btn:hover{background:#f1f5f9}
        .floating-controls{position:fixed;top:16px;right:16px;z-index:9999;display:flex;gap:8px}
        @media print{body{margin:0}.floating-controls{display:none}}
        </style></head><body>';
        echo '<div class="floating-controls">'
            . '<button class="btn" onclick="window.print()">Print</button>'
            . '<button class="btn" onclick="downloadReceiptPdf()">Download PDF</button>'
            . '</div>';
        echo '<div class="receipt">';
        echo '<div class="header"><div class="brand"><span>💧 AquaBill</span></div><div>Official Receipt</div></div>';
        echo '<div class="section"><h3>Transaction</h3><table><tr><th>ID</th><th>Date</th><th>Amount</th><th>Status</th></tr>';
        echo '<tr><td>#' . (int)$txn['payment_id'] . '</td><td>' . htmlspecialchars($txn['payment_date']) . '</td><td>KES ' . number_format((float)$txn['amount'],2) . '</td><td><span class="badge">' . htmlspecialchars($statusLabel) . ' ✅</span></td></tr></table></div>';
        echo '<div class="section"><h3>Client</h3><table><tr><th>Name</th><th>Email</th><th>Client ID</th></tr>';
        echo '<tr><td>' . htmlspecialchars($_SESSION['name'] ?? $txn['client_name'] ?? '') . '</td><td>' . htmlspecialchars($txn['client_email'] ?? '') . '</td><td>' . htmlspecialchars((string)$txn['client_id']) . '</td></tr></table></div>';
        if (!empty($txn['bill_id'])) {
            $billModel = new Bill($this->db);
            $bill = $billModel->getBillById((int)$txn['bill_id']);
            if ($bill) {
                echo '<div class="section"><h3>Bill</h3><table><tr><th>Bill ID</th><th>Status</th><th>Due</th><th>Paid</th></tr>';
                echo '<tr><td>#' . (int)$bill['id'] . '</td><td>' . htmlspecialchars(ucfirst($bill['status'] ?? $bill['payment_status'] ?? 'pending')) . '</td><td>KES ' . number_format((float)($bill['amount_due'] ?? 0),2) . '</td><td>KES ' . number_format((float)($bill['amount_paid'] ?? 0),2) . '</td></tr></table></div>';
            }
        }
        echo '<div class="footer">This is a system-generated receipt. Save for your records.</div></div>';
        echo '<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>';
        echo '<script>function downloadReceiptPdf(){var el=document.querySelector(".receipt");if(!el){return;}html2canvas(el,{scale:2}).then(function(canvas){var imgData=canvas.toDataURL("image/png");var pdf=new window.jspdf.jsPDF("p","mm","a4");var pageW=pdf.internal.pageSize.getWidth();var pageH=pdf.internal.pageSize.getHeight();var margin=10;var imgW=pageW-margin*2;var imgH=canvas.height*imgW/canvas.width;var x=margin;var y=margin;if(imgH>pageH-margin*2){var scale=(pageH-margin*2)/imgH;imgW=imgW*scale;imgH=imgH*scale;x=(pageW-imgW)/2;y=margin;}pdf.addImage(imgData,"PNG",x,y,imgW,imgH);pdf.save("receipt-' . (int)$paymentId . '.pdf");});}</script>';
        echo '</body></html>';
        exit();
    }
}
