<?php
namespace app\models;

class MpesaAPI {
    private $db;
    private $consumerKey;
    private $consumerSecret;
    private $baseUrl;
    private $callbackUrl;
    private $passKey;
    private $shortCode;
    private $environment;
    
    /**
     * Constructor initializes the M-Pesa API with credentials
     * 
     * @param string $environment 'sandbox' or 'production'
     */
    public function __construct(string $environment = 'sandbox') {
        $this->db = new \app\core\Database();
        $this->environment = $environment;
        
        if ($environment === 'sandbox') {
            // Sandbox credentials for testing
            $this->consumerKey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
            $this->consumerSecret = '83b2ec5a9d5a3be6c11d4c686f58343e99a3c3d0b7f3226c6f6e32fed6192c82';
            $this->baseUrl = 'https://sandbox.safaricom.co.ke';
            $this->passKey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
            $this->shortCode = '174379'; // Standard sandbox shortcode
        } else {
            // Production credentials - these would be provided by Safaricom after going live
            // These should be stored in environment variables or a secure configuration
            $this->consumerKey = getenv('MPESA_CONSUMER_KEY') ?: 'YOUR_PRODUCTION_CONSUMER_KEY';
            $this->consumerSecret = getenv('MPESA_CONSUMER_SECRET') ?: 'YOUR_PRODUCTION_CONSUMER_SECRET';
            $this->baseUrl = 'https://api.safaricom.co.ke';
            $this->passKey = getenv('MPESA_PASS_KEY') ?: 'YOUR_PRODUCTION_PASSKEY';
            $this->shortCode = getenv('MPESA_SHORTCODE') ?: 'YOUR_PRODUCTION_SHORTCODE';
        }
        
        // Set the callback URL for STK Push responses
        // In production, this should be a publicly accessible URL
        $baseUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $baseUrl .= $_SERVER['HTTP_HOST'] ?? 'localhost';
        $this->callbackUrl = $baseUrl . '/index.php?page=mpesa_callback';
    }
    
