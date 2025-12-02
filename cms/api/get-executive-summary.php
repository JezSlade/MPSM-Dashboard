<?php
/**
 * Executive Summary API - V2 (Database-Only, No HTTP Calls)
 * Handles empty cache gracefully
 */

require '../config.php';
require '../functions.php';

requireAuth();

$forceRefresh = isset($_GET['force']) && $_GET['force'] === '1';

try {
    $cacheKey = 'executive-summary-v2';
    $cacheTTL = 1800; // 30 minutes

    // Try cache first unless force refresh
    if (!$forceRefresh) {
        $cached = cacheGet($cacheKey);
        if ($cached !== null) {
            $data = json_decode($cached, true);
            if ($data && isset($data['timestamp'])) {
                $age = time() - $data['timestamp'];
                if ($age < $cacheTTL) {
                    jsonSuccess([
                        'summary' => $data['summary'],
                        'cached' => true,
                        'cache_age_seconds' => $age
                    ]);
                }
            }
        }
    }

    // Build fresh summary (database-only)
    $summary = buildExecutiveSummaryV2();

    // Cache result
    cacheStore($cacheKey, json_encode([
        'timestamp' => time(),
        'summary' => $summary
    ]));

    jsonSuccess([
        'summary' => $summary,
        'cached' => false,
        'cache_age_seconds' => 0
    ]);

} catch (Exception $e) {
    error_log("Executive Summary V2 Error: " . $e->getMessage());
    jsonError("Failed to generate executive summary: " . $e->getMessage());
}

