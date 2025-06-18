<?php
// api/get_device_detail.php
// Fetch full device detail for one device by serialNumber or externalIdentifier
// (Debug hack: externalIdentifier “DM060” – remove when done)

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// 1. Load environment variables
function load_env($path = __DIR__ . '/../.env') {
    $env = [];
    if (!file_exists($path)) return $env;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$k, $v] = explode('=', $line, 2) + [null, null];
        if ($k) $env[trim($k)] = trim($v);
    }
    return $env;
}
$env = load_env();

// 2. Redis caching helper
require_once __DIR__ . '/../includes/redis.php';

// 3. OAuth token helper (reuse shared implementation)
require_once __DIR__ . '/get_token.php';
$token = get_token();

// 4. Read & normalize input
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true) ?: $_GET;
$normalized = [];
foreach ($in as $k => $v) {
    $normalized[strtolower($k)] = is_string($v) ? trim($v) : $v;
}
$in = $normalized;

// DEBUG hack – hard-code DM060
$in['externalidentifier'] = 'DM060';
$in['assetnumber']       = 'DM060';

// Map to schema fields
if (isset($in['externalidentifier'])) {
    $in['assetnumber'] = strtoupper($in['externalidentifier']);
}
if (isset($in['serialnumber'])) {
    $in['serialnumber'] = strtoupper($in['serialnumber']);
}

// 5. Check cache
$cacheKey = 'mpsm:api:get_device_detail:' . md5(json_encode($in));
if ($cached = getCache($cacheKey)) {
    echo $cached;
    exit;
}

// 6. cURL helper – matches other api/*.php exactly
function callApi(string $method, string $url, string $token, array $body = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        "Authorization: Bearer $token"
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['request' => $body]));
    } elseif ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['request' => $body]));
        }
    }
    $rsp = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($rsp, true);
    return $data !== null ? $data : ['status' => $status, 'raw' => $rsp];
}

// 7. Lookup device via POST /Device/Get
$deviceBase = rtrim($env['API_BASE_URL'], '/') . '/Device/';
$lookupBody = [
    'dealerCode'   => $env['DEALER_CODE']   ?? '',
    'CustomerCode' => $env['CUSTOMER_CODE'] ?? ''
];
if (!empty($in['serialnumber'])) {
    $lookupBody['serialNumber'] = $in['serialnumber'];
} elseif (!empty($in['assetnumber'])) {
    $lookupBody['assetNumber']  = $in['assetnumber'];
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Missing serialNumber or externalIdentifier']);
    exit;
}

$respGet = callApi('POST', $deviceBase . 'Get', $token, $lookupBody);
$device  = $respGet['Result'] ?? null;
if (!is_array($device)) {
    http_response_code(404);
    echo json_encode(['error' => 'Device not found', 'lookup' => $respGet], JSON_PRETTY_PRINT);
    exit;
}

// 8. Extract identifiers
$deviceId     = $device['Id'];
$serialNumber = $device['SerialNumber'] ?? '';
$assetNumber  = $device['AssetNumber']  ?? '';
$dealerCode   = $device['Dealer']['Code']   ?? $env['DEALER_CODE']   ?? '';
$customerCode = $device['Customer']['Code'] ?? $env['CUSTOMER_CODE'] ?? '';
$dealerId     = $device['Dealer']['Id']     ?? $env['DEALER_ID']     ?? null;

// 9. Fan-out all endpoints
$output = ['device' => $device];

// GET /Device/GetDeviceDashboard
$output['GetDeviceDashboard'] = callApi(
    'GET',
    $deviceBase
      . "GetDeviceDashboard?dealerId={$dealerId}"
      . "&customerId={$device['Customer']['Id']}"
      . "&deviceId={$deviceId}",
    $token,
    null
);

// POST /Device/GetDevices
$output['GetDevices'] = callApi(
    'POST',
    $deviceBase . 'GetDevices',
    $token,
    [
      'PageNumber'   => 1,
      'PageRows'     => 100,
      'SortColumn'   => 'Id',
      'SortOrder'    => 'Asc',
      'DealerCode'   => $dealerCode,
      'CustomerCode' => $customerCode,
      'Search'       => $serialNumber
    ]
);

// POST /Device/GetDeviceAlerts
$output['GetDeviceAlerts'] = callApi(
    'POST',
    $deviceBase . 'GetDeviceAlerts',
    $token,
    [
      'PageNumber'   => 1,
      'PageRows'     => 100,
      'SortColumn'   => 'InitialDate',
      'SortOrder'    => 'Desc',
      'DealerCode'   => $dealerCode,
      'CustomerCode' => $customerCode,
      'SerialNumber' => $serialNumber,
      'AssetNumber'  => $assetNumber
    ]
);

// POST /Device/GetDevicesCount
$output['GetDevicesCount'] = callApi(
    'POST',
    $deviceBase . 'GetDevicesCount',
    $token,
    ['DealerCode'   => $dealerCode, 'CustomerCode' => $customerCode]
);

// POST /Device/GetAvailableSupplies
$output['GetAvailableSupplies'] = callApi(
    'POST',
    $deviceBase . 'GetAvailableSupplies',
    $token,
    ['DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
     'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber]
);

// POST /Device/GetSupplyAlerts
$output['GetSupplyAlerts'] = callApi(
    'POST',
    $deviceBase . 'GetSupplyAlerts',
    $token,
    [
      'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
      'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
      'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]
);

// POST /Device/GetMaintenanceAlerts
$output['GetMaintenanceAlerts'] = callApi(
    'POST',
    $deviceBase . 'GetMaintenanceAlerts',
    $token,
    [
      'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
      'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
      'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]
);

// POST /Device/GetDeviceDataHistory
$output['GetDeviceDataHistory'] = callApi(
    'POST',
    $deviceBase . 'GetDeviceDataHistory',
    $token,
    ['DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
     'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,
     'DeviceId'=>$deviceId]
);

// POST /Device/GetDeviceChart
$output['GetDeviceChart'] = callApi(
    'POST',
    $deviceBase . 'GetDeviceChart',
    $token,
    ['DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
     'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,
     'DeviceId'=>$deviceId]
);

// POST /Device/GetErrorsMessagesDataHistory
$output['GetErrorsMessagesDataHistory'] = callApi(
    'POST',
    $deviceBase . 'GetErrorsMessagesDataHistory',
    $token,
    ['PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
     'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
     'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber]
);

// POST /Device/GetAttributesDataHistory
$output['GetAttributesDataHistory'] = callApi(
    'POST',
    $deviceBase . 'GetAttributesDataHistory',
    $token,
    ['DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
     'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,
     'DeviceId'=>$deviceId]
);

// GET /SdsAction/GetDeviceActionsDashboard
$actionBase = rtrim($env['API_BASE_URL'], '/') . '/SdsAction/';
$output['GetDeviceActionsDashboard'] = callApi(
    'GET',
    $actionBase . "GetDeviceActionsDashboard?deviceId={$deviceId}&dealerId={$dealerId}",
    $token,
    null
);

// 10. Cache & return successful response
$response = json_encode($output, JSON_PRETTY_PRINT);
setCache($cacheKey, $response, 60);
echo $response;
