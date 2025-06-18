<?php
// api/get_device_detail.php
// POC: Fetch all Device/* details for one device by serialNumber or externalIdentifier
// Implements wrapBody toggle for lookup, extracts PagedResultResponse.Result properly

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// 1. Load .env
function load_env($path = __DIR__ . '/../.env') {
    $env = [];
    foreach (@file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$k,$v] = explode('=', $line, 2) + [null,null];
        if ($k) $env[trim($k)] = trim($v);
    }
    return $env;
}
$env = load_env();

// 2. Redis helper
require_once __DIR__ . '/../includes/redis.php';

// 3. Acquire OAuth token
function get_token(array $env) {
    foreach (['CLIENT_ID','CLIENT_SECRET','USERNAME','PASSWORD','SCOPE','TOKEN_URL'] as $k) {
        if (empty($env[$k])) {
            echo json_encode(['error'=>"Missing $k in .env"]);
            exit;
        }
    }
    $post = http_build_query([
        'grant_type'    => 'password',
        'client_id'     => $env['CLIENT_ID'],
        'client_secret' => $env['CLIENT_SECRET'],
        'username'      => $env['USERNAME'],
        'password'      => $env['PASSWORD'],
        'scope'         => $env['SCOPE'],
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
    if ($code !== 200 || empty($json['access_token'])) {
        echo json_encode(['error'=>'Token request failed','details'=>$json]);
        exit;
    }
    return $json['access_token'];
}
$token = get_token($env);

// 4. Read & normalize input
$raw = json_decode(file_get_contents('php://input'), true) ?: $_GET;
$in = [];
foreach ($raw as $k => $v) {
    $in[strtolower($k)] = is_string($v) ? trim($v) : $v;
}
// map externalIdentifier → AssetNumber
if (isset($in['externalidentifier'])) {
    $in['assetnumber'] = strtoupper($in['externalidentifier']);
}
// uppercase serial
if (isset($in['serialnumber'])) {
    $in['serialnumber'] = strtoupper($in['serialnumber']);
}

// 5. Build lookup for /Device/GetDevices
$lookup = [
    'PageNumber' => 1,
    'PageRows'   => 1,
    'SortColumn' => 'Id',
    'SortOrder'  => 'Asc',
];
if (!empty($in['serialnumber'])) {
    $lookup['SerialNumber'] = $in['serialnumber'];
} elseif (!empty($in['assetnumber'])) {
    $lookup['AssetNumber'] = $in['assetnumber'];
} else {
    http_response_code(400);
    echo json_encode(['error'=>'Missing serialNumber or externalIdentifier']);
    exit;
}

// 6. callApi helper with wrapBody toggle
function callApi(string $method, string $url, string $token, array $body = null, bool $wrapBody = true) {
    $ch = curl_init($url);
    $hdr = ["Authorization: Bearer $token","Accept: application/json"];
    if ($body !== null) {
        $hdr[] = "Content-Type: application/json";
        $payload = $wrapBody
                 ? json_encode(['request'=>$body])
                 : json_encode($body);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_POST, true);
    } elseif ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => $hdr,
    ]);
    $rsp = curl_exec($ch);
    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['status'=>0,'error'=>$err];
    }
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($rsp, true);
    return ['status'=>$status, 'raw'=>$rsp, 'data'=>$data];
}

// 7. Resolve the device via POST /Device/GetDevices (no request wrapper)
$base      = rtrim($env['API_BASE_URL'],'/').'/Device/';
$lookupUrl = $base.'GetDevices';
$resp = callApi('POST', $lookupUrl, $token, $lookup, false);

// debug on ?debug=1
if (!empty($in['debug'])) {
    echo json_encode([
        'lookupUrl'  => $lookupUrl,
        'lookupBody' => $lookup,
        'lookupResp' => $resp
    ], JSON_PRETTY_PRINT);
    exit;
}

