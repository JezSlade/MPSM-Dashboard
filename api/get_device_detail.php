<?php
// api/get_device_detail.php
// Fetch full device detail by deviceId, serialNumber or externalIdentifier

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('log_errors','1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// Redis helper
require_once __DIR__ . '/../includes/redis.php';

// 1. Load .env
function load_env($path = __DIR__ . '/../.env') {
    $env = [];
    if (!file_exists($path)) return $env;
    foreach (file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$k, $v] = explode('=', $line, 2) + [null,null];
        if ($k) $env[trim($k)] = trim($v);
    }
    return $env;
}
$env = load_env();

// 2. Get OAuth token
function get_token(array $env) {
    foreach (['CLIENT_ID','CLIENT_SECRET','USERNAME','PASSWORD','SCOPE','TOKEN_URL'] as $k) {
        if (empty($env[$k])) {
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
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
        ],
    ]);
    $rsp = curl_exec($ch);
    $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = json_decode($rsp, true);
    if ($code!==200 || empty($json['access_token'])) {
        http_response_code(401);
        echo json_encode(['error'=>'Token request failed','details'=>$json]);
        exit;
    }
    return $json['access_token'];
}
$token = get_token($env);

// 3. Read & normalize input
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true) ?: $_GET;
$norm = [];
foreach ($in as $k=>$v) {
    $lk = strtolower($k);
    $norm[$lk] = is_string($v) ? trim($v) : $v;
}
$in = $norm;

// map externalIdentifier → assetnumber
if (isset($in['externalidentifier'])) {
    $in['assetnumber'] = strtoupper($in['externalidentifier']);
}
// uppercase serialNumber
if (isset($in['serialnumber'])) {
    $in['serialnumber'] = strtoupper($in['serialnumber']);
}

// 4. HTTP helper
function callApi($method, $url, $token, $body=null) {
    $ch = curl_init($url);
    $hdr = [
        "Accept: application/json",
        "Authorization: Bearer $token"
    ];
    if ($body !== null) {
        $hdr[] = "Content-Type: application/json";
    }
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => $hdr
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    $rsp = curl_exec($ch);
    $status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($rsp,true);
    return $data === null ? ['status'=>$status,'raw'=>$rsp,'error'=>'JSON decode failed'] : $data;
}

// 5. Caching
$cacheKey = 'mpsm:api:get_device_detail:' . md5(json_encode($in));
if ($cached = getCache($cacheKey)) {
    echo $cached;
    exit;
}
ob_start();

// Dealer + API base
$dealerId   = $env['DEALER_ID']   ?? '';
$dealerCode = $env['DEALER_CODE'] ?? '';
$base       = rtrim($env['API_BASE_URL'],'/') . '/Device/';

// 6. Resolve deviceId
if (!empty($in['deviceid'])) {
    $deviceId = $in['deviceid'];

} elseif (!empty($in['serialnumber']) || !empty($in['assetnumber'])) {
    $searchVal = $in['serialnumber'] ?? $in['assetnumber'];
    // POST /Device/GetDevices with FilterDealerId + FilterText
    $lookup = callApi('POST', $base.'GetDevices', $token, [
        'PageNumber'       => 1,
        'PageRows'         => 1,
        'SortColumn'       => 'Id',
        'SortOrder'        => 'Asc',
        'FilterDealerId'   => $dealerId,
        'FilterText'       => $searchVal
    ]);
    if (empty($lookup['Result'][0]['Id'])) {
        http_response_code(404);
        echo json_encode([
            'error'  => 'Device not found by externalIdentifier or serialNumber',
            'lookup' => $lookup
        ], JSON_PRETTY_PRINT);
        exit;
    }
    $deviceId = $lookup['Result'][0]['Id'];

} else {
    http_response_code(400);
    echo json_encode(['error'=>'Missing deviceId, serialNumber, or externalIdentifier'], JSON_PRETTY_PRINT);
    exit;
}

// 7. Fetch full device info
$respGet = callApi('POST', $base.'Get', $token, [
    'DealerCode' => $dealerCode,
    'Id'         => $deviceId
]);
if (empty($respGet['Result']['Id'])) {
    http_response_code(404);
    echo json_encode([
        'error'  => 'Device not found',
        'lookup' => $respGet
    ], JSON_PRETTY_PRINT);
    exit;
}
$device = $respGet['Result'];

// 8. Fan-out other endpoints (example: dashboard)
$output = ['device' => $device];
$output['GetDeviceDashboard'] = callApi(
    'GET',
    $base."GetDeviceDashboard?dealerId={$dealerId}&customerId={$device['Customer']['Id']}&deviceId={$deviceId}"
);
// …repeat for other endpoints as needed…

// 9. Cache & respond
$response = json_encode($output, JSON_PRETTY_PRINT);
setCache($cacheKey, $response, 60);
echo $response;
ob_end_flush();
