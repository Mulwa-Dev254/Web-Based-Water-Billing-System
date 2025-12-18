<?php
header('Content-Type: application/json');

// Read JSON input
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// Simple validation
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'ResponseCode' => '1',
        'ResponseDescription' => 'Invalid JSON payload',
        'error' => 'Bad Request'
    ]);
    exit;
}

$amount = isset($data['Amount']) ? (int)$data['Amount'] : 0;
$phone = isset($data['PhoneNumber']) ? (string)$data['PhoneNumber'] : '';
$callback = isset($data['CallBackURL']) ? (string)$data['CallBackURL'] : '';

if ($amount < 1) {
    http_response_code(400);
    echo json_encode([
        'ResponseCode' => '1',
        'ResponseDescription' => 'Invalid Amount',
        'error' => 'Amount must be >= 1'
    ]);
    exit;
}

if (empty($phone)) {
    http_response_code(400);
    echo json_encode([
        'ResponseCode' => '1',
        'ResponseDescription' => 'Invalid PhoneNumber',
        'error' => 'PhoneNumber required'
    ]);
    exit;
}

// Simulate successful STK push initiation
$merchantId = 'MER_' . substr(sha1((string)microtime(true)), 0, 12);
$checkoutId = 'ws_CO_' . substr(md5(uniqid('', true)), 0, 16);

http_response_code(200);
echo json_encode([
    'MerchantRequestID' => $merchantId,
    'CheckoutRequestID' => $checkoutId,
    'ResponseCode' => '0',
    'ResponseDescription' => 'Success. Request accepted for processing',
    'CustomerMessage' => 'Success. Please complete payment on your phone'
]);

// Optional: You could simulate a callback by making an HTTP POST to $callback.
// For safety, we skip auto-callback here. You can trigger a manual callback from a separate script if needed.
