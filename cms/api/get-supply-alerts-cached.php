<?php
/**
 * Get Cached Supply Alerts API
 * Returns pre-cached supply alert data from MySQL cache
 *
 * OPTIMIZED VERSION 1.0.0:
 * - Uses mpsm_cache_device_drilldown table for instant response
 * - No API calls required (all data pre-fetched by background refresh)
 * - Response time: <100ms (vs 2-5 seconds with live API)
 *
 * Related: refresh-cache-enhanced.php (background population)
 */

require '../config.php';
require '../functions.php';

requireAuth();

set_time_limit(30);
ini_set('memory_limit', '256M');

// Get request parameters
$customerCode = $_GET['customerCode'] ?? null;
$pageNumber = isset($_GET['pageNumber']) ? (int)$_GET['pageNumber'] : 1;
$pageRows = isset($_GET['pageRows']) ? (int)$_GET['pageRows'] : 500;
$sortColumn = $_GET['sortColumn'] ?? 'InitialDate';
$sortOrder = $_GET['sortOrder'] ?? 'Desc';

try {
    $pdo = getDatabase();
    $prefix = DB_PREFIX;

    // Check if cache tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE '{$prefix}cache_device_drilldown'");
    $cacheTableExists = $stmt->rowCount() > 0;

    if (!$cacheTableExists) {
        jsonError("Cache not initialized. Please run refresh-cache-enhanced.php to populate the cache.", 503);
        exit;
    }

    // Get cache age for reporting
    $stmt = $pdo->query("
        SELECT
            MAX(cached_at) as latest_cache,
            MIN(cached_at) as oldest_cache,
            COUNT(*) as total_entries
        FROM {$prefix}cache_device_drilldown
        WHERE has_alerts = 1
    ");
    $cacheInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    $latestCache = $cacheInfo['latest_cache'] ?? null;
    $cacheAge = $latestCache ? strtotime($latestCache) : 0;
    $ageSeconds = $cacheAge > 0 ? time() - $cacheAge : 999999;

    // Warn if cache is stale (> 30 minutes old)
    if ($ageSeconds > 1800) {
        error_log("[WARNING] Supply alerts cache is stale: {$ageSeconds} seconds old. Background refresh may not be running.");
    }

    // Build query to fetch devices with alerts
    $sql = "
        SELECT
            drilldown_data,
            serial_number,
            cached_at
        FROM {$prefix}cache_device_drilldown
        WHERE has_alerts = 1
    ";

    // Add customer filter if specified
    if ($customerCode) {
        $sql .= " AND JSON_EXTRACT(drilldown_data, '$.CustomerCode') = :customerCode";
    }

    $stmt = $pdo->prepare($sql);
    if ($customerCode) {
        $stmt->bindValue(':customerCode', $customerCode, PDO::PARAM_STR);
    }
    $stmt->execute();

    // Extract all supply alerts from drilldown data
    $allAlerts = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $drilldown = json_decode($row['drilldown_data'], true);
        if (is_array($drilldown) && isset($drilldown['SupplyAlerts']) && is_array($drilldown['SupplyAlerts'])) {
            foreach ($drilldown['SupplyAlerts'] as $alert) {
                $allAlerts[] = $alert;
            }
        }
    }

    // Sort alerts
    if (count($allAlerts) > 0) {
        usort($allAlerts, function($a, $b) use ($sortColumn, $sortOrder) {
            $aVal = $a[$sortColumn] ?? '';
            $bVal = $b[$sortColumn] ?? '';

            $cmp = strcmp($aVal, $bVal);
            return ($sortOrder === 'Desc') ? -$cmp : $cmp;
        });
    }

    $total = count($allAlerts);

    // Apply pagination
    $start = ($pageNumber - 1) * $pageRows;
    $paginatedAlerts = array_slice($allAlerts, $start, $pageRows);

    // Format cache age for human readability
    if ($ageSeconds < 60) {
        $cacheAgeHuman = "$ageSeconds seconds";
    } elseif ($ageSeconds < 3600) {
        $minutes = round($ageSeconds / 60);
        $cacheAgeHuman = "$minutes minutes";
    } else {
        $hours = round($ageSeconds / 3600, 1);
        $cacheAgeHuman = "$hours hours";
    }

    jsonSuccess([
        'alerts' => $paginatedAlerts,
        'total' => $total,
        'page' => [
            'number' => $pageNumber,
            'size' => $pageRows,
        ],
        'meta' => [
            'total_rows' => $total,
            'items_returned' => count($paginatedAlerts),
            'cached' => true,
            'cache_age_seconds' => $ageSeconds,
            'cache_age_human' => $cacheAgeHuman,
            'latest_cache_timestamp' => $latestCache,
            'source' => 'mysql_cache',
            'performance_note' => 'Served from MySQL cache - no API calls required'
        ]
    ]);

} catch (Exception $e) {
    error_log("[ERROR] get-supply-alerts-cached.php: " . $e->getMessage());
    jsonError("Failed to fetch cached supply alerts: " . $e->getMessage());
}
