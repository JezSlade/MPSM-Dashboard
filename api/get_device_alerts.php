<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

// Manual .env parsing
$env = parse_ini_file(__DIR__ . '/../.env');
$clientId = $env['CLIENT_ID'] ?? '';
$clientSecret = $env['CLIENT_SECRET'] ?? '';
$username = $env['USERNAME'] ?? '';
$password = $env['PASSWORD'] ?? '';
$scope = $env['SCOPE'] ?? '';
$tokenUrl = $env['TOKEN_URL'] ?? '';
$apiBaseUrl = rtrim($env['API_BASE_URL'] ?? '', '/') . '/';
$dealerCode = $env['DEALER_CODE'] ?? '';
$defaultCustomer = 'W9OPXL0YDK';

// --- Get Auth Token ---
$token = null;
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
    'method' => 'POST',
    'header' => "Content-Type: application/x-www-form-urlencoded",
    'content' => $tokenPayload
  ]
]);

$tokenResponse = file_get_contents($tokenUrl, false, $tokenContext);
$tokenData = json_decode($tokenResponse, true);
$token = $tokenData['access_token'] ?? null;

if (!$token) {
  http_response_code(500);
  echo json_encode(['error' => 'Unable to retrieve access token']);
  exit;
}

// --- Prepare Payload ---
$customerCode = $_GET['customer'] ?? $defaultCustomer;

$payload = json_encode([
  'DealerCode' => $dealerCode,
  'CustomerCode' => $customerCode,
  'DeviceId' => null,
  'SerialNumber' => null,
  'AssetNumber' => null,
  'InitialFrom' => null,
  'InitialTo' => null,
  'ExhaustedFrom' => null,
  'ExhaustedTo' => null,
  'Brand' => null,
  'Model' => null,
  'OfficeDescription' => null,
  'SupplySetDescription' => null,
  'FilterCustomerText' => null,
  'ManageOption' => null,
  'InstallationOption' => null,
  'CancelOption' => null,
  'HiddenOption' => null,
  'SupplyType' => null,
  'ColorType' => null,
  'ExcludeForStockShippedSupplies' => false,
  'FilterText' => null,
  'PageNumber' => 1,
  'PageRows' => 50,
  'SortColumn' => 'InitialDate',
  'SortOrder' => 0
]);

// --- Perform API Call ---
$opts = [
  'http' => [
    'method' => 'POST',
    'header' => "Content-Type: application/json\r\nAuthorization: Bearer $token",
    'content' => $payload
  ]
];
$context = stream_context_create($opts);
$response = file_get_contents($apiBaseUrl . 'SupplyAlert/List', false, $context);

// --- Output JSON ---
header('Content-Type: application/json');
echo $response;
