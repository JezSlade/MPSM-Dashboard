<?php
// api/get_device_detail.php
// POC: Fetch all Device/* details for a single device
// Supports input via serialNumber or externalIdentifier (AssetNumber)

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// 1. Load .env manually
function load_env($path = __DIR__ . '/../.env') {
    $env = [];
    foreach (@file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$k, $v] = explode('=', $line, 2) + [null,null];
        if ($k) $env[trim($k)] = trim($v);
    }
    return $env;
}
$env = load_env();

// 2. Redis cache helper
require_once __DIR__ . '/../includes/redis.php';

// 3. OAuth token acquisition
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
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded','Accept: application/json'],
    ]);
    $rsp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = json_decode($rsp, true);
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
// if externalIdentifier passed, treat as AssetNumber
if (isset($in['externalidentifier'])) {
    $in['assetnumber'] = strtoupper($in['externalidentifier']);
}
// uppercase serial if present
if (isset($in['serialnumber'])) {
    $in['serialnumber'] = strtoupper($in['serialnumber']);
}

// 5. Resolve device via GetDevicesRequest
// build lookup body per schema
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

// optionally debug:
// if (!empty($in['debug'])) { echo json_encode(['lookup'=>$lookup],JSON_PRETTY_PRINT); exit; }

function callApi(string $method, string $url, string $token, array $body = null) {
    $ch = curl_init($url);
    $hdr = ["Authorization: Bearer $token","Accept: application/json"];
    if ($body !== null) {
        $hdr[] = "Content-Type: application/json";
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['request'=>$body]));
        curl_setopt($ch, CURLOPT_POST, true);
    } elseif ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>$hdr]);
    $rsp = curl_exec($ch);
    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['error'=>$err];
    }
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status'=>$status,'data'=>json_decode($rsp,true)];
}

// 6. Hit /Device/GetDevices to find one device
$base = rtrim($env['API_BASE_URL'],'/').'/Device/';
$resp = callApi('POST', $base.'GetDevices', $token, $lookup);
$deviceList = $resp['data']['Result'] ?? $resp['data']['Devices'] ?? [];
if (empty($deviceList) || !is_array($deviceList)) {
    http_response_code(404);
    echo json_encode(['error'=>'Device not found','lookupResponse'=>$resp]);
    exit;
}
$device = $deviceList[0];

// extract identifiers
$deviceId     = $device['Id'] ?? null;
$serialNumber = $device['SerialNumber'] ?? $in['serialnumber'] ?? null;
$assetNumber  = $device['AssetNumber']    ?? $in['assetnumber']  ?? null;
$dealerCode   = $device['Dealer']['Code'] ?? $env['DEALER_CODE']    ?? null;
$customerCode = $device['Customer']['Code']?? $env['CUSTOMER_CODE']  ?? null;

// 7. Cache key
$cacheKey = "device_detail:{$deviceId}";
if ($cached = getCache($cacheKey)) {
    echo $cached;
    exit;
}

// 8. Sequentially call all Device/* endpoints
$out = ['lookup'=>$device];

$endpoints = [
    // 1. /Device/Get
    'Get' => ['POST','Get',[
        'dealerCode'  => $dealerCode,
        'id'          => $deviceId,
        'CustomerCode'=> $customerCode,
        'SerialNumber'=> $serialNumber,
        'AssetNumber' => $assetNumber
    ]],
    // 2. /Device/GetDeviceDashboard (GET)
    'GetDeviceDashboard' => ['GET',"GetDeviceDashboard?dealerId={$env['DEALER_ID']}&customerId={$device['Customer']['Id']}&deviceId={$deviceId}", null],
    // 3. /Device/GetDevicesCount
    'GetDevicesCount' => ['POST','GetDevicesCount',[
        'DealerCode'  => $dealerCode,
        'CustomerCode'=> $customerCode
    ]],
    // 4. /Device/GetDeviceAlerts
    'GetDeviceAlerts' => ['POST','GetDeviceAlerts',[
        'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,
        'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,
        'AssetNumber'=>$assetNumber
    ]],
    // 5. /Device/GetAvailableSupplies
    'GetAvailableSupplies'=>['POST','GetAvailableSupplies',[
        'dealerCode'=>$dealerCode,
        'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,
        'AssetNumber'=>$assetNumber
    ]],
    // 6. /Device/GetSupplyAlerts
    'GetSupplyAlerts'=>['POST','GetSupplyAlerts',[
        'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    // 7. /Device/GetMaintenanceAlerts
    'GetMaintenanceAlerts'=>['POST','GetMaintenanceAlerts',[
        'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    // 8. /Device/GetDeviceDataHistory
    'GetDeviceDataHistory'=>['POST','GetDeviceDataHistory',[
        'dealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,
        'DeviceId'=>$deviceId
    ]],
    // 9. /Device/GetDeviceChart
    'GetDeviceChart'=>['POST','GetDeviceChart',[
        'dealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,
        'DeviceId'=>$deviceId
    ]],
    // 10. /Device/GetErrorsMessagesDataHistory
    'GetErrorsMessagesDataHistory'=>['POST','GetErrorsMessagesDataHistory',[
        'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    // 11. /Device/GetAttributesDataHistory
    'GetAttributesDataHistory'=>['POST','GetAttributesDataHistory',[
        'dealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,
        'DeviceId'=>$deviceId
    ]],
    // 12. /Device/GetDeviceActionsDashboard
    'GetDeviceActionsDashboard'=>['POST','GetDeviceActionsDashboard',[
        'dealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'DeviceId'=>$deviceId,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]]
];

foreach ($endpoints as $name => [$method, $path, $body]) {
    $url = ($method==='GET')
         ? $base.$path
         : $base.$path;
    $out[$name] = callApi($method, $url, $token, $body);
}

// 9. Cache & return combined
$response = json_encode($out, JSON_PRETTY_PRINT);
setCache($cacheKey, $response, 60);
echo $response;
