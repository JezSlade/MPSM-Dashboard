<?php
// v1.1.0 [Fail-Loud Token + Debug-Ready Output]
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

// Use curl explicitly
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
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    fail('Curl error', ['curl_error' => curl_error($ch)]);
}
curl_close($ch);

if ($http_code >= 400) {
    fail("API error", ['http_code' => $http_code, 'response' => $response]);
}

$data = json_decode($response, true);
if (!$data || !isset($data['access_token'])) {
    fail('Invalid token response', ['raw' => $response]);
}

echo json_encode($data);
