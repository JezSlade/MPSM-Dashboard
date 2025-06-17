<?php
// cache_engine.php
// Monolithic cache engine for pre‐fetching all key MPS Monitor API data.
// --------------------------------------------------------------------

// Enable debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1) Locate and load .env one level up
$envPath = realpath(__DIR__ . '/../.env');
if (! $envPath || ! is_readable($envPath)) {
    exit("Fatal error: .env not found or unreadable at " . (__DIR__ . '/../.env') . "\n");
}
$env = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') {
        continue;
    }
    [$key, $val] = explode('=', $line, 2) + [null, null];
    if ($key !== null) {
        $env[trim($key)] = trim($val);
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

// 3) Helper: fetch OAuth token
function getToken() {
    global $TOKEN_URL, $USERNAME, $PASSWORD, $CLIENT_ID, $CLIENT_SECRET, $SCOPE;
    $postFields = http_build_query([
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
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($resp, true);
    return $data['access_token'] ?? null;
}

// 4) Helper: generic paged GET
function fetchPaged($endpoint, $params = [], $token, $pageSize = 200) {
    global $BASE_URL, $DEALER_CODE;
    $all    = [];
    $page   = 1;
    do {
        $params['dealerCode'] = $DEALER_CODE;
        $params['pageNumber'] = $page;
        $params['pageRows']   = $pageSize;
        $url = $BASE_URL . ltrim($endpoint, '/') . '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ["Authorization: Bearer {$token}"],
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($resp, true);

        $items   = $data['items']   ?? $data['results'] ?? [];
        $all     = array_merge($all, $items);
        $hasMore = count($items) === $pageSize;
        $page++;
    } while ($hasMore);

    return $all;
}

// 5) Main orchestration
$token = getToken();
if (! $token) {
    exit("Fatal error: Unable to retrieve access token.\n");
}

// Endpoints to cache
$toCache = [
    'Customers'      => ['endpoint' => '/Customer/GetCustomers',    'method' => 'POST'],
    'Devices'        => ['endpoint' => '/Device/GetDevices',        'method' => 'GET'],
    'DeviceAlerts'   => ['endpoint' => '/Device/GetDeviceAlerts',   'method' => 'GET'],
    'DeviceCounters' => ['endpoint' => '/Counter/List',             'method' => 'POST'],
    'DeviceDetail'   => ['endpoint' => '/Device/GetDevice',         'method' => 'GET'],
];

// Ensure cache directory exists
$cacheDir = __DIR__ . '/cache';
if (! is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

foreach ($toCache as $name => $info) {
    echo "Caching {$name}...\n";
    if ($info['method'] === 'GET') {
        $data = fetchPaged($info['endpoint'], [], $token);
    } else {
        // Simple POST pull (adjust pageRows if necessary)
        $url = $BASE_URL . ltrim($info['endpoint'], '/');
        $payload = json_encode([
            'dealerCode' => $DEALER_CODE,
            'pageNumber' => 1,
            'pageRows'   => 1000
        ]);
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
        curl_close($ch);
        $data = json_decode($resp, true);
    }

    file_put_contents("{$cacheDir}/{$name}.json", json_encode($data, JSON_PRETTY_PRINT));
    sleep(1); // throttle
}

echo "Caching complete.\n";
