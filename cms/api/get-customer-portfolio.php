<?php
/**
 * Customer Portfolio API
 * Returns customer list with aggregated metrics and health scores
 * Used for executive dashboard portfolio table
 */

require '../config.php';
require '../functions.php';

requireAuth();

$forceRefresh = isset($_GET['force']) && $_GET['force'] === '1';

try {
    $cacheKey = 'customer-portfolio';
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
                        'customers' => $data['customers'],
                        'total' => count($data['customers']),
                        'cached' => true,
                        'cache_age_seconds' => $age
                    ]);
                }
            }
        }
    }

    // Build fresh portfolio
    $customers = buildCustomerPortfolio();

    // Cache result
    cacheStore($cacheKey, json_encode([
        'timestamp' => time(),
        'customers' => $customers
    ]));

    jsonSuccess([
        'customers' => $customers,
        'total' => count($customers),
        'cached' => false,
        'cache_age_seconds' => 0
    ]);

} catch (Exception $e) {
    error_log("Customer Portfolio Error: " . $e->getMessage());
    jsonError("Failed to generate customer portfolio: " . $e->getMessage());
}

function buildCustomerPortfolio() {
    $pdo = getDatabase();

    // Fetch all customers from API
    $customersResponse = fetchAllCustomers();

    $portfolio = [];

    foreach ($customersResponse as $customer) {
        $customerCode = $customer['Code'] ?? '';
        $customerName = $customer['Name'] ?? $customerCode;

        if (!$customerCode) {
            continue;
        }

        // Fetch customer dashboard metrics (cached)
        $metrics = fetchCustomerDashboardMetrics($customerCode);

        // Fetch additional metrics from database
        $dbMetrics = fetchCustomerDatabaseMetrics($pdo, $customerCode);

        // Calculate health score
        $healthScore = calculateHealthScore($metrics, $dbMetrics);

        // Determine last contact from devices
        $lastContact = $dbMetrics['lastContact'] ?? null;

        $portfolio[] = [
            'code' => $customerCode,
            'name' => $customerName,
            'totalDevices' => $metrics['totalDevices'],
            'onlineDevices' => $metrics['totalDevices'] - $metrics['offlineDevices'],
            'offlineDevices' => $metrics['offlineDevices'],
            'alertCount' => $metrics['alertCount'],
            'connectorCount' => $metrics['connectorCount'],
            'connectorsActive' => $metrics['connectorsActive'],
            'connectorsOffline' => max(0, $metrics['connectorCount'] - $metrics['connectorsActive']),
            'ghostDevices' => $dbMetrics['ghostDevices'],
            'missingAssets' => $dbMetrics['missingAssets'],
            'duplicateIPs' => $dbMetrics['duplicateIPs'],
            'panelErrors24h' => $dbMetrics['panelErrors24h'],
            'lastContact' => $lastContact,
            'healthScore' => $healthScore,
            'healthStatus' => getHealthStatus($healthScore)
        ];
    }

    // Sort by health score (lowest first = needs attention)
    usort($portfolio, function($a, $b) {
        return $a['healthScore'] - $b['healthScore'];
    });

    return $portfolio;
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

function fetchCustomerDashboardMetrics($customerCode) {
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
            'totalDevices' => 0,
            'offlineDevices' => 0,
            'alertCount' => 0,
            'connectorCount' => 0,
            'connectorsActive' => 0
        ];
    }

    $data = json_decode($response, true);
    $dashboard = $data['dashboard']['MpsDashboardCustomer'] ?? [];
    $connectors = $data['dashboard']['Connectors'] ?? [];

    $totalDevices = (int)($dashboard['TotalManagedDevices'] ?? 0);
    $offlineDevices = (int)($dashboard['OfflineDevices'] ?? 0);

    // Sum all supply alert categories
    $alertCount = 0;
    $supplyAlerts = $dashboard['SupplyAlerts'] ?? [];
    foreach ($supplyAlerts as $alert) {
        $alertCount += (int)($alert['Value'] ?? 0);
    }

    $connectorCount = (int)($connectors['TotalWin'] ?? 0) + (int)($connectors['TotalEmbedded'] ?? 0);
    $connectorsActive = (int)($connectors['LastDay'] ?? 0);

    return [
        'totalDevices' => $totalDevices,
        'offlineDevices' => $offlineDevices,
        'alertCount' => $alertCount,
        'connectorCount' => $connectorCount,
        'connectorsActive' => $connectorsActive
    ];
}

