<?php
// api/get_device_detail.php
// Fetch comprehensive data for one device across all Device/* endpoints

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// Load .env into $env
if (!function_exists('load_env')) {
    function load_env($path = __DIR__ . '/../.env') {
        $env = [];
        if (!file_exists($path)) return $env;
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            [$key, $val] = explode('=', $line, 2);
            $env[trim($key)] = trim($val);
        }
        return $env;
    }
}
$env = load_env();

// Obtain OAuth token
if (!function_exists('get_token')) {
    function get_token(array $env) {
        $required = ['CLIENT_ID','CLIENT_SECRET','USERNAME','PASSWORD','SCOPE','TOKEN_URL'];
        foreach ($required as $key) {
            if (empty($env[$key])) {
                echo json_encode(['error'=>"Missing $key in .env"]);
                exit;
            }
        }
        $post = http_build_query([
            'grant_type'    => 'password',
            'client_id'     => $env['CLIENT_ID'],
            'client_secret' => $env['CLIENT_SECRET'],
            'username'      => $env['USERNAME'],
            'password'      => $env['PASSWORD'],
            'scope'         => $env['SCOPE']
        ]);
        $ch = curl_init($env['TOKEN_URL']);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
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
}
$token = get_token($env);

// Require a device id (or fallback to DEFAULT_DEVICE_ID in .env)
$deviceId = $_GET['id'] ?? ($env['DEFAULT_DEVICE_ID'] ?? null);
if (!$deviceId) {
    http_response_code(400);
    echo json_encode(['error'=>'Missing device id']);
    exit;
}

// Helper to call each Device API
function callApi(string $method, string $url, string $token, array $body = null) {
    $ch = curl_init($url);
    $hdr = ["Authorization: Bearer $token", "Accept: application/json"];
    if ($body !== null) {
        $hdr[] = "Content-Type: application/json";
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['request'=>$body]));
        curl_setopt($ch, CURLOPT_POST, true);
    } elseif ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $hdr,
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

// Base URL from .env
$base = rtrim($env['API_BASE_URL'], '/').'/Device/';

// Define each endpoint call
$calls = [
    'Get'                          => ['method'=>'POST','path'=>'Get','body'=>['Id'=>$deviceId]],
    'GetDeviceDashboard'           => ['method'=>'GET', 'path'=>"GetDeviceDashboard?id={$deviceId}"],
    'GetDevices'                   => ['method'=>'POST','path'=>'GetDevices','body'=>['DeviceIds'=>[$deviceId]]],
    'GetDeviceAlerts'              => ['method'=>'POST','path'=>'GetDeviceAlerts','body'=>['DeviceId'=>$deviceId]],
    'GetDevicesCount'              => ['method'=>'POST','path'=>'GetDevicesCount','body'=>['DeviceId'=>$deviceId]],
    'GetAvailableSupplies'         => ['method'=>'POST','path'=>'GetAvailableSupplies','body'=>['DeviceId'=>$deviceId]],
    'GetSupplyAlerts'              => ['method'=>'POST','path'=>'GetSupplyAlerts','body'=>['DeviceId'=>$deviceId]],
    'GetMaintenanceAlerts'         => ['method'=>'POST','path'=>'GetMaintenanceAlerts','body'=>['DeviceId'=>$deviceId]],
    'GetDeviceDataHistory'         => ['method'=>'POST','path'=>'GetDeviceDataHistory','body'=>['DeviceId'=>$deviceId]],
    'GetDeviceChart'               => ['method'=>'POST','path'=>'GetDeviceChart','body'=>['DeviceId'=>$deviceId]],
    'GetErrorsMessagesDataHistory' => ['method'=>'POST','path'=>'GetErrorsMessagesDataHistory','body'=>['DeviceId'=>$deviceId]],
    'GetAttributesDataHistory'     => ['method'=>'POST','path'=>'GetAttributesDataHistory','body'=>['DeviceId'=>$deviceId]],
    'GetDeviceActionsDashboard'    => ['method'=>'POST','path'=>'GetDeviceActionsDashboard','body'=>['DeviceId'=>$deviceId]],
];

// Aggregate and output
$output = ['deviceId'=>$deviceId];
foreach ($calls as $name => $cfg) {
    $url = $cfg['method']==='GET' ? $base.$cfg['path'] : $base.$cfg['path'];
    $output[$name] = callApi($cfg['method'], $url, $token, $cfg['body'] ?? null);
}

echo json_encode($output, JSON_PRETTY_PRINT);
