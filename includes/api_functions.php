<?php
// includes/api_functions.php
// Shared helpers for ALL api/*.php endpoints

// —————————————————————————————————————————————————————
// Basic setup & error logging
// —————————————————————————————————————————————————————
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors',   '1');
ini_set('log_errors',       '1');
ini_set('error_log',        __DIR__ . '/../logs/debug.log');

// —————————————————————————————————————————————————————
// Redis cache helpers (wrap your existing redis.php)
// —————————————————————————————————————————————————————
require_once __DIR__ . '/redis.php';
function get_cache(string $key)    { return getCache($key); }
function set_cache(string $key, string $val, int $ttl) { setCache($key, $val, $ttl); }

// —————————————————————————————————————————————————————
// Load .env
// —————————————————————————————————————————————————————
function load_env($path = __DIR__ . '/../.env') {
    $env = [];
    if (!file_exists($path)) return $env;
    foreach (@file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$k, $v] = explode('=', $line, 2) + [null,null];
        if ($k) $env[trim($k)] = trim($v);
    }
    return $env;
}
function env(): array {
    static $e = null;
    return $e ?? ($e = load_env());
}

// —————————————————————————————————————————————————————
// OAuth token (identical to your existing get_token.php)
// —————————————————————————————————————————————————————
function get_token(): string {
    $e = env();
    foreach (['CLIENT_ID','CLIENT_SECRET','USERNAME','PASSWORD','SCOPE','TOKEN_URL'] as $k) {
        if (empty($e[$k])) {
            error_log("Missing $k in .env", 3, __DIR__.'/../logs/debug.log');
            http_response_code(500);
            echo json_encode(['error'=>"Configuration Error: Missing $k in .env"]);
            exit;
        }
    }
    $post = http_build_query([
        'grant_type'    => 'password',
        'client_id'     => $e['CLIENT_ID'],
        'client_secret' => $e['CLIENT_SECRET'],
        'username'      => $e['USERNAME'],
        'password'      => $e['PASSWORD'],
        'scope'         => $e['SCOPE'],
    ]);
    $ch = curl_init($e['TOKEN_URL']);
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
        http_response_code(401);
        echo json_encode(['error'=>'Token request failed','details'=>$json]);
        exit;
    }
    return $json['access_token'];
}

// —————————————————————————————————————————————————————
// Generic callApi wrapper (matches all your working files)
// —————————————————————————————————————————————————————
function call_api(string $method, string $path, array $body = null) {
    $e     = env();
    $token = get_token();
    $url   = rtrim($e['API_BASE_URL'], '/') . '/' . ltrim($path, '/');
    $ch    = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        "Authorization: Bearer $token"
    ]);

    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['request'=>$body]));
    } elseif (strtoupper($method) !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['request'=>$body]));
        }
    }

    $rsp    = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($rsp, true);
    return $data !== null ? $data : ['status'=>$status,'raw'=>$rsp];
}

// —————————————————————————————————————————————————————
// Business-level helpers (these can call each other!)
// —————————————————————————————————————————————————————

/**
 * 1) get_customers: no inputs, returns array of CustomerBaseDto
 */
function get_customers(): array {
    $e = env();
    $resp = call_api('POST', 'Customer/GetCustomers', [
        'DealerCode' => $e['DEALER_CODE'] ?? ''
    ]);
    return $resp['Result'] ?? [];
}

/**
 * 2) get_devices: needs a customerCode, returns array of DeviceDto
 */
function get_devices(string $customerCode): array {
    $e = env();
    $resp = call_api('POST', 'Device/GetDevices', [
        'PageNumber'   => 1,
        'PageRows'     => 100,
        'SortColumn'   => 'Id',
        'SortOrder'    => 'Asc',
        'DealerCode'   => $e['DEALER_CODE']   ?? '',
        'CustomerCode' => $customerCode
    ]);
    return $resp['Result'] ?? [];
}

/**
 * 3) get_device_counters: needs a deviceId, returns whatever your Counter/ListDetailed returns
 */
function get_device_counters(string $deviceId): array {
    return call_api('POST', 'Counter/ListDetailed', [
        'DeviceIds' => [$deviceId]
    ]);
}

/**
 * 4) get_device_by_external: two-step lookup
 */
function get_device_by_external(string $extId): ?array {
    // lookup via GetDevices → extract Id
    $list = call_api('POST', 'Device/GetDevices', [
      'PageNumber'   => 1,
      'PageRows'     => 1,
      'SortColumn'   => 'Id',
      'SortOrder'    => 'Asc',
      'DealerCode'   => env()['DEALER_CODE']   ?? '',
      'CustomerCode' => env()['CUSTOMER_CODE'] ?? '',
      'Search'       => strtoupper($extId)
    ])['Result'] ?? [];

    if (empty($list[0]['Id'])) return null;
    $id = $list[0]['Id'];

    // fetch full DeviceDto
    $full = call_api('POST', 'Device/Get', [
      'DealerCode' => env()['DEALER_CODE'] ?? '',
      'Id'         => $id
    ])['Result'] ?? null;

    return is_array($full) ? $full : null;
}
