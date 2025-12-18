<?php
// app/controllers/CollectorController.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Meter.php';
require_once __DIR__ . '/../models/MeterReading.php';
require_once __DIR__ . '/../models/MeterImage.php'; // NEW: Include MeterImage model
require_once __DIR__ . '/../models/ServiceRequest.php';
require_once __DIR__ . '/../models/ServiceAttendance.php';
require_once __DIR__ . '/../models/MeterInstallation.php';

class CollectorController {
    private Database $db;
    private Auth $auth;
    private Meter $meterModel;
    private MeterReading $meterReadingModel;
    private MeterImage $meterImageModel; // NEW: Declare MeterImage model
    private ServiceRequest $serviceRequestModel;
    private ServiceAttendance $serviceAttendanceModel;
	private MeterInstallation $meterInstallationModel;

    public function __construct(Database $database_instance, Auth $auth_instance) {
        $this->db = $database_instance;
        $this->auth = $auth_instance;
        $this->meterModel = new Meter($this->db);
        $this->meterReadingModel = new MeterReading($this->db);
        $this->meterImageModel = new MeterImage($this->db); // NEW: Initialize MeterImage model
        $this->serviceRequestModel = new ServiceRequest($this->db);
        $this->serviceAttendanceModel = new ServiceAttendance($this->db);
		$this->meterInstallationModel = new MeterInstallation($this->db);
    }

    /**
     * Helper to check if the user is a collector.
     * Redirects to login if not.
     */
    private function checkCollectorAuth(): void {
        if (!$this->auth->isLoggedIn() || $this->auth->getUserRole() !== 'collector') {
            header('Location: index.php?page=login');
            exit();
        }
    }

    /**
     * Displays the main collector dashboard.
     * Shows summary of assigned tasks (meters and service requests).
     */
    public function dashboard(): void {
        $this->checkCollectorAuth();
        $collectorId = $_SESSION['user_id'];

        $assignedMeters = $this->meterModel->getMetersAssignedToCollector($collectorId);
        // Fetch service requests assigned to this collector that are 'assigned' or 'pending'
        $pendingServiceRequests = $this->serviceRequestModel->getServiceRequests($collectorId, 'assigned');
        $totalReadingsToday = $this->meterReadingModel->getReadingsCountByDate($collectorId, date('Y-m-d'));
        $totalReadingsThisMonth = $this->meterReadingModel->getReadingsCountByMonth($collectorId, date('Y-m'));

        $data = [
            'assignedMeters' => $assignedMeters,
            'pendingServiceRequests' => $pendingServiceRequests,
            'username' => $_SESSION['username'],
            'totalReadingsToday' => $totalReadingsToday,
            'totalReadingsThisMonth' => $totalReadingsThisMonth
        ];

        require_once __DIR__ . '/../views/collector/dashboard.php';
    }

