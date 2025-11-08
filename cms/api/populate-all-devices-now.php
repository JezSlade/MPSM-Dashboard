<?php
/**
 * POPULATE ALL DEVICES FROM API
 *
 * This script will fetch ALL devices from the API and populate the database
 * It bypasses refresh-cache-enhanced.php and does it directly
 */

set_time_limit(0);
ini_set('memory_limit', '1G');

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once dirname(__DIR__) . '/functions.php';

if (php_sapi_name() !== 'cli') {
    requireAuth();
    header('Content-Type: text/plain; charset=utf-8');
}

echo "=== POPULATING ALL DEVICES FROM API ===\n\n";

$startTime = microtime(true);
$stats = [
    'total_fetched' => 0,
    'inserted' => 0,
    'updated' => 0,
    'errors' => 0,
    'pages' => 0
];

try {
    $pdo = getDatabase();
    $prefix = DB_PREFIX;

    echo "Step 1: Fetching all devices from API...\n\n";

    $pageNumber = 1;
    $maxPages = 500;
    $allDevices = [];

    while ($pageNumber <= $maxPages) {
        echo "Fetching page $pageNumber...";

        // Call API directly through /mps-api/query
        $payload = json_encode([
            'action' => 'Device/List',
            'params' => [
                'PageNumber' => $pageNumber,
                'PageRows' => 50,
                'SortColumn' => 'Id',
                'SortOrder' => 0
            ]
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $payload,
                'timeout' => 30
            ]
        ]);

        $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

        if (!$response) {
            echo " FAILED\n";
            break;
        }

        $decoded = json_decode($response, true);

        if (!$decoded || !isset($decoded['success']) || !$decoded['success']) {
            echo " API ERROR\n";
            break;
        }

        $devices = $decoded['data'] ?? [];
        $deviceCount = count($devices);
        $stats['total_fetched'] += $deviceCount;
        $stats['pages']++;

        echo " Got $deviceCount devices (Total: {$stats['total_fetched']})\n";

        if ($deviceCount == 0) {
            echo "No more devices\n";
            break;
        }

        // Store devices immediately
        foreach ($devices as $device) {
            $serialNumber = $device['AssetNumber'] ?? $device['SerialNumber'] ?? null;
            $customerCode = $device['CustomerCode'] ?? null;

            if (!$serialNumber) {
                $stats['errors']++;
                continue;
            }

            try {
                $sql = "INSERT INTO {$prefix}cache_devices
                        (serial_number, device_data, customer_code, is_uninstalled, cached_at)
                        VALUES (:serial, :data, :customer, 0, datetime('now'))
                        ON CONFLICT(serial_number) DO UPDATE SET
                        device_data = :data2,
                        customer_code = :customer2,
                        cached_at = datetime('now')";

                $stmt = $pdo->prepare($sql);
                $deviceJson = json_encode($device, JSON_UNESCAPED_UNICODE);

                $result = $stmt->execute([
                    ':serial' => $serialNumber,
                    ':data' => $deviceJson,
                    ':customer' => $customerCode,
                    ':data2' => $deviceJson,
                    ':customer2' => $customerCode
                ]);

                if ($result) {
                    $stats['inserted']++;
                }

            } catch (Exception $e) {
                echo "  ERROR storing $serialNumber: {$e->getMessage()}\n";
                $stats['errors']++;
            }
        }

        // Check if this was the last page
        if ($deviceCount < 100) {
            echo "\nLast page (< 100 devices)\n";
            break;
        }

        $pageNumber++;
        usleep(100000); // 100ms delay
    }

    $duration = round(microtime(true) - $startTime, 2);

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "FINAL STATISTICS\n";
    echo str_repeat('=', 80) . "\n";
    echo "Total devices fetched: {$stats['total_fetched']}\n";
    echo "Pages processed: {$stats['pages']}\n";
    echo "Inserted/Updated: {$stats['inserted']}\n";
    echo "Errors: {$stats['errors']}\n";
    echo "Duration: {$duration}s\n";
    echo str_repeat('=', 80) . "\n\n";

    // Verify final count
    $stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}cache_devices");
    $finalCount = $stmt->fetchColumn();
    echo "Final device count in DB: $finalCount\n";

} catch (Exception $e) {
    echo "\nCRITICAL ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== COMPLETE ===\n";
