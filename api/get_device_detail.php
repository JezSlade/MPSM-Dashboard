<?php
// api/get_device_detail.php
// Fetch full device detail for one device by externalIdentifier or serialNumber

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors',   '1');
ini_set('log_errors',       '1');
ini_set('error_log',        __DIR__ . '/../logs/debug.log');

// 1. Redis cache helper
require_once __DIR__ . '/../includes/redis.php';

// 2. Load environment (.env)
function load_env($path = __DIR__ . '/../.env') {
    $env = [];
    if (!file_exists($path)) return $env;
    foreach (file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$k,$v] = explode('=', $line, 2) + [null,null];
        if ($k) $env[trim($k)] = trim($v);
    }
    return $env;
}
$env = load_env();

// 3. Inline get_token (same as your other APIs)
function get_token(array $env) {
    foreach (['CLIENT_ID','CLIENT_SECRET','USERNAME','PASSWORD','SCOPE','TOKEN_URL'] as $k) {
        if (empty($env[$k])) {
            error_log("Missing $k in .env", 3, __DIR__.'/../logs/debug.log');
            http_response_code(500);
            echo json_encode(['error'=>"Missing $k in .env"]);
            exit;
        }
    }
    $post = http_build_query([
        'grant_type'=>'password',
        'client_id'=>$env['CLIENT_ID'],
        'client_secret'=>$env['CLIENT_SECRET'],
        'username'=>$env['USERNAME'],
        'password'=>$env['PASSWORD'],
        'scope'=>$env['SCOPE'],
    ]);
    $ch = curl_init($env['TOKEN_URL']);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ],
    ]);
    $rsp  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = json_decode($rsp,true);
    if ($code!==200 || empty($json['access_token'])) {
        http_response_code(401);
        echo json_encode(['error'=>'Token request failed','details'=>$json]);
        exit;
    }
    return $json['access_token'];
}
$token = get_token($env);

// 4. Read & normalize input
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true) ?: $_GET;
$normalized = [];
foreach ($in as $k=>$v) {
    $normalized[strtolower($k)] = is_string($v)?trim($v):$v;
}
$in = $normalized;

// DEBUG: hard-code externalIdentifier (remove later)
$in['externalidentifier'] = 'DM060';

// map externalIdentifier → use later
$extId = strtoupper(trim($in['externalidentifier'] ?? ''));

// 5. Cache key & check
$cacheKey = 'mpsm:api:get_device_detail:' . md5(json_encode($in));
if ($cached = getCache($cacheKey)) {
    echo $cached;
    exit;
}

// 6. cURL helper (wrap POST bodies under "request")
function callApi(string $method, string $url, string $token, array $body=null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        "Authorization: Bearer $token"
    ]);
    if ($method==='POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['request'=>$body]));
    } elseif ($method!=='GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($body!==null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['request'=>$body]));
        }
    }
    $rsp = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($rsp,true);
    return $data!==null ? $data : ['status'=>$status,'raw'=>$rsp];
}

// Base URLs
$deviceBase  = rtrim($env['API_BASE_URL'],'/').'/Device/';
$actionBase  = rtrim($env['API_BASE_URL'],'/').'/SdsAction/';

// 7. Resolve device by externalIdentifier (two-step lookup)
if ($extId !== '') {
    $lookup = callApi('POST', $deviceBase.'GetDevices', $token, [
        'PageNumber'   => 1,
        'PageRows'     => 1,
        'SortColumn'   => 'Id',
        'SortOrder'    => 'Asc',
        'Filter'       => ['ExternalIdentifier'=>$extId]
    ]);
    $found = $lookup['Result'][0] ?? null;
    if (empty($found['Id'])) {
        http_response_code(404);
        echo json_encode(['error'=>'Device not found by externalIdentifier','lookup'=>$lookup], JSON_PRETTY_PRINT);
        exit;
    }
    // Use the found DeviceDto for full detail
    $deviceId     = $found['Id'];
    $serialNumber = $found['SerialNumber'] ?? '';
    $assetNumber  = $found['AssetNumber']  ?? '';
    // Now fetch full DeviceDto via Get
    $respGet = callApi('POST', $deviceBase.'Get', $token, [
        'dealerCode'=>$env['DEALER_CODE'] ?? '',
        'id'        =>$deviceId
    ]);
    $device = $respGet['Result'] ?? null;
    if (!is_array($device)) {
        http_response_code(404);
        echo json_encode(['error'=>'Device not found by ID','lookupGet'=>$respGet], JSON_PRETTY_PRINT);
        exit;
    }
} else {
    http_response_code(400);
    echo json_encode(['error'=>'Missing externalIdentifier']);
    exit;
}

