<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

header('Content-Type: application/json');

// Load .env manually
$envPath = realpath(__DIR__ . '/../.env');
if (!file_exists($envPath)) {
    echo json_encode(['success' => false, 'error' => 'Missing .env']);
    exit;
}

$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    [$key, $value] = explode('=', $line, 2);
    putenv(trim($key) . '=' . trim($value));
}

$serial = $_GET['serial'] ?? null;
if (!$serial) {
    echo json_encode(['success' => false, 'error' => 'Missing serial']);
    exit;
}

// Generate token
$tokenUrl = getenv('TOKEN_URL');
$clientId = getenv('CLIENT_ID');
$clientSecret = getenv('CLIENT_SECRET');
$username = getenv('USERNAME');
$password = getenv('PASSWORD');
$scope = getenv('SCOPE');

$tokenPayload = http_build_query([
    'grant_type' => 'password',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'username' => $username,
    'password' => $password,
    'scope' => $scope,
]);

$tokenContext = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded",
        'content' => $tokenPayload,
    ]
]);

$tokenResponse = @file_get_contents($tokenUrl, false, $tokenContext);
$tokenData = json_decode($tokenResponse, true);
$token = $tokenData['access_token'] ?? null;

if (!$token) {
    echo json_encode(['success' => false, 'error' => 'Failed to authenticate']);
    exit;
}

// Call Device/GetDetails
$apiBase = getenv('API_BASE_URL');
$detailPayload = json_encode(['SerialNumber' => $serial]);

$detailContext = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\nAuthorization: Bearer $token",
        'content' => $detailPayload,
    ]
]);

$detailResponse = @file_get_contents($apiBase . 'Device/GetDetails', false, $detailContext);
if ($detailResponse === false) {
    echo json_encode(['success' => false, 'error' => 'Failed to get device details']);
    exit;
}

$detailData = json_decode($detailResponse, true);
echo json_encode(['success' => true, 'data' => $detailData]);
