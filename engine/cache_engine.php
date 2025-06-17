<?php
// cache_engine.php
// Monolithic cache engine for pre-fetching all key MPS Monitor API data,
// streaming verbose logs straight to the browser window.
// --------------------------------------------------------------------

// Turn off output buffering and compression
@ini_set('zlib.output_compression',    0);
@ini_set('implicit_flush',             1);
while (ob_get_level() > 0) { ob_end_flush(); }
ob_implicit_flush(true);

// Send page headers
header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Cache Engine Log</title>
  <style>
    body { background: #111; color: #eee; font-family: monospace; padding: 1rem; }
    pre { margin: 0; }
  </style>
</head>
<body><pre>';

function logv($msg) {
    $time = date('H:i:s');
    echo "[{$time}] {$msg}\n";
    @ob_flush();
    @flush();
}

// 1) Locate and load .env one level up
$expectedEnv = __DIR__ . '/../.env';
logv("Looking for .env at {$expectedEnv}");
$envPath = realpath($expectedEnv);
if (! $envPath || ! is_readable($envPath)) {
    logv("ERROR: .env not found or unreadable");
    echo '</pre></body></html>';
    exit;
}
logv(".env found, loading…");

$env = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    [$key, $val] = explode('=', $line, 2) + [null, null];
    if ($key !== null) {
        $envKey = trim($key);
        $envVal = trim($val);
        $env[$envKey] = $envVal;
        logv("Loaded env {$envKey}");
    }
}

// 2) Configuration
$BASE_URL      = rtrim($env['API_BASE_URL'] ?? '', '/') . '/';
$CLIENT_ID     = $env['CLIENT_ID']     ?? '';
$CLIENT_SECRET = $env['CLIENT_SECRET'] ?? '';
$USERNAME      = $env['USERNAME']      ?? '';
$PASSWORD      = $env['PASSWORD']      ?? '';
$DEALER_CODE   = $env['DEALER_CODE']   ?? '';
$SCOPE         = $env['SCOPE']         ?? '';
$TOKEN_URL     = $env['TOKEN_URL']     ?? '';

logv("Configured BASE_URL: {$BASE_URL}");
logv("Configured TOKEN_URL: {$TOKEN_URL}");

// 3) Token fetch
function getToken() {
    global $TOKEN_URL, $USERNAME, $PASSWORD, $CLIENT_ID, $CLIENT_SECRET, $SCOPE;
    logv("Requesting token…");
    $post = http_build_query([
        'grant_type'    => 'password',
        'username'      => $USERNAME,
        'password'      => $PASSWORD,
        'client_id'     => $CLIENT_ID,
        'client_secret' => $CLIENT_SECRET,
        'scope'         => $SCOPE,
    ]);
    $ch = curl_init($TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    logv("Token endpoint returned HTTP {$code}");
    $json = json_decode($resp, true);
    if (isset($json['access_token'])) {
        logv("Token acquired");
        return $json['access_token'];
    }
    logv("Failed to parse token response");
    return null;
}

// 4) Paged GET helper
function fetchPaged($endpoint, $token, $pageSize = 200) {
    global $BASE_URL, $DEALER_CODE;
    $all  = [];
    $page = 1;
    logv("Starting paged GET for {$endpoint}");
    do {
        $params = [
            'dealerCode' => $DEALER_CODE,
            'pageNumber' => $page,
            'pageRows'   => $pageSize,
        ];
        $url = $BASE_URL . ltrim($endpoint, '/') . '?' . http_build_query($params);
        logv("GET {$url}");
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ["Authorization: Bearer {$token}"],
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        logv("  HTTP {$code}");
        $data = json_decode($resp, true);
        $items = $data['items'] ?? $data['results'] ?? [];
        logv("  Retrieved ".count($items)." items");
        $all = array_merge($all, $items);
        $page++;
    } while (count($items) === $pageSize);
    logv("Completed {$endpoint}: total ".count($all)." items");
    return $all;
}

// 5) Run cache
logv("=== BEGIN CACHE ===");
$token = getToken();
if (! $token) {
    logv("ERROR: Cannot obtain token, aborting.");
    echo '</pre></body></html>';
    exit;
}

// Correct cache directory: project-root /cache, not engine/cache
$cacheDir = realpath(__DIR__ . '/../cache') ?: (__DIR__ . '/../cache');
logv("Using cache directory: {$cacheDir}");
if (! is_dir($cacheDir)) {
    logv("Creating cache dir: {$cacheDir}");
    mkdir($cacheDir, 0755, true);
}

$toCache = [
    'Customers'      => ['ep'=>'/Customer/GetCustomers','method'=>'POST'],
    'Devices'        => ['ep'=>'/Device/GetDevices','method'=>'GET'],
    'DeviceAlerts'   => ['ep'=>'/Device/GetDeviceAlerts','method'=>'GET'],
    'DeviceCounters' => ['ep'=>'/Counter/List','method'=>'POST'],
    'DeviceDetail'   => ['ep'=>'/Device/GetDevice','method'=>'GET'],
];

foreach ($toCache as $name => $info) {
    logv("-- Caching {$name} --");
    if ($info['method'] === 'GET') {
        $data = fetchPaged($info['ep'], $token);
    } else {
        $url = $BASE_URL . ltrim($info['ep'], '/');
        $bodyArr = ['dealerCode'=>$DEALER_CODE,'pageNumber'=>1,'pageRows'=>1000];
        $body = json_encode($bodyArr);
        logv("POST {$url}");
        logv("  Payload: ".json_encode($bodyArr));
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$token}",
                "Content-Type: application/json"
            ],
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        logv("  HTTP {$code}, ".strlen($resp)." bytes");
        $data = json_decode($resp, true);
    }
    $out = "{$cacheDir}/{$name}.json";
    file_put_contents($out, json_encode($data, JSON_PRETTY_PRINT));
    logv("Wrote {$out} (".filesize($out)." bytes)");
    sleep(1);
}

logv("=== CACHE COMPLETE ===");
echo '</pre></body></html>';
