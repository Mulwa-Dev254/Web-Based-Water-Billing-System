<?php
namespace app\models;

class SMS {
    private $db;
    private $apiKey;
    private $senderId;
    private $apiUrl;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = new \app\core\Database();
        
        // Initialize SMS API credentials
        // In a real implementation, these would be loaded from a configuration file or environment variables
        $this->apiKey = getenv('SMS_API_KEY') ?: 'YOUR_SMS_API_KEY';
        $this->senderId = getenv('SMS_SENDER_ID') ?: 'WATERBILL';
        $this->apiUrl = 'https://api.africastalking.com/version1/messaging'; // Example API endpoint
    }
    
    /**
     * Send an SMS message
     * 
     * @param string $recipient Phone number of recipient
     * @param string $message Message content
     * @return bool True if sent successfully, false otherwise
     */
    public function sendSMS($recipient, $message) {
        // Log the SMS in the database first
        $this->db->query('INSERT INTO sms_notifications (recipient, message, status, created_at) VALUES (?, ?, ?, NOW())');
        $this->db->bind([$recipient, $message, 'pending']);
        $success = $this->db->execute();
        $smsId = $this->db->lastInsertId();
        $this->db->closeStmt();
        
        if (!$success) {
            error_log("Failed to log SMS in database");
            return false;
        }
        
        // For now, we'll simulate sending the SMS
        // In a real implementation, this would call an SMS gateway API
        $sent = $this->simulateSendSMS($recipient, $message);
        
        // Update the SMS status in the database
        $this->db->query('UPDATE sms_notifications SET status = ?, sent_at = NOW() WHERE id = ?');
        $this->db->bind([$sent ? 'sent' : 'failed', $smsId]);
        $this->db->execute();
        $this->db->closeStmt();
        
        return $sent;
    }
    
    /**
     * Simulate sending an SMS
     * This method would be replaced with actual API calls in production
     * 
     * @param string $recipient Phone number of recipient
     * @param string $message Message content
     * @return bool Always returns true for simulation
     */
    private function simulateSendSMS($recipient, $message) {
        // Log the simulated SMS for debugging
        error_log("SIMULATED SMS to {$recipient}: {$message}");
        return true;
    }
    
    /**
     * Send bill notification SMS
     * 
     * @param int $billId Bill ID
     * @return bool True if sent successfully, false otherwise
     */
    public function sendBillNotification($billId) {
        // Get bill details
        $this->db->query('SELECT b.*, c.name, c.phone 
                          FROM bills b 
                          JOIN clients c ON b.client_id = c.id 
                          WHERE b.id = ? LIMIT 1');
        $this->db->bind([$billId]);
        $bill = $this->db->single();
        $this->db->closeStmt();
        
        if (!$bill || empty($bill['phone'])) {
            error_log("Cannot send bill notification: Bill not found or client has no phone number");
            return false;
        }
        
        // Format the message
        $dueDate = date('d/m/Y', strtotime($bill['due_date']));
        $message = "Dear {$bill['name']}, your water bill for meter {$bill['meter_number']} is KES {$bill['amount_due']}. ";
        $message .= "Due date: {$dueDate}. Please pay to avoid disconnection.";
        
        // Send the SMS
        return $this->sendSMS($bill['phone'], $message);
    }
    
    /**
     * Send payment reminder SMS
     * 
     * @param int $billId Bill ID
     * @return bool True if sent successfully, false otherwise
     */
    public function sendPaymentReminder($billId) {
        // Get bill details
        $this->db->query('SELECT b.*, c.name, c.phone 
                          FROM bills b 
                          JOIN clients c ON b.client_id = c.id 
                          WHERE b.id = ? AND b.status != "paid" LIMIT 1');
        $this->db->bind([$billId]);
        $bill = $this->db->single();
        $this->db->closeStmt();
        
        if (!$bill || empty($bill['phone'])) {
            error_log("Cannot send payment reminder: Bill not found, already paid, or client has no phone number");
            return false;
        }
        
        // Calculate days overdue
        $dueDate = strtotime($bill['due_date']);
        $today = strtotime(date('Y-m-d'));
        $daysOverdue = floor(($today - $dueDate) / (60 * 60 * 24));
        
        // Format the message
        $balance = $bill['amount_due'] - $bill['amount_paid'];
        $message = "REMINDER: Dear {$bill['name']}, your water bill of KES {$balance} for meter {$bill['meter_number']} ";
        
        if ($daysOverdue > 0) {
            $message .= "is overdue by {$daysOverdue} days. ";
        } else {
            $message .= "is due soon. ";
        }
        
        $message .= "Please pay to avoid disconnection.";
        
        // Send the SMS
        return $this->sendSMS($bill['phone'], $message);
    }
    
    /**
     * Send disconnection notice SMS
     * 
     * @param int $clientId Client ID
     * @param string $meterNumber Meter number
     * @param float $balance Outstanding balance
     * @return bool True if sent successfully, false otherwise
     */
    public function sendDisconnectionNotice($clientId, $meterNumber, $balance) {
        // Get client details
        $this->db->query('SELECT name, phone FROM clients WHERE id = ? LIMIT 1');
        $this->db->bind([$clientId]);
        $client = $this->db->single();
        $this->db->closeStmt();
        
        if (!$client || empty($client['phone'])) {
            error_log("Cannot send disconnection notice: Client not found or has no phone number");
            return false;
        }
        
        // Format the message
        $message = "DISCONNECTION NOTICE: Dear {$client['name']}, your water service for meter {$meterNumber} ";
        $message .= "will be disconnected within 24 hours due to non-payment of KES {$balance}. ";
        $message .= "Please make immediate payment to avoid disconnection.";
        
        // Send the SMS
        return $this->sendSMS($client['phone'], $message);
    }
    
    /**
     * Send reconnection confirmation SMS
     * 
     * @param int $clientId Client ID
     * @param string $meterNumber Meter number
     * @return bool True if sent successfully, false otherwise
     */
    public function sendReconnectionConfirmation($clientId, $meterNumber) {
        // Get client details
        $this->db->query('SELECT name, phone FROM clients WHERE id = ? LIMIT 1');
        $this->db->bind([$clientId]);
        $client = $this->db->single();
        $this->db->closeStmt();
        
        if (!$client || empty($client['phone'])) {
            error_log("Cannot send reconnection confirmation: Client not found or has no phone number");
            return false;
        }
        
        // Format the message
        $message = "Dear {$client['name']}, your water service for meter {$meterNumber} has been reconnected. ";
        $message .= "Thank you for your payment.";
        
        // Send the SMS
        return $this->sendSMS($client['phone'], $message);
    }
    
    /**
     * Get all SMS notifications with pagination
     * 
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array Array of SMS notifications
     */
    public function getAllNotifications($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $this->db->query('SELECT * FROM sms_notifications ORDER BY created_at DESC LIMIT ?, ?');
        $this->db->bind([$offset, $limit]);
        $notifications = $this->db->resultSet();
        $this->db->closeStmt();
        
        // Get total count for pagination
        $this->db->query('SELECT COUNT(*) as total FROM sms_notifications');
        $count = $this->db->single();
        $this->db->closeStmt();
        
        return [
            'notifications' => $notifications,
            'total' => $count['total'],
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($count['total'] / $limit)
        ];
    }
}