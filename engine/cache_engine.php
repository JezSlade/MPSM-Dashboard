<?php
// cache_engine.php
// Monolithic cache engine—streaming live logs in browser, iterating all customers/devices,
// showing on-screen snapshots of each API response, with exact endpoint names.
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

// simple logger
function logv(string $msg) {
    echo '['.date('H:i:s').'] '.$msg."\n";
    @ob_flush(); @flush();
}
// helper to snapshot first N items
function snapshot(array $data, int $n = 3) {
    $items = $data['items'] ?? $data['results'] ?? $data;
    return array_slice($items, 0, $n);
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
    return $j['access_token'] ?? null;
}

// 4) POST helper
function fetchPost(string $endpoint, array $body, string $token): array {
    global $BASE_URL;
    $url = $BASE_URL . ltrim($endpoint, '/');
    logv("POST {$url}");
    logv("  Body: " . json_encode($body));
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
    logv("ERROR: cannot obtain token, aborting");
    exit('</pre></body></html>');
}

// ensure cache dir
$cacheDir = dirname(__DIR__) . '/cache';
if (! is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
    logv("Created cache dir: {$cacheDir}");
}
logv("Using cache dir: {$cacheDir}");

// 6) Customers
$cp = [
    'DealerCode'=> $DEALER_CODE,
    'Code'=> null,
    'HasHpSds'=> null,
    'FilterText'=> null,
    'PageNumber'=>1,
    'PageRows'=>2147483647,
    'SortColumn'=>'Id',
    'SortOrder'=>0,
];
logv("-- Caching Customers --");
$customers = fetchPost('Customer/GetCustomers', $cp, $token);
file_put_contents("{$cacheDir}/Customers.json", json_encode($customers, JSON_PRETTY_PRINT));
logv("Wrote Customers.json");
logv("Snapshot Customers:"); print_r(snapshot($customers));

// 7) Devices for all customers
$allDevices = [];
$custList = $customers['items'] ?? $customers['results'] ?? [];
logv("Found ".count($custList)." customers; fetching devices");
foreach ($custList as $cust) {
    $code = $cust['Code'] ?? $cust['code'] ?? null;
    if (!$code) continue;
    logv(" - CustomerCode: {$code}");
    $dp = [
        'dealerCode'=> $DEALER_CODE,
        'customerCode'=> $code,
        'pageNumber'=>1,
        'pageRows'=>2147483647,
        'sortColumn'=>'Id',
        'sortOrder'=>0,
    ];
    $resp = fetchPost('Device/GetDevices', $dp, $token);
    $list = $resp['items'] ?? $resp['results'] ?? [];
    logv("   Retrieved ".count($list)." devices");
    foreach ($list as $d) {
        $id = $d['Id'] ?? $d['id'] ?? null;
        if ($id) $allDevices[$id] = $d;
    }
    usleep(200000);
}
file_put_contents("{$cacheDir}/Devices.json", json_encode(array_values($allDevices), JSON_PRETTY_PRINT));
logv("Wrote Devices.json");
logv("Snapshot Devices:"); print_r(array_slice(array_values($allDevices), 0, 3));

// 8) Alerts for all customers
$allAlerts = [];
logv("Fetching alerts for each customer");
foreach ($custList as $cust) {
    $code = $cust['Code'] ?? $cust['code'] ?? null;
    if (!$code) continue;
    $ap = [
        'dealerCode'=> $DEALER_CODE,
        'customerCode'=> $code,
        'pageNumber'=>1,
        'pageRows'=>2147483647,
        'sortColumn'=>'CreationDate',
        'sortOrder'=>1,
    ];
    $resp = fetchPost('Device/GetDeviceAlerts', $ap, $token);
    $list = $resp['items'] ?? $resp['results'] ?? [];
    logv("   Customer {$code}: ".count($list)." alerts");
    foreach ($list as $a) {
        $key = $a['Id'] ?? $a['id'] ?? uniqid();
        $allAlerts[$key] = $a;
    }
    usleep(200000);
}
file_put_contents("{$cacheDir}/DeviceAlerts.json", json_encode(array_values($allAlerts), JSON_PRETTY_PRINT));
logv("Wrote DeviceAlerts.json");
logv("Snapshot Alerts:"); print_r(array_slice(array_values($allAlerts), 0, 3));

// 9) Counters (single call)
$cpay = [
    'FilterDealerId'=> $DEALER_ID,
    'FilterCustomerCodes'=> [],
    'PageNumber'=>1,
    'PageRows'=>2147483647,
    'SortColumn'=>'Id',
    'SortOrder'=>0,
];
logv("-- Caching Counters --");
$counters = fetchPost('Counter/List', $cpay, $token);
file_put_contents("{$cacheDir}/DeviceCounters.json", json_encode($counters, JSON_PRETTY_PRINT));
logv("Wrote DeviceCounters.json");
logv("Snapshot Counters:"); print_r(snapshot($counters));

// 10) Detail for every device
logv("Fetching detail for ".count($allDevices)." devices");
$detailData = [];
foreach ($allDevices as $id => $_) {
    logv(" - Device ID: {$id}");
    $dpay = ['dealerCode'=>$DEALER_CODE,'id'=>$id];
    $detail = fetchPost('Device/GetDevice', $dpay, $token);
    $detailData[$id] = $detail;
    usleep(200000);
}
file_put_contents("{$cacheDir}/DeviceDetail.json", json_encode($detailData, JSON_PRETTY_PRINT));
logv("Wrote DeviceDetail.json");
logv("Snapshot Detail of first device:"); print_r(snapshot($detailData[array_key_first($detailData)] ?? []));

// 11) Done
logv("=== CACHE COMPLETE ===");
echo '</pre></body></html>';
