<?php
// api/get_device_detail.php
// Fetch full device detail for one device by serialNumber or externalIdentifier
// (With hard-coded externalIdentifier “DM060” for debugging)

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// 1. Redis helper
require_once __DIR__ . '/../includes/redis.php';

// 2. Load .env (self-contained)
if (!function_exists('load_env')) {
    function load_env($path = __DIR__ . '/../.env') {
        $env = [];
        if (!file_exists($path)) return $env;
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            [$key, $val] = explode('=', $line, 2) + [null,null];
            if ($key) $env[trim($key)] = trim($val);
        }
        return $env;
    }
}
$env = load_env();

// 3. Acquire OAuth token (self-contained)
if (!function_exists('get_token')) {
    function get_token(array $env) {
        $required = ['CLIENT_ID','CLIENT_SECRET','USERNAME','PASSWORD','SCOPE','TOKEN_URL'];
        foreach ($required as $k) {
            if (empty($env[$k])) {
                error_log("Missing $k in .env", 3, __DIR__ . '/../logs/debug.log');
                http_response_code(500);
                echo json_encode(['error'=>"Configuration Error: Missing $k in .env"]);
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
            error_log("Token request failed ($code): ".print_r($json,true), 3, __DIR__ . '/../logs/debug.log');
            http_response_code(401);
            echo json_encode(['error'=>'Token request failed','details'=>$json]);
            exit;
        }
        return $json['access_token'];
    }
}
$token = get_token($env);

// 4. Read & normalize input
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE || empty($in)) {
    $in = $_GET;
}
$normalized = [];
foreach ($in as $k => $v) {
    $normalized[strtolower($k)] = is_string($v) ? trim($v) : $v;
}
$in = $normalized;

// DEBUG hack: hard-code DM060
$in['externalidentifier'] = 'DM060';
$in['assetnumber']       = 'DM060';

// map externalIdentifier → AssetNumber
if (isset($in['externalidentifier'])) {
    $in['assetnumber'] = strtoupper($in['externalidentifier']);
}
// uppercase serialNumber
if (isset($in['serialnumber'])) {
    $in['serialnumber'] = strtoupper($in['serialnumber']);
}

// 5. cURL helper (direct root payload)
if (!function_exists('callApi')) {
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
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }
        } elseif ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }
        }
        $rsp = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($rsp, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error for $url: $rsp", 3, __DIR__.'/../logs/debug.log');
            return ['status'=>$status,'raw'=>$rsp,'error'=>'JSON decode failed'];
        }
        return $data;
    }
}

// 6. Redis cache
$cacheKey = 'mpsm:api:get_device_detail:' . md5(json_encode($in));
if ($cached = getCache($cacheKey)) {
    echo $cached;
    exit;
}

ob_start();

// 7. Lookup via POST /Device/Get
$deviceBase = rtrim($env['API_BASE_URL'],'/').'/Device/';
$lookupBody = [
    'DealerCode'   => $env['DEALER_CODE']   ?? '',
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

$respGet = callApi('POST', $deviceBase.'Get', $token, $lookupBody);
if (empty($respGet['Result']) || !is_array($respGet['Result'])) {
    http_response_code(404);
    echo json_encode(['error'=>'Device not found','lookup'=>$respGet], JSON_PRETTY_PRINT);
    exit;
}
$device = $respGet['Result'];

// 8. Extract IDs & codes
$deviceId     = $device['Id'];
$serialNumber = $device['SerialNumber'] ?? '';
$assetNumber  = $device['AssetNumber']  ?? '';
$dealerCode   = $device['Dealer']['Code']   ?? $env['DEALER_CODE']   ?? '';
$customerCode = $device['Customer']['Code'] ?? $env['CUSTOMER_CODE'] ?? '';
$dealerId     = $device['Dealer']['Id']     ?? $env['DEALER_ID']     ?? null;

// 9. Fan-out all endpoints
$output = ['device'=>$device];
$endpoints = [
    'GetDeviceDashboard'         => ['GET',  $deviceBase."GetDeviceDashboard?dealerId={$dealerId}&customerId={$device['Customer']['Id']}&deviceId={$deviceId}", null],
    'GetDevices'                 => ['POST', $deviceBase.'GetDevices', [
        'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'Id','SortOrder'=>'Asc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'Search'=>$serialNumber
    ]],
    'GetDeviceAlerts'            => ['POST', $deviceBase.'GetDeviceAlerts', [
        'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    'GetDevicesCount'            => ['POST', $deviceBase.'GetDevicesCount', [
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode
    ]],
    'GetAvailableSupplies'       => ['POST', $deviceBase.'GetAvailableSupplies', [
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    'GetSupplyAlerts'            => ['POST', $deviceBase.'GetSupplyAlerts', [
        'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    'GetMaintenanceAlerts'       => ['POST', $deviceBase.'GetMaintenanceAlerts', [
        'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    'GetDeviceDataHistory'       => ['POST', $deviceBase.'GetDeviceDataHistory', [
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,'DeviceId'=>$deviceId
    ]],
    'GetDeviceChart'             => ['POST', $deviceBase.'GetDeviceChart', [
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,'DeviceId'=>$deviceId
    ]],
    'GetErrorsMessagesDataHistory'=>['POST',$deviceBase.'GetErrorsMessagesDataHistory', [
        'PageNumber'=>1,'PageRows'=>100,'SortColumn'=>'InitialDate','SortOrder'=>'Desc',
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber
    ]],
    'GetAttributesDataHistory'   =>['POST',$deviceBase.'GetAttributesDataHistory', [
        'DealerCode'=>$dealerCode,'CustomerCode'=>$customerCode,
        'SerialNumber'=>$serialNumber,'AssetNumber'=>$assetNumber,'DeviceId'=>$deviceId
    ]],
    'GetDeviceActionsDashboard'  =>['GET', $deviceBase."GetDeviceActionsDashboard?deviceId={$deviceId}&dealerId={$dealerId}", null]
];

foreach ($endpoints as $name => [$method,$url,$body]) {
    $output[$name] = callApi($method, $url, $token, $body);
}

// 10. Cache & return
$response = json_encode($output, JSON_PRETTY_PRINT);
setCache($cacheKey, $response, 60);
echo $response;

ob_end_flush();
