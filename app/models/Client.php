<?php
// app/models/Client.php

require_once '../app/core/Database.php';

class Client {
    private $db;

    public function __construct($database_wrapper) {
        $this->db = $database_wrapper;
    }

    /**
     * Registers a new client and links them to a user account.
     * This method is typically called after a user registers with the 'client' role.
     *
     * @param int $user_id The ID of the user account.
     * @param string $full_name The full name of the client.
     * @param string $address The physical address of the client.
     * @param string $contact_phone The contact phone number of the client.
     * @return bool True on successful client creation, false otherwise.
     */
    public function registerClient($user_id, $full_name, $address, $contact_phone) {
        $this->db->query('INSERT INTO clients (user_id, full_name, address, contact_phone) VALUES (?, ?, ?, ?)');
        $this->db->bind([$user_id, $full_name, $address, $contact_phone]);

        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            // Log the error for debugging purposes
            // error_log("Error registering client: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Retrieves client details by user ID.
     *
     * @param int $user_id The ID of the user associated with the client.
     * @return array|false An associative array of client data if found, false otherwise.
     */
    public function getClientByUserId($user_id) {
        $this->db->query('
            SELECT c.*, u.email, u.username, u.full_name
            FROM clients c
            JOIN users u ON c.user_id = u.id
            WHERE u.id = ?
            LIMIT 1
        ');
        $this->db->bind([$user_id]);
        $client = $this->db->single();
        $this->db->closeStmt();
        return $client ?: false;
    }

    /**
     * Retrieves all clients from the database.
     *
     * @return array An array of all clients with their user information.
     */
    public function getAllClients() {
        $this->db->query('
            SELECT c.*, u.email, u.username, u.full_name
            FROM clients c
            JOIN users u ON c.user_id = u.id
            ORDER BY c.id DESC
        ');
        $clients = $this->db->resultSet();
        $this->db->closeStmt();
        return $clients;
    }
    


    // You can add more methods here for updating client details, etc.
    public function getClientById(int $clientId): array|false {
        $this->db->query('
            SELECT c.*, u.email, u.username, u.full_name
            FROM clients c
            JOIN users u ON c.user_id = u.id
            WHERE c.id = ?
            LIMIT 1
        ');
        $this->db->bind([$clientId]);
        $row = $this->db->single();
        $this->db->closeStmt();
        return $row ?: false;
    }

    public function updateClientByUserId(int $userId, string $full_name, string $address, string $contact_phone, ?string $contact_email = null): bool {
        if ($contact_email !== null) {
            $this->db->query('UPDATE clients SET full_name = ?, address = ?, contact_phone = ?, contact_email = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?');
            $this->db->bind([$full_name, $address, $contact_phone, $contact_email, $userId]);
        } else {
            $this->db->query('UPDATE clients SET full_name = ?, address = ?, contact_phone = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?');
            $this->db->bind([$full_name, $address, $contact_phone, $userId]);
        }
        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            $this->db->closeStmt();
            return false;
        }
    }
}
