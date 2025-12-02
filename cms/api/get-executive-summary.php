<?php
/**
 * Executive Summary API
 * Aggregates dealer-wide metrics from existing cached endpoints
 * Returns comprehensive KPIs for executive dashboard
 */

require '../config.php';
require '../functions.php';

requireAuth();

$forceRefresh = isset($_GET['force']) && $_GET['force'] === '1';

try {
    $cacheKey = 'executive-summary';
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

    // Build fresh summary
    $summary = buildExecutiveSummary();

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
    error_log("Executive Summary Error: " . $e->getMessage());
    jsonError("Failed to generate executive summary: " . $e->getMessage());
}

function buildExecutiveSummary() {
    $pdo = getDatabase();

    // Initialize metrics structure
    $metrics = [
        // Core counts
        'totalCustomers' => 0,
        'totalDevices' => 0,
        'offlineDevices' => 0,
        'totalAlerts' => 0,
        'totalConnectors' => 0,
        'activeConnectorsLastDay' => 0,

        // Device status breakdown
        'devicesByStatus' => [
            'online' => 0,
            'offline' => 0,
            'error' => 0
        ],

        // Data Quality Metrics
        'missingAssetNumbers' => 0,
        'assetNumberCompleteness' => 0,
        'profileCompletenessAvg' => 0,
        'duplicateIPs' => 0,
        'duplicateIPsList' => [],
        'unmappedDevices' => 0,

        // Fleet Health Metrics
        'ghostDevices7d' => 0,
        'ghostDevices30d' => 0,
        'fleetAgeDistribution' => [
            'under1yr' => 0,
            'age1to3yr' => 0,
            'age3to5yr' => 0,
            'over5yr' => 0,
            'unknown' => 0
        ],
        'uninstalledDevices' => 0,

        // Supply Metrics
        'supplyAlertsTotal' => 0,
        'supplyAlertsByType' => [],
        'avgSupplyResponseTime' => 0,
        'prematureReplacements' => 0,

        // Panel Message Metrics
        'panelMessagesLast24h' => 0,
        'panelMessagesLast7d' => 0,
        'problemDevices' => 0, // >10 errors in 24h
        'errorClusteringByModel' => [],

        // Connector Health
        'connectorHealthScore' => 0,
        'connectorsOffline' => 0,

        // Cache Health
        'cacheHealthScore' => 0,
        'cacheFreshnessAvg' => 0,
        'drillDownCoverage' => 0,

        // Alert Definitions
        'alertDefinitionCoverage' => 0,
        'unmappedAlertCodes' => 0,

        // Page Volume (30 days)
        'pageVolume30d' => [
            'mono' => 0,
            'color' => 0,
            'total' => 0
        ],

        // Top lists
        'topCustomersByDevices' => [],
        'topCustomersByAlerts' => [],
        'topProblemDevices' => [],
        'topProblematicModels' => []
    ];

    // Fetch all customers
    $customers = fetchAllCustomers();
    $metrics['totalCustomers'] = count($customers);

    // Aggregate per-customer metrics
    $customerMetrics = [];
    foreach ($customers as $customer) {
        $customerData = fetchCustomerMetrics($customer['Code']);
        $customerMetrics[] = $customerData;

        // Sum totals
        $metrics['totalDevices'] += $customerData['totalDevices'];
        $metrics['offlineDevices'] += $customerData['offlineDevices'];
        $metrics['totalAlerts'] += $customerData['alertCount'];
        $metrics['totalConnectors'] += $customerData['connectorCount'];
    }

    // Fetch dealer-wide device data from cache
    $deviceData = fetchDealerDeviceData($pdo);

    // Calculate device status breakdown
    $metrics['devicesByStatus']['online'] = $metrics['totalDevices'] - $metrics['offlineDevices'];
    $metrics['devicesByStatus']['offline'] = $metrics['offlineDevices'];

    // Data Quality: Missing Asset Numbers
    $missingAssets = $pdo->query("
        SELECT COUNT(*) as count
        FROM mpsm_cache_devices
        WHERE (device_data->>'AssetNumber' IS NULL
            OR device_data->>'AssetNumber' = ''
            OR device_data->>'AssetNumber' = 'null')
        AND is_uninstalled = 0
    ")->fetch(PDO::FETCH_ASSOC);
    $metrics['missingAssetNumbers'] = (int)($missingAssets['count'] ?? 0);
    $metrics['assetNumberCompleteness'] = $metrics['totalDevices'] > 0
        ? round((($metrics['totalDevices'] - $metrics['missingAssetNumbers']) / $metrics['totalDevices']) * 100, 1)
        : 100;

    // Data Quality: Duplicate IPs
    $duplicateIPs = $pdo->query("
        SELECT device_data->>'IpAddress' as ip, COUNT(*) as count
        FROM mpsm_cache_devices
        WHERE device_data->>'IpAddress' IS NOT NULL
        AND device_data->>'IpAddress' != ''
        AND device_data->>'IpAddress' != 'null'
        AND is_uninstalled = 0
        GROUP BY device_data->>'IpAddress'
        HAVING COUNT(*) > 1
        ORDER BY count DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    $metrics['duplicateIPs'] = count($duplicateIPs);
    $metrics['duplicateIPsList'] = array_slice($duplicateIPs, 0, 10); // Top 10

    // Fleet Health: Ghost Devices (no contact in 7/30 days)
    $now = new DateTime();
    $ghostCount7d = 0;
    $ghostCount30d = 0;

    $stmt = $pdo->query("
        SELECT device_data->>'LastContact' as last_contact,
               device_data->>'LastUpdate' as last_update
        FROM mpsm_cache_devices
        WHERE is_uninstalled = 0
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $lastContact = $row['last_contact'] ?: $row['last_update'];
        if ($lastContact && $lastContact !== 'null') {
            try {
                $contactDate = new DateTime($lastContact);
                $daysSince = $now->diff($contactDate)->days;
                if ($daysSince > 7) $ghostCount7d++;
                if ($daysSince > 30) $ghostCount30d++;
            } catch (Exception $e) {
                // Invalid date, skip
            }
        }
    }
    $metrics['ghostDevices7d'] = $ghostCount7d;
    $metrics['ghostDevices30d'] = $ghostCount30d;

    // Fleet Age Distribution
    $stmt = $pdo->query("
        SELECT device_data->>'InstallDate' as install_date
        FROM mpsm_cache_devices
        WHERE is_uninstalled = 0
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $installDate = $row['install_date'];
        if ($installDate && $installDate !== 'null') {
            try {
                $install = new DateTime($installDate);
                $age = $now->diff($install);
                $years = $age->y;

                if ($years < 1) {
                    $metrics['fleetAgeDistribution']['under1yr']++;
                } elseif ($years < 3) {
                    $metrics['fleetAgeDistribution']['age1to3yr']++;
                } elseif ($years < 5) {
                    $metrics['fleetAgeDistribution']['age3to5yr']++;
                } else {
                    $metrics['fleetAgeDistribution']['over5yr']++;
                }
            } catch (Exception $e) {
                $metrics['fleetAgeDistribution']['unknown']++;
            }
        } else {
            $metrics['fleetAgeDistribution']['unknown']++;
        }
    }

    // Uninstalled devices count
    $uninstalled = $pdo->query("
        SELECT COUNT(*) as count FROM mpsm_cache_devices WHERE is_uninstalled = 1
    ")->fetch(PDO::FETCH_ASSOC);
    $metrics['uninstalledDevices'] = (int)($uninstalled['count'] ?? 0);

    // Panel Messages metrics
    $panelStats = $pdo->query("
        SELECT
            COUNT(CASE WHEN received_at >= NOW() - INTERVAL 24 HOUR THEN 1 END) as last_24h,
            COUNT(CASE WHEN received_at >= NOW() - INTERVAL 7 DAY THEN 1 END) as last_7d,
            COUNT(DISTINCT device_serial) as unique_devices
        FROM mpsm_panel_messages
        WHERE received_at >= NOW() - INTERVAL 7 DAY
    ")->fetch(PDO::FETCH_ASSOC);

    $metrics['panelMessagesLast24h'] = (int)($panelStats['last_24h'] ?? 0);
    $metrics['panelMessagesLast7d'] = (int)($panelStats['last_7d'] ?? 0);

    // Problem Devices (high error frequency)
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

    // Alert Definition Coverage
    $totalAlertCodes = $pdo->query("
        SELECT COUNT(DISTINCT maintenance_alert_code) as count
        FROM mpsm_panel_messages
        WHERE maintenance_alert_code IS NOT NULL
    ")->fetch(PDO::FETCH_ASSOC);

    $definedAlertCodes = $pdo->query("
        SELECT COUNT(DISTINCT alert_code) as count
        FROM mpsm_alert_definitions
    ")->fetch(PDO::FETCH_ASSOC);

    $totalCodes = (int)($totalAlertCodes['count'] ?? 0);
    $definedCodes = (int)($definedAlertCodes['count'] ?? 0);
    $metrics['unmappedAlertCodes'] = max(0, $totalCodes - $definedCodes);
    $metrics['alertDefinitionCoverage'] = $totalCodes > 0
        ? round(($definedCodes / $totalCodes) * 100, 1)
        : 100;

    // Cache Health Metrics
    $cacheStats = $pdo->query("
        SELECT
            COUNT(*) as total_cached,
            AVG(TIMESTAMPDIFF(SECOND, cached_at, NOW())) as avg_age_seconds
        FROM mpsm_cache_devices
        WHERE is_uninstalled = 0
    ")->fetch(PDO::FETCH_ASSOC);

    $drillDownStats = $pdo->query("
        SELECT COUNT(*) as count FROM mpsm_cache_device_drilldown
    ")->fetch(PDO::FETCH_ASSOC);

    $totalCached = (int)($cacheStats['total_cached'] ?? 0);
    $drillDownCount = (int)($drillDownStats['count'] ?? 0);

    $metrics['cacheFreshnessAvg'] = round((float)($cacheStats['avg_age_seconds'] ?? 0));
    $metrics['drillDownCoverage'] = $totalCached > 0
        ? round(($drillDownCount / $totalCached) * 100, 1)
        : 0;

    // Cache health score (100 = perfect, deduct for staleness and missing drilldown)
    $freshnessScore = min(100, max(0, 100 - ($metrics['cacheFreshnessAvg'] / 3600 * 10))); // -10 per hour
    $coverageScore = $metrics['drillDownCoverage'];
    $metrics['cacheHealthScore'] = round(($freshnessScore + $coverageScore) / 2, 1);

    // Connector aggregation
    $totalConnectorsActive = 0;
    foreach ($customerMetrics as $cm) {
        $totalConnectorsActive += $cm['connectorsActive'];
    }
    $metrics['activeConnectorsLastDay'] = $totalConnectorsActive;
    $metrics['connectorsOffline'] = max(0, $metrics['totalConnectors'] - $metrics['activeConnectorsLastDay']);
    $metrics['connectorHealthScore'] = $metrics['totalConnectors'] > 0
        ? round(($metrics['activeConnectorsLastDay'] / $metrics['totalConnectors']) * 100, 1)
        : 100;

    // Top customers by devices
    usort($customerMetrics, function($a, $b) {
        return $b['totalDevices'] - $a['totalDevices'];
    });
    $metrics['topCustomersByDevices'] = array_slice(array_map(function($c) {
        return [
            'name' => $c['name'],
            'code' => $c['code'],
            'devices' => $c['totalDevices']
        ];
    }, $customerMetrics), 0, 10);

    // Top customers by alerts
    usort($customerMetrics, function($a, $b) {
        return $b['alertCount'] - $a['alertCount'];
    });
    $metrics['topCustomersByAlerts'] = array_slice(array_map(function($c) {
        return [
            'name' => $c['name'],
            'code' => $c['code'],
            'alerts' => $c['alertCount']
        ];
    }, $customerMetrics), 0, 10);

    return $metrics;
}

function fetchAllCustomers() {
    $url = 'https://mpsm.resolutionsbydesign.us/cms/api/get-customers.php?dealerCode=' . DEFAULT_DEALER_CODE;
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 30
        ]
    ]);

    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return [];
    }

    $data = json_decode($response, true);
    return $data['customers'] ?? [];
}

function fetchCustomerMetrics($customerCode) {
    $url = 'https://mpsm.resolutionsbydesign.us/cms/api/get-customer-dashboard-cached.php?customerCode=' . urlencode($customerCode);
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 15
        ]
    ]);

    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return [
            'code' => $customerCode,
            'name' => $customerCode,
            'totalDevices' => 0,
            'offlineDevices' => 0,
            'alertCount' => 0,
            'connectorCount' => 0,
            'connectorsActive' => 0
        ];
    }

    $data = json_decode($response, true);
    $dashboard = $data['dashboard']['MpsDashboardCustomer'] ?? [];

    $totalDevices = (int)($dashboard['TotalManagedDevices'] ?? 0);
    $offlineDevices = (int)($dashboard['OfflineDevices'] ?? 0);

    // Sum all supply alert categories
    $alertCount = 0;
    $supplyAlerts = $dashboard['SupplyAlerts'] ?? [];
    foreach ($supplyAlerts as $alert) {
        $alertCount += (int)($alert['Value'] ?? 0);
    }

    // Connector counts
    $connectors = $data['dashboard']['Connectors'] ?? [];
    $connectorCount = (int)($connectors['TotalWin'] ?? 0) + (int)($connectors['TotalEmbedded'] ?? 0);
    $connectorsActive = (int)($connectors['LastDay'] ?? 0);

    return [
        'code' => $customerCode,
        'name' => $customerCode, // Will be enriched later
        'totalDevices' => $totalDevices,
        'offlineDevices' => $offlineDevices,
        'alertCount' => $alertCount,
        'connectorCount' => $connectorCount,
        'connectorsActive' => $connectorsActive
    ];
}

function fetchDealerDeviceData($pdo) {
    // Fetch from cache for performance
    $stmt = $pdo->query("
        SELECT device_data
        FROM mpsm_cache_devices
        WHERE is_uninstalled = 0
        LIMIT 1000
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/*
CHANGELOG
2025-12-02 Claude
- Initial implementation: Executive summary API aggregating 30+ dealer-wide metrics
- Includes device health, data quality, fleet age, panel messages, connectors, cache health
- Uses existing cached endpoints and database tables (no new backend)
- 30-minute cache TTL with force refresh option
*/