// Extract codes
$dealerCode   = $device['Dealer']['Code']   ?? $env['DEALER_CODE']   ?? '';
$customerCode = $device['Customer']['Code'] ?? $env['CUSTOMER_CODE'] ?? '';
$dealerId     = $device['Dealer']['Id']     ?? $env['DEALER_ID']     ?? null;
$customerId   = $device['Customer']['Id']   ?? null;

// 8. Fan-out all endpoints
$output = ['device'=>$device];

// GET /Device/GetDeviceDashboard
$output['GetDeviceDashboard'] = callApi(
    'GET',
    $deviceBase."GetDeviceDashboard?dealerId={$dealerId}&customerId={$customerId}&deviceId={$deviceId}",
    $token,
    null
);

// POST /Device/GetDevices
$output['GetDevices'] = callApi(
    'POST',
    $deviceBase.'GetDevices',
    $token,
    [
      'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'Id','SortOrder'=>'Asc',
      'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
      'Search'=>$serialNumber
    ]
);

// POST /Device/GetDeviceAlerts
$output['GetDeviceAlerts'] = callApi(
    'POST',
    $deviceBase.'GetDeviceAlerts',
    $token,
    [
      'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
      'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
      'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]
);

// POST /Device/GetDevicesCount
$output['GetDevicesCount'] = callApi(
    'POST',
    $deviceBase.'GetDevicesCount',
    $token,
    ['DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode]
);

// POST /Device/GetAvailableSupplies
$output['GetAvailableSupplies'] = callApi(
    'POST',
    $deviceBase.'GetAvailableSupplies',
    $token,
    ['DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
     'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber]
);

// POST /Device/GetSupplyAlerts
$output['GetSupplyAlerts'] = callApi(
    'POST',
    $deviceBase.'GetSupplyAlerts',
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
    $deviceBase.'GetMaintenanceAlerts',
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
    $deviceBase.'GetDeviceDataHistory',
    $token,
    ['DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
     'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,
     'DeviceId'=>$deviceId]
);

// POST /Device/GetDeviceChart
$output['GetDeviceChart'] = callApi(
    'POST',
    $deviceBase.'GetDeviceChart',
    $token,
    ['DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
     'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,
     'DeviceId'=>$deviceId]
);

// POST /Device/GetErrorsMessagesDataHistory
$output['GetErrorsMessagesDataHistory'] = callApi(
    'POST',
    $deviceBase.'GetErrorsMessagesDataHistory',
    $token,
    ['PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
     'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
     'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber]
);

// POST /Device/GetAttributesDataHistory
$output['GetAttributesDataHistory'] = callApi(
    'POST',
    $deviceBase.'GetAttributesDataHistory',
    $token,
    ['DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
     'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,
     'DeviceId'=>$deviceId]
);

// GET /SdsAction/GetDeviceActionsDashboard
$output['GetDeviceActionsDashboard'] = callApi(
    'GET',
    $actionBase."GetDeviceActionsDashboard?deviceId={$deviceId}&dealerId={$dealerId}",
    $token,
    null
);

// 9. Cache & return
$response = json_encode($output, JSON_PRETTY_PRINT);
setCache($cacheKey, $response, 60);
echo $response;