function fetchCustomerDatabaseMetrics($pdo, $customerCode) {
    // Ghost devices (no contact in 7+ days)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM mpsm_cache_devices
        WHERE customer_code = :customerCode
        AND is_uninstalled = 0
        AND (
            TIMESTAMPDIFF(DAY, STR_TO_DATE(device_data->>'$.LastContact', '%Y-%m-%dT%H:%i:%s'), NOW()) > 7
            OR (device_data->>'$.LastContact' IS NULL AND TIMESTAMPDIFF(DAY, cached_at, NOW()) > 7)
        )
    ");
    $stmt->execute(['customerCode' => $customerCode]);
    $ghostDevices = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

    // Missing asset numbers
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM mpsm_cache_devices
        WHERE customer_code = :customerCode
        AND is_uninstalled = 0
        AND (device_data->>'$.AssetNumber' IS NULL
            OR device_data->>'$.AssetNumber' = ''
            OR device_data->>'$.AssetNumber' = 'null')
    ");
    $stmt->execute(['customerCode' => $customerCode]);
    $missingAssets = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

    // Duplicate IPs within customer
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM (
            SELECT device_data->>'$.IpAddress' as ip
            FROM mpsm_cache_devices
            WHERE customer_code = :customerCode
            AND is_uninstalled = 0
            AND device_data->>'$.IpAddress' IS NOT NULL
            AND device_data->>'$.IpAddress' != ''
            AND device_data->>'$.IpAddress' != 'null'
            GROUP BY device_data->>'$.IpAddress'
            HAVING COUNT(*) > 1
        ) as duplicates
    ");
    $stmt->execute(['customerCode' => $customerCode]);
    $duplicateIPs = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

    // Panel errors in last 24h
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM mpsm_panel_messages pm
        INNER JOIN mpsm_cache_devices cd ON pm.device_serial = cd.serial_number
        WHERE cd.customer_code = :customerCode
        AND pm.received_at >= NOW() - INTERVAL 24 HOUR
    ");
    $stmt->execute(['customerCode' => $customerCode]);
    $panelErrors24h = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

    // Last contact (most recent device update)
    $stmt = $pdo->prepare("
        SELECT MAX(
            COALESCE(
                STR_TO_DATE(device_data->>'$.LastContact', '%Y-%m-%dT%H:%i:%s'),
                cached_at
            )
        ) as last_contact
        FROM mpsm_cache_devices
        WHERE customer_code = :customerCode
        AND is_uninstalled = 0
    ");
    $stmt->execute(['customerCode' => $customerCode]);
    $lastContactRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $lastContact = $lastContactRow['last_contact'] ?? null;

    return [
        'ghostDevices' => $ghostDevices,
        'missingAssets' => $missingAssets,
        'duplicateIPs' => $duplicateIPs,
        'panelErrors24h' => $panelErrors24h,
        'lastContact' => $lastContact
    ];
}

function calculateHealthScore($metrics, $dbMetrics) {
    // Start at 100, deduct for issues
    $score = 100;

    $totalDevices = $metrics['totalDevices'];
    if ($totalDevices === 0) {
        return 0; // No devices = no score
    }

    // Offline devices penalty (max -30 points)
    $offlineRate = $metrics['offlineDevices'] / $totalDevices;
    $score -= min(30, $offlineRate * 100);

    // Alert count penalty (max -20 points)
    $alertRate = $metrics['alertCount'] / $totalDevices;
    $score -= min(20, $alertRate * 20);

    // Offline connectors penalty (10 points per offline connector)
    $connectorsOffline = max(0, $metrics['connectorCount'] - $metrics['connectorsActive']);
    $score -= min(20, $connectorsOffline * 10);

    // Ghost devices penalty (max -15 points)
    $ghostRate = $dbMetrics['ghostDevices'] / $totalDevices;
    $score -= min(15, $ghostRate * 50);

    // Data quality penalty (max -10 points)
    $missingAssetRate = $dbMetrics['missingAssets'] / $totalDevices;
    $score -= min(5, $missingAssetRate * 20);

    // Duplicate IPs penalty
    $score -= min(5, $dbMetrics['duplicateIPs'] * 2);

    // Panel errors penalty (recent activity indicator)
    $errorRate = $dbMetrics['panelErrors24h'] / $totalDevices;
    $score -= min(10, $errorRate * 30);

    return max(0, min(100, round($score)));
}

function getHealthStatus($score) {
    if ($score >= 90) return 'excellent';
    if ($score >= 75) return 'good';
    if ($score >= 60) return 'fair';
    if ($score >= 40) return 'poor';
    return 'critical';
}

/*
CHANGELOG
2025-12-02 Claude
- Initial implementation: Customer portfolio API with health scoring
- Calculates comprehensive health scores based on offline devices, alerts, connectors, ghost devices, data quality
- Includes customer-specific metrics: ghost devices, missing assets, duplicate IPs, panel errors
- Uses cached endpoints for performance (30-minute TTL)
- Returns sorted list (lowest health score first = needs attention)
*/
