<?php
// app/models/ServiceOrder.php

class ServiceOrder {
    private $db;
    
    public function __construct() {
        global $conn;
        $this->db = $conn;
    }
    
    /**
     * Get all service orders
     */
    public function getAllServiceOrders() {
        $sql = "SELECT so.*, st.name as service_type, c.name as client_name, 
                u.name as assignee_name 
                FROM service_orders so 
                LEFT JOIN service_types st ON so.service_type_id = st.id 
                LEFT JOIN clients c ON so.client_id = c.id 
                LEFT JOIN users u ON so.assigned_to = u.id 
                ORDER BY so.created_at DESC";
        
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $orders = [];
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
            return $orders;
        }
        
        return [];
    }
    
    /**
     * Get service order by ID
     */
    public function getServiceOrderById($orderId) {
        $sql = "SELECT so.*, st.name as service_type, c.name as client_name, 
                c.phone as client_phone, c.email as client_email, 
                u.name as assignee_name 
                FROM service_orders so 
                LEFT JOIN service_types st ON so.service_type_id = st.id 
                LEFT JOIN clients c ON so.client_id = c.id 
                LEFT JOIN users u ON so.assigned_to = u.id 
                WHERE so.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Create a new service order
     */
    public function createServiceOrder($clientId, $serviceTypeId, $description, $scheduledDate, $priority, $createdBy) {
        $sql = "INSERT INTO service_orders (client_id, service_type_id, description, scheduled_date, priority, created_by) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iisssi", $clientId, $serviceTypeId, $description, $scheduledDate, $priority, $createdBy);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        return false;
    }
    
    /**
     * Assign a service order to a staff member
     */
    public function assignServiceOrder($orderId, $assigneeId) {
        $sql = "UPDATE service_orders SET assigned_to = ?, status = 'assigned', updated_by = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $updatedBy = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $stmt->bind_param("iii", $assigneeId, $updatedBy, $orderId);
        
        return $stmt->execute();
    }
    
    /**
     * Update service order status
     */
    public function updateServiceOrderStatus($orderId, $status, $updatedBy) {
        $sql = "UPDATE service_orders SET status = ?, updated_by = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sii", $status, $updatedBy, $orderId);
        
        return $stmt->execute();
    }
    
    /**
     * Get service types
     */
    public function getServiceTypes() {
        $sql = "SELECT * FROM service_types ORDER BY name";
        
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $types = [];
            while ($row = $result->fetch_assoc()) {
                $types[] = $row;
            }
            return $types;
        }
        
        return [];
    }
    
    /**
     * Create service order tables
     */
    public function createServiceOrderTables() {
        // Create service orders table
        $sql = "CREATE TABLE IF NOT EXISTS service_orders (
            id INT(11) NOT NULL AUTO_INCREMENT,
            client_id INT(11) NOT NULL,
            service_type_id INT(11) NOT NULL,
            description TEXT NOT NULL,
            status ENUM('pending', 'assigned', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
            priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
            scheduled_date DATE NOT NULL,
            assigned_to INT(11) DEFAULT NULL,
            created_by INT(11) NOT NULL,
            updated_by INT(11) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $this->db->query($sql);
        
        // Create service types table
        $typesSql = "CREATE TABLE IF NOT EXISTS service_types (
            id INT(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $this->db->query($typesSql);
        
        // Insert default service types
        $insertTypesSql = "INSERT IGNORE INTO service_types (name, description) VALUES 
            ('Meter Installation', 'Installation of new water meters'),
            ('Meter Replacement', 'Replacement of faulty or old water meters'),
            ('Meter Reading', 'Regular reading of water meters'),
            ('Pipe Repair', 'Repair of damaged water pipes'),
            ('Connection Repair', 'Repair of water connections'),
            ('Water Quality Check', 'Testing water quality at customer premises'),
            ('Leak Detection', 'Detecting and fixing water leaks'),
            ('Billing Issue', 'Resolving billing related issues'),
            ('General Maintenance', 'General maintenance of water infrastructure')";
        
        $this->db->query($insertTypesSql);
        
        return true;
    }
}