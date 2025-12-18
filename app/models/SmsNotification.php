<?php
// app/models/SmsNotification.php

class SmsNotification {
    private $db;
    
    public function __construct() {
        global $conn;
        $this->db = $conn;
        // Ensure the sms_notifications table exists to avoid runtime failures
        $this->createSmsNotificationsTable();
    }
    
    /**
     * Record a new SMS notification
     */
    public function recordNotification($phoneNumber, $message, $type, $status = 'pending', $referenceId = null) {
        $sql = "INSERT INTO sms_notifications (phone_number, message, type, status, reference_id) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssi", $phoneNumber, $message, $type, $status, $referenceId);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update SMS notification status
     */
    public function updateStatus($notificationId, $status, $responseData = null) {
        $sql = "UPDATE sms_notifications SET status = ?, response_data = ?, updated_at = NOW() WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssi", $status, $responseData, $notificationId);
        
        return $stmt->execute();
    }
    
    /**
     * Get notifications by reference ID
     */
    public function getNotificationsByReferenceId($referenceId, $type = null) {
        $sql = "SELECT * FROM sms_notifications WHERE reference_id = ?";
        $params = [$referenceId];
        $types = "i";
        
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
            $types .= "s";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            return $notifications;
        }
        
        return [];
    }
    
    /**
     * Get pending notifications
     */
    public function getPendingNotifications($limit = 10) {
        $sql = "SELECT * FROM sms_notifications WHERE status = 'pending' ORDER BY created_at ASC LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            return $notifications;
        }
        
        return [];
    }
    
    /**
     * Send payment confirmation SMS
     */
    public function sendPaymentConfirmation($phoneNumber, $amount, $receiptNumber, $accountNumber, $balance = null) {
        // Format the message
        $message = "Payment received: Ksh {$amount} for account {$accountNumber}. Receipt: {$receiptNumber}";
        
        if ($balance !== null) {
            $message .= ". Current balance: Ksh {$balance}";
        }
        
        $message .= ". Thank you for your payment.";
        
        // Record the notification
        $notificationId = $this->recordNotification($phoneNumber, $message, 'payment_confirmation');
        
        if ($notificationId) {
            // Here you would integrate with an actual SMS gateway
            // For now, we'll just simulate a successful send
            $this->updateStatus($notificationId, 'sent');
            return true;
        }
        
        return false;
    }
    
    /**
     * Send bill notification SMS
     */
    public function sendBillNotification($phoneNumber, $amount, $dueDate, $accountNumber) {
        // Format the message
        $message = "New bill generated: Ksh {$amount} for account {$accountNumber}. Due date: {$dueDate}. Please pay on time to avoid disconnection.";
        
        // Record the notification
        $notificationId = $this->recordNotification($phoneNumber, $message, 'bill_notification');
        
        if ($notificationId) {
            // Here you would integrate with an actual SMS gateway
            // For now, we'll just simulate a successful send
            $this->updateStatus($notificationId, 'sent');
            return true;
        }
        
        return false;
    }
    
    /**
     * Send payment reminder SMS
     */
    public function sendPaymentReminder($phoneNumber, $amount, $dueDate, $accountNumber, $daysOverdue) {
        // Format the message
        $message = "REMINDER: Your water bill of Ksh {$amount} for account {$accountNumber} was due on {$dueDate} ({$daysOverdue} days ago). Please pay to avoid disconnection.";
        
        // Record the notification
        $notificationId = $this->recordNotification($phoneNumber, $message, 'payment_reminder');
        
        if ($notificationId) {
            // Here you would integrate with an actual SMS gateway
            // For now, we'll just simulate a successful send
            $this->updateStatus($notificationId, 'sent');
            return true;
        }
        
        return false;
    }
    
    /**
     * Send disconnection notice SMS
     */
    public function sendDisconnectionNotice($phoneNumber, $amount, $accountNumber, $disconnectionDate) {
        // Format the message
        $message = "URGENT: Your water service for account {$accountNumber} will be disconnected on {$disconnectionDate} due to unpaid bill of Ksh {$amount}. Please pay immediately to avoid disconnection.";
        
        // Record the notification
        $notificationId = $this->recordNotification($phoneNumber, $message, 'disconnection_notice');
        
        if ($notificationId) {
            // Here you would integrate with an actual SMS gateway
            // For now, we'll just simulate a successful send
            $this->updateStatus($notificationId, 'sent');
            return true;
        }
        
        return false;
    }
    
    /**
     * Get recent notifications by recipient phone number
     */
    public function getNotificationsByPhoneNumber($phoneNumber, $limit = 10) {
        $sql = "SELECT * FROM sms_notifications WHERE phone_number = ? ORDER BY created_at DESC LIMIT ?";

        try {
            $stmt = $this->db->prepare($sql);
        } catch (\Throwable $e) {
            // Table may not exist yet; create and return empty set gracefully
            $this->createSmsNotificationsTable();
            return [];
        }
        $stmt->bind_param("si", $phoneNumber, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            return $notifications;
        }

        return [];
    }
    
    /**
     * Create SMS notifications table
     */
    public function createSmsNotificationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS sms_notifications (
            id INT(11) NOT NULL AUTO_INCREMENT,
            phone_number VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) NOT NULL,
            status ENUM('pending', 'sent', 'failed', 'delivered') NOT NULL DEFAULT 'pending',
            reference_id INT(11) DEFAULT NULL,
            response_data TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY phone_number (phone_number),
            KEY status (status),
            KEY reference_id (reference_id),
            KEY type (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        return $this->db->query($sql);
    }
}
