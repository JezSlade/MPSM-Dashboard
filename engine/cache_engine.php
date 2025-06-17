<?php
// cache_engine.php
// Monolithic cache engine—streaming logs live and using exact api/*.php logic.
// --------------------------------------------------------------------

// Disable buffering/compression
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush',      1);
while (ob_get_level() > 0) ob_end_flush();
ob_implicit_flush(true);

// HTML header for live log
header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html><html><head><meta charset="utf-8"><title>Cache Engine Log</title>'
   .'<style>body{background:#111;color:#eee;font-family:monospace;padding:1rem}pre{margin:0}</style>'
   .'</head><body><pre>';

// logger
function logv(string $msg) {
    echo '['.date('H:i:s').'] '.$msg."\n";
    @ob_flush(); @flush();
}

// 1) Load .env
$envFile = realpath(__DIR__ . '/../.env');
logv("Loading .env from {$envFile}");
if (! $envFile || ! is_readable($envFile)) {
    logv("ERROR: .env not found or unreadable");
    exit('</pre></body></html>');
}
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    [$k,$v] = explode('=', $line, 2) + [null,''];
    $env[trim($k)] = trim($v);
    logv("Loaded env {$k}");
}

// 2) Config
$BASE_URL      = rtrim($env['API_BASE_URL'] ?? '', '/') . '/';
$TOKEN_URL     = $env['TOKEN_URL']       ?? '';
$USERNAME      = $env['USERNAME']        ?? '';
$PASSWORD      = $env['PASSWORD']        ?? '';
$CLIENT_ID     = $env['CLIENT_ID']       ?? '';
$CLIENT_SECRET = $env['CLIENT_SECRET']   ?? '';
$SCOPE         = $env['SCOPE']           ?? '';
$DEALER_CODE   = $env['DEALER_CODE']     ?? '';
$DEALER_ID     = $env['DEALER_ID']       ?? '';

logv("Configured BASE_URL: {$BASE_URL}");
logv("Configured TOKEN_URL: {$TOKEN_URL}");

// 3) Get OAuth token
function getToken() {
    global $TOKEN_URL, $USERNAME, $PASSWORD, $CLIENT_ID, $CLIENT_SECRET, $SCOPE;
    logv("Requesting access token");
    $ch = curl_init($TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'grant_type'    => 'password',
            'username'      => $USERNAME,
            'password'      => $PASSWORD,
            'client_id'     => $CLIENT_ID,
            'client_secret' => $CLIENT_SECRET,
            'scope'         => $SCOPE,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    logv("Token endpoint HTTP {$code}");
    $j = json_decode($resp, true);
    if (isset($j['access_token'])) {
        logv("Token acquired");
        return $j['access_token'];
    }
    logv("Failed to parse token response: ".substr($resp,0,200).'…');
    return null;
}

// 4) POST helper
function fetchPost(string $endpoint, array $body, string $token): array {
    global $BASE_URL;
    $url = $BASE_URL . ltrim($endpoint, '/');
    logv("POST {$url}");
    logv("  Payload: " . json_encode($body));
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer {$token}",
            "Content-Type: application/json",
            "Accept: application/json",
        ],
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    logv("  HTTP {$code}, ".strlen($resp)." bytes");
    return json_decode($resp, true) ?: [];
}

// 5) Begin caching
logv("=== BEGIN CACHING ===");
$token = getToken();
if (! $token) {
    logv("ERROR: Cannot obtain token, aborting.");
    exit('</pre></body></html>');
}

// ensure cache dir at project root
$cacheDir = dirname(__DIR__) . '/cache';
if (! is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
    logv("Created cache dir: {$cacheDir}");
}
logv("Using cache dir: {$cacheDir}");

// 6) Exact payloads from your api/*.php

// Customers: get_customers.php
$customersPayload = [
    'DealerCode'   => $DEALER_CODE,
    'Code'         => null,
    'HasHpSds'     => null,
    'FilterText'   => null,
    'PageNumber'   => 1,
    'PageRows'     => 2147483647,
    'SortColumn'   => 'Id',
    'SortOrder'    => 0,
];
$customers = fetchPost('Customer/GetCustomers', $customersPayload, $token);
file_put_contents("{$cacheDir}/Customers.json", json_encode($customers, JSON_PRETTY_PRINT));
logv("Wrote Customers.json");

// Devices: get_devices.php uses /Device/List
$devicesPayload = [
    'FilterDealerId'       => $DEALER_ID,
    'FilterCustomerCodes'  => [],            // all customers
    'ProductBrand'         => null,
    'ProductModel'         => null,
    'OfficeId'             => null,
    'Status'               => 1,
    'FilterText'           => null,
    'PageNumber'           => 1,
    'PageRows'             => 2147483647,
    'SortColumn'           => 'Id',
    'SortOrder'            => 0,
];
$devices = fetchPost('Device/List', $devicesPayload, $token);
file_put_contents("{$cacheDir}/Devices.json", json_encode($devices, JSON_PRETTY_PRINT));
logv("Wrote Devices.json");

// Alerts: get_device_alerts.php uses /SupplyAlert/List
$alertsPayload = [
    'CustomerCode' => $env['CUSTOMER_CODE'] ?? null,
    'PageNumber'   => 1,
    'PageRows'     => 2147483647,
    'SortColumn'   => 'CreationDate',
    'SortOrder'    => 1,
];
$alerts = fetchPost('SupplyAlert/List', $alertsPayload, $token);
file_put_contents("{$cacheDir}/DeviceAlerts.json", json_encode($alerts, JSON_PRETTY_PRINT));
logv("Wrote DeviceAlerts.json");

// Counters: get_device_counters.php
$countersPayload = [
    'FilterDealerId'       => $DEALER_ID,
    'FilterCustomerCodes'  => [], 
    'PageNumber'           => 1,
    'PageRows'             => 2147483647,
    'SortColumn'           => 'Id',
    'SortOrder'            => 0,
];
$counters = fetchPost('Counter/List', $countersPayload, $token);
file_put_contents("{$cacheDir}/DeviceCounters.json", json_encode($counters, JSON_PRETTY_PRINT));
logv("Wrote DeviceCounters.json");

// 7) Per-device detail (get_device_detail.php is placeholder, so using /Device/GetDevice)
$detailData = [];
$list = $devices['items'] ?? $devices['results'] ?? [];
logv("Fetching detail for ".count($list)." devices");
foreach ($list as $dev) {
    $id = $dev['Id'] ?? $dev['id'] ?? $dev['externalIdentifier'] ?? null;
    if (! $id) continue;
    logv(" - Device ID: {$id}");
    $detailPayload = ['dealerCode' => $DEALER_CODE, 'id' => $id];
    $detail = fetchPost('Device/GetDevice', $detailPayload, $token);
    $detailData[$id] = $detail;
    usleep(200000);
}
file_put_contents("{$cacheDir}/DeviceDetail.json", json_encode($detailData, JSON_PRETTY_PRINT));
logv("Wrote DeviceDetail.json");

// 8) Done
logv("=== CACHE COMPLETE ===");
echo '</pre></body></html>';
