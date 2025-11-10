<?php
/**
 * Quick Cache Warmer - Populate 100 devices to unblock dashboard
 *
 * This runs quickly (under 30 seconds) to provide minimal cache data
 * so the dashboard becomes interactive while waiting for full cache refresh.
 */

require __DIR__ . '/cms/config.php';
require __DIR__ . '/cms/functions.php';

set_time_limit(25); // Stay under 30-second web timeout

$pdo = getDatabase();

echo "Quick Cache Warmer - Populating minimal cache...\n\n";

// Truncate existing cache
$pdo->exec("TRUNCATE TABLE " . DB_PREFIX . "cache_devices");

// Use mps-api/query endpoint (handles auth internally)
$params = [
    'FilterDealerId' => null,
    'FilterDealerCodes' => null,
    'FilterCustomerCodes' => null,
    'ProductBrand' => null,
    'ProductModel' => null,
    'OfficeId' => null,
    'Status' => null,
    'FilterText' => null,
    'PageRows' => 100,
    'PageNumber' => 1,
    'SortColumn' => 'Id',
    'SortOrder' => 0,
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($params),
        'timeout' => 20
    ]
]);

$response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

if ($response === false) {
    die("ERROR: Failed to fetch devices from API\n");
}

$data = json_decode($response, true);
$devices = $data['Devices'] ?? [];

echo "✓ Fetched " . count($devices) . " devices from API\n";

// Insert into cache
$inserted = 0;
$stmt = $pdo->prepare("
    INSERT INTO " . DB_PREFIX . "cache_devices
    (serial_number, model, customer_code, customer_name, location, status, cached_at, device_json)
    VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
");

foreach ($devices as $device) {
    $serial = $device['SerialNumber'] ?? null;
    if (!$serial) continue;

    $stmt->execute([
        $serial,
        $device['Model'] ?? '',
        $device['Customer_Code'] ?? '',
        $device['Customer_Description'] ?? '',
        $device['Location_Name'] ?? '',
        $device['Status'] ?? 'Active',
        json_encode($device)
    ]);

    $inserted++;
}

echo "✓ Inserted $inserted devices into cache\n\n";

echo "Dashboard should now be interactive.\n";
echo "Full cache will populate at next hourly cron (02:00).\n\n";

echo "Visit: https://mpsm.resolutionsbydesign.us/cms/\n";
