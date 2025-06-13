<?php
// api/get_customer_list.php — Retrieves customers from MPSM API with full validation and debug

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

// === Wrapped payload from SDK
$wrappedPayload = [
    'Url' => 'Customer/GetCustomers',
    'Request' => [
        'DealerCode' => $dealerCode,
        'Code' => null,
        'HasHpSds' => null,
        'FilterText' => null,
        'PageNumber' => 1,
        'PageRows' => 2147483647,
        'SortColumn' => 'Id',
        'SortOrder' => 0
    ],
    'Method' => 'POST'
];

try {
    $token = get_fresh_token($env);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

// === Curl request to MPSM
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

// === Raw response logging (for backend debug only)
error_log("MPSM /Customer/GetCustomers response: $response");

if ($httpCode !== 200 || !$response || strpos($contentType, 'application/json') === false) {
    http_response_code($httpCode);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unexpected response from MPSM API.',
        'data' => [
            'httpCode' => $httpCode,
            'raw' => $response
        ]
    ]);
    exit;
}

// === Decode and validate payload
$data = json_decode($response, true);
if (!$data || !isset($data['Result'])) {
    http_response_code(502);
    echo json_encode([
        'status' => 'error',
        'message' => 'MPSM returned invalid structure.',
        'data' => ['raw' => $response]
    ]);
    exit;
}

if (!is_array($data['Result'])) {
    http_response_code(502);
    echo json_encode([
        'status' => 'error',
        'message' => 'Expected Result to be an array.',
        'data' => ['result' => $data['Result']]
    ]);
    exit;
}

// === Success
echo json_encode([
    'status' => 'success',
    'message' => '',
    'data' => [
        'customers' => $data['Result'],
        'total' => count($data['Result']),
        'limit' => count($data['Result']),
        'offset' => 0
    ]
]);
