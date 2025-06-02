<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// explore_all_endpoints.php
// v2.0 — fully integrated MPSM explorer using core/bootstrap.php

require_once __DIR__ . '/core/bootstrap.php';
require_login();
require_permission('view_customers');

$dealerId = $_ENV['MPSM_DEALER_ID'] ?? 'SZ13qRwU5GtFLj0i_CbEgQ2';
$selectedCustomerId = $_GET['CustomerId'] ?? '';
$selectedDeviceId = $_GET['DeviceId'] ?? '';
$selectedAsset = $_GET['AssetNumber'] ?? '';

$allEndpoints = json_decode(file_get_contents(__DIR__ . '/AllEndpoints.json'), true);
$customers = fetch_mps_api('Customer/GetCustomers');

echo <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>MPSM Endpoint Explorer</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #121212; color: #eee; margin: 0; padding: 2em; }
    label { display: inline-block; width: 130px; }
    select, input[type="submit"] { padding: 6px; background: #222; color: #eee; border: 1px solid #555; border-radius: 6px; margin-bottom: 10px; width: 320px; }
    pre { background: #1a1a1a; color: #0f0; padding: 1em; overflow-x: auto; font-size: 0.9em; }
    .box { background: #1a1a1a; padding: 1em 2em; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 0 10px #000 inset; }
    .section { margin-top: 40px; border-top: 1px solid #444; padding-top: 20px; }
    h1, h2, h3 { color: cyan; }
    .form-group { margin-bottom: 15px; }
    summary { cursor: pointer; color: #ccc; margin-bottom: 5px; }
  </style>
</head>
<body>
<h1>MPSM Endpoint Explorer</h1>
<div class="box">
<form method="GET">
  <div class="form-group">
    <label>CustomerId:</label>
    <select name="CustomerId" onchange="this.form.submit()">
      <option value="">-- Choose --</option>
HTML;

foreach ($customers['Items'] ?? [] as $c) {
    $sel = ($c['Id'] === $selectedCustomerId) ? 'selected' : '';
    echo "<option value='{$c['Id']}' $sel>{$c['Name']}</option>";
}

echo "</select></div>";

if ($selectedCustomerId) {
    $devices = fetch_mps_api('CustomerDashboard/Devices', [
        'DealerId' => $dealerId,
        'CustomerId' => $selectedCustomerId,
        'PageIndex' => 0,
        'PageSize' => 50
    ]);

    echo "<div class='form-group'><label>DeviceId:</label><select name='DeviceId'>";
    foreach ($devices['Items'] ?? [] as $d) {
        $sel = ($d['Id'] === $selectedDeviceId) ? 'selected' : '';
        echo "<option value='{$d['Id']}' $sel>{$d['Product']['Model']} ({$d['SerialNumber']})</option>";
    }
    echo "</select></div>";

    echo "<div class='form-group'><label>AssetNumber:</label><select name='AssetNumber'>";
    foreach ($devices['Items'] ?? [] as $d) {
        $sel = ($d['AssetNumber'] === $selectedAsset) ? 'selected' : '';
        echo "<option value='{$d['AssetNumber']}' $sel>{$d['AssetNumber']}</option>";
    }
    echo "</select></div>";
}

echo "<input type='submit' value='Explore Endpoints'></form></div>";

// ──────────────────────── API EXPLORATION ──────────────────────────
if ($selectedCustomerId && $selectedDeviceId && $selectedAsset) {
    echo "<h2>Live API Results</h2>";

    foreach ($allEndpoints as $group => $entries) {
        echo "<div class='section'><h3>$group</h3>";

        foreach ($entries as $ep) {
            $method = strtoupper($ep['method'] ?? 'GET');
            $path = ltrim($ep['path'] ?? '', '/');
            $desc = htmlspecialchars($ep['summary'] ?? '');
            $reqBody = $ep['requestBody'] ?? [];

            echo "<div><strong>$method /$path</strong><br><small>$desc</small>";

            if ($method === 'GET') {
                $result = fetch_mps_api($path);
                echo "<pre>" . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)) . "</pre></div>";
                continue;
            }

            $payload = [];
            foreach ($reqBody as $key => $type) {
                $payload[$key] = match ($key) {
                    'DealerId'    => $dealerId,
                    'CustomerId'  => $selectedCustomerId,
                    'DeviceId'    => $selectedDeviceId,
                    'AssetNumber' => $selectedAsset,
                    'PageIndex'   => 0,
                    'PageSize'    => 5,
                    default       => match ($type) {
                        'string'  => '',
                        'boolean' => false,
                        'integer' => 0,
                        'array'   => [],
                        default   => null
                    }
                };
            }

            try {
                $response = fetch_mps_api($path, $payload);
            } catch (Exception $e) {
                $response = ['error' => $e->getMessage()];
            }

            echo "<details><summary>Payload</summary><pre>" . htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT)) . "</pre></details>";
            echo "<pre>" . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT)) . "</pre></div>";
        }

        echo "</div>";
    }
}

echo "</body></html>";
