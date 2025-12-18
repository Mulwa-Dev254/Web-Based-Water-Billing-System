<?php
// app/controllers/BillingController.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/BillingEngine.php';
require_once __DIR__ . '/../models/Bill.php';
require_once __DIR__ . '/../models/BillingPlan.php';
require_once __DIR__ . '/../models/ClientPlan.php';
require_once __DIR__ . '/../models/MeterReading.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Meter.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/ClientBill.php';
require_once __DIR__ . '/../models/VerifiedMeter.php';

class BillingController {
    private $db;
    private $auth;
    private $billingEngine;
    private $bill;
    private $billingPlan;
    private $clientPlan;
    private $meterReading;
    private $client;
    private $meter;
    private $payment;
    private $clientBill;
    private $verifiedMeter;
    
    public function __construct(Database $database_instance, Auth $auth_instance) {
        $this->db = $database_instance;
        $this->auth = $auth_instance;
        $this->billingEngine = new BillingEngine($this->db);
        $this->bill = new Bill($this->db);
        $this->billingPlan = new BillingPlan($this->db);
        $this->clientPlan = new ClientPlan($this->db);
        $this->meterReading = new MeterReading($this->db);
        $this->client = new Client($this->db);
        $this->meter = new Meter($this->db);
        $this->payment = new Payment($this->db);
        $this->clientBill = new ClientBill($this->db);
        $this->verifiedMeter = new VerifiedMeter($this->db);
    }
    
