<?php
/**
 * Force Populate ALL Drill-Downs
 *
 * This script will loop until ALL devices in the cache have drill-down data.
 * It bypasses time limits and processes devices in batches.
 */

set_time_limit(0); // No time limit
ini_set('memory_limit', '1G');

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once dirname(__DIR__) . '/functions.php';

// Check if running via CLI or requireAuth for web
if (php_sapi_name() !== 'cli') {
    requireAuth();
    header('Content-Type: text/plain; charset=utf-8');
}

echo "=== FORCE POPULATE ALL DRILL-DOWNS ===\n\n";

$startTime = microtime(true);
$stats = [
    'total_devices' => 0,
    'already_cached' => 0,
    'newly_cached' => 0,
    'failed' => 0,
    'api_calls' => 0,
    'batches' => 0
];

try {
    $pdo = getDatabase();

    // Ensure drill-down table exists
    $drilldownTable = DB_PREFIX . 'cache_device_drilldown';
    $devicesTable = DB_PREFIX . 'cache_devices';

    echo "Step 1: Counting devices...\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM $devicesTable");
    $stats['total_devices'] = $stmt->fetchColumn();
    echo "Total devices in cache: {$stats['total_devices']}\n\n";

    // Process in batches to avoid memory issues
    $batchSize = 100;
    $offset = 0;

    while (true) {
        $stats['batches']++;
        echo "Batch #{$stats['batches']} (offset: $offset, size: $batchSize)\n";
        echo str_repeat('-', 80) . "\n";

        // Get devices without drill-down
        $stmt = $pdo->prepare("
            SELECT cd.serial_number, cd.device_data, cd.customer_code
            FROM $devicesTable cd
            LEFT JOIN $drilldownTable cdd ON cd.serial_number = cdd.serial_number
            WHERE cdd.serial_number IS NULL
            LIMIT $batchSize OFFSET $offset
        ");
        $stmt->execute();
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($devices)) {
            echo "No more devices to process!\n";
            break;
        }

        echo "Processing " . count($devices) . " devices without drill-down...\n";

        foreach ($devices as $device) {
            $serialNumber = $device['serial_number'];
            $deviceData = json_decode($device['device_data'], true);

            if (!$deviceData) {
                echo "  ✗ $serialNumber - Invalid device data\n";
                $stats['failed']++;
                continue;
            }

            try {
                // Fetch drill-down data
                $drillDownData = fetchDeviceDrillDownData($deviceData);
                $stats['api_calls']++;

                if ($drillDownData) {
                    // Cache it
                    cacheDeviceDrillDownSimple($pdo, $serialNumber, $drillDownData);
                    $stats['newly_cached']++;
                    echo "  ✓ $serialNumber - Drill-down cached\n";
                } else {
                    echo "  ⚠ $serialNumber - No drill-down data returned\n";
                    $stats['failed']++;
                }

                // Rate limiting delay
                usleep(250000); // 250ms

            } catch (Exception $e) {
                echo "  ✗ $serialNumber - Error: {$e->getMessage()}\n";
                $stats['failed']++;

                // If rate limited, wait longer
                if (stripos($e->getMessage(), 'rate limit') !== false) {
                    echo "  ⏱ Rate limited - waiting 30 seconds...\n";
                    sleep(30);
                }
            }
        }

        echo "\nBatch summary: {$stats['newly_cached']} cached, {$stats['failed']} failed\n";
        echo "Progress: " . ($stats['newly_cached'] + $stats['already_cached']) . " / {$stats['total_devices']}\n\n";

        // Don't increment offset - we're always getting the first N devices without drill-down
        // $offset += $batchSize;

        // Check if we're done
        $stmt = $pdo->query("
            SELECT COUNT(*)
            FROM $devicesTable cd
            LEFT JOIN $drilldownTable cdd ON cd.serial_number = cdd.serial_number
            WHERE cdd.serial_number IS NULL
        ");
        $remaining = $stmt->fetchColumn();

        if ($remaining == 0) {
            echo "✓ All devices now have drill-down data!\n";
            break;
        }

        echo "Remaining: $remaining devices\n";
        echo "Continuing to next batch...\n\n";

        // Safety pause between batches
        sleep(5);
    }

    $duration = round(microtime(true) - $startTime, 2);

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "FINAL STATISTICS\n";
    echo str_repeat('=', 80) . "\n";
    echo "Total devices: {$stats['total_devices']}\n";
    echo "Newly cached: {$stats['newly_cached']}\n";
    echo "Failed: {$stats['failed']}\n";
    echo "API calls: {$stats['api_calls']}\n";
    echo "Batches processed: {$stats['batches']}\n";
    echo "Duration: {$duration}s\n";

    // Final verification
    $stmt = $pdo->query("SELECT COUNT(*) FROM $drilldownTable");
    $finalCount = $stmt->fetchColumn();
    $coverage = $stats['total_devices'] > 0 ? round(($finalCount / $stats['total_devices']) * 100, 2) : 0;

    echo "\nFinal drill-down count: $finalCount\n";
    echo "Coverage: {$coverage}%\n";

} catch (Exception $e) {
    echo "\nCRITICAL ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// Helper functions

function fetchDeviceDrillDownData($device) {
    $deviceId = $device['Id'] ?? null;

    if (!$deviceId) {
        return null;
    }

    $drillDownData = [];

    // 1. Counter details
    try {
        $counterResponse = callMPSMAPI('Counter/ListDetailed', [
            'DeviceId' => $deviceId
        ]);
        if ($counterResponse && isset($counterResponse['Counters'])) {
            $drillDownData['counterDetails'] = $counterResponse['Counters'];
        }
    } catch (Exception $e) {
        // Continue even if this fails
    }

    // 2. Device actions/health
    try {
        $actionsResponse = callMPSMAPI('SdsAction/GetDeviceActions', [
            'DeviceId' => $deviceId
        ]);
        if ($actionsResponse) {
            $drillDownData['deviceHealth'] = $actionsResponse;
        }
    } catch (Exception $e) {
        // Continue even if this fails
    }

    // 3. Supply alerts
    try {
        $alertsResponse = callMPSMAPI('SupplyAlert/List', [
            'DeviceId' => $deviceId
        ]);
        if ($alertsResponse && isset($alertsResponse['SupplyAlerts'])) {
            $drillDownData['supplyAlerts'] = $alertsResponse['SupplyAlerts'];
        }
    } catch (Exception $e) {
        // Continue even if this fails
    }

    return empty($drillDownData) ? null : $drillDownData;
}

function cacheDeviceDrillDownSimple($pdo, $serialNumber, $drillDownData) {
    $table = DB_PREFIX . 'cache_device_drilldown';

    $hasAlerts = !empty($drillDownData['supplyAlerts']);
    $hasSupplies = !empty($drillDownData['supplyAlerts']);

    $stmt = $pdo->prepare("
        INSERT INTO $table (serial_number, drilldown_data, has_alerts, has_supplies, cached_at)
        VALUES (:serial, :data, :alerts, :supplies, NOW())
        ON DUPLICATE KEY UPDATE
            drilldown_data = VALUES(drilldown_data),
            has_alerts = VALUES(has_alerts),
            has_supplies = VALUES(has_supplies),
            cached_at = NOW()
    ");

    $stmt->execute([
        ':serial' => $serialNumber,
        ':data' => json_encode($drillDownData, JSON_UNESCAPED_UNICODE),
        ':alerts' => $hasAlerts ? 1 : 0,
        ':supplies' => $hasSupplies ? 1 : 0
    ]);
}

echo "\n=== PROCESS COMPLETE ===\n";
