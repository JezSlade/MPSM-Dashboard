<?php
// ─────────────────────────────────────────────────────────────────────
// MPSM Endpoint Explorer (Standalone, Debug Version)
// ─────────────────────────────────────────────────────────────────────
function loadEnv() {
    $envPath = __DIR__ . '/.env';
    if (!file_exists($envPath)) die(".env file missing");
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') === false || str_starts_with(trim($line), '#')) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

function getAccessToken() {
    $url = $_ENV['TOKEN_URL'];
    $post = http_build_query([
        'client_id' => $_ENV['CLIENT_ID'],
        'client_secret' => $_ENV['CLIENT_SECRET'],
        'grant_type' => 'password',
        'username' => $_ENV['USERNAME'],
        'password' => $_ENV['PASSWORD'],
        'scope' => $_ENV['SCOPE']
    ]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    if (!isset($data['access_token'])) {
        die("<pre><strong>Auth failed:</strong>\nError: $err\nResponse: $response</pre>");
    }
    return $data['access_token'];
}

function apiGET($path, $token) {
    $url = rtrim($_ENV['BASE_URL'], '/') . '/' . ltrim($path, '/');
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["Authorization: Bearer $token", "Content-Type: application/json"]
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

function apiPOST($path, $token, $payload) {
    $url = rtrim($_ENV['BASE_URL'], '/') . '/' . ltrim($path, '/');
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ]
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

// ────── INIT ──────
loadEnv();
$token = getAccessToken();
$baseUrl = rtrim($_ENV['BASE_URL'], '/');
$dealerId = $_ENV['MPSM_DEALER_ID'] ?? 'SZ13qRwU5GtFLj0i_CbEgQ2';
$allEndpoints = json_decode(file_get_contents(__DIR__ . '/AllEndpoints.json'), true);
$selectedCustomerId = $_GET['CustomerId'] ?? '';
$selectedDeviceId = $_GET['DeviceId'] ?? '';
$selectedAsset = $_GET['AssetNumber'] ?? '';

// ────── UI HEADER ──────
echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MPSM Explorer (Standalone)</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #121212; color: #eee; margin: 0; padding: 2em; }
    h1, h2 { color: cyan; }
    label { display: inline-block; width: 130px; }
    select, input[type="text"] { padding: 6px; background: #222; color: #eee; border: 1px solid #555; border-radius: 6px; margin-bottom: 10px; width: 320px; }
    pre { background: #222; color: #0f0; padding: 1em; overflow-x: auto; font-size: 0.9em; }
    .box { background: #1a1a1a; padding: 1em 2em; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 0 10px #000 inset; }
    .section { margin-top: 40px; border-top: 1px solid #444; padding-top: 20px; }
    .error { color: red; }
    .form-group { margin-bottom: 15px; }
    .warn { color: orange; font-size: 0.9em; }
  </style>
</head>
<body>
<h1>MPSM Endpoint Explorer</h1>
<div class="box">
<form method="GET">
HTML;

// Step 1: Load Customers
$customerPayload = ['DealerId' => $dealerId, 'PageIndex' => 0, 'PageSize' => 50];
$customers = apiPOST('/Customer/GetCustomers', $token, $customerPayload);

// Debug customer API output
echo "<pre class='warn'>DEBUG: /Customer/GetCustomers\n" . htmlspecialchars(json_encode($customers, JSON_PRETTY_PRINT)) . "</pre>";

echo "<div class='form-group'><label>CustomerId:</label><select name='CustomerId' onchange='this.form.submit()'><option value=''>-- Choose --</option>";
if (!empty($customers['Items'])) {
    foreach ($customers['Items'] as $c) {
        $selected = $c['Id'] === $selectedCustomerId ? 'selected' : '';
        echo "<option value='{$c['Id']}' $selected>{$c['Name']}</option>";
    }
} else {
    echo "<option value=''>[No customers found]</option>";
}
echo "</select></div>";

// Step 2: Devices and Assets
if ($selectedCustomerId) {
    $devices = apiPOST('/CustomerDashboard/Devices', $token, [
        'DealerId' => $dealerId,
        'CustomerId' => $selectedCustomerId,
        'PageIndex' => 0,
        'PageSize' => 50
    ]);

    echo "<pre class='warn'>DEBUG: /CustomerDashboard/Devices\n" . htmlspecialchars(json_encode($devices, JSON_PRETTY_PRINT)) . "</pre>";

    echo "<div class='form-group'><label>DeviceId:</label><select name='DeviceId'>";
    foreach ($devices['Items'] ?? [] as $d) {
        $sel = $d['Id'] === $selectedDeviceId ? 'selected' : '';
        echo "<option value='{$d['Id']}' $sel>{$d['Product']['Model']} ({$d['SerialNumber']})</option>";
    }
    echo "</select></div>";

    echo "<div class='form-group'><label>AssetNumber:</label><select name='AssetNumber'>";
    foreach ($devices['Items'] ?? [] as $d) {
        $sel = $d['AssetNumber'] === $selectedAsset ? 'selected' : '';
        echo "<option value='{$d['AssetNumber']}' $sel>{$d['AssetNumber']}</option>";
    }
    echo "</select></div>";
}

echo "<input type='submit' value='Explore Endpoints'></form></div>";

// Step 3: Main API Explorer
if ($selectedCustomerId && $selectedDeviceId && $selectedAsset) {
    echo "<h2>Live API Results</h2>";
    foreach ($allEndpoints as $group => $entries) {
        echo "<div class='section'><h3>$group</h3>";
        foreach ($entries as $ep) {
            $method = strtoupper($ep['method'] ?? 'GET');
            $path = ltrim($ep['path'] ?? '', '/');
            $desc = htmlspecialchars($ep['summary'] ?? '');
            echo "<div><strong>$method /$path</strong><br><small>$desc</small>";

            $required = [];
            if (str_contains($path, 'Customer')) $required['CustomerId'] = $selectedCustomerId;
            if (str_contains($path, 'Device'))   $required['DeviceId'] = $selectedDeviceId;
            if (str_contains($path, 'Asset'))    $required['AssetNumber'] = $selectedAsset;

            $payload = array_merge(['DealerId' => $dealerId, 'PageIndex' => 0, 'PageSize' => 5], $required);
            $result = ($method === 'POST')
                ? apiPOST($path, $token, $payload)
                : apiGET($path, $token);

            echo "<pre>" . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)) . "</pre></div>";
        }
        echo "</div>";
    }
}
echo "</body></html>";
