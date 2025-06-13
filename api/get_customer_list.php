<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../sanitize_env.php';
$env = loadEnv(__DIR__ . '/../.env');
$debug = strtolower($env['DEBUG_MODE'] ?? '') === 'true';

// === Log everything always if DEBUG_MODE is on
function log_debug($label, $data) {
    if (!is_string($data)) $data = json_encode($data);
    error_log("DEBUG [$label]: $data");
}

// === Get Authorization header robustly
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ??
              $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ??
              $_SERVER['Authorization'] ??
              getallheaders()['Authorization'] ?? '';

log_debug('Authorization Header', $authHeader);
log_debug('Session', $_SESSION);

// === Validate session auth
if (!str_starts_with($authHeader, 'Session ') || empty($_SESSION['auth_token'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing or invalid session authorization.',
        'data' => $debug ? [
            'authHeader' => $authHeader,
            'session' => $_SESSION
        ] : null
    ]);
    exit;
}

// === Extract values from env
$baseUrl = $env['BASE_URL'] ?? $env['MPSM_API_BASE_URL'] ?? '';
$dealerCode = $env['DEALER_CODE'] ?? '';
if (!$baseUrl || !$dealerCode) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Missing BASE_URL or DEALER_CODE.', 'data' => null]);
    exit;
}

// === Pagination
$limit = min(intval($_GET['limit'] ?? 10), 100);
$offset = max(intval($_GET['offset'] ?? 0), 0);
$pageNumber = floor($offset / $limit) + 1;

// === Payload
$payload = [
    'pageNumber' => $pageNumber,
    'pageRows' => $limit,
    'dealerCode' => $dealerCode
];
log_debug('Payload', $payload);

// === Curl request to MPSM API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/Customer/List');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $_SESSION['auth_token'],
    "Content-Type: application/json",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

log_debug('HTTP Code', $httpCode);
log_debug('API Response', $response);

// === Response validation
if ($httpCode !== 200 || !$response || strpos($contentType, 'application/json') === false) {
    http_response_code($httpCode);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch customer data.',
        'data' => $debug ? [
            'http' => $httpCode,
            'raw' => $response
        ] : null
    ]);
    exit;
}

$data = json_decode($response, true);
$results = $data['result'] ?? [];

echo json_encode([
    'status' => 'success',
    'message' => '',
    'data' => [
        'customers' => $results,
        'total' => count($results),
        'limit' => $limit,
        'offset' => $offset
    ]
]);
