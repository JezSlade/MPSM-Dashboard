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

// Get OAuth token
$token = getMPSAuthToken();
if (!$token) {
    die("ERROR: Failed to get OAuth token\n");
}

echo "✓ OAuth token obtained\n";

// Fetch just first page (100 devices)
$url = MPS_API_BASE . 'Device?dealerCode=' . DEFAULT_DEALER_CODE . '&pageSize=100&pageNumber=1';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die("ERROR: API returned HTTP $httpCode\n");
}

$data = json_decode($response, true);
$devices = $data['items'] ?? [];

echo "✓ Fetched " . count($devices) . " devices from API\n";

// Insert into cache
$inserted = 0;
$stmt = $pdo->prepare("
    INSERT INTO " . DB_PREFIX . "cache_devices
    (serial_number, model, customer_code, customer_name, location, status, cached_at, device_json)
    VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
");

foreach ($devices as $device) {
    $serial = $device['serialNumber'] ?? $device['SerialNumber'] ?? null;
    if (!$serial) continue;

    $stmt->execute([
        $serial,
        $device['model'] ?? $device['Model'] ?? '',
        $device['customer']['code'] ?? $device['Customer_Code'] ?? '',
        $device['customer']['description'] ?? $device['Customer_Description'] ?? '',
        $device['locationName'] ?? $device['Location_Name'] ?? '',
        $device['status'] ?? 'Active',
        json_encode($device)
    ]);

    $inserted++;
}

echo "✓ Inserted $inserted devices into cache\n\n";

echo "Dashboard should now be interactive.\n";
echo "Full cache will populate at next hourly cron (02:00).\n\n";

echo "Visit: https://mpsm.resolutionsbydesign.us/cms/\n";
