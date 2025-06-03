<?php
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$name, $value] = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}
loadEnv(__DIR__ . '/.env');

$clientId = $_ENV['CLIENT_ID'] ?? '';
$clientSecret = $_ENV['CLIENT_SECRET'] ?? '';
$username = $_ENV['USERNAME'] ?? '';
$password = $_ENV['PASSWORD'] ?? '';
$scope = $_ENV['SCOPE'] ?? '';
$tokenUrl = $_ENV['TOKEN_URL'] ?? '';
$baseUrl = rtrim($_ENV['BASE_URL'] ?? '', '/');
$dealerCode = $_ENV['DEALER_CODE'] ?? '';
$dealerId = $_ENV['DEALER_ID'] ?? '';
$debug = ($_ENV['DEBUG'] ?? 'false') === 'true';

function getAccessToken($url, $clientId, $clientSecret, $username, $password, $scope, $debug = false) {
    $data = http_build_query([
        'grant_type' => 'password',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'username' => $username,
        'password' => $password,
        'scope' => $scope
    ]);

    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $data,
            'ignore_errors' => true
        ]
    ];

    $context = stream_context_create($opts);
    $result = file_get_contents($url, false, $context);
    $json = json_decode($result, true);

    // if ($debug) echo "<pre><strong>Token Response:</strong>\n" . htmlspecialchars(print_r($json, true)) . "</pre>";
    return $json['access_token'] ?? null;
}

function callGetCustomers($baseUrl, $token, $dealerCode, $debug = false) {
    $payload = [
        "DealerCode" => $dealerCode,
        "Code" => null,
        "HasHpSds" => null,
        "FilterText" => null,
        "PageNumber" => 1,
        "PageRows" => 2147483647,
        "SortColumn" => "Id",
        "SortOrder" => 0
    ];

    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => "Authorization: Bearer $token\r\nAccept: application/json\r\nContent-Type: application/json\r\n",
            'content' => json_encode($payload),
            'ignore_errors' => true
        ]
    ];

    $context = stream_context_create($opts);
    $result = file_get_contents($baseUrl . '/Customer/GetCustomers', false, $context);
    $json = json_decode($result, true);

    // if ($debug) echo "<pre><strong>Customer Response:</strong>\n" . htmlspecialchars(print_r($json, true)) . "</pre>";
    return $json['Result'] ?? [];
}

function callGetDevices($baseUrl, $token, $dealerId, $customerCode, $debug = false) {
    $payload = [
        "FilterDealerId" => $dealerId,
        "FilterCustomerCodes" => [$customerCode],
        "ProductBrand" => null,
        "ProductModel" => null,
        "OfficeId" => null,
        "Status" => 1,
        "FilterText" => null,
        "PageNumber" => 1,
        "PageRows" => 50,
        "SortColumn" => "Id",
        "SortOrder" => 0
    ];

    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => "Authorization: Bearer $token\r\nAccept: application/json\r\nContent-Type: application/json\r\n",
            'content' => json_encode($payload),
            'ignore_errors' => true
        ]
    ];

    $context = stream_context_create($opts);
    $result = file_get_contents($baseUrl . '/Device/List', false, $context);
    $json = json_decode($result, true);

    // if ($debug) echo "<pre><strong>Device Response:</strong>\n" . htmlspecialchars(print_r($json, true)) . "</pre>";
    return $json['Result'] ?? [];
}

