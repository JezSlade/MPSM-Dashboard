<?php
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
    curl_close($ch);
    $data = json_decode($response, true);
    if (!isset($data['access_token'])) {
        die("<pre><strong>Auth failed</strong>\n$response</pre>");
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
$dealerId = $_ENV['MPSM_DEALER_ID'] ?? 'SZ13qRwU5GtFLj0i_CbEgQ2';
$allEndpoints = json_decode(file_get_contents(__DIR__ . '/AllEndpoints.json'), true);
$selectedCustomerId = $_GET['CustomerId'] ?? '';
$selectedDeviceId = $_GET['DeviceId'] ?? '';
$selectedAsset = $_GET['AssetNumber'] ?? '';

// ────── PAGE HEADER ──────
echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MPSM Endpoint Explorer</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #121212; color: #eee; margin: 0; padding: 2em; }
    label { display: inline-block; width: 130px; }
    select, input[type="text"] { padding: 6px; background: #222; color: #eee; border: 1px solid #555; border-radius: 6px; margin-bottom: 10px; width: 320px; }
    pre { background: #222; color: #0f0; padding: 1em; overflow-x: auto; font-size: 0.9em; }
    .box { background: #1a1a1a; padding: 1em 2em; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 0 10px #000 inset; }
    .section { margin-top: 40px; border-top: 1px solid #444; padding-top: 20px; }
    h1, h2, h3 { color: cyan; }
    .form-group { margin-bottom: 15px; }
  </style>
</head>
<body>
<h1>MPSM Endpoint Explorer</h1>
<div class="box">
<form method="GET">
HTML;

// Step 1: Load Customers
$customers = apiPOST('/Customer/GetCustomers', $token, [
    'DealerId' => $dealerId,
    'CustomerId' => null,
    'SiteId' => null,
    'Filters' => [],
    'PageIndex' => 0,
    'PageSize' => 50
]);

echo "<div class='form-group'><label>CustomerId:</label><select name='CustomerId' onchange='this.form.submit()'><option value=''>-- Choose --</option>";
foreach ($customers['Items'] ?? [] as $c) {
    $selected = $c['Id'] === $selectedCustomerId ? 'selected' : '';
    echo "<option value='{$c['Id']}' $selected>{$c['Name']}</option>";
}
echo "</select></div>";

if ($selectedCustomerId) {
    $devices = apiPOST('/CustomerDashboard/Devices', $token, [
        'DealerId' => $dealerId,
        'CustomerId' => $selectedCustomerId,
        'PageIndex' => 0,
        'PageSize' => 50
    ]);

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

// ────── EXPLORE ENDPOINTS ──────
if ($selectedCustomerId && $selectedDeviceId && $selectedAsset) {
    echo "<h2>Live API Results (Dynamic)</h2>";
    foreach ($allEndpoints as $group => $entries) {
        echo "<div class='section'><h3>$group</h3>";
        foreach ($entries as $ep) {
            $method = strtoupper($ep['method'] ?? 'GET');
            $path = ltrim($ep['path'] ?? '', '/');
            $desc = htmlspecialchars($ep['summary'] ?? '');
            $reqBody = $ep['requestBody'] ?? [];

            echo "<div><strong>$method /$path</strong><br><small>$desc</small>";

            if ($method === 'GET') {
                $result = apiGET($path, $token);
                echo "<pre>" . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)) . "</pre></div>";
                continue;
            }

            $payload = [];
            foreach ($reqBody as $key => $type) {
                if ($key === 'DealerId') $payload[$key] = $dealerId;
                elseif ($key === 'CustomerId') $payload[$key] = $selectedCustomerId;
                elseif ($key === 'DeviceId') $payload[$key] = $selectedDeviceId;
                elseif ($key === 'AssetNumber') $payload[$key] = $selectedAsset;
                elseif ($key === 'PageIndex') $payload[$key] = 0;
                elseif ($key === 'PageSize') $payload[$key] = 5;
                else $payload[$key] = match ($type) {
                    'string' => '',
                    'boolean' => false,
                    'integer' => 0,
                    'array' => [],
                    default => null
                };
            }

            $response = apiPOST($path, $token, $payload);
            echo "<details><summary>Payload</summary><pre>" . htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT)) . "</pre></details>";
            echo "<pre>" . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT)) . "</pre></div>";
        }
        echo "</div>";
    }
}
echo "</body></html>";
