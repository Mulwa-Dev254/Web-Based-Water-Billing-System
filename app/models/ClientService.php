<?php
// app/models/ClientService.php

require_once '../app/core/Database.php';

class ClientService {
    private $db;

    public function __construct($database_wrapper) {
        $this->db = $database_wrapper;
    }

    /**
     * Records a client's application for a service.
     *
     * @param int $user_id The ID of the user (client).
     * @param int $service_id The ID of the service being applied for.
     * @param string $status Initial status (e.g., 'pending', 'approved', 'rejected').
     * @param string|null $application_date Optional: date of application.
     * @return bool True on success, false on failure.
     */
    public function applyForService($user_id, $service_id, $status = 'pending', $application_date = null) {
        $this->db->query('INSERT INTO client_services (user_id, service_id, status, application_date) VALUES (?, ?, ?, ?)');
        $this->db->bind([$user_id, $service_id, $status, $application_date ?? date('Y-m-d')]);

        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            error_log("Failed to apply for service: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Creates a billable client service entry from a confirmed service request.
     * This is called when a service request status becomes 'confirmed' by the client.
     *
     * @param int $userId The ID of the user (client).
     * @param int $serviceId The ID of the service.
     * @param int $serviceRequestId The ID of the original service request.
     * @param float $cost The cost of the service.
     * @return bool True on success, false on failure.
     */
    public function createBillableService(int $userId, int $serviceId, int $serviceRequestId, float $cost): bool {
        error_log("ClientService Debug: createBillableService called with UserID=$userId, ServiceID=$serviceId, ServiceRequestID=$serviceRequestId, Cost=$cost");

        // Check if this service request already has a corresponding billable entry
        $this->db->query('SELECT id FROM client_services WHERE service_request_id = ?');
        $this->db->bind([$serviceRequestId]);
        $existingEntry = $this->db->single();
        $this->db->closeStmt();

        if ($existingEntry) {
            error_log("ClientService Debug: Billable service already exists for service request ID: " . $serviceRequestId . ". ID: " . $existingEntry['id']);
            return true; // Already exists, consider it successful
        }

        // Set the status explicitly to 'pending_payment'
        $status_to_set = "pending_payment";
        $sql = 'INSERT INTO client_services (user_id, service_id, service_request_id, status, application_date, payment_date) VALUES (?, ?, ?, ?, NOW(), NULL)';
        $params = [$userId, $serviceId, $serviceRequestId, $status_to_set];

        error_log("ClientService Debug: INSERT SQL: " . $sql);
        error_log("ClientService Debug: INSERT Params: " . json_encode($params));

        $this->db->query($sql);
        $this->db->bind($params);

        if ($this->db->execute()) {
            $lastId = $this->db->lastInsertId();
            $this->db->closeStmt();
            error_log("ClientService Debug: Billable service created successfully with ID: " . $lastId . " for ServiceRequestID: " . $serviceRequestId . " with status: " . $status_to_set);
            return true;
        } else {
            error_log("ClientService Error: Failed to create billable service from request: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    public function getByServiceRequestId(int $serviceRequestId): ?array {
        $this->db->query('SELECT * FROM client_services WHERE service_request_id = ? LIMIT 1');
        $this->db->bind([$serviceRequestId]);
        $row = $this->db->single();
        $this->db->closeStmt();
        return $row ?: null;
    }

    /**
     * Retrieves all services applied by a specific user.
     * Joins with `services` table to get service details.
     *
     * @param int $user_id The ID of the user.
     * @return array An array of associative arrays, each representing an applied service.
     */
    public function getClientServicesByUserId($user_id) {
        error_log("ClientService Debug: getClientServicesByUserId called for UserID: $user_id");

        // --- Debugging: Get table schema to check column types ---
        try {
            $this->db->query("DESCRIBE client_services");
            $schema = $this->db->resultSet();
            $this->db->closeStmt();
            error_log("ClientService Debug: client_services table schema: " . json_encode($schema));
        } catch (Exception $e) {
            error_log("ClientService Error: Failed to get client_services schema: " . $e->getMessage());
        }
        // --- End Debugging Schema ---

        $this->db->query('
            SELECT cs.*, s.service_name, s.description, s.cost
            FROM client_services cs
            JOIN services s ON cs.service_id = s.id
            WHERE cs.user_id = ?
            ORDER BY cs.application_date DESC
        ');
        $this->db->bind([$user_id]);
        $services = $this->db->resultSet();
        $this->db->closeStmt();
        error_log("ClientService Debug: Fetched " . count($services) . " client services for UserID: $user_id");
        // Log details of each fetched service for debugging
        foreach ($services as $service) {
            error_log("ClientService Debug: Service ID: " . $service['id'] . ", Name: " . $service['service_name'] . ", Status: '" . $service['status'] . "', Payment Date: '" . ($service['payment_date'] ?? 'NULL') . "'");
        }
        return $services;
    }

    /**
     * Retrieves a single client service application record by its primary ID.
     * This method is used to fetch a record by its unique ID, regardless of user,
     * often for internal checks where user ownership is verified separately.
     *
     * @param int $id The ID of the client_services record.
     * @return array|false An associative array of client service data if found, false otherwise.
     */
    public function getClientServiceById($id) {
        $this->db->query('SELECT * FROM client_services WHERE id = ?');
        $this->db->bind([$id]);
        $service = $this->db->single();
        $this->db->closeStmt();
        return $service;
    }

    /**
     * Retrieves a single client service application record by its ID and user ID.
     * This method is used for secure retrieval where the client_service_id must belong to the given user.
     *
     * @param int $id The ID of the client_services record.
     * @param int $user_id The ID of the user.
     * @return array|false An associative array of client service data if found, false otherwise.
     */
    public function getUsersClientServiceById($id, $user_id) {
        $this->db->query('
            SELECT cs.*, s.service_name, s.cost
            FROM client_services cs
            JOIN services s ON cs.service_id = s.id
            WHERE cs.id = ? AND cs.user_id = ?
        ');
        $this->db->bind([$id, $user_id]);
        $service = $this->db->single();
        $this->db->closeStmt();
        return $service;
    }

    /**
     * Retrieves a client service application by a specific service_id and user_id.
     * Useful for checking if a client has already applied for a particular service.
     *
     * @param int $service_id The ID of the service.
     * @param int $user_id The ID of the user.
     * @return array|null An associative array of client service data if found, null otherwise.
     */
    public function getClientServiceByIdAndUserId($service_id, $user_id) {
        $this->db->query('
            SELECT cs.*
            FROM client_services cs
            WHERE cs.service_id = ? AND cs.user_id = ? AND (cs.status = "pending" OR cs.status = "approved")
        ');
        $this->db->bind([$service_id, $user_id]);
        $service = $this->db->single();
        $this->db->closeStmt();
        return $service;
    }

    /**
     * Updates the status of a client's service application.
     *
     * @param int $client_service_id The ID of the client's service application record.
     * @param string $new_status The new status (e.g., 'pending', 'approved', 'rejected', 'completed', 'cancelled').
     * @return bool True on success, false on failure.
     */
    public function updateClientServiceStatus($client_service_id, $new_status) {
        $this->db->query('UPDATE client_services SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $this->db->bind([$new_status, $client_service_id]);
        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            error_log("Failed to update service status: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Marks a client service as paid
     * @param int $clientServiceId The ID of the client service
     * @return bool True if successful, false otherwise
     */
    public function markServiceAsPaid(int $clientServiceId): bool {
        try {
            // First verify the service exists and is approved or pending_payment
            $service = $this->getClientServiceById($clientServiceId);
            if (!$service || ($service['status'] !== 'approved' && $service['status'] !== 'pending_payment')) {
                error_log("Service not found or not approved/pending_payment for payment. ID: $clientServiceId. Current status: " . ($service['status'] ?? 'N/A'));
                return false;
            }

            $this->db->query('UPDATE client_services SET status = "paid", payment_date = NOW() WHERE id = ? AND (status = "approved" OR status = "pending_payment")');
            $this->db->bind([$clientServiceId]);
            
            if ($this->db->execute()) {
                $rowsAffected = $this->db->rowCount();
                $this->db->closeStmt();
                
                if ($rowsAffected === 0) {
                    error_log("No rows affected when marking service as paid. ID: " . $clientServiceId . ". Status might not have been 'approved' or 'pending_payment'.");
                    return false;
                }
                error_log("ClientService Debug: Service ID $clientServiceId marked as paid successfully.");
                return true;
            } else {
                error_log("ClientService Error: Failed to mark service as paid. Error: " . $this->db->getError());
                $this->db->closeStmt();
                return false;
            }
        } catch (Exception $e) {
            error_log("ClientService Exception in markServiceAsPaid: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancels a client's service application by updating its status to 'cancelled'.
     *
     * @param int $client_service_id The ID of the client's service application record.
     * @return bool True on success, false on failure.
     */
    public function cancelClientService($client_service_id) {
        return $this->updateClientServiceStatus($client_service_id, 'cancelled');
    }
}
