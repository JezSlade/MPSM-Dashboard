<?php
// api/get_device_detail.php
// POC: Fetch all Device/* details for a single device
// Supports input via serialNumber or externalIdentifier (AssetNumber)

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors','1');
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
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ],
    ]);
    $rsp  = curl_exec($ch);
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
// map externalIdentifier → AssetNumber
if (isset($in['externalidentifier'])) {
    $in['assetnumber'] = strtoupper($in['externalidentifier']);
}
// uppercase serial
if (isset($in['serialnumber'])) {
    $in['serialnumber'] = strtoupper($in['serialnumber']);
}

// 5. Build lookup for GetDevices
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

// 6. cURL helper (follows redirects)
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
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => $hdr
    ]);
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

// 7. Resolve device via /Device/GetDevices
$base = rtrim($env['API_BASE_URL'],'/').'/Device/';
$resp = callApi('POST', $base.'GetDevices', $token, $lookup);
$deviceList = $resp['data']['Result'] ?? $resp['data']['Devices'] ?? [];
if (empty($deviceList) || !is_array($deviceList)) {
    http_response_code(404);
    echo json_encode(['error'=>'Device not found','lookupResponse'=>$resp]);
    exit;
}
$device = $deviceList[0];

// identifiers & codes
$deviceId     = $device['Id'] ?? null;
$serialNumber = $device['SerialNumber'] ?? null;
$assetNumber  = $device['AssetNumber']    ?? null;
$dealerCode   = $device['Dealer']['Code'] ?? $env['DEALER_CODE'] ?? '';
$customerCode = $device['Customer']['Code']?? $env['CUSTOMER_CODE'] ?? '';

// 8. Redis cache key
$cacheKey = "device_detail:{$deviceId}";
if ($cached = getCache($cacheKey)) {
    echo $cached;
    exit;
}

// 9. Fan-out all Device endpoints
$out = ['lookup' => $device];
$endpoints = [
    'Get'                        => ['POST','Get',[
        'dealerCode'=>$dealerCode,'id'=>$deviceId,
        'CustomerCode'=>$customerCode,'SerialNumber'=>$serialNumber,
        'AssetNumber'=>$assetNumber
    ]],
    'GetDeviceDashboard'         => ['GET',"GetDeviceDashboard?dealerId={$env['DEALER_ID']}&customerId={$device['Customer']['Id']}&deviceId={$deviceId}",null],
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
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,
        'DeviceId'=>$deviceId
    ]],
    'GetDeviceChart'             => ['POST','GetDeviceChart',[
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,
        'DeviceId'=>$deviceId
    ]],
    'GetErrorsMessagesDataHistory'=> ['POST','GetErrorsMessagesDataHistory',[
        'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    'GetAttributesDataHistory'   => ['POST','GetAttributesDataHistory',[
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,
        'DeviceId'=>$deviceId
    ]],
    'GetDeviceActionsDashboard'  => ['POST','GetDeviceActionsDashboard',[
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'DeviceId'=>$deviceId,'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]]
];

foreach ($endpoints as $name => [$method,$path,$body]) {
    $out[$name] = callApi($method, $base.$path, $token, $body);
}

// 10. Cache & return combined
$response = json_encode($out, JSON_PRETTY_PRINT);
setCache($cacheKey, $response, 60);
echo $response;
