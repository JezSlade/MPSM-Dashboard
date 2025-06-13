<?php
// api/get_customers.php — Uniform Customer Request Proxy

header('Content-Type: application/json');

// === Load .env
$envFile = __DIR__ . '/../.env';
$env = [];
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0 || !strpos($line, '=')) continue;
        list($key, $val) = explode('=', $line, 2);
        $env[trim($key)] = trim($val);
    }
}

// === Get token from Authorization header
$headers = getallheaders();
$tokenHeader = $headers['Authorization'] ?? '';
if (!str_starts_with($tokenHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['error' => 'Missing or invalid Authorization header']);
    exit;
}
$token = trim(substr($tokenHeader, 7));

// === Prepare request
$request = [
    'Url' => 'Customer/GetCustomers',
    'Method' => 'POST',
    'Request' => [
        'DealerCode' => $env['DEALER_CODE'] ?? '',
        'Code' => null,
        'HasHpSds' => null,
        'FilterText' => null,
        'PageNumber' => 1,
        'PageRows' => 2147483647,
        'SortColumn' => 'Id',
        'SortOrder' => 0
    ]
];

// === Curl POST to external API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $env['BASE_URL'] . '/Customer/GetCustomers');
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
