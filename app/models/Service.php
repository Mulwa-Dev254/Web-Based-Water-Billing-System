<?php
// app/models/Service.php

// Ensure the Database wrapper is included as this model will use it
require_once '../app/core/Database.php';

class Service {
    private $db; // Database wrapper instance

    /**
     * Constructor for the Service model.
     * It requires an instance of the Database wrapper to perform database operations.
     */
    public function __construct($database_wrapper) {
        $this->db = $database_wrapper;
    }

    /**
     * Adds a new service to the 'services' table.
     *
     * @param string $service_name The name of the service (e.g., 'New Water Connection').
     * @param string $description A brief description of the service.
     * @param float $cost The typical cost associated with the service.
     * @param bool $is_active Whether the service is currently offered.
     * @return bool True on success, false on failure.
     */
    public function addService($service_name, $description, $cost, $is_active) {
        $this->db->query('INSERT INTO services (service_name, description, cost, is_active) VALUES (?, ?, ?, ?)');
        $this->db->bind([$service_name, $description, $cost, $is_active]);

        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            // In a real application, you would log the error here.
            // error_log("Failed to add service: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Retrieves all services from the 'services' table.
     *
     * @return array An array of associative arrays, each representing a service.
     */
    public function getAllServices() {
        $this->db->query('SELECT * FROM services ORDER BY service_name ASC');
        $services = $this->db->resultSet();
        $this->db->closeStmt();
        return $services;
    }

    /**
     * Retrieves all active services from the 'services' table.
     * This method filters services where 'is_active' is true (or 1).
     *
     * @return array An array of associative arrays, each representing an active service.
     */
    public function getAllActiveServices(): array {
        $this->db->query('SELECT * FROM services WHERE is_active = 1 ORDER BY service_name ASC');
        $services = $this->db->resultSet();
        $this->db->closeStmt();
        return $services;
    }


    /**
     * Retrieves a single service by its ID.
     *
     * @param int $id The ID of the service.
     * @return array|null An associative array representing the service, or null if not found.
     */
    public function getServiceById($id) {
        $this->db->query('SELECT * FROM services WHERE id = ?');
        $this->db->bind([$id]);
        $service = $this->db->single();
        $this->db->closeStmt();
        return $service;
    }

    public function getServiceByName(string $service_name) {
        $this->db->query('SELECT * FROM services WHERE service_name = ? LIMIT 1');
        $this->db->bind([$service_name]);
        $service = $this->db->single();
        $this->db->closeStmt();
        return $service ?: null;
    }

    /**
     * Updates an existing service.
     *
     * @param int $id The ID of the service to update.
     * @param string $service_name The name of the service.
     * @param string $description A brief description of the service.
     * @param float $cost The typical cost associated with the service.
     * @param bool $is_active Whether the service is currently offered.
     * @return bool True on success, false on failure.
     */
    public function updateService($id, $service_name, $description, $cost, $is_active) {
        $this->db->query('UPDATE services SET service_name = ?, description = ?, cost = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $this->db->bind([$service_name, $description, $cost, $is_active, $id]);

        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            // error_log("Failed to update service: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Deletes a service by its ID.
     *
     * @param int $id The ID of the service to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteService($id) {
        $this->db->query('DELETE FROM services WHERE id = ?');
        $this->db->bind([$id]);

        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            // error_log("Failed to delete service: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }
}
