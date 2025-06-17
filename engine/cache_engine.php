<?php
// cache_engine.php
// Monolithic cache engine for pre-fetching all key MPS Monitor API data.
// --------------------------------------------------------------------

// Enable debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1) Parse .env manually
$env = [];
foreach (file(__DIR__.'/.env', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
    if (strpos(trim($line), '#')===0) continue;
    list($key, $val) = explode('=', $line, 2);
    $env[trim($key)] = trim($val);
}

// 2) Basic configuration
$BASE_URL    = rtrim($env['API_BASE_URL'] ?? $env['BASE_URL'], '/') . '/';
$CLIENT_ID   = $env['CLIENT_ID'];
$CLIENT_SECRET = $env['CLIENT_SECRET'];
$USERNAME    = $env['USERNAME'];
$PASSWORD    = $env['PASSWORD'];
$DEALER_CODE = $env['DEALER_CODE'];
$DEALER_ID   = $env['DEALER_ID'];

// 3) Helper: fetch OAuth token
function getToken() {
    global $env;
    $url = $env['TOKEN_URL'];
    $post = http_build_query([
        'grant_type'    => 'password',
        'username'      => $env['USERNAME'],
        'password'      => $env['PASSWORD'],
        'client_id'     => $env['CLIENT_ID'],
        'client_secret' => $env['CLIENT_SECRET'],
        'scope'         => $env['SCOPE'],
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($resp, true);
    return $data['access_token'] ?? null;
}

// 4) Helper: generic paged GET
function fetchPaged($endpoint, $params = [], $token, $pageSize = 100) {
    global $BASE_URL;
    $all = [];
    $page = 1;
    do {
        $params['dealerCode'] = $params['dealerCode'] ?? $GLOBALS['DEALER_CODE'];
        $params['pageNumber'] = $page;
        $params['pageRows']   = $pageSize;
        $url = $BASE_URL . ltrim($endpoint, '/')
             . '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ["Authorization: Bearer {$token}"],
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($resp, true);

        // Assuming paged response shape: data => items, metadata => hasMore
        $items = $data['items'] ?? $data['results'] ?? [];
        $all   = array_merge($all, $items);
        $hasMore = count($items) === $pageSize;
        $page++;
    } while ($hasMore);

    return $all;
}

// 5) Main orchestration
$token = getToken();
if (!$token) {
    error_log("Failed to retrieve API token.");
    exit(1);
}

// Define endpoints to cache (yank logic from api/*.php)
$toCache = [
    'GetCustomers'      => ['endpoint' => '/Customer/GetCustomers',      'method' => 'POST'],
    'GetDevices'        => ['endpoint' => '/Device/GetDevices',          'method' => 'GET'],
    'GetDeviceAlerts'   => ['endpoint' => '/Device/GetDeviceAlerts',     'method' => 'GET'],
    'GetDeviceCounters' => ['endpoint' => '/Counter/List',               'method' => 'POST'],
    'GetDeviceDetail'   => ['endpoint' => '/Device/GetDevice',           'method' => 'GET'],
];

// Storage directory
$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

foreach ($toCache as $name => $info) {
    echo "Caching {$name}...\n";
    if ($info['method'] === 'GET') {
        $data = fetchPaged($info['endpoint'], [], $token, 200);
    } else {
        // For POST endpoints, do a single full pull (or implement your own paging logic)
        $url = $BASE_URL . ltrim($info['endpoint'], '/');
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(['dealerCode' => $DEALER_CODE, 'pageNumber'=>1,'pageRows'=>1000]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$token}",
                'Content-Type: application/json'
            ],
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($resp, true);
    }
    file_put_contents("{$cacheDir}/{$name}.json", json_encode($data, JSON_PRETTY_PRINT));
    // Throttle between calls
    sleep(1);
}

echo "Caching complete.\n";
