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

$clientId       = $_ENV['CLIENT_ID']       ?? '';
$clientSecret   = $_ENV['CLIENT_SECRET']   ?? '';
$username       = $_ENV['USERNAME']        ?? '';
$password       = $_ENV['PASSWORD']        ?? '';
$scope          = $_ENV['SCOPE']           ?? '';
$tokenUrl       = $_ENV['TOKEN_URL']       ?? '';
$baseUrl        = rtrim($_ENV['BASE_URL']   ?? '', '/');
$dealerCode     = $_ENV['DEALER_CODE']     ?? '';
$dealerId       = $_ENV['DEALER_ID']       ?? '';
$debug          = ($_ENV['DEBUG']           ?? 'false') === 'true';
$devicePageSize = intval($_ENV['DEVICE_PAGE_SIZE'] ?? 50);

function getAccessToken($url, $clientId, $clientSecret, $username, $password, $scope) {
    $data = http_build_query([
        'grant_type'    => 'password',
        'client_id'     => $clientId,
        'client_secret' => $clientSecret,
        'username'      => $username,
        'password'      => $password,
        'scope'         => $scope
    ]);
    $opts = ['http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $data,
        'ignore_errors' => true
    ]];
    $result = file_get_contents($url, false, stream_context_create($opts));
    return json_decode($result, true)['access_token'] ?? null;
}

function postJson($url, $token, $payload) {
    $opts = ['http' => [
        'method'  => 'POST',
        'header'  =>
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
        "Code"       => null,
        "HasHpSds"   => null,
        "FilterText" => null,
        "PageNumber" => 1,
        "PageRows"   => 2147483647,
        "SortColumn" => "Id",
        "SortOrder"  => 0
    ])['Result'] ?? [];
}

function callGetDevices($baseUrl, $token, $dealerId, $customerCode, $pageNumber, $pageRows) {
    return postJson("$baseUrl/Device/List", $token, [
        "FilterDealerId"      => $dealerId,
        "FilterCustomerCodes" => [$customerCode],
        "ProductBrand"        => null,
        "ProductModel"        => null,
        "OfficeId"            => null,
        "Status"              => 1,
        "FilterText"          => null,
        "PageNumber"          => $pageNumber,
        "PageRows"            => $pageRows,
        "SortColumn"          => "Id",
        "SortOrder"           => 0
    ]);
}

function callGetDeviceDetails($baseUrl, $token, $deviceId) {
    return postJson("$baseUrl/Device/GetDetailedInformations", $token, [
        "DeviceId" => $deviceId
    ]);
}

function callGetDeviceAlerts($baseUrl, $token, $dealerCode, $deviceId) {
    return postJson("$baseUrl/SupplyAlert/List", $token, [
        "DealerCode"       => $dealerCode,
        "DeviceId"         => $deviceId,
        "SerialNumber"     => null,
        "AssetNumber"      => null,
        "InitialFrom"      => null,
        "InitialTo"        => null,
        "ExhaustedFrom"    => null,
        "ExhaustedTo"      => null,
        "Brand"            => null,
        "Model"            => null,
        "OfficeDescription"=> null,
        "SupplySetDescription"=> null,
        "CustomerCode"     => null,
        "FilterCustomerText"=> null,
        "ManageOption"     => null,
        "InstallationOption"=> null,
        "CancelOption"     => null,
        "HiddenOption"     => null,
        "SupplyType"       => null,
        "ColorType"        => null,
        "ExcludeForStockShippedSupplies"=> false,
        "FilterText"       => null,
        "PageNumber"       => 1,
        "PageRows"         => 50,
        "SortColumn"       => "InitialDate",
        "SortOrder"        => 0
    ]);
}

function callGetDeviceCounters($baseUrl, $token, $dealerCode, $customerCode, $serialNumber) {
    return postJson("$baseUrl/Counter/ListDetailed", $token, [
        "DealerCode"         => $dealerCode,
        "CustomerCode"       => $customerCode,
        "SerialNumber"       => $serialNumber,
        "AssetNumber"        => null,
        "CounterDetaildTags" => null
    ]);
}

// == STATE ==
$token = getAccessToken($tokenUrl, $clientId, $clientSecret, $username, $password, $scope);
if (!$token) {
    die("❌ Failed to get access token.");
}

$customers        = callGetCustomers($baseUrl, $token, $dealerCode);
$selectedCustomer = $_POST['customer']    ?? null;
$page             = max(1, intval($_POST['page'] ?? 1));
$drillId          = $_POST['drill']       ?? null;

$devicesData = $selectedCustomer
    ? callGetDevices($baseUrl, $token, $dealerId, $selectedCustomer, $page, $devicePageSize)
    : [];
$devices      = $devicesData['Result']   ?? [];
$totalDevices = $devicesData['TotalRows'] ?? 0;

// Sort devices so those generating alerts come first
usort($devices, function($a, $b) {
    return ($b['IsAlertGenerator'] ?? false) <=> ($a['IsAlertGenerator'] ?? false);
});

$deviceDetails = $drillId
    ? callGetDeviceDetails($baseUrl, $token, $drillId)
    : null;

$deviceAlerts = $drillId
    ? callGetDeviceAlerts($baseUrl, $token, $dealerCode, $drillId)
    : null;

