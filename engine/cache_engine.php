<?php
// cache_engine.php
// Monolithic cache engine for pre-fetching all MPS Monitor API data,
// streaming logs live to the browser.
// --------------------------------------------------------------------

// 0) Turn off buffering/compression so logs flush immediately
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush',      1);
while (ob_get_level() > 0) ob_end_flush();
ob_implicit_flush(true);

// 1) HTML header for live log
header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html><html><head><meta charset="utf-8">'
   .'<title>Cache Engine Log</title>'
   .'<style>body{background:#111;color:#eee;font-family:monospace;padding:1rem}'
   .'pre{margin:0}</style></head><body><pre>';

// simple logger
function logv(string $msg) {
    echo '['.date('H:i:s').'] '.$msg."\n";
    @ob_flush(); @flush();
}

// 2) Load .env from project root
$envFile = realpath(__DIR__ . '/../.env');
logv("Loading .env from {$envFile}");
if (! $envFile || ! is_readable($envFile)) {
    logv("ERROR: .env not found");
    exit('</pre></body></html>');
}
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    [$k, $v] = explode('=', $line, 2) + [null, ''];
    $env[trim($k)] = trim($v);
    logv("Loaded env {$k}");
}

// 3) Configuration
$BASE_URL      = rtrim($env['API_BASE_URL'] ?? '', '/') . '/';
$TOKEN_URL     = $env['TOKEN_URL']       ?? '';
$USERNAME      = $env['USERNAME']        ?? '';
$PASSWORD      = $env['PASSWORD']        ?? '';
$CLIENT_ID     = $env['CLIENT_ID']       ?? '';
$CLIENT_SECRET = $env['CLIENT_SECRET']   ?? '';
$SCOPE         = $env['SCOPE']           ?? '';
$DEALER_CODE   = $env['DEALER_CODE']     ?? '';

logv("BASE_URL: {$BASE_URL}");
logv("TOKEN_URL: {$TOKEN_URL}");

// 4) Acquire OAuth token
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
    logv("Failed to parse token response");
    return null;
}

// 5) POST helper
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

// 6) Begin caching
logv("=== BEGIN CACHING ===");
$token = getToken();
if (! $token) {
    logv("ERROR: cannot obtain token, aborting");
    exit('</pre></body></html>');
}

// ensure cache directory exists at project root
$cacheDir = dirname(__DIR__) . '/cache';
logv("Using cache dir: {$cacheDir}");
if (! is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
    logv("Created cache dir");
}

// 7) Customers
$customers = fetchPost('Customer/GetCustomers', [
    'dealerCode' => $DEALER_CODE,
    'pageNumber' => 1,
    'pageRows'   => 1000,
], $token);
file_put_contents("{$cacheDir}/Customers.json", json_encode($customers, JSON_PRETTY_PRINT));
logv("Wrote Customers.json");

// 8) Devices
$devices = fetchPost('Device/GetDevices', [
    'dealerCode' => $DEALER_CODE,
    'pageNumber' => 1,
    'pageRows'   => 1000,
], $token);
file_put_contents("{$cacheDir}/Devices.json", json_encode($devices, JSON_PRETTY_PRINT));
logv("Wrote Devices.json");

// 9) DeviceAlerts
$alerts = fetchPost('Device/GetDeviceAlerts', [
    'dealerCode' => $DEALER_CODE,
    'pageNumber' => 1,
    'pageRows'   => 1000,
], $token);
file_put_contents("{$cacheDir}/DeviceAlerts.json", json_encode($alerts, JSON_PRETTY_PRINT));
logv("Wrote DeviceAlerts.json");

// 10) DeviceCounters
$counters = fetchPost('Counter/List', [
    'dealerCode' => $DEALER_CODE,
    'pageNumber' => 1,
    'pageRows'   => 1000,
], $token);
file_put_contents("{$cacheDir}/DeviceCounters.json", json_encode($counters, JSON_PRETTY_PRINT));
logv("Wrote DeviceCounters.json");

// 11) DeviceDetail per-device
$detailData = [];
// original payload for list call returns under ['items'] or ['results']
$list = $devices['items'] ?? $devices['results'] ?? [];
logv("Fetching detail for ".count($list)." devices");
foreach ($list as $device) {
    $id = $device['id'] ?? $device['Id'] ?? $device['externalIdentifier'] ?? null;
    if (! $id) continue;
    logv(" - Device ID: {$id}");
    $d = fetchPost('Device/GetDevice', [
        'dealerCode'=> $DEALER_CODE,
        'id'        => $id,
    ], $token);
    $detailData[$id] = $d;
    // slight pause to avoid hammering
    usleep(200000);
}
file_put_contents("{$cacheDir}/DeviceDetail.json", json_encode($detailData, JSON_PRETTY_PRINT));
logv("Wrote DeviceDetail.json");

// 12) Done
logv("=== CACHE COMPLETE ===");
echo '</pre></body></html>';
