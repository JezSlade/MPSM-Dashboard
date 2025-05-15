<?php
// v1.0.0 [Fetch Devices by Customer from /CustomerDashboard/Devices]
header('Content-Type: application/json');

function fail($msg, $extra = []) {
    http_response_code(500);
    echo json_encode(array_merge(['error' => $msg], $extra));
    exit;
}

// Load environment
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) fail('.env not found');
$env = parse_ini_file($envPath);

$client_id     = $env['CLIENT_ID']     ?? fail('CLIENT_ID missing');
$client_secret = $env['CLIENT_SECRET'] ?? fail('CLIENT_SECRET missing');
$username      = $env['USERNAME']      ?? fail('USERNAME missing');
$password      = $env['PASSWORD']      ?? fail('PASSWORD missing');

// Read CustomerId from input
$input = json_decode(file_get_contents('php://input'), true);
$customerId = $input['CustomerId'] ?? fail("Missing CustomerId");

// Fetch token
function fetch_token($client_id, $client_secret, $username, $password) {
    $url = 'https://api.abassetmanagement.com/api3/token';
    $post = http_build_query([
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'grant_type'    => 'password',
        'username'      => $username,
        'password'      => $password,
        'scope'         => 'account'
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded',
            'Cache-Control: no-cache'
        ],
        CURLOPT_TIMEOUT => 10
    ]);

    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch)) fail("Token curl error", ['error' => curl_error($ch)]);
    curl_close($ch);

    $json = json_decode($res, true);
    if ($code >= 400 || !$json || !isset($json['access_token'])) {
        fail("Token error", ['http_code' => $code, 'response' => $res]);
    }
    return $json['access_token'];
}

$token = fetch_token($client_id, $client_secret, $username, $password);

// Fetch devices for customer
$url = 'https://api.abassetmanagement.com/api3/CustomerDashboard/Devices';
$payload = json_encode([
    'CustomerId' => $customerId,
    'SortColumn' => 'DeviceId',
    'SortOrder' => 0,
    'PageNumber' => 1,
    'PageRows' => 2147483647
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ],
    CURLOPT_TIMEOUT => 20
]);

$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (curl_errno($ch)) fail("Device curl error", ['error' => curl_error($ch)]);
curl_close($ch);

$data = json_decode($response, true);
if ($code >= 400 || !$data || !isset($data['Result'])) {
    fail("Device fetch failed", ['http_code' => $code, 'response' => $response]);
}

echo json_encode($data);
