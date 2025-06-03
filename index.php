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
        "DealerCode"            => $dealerCode,
        "DeviceId"              => $deviceId,
        "SerialNumber"          => null,
        "AssetNumber"           => null,
        "InitialFrom"           => null,
        "InitialTo"             => null,
        "ExhaustedFrom"         => null,
        "ExhaustedTo"           => null,
        "Brand"                 => null,
        "Model"                 => null,
        "OfficeDescription"     => null,
        "SupplySetDescription"  => null,
        "CustomerCode"          => null,
        "FilterCustomerText"    => null,
        "ManageOption"          => null,
        "InstallationOption"    => null,
        "CancelOption"          => null,
        "HiddenOption"          => null,
        "SupplyType"            => null,
        "ColorType"             => null,
        "ExcludeForStockShippedSupplies" => false,
        "FilterText"            => null,
        "PageNumber"            => 1,
        "PageRows"              => 50,
        "SortColumn"            => "InitialDate",
        "SortOrder"             => 0
    ]);
}

function callGetDeviceCounters($baseUrl, $token, $dealerCode, $customerCode, $serialNumber, $fromDate, $toDate) {
    return postJson("$baseUrl/Counter/List", $token, [
        "DealerCode"   => $dealerCode,
        "CustomerCode" => $customerCode,
        "SerialNumber" => $serialNumber,
        "AssetNumber"  => null,
        "FromDate"     => $fromDate,
        "ToDate"       => $toDate
    ]);
}

function callGetDevicePanelMessages($baseUrl, $token, $dealerCode, $deviceId) {
    return postJson("$baseUrl/Device/PanelMessage/List", $token, [
        "DealerCode"   => $dealerCode,
        "DeviceId"     => $deviceId,
        "SerialNumber" => null,
        "AssetNumber"  => null,
        "FromDate"     => null,
        "ToDate"       => null,
        "PageNumber"   => 1,
        "PageRows"     => 50,
        "SortColumn"   => "DateTime",
        "SortOrder"    => 0
    ]);
}

function callGetDeviceNotifications($baseUrl, $token, $dealerCode, $deviceId) {
    return postJson("$baseUrl/Device/Notification/List", $token, [
        "DealerCode"   => $dealerCode,
        "DeviceId"     => $deviceId,
        "SerialNumber" => null,
        "AssetNumber"  => null,
        "FromDate"     => null,
        "ToDate"       => null,
        "PageNumber"   => 1,
        "PageRows"     => 50,
        "SortColumn"   => "Timestamp",
        "SortOrder"    => 0
    ]);
}

function callGetDeviceLogs($baseUrl, $token, $dealerCode, $deviceId) {
    return postJson("$baseUrl/Device/Log/List", $token, [
        "DealerCode"   => $dealerCode,
        "DeviceId"     => $deviceId,
        "LogType"      => null,
        "FromDate"     => null,
        "ToDate"       => null,
        "PageNumber"   => 1,
        "PageRows"     => 50,
        "SortColumn"   => "DateTime",
        "SortOrder"    => 1
    ]);
}

function callGetSupplyHistory($baseUrl, $token, $dealerCode, $deviceId, $fromDate, $toDate) {
    return postJson("$baseUrl/Device/SupplyHistory/List", $token, [
        "DealerCode"   => $dealerCode,
        "DeviceId"     => $deviceId,
        "FromDate"     => $fromDate,
        "ToDate"       => $toDate,
        "PageNumber"   => 1,
        "PageRows"     => 50,
        "SortColumn"   => "Date",
        "SortOrder"    => 0
    ]);
}

function callGetJobHistory($baseUrl, $token, $dealerCode, $deviceId, $fromDate, $toDate) {
    return postJson("$baseUrl/Device/JobHistory/List", $token, [
        "DealerCode"   => $dealerCode,
        "DeviceId"     => $deviceId,
        "SerialNumber" => null,
        "AssetNumber"  => null,
        "FromDate"     => $fromDate,
        "ToDate"       => $toDate,
        "PageNumber"   => 1,
        "PageRows"     => 50,
        "SortColumn"   => "JobDate",
        "SortOrder"    => 0
    ]);
}

