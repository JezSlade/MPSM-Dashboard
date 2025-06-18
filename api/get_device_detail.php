<?php
// api/get_device_detail.php
// Fetch full device detail for one device by serialNumber or externalIdentifier

// 1. Standard headers & error settings
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// 2. Load .env (identical to other API files)
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

// 3. Include Redis helper (unchanged)
require_once __DIR__ . '/../includes/redis.php';

// 4. Acquire OAuth token (same as get_token.php)
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

// 5. Read & normalize input (identical to other files)
$raw = json_decode(file_get_contents('php://input'), true) ?: $_GET;
$in  = [];
foreach ($raw as $k => $v) {
    $in[strtolower($k)] = is_string($v) ? trim($v) : $v;
}
// map externalIdentifier → AssetNumber
if (isset($in['externalidentifier'])) {
    $in['assetnumber'] = strtoupper($in['externalidentifier']);
}
// uppercase serialNumber
if (isset($in['serialnumber'])) {
    $in['serialnumber'] = strtoupper($in['serialnumber']);
}

// 6. callApi helper (payload sent at root, not wrapped)
function callApi(string $method, string $url, string $token, array $body = null) {
    $ch = curl_init($url);
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        "Authorization: Bearer $token"
    ];
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));  // direct root payload
    } elseif ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    $rsp = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    // return decoded JSON or raw status on parse failure
    $data = json_decode($rsp, true);
    return $data !== null ? $data : ['status'=>$status,'raw'=>$rsp];
}

// 7. Resolve the device via POST /Device/Get
$baseUrl  = rtrim($env['API_BASE_URL'],'/').'/Device/';
$lookupBody = [
    'dealerCode'   => $env['DEALER_CODE']   ?? '',
    'CustomerCode' => $env['CUSTOMER_CODE'] ?? ''
];
if (!empty($in['serialnumber'])) {
    $lookupBody['SerialNumber'] = $in['serialnumber'];
} elseif (!empty($in['assetnumber'])) {
    $lookupBody['AssetNumber']  = $in['assetnumber'];
} else {
    http_response_code(400);
    echo json_encode(['error'=>'Missing serialNumber or externalIdentifier']);
    exit;
}

// debug flag to inspect lookup
if (!empty($in['debug'])) {
    echo json_encode([
        'lookupUrl'  => $baseUrl.'Get',
        'lookupBody' => $lookupBody,
        'response'   => callApi('POST',$baseUrl.'Get',$token,$lookupBody)
    ], JSON_PRETTY_PRINT);
    exit;
}

// call /Device/Get
$respGet = callApi('POST', $baseUrl.'Get', $token, $lookupBody);
if (empty($respGet['Result']) || !is_array($respGet['Result'])) {
    http_response_code(404);
    echo json_encode(['error'=>'Device not found','lookupResponse'=>$respGet], JSON_PRETTY_PRINT);
    exit;
}
$device = $respGet['Result'];

// 8. Extract IDs & codes
$deviceId     = $device['Id'];
$serialNumber = $device['SerialNumber'] ?? '';
$assetNumber  = $device['AssetNumber']  ?? '';
$dealerCode   = $device['Dealer']['Code']   ?? $env['DEALER_CODE']   ?? '';
$customerCode = $device['Customer']['Code'] ?? $env['CUSTOMER_CODE'] ?? '';

// 9. Redis cache check
$cacheKey = "device_detail:{$deviceId}";
if ($cached = getCache($cacheKey)) {
    echo $cached;
    exit;
}

// 10. Fan-out to all Device/* endpoints
$output = ['device'=>$device];
$endpoints = [
    'GetDeviceDashboard'         => ['GET',"GetDeviceDashboard?dealerId={$env['DEALER_ID']}&customerId={$device['Customer']['Id']}&deviceId={$deviceId}",null],
    'GetDevices'                 => ['POST','GetDevices',[
        'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'Id','SortOrder'=>'Asc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'Search'=>$serialNumber
    ]],
    'GetDeviceAlerts'            => ['POST','GetDeviceAlerts',[
        'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    'GetDevicesCount'            => ['POST','GetDevicesCount',[
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode
    ]],
    'GetAvailableSupplies'       => ['POST','GetAvailableSupplies',[
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    'GetSupplyAlerts'            => ['POST','GetSupplyAlerts',[
        'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    'GetMaintenanceAlerts'       => ['POST','GetMaintenanceAlerts',[
        'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    'GetDeviceDataHistory'       => ['POST','GetDeviceDataHistory',[
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,'DeviceId'=>$deviceId
    ]],
    'GetDeviceChart'             => ['POST','GetDeviceChart',[
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,'DeviceId'=>$deviceId
    ]],
    'GetErrorsMessagesDataHistory'=>['POST','GetErrorsMessagesDataHistory',[
        'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    'GetAttributesDataHistory'   =>['POST','GetAttributesDataHistory',[
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,'DeviceId'=>$deviceId
    ]],
    'GetDeviceActionsDashboard'  =>['POST','GetDeviceActionsDashboard',[
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'DeviceId'=>$deviceId,'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]]
];

foreach ($endpoints as $name=>[$method,$path,$body]) {
    $output[$name] = callApi($method,$baseUrl.$path,$token,$body);
}

// 11. Cache & return combined
$response = json_encode($output, JSON_PRETTY_PRINT);
setCache($cacheKey,$response,60);
echo $response;
