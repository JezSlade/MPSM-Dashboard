<?php
/**
 * Get ACTUAL Device Count from API
 * Query ALL pages, ALL customers, ALL dealers
 */

set_time_limit(300); // 5 minutes
ini_set('memory_limit', '512M');

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once dirname(__DIR__) . '/functions.php';
requireAuth();

header('Content-Type: text/plain; charset=utf-8');

echo "=== QUERYING ACTUAL API DEVICE COUNT ===\n\n";

try {
    $totalDevices = 0;
    $pageNumber = 1;
    $maxPages = 500; // Check up to 500 pages (25,000 devices max at 50 per page)

    echo "Fetching from Device/List API (NO filters - ALL customers, ALL dealers)...\n\n";

    while ($pageNumber <= $maxPages) {
        $params = [
            'PageNumber' => $pageNumber,
            'PageRows' => 50,
            'SortColumn' => 'Id',
            'SortOrder' => 0
            // NO FilterDealerId
            // NO FilterDealerCodes
            // NO FilterCustomerCodes
            // This gets EVERYTHING
        ];

        try {
            $response = callMPSAPI('Device/List', $params);

            if (!$response || !isset($response['Devices'])) {
                echo "Page $pageNumber: No response or no Devices array - STOPPING\n";
                break;
            }

            $devices = $response['Devices'];
            $deviceCount = count($devices);
            $totalDevices += $deviceCount;

            // Show progress
            if ($pageNumber <= 5 || $pageNumber % 10 == 0 || $deviceCount < 50) {
                echo "Page $pageNumber: $deviceCount devices (Running total: $totalDevices)\n";
            }

            // If we got less than 50, we're done
            if ($deviceCount < 50) {
                echo "\nLast page reached at page $pageNumber with $deviceCount devices\n";
                break;
            }

            $pageNumber++;
            usleep(100000); // 100ms delay

        } catch (Exception $e) {
            echo "\nERROR on page $pageNumber: " . $e->getMessage() . "\n";

            // If rate limited, wait and retry
            if (stripos($e->getMessage(), 'rate limit') !== false) {
                echo "Rate limited - waiting 30 seconds...\n";
                sleep(30);
                continue; // Retry same page
            }

            break;
        }
    }

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "FINAL COUNT: $totalDevices devices\n";
    echo "Pages fetched: " . ($pageNumber - 1) . "\n";
    echo str_repeat('=', 80) . "\n\n";

    // Now compare with database
    $pdo = getDatabase();

    $stmt = $pdo->query("SELECT COUNT(*) FROM " . DB_PREFIX . "cache_devices");
    $dbDevices = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM " . DB_PREFIX . "cache_device_drilldown");
    $dbDrilldown = $stmt->fetchColumn();

    echo "DATABASE STATUS:\n";
    echo "Devices in DB: $dbDevices\n";
    echo "Drill-downs in DB: $dbDrilldown\n";
    echo "Missing from DB: " . ($totalDevices - $dbDevices) . "\n";
    echo "Missing drill-downs: " . ($dbDevices - $dbDrilldown) . "\n\n";

    echo "CRITICAL ISSUES:\n";
    if ($totalDevices > $dbDevices) {
        echo "⚠️  DATABASE IS MISSING " . ($totalDevices - $dbDevices) . " DEVICES!\n";
        echo "The refresh-cache script is NOT fetching all devices from the API.\n";
        echo "Need to fix the Device/List pagination logic.\n\n";
    }

    if ($dbDevices > $dbDrilldown) {
        echo "⚠️  DATABASE IS MISSING " . ($dbDevices - $dbDrilldown) . " DRILL-DOWNS!\n";
        echo "Need to run force-populate-all-drilldowns.php\n\n";
    }

    echo "ACTUAL TOTAL IN API: $totalDevices devices\n";

} catch (Exception $e) {
    echo "\nFATAL ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
