<?php
// app/models/ServiceAttendance.php

require_once __DIR__ . '/../core/Database.php';

class ServiceAttendance {
    private Database $db;

    public function __construct(Database $database_instance) {
        $this->db = $database_instance;
    }

    /**
     * Records a service attendance by a collector.
     *
     * @param int $serviceRequestId The ID of the service request.
     * @param int $collectorId The ID of the collector.
     * @param string $notes Any notes from the attendance.
     * @param string $statusAfterAttendance The status set after attendance (e.g., 'serviced', 'unable to complete').
     * @param float|null $latitude Optional latitude of attendance.
     * @param float|null $longitude Optional longitude of attendance.
     * @return bool True on success, false on failure.
     */
    public function recordAttendance(int $serviceRequestId, int $collectorId, string $notes, string $statusAfterAttendance, ?float $latitude = null, ?float $longitude = null): bool {
        $this->db->query('INSERT INTO service_attendances (service_request_id, collector_id, attendance_date, notes, status_update, latitude, longitude) VALUES (?, ?, NOW(), ?, ?, ?, ?)');
        $this->db->bind([$serviceRequestId, $collectorId, $notes, $statusAfterAttendance, $latitude, $longitude]);
        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            error_log("Failed to record service attendance: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Enhanced attendance record with optional photo evidence.
     * Ensures photo_data column exists and stores Base64 image.
     */
    public function recordAttendanceEnhanced(int $serviceRequestId, int $collectorId, string $notes, string $statusAfterAttendance, ?float $latitude = null, ?float $longitude = null, ?string $photoData = null): bool {
        // Ensure photo_data column exists
        try {
            $this->db->query("SHOW COLUMNS FROM service_attendances LIKE 'photo_data'");
            $col = $this->db->single();
            $this->db->closeStmt();
            if (!$col) {
                $this->db->query("ALTER TABLE service_attendances ADD COLUMN photo_data LONGTEXT NULL AFTER longitude");
                $this->db->execute();
                $this->db->closeStmt();
            }
        } catch (\Throwable $e) {
            // proceed even if ALTER fails; we will attempt insert without photo
            try { $this->db->closeStmt(); } catch (\Throwable $ee) {}
        }

        // If photo_data column exists, include it in insert; else fallback
        $hasPhotoCol = false;
        try {
            $this->db->query("SHOW COLUMNS FROM service_attendances LIKE 'photo_data'");
            $hasPhotoCol = (bool)$this->db->single();
            $this->db->closeStmt();
        } catch (\Throwable $e) {}

        if ($hasPhotoCol) {
            $this->db->query('INSERT INTO service_attendances (service_request_id, collector_id, attendance_date, notes, status_update, latitude, longitude, photo_data) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)');
            $this->db->bind([$serviceRequestId, $collectorId, $notes, $statusAfterAttendance, $latitude, $longitude, $photoData]);
        } else {
            $this->db->query('INSERT INTO service_attendances (service_request_id, collector_id, attendance_date, notes, status_update, latitude, longitude) VALUES (?, ?, NOW(), ?, ?, ?, ?)');
            $this->db->bind([$serviceRequestId, $collectorId, $notes, $statusAfterAttendance, $latitude, $longitude]);
        }

        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            error_log("Failed to record enhanced service attendance: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Retrieves service attendances for a specific collector.
     * Joins with service_requests, users, and services for comprehensive data.
     *
     * @param int $collectorId The ID of the collector.
     * @return array An array of associative arrays, each representing a service attendance.
     */
    public function getAttendancesByCollectorId(int $collectorId): array {
        $this->db->query('
            SELECT sa.*, sr.description AS request_description, u.username AS client_username, s.service_name
            FROM service_attendances sa
            JOIN service_requests sr ON sa.service_request_id = sr.id
            JOIN users u ON sr.client_id = u.id
            JOIN services s ON sr.service_id = s.id
            WHERE sa.collector_id = ?
            ORDER BY sa.attendance_date DESC
        ');
        $this->db->bind([$collectorId]);
        $attendances = $this->db->resultSet();
        $this->db->closeStmt();
        return $attendances;
    }

    /**
     * Retrieves all service attendances (for admin view).
     *
     * @return array An array of associative arrays, each representing a service attendance.
     */
    public function getAllAttendances(): array {
        $this->db->query('
            SELECT sa.*, sr.description AS request_description, u.username AS client_username, s.service_name, c.username AS collector_username
            FROM service_attendances sa
            JOIN service_requests sr ON sa.service_request_id = sr.id
            JOIN users u ON sr.client_id = u.id
            JOIN services s ON sr.service_id = s.id
            JOIN users c ON sa.collector_id = c.id
            ORDER BY sa.attendance_date DESC
        ');
        $attendances = $this->db->resultSet();
        $this->db->closeStmt();
        return $attendances;
    }
    
    /**
     * Retrieves service attendances for a specific collector within a date range.
     * Joins with service_requests, users, and services for comprehensive data.
     *
     * @param int $collectorId The ID of the collector.
     * @param string $dateFrom The start date in Y-m-d format.
     * @param string $dateTo The end date in Y-m-d format.
     * @return array An array of associative arrays, each representing a service attendance.
     */
    public function getAttendancesByDateRange(int $collectorId, string $dateFrom, string $dateTo): array {
        $this->db->query('
            SELECT sa.*, sr.description AS request_description, u.username AS client_username, s.service_name
            FROM service_attendances sa
            JOIN service_requests sr ON sa.service_request_id = sr.id
            JOIN users u ON sr.client_id = u.id
            JOIN services s ON sr.service_id = s.id
            WHERE sa.collector_id = ?
            AND DATE(sa.attendance_date) BETWEEN ? AND ?
            ORDER BY sa.attendance_date DESC
        ');
        $this->db->bind([$collectorId, $dateFrom, $dateTo]);
        $attendances = $this->db->resultSet();
        $this->db->closeStmt();
        return $attendances;
    }
}