// extract list from PagedResultResponse.Result
$deviceList = $resp['data']['Result'] ?? [];
if (!is_array($deviceList) || count($deviceList) === 0) {
    http_response_code(404);
    echo json_encode([
      'error' => 'Device not found',
      'lookupResponse' => $resp
    ], JSON_PRETTY_PRINT);
    exit;
}
$device = $deviceList[0];

// 8. Extract identifiers & codes
$deviceId     = $device['Id']           ?? null;
$serialNumber = $device['SerialNumber'] ?? null;
$assetNumber  = $device['AssetNumber']  ?? null;
$dealerCode   = $device['Dealer']['Code']   ?? $env['DEALER_CODE']   ?? '';
$customerCode = $device['Customer']['Code'] ?? $env['CUSTOMER_CODE'] ?? '';

if (!$deviceId) {
    http_response_code(500);
    echo json_encode(['error'=>'Resolved device has no Id'], JSON_PRETTY_PRINT);
    exit;
}

// 9. Redis cache check
$cacheKey = "device_detail:{$deviceId}";
if ($cached = getCache($cacheKey)) {
    echo $cached;
    exit;
}

// 10. Fan-out all Device endpoints
$out           = ['lookup'=>$device];
$endpoints     = [
    // 1. /Device/Get (POST)
    'Get' => ['POST','Get',[
        'dealerCode'   => $dealerCode,
        'id'           => $deviceId,
        'CustomerCode' => $customerCode,
        'SerialNumber' => $serialNumber,
        'AssetNumber'  => $assetNumber
    ]],
    // 2. /Device/GetDeviceDashboard (GET)
    'GetDeviceDashboard' => ['GET',
        "GetDeviceDashboard?dealerId={$env['DEALER_ID']}".
        "&customerId={$device['Customer']['Id']}".
        "&deviceId={$deviceId}",
        null
    ],
    // 3. /Device/GetDevicesCount
    'GetDevicesCount'=>['POST','GetDevicesCount',[
        'DealerCode' => $dealerCode,
        'CustomerCode' => $customerCode
    ]],
    // 4. /Device/GetDeviceAlerts
    'GetDeviceAlerts'=>['POST','GetDeviceAlerts',[
        'PageNumber'=>1,'PageRows'=>100,
        'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    // 5. /Device/GetAvailableSupplies
    'GetAvailableSupplies'=>['POST','GetAvailableSupplies',[
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    // 6. /Device/GetSupplyAlerts
    'GetSupplyAlerts'=>['POST','GetSupplyAlerts',[
        'PageNumber'=>1,'PageRows'=>100,
        'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    // 7. /Device/GetMaintenanceAlerts
    'GetMaintenanceAlerts'=>['POST','GetMaintenanceAlerts',[
        'PageNumber'=>1,'PageRows'=>100,
        'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    // 8. /Device/GetDeviceDataHistory
    'GetDeviceDataHistory'=>['POST','GetDeviceDataHistory',[
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,
        'DeviceId'=>$deviceId
    ]],
    // 9. /Device/GetDeviceChart
    'GetDeviceChart'=>['POST','GetDeviceChart',[
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,
        'DeviceId'=>$deviceId
    ]],
    //10. /Device/GetErrorsMessagesDataHistory
    'GetErrorsMessagesDataHistory'=>['POST','GetErrorsMessagesDataHistory',[
        'PageNumber'=>1,'PageRows'=>100,
        'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    //11. /Device/GetAttributesDataHistory
    'GetAttributesDataHistory'=>['POST','GetAttributesDataHistory',[
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,
        'DeviceId'=>$deviceId
    ]],
    //12. /Device/GetDeviceActionsDashboard
    'GetDeviceActionsDashboard'=>['POST','GetDeviceActionsDashboard',[
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'DeviceId'=>$deviceId,'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]]
];

foreach ($endpoints as $name => [$method,$path,$body]) {
    $url = $base . $path;
    // always wrap under "request"
    $out[$name] = callApi($method, $url, $token, $body, true);
}

// 11. Cache & return
$response = json_encode($out, JSON_PRETTY_PRINT);
setCache($cacheKey, $response, 60);
echo $response;
