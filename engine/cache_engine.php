<?php
// cache_engine.php
// Monolithic cache engine for pre-fetching all key MPS Monitor API data,
// streaming verbose logs to the browser window, with proper POST usage
// and per-device detail fetching.
// --------------------------------------------------------------------

// Disable buffering/compression
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush',      1);
while (ob_get_level() > 0) ob_end_flush();
ob_implicit_flush(true);

// HTML header
header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html><html><head><meta charset="utf-8"><title>Cache Engine</title>
<style>body{background:#111;color:#eee;font-family:monospace;padding:1rem}pre{margin:0}</style>
</head><body><pre>';

// logger
function logv($msg) {
    echo '['.date('H:i:s').'] '.$msg."\n";
    @ob_flush(); @flush();
}

// 1) load .env
$envPath = realpath(__DIR__ . '/../.env');
logv("Loading .env from {$envPath}");
if (! $envPath || ! is_readable($envPath)) {
    logv("ERROR: .env missing");
    exit('</pre></body></html>');
}
$env = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    [$k, $v] = explode('=', $line, 2) + [null,null];
    if ($k) { $env[trim($k)] = trim($v); logv("Loaded env {$k}"); }
}

// 2) config
$BASE_URL      = rtrim($env['API_BASE_URL'] ?? '', '/') . '/';
$TOKEN_URL     = $env['TOKEN_URL']         ?? '';
$USERNAME      = $env['USERNAME']          ?? '';
$PASSWORD      = $env['PASSWORD']          ?? '';
$CLIENT_ID     = $env['CLIENT_ID']         ?? '';
$CLIENT_SECRET = $env['CLIENT_SECRET']     ?? '';
$SCOPE         = $env['SCOPE']             ?? '';
$DEALER_CODE   = $env['DEALER_CODE']       ?? '';

logv("BASE_URL: {$BASE_URL}");

// 3) get token
function getToken() {
    global $TOKEN_URL, $USERNAME, $PASSWORD, $CLIENT_ID, $CLIENT_SECRET, $SCOPE;
    logv("Requesting token");
    $ch = curl_init($TOKEN_URL);
    curl_setopt_array($ch,[
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>http_build_query([
            'grant_type'=>'password','username'=>$USERNAME,
            'password'=>$PASSWORD,'client_id'=>$CLIENT_ID,
            'client_secret'=>$CLIENT_SECRET,'scope'=>$SCOPE
        ]),
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $r = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    logv("Token HTTP {$code}");
    $j = json_decode($r,true);
    return $j['access_token'] ?? null;
}

// 4) POST helper
function fetchPost($endpoint, $bodyArr, $token) {
    global $BASE_URL;
    $url = $BASE_URL . ltrim($endpoint,'/');
    logv("POST {$url}");
    $ch = curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>json_encode($bodyArr),
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_HTTPHEADER=>[
            "Authorization: Bearer {$token}",
            "Content-Type: application/json"
        ],
    ]);
    $r = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    logv("  HTTP {$code}, ".strlen($r)." bytes");
    return json_decode($r,true);
}

// 5) browser-ready
logv("=== BEGIN ===");
$token = getToken();
if (! $token) exit(logv("ERROR: no token").'</pre></body></html>');

// cache dir
$cacheDir = dirname(__DIR__).'/cache';
logv("Cache dir: {$cacheDir}");
if (! is_dir($cacheDir)) mkdir($cacheDir,0755,true);

// 6) cache Customers
$customers = fetchPost('Customer/GetCustomers', [
    'dealerCode'=>$DEALER_CODE,'pageNumber'=>1,'pageRows'=>1000
], $token);
file_put_contents("{$cacheDir}/Customers.json", json_encode($customers, JSON_PRETTY_PRINT));
logv("Wrote Customers.json");

// 7) cache Devices (POST)
$devices = fetchPost('Device/GetDevices', [
    'dealerCode'=>$DEALER_CODE,'pageNumber'=>1,'pageRows'=>1000
], $token);
file_put_contents("{$cacheDir}/Devices.json", json_encode($devices, JSON_PRETTY_PRINT));
logv("Wrote Devices.json");

// 8) cache Alerts (POST)
$alerts = fetchPost('Device/GetDeviceAlerts', [
    'dealerCode'=>$DEALER_CODE,'pageNumber'=>1,'pageRows'=>1000
], $token);
file_put_contents("{$cacheDir}/DeviceAlerts.json", json_encode($alerts, JSON_PRETTY_PRINT));
logv("Wrote DeviceAlerts.json");

// 9) cache Counters (POST)
$counters = fetchPost('Counter/List', [
    'dealerCode'=>$DEALER_CODE,'pageNumber'=>1,'pageRows'=>1000
], $token);
file_put_contents("{$cacheDir}/DeviceCounters.json", json_encode($counters, JSON_PRETTY_PRINT));
logv("Wrote DeviceCounters.json");

// 10) cache per-device detail
$details = [];
foreach (($devices['items'] ?? $devices['results'] ?? []) as $d) {
    $id = $d['id'] ?? $d['Id'] ?? $d['externalIdentifier'] ?? null;
    if (! $id) continue;
    logv("Fetching detail for device {$id}");
    $dt = fetchPost('Device/GetDevice', [
        'dealerCode'=>$DEALER_CODE,'id'=>$id
    ], $token);
    $details[$id] = $dt;
    usleep(200000);
}
file_put_contents("{$cacheDir}/DeviceDetail.json", json_encode($details, JSON_PRETTY_PRINT));
logv("Wrote DeviceDetail.json");

// done
logv("=== COMPLETE ===");
echo '</pre></body></html>';
