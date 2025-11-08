<?php
/**
 * Diagnose Cache Population Issue
 * Root cause analysis for why drill-down stopped at 100 devices
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once dirname(__DIR__) . '/functions.php';
requireAuth();

header('Content-Type: text/plain; charset=utf-8');

echo "=== CACHE POPULATION ROOT CAUSE ANALYSIS ===\n\n";

try {
    $pdo = getDatabase();

    // 1. Check actual database counts
    echo "1. CURRENT DATABASE STATE\n";
    echo str_repeat('=', 80) . "\n";

    $stmt = $pdo->query("SELECT COUNT(*) FROM " . DB_PREFIX . "cache_devices");
    $totalDevices = $stmt->fetchColumn();
    echo "Total devices in mpsm_cache_devices: $totalDevices\n";

    $stmt = $pdo->query("SELECT COUNT(*) FROM " . DB_PREFIX . "cache_device_drilldown");
    $drilldownCount = $stmt->fetchColumn();
    echo "Devices with drill-down in mpsm_cache_device_drilldown: $drilldownCount\n";

    $coverage = $totalDevices > 0 ? round(($drilldownCount / $totalDevices) * 100, 2) : 0;
    echo "Coverage: {$coverage}%\n";

    // 2. Check which devices HAVE drill-down
    echo "\n2. DRILL-DOWN CACHE DETAILS\n";
    echo str_repeat('=', 80) . "\n";

    $stmt = $pdo->query("
        SELECT MIN(cached_at) as first, MAX(cached_at) as last, COUNT(*) as total
        FROM " . DB_PREFIX . "cache_device_drilldown
    ");
    $cacheInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "First cached: {$cacheInfo['first']}\n";
    echo "Last cached: {$cacheInfo['last']}\n";
    echo "Total cached: {$cacheInfo['total']}\n";

    // 3. Check which devices DON'T have drill-down
    echo "\n3. DEVICES WITHOUT DRILL-DOWN\n";
    echo str_repeat('=', 80) . "\n";

    $stmt = $pdo->query("
        SELECT cd.serial_number, cd.customer_code
        FROM " . DB_PREFIX . "cache_devices cd
        LEFT JOIN " . DB_PREFIX . "cache_device_drilldown cdd
            ON cd.serial_number = cdd.serial_number
        WHERE cdd.serial_number IS NULL
        LIMIT 10
    ");

    $missing = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $missing[] = $row;
    }

    echo "Devices without drill-down (first 10): " . count($missing) . "\n";
    foreach ($missing as $device) {
        echo "  - {$device['serial_number']} (Customer: {$device['customer_code']})\n";
    }

    // 4. Query API to determine total available devices
    echo "\n4. API DEVICE COUNT ANALYSIS\n";
    echo str_repeat('=', 80) . "\n";

    echo "Querying Device/List API to determine total available devices...\n";

    $pageNumber = 1;
    $totalApiDevices = 0;
    $apiPages = [];

    while ($pageNumber <= 250) { // Check up to 250 pages
        $params = [
            'PageNumber' => $pageNumber,
            'PageRows' => 50,
            'SortColumn' => 'Id',
            'SortOrder' => 0
        ];

        try {
            $response = callMPSMAPI('Device/List', $params);

            if (!$response || !isset($response['Devices'])) {
                echo "Page $pageNumber: No response or no Devices array\n";
                break;
            }

            $devices = $response['Devices'];
            $deviceCount = count($devices);
            $totalApiDevices += $deviceCount;

            $apiPages[] = [
                'page' => $pageNumber,
                'count' => $deviceCount
            ];

            if ($pageNumber <= 10 || $deviceCount < 50) {
                echo "Page $pageNumber: $deviceCount devices (Total so far: $totalApiDevices)\n";
            }

            if ($deviceCount < 50) {
                echo "Last page detected at page $pageNumber with $deviceCount devices\n";
                break;
            }

            if ($pageNumber % 10 == 0) {
                echo "Page $pageNumber: Still fetching... (Total so far: $totalApiDevices)\n";
            }

            $pageNumber++;
            usleep(100000); // 100ms delay

        } catch (Exception $e) {
            echo "Error on page $pageNumber: " . $e->getMessage() . "\n";
            break;
        }
    }

    echo "\nTOTAL DEVICES IN API: $totalApiDevices\n";
    echo "Pages fetched: " . count($apiPages) . "\n";

    // 5. Compare database vs API
    echo "\n5. DATABASE VS API COMPARISON\n";
    echo str_repeat('=', 80) . "\n";

    $missing_from_db = $totalApiDevices - $totalDevices;
    $missing_drilldown = $totalDevices - $drilldownCount;

    echo "Devices in API: $totalApiDevices\n";
    echo "Devices in DB: $totalDevices\n";
    echo "Missing from DB: $missing_from_db\n\n";

    echo "Devices in DB: $totalDevices\n";
    echo "Drill-downs cached: $drilldownCount\n";
    echo "Missing drill-downs: $missing_drilldown\n\n";

    // 6. Check for hard limits or issues
    echo "\n6. POTENTIAL ISSUES DETECTED\n";
    echo str_repeat('=', 80) . "\n";

    $issues = [];

    if ($drilldownCount == 100) {
        $issues[] = "⚠️  CRITICAL: Drill-down count is exactly 100 - suggests hard limit or early termination";
    }

    if ($missing_from_db > 0) {
        $issues[] = "⚠️  WARNING: $missing_from_db devices from API are not in database cache";
    }

    if ($missing_drilldown > 0) {
        $issues[] = "⚠️  WARNING: $missing_drilldown devices in DB are missing drill-down data";
    }

    if ($totalApiDevices > 200 * 50) {
        $issues[] = "⚠️  INFO: Total devices ($totalApiDevices) exceeds 200-page limit (10,000 devices)";
    }

    if (empty($issues)) {
        echo "✓ No issues detected - system operating normally\n";
    } else {
        foreach ($issues as $issue) {
            echo "$issue\n";
        }
    }

    // 7. Recommended actions
    echo "\n7. RECOMMENDED ACTIONS\n";
    echo str_repeat('=', 80) . "\n";

    if ($drilldownCount < $totalDevices) {
        echo "1. Run refresh-cache-enhanced.php to populate remaining drill-downs\n";
        echo "   URL: /cms/api/refresh-cache-enhanced.php\n\n";
    }

    if ($missing_from_db > 50) {
        echo "2. Device list cache is incomplete - run device list refresh\n";
        echo "   URL: /cms/api/refresh-cache-enhanced.php?skipDrilldown=1\n\n";
    }

    if ($totalApiDevices > $totalDevices + 100) {
        echo "3. Significant devices missing - verify pagination logic in refresh script\n";
    }

    echo "\n8. NEXT STEPS\n";
    echo str_repeat('=', 80) . "\n";
    echo "A. Trigger manual cache refresh to populate all drill-downs\n";
    echo "B. Monitor the refresh process via logs\n";
    echo "C. Verify final counts match API totals\n";

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== ANALYSIS COMPLETE ===\n";
