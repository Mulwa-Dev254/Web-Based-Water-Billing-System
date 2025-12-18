<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(403); echo 'unauthorized'; exit; }
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
$reason = isset($_POST['reason']) ? (string)$_POST['reason'] : '';
$phone = isset($_POST['phone']) ? (string)$_POST['phone'] : '';
$billId = isset($_POST['bill_id']) ? (int)$_POST['bill_id'] : 0;
if ($amount <= 0 || $reason === '' || !preg_match('/^(?:2547\d{8}|07\d{8})$/', $phone)) { http_response_code(400); echo 'invalid_request'; exit; }
$phoneFormatted = preg_match('/^2547\d{8}$/', $phone) ? $phone : ('254' . ltrim($phone, '0'));
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Payment.php';
require_once __DIR__ . '/../app/models/Bill.php';
$db = new Database($conn);
$paymentModel = new Payment($db);
$billModel = new Bill($db);
$userId = (int)$_SESSION['user_id'];
$type = $billId > 0 ? 'bill_payment' : 'service_payment';
$refId = $billId > 0 ? $billId : 0;
$ok = $paymentModel->recordPayment($userId, $type, $refId, $amount, 'Mpesa STK-Push', null, 'pending');
if (!$ok) { $_SESSION['error'] = 'Failed to record payment entry'; header('Location: index.php?page=client_payments'); exit; }
$resp = $paymentModel->initiateMpesaPayment([
    'phone' => $phoneFormatted,
    'amount' => $amount,
    'reference' => ($billId>0?('Bill#'.$billId):$reason),
    'description' => 'Water Billing Payment'
]);
if (($resp['success'] ?? false) === true) {
    $paymentModel->storeMpesaRequest([
        'checkout_request_id' => $resp['checkout_request_id'] ?? '',
        'bill_id' => $billId > 0 ? $billId : null,
        'amount' => $amount,
        'phone' => $phoneFormatted,
        'status' => 'pending'
    ]);
    if ($billId > 0) {
        $bill = $billModel->getBillById($billId);
        if ($bill) { $billModel->updateBillPayment($billId, (float)($bill['amount_paid'] ?? 0), 'pending'); }
    }
    $_SESSION['success'] = 'STK Push initiated successfully';
} else {
    $_SESSION['error'] = 'M-Pesa payment initiation failed: ' . ($resp['message'] ?? 'unknown error');
}
header('Location: index.php?page=client_payments');
exit;
