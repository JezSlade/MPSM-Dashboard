<?php
/**
 * System Diagnostics API
 * Provides comprehensive analysis of:
 * - Panel callback errors
 * - Drill-down cache statistics
 * - System health metrics
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

define('MPS_ENGINE_ACCESS', true);
require_once dirname(__DIR__, 2) . '/mps-api/callbacks/panel-message-common.php';

requireAuth();

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDatabase();
    $debugTable = DB_PREFIX . 'panel_callback_debug';
    $messagesTable = DB_PREFIX . 'panel_messages';
    $drilldownTable = DB_PREFIX . 'cache_device_drilldown';
    $devicesTable = DB_PREFIX . 'cache_devices';

    ensurePanelCallbackDebugTable($pdo);

    $diagnostics = [];

    // ===================================================================
    // PANEL CALLBACK ERROR ANALYSIS
    // ===================================================================

    $diagnostics['panel_callbacks'] = [];

    // Total callback statistics
    $stmt = $pdo->query("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success_count,
            SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) as error_count,
            MAX(timestamp) as last_callback
        FROM {$debugTable}
    ");
    $callbackStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $diagnostics['panel_callbacks']['total'] = (int)$callbackStats['total'];
    $diagnostics['panel_callbacks']['success'] = (int)$callbackStats['success_count'];
    $diagnostics['panel_callbacks']['errors'] = (int)$callbackStats['error_count'];
    $diagnostics['panel_callbacks']['last_callback'] = $callbackStats['last_callback'];

    // Breakdown of error types
    $stmt = $pdo->query("
        SELECT
            message,
            COUNT(*) as count,
            MAX(timestamp) as last_occurrence
        FROM {$debugTable}
        WHERE status = 'ERROR'
        GROUP BY message
        ORDER BY count DESC
    ");
    $errorBreakdown = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $errorBreakdown[] = [
            'error_type' => $row['message'],
            'count' => (int)$row['count'],
            'last_occurrence' => $row['last_occurrence']
        ];
    }
    $diagnostics['panel_callbacks']['error_breakdown'] = $errorBreakdown;

    // Analyze "Invalid JSON payload" errors specifically
    $stmt = $pdo->query("
        SELECT id, timestamp, raw_body
        FROM {$debugTable}
        WHERE status = 'ERROR' AND message LIKE 'Invalid JSON%'
        ORDER BY timestamp DESC
        LIMIT 100
    ");

    $invalidJsonAnalysis = [
        'total' => 0,
        'actually_invalid' => 0,
        'valid_but_not_array' => 0,
        'valid_null' => 0,
        'empty' => 0,
        'samples' => []
    ];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rawBody = trim($row['raw_body'] ?? '');
        $invalidJsonAnalysis['total']++;

        if ($rawBody === '') {
            $invalidJsonAnalysis['empty']++;
            continue;
        }

        $decoded = json_decode($rawBody, true);
        $jsonError = json_last_error();

        if ($jsonError === JSON_ERROR_NONE) {
            if ($decoded === null) {
                $invalidJsonAnalysis['valid_null']++;
                if (count($invalidJsonAnalysis['samples']) < 5) {
                    $invalidJsonAnalysis['samples'][] = [
                        'id' => (int)$row['id'],
                        'timestamp' => $row['timestamp'],
                        'type' => 'valid_null',
                        'body' => $rawBody
                    ];
                }
            } elseif (!is_array($decoded)) {
                $invalidJsonAnalysis['valid_but_not_array']++;
                if (count($invalidJsonAnalysis['samples']) < 5) {
                    $invalidJsonAnalysis['samples'][] = [
                        'id' => (int)$row['id'],
                        'timestamp' => $row['timestamp'],
                        'type' => 'valid_' . gettype($decoded),
                        'body' => $rawBody
                    ];
                }
            }
        } else {
            $invalidJsonAnalysis['actually_invalid']++;
            if (count($invalidJsonAnalysis['samples']) < 5) {
                $invalidJsonAnalysis['samples'][] = [
                    'id' => (int)$row['id'],
                    'timestamp' => $row['timestamp'],
                    'type' => 'invalid_json',
                    'error' => json_last_error_msg(),
                    'body' => substr($rawBody, 0, 200)
                ];
            }
        }
    }

    $diagnostics['panel_callbacks']['invalid_json_analysis'] = $invalidJsonAnalysis;

    // ===================================================================
    // PANEL MESSAGES STATISTICS
    // ===================================================================

    $stmt = $pdo->query("
        SELECT
            COUNT(*) as total,
            MIN(ny_received_at) as first_message,
            MAX(ny_received_at) as last_message,
            COUNT(DISTINCT device_serial) as unique_devices,
            COUNT(DISTINCT customer_code) as unique_customers
        FROM {$messagesTable}
    ");
    $messageStats = $stmt->fetch(PDO::FETCH_ASSOC);

    $diagnostics['panel_messages'] = [
        'total_messages' => (int)$messageStats['total'],
        'unique_devices' => (int)$messageStats['unique_devices'],
        'unique_customers' => (int)$messageStats['unique_customers'],
        'first_message' => $messageStats['first_message'],
        'last_message' => $messageStats['last_message']
    ];

    // ===================================================================
    // DRILL-DOWN CACHE STATISTICS
    // ===================================================================

    // Total devices in main cache
    $stmt = $pdo->query("SELECT COUNT(*) FROM {$devicesTable}");
    $totalDevices = (int)$stmt->fetchColumn();

    // Total devices with drill-down cache
    $stmt = $pdo->query("SELECT COUNT(*) FROM {$drilldownTable}");
    $totalDrilldown = (int)$stmt->fetchColumn();

    // Drill-down cache details
    $stmt = $pdo->query("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN has_alerts = 1 THEN 1 ELSE 0 END) as with_alerts,
            SUM(CASE WHEN has_supplies = 1 THEN 1 ELSE 0 END) as with_supplies,
            MIN(cached_at) as oldest_entry,
            MAX(cached_at) as newest_entry
        FROM {$drilldownTable}
    ");
    $drilldownStats = $stmt->fetch(PDO::FETCH_ASSOC);

    $coverage = $totalDevices > 0 ? round(($totalDrilldown / $totalDevices) * 100, 2) : 0;

    $diagnostics['cache'] = [
        'total_devices' => $totalDevices,
        'devices_with_drilldown' => $totalDrilldown,
        'coverage_percent' => $coverage,
        'devices_with_alerts' => (int)$drilldownStats['with_alerts'],
        'devices_with_supplies' => (int)$drilldownStats['with_supplies'],
        'oldest_cache_entry' => $drilldownStats['oldest_entry'],
        'newest_cache_entry' => $drilldownStats['newest_entry']
    ];

    // Get cache age distribution
    $stmt = $pdo->query("
        SELECT
            SUM(CASE WHEN cached_at > NOW() - INTERVAL 1 HOUR THEN 1 ELSE 0 END) as last_hour,
            SUM(CASE WHEN cached_at > NOW() - INTERVAL 24 HOUR THEN 1 ELSE 0 END) as last_24h,
            SUM(CASE WHEN cached_at > NOW() - INTERVAL 7 DAY THEN 1 ELSE 0 END) as last_7d
        FROM {$drilldownTable}
    ");
    $ageStats = $stmt->fetch(PDO::FETCH_ASSOC);

    $diagnostics['cache']['freshness'] = [
        'cached_within_hour' => (int)$ageStats['last_hour'],
        'cached_within_24h' => (int)$ageStats['last_24h'],
        'cached_within_7d' => (int)$ageStats['last_7d']
    ];

    // Recent cache entries sample
    $stmt = $pdo->query("
        SELECT serial_number, cached_at, has_alerts, has_supplies
        FROM {$drilldownTable}
        ORDER BY cached_at DESC
        LIMIT 10
    ");
    $recentEntries = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $recentEntries[] = [
            'serial' => $row['serial_number'],
            'cached_at' => $row['cached_at'],
            'has_alerts' => (bool)$row['has_alerts'],
            'has_supplies' => (bool)$row['has_supplies']
        ];
    }
    $diagnostics['cache']['recent_entries'] = $recentEntries;

    // ===================================================================
    // SYSTEM HEALTH SUMMARY
    // ===================================================================

    $healthIssues = [];
    $healthStatus = 'EXCELLENT';

    // Check for excessive errors
    if ($callbackStats['error_count'] > 0 && $callbackStats['total'] > 0) {
        $errorRate = ($callbackStats['error_count'] / $callbackStats['total']) * 100;
        if ($errorRate > 50) {
            $healthIssues[] = "High callback error rate: {$errorRate}%";
            $healthStatus = 'CRITICAL';
        } elseif ($errorRate > 20) {
            $healthIssues[] = "Moderate callback error rate: {$errorRate}%";
            $healthStatus = 'WARNING';
        }
    }

    // Check cache coverage
    if ($coverage < 50) {
        $healthIssues[] = "Low drill-down cache coverage: {$coverage}%";
        if ($healthStatus === 'EXCELLENT') $healthStatus = 'WARNING';
    }

    // Check cache freshness
    if ($totalDrilldown > 0 && $ageStats['last_24h'] < $totalDrilldown * 0.5) {
        $healthIssues[] = "Stale cache: Less than 50% cached in last 24h";
        if ($healthStatus === 'EXCELLENT') $healthStatus = 'WARNING';
    }

    $diagnostics['health'] = [
        'status' => $healthStatus,
        'issues' => $healthIssues,
        'recommendations' => []
    ];

    // Add recommendations
    if ($invalidJsonAnalysis['actually_invalid'] > 0) {
        $diagnostics['health']['recommendations'][] = "Review {$invalidJsonAnalysis['actually_invalid']} invalid JSON payloads for patterns";
    }
    if ($coverage < 80) {
        $diagnostics['health']['recommendations'][] = "Run cache refresh to improve drill-down coverage (currently {$coverage}%)";
    }
    if (empty($healthIssues)) {
        $diagnostics['health']['recommendations'][] = "System operating normally - no action required";
    }

    // ===================================================================
    // RESPONSE
    // ===================================================================

    jsonSuccess($diagnostics);

} catch (Exception $e) {
    jsonError('Failed to generate diagnostics: ' . $e->getMessage(), 500);
}
