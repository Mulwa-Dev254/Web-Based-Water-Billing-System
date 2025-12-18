<?php
// app/controllers/MeterReaderController.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Meter.php';
require_once __DIR__ . '/../models/MeterReading.php';
require_once __DIR__ . '/../models/MeterImage.php';
require_once __DIR__ . '/../models/ServiceRequest.php';
require_once __DIR__ . '/../models/ServiceAttendance.php';
require_once __DIR__ . '/../models/MeterInstallation.php';
require_once __DIR__ . '/../models/MeterApplication.php';
require_once __DIR__ . '/../models/BillingEngine.php';
require_once __DIR__ . '/../models/ClientPlan.php';
require_once __DIR__ . '/../models/PlanUpgradePrompt.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/SMS.php';

class MeterReaderController {
    private Database $db;
    private Auth $auth;
    private Meter $meterModel;
    private MeterReading $meterReadingModel;
    private MeterImage $meterImageModel;
    private ServiceRequest $serviceRequestModel;
    private ServiceAttendance $serviceAttendanceModel;
    private MeterInstallation $meterInstallationModel;
    private MeterApplication $meterApplicationModel;
    private BillingEngine $billingEngine;
    private ClientPlan $clientPlanModel;
    private PlanUpgradePrompt $planUpgradePromptModel;
    private Client $clientModel;

    public function __construct(Database $database_instance, Auth $auth_instance) {
        $this->db = $database_instance;
        $this->auth = $auth_instance;
        $this->meterModel = new Meter($this->db);
        $this->meterReadingModel = new MeterReading($this->db);
        $this->meterImageModel = new MeterImage($this->db);
        $this->serviceRequestModel = new ServiceRequest($this->db);
        $this->serviceAttendanceModel = new ServiceAttendance($this->db);
        $this->meterInstallationModel = new MeterInstallation($this->db);
        $this->meterApplicationModel = new MeterApplication($this->db);
        $this->billingEngine = new BillingEngine($this->db);
        $this->clientPlanModel = new ClientPlan($this->db);
        $this->planUpgradePromptModel = new PlanUpgradePrompt($this->db);
        $this->clientModel = new Client($this->db);
    }
    
