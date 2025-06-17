<?php
// cache_engine.php
// Monolithic cache engine for pre-fetching all key MPS Monitor API data, with verbose logging.
// --------------------------------------------------------------------

// Enable debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// VERBOSE FLAG (set to false to silence logging)
$verbose = true;
function logv($msg) {
    global $verbose;
    if ($verbose) {
        echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    }
}

// 1) Locate and load .env one level up
$expectedEnv = __DIR__ . '/../.env';
logv("Looking for .env at {$expectedEnv}");
$envPath = realpath($expectedEnv);
if (! $envPath || ! is_readable($envPath)) {
    exit("Fatal error: .env not found or unreadable at {$expectedEnv}\n");
}
logv(".env found at {$envPath}, loading…");

$env = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') {
        continue;
    }
    [$key, $val] = explode('=', $line, 2) + [null, null];
    if ($key !== null) {
        $envKey = trim($key);
        $envVal = trim($val);
        $env[$envKey] = $envVal;
        logv("  Loaded env {$envKey} = " . (strlen($envVal)>40 ? substr($envVal,0,40).'…' : $envVal));
    }
}

// 2) Basic configuration
$BASE_URL      = rtrim($env['API_BASE_URL'] ?? '', '/') . '/';
$CLIENT_ID     = $env['CLIENT_ID']       ?? '';
$CLIENT_SECRET = $env['CLIENT_SECRET']   ?? '';
$USERNAME      = $env['USERNAME']        ?? '';
$PASSWORD      = $env['PASSWORD']        ?? '';
$DEALER_CODE   = $env['DEALER_CODE']     ?? '';
$SCOPE         = $env['SCOPE']           ?? '';
$TOKEN_URL     = $env['TOKEN_URL']       ?? '';

logv("Configuration:");
logv("  BASE_URL    = {$BASE_URL}");
logv("  TOKEN_URL   = {$TOKEN_URL}");
logv("  DEALER_CODE = {$DEALER_CODE}");

// 3) Helper: fetch OAuth token
function getToken() {
    global $TOKEN_URL, $USERNAME, $PASSWORD, $CLIENT_ID, $CLIENT_SECRET, $SCOPE;
    logv("Requesting access token from {$TOKEN_URL}");
    $postFields = http_build_query([
        'grant_type'    => 'password',
        'username'      => $USERNAME,
        'password'      => $PASSWORD,
        'client_id'     => $CLIENT_ID,
        'client_secret' => $CLIENT_SECRET,
        'scope'         => $SCOPE,
    ]);
    logv("  POST fields: " . substr($postFields, 0, 100) . '…');

    $ch = curl_init($TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    logv("  HTTP {$code} from token endpoint");
    if ($resp === false) {
        logv("  cURL error fetching token");
        return null;
    }
    $data = json_decode($resp, true);
    if (!isset($data['access_token'])) {
        logv("  Unexpected token response: " . substr($resp, 0, 200) . '…');
        return null;
    }
    logv("  Token acquired (".substr($data['access_token'],0,10)."…)");
    return $data['access_token'];
}

// 4) Helper: generic paged GET
function fetchPaged($endpoint, $params = [], $token, $pageSize = 200) {
    global $BASE_URL, $DEALER_CODE;
    $all    = [];
    $page   = 1;
    logv("Starting paged GET for {$endpoint}");
    do {
        $params['dealerCode'] = $DEALER_CODE;
        $params['pageNumber'] = $page;
        $params['pageRows']   = $pageSize;
        $url = $BASE_URL . ltrim($endpoint, '/') . '?' . http_build_query($params);

        logv("  Fetching page {$page}: {$url}");
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ["Authorization: Bearer {$token}"],
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        logv("    HTTP {$code} from endpoint");
        $data = json_decode($resp, true);
        $items = $data['items'] ?? $data['results'] ?? [];
        logv("    Retrieved " . count($items) . " items");
        $all   = array_merge($all, $items);
        $hasMore = count($items) === $pageSize;
        $page++;
    } while ($hasMore);

    logv("Completed {$endpoint}, total items fetched: " . count($all));
    return $all;
}

// 5) Main orchestration
logv("=== BEGIN CACHE ENGINE ===");
$token = getToken();
if (! $token) {
    exit("Fatal error: Unable to retrieve access token.\n");
}

$toCache = [
    'Customers'      => ['endpoint' => '/Customer/GetCustomers',    'method' => 'POST'],
    'Devices'        => ['endpoint' => '/Device/GetDevices',        'method' => 'GET'],
    'DeviceAlerts'   => ['endpoint' => '/Device/GetDeviceAlerts',   'method' => 'GET'],
    'DeviceCounters' => ['endpoint' => '/Counter/List',             'method' => 'POST'],
    'DeviceDetail'   => ['endpoint' => '/Device/GetDevice',         'method' => 'GET'],
];

$cacheDir = __DIR__ . '/cache';
if (! is_dir($cacheDir)) {
    logv("Creating cache directory at {$cacheDir}");
    mkdir($cacheDir, 0755, true);
}

foreach ($toCache as $name => $info) {
    logv("--- Caching: {$name} ---");
    if ($info['method'] === 'GET') {
        $data = fetchPaged($info['endpoint'], [], $token);
    } else {
        // POST-based pull
        $url = $BASE_URL . ltrim($info['endpoint'], '/');
        $payloadArr = ['dealerCode' => $DEALER_CODE, 'pageNumber' => 1, 'pageRows' => 1000];
        $payload = json_encode($payloadArr);
        logv("  POST {$url}");
        logv("    Payload: " . json_encode($payloadArr));
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$token}",
                'Content-Type: application/json'
            ],
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        logv("    HTTP {$code}, response length: " . strlen($resp));
        $data = json_decode($resp, true);
        logv("    Decoded top-level keys: " . implode(', ', array_keys((array)$data)));
    }

    $outFile = "{$cacheDir}/{$name}.json";
    file_put_contents($outFile, json_encode($data, JSON_PRETTY_PRINT));
    logv("  Wrote cache file {$outFile} (".filesize($outFile)." bytes)");
    sleep(1); // throttle
}

logv("=== CACHING COMPLETE ===");
