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
$devicePageSize = intval($_ENV['DEVICE_PAGE_SIZE'] ?? 50);

function getAccessToken($url, $clientId, $clientSecret, $username, $password, $scope) {
    $data = http_build_query([
        'grant_type' => 'password',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'username' => $username,
        'password' => $password,
        'scope' => $scope
    ]);
    $opts = ['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $data,
        'ignore_errors' => true
    ]];
    $result = file_get_contents($url, false, stream_context_create($opts));
    $json = json_decode($result, true);
    return $json['access_token'] ?? null;
}

function postJson($url, $token, $payload) {
    $opts = ['http' => [
        'method' => 'POST',
        'header' =>
            "Authorization: Bearer $token\r\n" .
            "Accept: application/json\r\n" .
            "Content-Type: application/json\r\n",
        'content' => json_encode($payload),
        'ignore_errors' => true
    ]];
    $result = file_get_contents($url, false, stream_context_create($opts));
    return json_decode($result, true);
}

function callGetCustomers($baseUrl, $token, $dealerCode) {
    return postJson("$baseUrl/Customer/GetCustomers", $token, [
        "DealerCode" => $dealerCode,
        "Code" => null,
        "HasHpSds" => null,
        "FilterText" => null,
        "PageNumber" => 1,
        "PageRows" => 2147483647,
        "SortColumn" => "Id",
        "SortOrder" => 0
    ])['Result'] ?? [];
}

function callGetDevices($baseUrl, $token, $dealerId, $customerCode, $pageNumber, $pageRows) {
    return postJson("$baseUrl/Device/List", $token, [
        "FilterDealerId" => $dealerId,
        "FilterCustomerCodes" => [$customerCode],
        "ProductBrand" => null,
        "ProductModel" => null,
        "OfficeId" => null,
        "Status" => 1,
        "FilterText" => null,
        "PageNumber" => $pageNumber,
        "PageRows" => $pageRows,
        "SortColumn" => "Id",
        "SortOrder" => 0
    ]);
}

function callGetDeviceDetails($baseUrl, $token, $deviceId) {
    return postJson("$baseUrl/Device/GetDetailedInformations", $token, ["Id" => $deviceId]) ?? [];
}

$token = getAccessToken($tokenUrl, $clientId, $clientSecret, $username, $password, $scope);
if (!$token) die("❌ Failed to get access token.");

$customers = callGetCustomers($baseUrl, $token, $dealerCode);
$selectedCustomer = $_POST['customer'] ?? null;
$page = max(1, intval($_POST['page'] ?? 1));
$drillId = $_POST['drill'] ?? null;
$devicesData = $selectedCustomer ? callGetDevices($baseUrl, $token, $dealerId, $selectedCustomer, $page, $devicePageSize) : [];
$devices = $devicesData['Result'] ?? [];
$totalDevices = $devicesData['TotalRows'] ?? 0;
$deviceDetails = $drillId ? callGetDeviceDetails($baseUrl, $token, $drillId) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MVPPOS - Device Drill & Pagination</title>
    <style>
        body { background: #111; color: #eee; font-family: monospace; padding: 2rem; }
        h1, h2 { color: #00ffcc; }
        form { margin-bottom: 2rem; }
        select, button { padding: 5px; font-size: 1rem; }
        table { width: 100%; border-collapse: collapse; background: #1f1f1f; margin-top: 2rem; }
        th, td { padding: 10px; border: 1px solid #333; }
        th { background: #333; }
        tr:hover { background: #2a2a2a; cursor: pointer; }
        .drilldown { background: #2b2b2b; border-top: 2px solid #555; font-size: 0.9rem; }
        .pagination { margin-top: 1rem; }
    </style>
</head>
<body>
    <h1>MVPPOS: Devices with Drill-down</h1>
    <form method="POST">
        <label for="customer">Customer:</label>
        <select name="customer" id="customer" onchange="this.form.submit()">
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
        <input type="hidden" name="page" value="<?= $page ?>">
    </form>

    <?php if ($selectedCustomer && $devices): ?>
        <h2>Devices (<?= count($devices) ?> of <?= $totalDevices ?>)</h2>
        <form method="POST">
            <input type="hidden" name="customer" value="<?= htmlspecialchars($selectedCustomer) ?>">
            <input type="hidden" name="page" value="<?= $page - 1 ?>">
            <button type="submit" <?= $page <= 1 ? 'disabled' : '' ?>>Prev</button>
        </form>
        <form method="POST" class="pagination">
            <input type="hidden" name="customer" value="<?= htmlspecialchars($selectedCustomer) ?>">
            <input type="hidden" name="page" value="<?= $page + 1 ?>">
            <button type="submit" <?= ($page * $devicePageSize >= $totalDevices) ? 'disabled' : '' ?>>Next</button>
        </form>

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
                <tr onclick="document.getElementById('drill<?= $d['Id'] ?>').submit();">
                    <td><?= htmlspecialchars($d['SerialNumber'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['Product']['Model'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['Product']['Brand'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['IpAddress'] ?? '-') ?></td>
                    <td><?= ($d['IsOffline'] ?? false) ? 'Offline' : 'Online' ?></td>
                </tr>
                <?php if ($drillId === $d['Id'] && $deviceDetails): ?>
                <tr class="drilldown">
                    <td colspan="5">
                        <pre><?= htmlspecialchars(print_r($deviceDetails, true)) ?></pre>
                    </td>
                </tr>
                <?php endif; ?>
                <form id="drill<?= $d['Id'] ?>" method="POST">
                    <input type="hidden" name="customer" value="<?= htmlspecialchars($selectedCustomer) ?>">
                    <input type="hidden" name="page" value="<?= $page ?>">
                    <input type="hidden" name="drill" value="<?= $d['Id'] ?>">
                </form>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($selectedCustomer): ?>
        <p>No devices found for selected customer.</p>
    <?php endif; ?>
</body>
</html>
