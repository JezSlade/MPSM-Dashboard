<?php
// Enable strict error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON response header
header('Content-Type: application/json');

// Load .env variables
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    http_response_code(500);
    echo json_encode(['error' => '.env file not found']);
    exit;
}

$env = parse_ini_file($envPath, false, INI_SCANNER_TYPED);

// Validate required fields
$required = ['CLIENT_ID', 'CLIENT_SECRET', 'API_USER', 'API_PASS'];
foreach ($required as $key) {
    if (empty($env[$key])) {
        http_response_code(500);
        echo json_encode(['error' => "Missing required .env key: $key"]);
        exit;
    }
}

// Prepare token request
$tokenUrl = "https://api.abassetmanagement.com/api3/token";
$postFields = http_build_query([
    'client_id'     => $env['CLIENT_ID'],
    'client_secret' => $env['CLIENT_SECRET'],
    'grant_type'    => 'password',
    'username'      => $env['API_USER'],
    'password'      => $env['API_PASS'],
    'scope'         => 'account'
]);

// Initialize CURL
$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

// Handle curl errors
if ($curlErr) {
    http_response_code(502);
    echo json_encode(['error' => 'CURL error', 'details' => $curlErr]);
    exit;
}

// Decode and re-validate response
$data = json_decode($response, true);
if ($httpCode !== 200 || !isset($data['access_token'])) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Token request failed',
        'status' => $httpCode,
        'response' => $data
    ]);
    exit;
}

// Return token
echo json_encode([
    'access_token' => $data['access_token'],
    'expires_in' => $data['expires_in'] ?? null,
    'token_type' => $data['token_type'] ?? 'Bearer'
]);