function callGetAlerts($baseUrl, $token, $dealerCode, $customerCode, $debug = false) {
    $payload = [
        "DealerCode" => $dealerCode,
        "CustomerCode" => $customerCode,
        "DeviceId" => null,
        "SerialNumber" => null,
        "AssetNumber" => null,
        "InitialFrom" => null,
        "InitialTo" => null,
        "ExhaustedFrom" => null,
        "ExhaustedTo" => null,
        "Brand" => null,
        "Model" => null,
        "OfficeDescription" => null,
        "SupplySetDescription" => null,
        "FilterCustomerText" => null,
        "ManageOption" => null,
        "InstallationOption" => null,
        "CancelOption" => null,
        "HiddenOption" => null,
        "SupplyType" => null,
        "ColorType" => null,
        "ExcludeForStockShippedSupplies" => false,
        "FilterText" => null,
        "PageNumber" => 1,
        "PageRows" => 50,
        "SortColumn" => "InitialDate",
        "SortOrder" => 0
    ];

    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => "Authorization: Bearer $token\r\nAccept: application/json\r\nContent-Type: application/json\r\n",
            'content' => json_encode($payload),
            'ignore_errors' => true
        ]
    ];

    $context = stream_context_create($opts);
    $result = file_get_contents($baseUrl . '/SupplyAlert/List', false, $context);
    $json = json_decode($result, true);

    // if ($debug) echo "<pre><strong>Alert Response:</strong>\n" . htmlspecialchars(print_r($json, true)) . "</pre>";
    return $json['Result'] ?? [];
}

$token = getAccessToken($tokenUrl, $clientId, $clientSecret, $username, $password, $scope, $debug);
if (!$token) die("<strong style='color:red;'>❌ Failed to get access token.</strong>");

$customers = callGetCustomers($baseUrl, $token, $dealerCode, $debug);
$selectedCustomer = $_POST['customer'] ?? null;
$devices = $selectedCustomer ? callGetDevices($baseUrl, $token, $dealerId, $selectedCustomer, $debug) : [];
$alerts = $selectedCustomer ? callGetAlerts($baseUrl, $token, $dealerCode, $selectedCustomer, $debug) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MPSM MVPPOS - Customers, Devices, Alerts</title>
    <style>
        body {
            background: #111;
            color: #eee;
            font-family: monospace;
            padding: 2rem;
        }
        h1, h2 {
            color: #00ffcc;
        }
        select, button {
            padding: 5px;
            font-size: 1rem;
        }
        form {
            margin-bottom: 2rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #1f1f1f;
            margin-top: 2rem;
        }
        th, td {
            padding: 10px;
            border: 1px solid #333;
        }
        th {
            background: #333;
        }
        tr:hover {
            background: #2a2a2a;
        }
    </style>
</head>
<body>
    <h1>MPSM: Device + Alert Viewer</h1>

    <form method="POST">
        <label for="customer">Select Customer:</label>
        <select name="customer" id="customer">
            <option value="">-- Choose One --</option>
            <?php foreach ($customers as $c): 
                $code = $c['Code'] ?? '';
                $desc = $c['Description'] ?? '';
                $selected = ($code === $selectedCustomer) ? 'selected' : '';
            ?>
                <option value="<?= htmlspecialchars($code) ?>" <?= $selected ?>>
                    <?= htmlspecialchars($desc) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Load</button>
    </form>

    <?php if (!empty($devices)): ?>
        <h2>Devices for <?= htmlspecialchars($selectedCustomer) ?></h2>
        <table>
            <thead>
                <tr>
                    <th>Serial</th>
                    <th>Model</th>
                    <th>Brand</th>
                    <th>IP</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($devices as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['SerialNumber'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['Product']['Model'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['Product']['Brand'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['IpAddress'] ?? '-') ?></td>
                    <td><?= ($d['IsOffline'] ?? false) ? 'Offline' : 'Online' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (!empty($alerts)): ?>
        <h2>Alerts for <?= htmlspecialchars($selectedCustomer) ?></h2>
        <table>
            <thead>
                <tr>
                    <th>Serial</th>
                    <th>Model</th>
                    <th>Warning</th>
                    <th>IP</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($alerts as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['SerialNumber'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($a['ProductModel'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($a['Warning'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($a['IpAddress'] ?? '-') ?></td>
                    <td><?= htmlspecialchars(substr($a['ActualDate'] ?? '', 0, 10)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
