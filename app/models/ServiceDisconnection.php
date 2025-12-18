<?php
// app/models/ServiceDisconnection.php

class ServiceDisconnection {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Create a new disconnection order
     */
    public function createDisconnectionOrder($data) {
        $query = "INSERT INTO service_disconnections 
                 (client_id, meter_number, outstanding_balance, reason, status, scheduled_date, notes, created_by, assigned_to) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "isdsssii", 
            $data['client_id'], 
            $data['meter_number'], 
            $data['outstanding_balance'], 
            $data['reason'], 
            $data['status'], 
            $data['scheduled_date'], 
            $data['notes'], 
            $data['created_by'], 
            $data['assigned_to']
        );
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        return false;
    }
    
    /**
     * Create a new reconnection order
     */
    public function createReconnectionOrder($data) {
        $query = "INSERT INTO service_reconnections 
                 (disconnection_id, client_id, meter_number, payment_id, status, scheduled_date, notes, created_by, assigned_to) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "iisisssii", 
            $data['disconnection_id'], 
            $data['client_id'], 
            $data['meter_number'], 
            $data['payment_id'], 
            $data['status'], 
            $data['scheduled_date'], 
            $data['notes'], 
            $data['created_by'], 
            $data['assigned_to']
        );
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        return false;
    }
    
    /**
     * Get all disconnection orders
     */
    public function getAllDisconnectionOrders() {
        $query = "SELECT d.*, c.name as client_name, c.phone as client_phone, 
                 u1.name as created_by_name, u2.name as assigned_to_name 
                 FROM service_disconnections d 
                 LEFT JOIN clients c ON d.client_id = c.id 
                 LEFT JOIN users u1 ON d.created_by = u1.id 
                 LEFT JOIN users u2 ON d.assigned_to = u2.id 
                 ORDER BY d.created_at DESC";
        
        $result = $this->db->query($query);
        $orders = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        
        return $orders;
    }
    
    /**
     * Get all reconnection orders
     */
    public function getAllReconnectionOrders() {
        $query = "SELECT r.*, c.name as client_name, c.phone as client_phone, 
                 u1.name as created_by_name, u2.name as assigned_to_name 
                 FROM service_reconnections r 
                 LEFT JOIN clients c ON r.client_id = c.id 
                 LEFT JOIN users u1 ON r.created_by = u1.id 
                 LEFT JOIN users u2 ON r.assigned_to = u2.id 
                 ORDER BY r.created_at DESC";
        
        $result = $this->db->query($query);
        $orders = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        
        return $orders;
    }
    
    /**
     * Get disconnection orders by assignee
     */
    public function getDisconnectionOrdersByAssignee($userId) {
        $query = "SELECT d.*, c.name as client_name, c.phone as client_phone, 
                 u1.name as created_by_name, u2.name as assigned_to_name 
                 FROM service_disconnections d 
                 LEFT JOIN clients c ON d.client_id = c.id 
                 LEFT JOIN users u1 ON d.created_by = u1.id 
                 LEFT JOIN users u2 ON d.assigned_to = u2.id 
                 WHERE d.assigned_to = ? 
                 ORDER BY d.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        
        return $orders;
    }
    
    /**
     * Get reconnection orders by assignee
     */
    public function getReconnectionOrdersByAssignee($userId) {
        $query = "SELECT r.*, c.name as client_name, c.phone as client_phone, 
                 u1.name as created_by_name, u2.name as assigned_to_name 
                 FROM service_reconnections r 
                 LEFT JOIN clients c ON r.client_id = c.id 
                 LEFT JOIN users u1 ON r.created_by = u1.id 
                 LEFT JOIN users u2 ON r.assigned_to = u2.id 
                 WHERE r.assigned_to = ? 
                 ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        
        return $orders;
    }
    
    /**
     * Get a disconnection order by ID
     */
    public function getDisconnectionById($id) {
        $query = "SELECT d.*, c.name as client_name, c.phone as client_phone, 
                 u1.name as created_by_name, u2.name as assigned_to_name 
                 FROM service_disconnections d 
                 LEFT JOIN clients c ON d.client_id = c.id 
                 LEFT JOIN users u1 ON d.created_by = u1.id 
                 LEFT JOIN users u2 ON d.assigned_to = u2.id 
                 WHERE d.id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Get a reconnection order by ID
     */
    public function getReconnectionById($id) {
        $query = "SELECT r.*, c.name as client_name, c.phone as client_phone, 
                 u1.name as created_by_name, u2.name as assigned_to_name 
                 FROM service_reconnections r 
                 LEFT JOIN clients c ON r.client_id = c.id 
                 LEFT JOIN users u1 ON r.created_by = u1.id 
                 LEFT JOIN users u2 ON r.assigned_to = u2.id 
                 WHERE r.id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Update a disconnection order
     */
    public function updateDisconnectionOrder($id, $data) {
        $query = "UPDATE service_disconnections SET 
                 status = ?, 
                 scheduled_date = ?, 
                 completed_date = ?, 
                 notes = ?, 
                 assigned_to = ? 
                 WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "ssssii", 
            $data['status'], 
            $data['scheduled_date'], 
            $data['completed_date'], 
            $data['notes'], 
            $data['assigned_to'], 
            $id
        );
        
        return $stmt->execute();
    }
    
    /**
     * Update a reconnection order
     */
    public function updateReconnectionOrder($id, $data) {
        $query = "UPDATE service_reconnections SET 
                 status = ?, 
                 scheduled_date = ?, 
                 completed_date = ?, 
                 notes = ?, 
                 assigned_to = ? 
                 WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "ssssii", 
            $data['status'], 
            $data['scheduled_date'], 
            $data['completed_date'], 
            $data['notes'], 
            $data['assigned_to'], 
            $id
        );
        
        return $stmt->execute();
    }
    
    /**
     * Get disconnection orders by client ID
     */
    public function getDisconnectionsByClientId($clientId) {
        $query = "SELECT * FROM service_disconnections WHERE client_id = ? ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        
        return $orders;
    }
    
    /**
     * Get reconnection orders by client ID
     */
    public function getReconnectionsByClientId($clientId) {
        $query = "SELECT * FROM service_reconnections WHERE client_id = ? ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        
        return $orders;
    }
    
    /**
     * Get pending disconnection orders count
     */
    public function getPendingDisconnectionsCount() {
        $query = "SELECT COUNT(*) as count FROM service_disconnections WHERE status IN ('pending', 'scheduled')";
        $result = $this->db->query($query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['count'];
        }
        
        return 0;
    }
    
    /**
     * Get pending reconnection orders count
     */
    public function getPendingReconnectionsCount() {
        $query = "SELECT COUNT(*) as count FROM service_reconnections WHERE status IN ('pending', 'scheduled')";
        $result = $this->db->query($query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['count'];
        }
        
        return 0;
    }
}