    /**
     * Get OAuth access token from M-Pesa
     * 
     * @return array Response with access token or error
     */
    public function getAccessToken() {
        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
        $url = $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials';
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($httpCode == 200 && !$error) {
            $result = json_decode($response, true);
            return [
                'success' => true,
                'token' => $result['access_token'] ?? null,
                'expires_in' => $result['expires_in'] ?? null
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to get access token: ' . ($error ?: $response)
        ];
    }
    
    /**
     * Initiate STK Push transaction
     * 
     * @param string $phoneNumber Customer phone number (format: 254XXXXXXXXX)
     * @param float $amount Amount to be charged
     * @param string $accountReference Your reference ID (e.g., bill number)
     * @param string $description Transaction description
     * @param int $billId Optional bill ID for reference
     * @return array Response with transaction details or error
     */
    public function initiateSTKPush($phoneNumber, $amount, $accountReference, $description, $billId = null) {
        // Format phone number (remove leading 0 or +)
        $phoneNumber = preg_replace('/^\+?0?/', '', $phoneNumber);
        if (!preg_match('/^254\d{9}$/', $phoneNumber)) {
            return [
                'success' => false,
                'message' => 'Invalid phone number format. Use format: 254XXXXXXXXX'
            ];
        }
        
        // Get access token
        $tokenResponse = $this->getAccessToken();
        if (!$tokenResponse['success']) {
            return $tokenResponse;
        }
        
        // Prepare STK Push request
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortCode . $this->passKey . $timestamp);
        
        $url = $this->baseUrl . '/mpesa/stkpush/v1/processrequest';
        $data = [
            'BusinessShortCode' => $this->shortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => round($amount),
            'PartyA' => $phoneNumber,
            'PartyB' => $this->shortCode,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => $this->callbackUrl,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $description
        ];
        
        // Log the request in the database
        $requestId = $this->logMpesaRequest(
            'stk_push', 
            $phoneNumber, 
            $amount, 
            $accountReference, 
            json_encode($data),
            $billId
        );
        
        if (!$requestId) {
            return [
                'success' => false,
                'message' => 'Failed to log M-Pesa request'
            ];
        }
        
        // Make API request
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $tokenResponse['token'],
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        // Update the request with the response
        $responseData = json_decode($response, true);
        $this->updateMpesaRequest(
            $requestId, 
            $responseData['CheckoutRequestID'] ?? null,
            $response, 
            ($httpCode >= 200 && $httpCode < 300 && isset($responseData['ResponseCode']) && $responseData['ResponseCode'] == '0') ? 'pending' : 'failed'
        );
        
        if ($httpCode >= 200 && $httpCode < 300 && !$error) {
            if (isset($responseData['ResponseCode']) && $responseData['ResponseCode'] == '0') {
                return [
                    'success' => true,
                    'message' => 'STK push initiated successfully',
                    'checkout_request_id' => $responseData['CheckoutRequestID'] ?? null,
                    'request_id' => $requestId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $responseData['errorMessage'] ?? 'STK push failed',
                    'response' => $responseData
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'API request failed: ' . ($error ?: $response)
        ];
    }
    
    /**
     * Process STK Push callback
     * 
     * @param array $callbackData Callback data from M-Pesa
     * @return array Processing result
     */
    public function processSTKPushCallback($callbackData) {
        if (!isset($callbackData['Body']['stkCallback'])) {
            return [
                'success' => false,
                'message' => 'Invalid callback data'
            ];
        }
        
        $stkCallback = $callbackData['Body']['stkCallback'];
        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
        $resultCode = $stkCallback['ResultCode'] ?? null;
        $resultDesc = $stkCallback['ResultDesc'] ?? null;
        
        // Find the request in the database
        $this->db->query('SELECT * FROM mpesa_requests WHERE checkout_request_id = ? LIMIT 1');
        $this->db->bind([$checkoutRequestId]);
        $request = $this->db->single();
        
        if (!$request) {
            return [
                'success' => false,
                'message' => 'Request not found for checkout ID: ' . $checkoutRequestId
            ];
        }
        
        // Update the request status
        $status = ($resultCode == 0) ? 'completed' : 'failed';
        $this->db->query('UPDATE mpesa_requests SET status = ?, callback_data = ?, updated_at = NOW() WHERE id = ?');
        $this->db->bind([$status, json_encode($callbackData), $request['id']]);
        $this->db->execute();
        
        // If successful, process the payment
        if ($resultCode == 0 && isset($stkCallback['CallbackMetadata']['Item'])) {
            $metadata = [];
            foreach ($stkCallback['CallbackMetadata']['Item'] as $item) {
                $metadata[$item['Name']] = $item['Value'] ?? null;
            }
            
            $mpesaReceiptNumber = $metadata['MpesaReceiptNumber'] ?? null;
            $transactionDate = $metadata['TransactionDate'] ?? null;
            $amount = $metadata['Amount'] ?? 0;
            $phoneNumber = $metadata['PhoneNumber'] ?? null;
            
            // Record the payment if there's a bill ID
            if (!empty($request['bill_id'])) {
                $payment = new Payment();
                $result = $payment->recordPayment(
                    $request['bill_id'], // Using bill_id as reference_id
                    'bill',
                    $request['bill_id'],
                    $amount,
                    'mpesa',
                    $mpesaReceiptNumber,
                    'completed'
                );
                
                if ($result) {
                    // Send SMS notification
                    try {
                        $sms = new SMS();
                        $sms->sendPaymentConfirmationSMS($request['bill_id'], $amount, $mpesaReceiptNumber);
                    } catch (\Exception $e) {
                        error_log("Failed to send payment confirmation SMS: " . $e->getMessage());
                    }
                }
                
                return [
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'payment_recorded' => $result,
                    'mpesa_receipt' => $mpesaReceiptNumber,
                    'amount' => $amount
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Callback processed successfully',
                'mpesa_receipt' => $mpesaReceiptNumber,
                'amount' => $amount
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Transaction failed: ' . $resultDesc
        ];
    }
    
    /**
     * Query STK Push transaction status
     * 
     * @param string $checkoutRequestId Checkout request ID from STK push
     * @return array Transaction status or error
     */
    public function querySTKPushStatus($checkoutRequestId) {
        // Get access token
        $tokenResponse = $this->getAccessToken();
        if (!$tokenResponse['success']) {
            return $tokenResponse;
        }
        
        // Prepare query request
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortCode . $this->passKey . $timestamp);
        
        $url = $this->baseUrl . '/mpesa/stkpushquery/v1/query';
        $data = [
            'BusinessShortCode' => $this->shortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId
        ];
        
        // Make API request
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $tokenResponse['token'],
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($httpCode >= 200 && $httpCode < 300 && !$error) {
            $responseData = json_decode($response, true);
            
            // Update the request in the database
            $this->db->query('UPDATE mpesa_requests SET query_response = ?, updated_at = NOW() WHERE checkout_request_id = ?');
            $this->db->bind([json_encode($responseData), $checkoutRequestId]);
            $this->db->execute();
            
            if (isset($responseData['ResultCode']) && $responseData['ResultCode'] == '0') {
                return [
                    'success' => true,
                    'message' => 'Transaction is being processed',
                    'response' => $responseData
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $responseData['ResultDesc'] ?? 'Query failed',
                    'response' => $responseData
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'API request failed: ' . ($error ?: $response)
        ];
    }
    
    /**
     * Log M-Pesa request in the database
     * 
     * @param string $type Request type (e.g., stk_push)
     * @param string $phoneNumber Customer phone number
     * @param float $amount Transaction amount
     * @param string $accountReference Account reference
     * @param string $requestData JSON-encoded request data
     * @param int $billId Optional bill ID for reference
     * @return int|bool ID of the created log entry or false on failure
     */
    private function logMpesaRequest($type, $phoneNumber, $amount, $accountReference, $requestData, $billId = null) {
        $this->db->query('INSERT INTO mpesa_requests (type, phone_number, amount, account_reference, request_data, bill_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
        $this->db->bind([$type, $phoneNumber, $amount, $accountReference, $requestData, $billId, 'initiated']);
        $success = $this->db->execute();
        
        if ($success) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update M-Pesa request in the database
     * 
     * @param int $requestId Request ID
     * @param string $checkoutRequestId Checkout request ID from M-Pesa
     * @param string $responseData JSON-encoded response data
     * @param string $status Request status
     * @return bool True if updated successfully, false otherwise
     */
    private function updateMpesaRequest($requestId, $checkoutRequestId, $responseData, $status) {
        $this->db->query('UPDATE mpesa_requests SET checkout_request_id = ?, response_data = ?, status = ?, updated_at = NOW() WHERE id = ?');
        $this->db->bind([$checkoutRequestId, $responseData, $status, $requestId]);
        return $this->db->execute();
    }
}