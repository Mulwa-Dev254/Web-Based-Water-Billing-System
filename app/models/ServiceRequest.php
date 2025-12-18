<?php
// app/models/ServiceRequest.php

require_once __DIR__ . '/../core/Database.php';

class ServiceRequest {
    private Database $db;

    public function __construct(Database $database_instance) {
        $this->db = $database_instance;
    }

    /**
     * Records a new service request from a client.
     *
     * @param int $clientId The ID of the client making the request.
     * @param int $serviceId The ID of the service being requested.
     * @param string $description A detailed description of the request.
     * @param int|null $meterId Optional: The ID of the meter if the request is meter-specific.
     * @return bool True on success, false on failure.
     */
    public function createServiceRequest(int $clientId, int $serviceId, string $description, ?int $meterId = null): bool {
        error_log("ServiceRequest Debug: Attempting to create service request with: ClientID=" . $clientId . ", ServiceID=" . $serviceId . ", Description='" . $description . "', MeterID=" . ($meterId ?? 'NULL'));

        try {
            // Initial status will be 'pending' and assigned_to_collector_id will be NULL
            $this->db->query('INSERT INTO service_requests (client_id, service_id, meter_id, description, request_date, status) VALUES (?, ?, ?, ?, NOW(), "pending")');
            $this->db->bind([$clientId, $serviceId, $meterId, $description]);
            
            if ($this->db->execute()) {
                $this->db->closeStmt();
                error_log("ServiceRequest Debug: Service request created successfully for ClientID: " . $clientId);
                return true;
            } else {
                error_log("Failed to create service request: " . $this->db->getError());
                $this->db->closeStmt();
                return false;
            }
        } catch (Exception $e) {
            error_log("ServiceRequest Model Exception in createServiceRequest: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches all service requests, optionally filtered by collector ID and status.
     *
     * @param int|null $collectorId Optional: Filter by assigned collector ID.
     * @param string|null $status Optional: Filter by status (e.g., 'pending', 'assigned', 'serviced', 'confirmed', 'rejected', 'cancelled').
     * @return array An array of associative arrays, each representing a service request.
     */
    public function getServiceRequests(?int $collectorId = null, ?string $status = null): array {
        try {
            $sql = '
                SELECT
                    sr.id,
                    sr.description,
                    sr.request_date,
                    sr.status,
                    sr.client_id,
                    sr.service_id,
                    sr.meter_id,
                    sr.assigned_to_collector_id,
                    u.username AS client_username,
                    s.service_name,
                    m.serial_number AS meter_serial_number,
                    c.username AS assigned_collector_username
                FROM
                    service_requests sr
                JOIN
                    users u ON sr.client_id = u.id
                LEFT JOIN
                    services s ON sr.service_id = s.id
                LEFT JOIN
                    meters m ON sr.meter_id = m.id
                LEFT JOIN
                    users c ON sr.assigned_to_collector_id = c.id
            ';
            $conditions = [];
            $params = [];

            if ($collectorId !== null) {
                $conditions[] = 'sr.assigned_to_collector_id = ?';
                $params[] = $collectorId;
            }
            if ($status !== null) {
                $conditions[] = 'sr.status = ?';
                $params[] = $status;
            }

            if (!empty($conditions)) {
                $sql .= ' WHERE ' . implode(' AND ', $conditions);
            }

            $sql .= ' ORDER BY sr.request_date DESC';

            $this->db->query($sql);
            if (!empty($params)) {
                $this->db->bind($params);
            }
            $requests = $this->db->resultSet();
            $this->db->closeStmt();
            return $requests;
        } catch (Exception $e) {
            error_log("ServiceRequest Model Exception in getServiceRequests: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieves a single service request by its ID.
     *
     * @param int $requestId The ID of the service request.
     * @return array|null An associative array of service request data, or null if not found.
     */
    public function getServiceRequestById(int $requestId): ?array {
        try {
            $this->db->query('
                SELECT
                    sr.id,
                    sr.description,
                    sr.request_date,
                    sr.status,
                    sr.client_id,
                    sr.service_id,
                    sr.meter_id,
                    sr.assigned_to_collector_id,
                    u.username AS client_username,
                    s.service_name,
                    m.serial_number AS meter_serial_number
                FROM
                    service_requests sr
                JOIN
                    users u ON sr.client_id = u.id
                LEFT JOIN
                    services s ON sr.service_id = s.id
                LEFT JOIN
                    meters m ON sr.meter_id = m.id
                WHERE
                    sr.id = ?
            ');
            $this->db->bind([$requestId]);
            $request = $this->db->single();
            $this->db->closeStmt();
            return $request;
        } catch (Exception $e) {
            error_log("ServiceRequest Model Exception in getServiceRequestById: " . $e->getMessage());
            $this->db->closeStmt();
            return null;
        }
    }

    /**
     * Get service requests by client ID.
     * @param int $clientId
     * @return array
     */
    public function getServiceRequestsByClientId(int $clientId): array {
        try {
            $this->db->query('
                SELECT
                    sr.id,
                    sr.description,
                    sr.request_date,
                    sr.status,
                    s.service_name,
                    m.serial_number AS meter_serial_number,
                    c.username AS assigned_collector_username
                FROM
                    service_requests sr
                JOIN
                    services s ON sr.service_id = s.id
                LEFT JOIN
                    meters m ON sr.meter_id = m.id
                LEFT JOIN
                    users c ON sr.assigned_to_collector_id = c.id
                WHERE
                    sr.client_id = ?
                ORDER BY
                    sr.request_date DESC
            ');
            $this->db->bind([$clientId]);
            $requests = $this->db->resultSet();
            $this->db->closeStmt();
            return $requests;
        } catch (Exception $e) {
            error_log("ServiceRequest Model Exception in getServiceRequestsByClientId: " . $e->getMessage());
            $this->db->closeStmt();
            return [];
        }
    }

    /**
     * Updates the status of a service request.
     *
     * @param int $requestId The ID of the service request.
     * @param string $newStatus The new status (e.g., 'pending', 'assigned', 'serviced', 'confirmed', 'rejected', 'cancelled').
     * @return bool True on success, false on failure.
     */
    public function updateServiceRequestStatus(int $requestId, string $newStatus): bool {
        try {
            $this->db->query('UPDATE service_requests SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $this->db->bind([$newStatus, $requestId]);
            if ($this->db->execute()) {
                $this->db->closeStmt();
                return true;
            } else {
                error_log("ServiceRequest Model Error: Failed to update status for request ID {$requestId}. DB Error: " . $this->db->getError());
                $this->db->closeStmt();
                return false;
            }
        } catch (Exception $e) {
            error_log("ServiceRequest Model Exception in updateServiceRequestStatus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates the status of a service request and assigns it to a collector.
     *
     * @param int $requestId The ID of the service request.
     * @param string $newStatus The new status (e.g., 'assigned').
     * @param int $collectorId The ID of the collector to assign.
     * @return bool True on success, false on failure.
     */
    public function updateServiceRequestStatusAndAssignCollector(int $requestId, string $newStatus, int $collectorId): bool {
        try {
            $this->db->query('UPDATE service_requests SET status = ?, assigned_to_collector_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $this->db->bind([$newStatus, $collectorId, $requestId]);
            if ($this->db->execute()) {
                $this->db->closeStmt();
                return true;
            } else {
                error_log("ServiceRequest Model Error: Failed to update status and assign collector for request ID {$requestId}. DB Error: " . $this->db->getError());
                $this->db->closeStmt();
                return false;
            }
        } catch (Exception $e) {
            error_log("ServiceRequest Model Exception in updateServiceRequestStatusAndAssignCollector: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Assigns a service request to a collector.
     *
     * @param int $requestId The ID of the service request.
     * @param int $collectorId The ID of the collector to assign.
     * @return bool True on success, false on failure.
     */
    public function assignServiceRequest(int $requestId, int $collectorId): bool {
        return $this->updateServiceRequestStatusAndAssignCollector($requestId, 'assigned', $collectorId);
    }
}