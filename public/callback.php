<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Payment.php';
$db = new Database($conn);
$payment = new Payment($db);
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!$payload) { echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']); exit; }
if (isset($payload['Body']['stkCallback'])) {
    $stk = $payload['Body']['stkCallback'];
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
    $payment->processMpesaCallback($flat);
} else {
    $payment->processMpesaCallback($payload);
}
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
exit;
