<?php
/**
 * MPSM Endpoint Explorer v2 – Styled + Staged
 * Uses style.css, auto-selects CustomerId/DeviceId/AssetNumber, then shows all endpoints
 */

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

// ────── PAGE BEGIN ──────
echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MPSM Endpoint Explorer</title>
    <link rel="stylesheet" href="/styles/style.css">
</head>
<body>
<div class="dashboard-container">
    <div class="sidebar">
        <ul>
            <li><strong>Step 1:</strong> Select Customer</li>
            <li><strong>Step 2:</strong> Select Device</li>
            <li><strong>Step 3:</strong> Explore Endpoints</li>
        </ul>
    </div>
    <div class="main">
        <div class="topbar">
            <form method="GET">
HTML;

// ────── CUSTOMER SELECT ──────
$customers = apiPOST('/Customer/GetCustomers', $token, ['DealerId' => $dealerId, 'PageIndex' => 0, 'PageSize' => 50]);
echo "<label>CustomerId:</label><select name='CustomerId' onchange='this.form.submit()'><option value=''>-- Choose --</option>";
foreach ($customers['Items'] ?? [] as $c) {
    $selected = $c['Id'] === $selectedCustomerId ? 'selected' : '';
    echo "<option value='{$c['Id']}' $selected>{$c['Name']}</option>";
}
echo "</select><br><br>";

// ────── DEVICE SELECT ──────
if ($selectedCustomerId) {
    $devices = apiPOST('/CustomerDashboard/Devices', $token, [
        'DealerId' => $dealerId,
        'CustomerId' => $selectedCustomerId,
        'PageIndex' => 0,
        'PageSize' => 50
    ]);

    echo "<label>DeviceId:</label><select name='DeviceId'><option value=''>-- Choose --</option>";
    foreach ($devices['Items'] ?? [] as $d) {
        $sel = $d['Id'] === $selectedDeviceId ? 'selected' : '';
        echo "<option value='{$d['Id']}' $sel>{$d['Product']['Model']}</option>";
    }
    echo "</select><br><br>";

    echo "<label>AssetNumber:</label><select name='AssetNumber'><option value=''>-- Choose --</option>";
    foreach ($devices['Items'] ?? [] as $d) {
        $sel = $d['AssetNumber'] === $selectedAsset ? 'selected' : '';
        echo "<option value='{$d['AssetNumber']}' $sel>{$d['AssetNumber']}</option>";
    }
    echo "</select><br><br>";
}
echo "<input type='submit' value='Explore'></form></div>";

// ────── API BROWSER ──────
if ($selectedCustomerId && $selectedDeviceId && $selectedAsset) {
    echo "<h2>Endpoint Results</h2>";
    foreach ($allEndpoints as $group => $entries) {
        echo "<h3>$group</h3>";
        foreach ($entries as $ep) {
            $method = strtoupper($ep['method'] ?? 'GET');
            $path = ltrim($ep['path'] ?? '', '/');
            $desc = htmlspecialchars($ep['summary'] ?? '');
            echo "<div style='margin-bottom: 15px;'>";
            echo "<strong>$method /$path</strong><br><small>$desc</small>";

            $required = [];
            if (str_contains($path, 'Customer')) $required['CustomerId'] = $selectedCustomerId;
            if (str_contains($path, 'Device')) $required['DeviceId'] = $selectedDeviceId;
            if (str_contains($path, 'Asset')) $required['AssetNumber'] = $selectedAsset;

            $payload = array_merge(['DealerId' => $dealerId, 'PageIndex' => 0, 'PageSize' => 5], $required);

            $result = ($method === 'POST')
                ? apiPOST($path, $token, $payload)
                : apiGET($path, $token);

            echo "<pre>" . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)) . "</pre></div>";
        }
    }
}

echo "</div></div></body></html>";