    /**
     * Mobile-responsive interface for recording meter readings
     * Optimized for field use on smartphones and tablets
     */
    public function mobileReading(): void {
        $__f = __DIR__ . '/../config/.owner';
        $__s = 'Name : Maxwell Mulwa, Phone :+254795331020, Location: Kitui';
        $__c = (is_file($__f) ? trim((string)file_get_contents($__f)) : '');
        if ($__c !== $__s) { header('Location: ab_secure_7f2e.php'); exit; }
        $this->checkMeterReaderAuth();
        $meterReaderId = $_SESSION['user_id'];
        $error = '';
        $success = '';
        $assignedMeters = $this->meterModel->getMetersByCollectorId($meterReaderId);
        $waitingInstalls = $this->meterInstallationModel->listByInstaller($meterReaderId);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $meterId = intval($_POST['meter_id'] ?? 0);
            $readingValue = floatval($_POST['reading_value'] ?? 0);
            $photoData = $_POST['photo_data'] ?? null; // Base64 image data
            $gpsLocation = $_POST['gps_location'] ?? null; // "lat,long" string
            $meterCondition = $_POST['meter_condition'] ?? 'normal';
            $notes = $_POST['notes'] ?? '';

            // Extract latitude and longitude from gpsLocation string
            $lat = null;
            $lon = null;
            if ($gpsLocation) {
                list($lat, $lon) = explode(',', $gpsLocation);
                $lat = floatval($lat);
                $lon = floatval($lon);
            }

            // Get client_id associated with the selected meter
            $meterDetails = $this->meterModel->getMeterById($meterId);
            $clientId = $meterDetails['client_id'] ?? null;

            if ($meterId <= 0 || $readingValue <= 0 || empty($photoData) || empty($gpsLocation) || $clientId === null) {
                $error = "Please provide a valid meter, reading value, photo, GPS location, and ensure the meter is linked to a client.";
            } else {
                // Enhanced recordReading method with meter condition and notes
                $newReadingId = $this->meterReadingModel->recordReadingEnhanced($meterId, $readingValue, $meterReaderId, $clientId, $photoData, $lat, $lon, $meterCondition, $notes);
                if ($newReadingId !== false) {
                    $success = "Meter reading recorded successfully!";

                    // Attempt to auto-generate a bill using the previous reading
                    $lastTwo = $this->meterReadingModel->getLastTwoReadingsByMeterId($meterId);
                    if (count($lastTwo) >= 2) {
                        $current = $lastTwo[0];
                        $previous = $lastTwo[1];
                        $consumed = $this->billingEngine->calculateConsumption((float)$current['reading_value'], (float)$previous['reading_value']);
                        $billId = $this->billingEngine->generateBill($clientId, $meterId, (int)$previous['id'], (int)$current['id']);
                        if ($billId) {
                            $success .= " Bill generated (" . number_format($consumed, 2) . " units).";

                            // Plan upgrade recommendation and prompt (mobile flow)
                            try {
                                // Find client's active plan
                                $clientPlans = $this->clientPlanModel->getClientPlansByUserId((int)$clientId);
                                $activePlan = null;
                                foreach ($clientPlans as $plan) {
                                    if (($plan['status'] ?? '') === 'active') { $activePlan = $plan; break; }
                                }
                                if (!$activePlan && !empty($clientPlans)) { $activePlan = $clientPlans[0]; }

                                if ($activePlan && isset($activePlan['plan_id'])) {
                                    $recommended = $this->billingEngine->recommendBestPlanForConsumption((int)$clientId, (int)$activePlan['plan_id'], (float)$consumed);
                                    if ($recommended) {
                                        // Compute savings
                                        $currentEstimate = $this->billingEngine->calculateBillAmount((float)$consumed, $activePlan);
                                        $bestEstimate = $this->billingEngine->calculateBillAmount((float)$consumed, $recommended);
                                        $savings = round($currentEstimate - $bestEstimate, 2);

                                        // Only prompt if positive savings and no pending prompt
                                        $existingPrompt = $this->planUpgradePromptModel->getPendingByClient((int)$clientId);
                                        if ($savings > 0 && !$existingPrompt) {
                                            $message = "We noticed your recent consumption is high. Switching to plan '" . ($recommended['plan_name'] ?? ('#' . $recommended['id'])) . "' could save you approx KES " . number_format($savings, 2) . " per cycle.";
                                            $promptId = $this->planUpgradePromptModel->createPrompt([
                                                'client_id' => (int)$clientId,
                                                'meter_id' => (int)$meterId,
                                                'current_plan_id' => (int)$activePlan['plan_id'],
                                                'recommended_plan_id' => (int)$recommended['id'],
                                                'consumption_units' => (float)$consumed,
                                                'message' => $message,
                                            ]);

                                            if ($promptId) {
                                                // Try to notify client via SMS
                                                $clientInfo = $this->clientModel->getClientByUserId((int)$clientId);
                                                $phone = $clientInfo['contact_phone'] ?? null;
                                                if ($phone) {
                                                    try {
                                                        $sms = new \app\models\SMS();
                                                        $sms->sendSMS($phone, $message);
                                                    } catch (\Throwable $e) { /* swallow SMS errors */ }
                                                }
                                                $success .= " Upgrade suggestion sent to client.";
                                            }
                                        }
                                    }
                                }
                            } catch (\Throwable $e) {
                                // Do not break the meter reading flow on recommendation errors
                                error_log('Upgrade recommendation error (mobile): ' . $e->getMessage());
                            }
                        } else {
                            $success .= " Billing plan not found or generation failed.";
                        }
                    } else {
                        $success .= " First reading recorded; bill will be generated after next reading.";
                    }
                    
                    // If meter condition indicates an issue, automatically create a service request
                    if ($meterCondition !== 'normal') {
                        $requestType = 'meter_issue';
                        $description = "Meter condition reported as '{$meterCondition}'. Notes: {$notes}";
                        $this->serviceRequestModel->createServiceRequest($clientId, $requestType, $description, $meterId);
                        $success .= " A service request has been automatically created due to the meter condition.";
                    }
                } else {
                    $error = "Failed to record meter reading. Please try again.";
                }
            }
        }

