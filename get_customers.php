<?php
// v1.1.0 [Fix: POST Method + JSON Body for /Customer/GetCustomers]
header('Content-Type: application/json');

function fail($msg, $extra = []) {
    http_response_code(500);
    echo json_encode(array_merge(['error' => $msg], $extra));
    exit;
}

$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) fail('.env not found');
$env = parse_ini_file($envPath);

$client_id     = $env['CLIENT_ID']     ?? fail('CLIENT_ID missing');
$client_secret = $env['CLIENT_SECRET'] ?? fail('CLIENT_SECRET missing');
$username      = $env['USERNAME']      ?? fail('USERNAME missing');
$password      = $env['PASSWORD']      ?? fail('PASSWORD missing');
$dealer_code   = 'NY06AGDWUQ'; // required input

function fetch_token($client_id, $client_secret, $username, $password) {
    $url = 'https://api.abassetmanagement.com/api3/token';
    $data = http_build_query([
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
        CURLOPT_POSTFIELDS     => $data,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded',
            'Cache-Control: no-cache'
        ],
        CURLOPT_TIMEOUT => 10
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch)) fail('Token curl error', ['error' => curl_error($ch)]);
    curl_close($ch);
    $json = json_decode($response, true);
    if ($code >= 400 || !$json || !isset($json['access_token'])) {
        fail('Token error', ['http_code' => $code, 'response' => $response]);
    }
    return $json['access_token'];
}

// Get token
$token = fetch_token($client_id, $client_secret, $username, $password);

// Call /Customer/GetCustomers
$url = 'https://api.abassetmanagement.com/api3/Customer/GetCustomers';
$payload = json_encode([
    "DealerCode" => $dealer_code,
    "Code" => null,
    "HasHpSds" => null,
    "FilterText" => null,
    "PageNumber" => 1,
    "PageRows" => 2147483647,
    "SortColumn" => "Id",
    "SortOrder" => 0
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
    CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (curl_errno($ch)) fail('Customer curl error', ['error' => curl_error($ch)]);
curl_close($ch);

$data = json_decode($response, true);
if ($code >= 400 || !$data || !isset($data['Result'])) {
    fail("Customer fetch failed", ['http_code' => $code, 'response' => $response]);
}

// Return simplified list
$out = array_map(function ($c) {
    return [
        'Id' => $c['Id'],
        'Code' => $c['Code'],
        'Description' => $c['Description']
    ];
}, $data['Result']);

echo json_encode(['Result' => $out]);
