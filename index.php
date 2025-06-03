
<?php
require_once 'env.php';
require_once 'functions.php';

$token = getAccessToken();

// Fetch customer list
$customers = callApiEndpoint(
    "{$baseUrl}Customer/GetCustomers",
    $token,
    [
        "DealerCode" => getenv("DEALER_CODE"),
        "Code" => null,
        "HasHpSds" => null,
        "FilterText" => null,
        "PageNumber" => 1,
        "PageRows" => 2147483647,
        "SortColumn" => "Id",
        "SortOrder" => 0
    ]
);

$selectedCustomerCode = $_GET['customer'] ?? null;
$selectedDeviceId = $_GET['deviceId'] ?? null;

$devices = [];
$alerts = [];
$deviceDetails = null;

if ($selectedCustomerCode) {
    $devices = callApiEndpoint(
        "{$baseUrl}Device/List",
        $token,
        [
            "FilterDealerId" => getenv("DEALER_ID"),
            "FilterCustomerCodes" => [$selectedCustomerCode],
            "PageNumber" => 1,
            "PageRows" => 50,
            "SortColumn" => "Id",
            "SortOrder" => 0
        ]
    );

    $alerts = callApiEndpoint(
        "{$baseUrl}SupplyAlert/List",
        $token,
        [
            "DealerCode" => getenv("DEALER_CODE"),
            "CustomerCode" => $selectedCustomerCode,
            "ManageOption" => 3,
            "HiddenOption" => 3,
            "PageNumber" => 1,
            "PageRows" => 50,
            "SortColumn" => "InitialDate",
            "SortOrder" => 1
        ]
    );
}

if ($selectedDeviceId) {
    $deviceDetails = callApiEndpoint(
        "{$baseUrl}Device/GetDetailedInformations",
        $token,
        [
            "DeviceId" => $selectedDeviceId
        ]
    );
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>MVPPOS</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #111; color: #eee; }
        select, table { margin-top: 10px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { padding: 10px; border: 1px solid #555; text-align: left; }
        th { background-color: #222; }
        tr:hover { background-color: #333; }
        .details { background-color: #222; padding: 10px; margin-top: 20px; border: 1px solid #444; }
    </style>
</head>
<body>
    <h1>MVPPOS: Devices + Drill-down</h1>

    <form method="get">
        <label for="customer">Customer:</label>
        <select name="customer" id="customer" onchange="this.form.submit()">
            <option value="">Select a customer</option>
            <?php foreach ($customers['Result'] ?? [] as $cust): ?>
                <option value="<?= $cust['Code'] ?>" <?= $cust['Code'] === $selectedCustomerCode ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cust['Description']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($devices['Result'] ?? false): ?>
        <h2>Devices (<?= count($devices['Result']) ?>)</h2>
        <table>
            <tr><th>Serial</th><th>Model</th><th>Brand</th><th>IP</th><th>Status</th></tr>
            <?php foreach ($devices['Result'] as $d): ?>
                <tr onclick="window.location.href='?customer=<?= $selectedCustomerCode ?>&deviceId=<?= $d['Id'] ?>'">
                    <td><?= $d['SerialNumber'] ?></td>
                    <td><?= $d['Product']['Model'] ?? '' ?></td>
                    <td><?= $d['Product']['Brand'] ?? '' ?></td>
                    <td><?= $d['IpAddress'] ?? '' ?></td>
                    <td><?= $d['IsOffline'] ? 'Offline' : 'Online' ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php if ($alerts['Result'] ?? false): ?>
        <h2>Alerts (<?= count($alerts['Result']) ?>)</h2>
        <table>
            <tr><th>Serial</th><th>Model</th><th>Warning</th><th>Initial Date</th><th>Actual Date</th></tr>
            <?php foreach ($alerts['Result'] as $a): ?>
                <tr>
                    <td><?= $a['SerialNumber'] ?></td>
                    <td><?= $a['ProductModel'] ?></td>
                    <td><?= $a['Warning'] ?></td>
                    <td><?= $a['InitialDate'] ?></td>
                    <td><?= $a['ActualDate'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php if ($deviceDetails['Result'] ?? false): ?>
        <div class="details">
            <h3>Device Details</h3>
            <pre><?php print_r($deviceDetails['Result']); ?></pre>
        </div>
    <?php endif; ?>
</body>
</html>
