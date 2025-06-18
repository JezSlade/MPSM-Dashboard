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

// Load .env
function load_env($path=__DIR__.'/../.env') {
    $env = [];
    if (!file_exists($path)) return $env;
    foreach (file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
        if (trim($line)==='' || str_starts_with(trim($line),'#')) continue;
        [$k,$v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v);
    }
    return $env;
}
$env = load_env();

// OAuth token (same as your other files)
function get_token($env) {
    foreach (['CLIENT_ID','CLIENT_SECRET','USERNAME','PASSWORD','SCOPE','TOKEN_URL'] as $k) {
        if (empty($env[$k])) {
            error_log("Missing $k in .env\n", 3, __DIR__.'/../logs/debug.log');
            http_response_code(500);
            echo json_encode(['error'=>"Configuration Error: Missing $k"]);
            exit;
        }
    }
    $post = http_build_query([
        'grant_type'=>'password',
        'client_id'=>$env['CLIENT_ID'],
        'client_secret'=>$env['CLIENT_SECRET'],
        'username'=>$env['USERNAME'],
        'password'=>$env['PASSWORD'],
        'scope'=>$env['SCOPE']
    ]);
    $ch = curl_init($env['TOKEN_URL']);
    curl_setopt_array($ch, [
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>$post,
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_HTTPHEADER=>[
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ]
    ]);
    $rsp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = json_decode($rsp,true);
    if ($code!==200 || empty($json['access_token'])) {
        error_log("Token failed {$code} ".print_r($json,1)."\n",3,__DIR__.'/../logs/debug.log');
        http_response_code(401);
        echo json_encode(['error'=>'Token request failed','details'=>$json]);
        exit;
    }
    return $json['access_token'];
}
$token = get_token($env);

// Read & normalize input
$raw = file_get_contents('php://input');
$in   = json_decode($raw,true);
if (json_last_error()!==JSON_ERROR_NONE || empty($in)) {
    $in = $_GET;
}
$norm = [];
foreach ($in as $k=>$v) {
    $lk = strtolower($k);
    $norm[$lk] = is_string($v) ? trim($v) : $v;
}
$in = $norm;
if (isset($in['externalidentifier'])) {
    $in['assetnumber'] = strtoupper($in['externalidentifier']);
}
if (isset($in['serialnumber'])) {
    $in['serialnumber'] = strtoupper($in['serialnumber']);
}

// HTTP helper
function callApi($method, $url, $token, $body=null) {
    $ch = curl_init($url);
    $hdr = ["Accept: application/json", "Authorization: Bearer $token"];
    if ($body !== null) {
        $hdr[] = 'Content-Type: application/json';
    }
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_FOLLOWLOCATION=>true,
        CURLOPT_HTTPHEADER=>$hdr
    ]);
    if ($method==='POST') {
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
    if ($data === null && json_last_error()!==JSON_ERROR_NONE) {
        error_log("JSON parse error for $url: $rsp\n",3,__DIR__.'/../logs/debug.log');
        return ['status'=>$status,'raw'=>$rsp,'error'=>'JSON decode failed'];
    }
    return $data;
}

// Caching
$cacheKey = 'mpsm:api:get_device_detail:'.md5(json_encode($in));
if ($cached = getCache($cacheKey)) {
    echo $cached; exit;
}
ob_start();

// Dealer/Customer codes
$dealerCode   = $env['DEALER_CODE']   ?? '';
$customerCode = $env['CUSTOMER_CODE'] ?? '';
$base = rtrim($env['API_BASE_URL'],'/').'/Device/';

// 1) Resolve deviceId (via direct input or List lookup)
if (!empty($in['deviceid'])) {
    $deviceId = $in['deviceid'];
} elseif (!empty($in['serialnumber']) || !empty($in['assetnumber'])) {
    $search = $in['serialnumber'] ?? $in['assetnumber'];
    $listBody = [
        'DealerCode'   => $dealerCode,
        'CustomerCode' => $customerCode,
        'Search'       => $search,
        'PageNumber'   => 1,
        'PageRows'     => 1,
        'SortColumn'   => 'Id',
        'SortOrder'    => 'Asc'
    ];
    $respList = callApi('POST', $base.'List', $token, $listBody);
    if (empty($respList['Result']['Items'][0])) {
        http_response_code(404);
        echo json_encode([
            'error'  => 'Device not found by externalIdentifier or serialNumber',
            'lookup' => $respList
        ], JSON_PRETTY_PRINT);
        exit;
    }
    $found = $respList['Result']['Items'][0];
    $deviceId     = $found['Id'];
    $serialNumber = $found['SerialNumber'] ?? '';
    $assetNumber  = $found['AssetNumber']  ?? '';
} else {
    http_response_code(400);
    echo json_encode(['error'=>'Missing deviceId, serialNumber, or externalIdentifier'], JSON_PRETTY_PRINT);
    exit;
}

// 2) Fetch full details
$detailReq = ['DeviceId'=>$deviceId];
$respDet = callApi('POST', $base.'GetDetailedInformations', $token, $detailReq);
if (empty($respDet['Result'])) {
    http_response_code(404);
    echo json_encode([
        'error'         => 'Device details not found',
        'lookupResponse'=> $respDet
    ], JSON_PRETTY_PRINT);
    exit;
}

// 3) Return & cache
$response = json_encode($respDet, JSON_PRETTY_PRINT);
setCache($cacheKey, $response, 60);
echo $response;
ob_end_flush();