    /**
     * Handles recording new meter readings.
     * Includes form for image, GPS, serial number, and reading value.
     */
    public function recordReading(): void {
        $this->checkCollectorAuth();
        $collectorId = $_SESSION['user_id'];
        $error = '';
        $success = '';

        $assignedMeters = $this->meterModel->getMetersByCollectorId($collectorId);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $meterId = intval($_POST['meter_id'] ?? 0);
            $readingValue = floatval($_POST['reading_value'] ?? 0);
            $photoData = $_POST['photo_data'] ?? null; // Base64 image data
            $gpsLocation = $_POST['gps_location'] ?? null; // "lat,long" string

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
                // Pass client_id to recordReading as it's needed for meter_images table
                if ($this->meterReadingModel->recordReading($meterId, $readingValue, $collectorId, $clientId, $photoData, $lat, $lon)) {
                    $success = "Meter reading recorded successfully!";
                    // Optionally update the meter's next_update_date or last_reading_date here if your schema supports it
                } else {
                    $error = "Failed to record meter reading. Please try again.";
                }
            }
        }

        $data = [
            'assignedMeters' => $assignedMeters,
            'error' => $error,
            'success' => $success
        ];

        require_once __DIR__ . '/../views/collector/record_reading.php';
    }

    /**
     * Handles updating the status of service requests.
     */
    public function updateServiceStatus(): void {
        $this->checkCollectorAuth();
        $collectorId = $_SESSION['user_id'];
        $error = '';
        $success = '';

        // Fetch only service requests assigned to this collector
        $serviceRequests = $this->serviceRequestModel->getServiceRequests($collectorId, 'assigned');

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
            $requestId = intval($_POST['request_id'] ?? 0);
            $newStatus = trim($_POST['new_status'] ?? '');
            $notes = trim($_POST['notes'] ?? ''); // Notes for service attendance
            $gpsLocation = $_POST['gps_location'] ?? null; // "lat,long" string for attendance
            $photoData = $_POST['photo_data'] ?? null; // Base64 photo evidence

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
                    // Second, record the attendance with GPS and photo evidence
                    if ($this->serviceAttendanceModel->recordAttendanceEnhanced($requestId, $collectorId, $notes, $newStatus, $lat, $lon, $photoData)) {
                        $success = "Service request status updated and attendance recorded successfully!";
                    } else {
                        $error = "Service request status updated, but failed to record attendance.";
                    }
                } else {
                    $error = "Failed to update service request status. Please try again.";
                }
            }
            // Re-fetch requests to reflect changes
            $serviceRequests = $this->serviceRequestModel->getServiceRequests($collectorId, 'assigned');
        }

        $data = [
            'serviceRequests' => $serviceRequests,
            'error' => $error,
            'success' => $success
        ];

        require_once __DIR__ . '/../views/collector/update_service.php';
    }

    /**
     * Displays all records (meter readings, service attendances) for the collector.
     */
    public function records(): void {
        $this->checkCollectorAuth();
        $collectorId = $_SESSION['user_id'];

        $meterReadings = $this->meterReadingModel->getReadingsByCollectorId($collectorId);
        $serviceAttendances = $this->serviceAttendanceModel->getAttendancesByCollectorId($collectorId);

        $data = [
            'meterReadings' => $meterReadings,
            'serviceAttendances' => $serviceAttendances
        ];

        require_once __DIR__ . '/../views/collector/records.php';
    }

    /**
     * Displays collector's profile.
     */
    public function profile(): void {
        $this->checkCollectorAuth();
        // Fetch collector's own details if needed from users table
        // For now, we'll just pass username and role from session
        $data = [
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
        ];
        require_once __DIR__ . '/../views/collector/profile.php';
    }

	/**
	 * Installer submission flow for collectors: submit initial installation details for a meter
	 */
	public function installations(): void {
		$this->checkCollectorAuth();
		$collectorId = $_SESSION['user_id'];
		$error = '';
		$success = '';

		$waitingInstalls = $this->meterInstallationModel->listByInstaller($collectorId);
		$assignedMeters = $this->meterModel->getMetersByCollectorId($collectorId);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$meterId = intval($_POST['meter_id'] ?? 0);
			$initialReading = floatval($_POST['initial_reading'] ?? 0);
			$notes = trim($_POST['notes'] ?? '');
			$gpsLocation = trim($_POST['gps_location'] ?? '');

			$meter = $this->meterModel->getMeterById($meterId);
			$clientId = $meter['client_id'] ?? 0;

			$photoUrl = null;
			if (isset($_FILES['install_photo']) && $_FILES['install_photo']['error'] === UPLOAD_ERR_OK) {
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
						'installer_user_id' => $collectorId,
						'initial_reading' => $initialReading,
						'photo_url' => $photoUrl,
						'gps_location' => $gpsLocation,
						'notes' => $notes,
						'status' => 'submitted'
					]);
					if ($ok) {
						$success = 'Installation submitted for admin review.';
						$waitingInstalls = $this->meterInstallationModel->listByInstaller($collectorId);
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
			'error' => $error,
			'success' => $success
		];

		require_once __DIR__ . '/../views/collector/installations.php';
	}
}
