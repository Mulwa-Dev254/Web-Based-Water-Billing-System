<?php
header('Content-Type: application/json');
http_response_code(200);
$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$seed = $auth ?: (string)microtime(true);
$token = substr(str_replace(['+', '/', '='], '', base64_encode(hash('sha256', $seed, true))), 0, 32);
echo json_encode([
    'access_token' => $token,
    'expires_in' => 3599,
]);
