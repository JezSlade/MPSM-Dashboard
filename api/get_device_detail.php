<?php
// api/get_device_detail.php
// Fetch full device detail by deviceId, externalIdentifier, or serialNumber
// Includes on-demand debug output via ?debug=1

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

// 2. Redis cache helper
require_once __DIR__ . '/../includes/redis.php';

// 3. Fetch OAuth token
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

// 4. Read + normalize input
$raw = json_decode(file_get_contents('php://input'), true) ?: $_GET;
$in = [];
foreach ($raw as $k => $v) {
    $in[strtolower($k)] = is_string($v) ? trim($v) : $v;
}
// uppercase identifiers
if (isset($in['externalidentifier'])) {
    $in['externalidentifier'] = strtoupper($in['externalidentifier']);
}
if (isset($in['serialnumber'])) {
    $in['serialnumber'] = strtoupper($in['serialnumber']);
}

// 5. Resolve deviceId
$deviceId = $in['deviceid'] ?? null;
if (!$deviceId && (!empty($in['externalidentifier']) || !empty($in['serialnumber']))) {
    $filter = [];
    if (!empty($in['externalidentifier'])) {
        $filter['ExternalIdentifier'] = $in['externalidentifier'];
    } elseif (!empty($in['serialnumber'])) {
        $filter['SerialNumber'] = $in['serialnumber'];
    }
    // debug injection: if debug=1, show filter and exit
    if (!empty($in['debug'])) {
        echo json_encode([
            'lookupBody'     => [
                'PageNumber'=>1,
                'PageRows'  =>1,
                'SortColumn'=>'Id',
                'SortOrder' =>'Asc',
                'Filter'    =>$filter
            ]
        ], JSON_PRETTY_PRINT);
        exit;
    }
    $base = rtrim($env['API_BASE_URL'],'/').'/Device/';
    $lookupBody = [
        'PageNumber'=>1,
        'PageRows'  =>1,
        'SortColumn'=>'Id',
        'SortOrder' =>'Asc',
        'Filter'    =>$filter
    ];
    $resp = callApi('POST',$base.'GetDevices',$token,$lookupBody);
    // debug injection: if debug=1, show full lookup response and exit
    if (!empty($in['debug'])) {
        echo json_encode([
            'lookupBody'     =>$lookupBody,
            'lookupResponse' =>$resp
        ], JSON_PRETTY_PRINT);
        exit;
    }
    $first = $resp['data']['Devices'][0] ?? null;
    if (!empty($first['Id'])) {
        $deviceId = $first['Id'];
    }
}

if (!$deviceId) {
    http_response_code(400);
    echo json_encode(['error'=>'Missing or invalid device identifier']);
    exit;
}

// 6. cURL helper: wrap body under "request"
function callApi(string $method,string $url,string $token,array $body=null) {
    $ch = curl_init($url);
    $hdr = ["Authorization: Bearer $token","Accept: application/json"];
    if ($body !== null) {
        $hdr[] = "Content-Type: application/json";
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['request'=>$body]));
        curl_setopt($ch, CURLOPT_POST, true);
    } elseif ($method!=='GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_HTTPHEADER   =>$hdr
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

// 7. Redis cache
$cacheKey = "mpsm:device_detail:{$deviceId}";
if ($cached = getCache($cacheKey)) {
    echo $cached;
    exit;
}

// 8. Fan-out all Device/* endpoints
$base      = rtrim($env['API_BASE_URL'],'/').'/Device/';
$endpoints = [
    'Get'                          => ['POST','Get',['Id'=>$deviceId]],
    'GetDeviceDashboard'           => ['GET',"GetDeviceDashboard?id={$deviceId}",null],
    'GetDevices'                   => ['POST','GetDevices',['DeviceIds'=>[$deviceId]]],
    'GetDeviceAlerts'              => ['POST','GetDeviceAlerts',['DeviceId'=>$deviceId]],
    'GetDevicesCount'              => ['POST','GetDevicesCount',['DeviceId'=>$deviceId]],
    'GetAvailableSupplies'         => ['POST','GetAvailableSupplies',['DeviceId'=>$deviceId]],
    'GetSupplyAlerts'              => ['POST','GetSupplyAlerts',['DeviceId'=>$deviceId]],
    'GetMaintenanceAlerts'         => ['POST','GetMaintenanceAlerts',['DeviceId'=>$deviceId]],
    'GetDeviceDataHistory'         => ['POST','GetDeviceDataHistory',['DeviceId'=>$deviceId]],
    'GetDeviceChart'               => ['POST','GetDeviceChart',['DeviceId'=>$deviceId]],
    'GetErrorsMessagesDataHistory' => ['POST','GetErrorsMessagesDataHistory',['DeviceId'=>$deviceId]],
    'GetAttributesDataHistory'     => ['POST','GetAttributesDataHistory',['DeviceId'=>$deviceId]],
    'GetDeviceActionsDashboard'    => ['POST','GetDeviceActionsDashboard',['DeviceId'=>$deviceId]],
];

ob_start();
$result = ['deviceId'=>$deviceId];
foreach ($endpoints as $name=>[$m,$p,$b]) {
    $result[$name] = callApi($m, $base.$p, $token, $b);
}
echo json_encode($result, JSON_PRETTY_PRINT);

// 9. Cache & return
$output = ob_get_clean();
setCache($cacheKey, $output, 60);
echo $output;
