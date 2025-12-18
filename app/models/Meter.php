<?php
// app/models/Meter.php

require_once dirname(__DIR__) . '/core/Database.php';

class Meter {
    private Database $db;

    public function __construct(Database $database_instance) {
        $this->db = $database_instance;
    }

    /**
     * Note: The getMetersByCollectorId() method is implemented below with a more comprehensive join.
     * This comment is kept as a reminder that the method exists further down in this file.
     */
     
    /**
     * Gets all available meters that can be assigned to clients.
     * 
     * @return array Array of available meters.
     */
    public function getAvailableMeters(): array {
        try {
            // Ensure source columns exist before filtering
            $this->ensureSourceColumns();
            // Get all company-provided meters that are not assigned to any client
            $this->db->query('SELECT id, serial_number, meter_type, initial_reading, status, photo_url, COALESCE(source, "company") AS source FROM meters WHERE client_id IS NULL AND (source IS NULL OR source = "company") ORDER BY id DESC');
            $meters = $this->db->resultSet();
            $this->db->closeStmt();
            return $meters;
        } catch (Exception $e) {
            error_log("Meter Model Exception in getAvailableMeters: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets available meters for a specific client context.
     * Includes company meters plus any client-added meters by this user that are unassigned.
     */
    public function getAvailableMetersForClient(int $userId): array {
        try {
            $this->ensureSourceColumns();
            $sql = 'SELECT id, serial_number, meter_type, initial_reading, status, photo_url, COALESCE(source, "company") AS source
                    FROM meters m
                    WHERE m.client_id IS NULL
                      AND (
                           (m.source IS NULL OR m.source = "company")
                           OR (m.source = "client" AND m.added_by_user_id = ?)
                      )
                      AND NOT EXISTS (
                           SELECT 1 FROM meter_applications ma
                           WHERE ma.meter_id = m.id AND ma.client_id = ?
                                 AND ma.status IN ("pending","submitted_to_admin","admin_verified","approved","confirmed")
                      )
                    ORDER BY m.id DESC';
            $this->db->query($sql);
            $this->db->bind([$userId, $userId]);
            $meters = $this->db->resultSet();
            $this->db->closeStmt();
            return $meters;
        } catch (Exception $e) {
            error_log("Meter Model Exception in getAvailableMetersForClient: " . $e->getMessage());
            return [];
        }
    }

    /** Check if a meter serial already exists */
    public function existsSerial(string $serialNumber): bool {
        try {
            $this->db->query('SELECT COUNT(*) AS c FROM meters WHERE serial_number = ?');
            $this->db->bind([$serialNumber]);
            $row = $this->db->single();
            $this->db->closeStmt();
            return isset($row['c']) ? ((int)$row['c'] > 0) : false;
        } catch (Exception $e) {
            error_log('Meter Model Exception in existsSerial: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Adds a new meter to the system.
     *
     * @param string $serialNumber Unique serial number of the meter.
     * @param string $meterType Type of meter (e.g., 'residential', 'commercial').
     * @param float $initialReading Initial reading of the meter.
     * @param string $status Initial status (e.g., 'in_stock', 'available').
     * @param string|null $photoUrl Optional URL to a photo of the meter.
     * @param string|null $gpsLocation Optional GPS location (e.g., 'lat,long' string).
     * @return bool True on success, false on failure.
     */
    public function addMeter(string $serialNumber, string $meterType, float $initialReading, string $status = 'in_stock', ?string $photoUrl = null, ?string $gpsLocation = null): bool {
        try {
            $this->db->query('INSERT INTO meters (serial_number, meter_type, initial_reading, status, photo_url, gps_location) VALUES (?, ?, ?, ?, ?, ?)');
            $this->db->bind([$serialNumber, $meterType, $initialReading, $status, $photoUrl, $gpsLocation]);
            if ($this->db->execute()) {
                $this->db->closeStmt();
                return true;
            } else {
                error_log("Meter Model Error: Failed to add meter. DB Error: " . $this->db->getError());
                $this->db->closeStmt();
                return false;
            }
        } catch (Exception $e) {
            error_log("Meter Model Exception in addMeter: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Adds a new meter provided/owned by a client, tracking source and who added it.
     * Returns inserted meter ID on success, 0 on failure.
     */
    public function addClientMeter(string $serialNumber, string $meterType, float $initialReading, string $status = 'functional', ?string $photoUrl = null, ?string $gpsLocation = null, int $clientUserId = 0): int {
        try {
            $this->ensureSourceColumns();
            $this->db->query('INSERT INTO meters (serial_number, meter_type, initial_reading, status, photo_url, gps_location, source, added_by_user_id) VALUES (?, ?, ?, ?, ?, ?, "client", ?)');
            $this->db->bind([$serialNumber, $meterType, $initialReading, $status, $photoUrl, $gpsLocation, $clientUserId]);
            if ($this->db->execute()) {
                $insertId = $this->db->lastInsertId();
                $this->db->closeStmt();
                return (int)$insertId;
            } else {
                error_log("Meter Model Error: Failed to add client meter. DB Error: " . $this->db->getError());
                $this->db->closeStmt();
                return 0;
            }
        } catch (Exception $e) {
            error_log("Meter Model Exception in addClientMeter: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Updates an existing meter's details.
     *
     * @param int $meterId The ID of the meter to update.
     * @param string $serialNumber Updated serial number.
     * @param string $meterType Updated meter type.
     * @param float $initialReading Updated initial reading.
     * @param string $status Updated status.
     * @param string|null $photoUrl Updated photo URL.
     * @param string|null $gpsLocation Updated GPS location.
     * @param int|null $clientId Client ID if assigned, null otherwise.
     * @param string|null $installationDate Installation date if installed.
     * @param int|null $assignedCollectorId Collector ID if assigned, null otherwise.
     * @return bool True on success, false on failure.
     */
    public function updateMeter(int $meterId, string $serialNumber, string $meterType, float $initialReading, string $status, ?string $photoUrl, ?string $gpsLocation, ?int $clientId, ?string $installationDate, ?int $assignedCollectorId): bool {
        try {
            $sql = 'UPDATE meters SET serial_number = ?, meter_type = ?, initial_reading = ?, status = ?, photo_url = ?, gps_location = ?, client_id = ?, installation_date = ?, assigned_collector_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
            $this->db->query($sql);
            $this->db->bind([$serialNumber, $meterType, $initialReading, $status, $photoUrl, $gpsLocation, $clientId, $installationDate, $assignedCollectorId, $meterId]);
            if ($this->db->execute()) {
                $this->db->closeStmt();
                return true;
            } else {
                error_log("Meter Model Error: Failed to update meter. DB Error: " . $this->db->getError());
                $this->db->closeStmt();
                return false;
            }
        } catch (Exception $e) {
            error_log("Meter Model Exception in updateMeter: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a meter by its ID.
     *
     * @param int $meterId The ID of the meter to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteMeter(int $meterId): bool {
        try {
            $this->db->query('DELETE FROM meters WHERE id = ?');
            $this->db->bind([$meterId]);
            if ($this->db->execute()) {
                $this->db->closeStmt();
                return true;
            } else {
                error_log("Meter Model Error: Failed to delete meter. DB Error: " . $this->db->getError());
                $this->db->closeStmt();
                return false;
            }
        } catch (Exception $e) {
            error_log("Meter Model Exception in deleteMeter: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Updates a meter's GPS location.
     *
     * @param int $meterId The ID of the meter to update.
     * @param float $latitude The latitude coordinate.
     * @param float $longitude The longitude coordinate.
     * @return bool True on success, false on failure.
     */
    public function updateMeterLocation(int $meterId, float $latitude, float $longitude): bool {
        try {
            // Format GPS location as a string or update latitude/longitude columns if they exist
            $gpsLocation = $latitude . ',' . $longitude;
            
            $this->db->query('UPDATE meters SET gps_location = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $this->db->bind([$gpsLocation, $meterId]);
            
            if ($this->db->execute()) {
                $this->db->closeStmt();
                return true;
            } else {
                error_log("Meter Model Error: Failed to update meter location. DB Error: " . $this->db->getError());
                $this->db->closeStmt();
                return false;
            }
        } catch (Exception $e) {
            error_log("Meter Model Exception in updateMeterLocation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves a single meter by its ID.
     *
     * @param int $meterId The ID of the meter.
     * @return array|null An associative array of meter data, or null if not found.
     */
    public function getMeterById(int $meterId): ?array {
        try {
            $this->db->query('SELECT m.*, u.username AS client_username, c.username AS collector_username FROM meters m LEFT JOIN users u ON m.client_id = u.id LEFT JOIN users c ON m.assigned_collector_id = c.id WHERE m.id = ?');
            $this->db->bind([$meterId]);
            $meter = $this->db->single();
            $this->db->closeStmt();
            return $meter;
        } catch (Exception $e) {
            error_log("Meter Model Exception in getMeterById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieves all meters, optionally filtered by status.
     * Joins with users table to get client and collector usernames.
     *
     * @param string|null $status Optional status to filter by (e.g., 'available', 'assigned').
     * @return array An array of associative arrays, each representing a meter.
     */
    public function getAllMeters(?string $status = null): array {
        try {
            $sql = 'SELECT m.*, u.username AS client_username, c.username AS collector_username FROM meters m LEFT JOIN users u ON m.client_id = u.id LEFT JOIN users c ON m.assigned_collector_id = c.id';
            $conditions = [];
            $params = [];

            if ($status) {
                $conditions[] = 'm.status = ?';
                $params[] = $status;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
            $sql .= ' ORDER BY m.created_at DESC';
            
            // If no status is provided, remove the WHERE clause entirely
            if (empty($conditions)) {
                $sql = 'SELECT m.*, u.username AS client_username, c.username AS collector_username FROM meters m LEFT JOIN users u ON m.client_id = u.id LEFT JOIN users c ON m.assigned_collector_id = c.id ORDER BY m.created_at DESC';
            }


            $this->db->query($sql);
            if (!empty($params)) {
                $this->db->bind($params);
            }
            $meters = $this->db->resultSet();
            $this->db->closeStmt();
            return $meters;
        } catch (Exception $e) {
            error_log("Meter Model Exception in getAllMeters: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Assigns a meter to a client.
     *
     * @param int $meterId The ID of the meter.
     * @param int $clientId The ID of the client (user_id).
     * @param string $installationDate The date of installation.
     * @return bool True on success, false on failure.
     */
    public function assignMeterToClient(int $meterId, int $clientId, string $installationDate): bool {
        try {
            $this->db->query('UPDATE meters SET client_id = ?, installation_date = ?, status = "assigned", assigned_collector_id = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $this->db->bind([$clientId, $installationDate, $meterId]);
            if ($this->db->execute()) {
                $this->db->closeStmt();
                return true;
            } else {
                error_log("Meter Model Error: Failed to assign meter to client. DB Error: " . $this->db->getError());
                $this->db->closeStmt();
                return false;
            }
        } catch (Exception $e) {
            error_log("Meter Model Exception in assignMeterToClient: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Unassigns a meter from a client and resets installation info.
     */
    public function unassignMeterFromClient(int $meterId): bool {
        try {
            $this->db->query('UPDATE meters SET client_id = NULL, installation_date = NULL, status = "available", updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $this->db->bind([$meterId]);
            if ($this->db->execute()) {
                $this->db->closeStmt();
                return true;
            } else {
                error_log("Meter Model Error: Failed to unassign meter from client. DB Error: " . $this->db->getError());
                $this->db->closeStmt();
                return false;
            }
        } catch (Exception $e) {
            error_log("Meter Model Exception in unassignMeterFromClient: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Assigns a meter to a collector for setup/inspection.
     *
     * @param int $meterId The ID of the meter.
     * @param int $collectorId The ID of the collector (user_id).
     * @return bool True on success, false on failure.
     */
    public function assignMeterToCollector(int $meterId, int $collectorId): bool {
        try {
            $this->db->query('UPDATE meters SET assigned_collector_id = ?, status = "assigned_to_collector", client_id = NULL, installation_date = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $this->db->bind([$collectorId, $meterId]);
            if ($this->db->execute()) {
                $this->db->closeStmt();
                return true;
            } else {
                error_log("Meter Model Error: Failed to assign meter to collector. DB Error: " . $this->db->getError());
                $this->db->closeStmt();
                return false;
            }
        } catch (Exception $e) {
            error_log("Meter Model Exception in assignMeterToCollector: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retrieves all meters assigned to a specific collector.
     *
     * @param int $collectorId The ID of the collector (user_id).
     * @return array An array of associative arrays, each representing a meter assigned to the collector.
     */
    public function getMetersByCollectorId(int $collectorId): array {
        try {
            $sql = 'SELECT m.*, 
                           u.username AS client_username, 
                           u.full_name AS client_name, 
                           u.email AS client_email, 
                           u.contact_phone AS client_phone, 
                           u.address AS client_address 
                    FROM meters m 
                    LEFT JOIN users u ON m.client_id = u.id 
                    WHERE m.assigned_collector_id = ? 
                    ORDER BY m.created_at DESC';
            $this->db->query($sql);
            $this->db->bind([$collectorId]);
            $meters = $this->db->resultSet();
            $this->db->closeStmt();
            return $meters;
        } catch (Exception $e) {
            error_log("Meter Model Exception in getMetersByCollectorId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Unassigns a meter from a collector.
     *
     * @param int $meterId The ID of the meter.
     * @return bool True on success, false on failure.
     */
    public function unassignMeterFromCollector(int $meterId): bool {
        try {
            $this->db->query('UPDATE meters SET assigned_collector_id = NULL, status = "available", updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $this->db->bind([$meterId]);
            if ($this->db->execute()) {
                $this->db->closeStmt();
                return true;
            } else {
                error_log("Meter Model Error: Failed to unassign meter from collector. DB Error: " . $this->db->getError());
                $this->db->closeStmt();
                return false;
            }
        } catch (Exception $e) {
            error_log("Meter Model Exception in unassignMeterFromCollector: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves meters assigned to a specific collector.
     *
     * @param int $collectorId The ID of the collector.
     * @return array An array of meters assigned to the collector.
     */
    public function getMetersAssignedToCollector(int $collectorId): array {
        try {
            $this->db->query('SELECT m.*, u.username AS client_username FROM meters m LEFT JOIN users u ON m.client_id = u.id WHERE m.assigned_collector_id = ? ORDER BY m.created_at DESC');
            $this->db->bind([$collectorId]);
            $meters = $this->db->resultSet();
            $this->db->closeStmt();
            return $meters;
        } catch (Exception $e) {
            error_log("Meter Model Exception in getMetersAssignedToCollector: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieves meters assigned to a specific client.
     *
     * @param int $clientId The ID of the client.
     * @return array An array of meters assigned to the client.
     */
    public function getMetersAssignedToClient(int $clientId): array {
        try {
            $this->ensureSourceColumns();
            $this->db->query('SELECT m.*, COALESCE(m.source, "company") AS source, c.username AS collector_username FROM meters m LEFT JOIN users c ON m.assigned_collector_id = c.id WHERE m.client_id = ? ORDER BY m.created_at DESC');
            $this->db->bind([$clientId]);
            $meters = $this->db->resultSet();
            $this->db->closeStmt();
            return $meters;
        } catch (Exception $e) {
            error_log("Meter Model Exception in getMetersAssignedToClient: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Retrieves all meters with client names for billing purposes.
     *
     * @return array An array of meters with client names.
     */
    public function getAllMetersWithClientNames(): array {
        try {
            $this->db->query('SELECT m.*, u.username AS client_username, u.full_name AS client_name 
                             FROM meters m 
                             JOIN users u ON m.client_id = u.id 
                             WHERE m.client_id IS NOT NULL 
                             ORDER BY COALESCE(u.full_name, u.username) ASC');
            $meters = $this->db->resultSet();
            $this->db->closeStmt();
            return $meters;
        } catch (Exception $e) {
            error_log("Meter Model Exception in getAllMetersWithClientNames: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verifies an installed meter by admin.
     *
     * @param int $meterId The ID of the meter.
     * @param string $verificationDate The date when the meter was verified.
     * @param string $verificationNotes Optional notes about the verification.
     * @return bool True on success, false on failure.
     */
    public function verifyMeter(int $meterId, string $verificationDate, string $verificationNotes = ''): bool {
        try {
            $this->db->query('UPDATE meters SET verification_date = ?, verification_notes = ?, status = "verified", updated_at = CURRENT_TIMESTAMP WHERE id = ? AND status = "installed"');
            $this->db->bind([$verificationDate, $verificationNotes, $meterId]);
            if ($this->db->execute()) {
                $this->db->closeStmt();
                return true;
            } else {
                error_log("Meter Model Error: Failed to verify meter. DB Error: " . $this->db->getError());
                $this->db->closeStmt();
                return false;
            }
        } catch (Exception $e) {
            error_log("Meter Model Exception in verifyMeter: " . $e->getMessage());
            return false;
        }
    }

    public function markWaitingForInstallation(int $meterId, int $installerUserId): bool {
        try {
            $this->db->query('UPDATE meters SET assigned_collector_id = ?, status = "waiting_installation", updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $this->db->bind([$installerUserId, $meterId]);
            if ($this->db->execute()) {
                $this->db->closeStmt();
                return true;
            }
            $this->db->closeStmt();
            return false;
        } catch (Exception $e) {
            error_log("Meter Model Exception in markWaitingForInstallation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retrieves all meters with detailed information including client and collector details.
     *
     * @return array An array of meters with detailed information.
     */
    public function getAllMetersWithDetails(): array {
        try {
            $this->ensureSourceColumns();
            $sql = 'SELECT m.id, m.serial_number, m.meter_type, m.initial_reading, m.status, m.photo_url, m.client_id, m.installation_date, m.assigned_collector_id, m.gps_location, COALESCE(m.source, "company") AS source, m.added_by_user_id, m.created_at, m.updated_at,
                           u.username AS client_username,
                           c.username AS collector_username
                    FROM meters m
                    LEFT JOIN users u ON m.client_id = u.id
                    LEFT JOIN users c ON m.assigned_collector_id = c.id
                    ORDER BY m.created_at DESC';
            
            $this->db->query($sql);
            $meters = $this->db->resultSet();
            $this->db->closeStmt();
            return $meters;
        } catch (Exception $e) {
            error_log("Meter Model Exception in getAllMetersWithDetails: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Flags a meter as problematic.
     */
    public function flagMeter(int $meterId): bool {
        try {
            $this->db->query('UPDATE meters SET status = "flagged", updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $this->db->bind([$meterId]);
            if ($this->db->execute()) {
                $this->db->closeStmt();
                return true;
            }
            $this->db->closeStmt();
            return false;
        } catch (Exception $e) {
            error_log("Meter Model Exception in flagMeter: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Unflags a meter back to available (only if not assigned to a client).
     */
    public function unflagMeter(int $meterId): bool {
        try {
            $this->db->query('UPDATE meters SET status = "available", updated_at = CURRENT_TIMESTAMP WHERE id = ? AND (client_id IS NULL OR client_id = 0)');
            $this->db->bind([$meterId]);
            if ($this->db->execute()) {
                $this->db->closeStmt();
                return true;
            }
            $this->db->closeStmt();
            return false;
        } catch (Exception $e) {
            error_log("Meter Model Exception in unflagMeter: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ensures new tracking columns exist: source (ENUM-like text) and added_by_user_id.
     * Safe to call repeatedly; alters table only if columns are missing.
     */
    private function ensureSourceColumns(): void {
        try {
            // Check for 'source' column
            $this->db->query('SHOW COLUMNS FROM meters LIKE "source"');
            $col = $this->db->single();
            $this->db->closeStmt();
            if (!$col) {
                $this->db->query('ALTER TABLE meters ADD COLUMN source VARCHAR(32) NULL DEFAULT NULL AFTER gps_location');
                $this->db->execute();
                $this->db->closeStmt();
            }

            // Check for 'added_by_user_id' column
            $this->db->query('SHOW COLUMNS FROM meters LIKE "added_by_user_id"');
            $col2 = $this->db->single();
            $this->db->closeStmt();
            if (!$col2) {
                $this->db->query('ALTER TABLE meters ADD COLUMN added_by_user_id INT NULL DEFAULT NULL AFTER source');
                $this->db->execute();
                $this->db->closeStmt();
            }
        } catch (Exception $e) {
            // Swallow errors silently; schema changes may require privileges
            error_log('ensureSourceColumns error: ' . $e->getMessage());
        }
    }
}
