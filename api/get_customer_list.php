<?php
// api/get_customer_list.php — Fetches customers using correct payload structure

header('Content-Type: application/json');

require_once __DIR__ . '/../sanitize_env.php';
require_once __DIR__ . '/lib/token_helper.php';

$env = loadEnv(__DIR__ . '/../.env');

$baseUrl = $env['BASE_URL'] ?? $env['MPSM_API_BASE_URL'] ?? '';
$dealerCode = $env['DEALER_CODE'] ?? '';

if (!$baseUrl || !$dealerCode) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Missing BASE_URL or DEALER_CODE in .env']);
    exit;
}

// === Construct wrapped payload as you specified
$wrappedPayload = [
    'Url' => 'Customer/GetCustomers',
    'Method' => 'POST',
    'Request' => [
        'DealerCode' => $dealerCode,
        'Code' => null,
        'HasHpSds' => null,
        'FilterText' => null,
        'PageNumber' => 1,
        'PageRows' => 2147483647,
        'SortColumn' => 'Id',
        'SortOrder' => 0
    ]
];

try {
    $token = get_fresh_token($env);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/Customer/GetCustomers');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$token}",
    "Content-Type: application/json",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($wrappedPayload));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($httpCode !== 200 || !$response || strpos($contentType, 'application/json') === false) {
    http_response_code($httpCode);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch customer data.',
        'data' => null
    ]);
    exit;
}

$data = json_decode($response, true);
$results = $data['Result'] ?? [];

echo json_encode([
    'status' => 'success',
    'message' => '',
    'data' => [
        'customers' => $results,
        'total' => count($results),
        'limit' => count($results),
        'offset' => 0
    ]
]);