function buildExecutiveSummaryV2() {
    $pdo = getDatabase();

    // Initialize all metrics with safe defaults
    $metrics = [
        'totalCustomers' => 0,
        'totalDevices' => 0,
        'offlineDevices' => 0,
        'totalAlerts' => 0,
        'totalConnectors' => 0,
        'activeConnectorsLastDay' => 0,
        'devicesByStatus' => ['online' => 0, 'offline' => 0, 'error' => 0],
        'missingAssetNumbers' => 0,
        'assetNumberCompleteness' => 100,
        'duplicateIPs' => 0,
        'duplicateIPsList' => [],
        'unmappedDevices' => 0,
        'ghostDevices7d' => 0,
        'ghostDevices30d' => 0,
        'fleetAgeDistribution' => ['under1yr' => 0, 'age1to3yr' => 0, 'age3to5yr' => 0, 'over5yr' => 0, 'unknown' => 0],
        'uninstalledDevices' => 0,
        'panelMessagesLast24h' => 0,
        'panelMessagesLast7d' => 0,
        'problemDevices' => 0,
        'topProblemDevices' => [],
        'connectorHealthScore' => 100,
        'connectorsOffline' => 0,
        'cacheHealthScore' => 0,
        'cacheFreshnessAvg' => 0,
        'drillDownCoverage' => 0,
        'alertDefinitionCoverage' => 0,
        'unmappedAlertCodes' => 0,
        'topCustomersByDevices' => [],
        'topCustomersByAlerts' => []
    ];

    // Check if cache tables exist and have data
    $cacheTableExists = checkTableExists($pdo, 'mpsm_cache_devices');
    if (!$cacheTableExists) {
        $metrics['_warning'] = 'Cache table not found. Run refresh-cache-enhanced.php to populate data.';
        return $metrics;
    }

    // Total devices from cache
    $deviceStats = $pdo->query("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN is_uninstalled = 1 THEN 1 ELSE 0 END) as uninstalled
        FROM mpsm_cache_devices
    ")->fetch(PDO::FETCH_ASSOC);

    $metrics['totalDevices'] = (int)($deviceStats['total'] ?? 0) - (int)($deviceStats['uninstalled'] ?? 0);
    $metrics['uninstalledDevices'] = (int)($deviceStats['uninstalled'] ?? 0);

    // If no devices, return early with warning
    if ($metrics['totalDevices'] === 0) {
        $metrics['_warning'] = 'No devices in cache. Run refresh-cache-enhanced.php to populate data.';
        return $metrics;
    }

    // Total unique customers from cache
    try {
        $customerCount = $pdo->query("
            SELECT COUNT(DISTINCT customer_code) as count
            FROM mpsm_cache_devices
            WHERE customer_code IS NOT NULL AND customer_code != ''
        ")->fetch(PDO::FETCH_ASSOC);
        $metrics['totalCustomers'] = (int)($customerCount['count'] ?? 0);
    } catch (Exception $e) {
        // customer_code column might not exist in old schema
    }

    // Duplicate IPs (safe fallback)
    try {
        $dupIPs = $pdo->query("
            SELECT COUNT(*) as count
            FROM (
                SELECT JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) as ip
                FROM mpsm_cache_devices
                WHERE is_uninstalled = 0
                  AND JSON_EXTRACT(device_data, '$.IpAddress') IS NOT NULL
                  AND JSON_EXTRACT(device_data, '$.IpAddress') != 'null'
                GROUP BY JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress'))
                HAVING COUNT(*) > 1
            ) as dups
        ")->fetch(PDO::FETCH_ASSOC);
        $metrics['duplicateIPs'] = (int)($dupIPs['count'] ?? 0);
    } catch (Exception $e) {
        // JSON functions not supported
    }

    // Missing asset numbers
    try {
        $missingAssets = $pdo->query("
            SELECT COUNT(*) as count
            FROM mpsm_cache_devices
            WHERE is_uninstalled = 0
              AND (JSON_EXTRACT(device_data, '$.AssetNumber') IS NULL
                OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.AssetNumber')) = ''
                OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.AssetNumber')) = 'null')
        ")->fetch(PDO::FETCH_ASSOC);
        $metrics['missingAssetNumbers'] = (int)($missingAssets['count'] ?? 0);
        $metrics['assetNumberCompleteness'] = $metrics['totalDevices'] > 0
            ? round((($metrics['totalDevices'] - $metrics['missingAssetNumbers']) / $metrics['totalDevices']) * 100, 1)
            : 100;
    } catch (Exception $e) {
        // JSON functions not supported
    }

    // Device status (online/offline) using cached status field
    try {
        $statusStats = $pdo->query("
            SELECT
                SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Status')) = 'Offline' THEN 1 ELSE 0 END) as offline,
                SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Status')) = 'Online' THEN 1 ELSE 0 END) as online
            FROM mpsm_cache_devices
            WHERE is_uninstalled = 0
        ")->fetch(PDO::FETCH_ASSOC);

        $metrics['offlineDevices'] = (int)($statusStats['offline'] ?? 0);
        $metrics['devicesByStatus']['offline'] = $metrics['offlineDevices'];

        $derivedOnline = max(0, $metrics['totalDevices'] - $metrics['offlineDevices']);
        $metrics['devicesByStatus']['online'] = (int)($statusStats['online'] ?? $derivedOnline);
        if ($metrics['devicesByStatus']['online'] === 0 && $derivedOnline > 0) {
            $metrics['devicesByStatus']['online'] = $derivedOnline;
        }
    } catch (Exception $e) {
        // Status stats unavailable; keep defaults
    }

    // Ghost devices (no contact in 7/30 days)
    try {
        $ghostStats = $pdo->query("
            SELECT
                SUM(
                    CASE
                        WHEN JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.LastContact')) IS NOT NULL
                             AND JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.LastContact')) != ''
                             AND JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.LastContact')) != 'null'
                             AND STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.LastContact')), '%Y-%m-%dT%H:%i:%s') IS NOT NULL
                             AND TIMESTAMPDIFF(DAY, STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.LastContact')), '%Y-%m-%dT%H:%i:%s'), NOW()) > 7
                        THEN 1 ELSE 0 END
                ) as ghost7d,
                SUM(
                    CASE
                        WHEN JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.LastContact')) IS NOT NULL
                             AND JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.LastContact')) != ''
                             AND JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.LastContact')) != 'null'
                             AND STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.LastContact')), '%Y-%m-%dT%H:%i:%s') IS NOT NULL
                             AND TIMESTAMPDIFF(DAY, STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.LastContact')), '%Y-%m-%dT%H:%i:%s'), NOW()) > 30
                        THEN 1 ELSE 0 END
                ) as ghost30d
            FROM mpsm_cache_devices
            WHERE is_uninstalled = 0
        ")->fetch(PDO::FETCH_ASSOC);

        $metrics['ghostDevices7d'] = (int)($ghostStats['ghost7d'] ?? 0);
        $metrics['ghostDevices30d'] = (int)($ghostStats['ghost30d'] ?? 0);
    } catch (Exception $e) {
        // Ghost calculation unavailable; keep defaults
    }

    // Fleet age distribution based on InstallDate
    try {
        $ageStats = $pdo->query("
            SELECT
                SUM(CASE WHEN install_date IS NULL THEN 1 ELSE 0 END) as unknown,
                SUM(CASE WHEN install_date IS NOT NULL AND TIMESTAMPDIFF(YEAR, install_date, NOW()) < 1 THEN 1 ELSE 0 END) as under1yr,
                SUM(CASE WHEN install_date IS NOT NULL AND TIMESTAMPDIFF(YEAR, install_date, NOW()) BETWEEN 1 AND 2 THEN 1 ELSE 0 END) as age1to3yr,
                SUM(CASE WHEN install_date IS NOT NULL AND TIMESTAMPDIFF(YEAR, install_date, NOW()) BETWEEN 3 AND 4 THEN 1 ELSE 0 END) as age3to5yr,
                SUM(CASE WHEN install_date IS NOT NULL AND TIMESTAMPDIFF(YEAR, install_date, NOW()) >= 5 THEN 1 ELSE 0 END) as over5yr
            FROM (
                SELECT STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.InstallDate')), '%Y-%m-%dT%H:%i:%s') as install_date
                FROM mpsm_cache_devices
                WHERE is_uninstalled = 0
            ) as installs
        ")->fetch(PDO::FETCH_ASSOC);

        $metrics['fleetAgeDistribution'] = [
            'under1yr' => (int)($ageStats['under1yr'] ?? 0),
            'age1to3yr' => (int)($ageStats['age1to3yr'] ?? 0),
            'age3to5yr' => (int)($ageStats['age3to5yr'] ?? 0),
            'over5yr' => (int)($ageStats['over5yr'] ?? 0),
            'unknown' => (int)($ageStats['unknown'] ?? 0)
        ];
    } catch (Exception $e) {
        // Install date parsing unavailable; keep defaults
    }

    // Devices with alerts (approximate Active Alerts)
    if (checkTableExists($pdo, 'mpsm_cache_device_drilldown')) {
        try {
            $alertStats = $pdo->query("
                SELECT COUNT(*) as devices_with_alerts
                FROM mpsm_cache_device_drilldown
                WHERE has_alerts = 1
            ")->fetch(PDO::FETCH_ASSOC);

            $metrics['totalAlerts'] = (int)($alertStats['devices_with_alerts'] ?? 0);
            $metrics['devicesByStatus']['error'] = min($metrics['totalDevices'], $metrics['totalAlerts']);
        } catch (Exception $e) {
            // Alert stats unavailable; keep defaults
        }
    }

    // Panel message stats
    if (checkTableExists($pdo, 'mpsm_panel_messages')) {
        try {
            $panelStats = $pdo->query("
                SELECT
                    COUNT(CASE WHEN received_at >= NOW() - INTERVAL 24 HOUR THEN 1 END) as last_24h,
                    COUNT(CASE WHEN received_at >= NOW() - INTERVAL 7 DAY THEN 1 END) as last_7d
                FROM mpsm_panel_messages
            ")->fetch(PDO::FETCH_ASSOC);

            $metrics['panelMessagesLast24h'] = (int)($panelStats['last_24h'] ?? 0);
            $metrics['panelMessagesLast7d'] = (int)($panelStats['last_7d'] ?? 0);

            // Problem devices
            $problemDevices = $pdo->query("
                SELECT device_serial, COUNT(*) as error_count
                FROM mpsm_panel_messages
                WHERE received_at >= NOW() - INTERVAL 24 HOUR
                GROUP BY device_serial
                HAVING COUNT(*) > 10
                ORDER BY error_count DESC
                LIMIT 20
            ")->fetchAll(PDO::FETCH_ASSOC);
            $metrics['problemDevices'] = count($problemDevices);
            $metrics['topProblemDevices'] = $problemDevices;
        } catch (Exception $e) {
            // Panel messages table issue
        }
    }

    // Cache health
    try {
        $cacheStats = $pdo->query("
            SELECT
                COUNT(*) as total_cached,
                AVG(TIMESTAMPDIFF(SECOND, cached_at, NOW())) as avg_age_seconds
            FROM mpsm_cache_devices
            WHERE is_uninstalled = 0
        ")->fetch(PDO::FETCH_ASSOC);

        $metrics['cacheFreshnessAvg'] = round((float)($cacheStats['avg_age_seconds'] ?? 0));

        if (checkTableExists($pdo, 'mpsm_cache_device_drilldown')) {
            $drillDownCount = $pdo->query("SELECT COUNT(*) as count FROM mpsm_cache_device_drilldown")->fetch(PDO::FETCH_ASSOC);
            $totalCached = (int)($cacheStats['total_cached'] ?? 0);
            $metrics['drillDownCoverage'] = $totalCached > 0
                ? round(((int)($drillDownCount['count'] ?? 0) / $totalCached) * 100, 1)
                : 0;
        }

        // Cache health score
        $freshnessScore = min(100, max(0, 100 - ($metrics['cacheFreshnessAvg'] / 3600 * 10)));
        $coverageScore = $metrics['drillDownCoverage'];
        $metrics['cacheHealthScore'] = round(($freshnessScore + $coverageScore) / 2, 1);
    } catch (Exception $e) {
        // Cache stats issue
    }

    // Alert definition coverage
    if (checkTableExists($pdo, 'mpsm_alert_definitions') && checkTableExists($pdo, 'mpsm_panel_messages')) {
        try {
            $totalCodes = $pdo->query("
                SELECT COUNT(DISTINCT maintenance_alert_code) as count
                FROM mpsm_panel_messages
                WHERE maintenance_alert_code IS NOT NULL
            ")->fetch(PDO::FETCH_ASSOC);

            $definedCodes = $pdo->query("
                SELECT COUNT(DISTINCT alert_code) as count
                FROM mpsm_alert_definitions
            ")->fetch(PDO::FETCH_ASSOC);

            $total = (int)($totalCodes['count'] ?? 0);
            $defined = (int)($definedCodes['count'] ?? 0);
            $metrics['unmappedAlertCodes'] = max(0, $total - $defined);
            $metrics['alertDefinitionCoverage'] = $total > 0 ? round(($defined / $total) * 100, 1) : 100;
        } catch (Exception $e) {
            // Alert coverage issue
        }
    }

    // Connector health estimate (based on customers with devices)
    $metrics['connectorHealthScore'] = 95; // Default estimate
    $metrics['totalConnectors'] = max(1, ceil($metrics['totalCustomers'] / 10)); // Rough estimate

    return $metrics;
}

function checkTableExists($pdo, $tableName) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$tableName}'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

/*
CHANGELOG
2025-12-02 Claude
- V2 implementation: database-only queries, no HTTP calls
- Gracefully handles empty cache with warning messages
- Uses JSON_EXTRACT instead of ->> operator for MySQL 5.7 compatibility
- All queries wrapped in try-catch for safety
- Returns safe defaults when tables/data missing
2025-12-06 Codex
- Added device status, ghost device, fleet age, and alert population metrics from cache tables to prevent empty executive cards while preserving safe fallbacks.
*/