$deviceSerial = null;
if ($drillId && $deviceDetails && ($deviceDetails['IsValid'] ?? false)) {
    $deviceSerial = $deviceDetails['Result']['SdsDevice']['SerialNumber'] 
                  ?? $deviceDetails['Result']['SerialNumber'] 
                  ?? null;
}
$deviceCounters = ($drillId && $deviceSerial)
    ? callGetDeviceCounters($baseUrl, $token, $dealerCode, $selectedCustomer, $deviceSerial)
    : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MVPPOS - Device Drill-down</title>
    <style>
        body { 
            background: #111; 
            color: #eee; 
            font-family: monospace; 
            padding: 2rem; 
        }
        h1, h2, h3 { 
            color: #00ffcc; 
            margin-bottom: 0.5rem; 
        }
        h4 { 
            color: #0ff; 
            margin: 0.5rem 0; 
        }
        form { 
            margin-bottom: 1rem; 
        }
        select, button { 
            padding: 5px; 
            font-size: 1rem; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: #1f1f1f; 
            margin-top: 1rem; 
        }
        th, td { 
            padding: 10px; 
            border: 1px solid #333; 
            text-align: left; 
        }
        th { 
            background: #333; 
        }
        tr:hover { 
            background: #2a2a2a; 
            cursor: pointer; 
        }
        .drilldown { 
            background: #2b2b2b; 
            border-top: 2px solid #555; 
            font-size: 0.9rem; 
        }
        .section { 
            margin-top: 1rem; 
            padding: 0.5rem; 
            background: #222; 
        }
        .subtable { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 0.5rem; 
        }
        .subtable th, .subtable td { 
            padding: 8px; 
            border: 1px solid #444; 
        }
        .subtable th { 
            background: #444; 
        }
        .pagination { 
            margin-top: 1rem; 
        }
    </style>
</head>
<body>
    <h1>MVPPOS: Devices + Drill-down</h1>

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
            <input type="hidden" name="page" value="<?= max(1, $page - 1) ?>">
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
                    <th>Alert?</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($devices as $d): ?>
                <tr onclick="document.getElementById('drill<?= $d['Id'] ?>').submit();">
                    <td><?= htmlspecialchars($d['SerialNumber']   ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['Product']['Model'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['Product']['Brand'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['IpAddress']       ?? '-') ?></td>
                    <td><?= ($d['IsOffline'] ?? false) ? 'Offline' : 'Online' ?></td>
                    <td><?= ($d['IsAlertGenerator'] ?? false) ? 'Yes' : 'No' ?></td>
                </tr>

                <?php if ($drillId === $d['Id'] && $deviceDetails && ($deviceDetails['IsValid'] ?? false)): ?>
                <tr class="drilldown">
                    <td colspan="6">
                        <h3>Device Details</h3>

                        <div class="section">
                            <h4>Device ID</h4>
                            <?= htmlspecialchars($deviceDetails['Result']['DeviceId']) ?>
                        </div>

                        <?php if (!empty($deviceDetails['Result']['DetailsBySupply'])): ?>
                        <div class="section">
                            <h4>Details by Supply</h4>
                            <table class="subtable">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Color</th>
                                        <th>Residual %</th>
                                        <th>Expected Exhaustion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($deviceDetails['Result']['DetailsBySupply'] as $supply): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($supply['SupplyType'])  ?></td>
                                        <td><?= htmlspecialchars($supply['ColorType'])   ?></td>
                                        <td><?= htmlspecialchars($supply['ResidualDurationPercentage'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($supply['ExpectedExhaustion'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($deviceDetails['Result']['SuppliesInfo'])): ?>
                        <div class="section">
                            <h4>Supplies Info</h4>
                            <pre><?= htmlspecialchars(print_r($deviceDetails['Result']['SuppliesInfo'], true)) ?></pre>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($deviceDetails['Result']['MaintenanceKitLevels'])): ?>
                        <div class="section">
                            <h4>Maintenance Kit Levels</h4>
                            <pre><?= htmlspecialchars(print_r($deviceDetails['Result']['MaintenanceKitLevels'], true)) ?></pre>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($deviceDetails['Result']['TechnicalInformation'])): ?>
                        <div class="section">
                            <h4>Technical Information</h4>
                            <pre><?= htmlspecialchars($deviceDetails['Result']['TechnicalInformation']) ?></pre>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($deviceAlerts['Result'])): ?>
                        <div class="section">
                            <h4>Device Alerts</h4>
                            <table class="subtable">
                                <thead>
                                    <tr>
                                        <th>Serial</th>
                                        <th>Model</th>
                                        <th>Warning</th>
                                        <th>Initial Date</th>
                                        <th>Actual Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($deviceAlerts['Result'] as $a): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($a['SerialNumber']   ?? '-') ?></td>
                                        <td><?= htmlspecialchars($a['ProductModel']    ?? '-') ?></td>
                                        <td><?= htmlspecialchars($a['Warning']         ?? '-') ?></td>
                                        <td><?= htmlspecialchars(substr($a['InitialDate'] ?? '', 0, 10)) ?></td>
                                        <td><?= htmlspecialchars(substr($a['ActualDate']  ?? '', 0, 10)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                        <?php if ($deviceCounters && !empty($deviceCounters['Result'])): ?>
                        <div class="section">
                            <h4>Device Counters (Detailed)</h4>
                            <table class="subtable">
                                <thead>
                                    <tr>
                                        <?php foreach (array_keys($deviceCounters['Result'][0]) as $col): ?>
                                            <th><?= htmlspecialchars($col) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($deviceCounters['Result'] as $counter): ?>
                                    <tr>
                                        <?php foreach ($counter as $val): ?>
                                            <td><?= htmlspecialchars((string)$val) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                    </td>
                </tr>
                <?php endif; ?>

                <form id="drill<?= $d['Id'] ?>" method="POST">
                    <input type="hidden" name="customer" value="<?= htmlspecialchars($selectedCustomer) ?>">
                    <input type="hidden" name="page"     value="<?= $page ?>">
                    <input type="hidden" name="drill"    value="<?= $d['Id'] ?>">
                </form>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($selectedCustomer): ?>
        <p>No devices found.</p>
    <?php endif; ?>
</body>
</html>
