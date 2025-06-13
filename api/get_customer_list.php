<?php
// api/get_customer_list.php — Securely fetch customers via MPSM API

header('Content-Type: application/json');
require_once __DIR__ . '/../sanitize_env.php';

$env = loadEnv(__DIR__ . '/../.env');

// === Determine API base URL from either env variable
$baseUrl = $env['BASE_URL'] ?? $env['MPSM_API_BASE_URL'] ?? '';
if (empty($baseUrl) || empty($env['DEALER_CODE'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Missing BASE_URL or DEALER_CODE in .env']);
    exit;
}

// === Robust Authorization header support (Apache/NGINX/FPM)
$headers = getallheaders() ?: [];
$tokenHeader = $headers['Authorization'] ??
               $headers['authorization'] ??
               $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if (!str_starts_with($tokenHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['error' => 'Missing or invalid Authorization header']);
    exit;
}
$token = trim(substr($tokenHeader, 7));

// === Construct flat request payload
$request = [
    'pageNumber' => 1,
    'pageRows' => 100,
    'dealerCode' => $env['DEALER_CODE']
];

// === Debug log for validation (optional)
error_log("Flat payload to /Customer/List: " . json_encode($request));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/Customer/List');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$token}",
    "Content-Type: application/json",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => curl_error($ch)]);
} elseif (strpos($contentType, 'application/json') === false) {
    http_response_code($httpCode);
    echo json_encode(['error' => 'Unexpected content type', 'body' => $response]);
} else {
    http_response_code($httpCode);
    echo $response;
}
curl_close($ch);