function callGetConfiguration($baseUrl, $token, $deviceId) {
    return postJson("$baseUrl/Device/Configuration/Get", $token, [
        "DeviceId" => $deviceId
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

// Panel messages
$panelMessages = $drillId
    ? callGetDevicePanelMessages($baseUrl, $token, $dealerCode, $drillId)
    : null;

// Notifications
$notifications = $drillId
    ? callGetDeviceNotifications($baseUrl, $token, $dealerCode, $drillId)
    : null;

// Logs
$logs = $drillId
    ? callGetDeviceLogs($baseUrl, $token, $dealerCode, $drillId)
    : null;

// Supply history last 30 days
$supplyHistory = null;
if ($drillId) {
    $toDate   = gmdate('Y-m-d\\TH:i:s\\Z');
    $fromDate = gmdate('Y-m-d\\TH:i:s\\Z', strtotime('-30 days'));
    $supplyHistory = callGetSupplyHistory($baseUrl, $token, $dealerCode, $drillId, $fromDate, $toDate);
}

// Job history last 30 days
$jobHistory = null;
if ($drillId) {
    $toDate   = gmdate('Y-m-d\\TH:i:s\\Z');
    $fromDate = gmdate('Y-m-d\\TH:i:s\\Z', strtotime('-30 days'));
    $jobHistory = callGetJobHistory($baseUrl, $token, $dealerCode, $drillId, $fromDate, $toDate);
}

// Configuration
$configuration = $drillId
    ? callGetConfiguration($baseUrl, $token, $drillId)
    : null;

// Device-specific meters last 7 days (Counter/List)
$deviceSerial   = null;
if ($drillId && $deviceDetails && ($deviceDetails['IsValid'] ?? false)) {
    $deviceSerial = $deviceDetails['Result']['SdsDevice']['SerialNumber'] 
                  ?? $deviceDetails['Result']['SerialNumber'] 
                  ?? null;
}
$deviceCounters = null;
if ($drillId && $deviceSerial && $selectedCustomer) {
    $toDate   = gmdate('Y-m-d\\TH:i:s\\Z');
    $fromDate = gmdate('Y-m-d\\TH:i:s\\Z', strtotime('-7 days'));
    $deviceCounters = callGetDeviceCounters(
        $baseUrl, $token, $dealerCode, $selectedCustomer, $deviceSerial, $fromDate, $toDate
    );
}
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
                                        <?php 
                                          $firstSupply = $deviceDetails['Result']['DetailsBySupply'][0];
                                          foreach (array_keys($firstSupply) as $col): ?>
                                            <th><?= htmlspecialchars($col) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($deviceDetails['Result']['DetailsBySupply'] as $supply): ?>
                                    <tr>
                                        <?php foreach ($supply as $val): ?>
                                            <td><?= htmlspecialchars((string)$val) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($deviceDetails['Result']['SuppliesInfo'])): ?>
                        <div class="section">
                            <h4>Supplies Info</h4>
                            <table class="subtable">
                                <thead>
                                    <tr>
                                        <th>Key</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($deviceDetails['Result']['SuppliesInfo'] as $key => $val): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($key) ?></td>
                                        <td><?= htmlspecialchars((string)$val) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($deviceDetails['Result']['MaintenanceKitLevels'])): ?>
                        <div class="section">
                            <h4>Maintenance Kit Levels</h4>
                            <table class="subtable">
                                <thead>
                                    <tr>
                                        <?php foreach (array_keys($deviceDetails['Result']['MaintenanceKitLevels'][0]) as $col): ?>
                                            <th><?= htmlspecialchars($col) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($deviceDetails['Result']['MaintenanceKitLevels'] as $kit): ?>
                                    <tr>
                                        <?php foreach ($kit as $val): ?>
                                            <td><?= htmlspecialchars((string)$val) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($deviceDetails['Result']['TechnicalInformation'])): ?>
                        <div class="section">
                            <h4>Technical Information</h4>
                            <table class="subtable">
                                <thead>
                                    <tr>
                                        <th>Key</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($deviceDetails['Result']['TechnicalInformation'] as $key => $val): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($key) ?></td>
                                        <td><?= htmlspecialchars((string)$val) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
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
                                <?php 
                                  $alerts = $deviceAlerts['Result'];
                                  foreach ($alerts as $a): ?>
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

                        <?php if ($panelMessages && !empty($panelMessages['Result'])): ?>
                        <div class="section">
                            <h4>Panel Messages</h4>
                            <table class="subtable">
                                <thead>
                                    <tr>
                                        <th>DateTime</th>
                                        <th>Message</th>
                                        <th>Severity</th>
                                        <th>Cleared?</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                  $msgs = $panelMessages['Result'];
                                  foreach ($msgs as $pm): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(substr($pm['DateTime'] ?? '', 0, 19)) ?></td>
                                        <td><?= htmlspecialchars($pm['MessageText']   ?? '-') ?></td>
                                        <td><?= htmlspecialchars((string)($pm['Severity'] ?? '')) ?></td>
                                        <td><?= ($pm['IsCleared'] ?? false) ? 'Yes' : 'No' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                        <?php if ($notifications && !empty($notifications['Result'])): ?>
                        <div class="section">
                            <h4>Recent Notifications</h4>
                            <table class="subtable">
                                <thead>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Event Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                  $notes = $notifications['Result'];
                                  foreach ($notes as $n): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(substr($n['Timestamp'] ?? '', 0, 19)) ?></td>
                                        <td><?= htmlspecialchars($n['EventType']    ?? '-') ?></td>
                                        <td><?= htmlspecialchars($n['Description']  ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                        <?php if ($logs && !empty($logs['Result'])): ?>
                        <div class="section">
                            <h4>Device Logs</h4>
                            <table class="subtable">
                                <thead>
                                    <tr>
                                        <th>DateTime</th>
                                        <th>Log Type</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                  $ls = $logs['Result'];
                                  foreach ($ls as $l): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(substr($l['DateTime'] ?? '', 0, 19)) ?></td>
                                        <td><?= htmlspecialchars($l['LogType']       ?? '-') ?></td>
                                        <td><?= htmlspecialchars($l['Message']       ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                        <?php if ($supplyHistory && !empty($supplyHistory['Result'])): ?>
                        <div class="section">
                            <h4>Supply History (Last 30 Days)</h4>
                            <table class="subtable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>SupplyType</th>
                                        <th>ColorType</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                  $shs = $supplyHistory['Result'];
                                  foreach ($shs as $sh): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(substr($sh['Date'] ?? '', 0, 10)) ?></td>
                                        <td><?= htmlspecialchars($sh['SupplyType'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($sh['ColorType']  ?? '-') ?></td>
                                        <td><?= htmlspecialchars((string)($sh['Quantity'] ?? '')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                        <?php if ($jobHistory && !empty($jobHistory['Result'])): ?>
                        <div class="section">
                            <h4>Print Job History (Last 30 Days)</h4>
                            <table class="subtable">
                                <thead>
                                    <tr>
                                        <th>JobDate</th>
                                        <th>UserName</th>
                                        <th>Pages</th>
                                        <th>ColorPages</th>
                                        <th>TotalPages</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                  $jhs = $jobHistory['Result'];
                                  foreach ($jhs as $jh): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(substr($jh['JobDate'] ?? '', 0, 19)) ?></td>
                                        <td><?= htmlspecialchars($jh['UserName']   ?? '-') ?></td>
                                        <td><?= htmlspecialchars((string)($jh['Pages']    ?? '')) ?></td>
                                        <td><?= htmlspecialchars((string)($jh['ColorPages'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars((string)($jh['TotalPages'] ?? '')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                        <?php if ($configuration && !empty($configuration['Result'])): ?>
                        <div class="section">
                            <h4>Current Configuration</h4>
                            <table class="subtable">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                  $cfg = $configuration['Result'];
                                  foreach ($cfg as $key => $val): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($key) ?></td>
                                        <td><?= htmlspecialchars((string)$val) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                        <?php if ($deviceCounters && !empty($deviceCounters['Result'])): ?>
                        <div class="section">
                            <h4>Device Meters (Last 7 Days)</h4>
                            <table class="subtable">
                                <thead>
                                    <tr>
                                        <?php 
                                          $firstCounter = $deviceCounters['Result'][0];
                                          foreach (array_keys($firstCounter) as $col): ?>
                                            <th><?= htmlspecialchars($col) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                  $dcs = $deviceCounters['Result'];
                                  foreach ($dcs as $counter): ?>
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
