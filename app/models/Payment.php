<?php
// app/models/Payment.php

require_once '../app/core/Database.php';

class Payment {
    private $db;

    public function __construct($database_wrapper = null) {
        if ($database_wrapper) {
            $this->db = $database_wrapper;
        } else {
            $this->db = new Database();
        }
    }
    
    public function getTotalPaymentsAmount() {
        $this->ensureVerifiedPaymentsTable();
        $this->db->query('SELECT SUM(amount_paid) as total FROM verified_payments');
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    public function recordPayment($user_id, $type, $reference_id, $amount, $payment_method, $transaction_id = null, $status = 'pending') {
        $this->db->query('INSERT INTO payments (user_id, type, reference_id, amount, payment_method, transaction_id, status, payment_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
        $this->db->bind([$user_id, $type, $reference_id, $amount, $payment_method, $transaction_id, $status]);
        return $this->db->execute();
    }

    public function getPaymentHistoryByUserId($user_id) {
        $this->db->query('
            SELECT 
                p.id,
                p.user_id,
                COALESCE(NULLIF(p.type,\'\'),
                    CASE WHEN EXISTS (SELECT 1 FROM bills b WHERE b.id = p.reference_id) THEN "bill_payment"
                         ELSE "service_payment"
                    END
                ) AS type,
                p.reference_id,
                p.amount,
                p.payment_method,
                p.transaction_id,
                p.payment_date,
                p.status,
                p.created_at,
                p.updated_at
            FROM payments p 
            WHERE p.user_id = ? 
            ORDER BY p.payment_date DESC
        ');
        $this->db->bind([$user_id]);
        return $this->db->resultSet();
    }

    public function updatePaymentStatus($payment_id, $new_status, $transaction_id = null) {
        $sql = 'UPDATE payments SET status = ?, updated_at = CURRENT_TIMESTAMP';
        $params = [$new_status];

        if ($transaction_id !== null) {
            $sql .= ', transaction_id = ?';
            $params[] = $transaction_id;
        }
        $sql .= ' WHERE id = ?';
        $params[] = $payment_id;

        $this->db->query($sql);
        $this->db->bind($params);
        return $this->db->execute();
    }

    public function updatePaymentInitiation($payment_id, $checkout_id, $method_label = 'Mpesa STK-Push') {
        $this->db->query('UPDATE payments SET payment_method = ?, transaction_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $this->db->bind([$method_label, $checkout_id, $payment_id]);
        return $this->db->execute();
    }

    public function getPaymentById(int $paymentId) {
        $this->db->query('
            SELECT 
                p.id AS payment_id,
                p.*, 
                u.id AS client_id,
                u.username,
                u.full_name AS client_name,
                u.email AS client_email,
                b.id AS bill_id,
                COALESCE(NULLIF(p.type,\'\'), CASE WHEN b.id IS NOT NULL THEN "bill_payment" ELSE "service_payment" END) AS payment_type,
                p.transaction_id AS reference_number
            FROM payments p
            INNER JOIN users u ON p.user_id = u.id
            LEFT JOIN bills b ON b.id = p.reference_id
            WHERE p.id = ?
            LIMIT 1
        ');
        $this->db->bind([$paymentId]);
        $row = $this->db->single();
        $this->db->closeStmt();
        return $row;
    }

    public function flagPayment(int $paymentId, string $reason = ''): bool {
        $this->db->query('UPDATE payments SET status = "flagged", notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $this->db->bind([$reason, $paymentId]);
        return $this->db->execute();
    }

    public function unflagPayment(int $paymentId): bool {
        $this->db->query('UPDATE payments SET status = "pending", updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $this->db->bind([$paymentId]);
        return $this->db->execute();
    }

    public function addNote(int $paymentId, string $notes): bool {
        $this->db->query('UPDATE payments SET notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $this->db->bind([$notes, $paymentId]);
        return $this->db->execute();
    }

    public function verifyPayment(int $paymentId, string $verificationStatus, string $notes = '') {
        $this->ensureFlexibleStatusColumns();
        $payment = $this->getPaymentById($paymentId);
        if (!$payment) { return false; }
        $newStatus = ($verificationStatus === 'approved') ? 'confirmed_and_verified' : (($verificationStatus === 'rejected') ? 'rejected' : 'pending');
        $ok = $this->updatePaymentStatus($paymentId, $newStatus);
        if (!$ok) { return false; }
        if (($payment['type'] ?? '') === 'bill_payment') {
            $billModel = new Bill($this->db);
            $bill = $billModel->getBillById((int)($payment['reference_id'] ?? 0));
            if ($bill) {
                if ($newStatus === 'confirmed_and_verified' || $newStatus === 'completed') {
                    $newPaid = (float)($bill['amount_paid'] ?? 0) + (float)($payment['amount'] ?? 0);
                    $billModel->updateBillPayment((int)$bill['id'], $newPaid, 'confirmed_and_verified');
                    try {
                        require_once __DIR__ . '/ClientBill.php';
                        $cb = new ClientBill($this->db);
                        $cb->updateStatusByBillId((int)$bill['id'], (int)($payment['user_id'] ?? 0), 'confirmed_and_verified');
                    } catch (\Throwable $e) {}
                    $this->ensureVerifiedPaymentsTable();
                    $this->insertVerifiedPayment(
                        (int)$payment['payment_id'],
                        (string)($payment['client_name'] ?? ($payment['username'] ?? '')),
                        (float)($payment['amount'] ?? 0),
                        (string)($payment['payment_date'] ?? date('Y-m-d H:i:s')),
                        (string)($payment['payment_type'] ?? 'bill_payment')
                    );
                } elseif ($newStatus === 'rejected') {
                    $billModel->updateBillPayment((int)$bill['id'], (float)($bill['amount_paid'] ?? 0), 'pending');
                    try {
                        require_once __DIR__ . '/ClientBill.php';
                        $cb = new ClientBill($this->db);
                        $cb->updateStatusByBillId((int)$bill['id'], (int)($payment['user_id'] ?? 0), 'pending');
                    } catch (\Throwable $e) {}
                }
            }
        } elseif (($payment['type'] ?? '') === 'service_payment') {
            require_once __DIR__ . '/ClientService.php';
            $cs = new ClientService($this->db);
            $clientServiceId = (int)($payment['reference_id'] ?? 0);
            if ($clientServiceId > 0) {
                if ($newStatus === 'confirmed_and_verified' || $newStatus === 'completed') {
                    $cs->markServiceAsPaid($clientServiceId);
                    $this->ensureVerifiedPaymentsTable();
                    $this->insertVerifiedPayment(
                        (int)$payment['payment_id'],
                        (string)($payment['client_name'] ?? ($payment['username'] ?? '')),
                        (float)($payment['amount'] ?? 0),
                        (string)($payment['payment_date'] ?? date('Y-m-d H:i:s')),
                        'service_payment'
                    );
                } elseif ($newStatus === 'rejected') {
                    try {
                        $cs->updateClientServiceStatus($clientServiceId, 'pending_payment');
                    } catch (\Throwable $e) {}
                }
            }
        } elseif (($payment['type'] ?? '') === 'plan_renewal') {
            require_once __DIR__ . '/ClientPlan.php';
            $cp = new ClientPlan($this->db);
            $clientPlanId = (int)($payment['reference_id'] ?? 0);
            if ($clientPlanId > 0) {
                if ($newStatus === 'confirmed_and_verified' || $newStatus === 'completed') {
                    $cp->markPlanAsPaid($clientPlanId);
                    $this->ensureVerifiedPaymentsTable();
                    $this->insertVerifiedPayment(
                        (int)$payment['payment_id'],
                        (string)($payment['client_name'] ?? ($payment['username'] ?? '')),
                        (float)($payment['amount'] ?? 0),
                        (string)($payment['payment_date'] ?? date('Y-m-d H:i:s')),
                        'plan_renewal'
                    );
                }
            }
        }
        return true;
    }
    
    /**
     * Get available payment methods
     * 
     * @return array List of payment methods
     */
    public function getPaymentMethods() {
        return [
            'mpesa' => 'M-Pesa',
            'mpesa_manual' => 'Manual M-Pesa',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'cheque' => 'Cheque',
            'other' => 'Other'
        ];
    }
    
    /**
     * Initiate M-Pesa payment via STK Push
     * 
     * @param array $payment_data Payment details
     * @return array Response with success status and message
     */
    public function initiateMpesaPayment($payment_data) {
        $phone = $payment_data['phone'] ?? '';
        $amount = $payment_data['amount'] ?? 0;
        $reference = $payment_data['reference'] ?? 'WaterBill';
        $description = $payment_data['description'] ?? 'Water Bill Payment';
        if (!preg_match('/^254[0-9]{9}$/', $phone)) {
            return ['success' => false, 'message' => 'Invalid phone number format. Must be in format: 254XXXXXXXXX'];
        }
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Invalid amount. Must be greater than zero.'];
        }
        $base = 'https://sandbox.safaricom.co.ke';
        $tokenUrl = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $consumerKey = getenv('MPESA_SANDBOX_CONSUMER_KEY') ?: (getenv('MPESA_CONSUMER_KEY') ?: 'JLZ20U19fXbm8D3c78rQntPYKqFLtQi72hEQraEs9ZFxqSlT');
        $consumerSecret = getenv('MPESA_SANDBOX_CONSUMER_SECRET') ?: (getenv('MPESA_CONSUMER_SECRET') ?: 'u4TL4iwZ5OE8xMilhbZST8zdqEVAYWHUeoruL0JeagA0s3cQKzPRrwKYx7pfntsG');
        $credentials = base64_encode($consumerKey . ':' . $consumerSecret);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic {$credentials}"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $tokenResp = curl_exec($ch);
        $tokenCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $tokenErr = curl_error($ch);
        curl_close($ch);
        if ($tokenErr) { return ['success' => false, 'message' => 'cURL Error: ' . $tokenErr]; }
        $tokenJson = json_decode($tokenResp, true);
        if ($tokenCode !== 200 || !isset($tokenJson['access_token'])) {
            $msg = $tokenJson['errorMessage'] ?? ($tokenJson['error'] ?? 'Failed to get access token. HTTP Code: ' . $tokenCode);
            return ['success' => false, 'message' => $msg, 'response' => $tokenJson];
        }
        $accessToken = $tokenJson['access_token'];
        $timestamp = date('YmdHis');
        $shortCode = getenv('MPESA_SANDBOX_SHORTCODE') ?: (getenv('MPESA_SHORTCODE') ?: '174379');
        $passKey = getenv('MPESA_SANDBOX_PASS_KEY') ?: (getenv('MPESA_PASS_KEY') ?: 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919');
        $password = base64_encode($shortCode . $passKey . $timestamp);
        $stkUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $callbackUrl = 'https://cohesively-locustlike-cody.ngrok-free.dev/water_billing_system/public/callback.php';
        $amountInt = max(1, (int)round((float)$amount));
        $payload = [
            'BusinessShortCode' => $shortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amountInt,
            'PartyA' => $phone,
            'PartyB' => $shortCode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => $reference,
            'TransactionDesc' => $description
        ];
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $stkUrl);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json']);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch2, CURLOPT_POST, true);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, false);
        $stkResp = curl_exec($ch2);
        $stkErr = curl_error($ch2);
        curl_close($ch2);
        if ($stkErr) { return ['success' => false, 'message' => 'cURL Error: ' . $stkErr]; }
        $stkJson = json_decode($stkResp, true);
        if (isset($stkJson['ResponseCode']) && $stkJson['ResponseCode'] == '0') {
            return ['success' => true, 'message' => 'STK Push initiated successfully', 'checkout_request_id' => $stkJson['CheckoutRequestID'], 'response' => $stkJson];
        }
        return ['success' => false, 'message' => $stkJson['errorMessage'] ?? 'Failed to initiate STK Push', 'response' => $stkJson];
    }
    
    /**
     * Store M-Pesa request for tracking
     * 
     * @param array $request_data Request details
     * @return int|bool The request ID if successful, false otherwise
     */
    public function storeMpesaRequest($request_data) {
        $this->ensureMpesaRequestsTable();
        // Extract request data
        $checkout_request_id = $request_data['checkout_request_id'] ?? '';
        $bill_id = $request_data['bill_id'] ?? null;
        $amount = $request_data['amount'] ?? 0;
        $phone = $request_data['phone'] ?? '';
        $status = $request_data['status'] ?? 'pending';
        
        $this->db->query('INSERT INTO mpesa_requests (checkout_request_id, bill_id, amount, phone_number, status, created_at) VALUES (?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE amount=VALUES(amount), phone_number=VALUES(phone_number), status=VALUES(status), updated_at=NOW()');
        $this->db->bind([$checkout_request_id, $bill_id, $amount, $phone, $status]);
        
        if ($this->db->execute()) {
            $request_id = $this->db->lastInsertId();
            $this->db->closeStmt();
            return $request_id;
        } else {
            error_log("Failed to store M-Pesa request: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }
    
    /**
     * Get payments for a specific bill
     * 
     * @param int $bill_id The bill ID
     * @return array List of payments for the bill
     */
    public function getPaymentsByBill($bill_id) {
        $this->db->query('SELECT p.*, p.payment_method as payment_method_name 
                         FROM payments p 
                         WHERE p.reference_id = ? AND (p.type = "bill_payment" OR p.type IS NULL OR p.type = "") 
                         ORDER BY p.payment_date DESC');
        $this->db->bind([$bill_id]);
        return $this->db->resultSet();
    }

    public function getPaymentsByService($client_service_id) {
        $this->db->query('SELECT p.*, p.payment_method as payment_method_name 
                         FROM payments p 
                         WHERE p.reference_id = ? AND (p.type = "service_payment" OR p.type IS NULL OR p.type = "") 
                         ORDER BY p.payment_date DESC');
        $this->db->bind([$client_service_id]);
        return $this->db->resultSet();
    }
    
    /**
     * Get recent payments for a client
     * 
     * @param int $client_id The client ID
     * @param int $limit Maximum number of payments to return
     * @return array List of recent payments
     */
    public function getRecentPaymentsByClient($client_id, $limit = 5) {
        $this->db->query('SELECT p.*, b.id as bill_id, p.payment_method as payment_method_name 
                         FROM payments p 
                         JOIN bills b ON p.reference_id = b.id AND p.type = "bill_payment" 
                         WHERE b.client_id = ? 
                         ORDER BY p.payment_date DESC 
                         LIMIT ?');
        $this->db->bind([$client_id, $limit]);
        return $this->db->resultSet();
    }
    
    /**
     * Process M-Pesa callback
     * 
     * @param array $callback_data Callback data from M-Pesa
     * @return bool True if processed successfully, false otherwise
     */
    public function processMpesaCallback($callback_data) {
        $this->ensureMpesaRequestsTable();
        // Extract callback data
        $result_code = $callback_data['ResultCode'] ?? null;
        $result_desc = $callback_data['ResultDesc'] ?? '';
        $checkout_request_id = $callback_data['CheckoutRequestID'] ?? '';
        $transaction_id = $callback_data['MpesaReceiptNumber'] ?? null;
        $transaction_date = $callback_data['TransactionDate'] ?? null;
        $phone_number = $callback_data['PhoneNumber'] ?? null;
        $amount = $callback_data['Amount'] ?? 0;
        
        // Log the callback for debugging
        error_log('M-Pesa callback received: ' . json_encode($callback_data));
        
        // Check if the transaction was successful
        if ($result_code !== '0') {
            // Update the mpesa_request status to failed
            $this->db->query('UPDATE mpesa_requests SET status = ?, result_desc = ?, updated_at = NOW() WHERE checkout_request_id = ?');
            $this->db->bind(['failed', $result_desc, $checkout_request_id]);
            $this->db->execute();
            $this->db->closeStmt();
            
            error_log("M-Pesa transaction failed: " . $result_desc);
            return false;
        }
        
        // Find the mpesa_request record
        $this->db->query('SELECT * FROM mpesa_requests WHERE checkout_request_id = ? LIMIT 1');
        $this->db->bind([$checkout_request_id]);
        $request = $this->db->single();
        $this->db->closeStmt();
        
        if (!$request) {
            error_log("M-Pesa request not found for checkout_request_id: " . $checkout_request_id);
            return false;
        }
        
        // Update the mpesa_request status to completed
        $this->db->query('UPDATE mpesa_requests SET status = ?, transaction_id = ?, result_desc = ?, updated_at = NOW() WHERE id = ?');
        $this->db->bind(['completed', $transaction_id, $result_desc, $request['id']]);
        $this->db->execute();
        $this->db->closeStmt();
        
        // If this is a bill payment, update the pending payment (if exists) or record a new one, then update the bill status
        if ($request['bill_id']) {
            // Get the bill details
            $this->db->query('SELECT * FROM bills WHERE id = ? LIMIT 1');
            $this->db->bind([$request['bill_id']]);
            $bill = $this->db->single();
            $this->db->closeStmt();
            
            if ($bill) {
                // Try to update the most recent pending payment for this bill and amount
                $this->db->query('SELECT id FROM payments WHERE type = "bill_payment" AND reference_id = ? AND amount = ? AND status = "pending" ORDER BY payment_date DESC LIMIT 1');
                $this->db->bind([$bill['id'], $amount]);
                $existing = $this->db->single();
                $this->db->closeStmt();

                if ($existing && isset($existing['id'])) {
                    // Update existing pending payment with transaction details
                    $this->db->query('UPDATE payments SET status = ?, transaction_id = ?, payment_method = ?, updated_at = NOW() WHERE id = ?');
                    $this->db->bind(['completed', $transaction_id, 'Mpesa STK-Push', $existing['id']]);
                    $payment_success = $this->db->execute();
                    $payment_id = $existing['id'];
                    $this->db->closeStmt();
                } else {
                    // Record a new completed payment
                    $this->db->query('INSERT INTO payments (user_id, type, reference_id, amount, payment_method, transaction_id, status, payment_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
                    $this->db->bind([$bill['client_id'], 'bill_payment', $bill['id'], $amount, 'Mpesa STK-Push', $transaction_id, 'completed']);
                    $payment_success = $this->db->execute();
                    $payment_id = $this->db->lastInsertId();
                    $this->db->closeStmt();
                }
                
                if ($payment_success) {
                    // Update the bill's paid amount and set to pending until verification
                    $new_amount_paid = $bill['amount_paid'] + $amount;
                    $this->db->query('UPDATE bills SET amount_paid = ?, payment_status = ?, updated_at = NOW() WHERE id = ?');
                    $this->db->bind([$new_amount_paid, 'pending', $bill['id']]);
                    $bill_update_success = $this->db->execute();
                    $this->db->closeStmt();
                    
                    if ($bill_update_success) {
                        // Send SMS notification for payment confirmation
                        $this->sendPaymentConfirmationSMS($bill['client_id'], $amount, $bill['id'], $transaction_id);
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    private function ensureFlexibleStatusColumns(): void {
        try {
            // Make payments.status flexible
            $this->db->query("SHOW COLUMNS FROM payments LIKE 'status'");
            $col = $this->db->single();
            $this->db->closeStmt();
            if ($col && isset($col['Type']) && stripos($col['Type'], 'enum') !== false && stripos($col['Type'], 'confirmed_and_verified') === false) {
                $this->db->query("ALTER TABLE payments MODIFY COLUMN status VARCHAR(32) NOT NULL DEFAULT 'pending'");
                $this->db->execute();
                $this->db->closeStmt();
            }
            // Make bills.payment_status flexible
            $this->db->query("SHOW COLUMNS FROM bills LIKE 'payment_status'");
            $bcol = $this->db->single();
            $this->db->closeStmt();
            if ($bcol && isset($bcol['Type']) && stripos($bcol['Type'], 'enum') !== false && stripos($bcol['Type'], 'confirmed_and_verified') === false) {
                $this->db->query("ALTER TABLE bills MODIFY COLUMN payment_status VARCHAR(32) NOT NULL DEFAULT 'pending'");
                $this->db->execute();
                $this->db->closeStmt();
            }
            // Make client_bills.bill_status flexible
            $this->db->query("SHOW COLUMNS FROM client_bills LIKE 'bill_status'");
            $ccol = $this->db->single();
            $this->db->closeStmt();
            if ($ccol && isset($ccol['Type']) && stripos($ccol['Type'], 'enum') !== false && stripos($ccol['Type'], 'confirmed_and_verified') === false) {
                $this->db->query("ALTER TABLE client_bills MODIFY COLUMN bill_status VARCHAR(32) NOT NULL DEFAULT 'pending'");
                $this->db->execute();
                $this->db->closeStmt();
            }
        } catch (\Throwable $e) {
            // ignore schema adjustments errors
        }
    }

    private function ensureMpesaRequestsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS mpesa_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            checkout_request_id VARCHAR(64) NOT NULL,
            bill_id INT NULL,
            amount DECIMAL(10,2) NOT NULL,
            phone_number VARCHAR(20) NOT NULL,
            transaction_id VARCHAR(64) NULL,
            result_desc TEXT NULL,
            status ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_checkout_request_id (checkout_request_id),
            INDEX (checkout_request_id),
            INDEX (bill_id),
            INDEX (status),
            INDEX (transaction_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->query($sql);
        $this->db->execute();
        $this->db->closeStmt();

        $this->ensureUniqueIndex('payments', 'uniq_transaction_id', 'ADD UNIQUE KEY uniq_transaction_id (transaction_id)');
    }

    private function ensureVerifiedPaymentsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS verified_payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            payment_id INT NOT NULL,
            client_name VARCHAR(255) NOT NULL,
            amount_paid DECIMAL(12,2) NOT NULL,
            payment_date DATETIME NOT NULL,
            payment_type VARCHAR(64) NOT NULL,
            UNIQUE KEY uniq_payment_id (payment_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->query($sql);
        $this->db->execute();
        $this->db->closeStmt();
    }

    public function insertVerifiedPayment(int $paymentId, string $clientName, float $amountPaid, string $paymentDate, string $paymentType): bool {
        $this->ensureVerifiedPaymentsTable();
        $this->db->query('INSERT INTO verified_payments (payment_id, client_name, amount_paid, payment_date, payment_type) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE client_name = VALUES(client_name), amount_paid = VALUES(amount_paid), payment_date = VALUES(payment_date), payment_type = VALUES(payment_type)');
        $this->db->bind([$paymentId, $clientName, $amountPaid, $paymentDate, $paymentType]);
        $ok = $this->db->execute();
        $this->db->closeStmt();
        return (bool)$ok;
    }

    private function ensureUniqueIndex($table, $indexName, $addClause) {
        $this->db->query('SHOW INDEX FROM ' . $table . ' WHERE Key_name = ?');
        $this->db->bind([$indexName]);
        $rows = $this->db->resultSet();
        $this->db->closeStmt();
        if (!$rows || count($rows) === 0) {
            $this->db->query('ALTER TABLE ' . $table . ' ' . $addClause);
            $this->db->execute();
            $this->db->closeStmt();
        }
    }
    
    /**
     * Send payment confirmation SMS
     * 
     * @param int $client_id Client ID
     * @param float $amount Payment amount
     * @param int $bill_id Bill ID
     * @param string $transaction_id M-Pesa transaction ID
     * @return bool True if SMS sent successfully, false otherwise
     */
    private function sendPaymentConfirmationSMS($client_id, $amount, $bill_id, $transaction_id) {
        // Load the SmsNotification class
        require_once __DIR__ . '/SmsNotification.php';
        
        // Get client phone number
        $this->db->query('SELECT phone, name FROM clients WHERE id = ? LIMIT 1');
        $this->db->bind([$client_id]);
        $client = $this->db->single();
        $this->db->closeStmt();
        
        if (!$client || empty($client['phone'])) {
            error_log("Cannot send SMS: Client phone not found for ID: " . $client_id);
            return false;
        }
        
        // Get bill details
        $this->db->query('SELECT meter_number FROM bills WHERE id = ? LIMIT 1');
        $this->db->bind([$bill_id]);
        $bill = $this->db->single();
        $this->db->closeStmt();
        
        $meter_number = $bill ? $bill['meter_number'] : 'N/A';
        
        // Format the message
        $message = "Dear " . ($client['name'] ?? 'Customer') . ", thank you for your payment of KES " . number_format($amount, 2);
        $message .= " for meter " . $meter_number . ". M-Pesa receipt: " . $transaction_id . ".";
        
        // Send the SMS
        try {
            $db = new \app\core\Database();
            $sms = new SmsNotification($db);
            return $sms->sendPaymentConfirmation($bill_id, $client['phone'], $amount, $transaction_id);
        } catch (\Exception $e) {
            error_log("SMS sending failed: " . $e->getMessage());
            
            // Fallback to the SMS class if available
            if (class_exists('\app\models\SMS')) {
                try {
                    $sms = new \app\models\SMS();
                    return $sms->sendSMS($client['phone'], $message);
                } catch (\Exception $e) {
                    error_log("Fallback SMS sending failed: " . $e->getMessage());
                }
            }
            
            return false;
        }
    }

    /**
     * Get payment details
     * 
     * @param int $payment_id Payment ID
     * @param int $user_id User ID
     * @return array|bool Payment details or false if not found
     */
    public function getPaymentDetails($payment_id, $user_id) {
        $this->db->query('
            SELECT p.*, 
                   CASE 
                       WHEN p.type = "plan_renewal" THEN bp.plan_name
                       WHEN p.type = "service_payment" THEN s.service_name
                   END AS item_name,
                   CASE 
                       WHEN p.type = "plan_renewal" THEN bp.base_rate
                       WHEN p.type = "service_payment" THEN s.cost
                   END AS item_price
            FROM payments p
            LEFT JOIN client_plans cp ON p.reference_id = cp.id AND p.type = "plan_renewal"
            LEFT JOIN billing_plans bp ON cp.plan_id = bp.id
            LEFT JOIN client_services cs ON p.reference_id = cs.id AND p.type = "service_payment"
            LEFT JOIN services s ON cs.service_id = s.id
            WHERE p.id = ? AND p.user_id = ?
        ');
        $this->db->bind([$payment_id, $user_id]);
        return $this->db->single();
    }
    
    /**
     * Gets a specified number of recent payments
     * 
     * @param int $limit The maximum number of payments to retrieve
     * @return array The recent payments
     */
    public function getRecentPayments($limit = 10) {
        $this->db->query('
            SELECT p.id as payment_id, p.*, u.id as client_id, u.username, u.full_name as client_name, u.email as client_email
            FROM payments p
            INNER JOIN users u ON p.user_id = u.id
            ORDER BY p.payment_date DESC
            LIMIT ?
        ');
        $this->db->bind([$limit]);
        $payments = $this->db->resultSet();
        $this->db->closeStmt();
        return $payments;
    }
    
    /**
     * Gets all payments for admin/finance manager view
     * 
     * @return array All payments with user details
     */
    public function getAllPayments() {
        $this->db->query('
            SELECT p.id as payment_id, p.*, u.id as client_id, u.username, u.full_name as client_name, u.email as client_email
            FROM payments p
            INNER JOIN users u ON p.user_id = u.id
            ORDER BY p.payment_date DESC
        ');
        return $this->db->resultSet();
    }
    
    /**
     * Gets payment report data for a specific period
     * 
     * @param string $startDate Start date for the report
     * @param string $endDate End date for the report
     * @param string $reportType Type of report (monthly, quarterly, annual)
     * @return array The payment report data
     */
    public function getPaymentReport(string $startDate, string $endDate, string $reportType = 'monthly') {
        $groupBy = '';
        $dateFormat = '';
        
        switch ($reportType) {
            case 'monthly':
                $groupBy = 'YEAR(p.payment_date), MONTH(p.payment_date)';
                $dateFormat = 'DATE_FORMAT(p.payment_date, "%Y-%m")';
                break;
            case 'quarterly':
                $groupBy = 'YEAR(p.payment_date), QUARTER(p.payment_date)';
                $dateFormat = 'CONCAT(YEAR(p.payment_date), "-Q", QUARTER(p.payment_date))';
                break;
            case 'annual':
                $groupBy = 'YEAR(p.payment_date)';
                $dateFormat = 'YEAR(p.payment_date)';
                break;
            default:
                $groupBy = 'p.payment_date';
                $dateFormat = 'DATE(p.payment_date)';
        }
        
        $this->db->query("SELECT 
                        {$dateFormat} as period,
                        COUNT(p.id) as payment_count,
                        SUM(p.amount) as total_amount,
                        p.payment_method,
                        p.status
                    FROM payments p
                    WHERE p.payment_date BETWEEN ? AND ?
                    GROUP BY {$groupBy}, p.payment_method, p.status
                    ORDER BY p.payment_date ASC");
        
        $this->db->bind([$startDate, $endDate]);
        $report = $this->db->resultSet();
        $this->db->closeStmt();
        return $report;
    }
    
    /**
     * Gets payment report data for a specific client and period
     * 
     * @param int $clientId The client ID
     * @param string $startDate Start date for the report
     * @param string $endDate End date for the report
     * @return array The payment report data
     */
    public function getClientPaymentReport(int $clientId, string $startDate, string $endDate) {
        $start = $startDate . ' 00:00:00';
        $endExclusive = date('Y-m-d', strtotime($endDate . ' +1 day')) . ' 00:00:00';
        $this->db->query('SELECT p.*, b.id AS bill_id, b.bill_date, b.amount_due AS bill_amount
                         FROM payments p
                         LEFT JOIN bills b ON p.reference_id = b.id AND p.type = "bill_payment"
                         WHERE p.user_id = ? AND p.payment_date >= ? AND p.payment_date < ?
                         ORDER BY p.payment_date ASC');
        $this->db->bind([$clientId, $start, $endExclusive]);
        $report = $this->db->resultSet();
        $this->db->closeStmt();
        return $report;
    }
    
    /**
     * Gets payment report data for all clients for a specific period
     * 
     * @param string $startDate Start date for the report
     * @param string $endDate End date for the report
     * @return array The payment report data
     */
    public function getAllClientsPaymentReport(string $startDate, string $endDate) {
        $start = $startDate . ' 00:00:00';
        $endExclusive = date('Y-m-d', strtotime($endDate . ' +1 day')) . ' 00:00:00';
        $this->db->query('SELECT p.*, u.full_name as client_name, u.email as client_email,
                         b.id AS bill_id, b.bill_date, b.amount_due AS bill_amount
                         FROM payments p
                         INNER JOIN users u ON p.user_id = u.id
                         LEFT JOIN bills b ON p.reference_id = b.id AND p.type = "bill_payment"
                         WHERE p.payment_date >= ? AND p.payment_date < ?
                         ORDER BY u.full_name ASC, p.payment_date ASC');
        $this->db->bind([$start, $endExclusive]);
        $report = $this->db->resultSet();
        $this->db->closeStmt();
        return $report;
    }
}
