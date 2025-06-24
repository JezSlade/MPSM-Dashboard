<?php
// api/get_customers.php

declare(strict_types=1);

// 1) Load .env into constants
require_once __DIR__ . '/../includes/env_parser.php';

// 2) Authentication helper: fetch or renew bearer token
require_once __DIR__ . '/../includes/auth.php';

// 3) CORS headers
require_once __DIR__ . '/../includes/cors.php';
send_cors_headers();

// 4) Logger
require_once __DIR__ . '/../includes/logger.php';
log_request();

// 5) Read & validate inputs
$pageNumber = isset($_GET['PageNumber']) && is_numeric($_GET['PageNumber'])
    ? (int) $_GET['PageNumber']
    : 1;
$pageRows = isset($_GET['PageRows']) && is_numeric($_GET['PageRows'])
    ? (int) $_GET['PageRows']
    : 15;

// 6) Build payload
$body = json_encode([
    'DealerCode'  => DEALER_CODE,
    'PageNumber'  => $pageNumber,
    'PageRows'    => $pageRows,
]);

// 7) Make the downstream call
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, API_BASE_URL . 'Customer/GetCustomers');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . get_bearer_token(),
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

$response = curl_exec($ch);
$curlErr  = curl_error($ch);
$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 8) Handle errors
if ($curlErr) {
    http_response_code(502);
    echo json_encode(['error' => 'Upstream request failed', 'details' => $curlErr]);
    exit;
}

http_response_code($status);
echo $response;
