<?php
/**
 * Analyze Invalid JSON Errors
 * Direct database query for diagnostic purposes
 */

require_once __DIR__ . '/bootstrap.php';

try {
    $pdo = getDatabase();
    $table = DB_PREFIX . 'panel_callback_debug';

    echo "\n=== INVALID JSON PAYLOAD ANALYSIS ===\n\n";

    // Count total invalid JSON errors
    $stmt = $pdo->query("
        SELECT COUNT(*) as total
        FROM {$table}
        WHERE status = 'ERROR' AND message = 'Invalid JSON payload'
    ");
    $errorCount = $stmt->fetchColumn();

    echo "Total Invalid JSON Payload Errors: {$errorCount}\n\n";

    // Get ALL invalid payloads for analysis
    $stmt = $pdo->query("
        SELECT id, timestamp, raw_body, unique_source, ip_address
        FROM {$table}
        WHERE status = 'ERROR' AND message = 'Invalid JSON payload'
        ORDER BY timestamp DESC
        LIMIT 50
    ");

    echo "Sample Invalid Payloads (Most Recent 50):\n";
    echo str_repeat('=', 120) . "\n\n";

    $validJsonCount = 0;
    $actuallyInvalidCount = 0;
    $emptyCount = 0;
    $patterns = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rawBody = $row['raw_body'];

        // Check if it's actually valid JSON
        $decoded = json_decode($rawBody, true);
        $jsonError = json_last_error();

        if ($decoded !== null && $jsonError === JSON_ERROR_NONE) {
            $validJsonCount++;
            echo "ID {$row['id']} - Time: {$row['timestamp']}\n";
            echo "⚠️  WARNING: This IS VALID JSON but was rejected!\n";
            echo "Source: {$row['unique_source']} | IP: {$row['ip_address']}\n";
            echo "Decoded successfully. Sample data:\n";
            echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
            echo str_repeat('-', 120) . "\n\n";
        } elseif (trim($rawBody) === '' || $rawBody === null) {
            $emptyCount++;
        } else {
            $actuallyInvalidCount++;

            // Track patterns
            $first50 = substr($rawBody, 0, 50);
            if (!isset($patterns[$first50])) {
                $patterns[$first50] = 0;
            }
            $patterns[$first50]++;

            echo "ID {$row['id']} - Time: {$row['timestamp']}\n";
            echo "✗ Actually Invalid JSON\n";
            echo "Error: " . json_last_error_msg() . "\n";
            echo "Source: {$row['unique_source']} | IP: {$row['ip_address']}\n";
            echo "Body (first 200 chars): " . substr($rawBody, 0, 200) . "...\n";
            echo str_repeat('-', 120) . "\n\n";
        }
    }

    echo "\n" . str_repeat('=', 120) . "\n";
    echo "SUMMARY:\n";
    echo "  • Valid JSON mistakenly rejected: {$validJsonCount}\n";
    echo "  • Actually invalid JSON: {$actuallyInvalidCount}\n";
    echo "  • Empty/null bodies: {$emptyCount}\n";
    echo "  • Total analyzed: " . ($validJsonCount + $actuallyInvalidCount + $emptyCount) . "\n";
    echo str_repeat('=', 120) . "\n\n";

    if (!empty($patterns)) {
        echo "Common Invalid Patterns:\n";
        arsort($patterns);
        foreach (array_slice($patterns, 0, 5) as $pattern => $count) {
            echo "  [{$count}x] {$pattern}...\n";
        }
        echo "\n";
    }

    // Now count drill-down cache
    echo "\n=== DRILL-DOWN CACHE ANALYSIS ===\n\n";

    $drilldownTable = DB_PREFIX . 'cache_device_drilldown';
    $devicesTable = DB_PREFIX . 'cache_devices';

    $stmt = $pdo->query("SELECT COUNT(*) FROM {$drilldownTable}");
    $drilldownCount = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM {$devicesTable}");
    $devicesCount = $stmt->fetchColumn();

    echo "Total Devices in Cache: {$devicesCount}\n";
    echo "Total Devices with Drill-Down Cache: {$drilldownCount}\n";

    $coverage = $devicesCount > 0 ? round(($drilldownCount / $devicesCount) * 100, 2) : 0;
    echo "Drill-Down Coverage: {$coverage}%\n\n";

    // Show recent drill-down cache entries
    $stmt = $pdo->query("
        SELECT serial_number, cached_at, has_alerts, has_supplies
        FROM {$drilldownTable}
        ORDER BY cached_at DESC
        LIMIT 15
    ");

    echo "Recent Drill-Down Cache Entries (Last 15):\n";
    echo str_repeat('=', 100) . "\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Serial: " . str_pad($row['serial_number'], 25) . " | ";
        echo "Cached: {$row['cached_at']} | ";
        echo "Alerts: " . ($row['has_alerts'] ? 'Yes' : 'No ') . " | ";
        echo "Supplies: " . ($row['has_supplies'] ? 'Yes' : 'No ') . "\n";
    }

    echo "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