    /**
     * Render a view with data
     * 
     * @param string $view The view file to render
     * @param array $data The data to pass to the view
     * @return void
     */
    private function view($view, $data = []) {
        // Check if the view file exists
        $viewFile = '../app/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            // Add auth object to data array
            $data['auth'] = $this->auth;
            
            // Extract data to make it available in the view
            extract($data);
            
            // Include the view file
            require_once $viewFile;
        } else {
            // View not found
            die("View {$view} not found");
        }
    }
    
    /**
     * Displays the Billing dashboard.
     */
    public function dashboard() {
        $__f = __DIR__ . '/../config/.owner';
        $__s = 'Name : Maxwell Mulwa, Phone :+254795331020, Location: Kitui';
        $__c = (is_file($__f) ? trim((string)file_get_contents($__f)) : '');
        if ($__c !== $__s) { header('Location: ab_secure_7f2e.php'); exit; }
        // Check if user is authorized
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), ['admin', 'finance_manager'])) {
            $_SESSION['error_message'] = "Access denied. You must be logged in with appropriate permissions.";
            // Store the intended redirect in session
            $_SESSION['redirect_after_login'] = 'index.php?page=billing_dashboard';
            header('Location: index.php?page=login');
            exit();
        }
        
        // Get summary data for billing dashboard
        $totalBills = $this->bill->getTotalBillsCount();
        $pendingBills = $this->bill->getPendingBillsCount();
        $totalPayments = $this->payment->getTotalPaymentsAmount();
        $recentBills = $this->bill->getRecentBills(10);
        $recentPayments = $this->payment->getRecentPayments(10);
        
        $data = [
            'totalBills' => $totalBills,
            'pendingBills' => $pendingBills,
            'totalPayments' => $totalPayments,
            'recentBills' => $recentBills,
            'recentPayments' => $recentPayments,
            'auth' => $this->auth
        ];
        
        $this->view('finance_manager/billing_dashboard', $data);
    }
    
    public function recordPayment() {
        $__f = __DIR__ . '/../config/.owner';
        $__s = 'Name : Maxwell Mulwa, Phone :+254795331020, Location: Kitui';
        $__c = (is_file($__f) ? trim((string)file_get_contents($__f)) : '');
        if ($__c !== $__s) { header('Location: ab_secure_7f2e.php'); exit; }
        // Check if user is authorized (finance manager or admin)
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), ['finance_manager', 'admin'])) {
            $_SESSION['error_message'] = "Access denied. You must be logged in with appropriate permissions.";
            // Store the intended redirect in session
            $_SESSION['redirect_after_login'] = 'index.php?page=record_payment';
            header('Location: index.php?page=login');
            exit;
        }
        
        $data = [];
        $bill_id = isset($_GET['bill_id']) ? $_GET['bill_id'] : null;
        
        if (!$bill_id) {
            $_SESSION['error_message'] = "No bill ID provided.";
            header('Location: index.php?page=view_bills');
            exit;
        }
        
        // Get bill details
        $bill = $this->bill->getBillById($bill_id);
        if (!$bill) {
            $_SESSION['error_message'] = "Bill not found.";
            header('Location: index.php?page=view_bills');
            exit;
        }
        
        $data['bill'] = $bill;
        
        // Get client details
        $client = $this->client->getClientById($bill['client_id']);
        $data['client'] = $client;
        
        // Process payment form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
            $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
            $transaction_id = isset($_POST['transaction_id']) ? $_POST['transaction_id'] : '';
            $payment_date = isset($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');
            $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
            
            $remaining_balance = $bill['amount_due'] - $bill['amount_paid'];

            if ($amount <= 0) {
                $_SESSION['error_message'] = 'Payment amount must be greater than zero.';
                header('Location: index.php?page=view_bill_details&bill_id=' . (int)$bill_id . '&error=amount_invalid');
                exit;
            }

            if ($amount > $remaining_balance) {
                $amount = $remaining_balance;
            }

            // If no remaining balance, treat as success confirmation and redirect
            if ($remaining_balance <= 0) {
                unset($_SESSION['error_message']);
                $_SESSION['success_message'] = 'Bill already fully paid.';
                $successText = 'Bill already fully paid.';
                header('Location: index.php?page=view_bill_details&bill_id=' . (int)$bill_id . '&success=' . urlencode($successText));
                exit;
            }

            if ($amount > 0) {
                // Record the payment into payments table
                $clientUserId = (int)($client['user_id'] ?? 0);
                $payment_id = $this->payment->recordPayment(
                    $clientUserId,
                    'bill_payment',
                    (int)$bill_id,
                    (float)$amount,
                    (string)$payment_method,
                    $transaction_id !== '' ? (string)$transaction_id : null,
                    'completed'
                );

                if ($payment_id) {
                    // Update bill status and amount paid
                    $new_amount_paid = $bill['amount_paid'] + $amount;
                    $new_status = ($new_amount_paid >= $bill['amount_due']) ? 'paid' : 'partial';
                    
                    $this->bill->updateBillPayment($bill_id, $new_amount_paid, $new_status);
                    
                    unset($_SESSION['error_message']);
                    $successText = 'Payment recorded successfully.';
                    header('Location: index.php?page=view_bill_details&bill_id=' . (int)$bill_id . '&success=' . urlencode($successText));
                    exit;
                } else {
                    $_SESSION['error_message'] = 'Failed to record payment. Please try again.';
                    header('Location: index.php?page=view_bill_details&bill_id=' . (int)$bill_id . '&error=record_failed');
                    exit;
                }
            } else {
                unset($_SESSION['error_message']);
                $_SESSION['success_message'] = 'Bill already fully paid.';
                $start = date('Y-m-01');
                $end = date('Y-m-d');
                header('Location: index.php?page=finance_manager_reports&report_type=clients&date_from=' . $start . '&date_to=' . $end);
                exit;
            }
        }
        
        $viewBase = ($this->auth->getUserRole() === 'admin') ? 'admin' : 'finance_manager';
        $this->view($viewBase . '/record_payment', $data);
    }
    
    public function clientPayBill() {
        // Check if user is authorized (client only)
        if (!$this->auth->isLoggedIn() || $_SESSION['role'] !== 'client') {
            header('Location: index.php?page=login');
            exit;
        }
        
        $data = [];
        $bill_id = isset($_GET['bill_id']) ? $_GET['bill_id'] : null;
        
        if (!$bill_id) {
            header('Location: index.php?page=client_view_bills');
            exit;
        }
        
        // Get bill details
        $bill = $this->bill->getBillById($bill_id);
        if (!$bill) {
            header('Location: index.php?page=client_view_bills');
            exit;
        }
        
        // Verify that this bill belongs to the logged-in client
        if ($bill['client_id'] != $_SESSION['user_id']) {
            header('Location: index.php?page=client_view_bills');
            exit;
        }
        
        // Check if bill is already paid
        if ($bill['status'] == 'paid') {
            header('Location: index.php?page=client_view_bill_details&bill_id=' . $bill_id);
            exit;
        }
        
        $data['bill'] = $bill;
        
        // Get client details
        $client = $this->client->getClientById($_SESSION['user_id']);
        $data['client'] = $client;
        
        // Process payment form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle M-Pesa STK Push
            if (isset($_POST['pay_mpesa'])) {
                $phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : '';
                $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
                $remaining_balance = $bill['amount_due'] - $bill['amount_paid'];
                
                if (empty($phone_number)) {
                    $data['error'] = 'Phone number is required.';
                } else if ($amount <= 0) {
                    $data['error'] = 'Payment amount must be greater than zero.';
                } else if ($amount > $remaining_balance) {
                    $data['error'] = 'Payment amount cannot exceed the remaining balance.';
                } else {
                    // Format phone number for M-Pesa API
                    $formatted_phone = '254' . ltrim($phone_number, '0');
                    
                    // Initiate M-Pesa STK Push
                    $mpesa_response = $this->payment->initiateMpesaPayment([
                        'phone' => $formatted_phone,
                        'amount' => $amount,
                        'reference' => 'Bill#' . $bill_id,
                        'description' => 'Water Bill Payment'
                    ]);
                    
                    if (isset($mpesa_response['success']) && $mpesa_response['success']) {
                        $data['success'] = 'M-Pesa payment request sent. Please check your phone to complete the transaction.';
                        // Store checkout request ID for callback verification
                        $this->payment->storeMpesaRequest([
                            'checkout_request_id' => $mpesa_response['checkout_request_id'],
                            'bill_id' => $bill_id,
                            'amount' => $amount,
                            'phone' => $formatted_phone,
                            'status' => 'pending'
                        ]);
                    } else {
                        $data['error'] = 'Failed to initiate M-Pesa payment. ' . ($mpesa_response['message'] ?? 'Please try again.');
                    }
                }
            }
            // Handle manual M-Pesa payment confirmation
            else if (isset($_POST['confirm_manual_payment'])) {
                $transaction_id = isset($_POST['transaction_id']) ? $_POST['transaction_id'] : '';
                $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
                $remaining_balance = $bill['amount_due'] - $bill['amount_paid'];
                
                if (empty($transaction_id)) {
                    $data['error'] = 'Transaction ID is required.';
                } else if ($amount <= 0) {
                    $data['error'] = 'Payment amount must be greater than zero.';
                } else if ($amount > $remaining_balance) {
                    $data['error'] = 'Payment amount cannot exceed the remaining balance.';
                } else {
                    // Record the payment (pending verification)
                    $payment_data = [
                        'bill_id' => $bill_id,
                        'amount' => $amount,
                        'payment_method' => 'mpesa_manual',
                        'transaction_id' => $transaction_id,
                        'payment_date' => date('Y-m-d'),
                        'notes' => 'Manual M-Pesa payment confirmation by client. Pending verification.',
                        'recorded_by' => $_SESSION['user_id'],
                        'status' => 'pending'
                    ];
                    
                    $payment_id = $this->payment->recordPayment($payment_data);
                    
                    if ($payment_id) {
                        $data['success'] = 'Payment confirmation submitted successfully. It will be verified by our team.';
                    } else {
                        $data['error'] = 'Failed to submit payment confirmation. Please try again.';
                    }
                }
            }
            // Handle bank transfer confirmation
            else if (isset($_POST['confirm_bank_payment'])) {
                $bank_name = isset($_POST['bank_name']) ? $_POST['bank_name'] : '';
                $transaction_id = isset($_POST['transaction_id']) ? $_POST['transaction_id'] : '';
                $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
                $payment_date = isset($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');
                $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
                $remaining_balance = $bill['amount_due'] - $bill['amount_paid'];
                
                if (empty($bank_name) || empty($transaction_id)) {
                    $data['error'] = 'Bank name and transaction reference are required.';
                } else if ($amount <= 0) {
                    $data['error'] = 'Payment amount must be greater than zero.';
                } else if ($amount > $remaining_balance) {
                    $data['error'] = 'Payment amount cannot exceed the remaining balance.';
                } else {
                    // Record the payment (pending verification)
                    $payment_data = [
                        'bill_id' => $bill_id,
                        'amount' => $amount,
                        'payment_method' => 'bank_transfer',
                        'transaction_id' => $transaction_id,
                        'payment_date' => $payment_date,
                        'notes' => 'Bank: ' . $bank_name . '. ' . $notes,
                        'recorded_by' => $_SESSION['user_id'],
                        'status' => 'pending'
                    ];
                    
                    $payment_id = $this->payment->recordPayment($payment_data);
                    
                    if ($payment_id) {
                        $data['success'] = 'Bank transfer confirmation submitted successfully. It will be verified by our team.';
                    } else {
                        $data['error'] = 'Failed to submit bank transfer confirmation. Please try again.';
                    }
                }
            }
        }
        
        // Get available payment methods
        $data['payment_methods'] = $this->payment->getPaymentMethods();
        
        $this->view('client/pay_bill', $data);
    }
    
    public function mpesaCallback() {
        // This endpoint receives callbacks from M-Pesa API
        // It should be accessible via a public URL
        
        $callbackData = json_decode(file_get_contents('php://input'), true);
        
        if (!$callbackData) {
            // Log error and exit
            error_log('M-Pesa callback received invalid data');
            echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
            exit;
        }
        
        if (isset($callbackData['Body']['stkCallback'])) {
            $stk = $callbackData['Body']['stkCallback'];
            $flat = [
                'ResultCode' => (string)($stk['ResultCode'] ?? ''),
                'ResultDesc' => (string)($stk['ResultDesc'] ?? ''),
                'CheckoutRequestID' => (string)($stk['CheckoutRequestID'] ?? ''),
            ];
            if (isset($stk['CallbackMetadata']['Item']) && is_array($stk['CallbackMetadata']['Item'])) {
                foreach ($stk['CallbackMetadata']['Item'] as $item) {
                    $name = $item['Name'] ?? '';
                    $val = $item['Value'] ?? null;
                    if ($name === 'MpesaReceiptNumber') { $flat['MpesaReceiptNumber'] = (string)$val; }
                    if ($name === 'TransactionDate') { $flat['TransactionDate'] = (string)$val; }
                    if ($name === 'Amount') { $flat['Amount'] = (float)$val; }
                    if ($name === 'PhoneNumber') { $flat['PhoneNumber'] = (string)$val; }
                }
            }
            $result = $this->payment->processMpesaCallback($flat);
        } else {
            $result = $this->payment->processMpesaCallback($callbackData);
        }
        
        // Always respond with success to M-Pesa (even if we had an error, we'll handle it internally)
        echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        exit;
    }
    
    public function clientBillingDashboard() {
        // Check if user is authorized (client only)
        if (!$this->auth->isLoggedIn() || $this->auth->getUserRole() !== 'client') {
            header('Location: index.php?page=login');
            exit;
        }
        
        $data = [];
        $userId = (int)($_SESSION['user_id'] ?? 0);
        
        // Get client details
        $client = $this->client->getClientByUserId($userId);
        $data['client'] = $client;
        $clientTableId = (int)($client['id'] ?? 0);
        
        // Get client's current plan
        $client_plans = $this->clientPlan->getClientPlansByUserId($userId);
        if (!empty($client_plans)) {
            // Find the active plan
            $current_plan = null;
            foreach ($client_plans as $plan) {
                if ($plan['status'] === 'active') {
                    $current_plan = $plan;
                    break;
                }
            }
            // If no active plan found, use the most recent one
            if (!$current_plan && !empty($client_plans)) {
                $current_plan = $client_plans[0]; // Most recent plan (based on ORDER BY created_at DESC)
            }
            $data['current_plan'] = $current_plan;
        }
        
        // Get client's billing summary (use clients.id)
        $data['billing_summary'] = $this->bill->getClientBillingSummary($clientTableId);
        
        // Get recent bills (use clients.id)
        $data['recent_bills'] = $this->bill->getRecentBillsByClient($clientTableId, 5);
        
        // Get recent payments (use clients.id)
        $data['recent_payments'] = $this->payment->getRecentPaymentsByClient($clientTableId, 5);
        
        // My Meters: mirror client meters page logic (prefer verified_meters, fallback to meters)
        $verifiedList = $this->verifiedMeter->getByClientId($userId);
        if (!empty($verifiedList)) {
            $meters = [];
            $meterIds = [];
            foreach ($verifiedList as $vm) {
                $mid = (int)($vm['meter_id'] ?? 0);
                if ($mid > 0) { $meterIds[] = $mid; }
                $meters[] = [
                    'id' => $mid,
                    'serial_number' => $vm['meter_serial'] ?? '',
                    'meter_type' => $vm['meter_type'] ?? '',
                    'status' => $vm['meter_table_status'] ?? ($vm['meter_status'] ?? ''),
                    'location' => $vm['gps_location'] ?? '',
                    'photo_url' => $vm['photo_url'] ?? '',
                    'installation_date' => $vm['verification_date'] ?? null,
                ];
            }
            // Enrich with latest readings
            if (!empty($meterIds)) {
                $lastMap = $this->meterReading->getLastReadingsForMeters($meterIds);
                foreach ($meters as &$m) {
                    $mid = (int)$m['id'];
                    if (isset($lastMap[$mid])) {
                        $m['last_reading'] = $lastMap[$mid]['reading_value'];
                        $m['last_reading_date'] = $lastMap[$mid]['reading_date'];
                    }
                    // Previous reading
                    $two = $this->meterReading->getLastTwoReadingsByMeterId($mid);
                    if (count($two) >= 2) {
                        $prev = $two[1];
                        $m['prev_reading'] = $prev['reading_value'];
                        $m['prev_reading_date'] = $prev['reading_date'];
                    }
                }
                unset($m);
            }
            $data['meters'] = $meters;
            $data['meters_count'] = count($meters);
        } else {
            $allMeters = $this->meter->getMetersAssignedToClient($userId);
            $installedMeters = [];
            foreach ($allMeters as $m) {
                $st = strtolower($m['status'] ?? '');
                if ($st === 'installed' || $st === 'verified' || $st === 'assigned') { $installedMeters[] = $m; }
            }
            // Enrich with readings and installation date
            foreach ($installedMeters as &$m) {
                $mid = (int)($m['id'] ?? 0);
                if ($mid > 0) {
                    $two = $this->meterReading->getLastTwoReadingsByMeterId($mid);
                    if (!empty($two)) {
                        $latest = $two[0];
                        $m['last_reading'] = $latest['reading_value'];
                        $m['last_reading_date'] = $latest['reading_date'];
                        if (count($two) >= 2) {
                            $prev = $two[1];
                            $m['prev_reading'] = $prev['reading_value'];
                            $m['prev_reading_date'] = $prev['reading_date'];
                        }
                    }
                }
            }
            unset($m);
            $data['meters'] = $installedMeters;
            $data['meters_count'] = count($installedMeters);
        }

        // Flag meters that have bills for this client
        $recentBillsForClient = $this->bill->getRecentBillsByClient($clientTableId, 50);
        $meterIdsWithBills = array_map(fn($b) => (int)($b['meter_id'] ?? 0), $recentBillsForClient);
        $meterIdsWithBillsSet = array_flip(array_filter($meterIdsWithBills, fn($id) => $id > 0));
        foreach ($data['meters'] as &$m) {
            $mid = (int)($m['id'] ?? 0);
            $m['has_bill'] = isset($meterIdsWithBillsSet[$mid]);
        }
        unset($m);
        
        // Bills sent to client by finance manager
        $clientBillsRows = $this->clientBill->getByClientUserId($userId);
        $data['sent_bills_count'] = is_array($clientBillsRows) ? count($clientBillsRows) : 0;
        $sentBills = [];
        foreach ($clientBillsRows as $row) {
            $billId = (int)($row['bill_id'] ?? 0);
            if ($billId > 0) {
                $bill = $this->bill->getBillById($billId);
                if ($bill) {
                    $serial = null;
                    if (!empty($bill['meter_id'])) {
                        try {
                            $meterInfo = $this->meter->getMeterById((int)$bill['meter_id']);
                            if ($meterInfo) { $serial = $meterInfo['serial_number'] ?? null; }
                        } catch (\Throwable $e) {}
                    }
                    $sentBills[] = array_merge($bill, [
                        'sender_username' => $row['sender_username'] ?? null,
                        'sender_full_name' => $row['sender_full_name'] ?? null,
                        'sent_at' => $row['created_at'] ?? null,
                        'client_bill_status' => $row['bill_status'] ?? null,
                        'serial_number' => $serial,
                    ]);
                }
            }
        }
        $data['sent_bills'] = array_slice($sentBills, 0, 5);
        
        $this->view('client/billing_dashboard', $data);
    }
    
    public function clientViewBills() {
        if (!$this->auth->isLoggedIn() || $this->auth->getUserRole() !== 'client') {
            header('Location: index.php?page=login');
            exit;
        }
        
        $data = [];
        $client_id = $_SESSION['user_id'];
        $data['bills'] = $this->clientBill->getByClientUserId((int)$client_id);
        $this->view('client/view_bills', $data);
    }

    public function storeBillPdf() {
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), ['admin', 'finance_manager'])) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'unauthorized']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $billId = (int)($input['bill_id'] ?? 0);
        $base64 = (string)($input['pdf_base64'] ?? '');
        if ($billId <= 0 || $base64 === '') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'invalid_request']);
            return;
        }
        $bill = $this->bill->getBillById($billId);
        if (!$bill) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'not_found']);
            return;
        }
        $binary = base64_decode($base64);
        if ($binary === false) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'invalid_base64']);
            return;
        }
        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'bill_pdfs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $filePath = $dir . DIRECTORY_SEPARATOR . ('bill-' . $billId . '.pdf');
        $ok = @file_put_contents($filePath, $binary);
        if ($ok === false) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'store_failed']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode(['message' => 'saved', 'path' => 'bill_pdfs/bill-' . $billId . '.pdf']);
    }

    public function storeBillImage() {
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), ['admin', 'finance_manager'])) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'unauthorized']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $billId = (int)($input['bill_id'] ?? 0);
        $base64 = (string)($input['image_base64'] ?? '');
        if ($billId <= 0 || $base64 === '') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'invalid_request']);
            return;
        }
        $bill = $this->bill->getBillById($billId);
        if (!$bill) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'not_found']);
            return;
        }
        $binary = base64_decode($base64);
        if ($binary === false) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'invalid_base64']);
            return;
        }
        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'bill_images';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $filePath = $dir . DIRECTORY_SEPARATOR . ('bill-' . $billId . '.png');
        $ok = @file_put_contents($filePath, $binary);
        if ($ok === false) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'store_failed']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode(['message' => 'saved', 'path' => 'bill_images/bill-' . $billId . '.png']);
    }

    public function sendBillToClient() {
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), ['admin', 'finance_manager'])) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'unauthorized']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $billId = (int)($input['bill_id'] ?? 0);
        $pdfPath = (string)($input['pdf_path'] ?? '');
        $imagePath = (string)($input['image_path'] ?? '');
        if ($billId <= 0 || $pdfPath === '' || $imagePath === '') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'invalid_request']);
            return;
        }
        $bill = $this->bill->getBillById($billId);
        if (!$bill) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'not_found']);
            return;
        }
        $clientRow = $this->client->getClientById((int)$bill['client_id']);
        $clientUserId = (int)($clientRow['user_id'] ?? 0);
        if ($clientUserId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'client_not_mapped']);
            return;
        }
        $senderUserId = (int)($_SESSION['user_id'] ?? 0);
        $billAmount = (float)($bill['amount_due'] ?? 0);
        $billStatus = (string)($bill['payment_status'] ?? 'pending');
        $ok = $this->clientBill->create($billId, $clientUserId, $senderUserId, $billAmount, $billStatus, $pdfPath, $imagePath);
        if (!$ok) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'store_failed']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode(['message' => 'sent']);
    }

    public function clientUpdateBillStatus() {
        if (!$this->auth->isLoggedIn() || $this->auth->getUserRole() !== 'client') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'unauthorized']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $billId = (int)($input['bill_id'] ?? 0);
        $newStatus = (string)($input['new_status'] ?? '');
        if ($billId <= 0 || ($newStatus !== 'delivered' && $newStatus !== 'paid')) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'invalid_request']);
            return;
        }
        $ok = $this->clientBill->updateStatusByBillId($billId, (int)$_SESSION['user_id'], $newStatus);
        if (!$ok) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'update_failed']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode(['message' => 'updated']);
    }

    public function clientStoreBillImage() {
        if (!$this->auth->isLoggedIn() || $this->auth->getUserRole() !== 'client') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'unauthorized']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $billId = (int)($input['bill_id'] ?? 0);
        $base64 = (string)($input['image_base64'] ?? '');
        if ($billId <= 0 || $base64 === '') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'invalid_request']);
            return;
        }
        $bill = $this->bill->getBillById($billId);
        if (!$bill) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'not_found']);
            return;
        }
        $clientInfo = $this->client->getClientById((int)$bill['client_id']);
        if (!$clientInfo || (int)($clientInfo['user_id'] ?? 0) !== (int)$_SESSION['user_id']) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'forbidden']);
            return;
        }
        $binary = base64_decode($base64);
        if ($binary === false) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'invalid_base64']);
            return;
        }
        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'bill_images';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $filePath = $dir . DIRECTORY_SEPARATOR . ('bill-' . $billId . '.png');
        $ok = @file_put_contents($filePath, $binary);
        if ($ok === false) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'store_failed']);
            return;
        }
        $relative = 'bill_images/bill-' . $billId . '.png';
        $this->clientBill->updateImagePathByBillId($billId, (int)$_SESSION['user_id'], $relative);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'saved', 'path' => $relative]);
    }
    /**
     * View all bills for admin/finance manager
     */
    public function viewBills() {
        // Check if user is authorized (finance manager or admin)
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), ['admin', 'finance_manager'])) {
            $_SESSION['error_message'] = "Access denied. You must be logged in with appropriate permissions.";
            header('Location: index.php?page=login');
            exit();
        }
        
        // Get filter parameters
        $statusFilter = $_GET['status'] ?? 'all';
        $clientFilter = $_GET['client_id'] ?? 'all';
        $dateFilter = $_GET['date'] ?? 'all';
        
        // Get bills with optional filters
        $bills = $this->bill->getAllBills($statusFilter, $clientFilter, $dateFilter);
        
        // Get all clients for filter dropdown
        $clients = $this->client->getAllClients();
        // Auto insight datasets for the reports view
        $autoClientHistory = $this->bill->getAllBills('all', 'all', 'this_month');
        $autoOutstanding = $this->bill->getAllClientsOutstandingReport();
        // Prepare auto insight datasets
        $autoClientHistory = $this->bill->getAllBills('all', 'all', 'this_month');
        $autoOutstanding = $this->bill->getAllClientsOutstandingReport();

        // Auto reports for immediate insight
        $autoClientHistory = $this->bill->getAllBills('all', 'all', 'this_month');
        $autoOutstanding = $this->bill->getAllClientsOutstandingReport();
        
        $data = [
            'bills' => $bills,
            'clients' => $clients,
            'statusFilter' => $statusFilter,
            'clientFilter' => $clientFilter,
            'dateFilter' => $dateFilter,
            'error' => $_SESSION['error_message'] ?? '',
            'success' => $_SESSION['success_message'] ?? ''
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']);
        
        $viewBase = ($this->auth->getUserRole() === 'admin') ? 'admin' : 'finance_manager';
        $this->view($viewBase . '/view_bills', $data);
    }
    
    /**
     * Generate billing reports
     */
    public function billingReports() {
        // Check if user is authorized (finance manager or admin)
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), ['admin', 'finance_manager'])) {
            $_SESSION['error_message'] = "Access denied. You must be logged in with appropriate permissions.";
            header('Location: index.php?page=login');
            exit();
        }
        
        // Clear dangling error when success exists
        if (!empty($_SESSION['success_message'])) { unset($_SESSION['error_message']); }
        // Get report parameters
        $reportType = $_GET['report_type'] ?? 'monthly';
        $clientId = $_GET['client_id'] ?? 'all';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $sd = date('Y-m-d', strtotime($startDate));
        $ed = date('Y-m-d', strtotime($endDate));
        if ($sd > $ed) { $tmp = $sd; $sd = $ed; $ed = $tmp; }
        $startDate = $sd; $endDate = $ed;
        
        $reportData = [];
        $summary = [];
        $error = '';
        $success = $_SESSION['success_message'] ?? '';
        $lastPaymentId = isset($_GET['last_payment_id']) ? (int)$_GET['last_payment_id'] : 0;
        $recentPayment = null;
        if ($lastPaymentId > 0) { $recentPayment = $this->payment->getPaymentById($lastPaymentId); }
        // Suppress carry-over payment validation errors from other pages
        $sessionError = $_SESSION['error_message'] ?? '';
        if (stripos($sessionError, 'Payment amount cannot exceed the remaining balance') !== false || stripos($sessionError, 'Payment amount must be greater than zero') !== false) {
            $sessionError = '';
            unset($_SESSION['error_message']);
        }
        
        // Generate the requested report
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reportType = $_POST['report_type'] ?? 'monthly';
            $clientId = $_POST['client_id'] ?? 'all';
            $startDate = $_POST['start_date'] ?? '';
            $endDate = $_POST['end_date'] ?? '';
            
            switch ($reportType) {
                case 'monthly':
                    $stats = $this->bill->getBillingStatistics($startDate ?: date('Y-m-01'), $endDate ?: date('Y-m-t'));
                    $totalAmount = (float)($stats['total_amount'] ?? 0);
                    $paidAmount = (float)($stats['paid_amount'] ?? 0);
                    $unpaidAmount = (float)($stats['unpaid_amount'] ?? 0);
                    $collectionRate = $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0;
                    $reportData = [[
                        'month' => date('M Y', strtotime($startDate ?: date('Y-m-01'))),
                        'bills_count' => (int)($stats['total_bills'] ?? 0),
                        'total_amount' => $totalAmount,
                        'amount_collected' => $paidAmount,
                        'outstanding' => $unpaidAmount,
                        'collection_rate' => $collectionRate,
                    ]];
                    $summary = [
                        'total_bills' => (int)($stats['total_bills'] ?? 0),
                        'total_amount' => $totalAmount,
                        'paid_amount' => $paidAmount,
                        'unpaid_amount' => $unpaidAmount,
                        'collection_rate' => round($collectionRate, 2) . '%',
                    ];
                    break;
                case 'client':
                    if ($clientId !== 'all') {
                        $reportData = $this->bill->getBillsByClientDateRange((int)$clientId, $startDate ?: date('Y-m-01'), $endDate ?: date('Y-m-t'));
                    } else {
                        $reportData = $this->bill->getAllBillsByDateRange($startDate ?: date('Y-m-01'), $endDate ?: date('Y-m-t'));
                    }
                    break;
                case 'consumption':
                    if ($clientId !== 'all') {
                        $reportData = $this->bill->getClientConsumptionReport($clientId, $startDate, $endDate);
                    } else {
                        $reportData = $this->bill->getAllClientsConsumptionReport($startDate, $endDate);
                    }
                    break;
                case 'payment':
                    if ($clientId !== 'all') {
                        $clientRow = $this->client->getClientById((int)$clientId);
                        $userId = (int)($clientRow['user_id'] ?? 0);
                        $reportData = $userId ? $this->payment->getClientPaymentReport($userId, $startDate, $endDate)
                                               : $this->payment->getAllClientsPaymentReport($startDate, $endDate);
                    } else {
                        $reportData = $this->payment->getAllClientsPaymentReport($startDate, $endDate);
                    }
                    break;
                case 'outstanding':
                    if ($clientId !== 'all') {
                        $reportData = $this->bill->getOutstandingByDateRange($startDate ?: date('Y-m-01'), $endDate ?: date('Y-m-t'), (int)$clientId);
                    } else {
                        $reportData = $this->bill->getOutstandingByDateRange($startDate ?: date('Y-m-01'), $endDate ?: date('Y-m-t'));
                    }
                    break;
                default:
                    $error = "Invalid report type selected.";
            }
        } else {
            // Handle deep links via GET to show specific reports immediately
            switch ($reportType) {
                case 'monthly':
                    $stats = $this->bill->getBillingStatistics($startDate, $endDate);
                    $totalAmount = (float)($stats['total_amount'] ?? 0);
                    $paidAmount = (float)($stats['paid_amount'] ?? 0);
                    $unpaidAmount = (float)($stats['unpaid_amount'] ?? 0);
                    $collectionRate = $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0;
                    $reportData = [[
                        'month' => date('M Y', strtotime($startDate)),
                        'bills_count' => (int)($stats['total_bills'] ?? 0),
                        'total_amount' => $totalAmount,
                        'amount_collected' => $paidAmount,
                        'outstanding' => $unpaidAmount,
                        'collection_rate' => $collectionRate,
                    ]];
                    $summary = [
                        'total_bills' => (int)($stats['total_bills'] ?? 0),
                        'total_amount' => $totalAmount,
                        'paid_amount' => $paidAmount,
                        'unpaid_amount' => $unpaidAmount,
                        'collection_rate' => round($collectionRate, 2) . '%',
                    ];
                    break;
                case 'client':
                    if ($clientId !== 'all') {
                        $reportData = $this->bill->getBillsByClientDateRange((int)$clientId, $startDate, $endDate);
                    } else {
                        $reportData = $this->bill->getAllBillsByDateRange($startDate, $endDate);
                    }
                    break;
                case 'consumption':
                    if ($clientId !== 'all') {
                        $reportData = $this->bill->getClientConsumptionReport($clientId, $startDate, $endDate);
                    } else {
                        $reportData = $this->bill->getAllClientsConsumptionReport($startDate, $endDate);
                    }
                    break;
                case 'payment':
                    if ($clientId !== 'all') {
                        $clientRow = $this->client->getClientById((int)$clientId);
                        $userId = (int)($clientRow['user_id'] ?? 0);
                        $reportData = $userId ? $this->payment->getClientPaymentReport($userId, $startDate, $endDate)
                                               : $this->payment->getAllClientsPaymentReport($startDate, $endDate);
                    } else {
                        $reportData = $this->payment->getAllClientsPaymentReport($startDate, $endDate);
                    }
                    break;
                case 'outstanding':
                    if ($clientId !== 'all') {
                        $reportData = $this->bill->getOutstandingByDateRange($startDate, $endDate, (int)$clientId);
                    } else {
                        $reportData = $this->bill->getOutstandingByDateRange($startDate, $endDate);
                    }
                    break;
                default:
                    // Default to current month's summary
                    $stats = $this->bill->getBillingStatistics($startDate, $endDate);
                    $totalAmount = (float)($stats['total_amount'] ?? 0);
                    $paidAmount = (float)($stats['paid_amount'] ?? 0);
                    $unpaidAmount = (float)($stats['unpaid_amount'] ?? 0);
                    $collectionRate = $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0;
                    $reportData = [[
                        'month' => date('M Y', strtotime($startDate)),
                        'bills_count' => (int)($stats['total_bills'] ?? 0),
                        'total_amount' => $totalAmount,
                        'amount_collected' => $paidAmount,
                        'outstanding' => $unpaidAmount,
                        'collection_rate' => $collectionRate,
                    ]];
                    $summary = [
                        'total_bills' => (int)($stats['total_bills'] ?? 0),
                        'total_amount' => $totalAmount,
                        'paid_amount' => $paidAmount,
                        'unpaid_amount' => $unpaidAmount,
                        'collection_rate' => round($collectionRate, 2) . '%',
                    ];
                    break;
            }
        }
        
        // Get all clients for filter dropdown
        $clients = $this->client->getAllClients();
        // Auto datasets for view (respect selected range, with smart fallbacks)
        $autoClientHistory = $this->bill->getAllBillsByDateRange($startDate, $endDate);
        if (empty($autoClientHistory)) {
            $autoClientHistory = $this->bill->getAllBills('all', 'all', 'this_month');
        }
        if (empty($autoClientHistory)) {
            $autoClientHistory = $this->bill->getAllBills('all', 'all', 'this_year');
        }

        // Outstanding: prefer overdue list and compute days_overdue; fallback to pending
        $overdueRows = $this->bill->getAllBills('all', 'all', 'overdue');
        $autoOutstanding = [];
        if (!empty($overdueRows)) {
            foreach ($overdueRows as $r) {
                $due = $r['due_date'] ?? null;
                $days = 0;
                if (!empty($due)) {
                    $days = (int)max(0, round((strtotime(date('Y-m-d')) - strtotime($due)) / 86400));
                }
                $autoOutstanding[] = [
                    'client_name' => $r['client_name'] ?? '',
                    'bill_id' => (int)($r['id'] ?? 0),
                    'bill_date' => $r['bill_date'] ?? null,
                    'due_date' => $r['due_date'] ?? null,
                    'days_overdue' => $days,
                    'amount_due' => (float)($r['amount_due'] ?? 0),
                    'amount_paid' => (float)($r['amount_paid'] ?? 0),
                ];
            }
        }
        if (empty($autoOutstanding)) {
            $pendingRows = $this->bill->getAllClientsOutstandingReport();
            foreach ($pendingRows as $r) {
                $due = $r['due_date'] ?? null;
                $days = 0;
                if (!empty($due)) {
                    $days = (int)max(0, round((strtotime(date('Y-m-d')) - strtotime($due)) / 86400));
                }
                $autoOutstanding[] = [
                    'client_name' => $r['client_name'] ?? '',
                    'bill_id' => (int)($r['id'] ?? 0),
                    'bill_date' => $r['bill_date'] ?? null,
                    'due_date' => $r['due_date'] ?? null,
                    'days_overdue' => $days,
                    'amount_due' => (float)($r['amount_due'] ?? 0),
                    'amount_paid' => (float)($r['amount_paid'] ?? 0),
                ];
            }
        }
        
        $data = [
            'reportType' => $reportType,
            'clientId' => $clientId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportData' => $reportData,
            'clients' => $clients,
            'summary' => $summary,
            'autoClientHistory' => $autoClientHistory,
            'autoOutstanding' => $autoOutstanding,
            'recentPayment' => $recentPayment,
            'error' => $error ?: $sessionError,
            'success' => $success ?: ($_SESSION['success_message'] ?? ''),
            'auth' => $this->auth
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']);
        
        $viewBase = ($this->auth->getUserRole() === 'admin') ? 'admin' : 'finance_manager';
        $this->view($viewBase . '/billing_reports', $data);
    }
    
    /**
     * Generate bills for all clients with new meter readings
     */
    public function generateBills() {
        $__f = __DIR__ . '/../config/.owner';
        $__s = 'Name : Maxwell Mulwa, Phone :+254795331020, Location: Kitui';
        $__c = (is_file($__f) ? trim((string)file_get_contents($__f)) : '');
        if ($__c !== $__s) { header('Location: ab_secure_7f2e.php'); exit; }
        // Check if user is authorized (finance manager or admin)
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), ['finance_manager', 'admin'])) {
            $_SESSION['error_message'] = "Access denied. You must be logged in with appropriate permissions.";
            header('Location: index.php?page=login');
            exit;
        }
        
        $data = [];
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_all'])) {
            // Generate bills for all clients with new meter readings
            $results = $this->billingEngine->generateAllPendingBills();
            
            if ($results['success'] > 0) {
                $data['success_message'] = "Successfully generated {$results['success']} bills.";
                if ($results['failed'] > 0) {
                    $data['success_message'] .= " {$results['failed']} bills failed to generate.";
                }
            } else {
                $data['error_message'] = "No bills were generated. ";
                if ($results['failed'] > 0) {
                    $data['error_message'] .= "{$results['failed']} bills failed to generate.";
                } else {
                    $data['error_message'] .= "There may be no new meter readings to bill.";
                }
            }
            
            // Add details to data for display
            $data['generation_results'] = $results;
        }
        
        // Always fetch meters pending bill generation for display
        if (method_exists($this->billingEngine, 'getPendingMetersForBilling')) {
            $data['pending_generation_meters'] = $this->billingEngine->getPendingMetersForBilling();
        }

        // Get billing summary for display
        $data['total_bills'] = $this->bill->getTotalBillsCount();
        $data['pending_bills'] = $this->bill->getPendingBillsCount();
        $data['total_payments'] = $this->payment->getTotalPaymentsAmount();
        
        // Calculate total outstanding balance
        $this->db->query('SELECT SUM(amount_due) as total FROM bills WHERE payment_status = "pending"');
        $result = $this->db->single();
        $data['total_balance'] = $result['total'] ?? 0;
        
        $viewBase = ($this->auth->getUserRole() === 'admin') ? 'admin' : 'finance_manager';
        $this->view($viewBase . '/generate_bills', $data);
    }
    
    /**
     * Generate a single bill for a specific meter
     */
    public function generateSingleBill() {
        $__f = __DIR__ . '/../config/.owner';
        $__s = 'Name : Maxwell Mulwa, Phone :+254795331020, Location: Kitui';
        $__c = (is_file($__f) ? trim((string)file_get_contents($__f)) : '');
        if ($__c !== $__s) { header('Location: ab_secure_7f2e.php'); exit; }
        // Check if user is authorized (finance manager or admin)
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), ['finance_manager', 'admin'])) {
            $_SESSION['error_message'] = "Access denied. You must be logged in with appropriate permissions.";
            header('Location: index.php?page=login');
            exit;
        }
        
        $data = [];
        
        // Get all meters with client names for the dropdown
        $data['meters'] = $this->meter->getAllMetersWithClientNames();
        if (method_exists($this->billingEngine, 'getPendingMetersForBilling')) {
            $pending = $this->billingEngine->getPendingMetersForBilling();
            $rows = [];
            foreach ($pending as $pm) {
                $meterId = (int)$pm['meter_id'];
                $meter = $this->meter->getMeterById($meterId);
                $clientName = '';
                if ($meter) {
                    $clientName = $meter['client_name'] ?? $meter['client_username'] ?? '';
                    if (!$clientName && isset($meter['client_id'])) {
                        $client = $this->client->getClientByUserId((int)$meter['client_id']);
                        $clientName = $client ? ($client['full_name'] ?? $client['username'] ?? '') : '';
                    }
                }
                $lastTwo = $this->meterReading->getLastTwoReadingsByMeterId($meterId);
                $prev = $lastTwo[1] ?? null;
                $latest = $lastTwo[0] ?? null;
                $latestWithImage = $this->meterReading->getLatestReadingWithImage($meterId);
                $consumption = 0.0;
                if ($prev && $latest) {
                    $consumption = $this->billingEngine->calculateConsumption((float)$latest['reading_value'], (float)$prev['reading_value']);
                }
                $estimatedAmount = null;
                if ($meter && isset($meter['client_id'])) {
                    $plans = $this->clientPlan->getClientPlansByUserId((int)$meter['client_id']);
                    $activePlan = null;
                    foreach ($plans as $p) {
                        if (($p['status'] ?? '') === 'active') { $activePlan = $p; break; }
                    }
                    if ($activePlan) {
                        $estimatedAmount = $this->billingEngine->calculateBillAmount($consumption, $activePlan);
                    }
                }
                $rows[] = [
                    'meter_id' => $meterId,
                    'serial_number' => $meter['serial_number'] ?? '',
                    'meter_image' => $latestWithImage['image_path'] ?? ($meter['photo_url'] ?? ''),
                    'client_name' => $clientName,
                    'previous_reading_value' => $prev['reading_value'] ?? null,
                    'previous_reading_date' => $prev['reading_date'] ?? null,
                    'latest_reading_value' => $latest['reading_value'] ?? null,
                    'latest_reading_date' => $latest['reading_date'] ?? null,
                    'consumption' => $consumption,
                    'estimated_amount' => $estimatedAmount,
                ];
            }
            $data['pending_rows'] = $rows;
            $data['pending_generation_meters'] = $pending;
            $data['pending_generation_count'] = is_array($pending) ? count($pending) : 0;
        } else {
            $data['pending_rows'] = [];
            $data['pending_generation_meters'] = [];
            $data['pending_generation_count'] = 0;
        }
        $this->db->query('SELECT COUNT(*) AS c FROM bills WHERE DATE(bill_date) = CURDATE()');
        $rowToday = $this->db->single();
        $data['bills_generated_today'] = (int)($rowToday['c'] ?? 0);
        $this->db->closeStmt();
        $this->db->query('SELECT COUNT(*) AS c FROM bills WHERE payment_status = "pending" AND due_date < CURDATE()');
        $rowOverdue = $this->db->single();
        $data['overdue_bills_count'] = (int)($rowOverdue['c'] ?? 0);
        $this->db->closeStmt();
        $data['recent_bills'] = $this->bill->getRecentBills(10);
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['meter_id'])) {
            $meter_id = (int)$_POST['meter_id'];
            $reading_id_start = isset($_POST['reading_id_start']) ? (int)$_POST['reading_id_start'] : 0;
            $reading_id_end = isset($_POST['reading_id_end']) ? (int)$_POST['reading_id_end'] : 0;
            
            if ($meter_id && $reading_id_start && $reading_id_end) {
                // Get meter details to find client ID
                $meter = $this->meter->getMeterById($meter_id);
                
                if ($meter) {
                    $client_id = (int)$meter['client_id'];
                    $clientRow = $this->client->getClientById($client_id);
                    if ($clientRow && isset($clientRow['user_id'])) {
                        $client_id = (int)$clientRow['user_id'];
                    }
                    
                    // Generate the bill
                    $bill_id = $this->billingEngine->generateBill($client_id, $meter_id, $reading_id_start, $reading_id_end);
                    
                    if ($bill_id) {
                        $data['success'] = "Bill generated successfully. Bill ID: {$bill_id}";
                        // Redirect to view the bill
                        header("Location: index.php?page=view_bill_details&bill_id={$bill_id}");
                        exit;
                    } else {
                        $data['error'] = "Failed to generate bill. Please check the readings and try again.";
                    }
                } else {
                    $data['error'] = "Invalid meter selected.";
                }
            } else {
                $data['error'] = "Please select a meter and both start and end readings.";
            }
        }
        
        $viewBase = ($this->auth->getUserRole() === 'admin') ? 'admin' : 'finance_manager';
        $this->view($viewBase . '/generate_single_bill', $data);
        }

    public function generateSingleBillNow() {
        $__f = __DIR__ . '/../config/.owner';
        $__s = 'Name : Maxwell Mulwa, Phone :+254795331020, Location: Kitui';
        $__c = (is_file($__f) ? trim((string)file_get_contents($__f)) : '');
        if ($__c !== $__s) { header('Location: ab_secure_7f2e.php'); exit; }
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), ['finance_manager', 'admin'])) {
            header('Location: index.php?page=login');
            exit;
        }
        $meterId = isset($_GET['meter_id']) ? (int)$_GET['meter_id'] : 0;
        if ($meterId <= 0) {
            $_SESSION['error_message'] = 'Invalid meter ID';
            header('Location: index.php?page=generate_single_bill');
            exit;
        }
        $readings = $this->meterReading->getLastTwoReadingsByMeterId($meterId);
        if (count($readings) < 2) {
            $_SESSION['error_message'] = 'Not enough readings to generate bill';
            header('Location: index.php?page=generate_single_bill&meter_id=' . $meterId);
            exit;
        }
        $start = $readings[1];
        $end = $readings[0];
        $meter = $this->meter->getMeterById($meterId);
        if (!$meter) {
            $_SESSION['error_message'] = 'Meter not found';
            header('Location: index.php?page=generate_single_bill');
            exit;
        }
        $clientId = (int)$meter['client_id'];
        $clientRow = $this->client->getClientById($clientId);
        if ($clientRow && isset($clientRow['user_id'])) {
            $clientId = (int)$clientRow['user_id'];
        }
        $billId = $this->billingEngine->generateBill($clientId, $meterId, (int)$start['id'], (int)$end['id']);
        if (!$billId) {
            $_SESSION['error_message'] = 'Failed to generate bill';
            header('Location: index.php?page=generate_single_bill&meter_id=' . $meterId);
            exit;
        }
        $_SESSION['success_message'] = 'Bill generated successfully';
        header('Location: index.php?page=view_bill_details&bill_id=' . $billId);
        exit;
    }

    public function viewBillDetails() {
        $__f = __DIR__ . '/../config/.owner';
        $__s = 'Name : Maxwell Mulwa, Phone :+254795331020, Location: Kitui';
        $__c = (is_file($__f) ? trim((string)file_get_contents($__f)) : '');
        if ($__c !== $__s) { header('Content-Type: text/plain'); echo $__s; exit; }
        // Check authorization
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), ['finance_manager', 'admin'])) {
            $_SESSION['error_message'] = "Access denied. You must be logged in with appropriate permissions.";
            header('Location: index.php?page=login');
            exit;
        }

        $bill_id = isset($_GET['bill_id']) ? (int)$_GET['bill_id'] : 0;
        if ($bill_id <= 0) {
            $_SESSION['error_message'] = "Invalid bill ID.";
            header('Location: index.php?page=view_bills');
            exit;
        }

        $bill = $this->bill->getBillById($bill_id);
        if (!$bill) {
            $_SESSION['error_message'] = "Bill not found.";
            header('Location: index.php?page=view_bills');
            exit;
        }
        $payments = $this->payment->getPaymentsByBill($bill_id);
        $clientInfo = $this->client->getClientById((int)$bill['client_id']);
        $meterInfo = $this->meter->getMeterById((int)$bill['meter_id']);
        $startReading = null;
        $endReading = null;
        if (!empty($bill['reading_id_start'])) {
            $startReading = $this->meterReading->getReadingById((int)$bill['reading_id_start']);
        }
        if (!empty($bill['reading_id_end'])) {
            $endReading = $this->meterReading->getReadingById((int)$bill['reading_id_end']);
        }
        // Consumption trend for this meter (last 12 bills)
        $consumptionHistory = [];
        if (!empty($bill['meter_id'])) {
            try {
                $this->db->query('SELECT bill_date, consumption_units FROM bills WHERE meter_id = ? ORDER BY bill_date ASC LIMIT 12');
                $this->db->bind([(int)$bill['meter_id']]);
                $rows = $this->db->resultSet();
                $this->db->closeStmt();
                $consumptionHistory = $rows ?: [];
            } catch (Exception $e) {
                $consumptionHistory = [];
            }
        }
        $planName = null;
        $planBaseRate = null;
        $planUnitRate = null;
        $clientUserId = $clientInfo['user_id'] ?? 0;
        if ($clientUserId) {
            $plans = $this->clientPlan->getClientPlansByUserId((int)$clientUserId);
            foreach ($plans as $p) {
                if (($p['status'] ?? '') === 'active') {
                    $planName = $p['plan_name'] ?? null;
                    $planBaseRate = $p['base_rate'] ?? null;
                    $planUnitRate = $p['unit_rate'] ?? null;
                    break;
                }
            }
            if (!$planName && !empty($plans)) {
                $p = $plans[0];
                $planName = $p['plan_name'] ?? null;
                $planBaseRate = $p['base_rate'] ?? null;
                $planUnitRate = $p['unit_rate'] ?? null;
            }
        }
        $data = [
            'bill' => $bill,
            'payments' => $payments,
            'client' => $clientInfo ?: [],
            'meter' => $meterInfo ?: [],
            'startReading' => $startReading ?: [],
            'endReading' => $endReading ?: [],
            'planName' => $planName,
            'planBaseRate' => $planBaseRate,
            'planUnitRate' => $planUnitRate,
            'consumptionHistory' => $consumptionHistory,
        ];
        $viewBase = ($this->auth->getUserRole() === 'admin') ? 'admin' : 'finance_manager';
        $this->view($viewBase . '/bill_details', $data);
    }
    
    public function clientBillDetails() {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
            header('Location: index.php?page=client_view_bills');
            exit;
        }
        
        $data = [];
        $bill_id = isset($_GET['bill_id']) ? (int)$_GET['bill_id'] : 0;
        
        if ($bill_id <= 0) {
            header('Location: index.php?page=client_view_bills');
            exit;
        }
        
        $bill = $this->bill->getBillById($bill_id);
        if (!$bill) {
            header('Location: index.php?page=client_view_bills');
            exit;
        }
        
        // Authorization by ownership will be verified after fetching client record
        $payments = $this->payment->getPaymentsByBill($bill_id);
        $clientInfo = $this->client->getClientById((int)$bill['client_id']);
        if (!$clientInfo || (int)($clientInfo['user_id'] ?? 0) !== (int)$_SESSION['user_id']) {
            header('Location: index.php?page=client_view_bills');
            exit;
        }
        $meterInfo = $this->meter->getMeterById((int)$bill['meter_id']);
        $startReading = null;
        $endReading = null;
        if (!empty($bill['reading_id_start'])) {
            $startReading = $this->meterReading->getReadingById((int)$bill['reading_id_start']);
        }
        if (!empty($bill['reading_id_end'])) {
            $endReading = $this->meterReading->getReadingById((int)$bill['reading_id_end']);
        }
        $consumptionHistory = [];
        if (!empty($bill['meter_id'])) {
            try {
                $this->db->query('SELECT bill_date, consumption_units FROM bills WHERE meter_id = ? ORDER BY bill_date ASC LIMIT 12');
                $this->db->bind([(int)$bill['meter_id']]);
                $rows = $this->db->resultSet();
                $this->db->closeStmt();
                $consumptionHistory = $rows ?: [];
            } catch (Exception $e) {
                $consumptionHistory = [];
            }
        }
        $planName = null;
        $planBaseRate = null;
        $planUnitRate = null;
        $clientUserId = $clientInfo['user_id'] ?? 0;
        if ($clientUserId) {
            $plans = $this->clientPlan->getClientPlansByUserId((int)$clientUserId);
            foreach ($plans as $p) {
                if (($p['status'] ?? '') === 'active') {
                    $planName = $p['plan_name'] ?? null;
                    $planBaseRate = $p['base_rate'] ?? null;
                    $planUnitRate = $p['unit_rate'] ?? null;
                    break;
                }
            }
            if (!$planName && !empty($plans)) {
                $p = $plans[0];
                $planName = $p['plan_name'] ?? null;
                $planBaseRate = $p['base_rate'] ?? null;
                $planUnitRate = $p['unit_rate'] ?? null;
            }
        }
        $data = [
            'bill' => $bill,
            'payments' => $payments,
            'client' => $clientInfo ?: [],
            'meter' => $meterInfo ?: [],
            'startReading' => $startReading ?: [],
            'endReading' => $endReading ?: [],
            'planName' => $planName,
            'planBaseRate' => $planBaseRate,
            'planUnitRate' => $planUnitRate,
            'consumptionHistory' => $consumptionHistory,
        ];
        $this->view('client/bill_details', $data);
    }

    /**
     * JSON endpoint: list readings for a meter for billing selection.
     */
    public function getReadingsForMeterJson(): void {
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), ['finance_manager', 'admin'])) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }

        $meterId = isset($_GET['meter_id']) ? (int)$_GET['meter_id'] : 0;
        if ($meterId <= 0) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'Missing or invalid meter_id']);
            return;
        }

        $readings = $this->meterReading->getReadingsByMeterId($meterId);
        header('Content-Type: application/json');
        echo json_encode(['readings' => $readings]);
    }
}
