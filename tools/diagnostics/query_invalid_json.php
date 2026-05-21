<?php
/**
 * Query Invalid JSON Payload Errors
 * Temporary diagnostic script
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

try {
    $db = config('database');
    $pdo = new PDO(
        sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $db['host'],
            $db['database'],
            $db['charset']
        ),
        $db['username'],
        $db['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Count total invalid JSON errors
    $stmt = $pdo->query("
        SELECT COUNT(*) as total
        FROM mpsm_panel_callback_debug
        WHERE status = 'ERROR' AND message = 'Invalid JSON payload'
    ");
    $total = $stmt->fetchColumn();

    echo "=== INVALID JSON PAYLOAD ANALYSIS ===\n\n";
    echo "Total Invalid JSON Payload Errors: $total\n\n";

    // Get sample of invalid payloads
    $stmt = $pdo->query("
        SELECT id, created_at, raw_body, source_ip
        FROM mpsm_panel_callback_debug
        WHERE status = 'ERROR' AND message = 'Invalid JSON payload'
        ORDER BY created_at DESC
        LIMIT 20
    ");

    echo "Sample Invalid Payloads (Most Recent 20):\n";
    echo str_repeat('=', 100) . "\n\n";

    $count = 0;
    while ($row = $stmt->fetch()) {
        $count++;
        echo "#{$count} - ID: {$row['id']} | Time: {$row['created_at']} | IP: {$row['source_ip']}\n";
        echo "Raw Body:\n";

        // Check if it's actually valid JSON that we're rejecting
        $decoded = json_decode($row['raw_body'], true);
        if ($decoded !== null) {
            echo "  ⚠️  WARNING: This IS valid JSON but was rejected!\n";
            echo "  Decoded: " . print_r($decoded, true) . "\n";
        } else {
            echo "  Body content: " . substr($row['raw_body'], 0, 500) .
                 (strlen($row['raw_body']) > 500 ? '...' : '') . "\n";
            echo "  JSON Error: " . json_last_error_msg() . "\n";
        }

        echo str_repeat('-', 100) . "\n\n";
    }

    // Also check for patterns in the raw bodies
    $stmt = $pdo->query("
        SELECT DISTINCT SUBSTRING(raw_body, 1, 100) as body_prefix, COUNT(*) as count
        FROM mpsm_panel_callback_debug
        WHERE status = 'ERROR' AND message = 'Invalid JSON payload'
        GROUP BY body_prefix
        ORDER BY count DESC
        LIMIT 10
    ");

    echo "\nMost Common Invalid Payload Patterns:\n";
    echo str_repeat('=', 100) . "\n";
    while ($row = $stmt->fetch()) {
        echo "Count: {$row['count']} | Prefix: {$row['body_prefix']}\n";
        echo str_repeat('-', 100) . "\n";
    }

    // Count devices with drill-down cache
    echo "\n\n=== DRILL-DOWN CACHE ANALYSIS ===\n\n";

    $stmt = $pdo->query("SELECT COUNT(*) FROM mpsm_cache_device_drilldown");
    $drilldownCount = $stmt->fetchColumn();
    echo "Total Devices with Drill-Down Cache: $drilldownCount\n";

    $stmt = $pdo->query("SELECT COUNT(*) FROM mpsm_cache_devices");
    $devicesCount = $stmt->fetchColumn();
    echo "Total Devices in Cache: $devicesCount\n";

    $coverage = $devicesCount > 0 ? round(($drilldownCount / $devicesCount) * 100, 2) : 0;
    echo "Drill-Down Coverage: {$coverage}%\n\n";

    // Show recent drill-down cache entries
    $stmt = $pdo->query("
        SELECT serial_number, cached_at, has_alerts, has_supplies
        FROM mpsm_cache_device_drilldown
        ORDER BY cached_at DESC
        LIMIT 10
    ");

    echo "Recent Drill-Down Cache Entries (Last 10):\n";
    echo str_repeat('=', 100) . "\n";
    while ($row = $stmt->fetch()) {
        echo "Serial: {$row['serial_number']} | Cached: {$row['cached_at']} | ";
        echo "Alerts: " . ($row['has_alerts'] ? 'Yes' : 'No') . " | ";
        echo "Supplies: " . ($row['has_supplies'] ? 'Yes' : 'No') . "\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