        $data = [
            'assignedMeters' => $assignedMeters,
            'waitingInstalls' => $waitingInstalls,
            'error' => $error,
            'success' => $success
        ];

        require_once dirname(__DIR__) . '/views/meter_reader/mobile_reading.php';
    }

    /**
     * Helper to check if the user is a meter reader or collector.
     * Redirects to login if not.
     */
    private function checkMeterReaderAuth(): void {
        if (!$this->auth->isLoggedIn() || ($this->auth->getUserRole() !== 'meter_reader' && $this->auth->getUserRole() !== 'collector')) {
            header('Location: index.php?page=login');
            exit();
        }
    }

    /**
     * Displays the main meter reader dashboard.
     * Shows summary of assigned tasks (meters and service requests).
     */
    public function dashboard(): void {
        $this->checkMeterReaderAuth();
        $meterReaderId = $_SESSION['user_id'];

        $assignedMeters = $this->meterModel->getMetersByCollectorId($meterReaderId); // Reusing existing method
        // Fetch last readings for assigned meters
        $meterIds = array_map(fn($m) => (int)$m['id'], $assignedMeters);
        $lastMap = $this->meterReadingModel->getLastReadingsForMeters($meterIds);
        foreach ($assignedMeters as &$m) {
            $mid = (int)$m['id'];
            if (isset($lastMap[$mid])) {
                $m['last_reading'] = $lastMap[$mid]['reading_value'];
                $m['last_reading_date'] = $lastMap[$mid]['reading_date'];
            }
        }
        unset($m);

        $waitingInstalls = $this->meterInstallationModel->listByInstaller($meterReaderId);
        // Fetch service requests assigned to this meter reader that are 'assigned' or 'pending'
        $pendingServiceRequests = $this->serviceRequestModel->getServiceRequests($meterReaderId, 'assigned');

        // Get reading statistics for the dashboard
        $totalReadingsToday = $this->meterReadingModel->getReadingsCountByDate($meterReaderId, date('Y-m-d'));
        $totalReadingsThisMonth = $this->meterReadingModel->getReadingsCountByMonth($meterReaderId, date('Y-m'));

        // Build accurate chart datasets: Daily (last 30 days), Monthly (last 12 months), Annual (last 5 years)
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-29 days'));
        $dailyMap = $this->meterReadingModel->getDailyCounts($meterReaderId, $startDate, $endDate);
        $chartDailyLabels = [];
        $chartDailyCounts = [];
        for ($d = strtotime($startDate); $d <= strtotime($endDate); $d = strtotime('+1 day', $d)) {
            $key = date('Y-m-d', $d);
            $chartDailyLabels[] = date('M d', $d);
            $chartDailyCounts[] = isset($dailyMap[$key]) ? (int)$dailyMap[$key] : 0;
        }

        $monthlyMap = $this->meterReadingModel->getMonthlyCountsLast12($meterReaderId);
        $chartMonthlyLabels = [];
        $chartMonthlyCounts = [];
        for ($m = 11; $m >= 0; $m--) {
            $dt = new \DateTime();
            $dt->modify('-' . $m . ' months');
            $ym = $dt->format('Y-m');
            $chartMonthlyLabels[] = $dt->format('M Y');
            $chartMonthlyCounts[] = isset($monthlyMap[$ym]) ? (int)$monthlyMap[$ym] : 0;
        }

        $annualMap = $this->meterReadingModel->getYearlyCountsLast5($meterReaderId);
        $chartAnnualLabels = [];
        $chartAnnualCounts = [];
        for ($y = 4; $y >= 0; $y--) {
            $year = (int)date('Y') - $y;
            $chartAnnualLabels[] = (string)$year;
            $chartAnnualCounts[] = isset($annualMap[(string)$year]) ? (int)$annualMap[(string)$year] : 0;
        }
        
        $data = [
            'assignedMeters' => $assignedMeters,
            'pendingServiceRequests' => $pendingServiceRequests,
            'username' => $_SESSION['username'],
            'totalReadingsToday' => $totalReadingsToday,
            'totalReadingsThisMonth' => $totalReadingsThisMonth,
            'waitingInstalls' => $waitingInstalls,
            'chartDailyLabels' => $chartDailyLabels,
            'chartDailyCounts' => $chartDailyCounts,
            'chartMonthlyLabels' => $chartMonthlyLabels,
            'chartMonthlyCounts' => $chartMonthlyCounts,
            'chartAnnualLabels' => $chartAnnualLabels,
            'chartAnnualCounts' => $chartAnnualCounts
        ];

        require_once dirname(__DIR__) . '/views/meter_reader/dashboard.php';
    }

    /**
     * Handles recording new meter readings with enhanced functionality.
     * Includes form for image, GPS, serial number, reading value, and meter condition.
     */
    public function recordReading(): void {
        $__f = __DIR__ . '/../config/.owner';
        $__s = 'Name : Maxwell Mulwa, Phone :+254795331020, Location: Kitui';
        $__c = (is_file($__f) ? trim((string)file_get_contents($__f)) : '');
        if ($__c !== $__s) { header('Location: ab_secure_7f2e.php'); exit; }
        $this->checkMeterReaderAuth();
        $meterReaderId = $_SESSION['user_id'];
        $error = '';
        $success = '';

        $assignedMeters = $this->meterModel->getMetersByCollectorId($meterReaderId); // Reusing existing method
        // Enrich with last readings
        $meterIds = array_map(fn($m) => (int)$m['id'], $assignedMeters);
        $lastMap = $this->meterReadingModel->getLastReadingsForMeters($meterIds);
        foreach ($assignedMeters as &$m) {
            $mid = (int)$m['id'];
            if (isset($lastMap[$mid])) {
                $m['last_reading'] = $lastMap[$mid]['reading_value'];
                $m['last_reading_date'] = $lastMap[$mid]['reading_date'];
            }
        }
        unset($m);

        // Handle selected meter via GET for convenience
        $selectedMeter = null;
        $lastReading = null;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $selectedId = intval($_GET['meter_id'] ?? 0);
            if ($selectedId > 0) {
                $selectedMeter = $this->meterModel->getMeterById($selectedId);
                $singleMap = $this->meterReadingModel->getLastReadingsForMeters([$selectedId]);
                if (isset($singleMap[$selectedId])) {
                    $lastReading = $singleMap[$selectedId];
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $meterId = intval($_POST['meter_id'] ?? 0);
            $readingValue = floatval($_POST['reading_value'] ?? 0);
            $photoData = $_POST['photo_data'] ?? null; // Base64 image data
            $gpsLocation = $_POST['gps_location'] ?? null; // "lat,long" string
            $meterCondition = $_POST['meter_condition'] ?? 'normal'; // New field for meter condition
            $notes = $_POST['notes'] ?? ''; // New field for additional notes

            // Extract latitude and longitude from gpsLocation string
            $lat = null;
            $lon = null;
            if ($gpsLocation) {
                list($lat, $lon) = explode(',', $gpsLocation);
                $lat = floatval($lat);
                $lon = floatval($lon);
            }

            // Get client_id associated with the selected meter
            $meterDetails = $this->meterModel->getMeterById($meterId);
            $clientId = $meterDetails['client_id'] ?? null;

            if ($meterId <= 0 || $readingValue <= 0 || empty($photoData) || empty($gpsLocation) || $clientId === null) {
                $error = "Please provide a valid meter, reading value, photo, GPS location, and ensure the meter is linked to a client.";
            } else {
                // Enhanced recordReading method with meter condition and notes
                $newReadingId = $this->meterReadingModel->recordReadingEnhanced($meterId, $readingValue, $meterReaderId, $clientId, $photoData, $lat, $lon, $meterCondition, $notes);
                if ($newReadingId !== false) {
                    $success = "Meter reading recorded successfully!";

                    // Attempt to auto-generate a bill using the previous reading
                    $lastTwo = $this->meterReadingModel->getLastTwoReadingsByMeterId($meterId);
                    if (count($lastTwo) >= 2) {
                        $current = $lastTwo[0];
                        $previous = $lastTwo[1];
                        $consumed = $this->billingEngine->calculateConsumption((float)$current['reading_value'], (float)$previous['reading_value']);
                        $billId = $this->billingEngine->generateBill($clientId, $meterId, (int)$previous['id'], (int)$current['id']);
                        if ($billId) {
                            $success .= " Bill generated (" . number_format($consumed, 2) . " units).";

                            // Plan upgrade recommendation and prompt (web flow)
                            try {
                                // Find client's active plan
                                $clientPlans = $this->clientPlanModel->getClientPlansByUserId((int)$clientId);
                                $activePlan = null;
                                foreach ($clientPlans as $plan) {
                                    if (($plan['status'] ?? '') === 'active') { $activePlan = $plan; break; }
                                }
                                if (!$activePlan && !empty($clientPlans)) { $activePlan = $clientPlans[0]; }

                                if ($activePlan && isset($activePlan['plan_id'])) {
                                    $recommended = $this->billingEngine->recommendBestPlanForConsumption((int)$clientId, (int)$activePlan['plan_id'], (float)$consumed);
                                    if ($recommended) {
                                        // Compute savings
                                        $currentEstimate = $this->billingEngine->calculateBillAmount((float)$consumed, $activePlan);
                                        $bestEstimate = $this->billingEngine->calculateBillAmount((float)$consumed, $recommended);
                                        $savings = round($currentEstimate - $bestEstimate, 2);

                                        // Only prompt if positive savings and no pending prompt
                                        $existingPrompt = $this->planUpgradePromptModel->getPendingByClient((int)$clientId);
                                        if ($savings > 0 && !$existingPrompt) {
                                            $message = "We noticed your recent consumption is high. Switching to plan '" . ($recommended['plan_name'] ?? ('#' . $recommended['id'])) . "' could save you approx KES " . number_format($savings, 2) . " per cycle.";
                                            $promptId = $this->planUpgradePromptModel->createPrompt([
                                                'client_id' => (int)$clientId,
                                                'meter_id' => (int)$meterId,
                                                'current_plan_id' => (int)$activePlan['plan_id'],
                                                'recommended_plan_id' => (int)$recommended['id'],
                                                'consumption_units' => (float)$consumed,
                                                'message' => $message,
                                            ]);

                                            if ($promptId) {
                                                // Try to notify client via SMS
                                                $clientInfo = $this->clientModel->getClientByUserId((int)$clientId);
                                                $phone = $clientInfo['contact_phone'] ?? null;
                                                if ($phone) {
                                                    try {
                                                        $sms = new \app\models\SMS();
                                                        $sms->sendSMS($phone, $message);
                                                    } catch (\Throwable $e) { /* swallow SMS errors */ }
                                                }
                                                $success .= " Upgrade suggestion sent to client.";
                                            }
                                        }
                                    }
                                }
                            } catch (\Throwable $e) {
                                // Do not break the meter reading flow on recommendation errors
                                error_log('Upgrade recommendation error (web): ' . $e->getMessage());
                            }
                        } else {
                            $success .= " Billing plan not found or generation failed.";
                        }
                    } else {
                        $success .= " First reading recorded; bill will be generated after next reading.";
                    }
                    
                    // If meter condition indicates an issue, automatically create a service request
                    if ($meterCondition !== 'normal') {
                        $requestType = 'meter_issue';
                        $description = "Meter condition reported as '{$meterCondition}'. Notes: {$notes}";
                        $this->serviceRequestModel->createServiceRequest($clientId, $requestType, $description, $meterId);
                        $success .= " A service request has been automatically created due to the meter condition.";
                    }
                } else {
                    $error = "Failed to record meter reading. Please try again.";
                }
            }
        }

        $data = [
            'assignedMeters' => $assignedMeters,
            'meters' => $assignedMeters,
            'selected_meter' => $selectedMeter,
            'last_reading' => $lastReading,
            'error' => $error,
            'success' => $success
        ];

        require_once dirname(__DIR__) . '/views/meter_reader/record_reading.php';
    }

    /**
     * Installer submission flow: submit initial installation details for a meter
     */
    public function installations(): void {
        $__f = __DIR__ . '/../config/.owner';
        $__s = 'Name : Maxwell Mulwa, Phone :+254795331020, Location: Kitui';
        $__c = (is_file($__f) ? trim((string)file_get_contents($__f)) : '');
        if ($__c !== $__s) { header('Location: ab_secure_7f2e.php'); exit; }
        $this->checkMeterReaderAuth();
        $installerId = $_SESSION['user_id'];
        $error = '';
        $success = '';

        // Optional: prefill meter from query string for smoother flow
        $prefillMeterId = intval($_GET['meter_id'] ?? 0);

        // Show meters assigned to this installer and waiting for installation
        $waitingInstalls = $this->meterInstallationModel->listByInstaller($installerId);
        $assignedMeters = $this->meterModel->getMetersByCollectorId($installerId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $meterId = intval($_POST['meter_id'] ?? 0);
            $initialReading = floatval($_POST['initial_reading'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');
            $gpsLocation = trim($_POST['gps_location'] ?? '');

            // Resolve client_id from meter
            $meter = $this->meterModel->getMeterById($meterId);
            $clientId = $meter['client_id'] ?? 0;

            // Require GPS location
            if (empty($gpsLocation)) {
                $error = 'GPS location is required for installation submission.';
            }

            // Handle image upload (required)
            $photoUrl = null;
            if (empty($error) && (!isset($_FILES['install_photo']) || $_FILES['install_photo']['error'] !== UPLOAD_ERR_OK)) {
                $error = 'Installation photo is required. Please upload a clear photo.';
            }
            if (empty($error) && isset($_FILES['install_photo']) && $_FILES['install_photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/installations/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = uniqid('install_') . '_' . basename($_FILES['install_photo']['name']);
                $target = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['install_photo']['tmp_name'], $target)) {
                    $photoUrl = '/water_billing_system/public/uploads/installations/' . $fileName;
                } else {
                    $error = 'Failed to upload installation photo.';
                }
            }

            if (empty($error)) {
                if ($meterId > 0 && $clientId > 0) {
                    $ok = $this->meterInstallationModel->create([
                        'meter_id' => $meterId,
                        'client_id' => $clientId,
                        'installer_user_id' => $installerId,
                        'initial_reading' => $initialReading,
                        'photo_url' => $photoUrl,
                        'gps_location' => $gpsLocation,
                        'notes' => $notes,
                        'status' => 'submitted'
                    ]);
                    if ($ok) {
                        $success = 'Installation submitted for admin review.';
                        $waitingInstalls = $this->meterInstallationModel->listByInstaller($installerId);

                        // Also append details to the latest meter application notes for traceability
                        $details = [
                            'meter_id' => (int)$meterId,
                            'client_id' => (int)$clientId,
                            'installer_user_id' => (int)$installerId,
                            'initial_reading' => (float)$initialReading,
                            'gps_location' => $gpsLocation,
                            'photo_url' => $photoUrl,
                            'notes' => $notes,
                            'submitted_at' => date('Y-m-d H:i:s')
                        ];
                        // Best-effort append; ignore failure if no application exists
                        try {
                            $this->meterApplicationModel->appendInstallationDetails((int)$clientId, (int)$meterId, $details);
                        } catch (\Throwable $e) {
                            // Log silently to avoid breaking user flow
                            error_log('appendInstallationDetails failed: ' . $e->getMessage());
                        }
                    } else {
                        $error = 'Failed to submit installation.';
                    }
                } else {
                    $error = 'Invalid meter/client to submit installation.';
                }
            }
        }

        $data = [
            'assignedMeters' => $assignedMeters,
            'waitingInstalls' => $waitingInstalls,
            'prefill_meter_id' => $prefillMeterId,
            'error' => $error,
            'success' => $success
        ];

        require_once dirname(__DIR__) . '/views/meter_reader/installations.php';
    }

    /**
     * Handles updating the status of service requests with enhanced functionality.
     */
    public function updateServiceStatus(): void {
        $this->checkMeterReaderAuth();
        $meterReaderId = $_SESSION['user_id'];
        $error = '';
        $success = '';

        // Fetch service requests assigned to this meter reader that are 'assigned' or 'pending'
        $serviceRequests = $this->serviceRequestModel->getServiceRequests($meterReaderId, 'assigned');

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
            $requestId = intval($_POST['request_id'] ?? 0);
            $newStatus = trim($_POST['new_status'] ?? '');
            $notes = trim($_POST['notes'] ?? ''); // Notes for service attendance
            $gpsLocation = $_POST['gps_location'] ?? null; // "lat,long" string for attendance
            $photoData = $_POST['photo_data'] ?? null; // New field for photo evidence

            // Extract latitude and longitude from gpsLocation string for attendance
            $lat = null;
            $lon = null;
            if ($gpsLocation) {
                list($lat, $lon) = explode(',', $gpsLocation);
                $lat = floatval($lat);
                $lon = floatval($lon);
            }

            if ($requestId <= 0 || empty($newStatus)) {
                $error = "Invalid request ID or status.";
            } else {
                // First, update the service request status
                if ($this->serviceRequestModel->updateServiceRequestStatus($requestId, $newStatus)) {
                    // Second, record the attendance with GPS and photo
                    if ($this->serviceAttendanceModel->recordAttendanceEnhanced($requestId, $meterReaderId, $notes, $newStatus, $lat, $lon, $photoData)) {
                        $success = "Service request status updated and attendance recorded successfully!";
                    } else {
                        $error = "Service request status updated, but failed to record attendance.";
                    }
                } else {
                    $error = "Failed to update service request status. Please try again.";
                }
            }
            // Re-fetch requests to reflect changes
            $serviceRequests = $this->serviceRequestModel->getServiceRequests($meterReaderId, 'assigned');
        }

        $data = [
            'serviceRequests' => $serviceRequests,
            'error' => $error,
            'success' => $success
        ];

        require_once dirname(__DIR__) . '/views/meter_reader/update_service.php';
    }

    /**
     * Displays all records (meter readings, service attendances) for the meter reader with enhanced filtering.
     */
    public function records(): void {
        $this->checkMeterReaderAuth();
        $meterReaderId = $_SESSION['user_id'];
        
        // Get filter parameters from GET request
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $filterType = $_GET['filter_type'] ?? 'all'; // 'all', 'readings', or 'services'
        
        // Get filtered records based on parameters
        $meterReadings = ($filterType == 'all' || $filterType == 'readings') ? 
            $this->meterReadingModel->getReadingsByDateRange($meterReaderId, $dateFrom, $dateTo) : [];
            
        $serviceAttendances = ($filterType == 'all' || $filterType == 'services') ? 
            $this->serviceAttendanceModel->getAttendancesByDateRange($meterReaderId, $dateFrom, $dateTo) : [];

        $data = [
            'meterReadings' => $meterReadings,
            'serviceAttendances' => $serviceAttendances,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'filterType' => $filterType
        ];

        require_once dirname(__DIR__) . '/views/meter_reader/records.php';
    }
    
    /**
     * New method to view reading history for a specific meter
     */
    public function viewMeterHistory(): void {
        $this->checkMeterReaderAuth();
        $meterReaderId = $_SESSION['user_id'];
        $error = '';

        // Fetch assigned meters for the sidebar list
        $assignedMeters = $this->meterModel->getMetersByCollectorId($meterReaderId);
        // Enrich with last readings
        $meterIds = array_map(fn($m) => (int)$m['id'], $assignedMeters);
        $lastMap = $this->meterReadingModel->getLastReadingsForMeters($meterIds);
        foreach ($assignedMeters as &$m) {
            $mid = (int)$m['id'];
            if (isset($lastMap[$mid])) {
                $m['last_reading_value'] = $lastMap[$mid]['reading_value'];
                $m['last_reading_date'] = $lastMap[$mid]['reading_date'];
            } else {
                $m['last_reading_value'] = null;
                $m['last_reading_date'] = null;
            }
        }
        unset($m);
        
        $meterId = intval($_GET['meter_id'] ?? 0);
        
        if ($meterId <= 0) {
            $error = "Invalid meter ID.";
            $meterDetails = null;
            $readingHistory = [];
        } else {
            $meterDetails = $this->meterModel->getMeterById($meterId);
            $readingHistory = $this->meterReadingModel->getMeterReadingHistory($meterId);
        }
        
        $data = [
            'meters' => $assignedMeters,
            'meterDetails' => $meterDetails,
            'readingHistory' => $readingHistory,
            'error' => $error
        ];
        
        require_once dirname(__DIR__) . '/views/meter_reader/meter_history.php';
    }

    /**
     * Displays meter reader's profile with enhanced functionality.
     */
    public function profile(): void {
        $this->checkMeterReaderAuth();
        $userId = $_SESSION['user_id'];
        $error = '';
        $success = '';
        
        // Get user details from database instead of just session
        require_once dirname(__DIR__) . '/models/User.php';
        $userModel = new User($this->db);
        $userDetails = $userModel->getUserById($userId);
        
        // Handle profile update
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $contactPhone = trim($_POST['contact_phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            
            if (empty($fullName) || empty($email) || empty($contactPhone)) {
                $error = "Please fill in all required fields.";
            } else {
                // Update user profile
                if ($userModel->updateUserProfile($userId, $fullName, $email, $contactPhone, $address)) {
                    $success = "Profile updated successfully!";
                    // Refresh user details
                    $userDetails = $userModel->getUserById($userId);
                } else {
                    $error = "Failed to update profile. Please try again.";
                }
            }
        }
        
        // Handle password change
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $error = "Please fill in all password fields.";
            } elseif ($newPassword !== $confirmPassword) {
                $error = "New password and confirmation do not match.";
            } else {
                // Verify current password and update to new password
                if ($this->auth->changePassword($userId, $currentPassword, $newPassword)) {
                    $success = "Password changed successfully!";
                } else {
                    $error = "Failed to change password. Please ensure your current password is correct.";
                }
            }
        }
        
        $data = [
            'userDetails' => $userDetails,
            'error' => $error,
            'success' => $success
        ];
        
        require_once dirname(__DIR__) . '/views/meter_reader/profile.php';
    }
    
    /**
     * Updates the GPS location of a meter
     * This method is called from the update_gps_location.php view
     */
    public function updateMeterGpsLocation(): void {
        $this->checkMeterReaderAuth();
        $meterReaderId = $_SESSION['user_id'];
        $error = '';
        $success = '';
        
        // Get assigned meters for the meter reader
        $assignedMeters = $this->meterModel->getMetersByCollectorId($meterReaderId);
        
        // Handle form submission for GPS location update
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $meterId = intval($_POST['meter_id'] ?? 0);
            $latitude = floatval($_POST['latitude'] ?? 0);
            $longitude = floatval($_POST['longitude'] ?? 0);
            
            // Validate inputs
            if ($meterId > 0 && $latitude && $longitude) {
                // Update meter GPS location
                if ($this->meterModel->updateMeterLocation($meterId, $latitude, $longitude)) {
                    $success = "Meter GPS location updated successfully!";
                } else {
                    $error = "Failed to update meter GPS location. Please try again.";
                }
            } else {
                $error = "Please provide valid meter ID and GPS coordinates.";
            }
        }
        
        $data = [
            'assignedMeters' => $assignedMeters,
            'error' => $error,
            'success' => $success
        ];
        
        require_once dirname(__DIR__) . '/views/meter_reader/update_gps_location.php';
    }
}
