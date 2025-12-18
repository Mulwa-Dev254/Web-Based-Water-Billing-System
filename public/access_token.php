<?php
header('Content-Type: application/json');

// Simulation mode for local testing (no external request)
$simulate = (isset($_GET['simulate']) && $_GET['simulate'] === '1') || (getenv('MPESA_TOKEN_SIMULATE') === '1');
if ($simulate) {
    $seed = (string)microtime(true);
    $token = substr(str_replace(['+', '/', '='], '', base64_encode(hash('sha256', $seed, true))), 0, 32);
    echo json_encode(['access_token' => $token, 'expires_in' => 3599]);
    exit;
}

$consumerKey = isset($_GET['ck']) ? trim($_GET['ck']) : (getenv('MPESA_SANDBOX_CONSUMER_KEY') ?: 'JLZ20U19fXbm8D3c78rQntPYKqFLtQi72hEQraEs9ZFxqSlT');
$consumerSecret = isset($_GET['cs']) ? trim($_GET['cs']) : (getenv('MPESA_SANDBOX_CONSUMER_SECRET') ?: 'u4TL4iwZ5OE8xMilhbZST8zdqEVAYWHUeoruL0JeagA0s3cQKzPRrwKYx7pfntsG');
$url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
$credentials = base64_encode($consumerKey . ':' . $consumerSecret);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic {$credentials}"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
$err = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($err) { echo json_encode(['error' => 'cURL Error: ' . $err]); exit; }
$result = json_decode($response, true);
if ($code === 200 && isset($result['access_token'])) {
    echo json_encode(['access_token' => $result['access_token'], 'expires_in' => $result['expires_in'] ?? 3599]);
} else {
    echo json_encode(['error' => 'Failed to get access token', 'http_code' => $code, 'response' => $result]);
}
