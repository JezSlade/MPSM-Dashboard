<?php
/**
 * Customer Portfolio API
 * Returns customer list with aggregated metrics and health scores
 * Used for dealer dashboard portfolio table
 */

require '../config.php';
require '../functions.php';

// Auth bypass: Allow secret parameter for programmatic access
$bypassSecret = 'DEALER_API_2025';
$providedSecret = $_GET['secret'] ?? $_POST['secret'] ?? '';
$authBypassed = ($providedSecret === $bypassSecret);

if (!$authBypassed) {
    requireAuth();
}

$forceRefresh = isset($_GET['force']) && $_GET['force'] === '1';
$source = 'live_api';
$limit = isset($_GET['limit']) ? min(max((int)$_GET['limit'], 1), 200) : 100;

try {
    $cacheKey = 'customer-portfolio-v2-limit-' . $limit;
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
                        'cache_age_seconds' => $age,
                        'cache_source' => $data['source'] ?? 'unknown'
                    ]);
                }
            }
        }
    }

    $pdo = getDatabase();
    $cacheDeviceCount = getCachedDeviceCount($pdo);

    // Build fresh portfolio (prefer cache when populated)
    if (!$forceRefresh && $cacheDeviceCount > 0) {
        $customers = buildCustomerPortfolioFromCache($pdo, $limit);
        $source = 'cache';
    } else {
        $customers = buildCustomerPortfolioLive($limit);
        $source = 'live_api';
    }

    // Cache result
    cacheStore($cacheKey, json_encode([
        'timestamp' => time(),
        'customers' => $customers,
        'source' => $source
    ]));

    jsonSuccess([
        'customers' => $customers,
        'total' => count($customers),
        'cached' => false,
        'cache_age_seconds' => 0,
        'cache_source' => $source
    ]);

} catch (Exception $e) {
    error_log("Customer Portfolio Error: " . $e->getMessage());
    jsonError("Failed to generate customer portfolio: " . $e->getMessage());
}

function buildCustomerPortfolioLive(int $limit) {
    $pdo = getDatabase();

    // Fetch all customers from API
    $customersResponse = fetchAllCustomers();

    $portfolio = [];

    foreach ($customersResponse as $customer) {
        if (count($portfolio) >= $limit) {
            break;
        }

        $customerCode = $customer['Code'] ?? '';
        $customerName = $customer['Description'] ?? $customer['Code'];

        if (!$customerCode) {
            continue;
        }

        // Fetch customer dashboard metrics from live API
        $metrics = fetchCustomerDashboardMetrics($customerCode);

        // Check if cache table exists and has data for this customer
        $cacheTableExists = checkTableExists($pdo, 'mpsm_cache_devices');
        $dbMetrics = [
            'offlineDevices' => 0,
            'ghostDevices' => 0,
            'noContactDataDevices' => 0,
            'missingAssets' => 0,
            'duplicateIPs' => 0,
            'panelErrors24h' => 0,
            'lastContact' => null
        ];

        if ($cacheTableExists) {
            try {
                $dbMetrics = fetchCustomerDatabaseMetrics($pdo, $customerCode);
            } catch (Exception $e) {
                // Cache empty or SQL error, use defaults
                error_log("Portfolio DB metrics error for {$customerCode}: " . $e->getMessage());
            }
        }

        // Calculate health score
        $healthScore = calculateHealthScore($metrics, $dbMetrics);

        // Only include customers with active devices (non-zero)
        if ($metrics['totalDevices'] > 0) {
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
                'noContactDataDevices' => $dbMetrics['noContactDataDevices'],
                'missingAssets' => $dbMetrics['missingAssets'],
                'duplicateIPs' => $dbMetrics['duplicateIPs'],
                'panelErrors24h' => $dbMetrics['panelErrors24h'],
                'lastContact' => $dbMetrics['lastContact'],
                'healthScore' => $healthScore,
                'healthStatus' => getHealthStatus($healthScore)
            ];
        }
    }

    // Sort by health score (lowest first = needs attention)
    usort($portfolio, function($a, $b) {
        return $a['healthScore'] - $b['healthScore'];
    });

    return $portfolio;
}

function buildCustomerPortfolioFromCache($pdo, int $limit) {
    $lastContactExpr = "STR_TO_DATE(
        REPLACE(
            REPLACE(
                SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.LastContact')), '.', 1),
                'T',
                ' '
            ),
            'Z',
            ''
        ),
        '%Y-%m-%d %H:%i:%s'
    )";
    $isOfflineExpr = "LOWER(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IsOffline')), ''))";
    $offlineFlagExpr = "({$isOfflineExpr} IN ('1','true','yes'))";

    $stmt = $pdo->prepare("
        SELECT
            customer_code,
            MAX(
                COALESCE(
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.CustomerName')), ''),
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.CustomerDescription')), ''),
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Description')), ''),
                    customer_code
                )
            ) as customer_name,
            COUNT(*) as total_devices,
            SUM(CASE
                    WHEN {$offlineFlagExpr} THEN 1
                    WHEN {$lastContactExpr} IS NOT NULL
                     AND {$lastContactExpr} <= DATE_SUB(NOW(), INTERVAL 2 DAY)
                    THEN 1 ELSE 0 END
            ) as offline_devices,
            SUM(CASE
                    WHEN {$lastContactExpr} IS NOT NULL
                     AND {$lastContactExpr} <= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    THEN 1 ELSE 0 END
            ) as ghost_devices,
            SUM(CASE WHEN {$lastContactExpr} IS NULL THEN 1 ELSE 0 END) as no_contact_data_devices,
            SUM(CASE
                    WHEN JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.AssetNumber')) IS NULL
                      OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.AssetNumber')) = ''
                      OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.AssetNumber')) = 'null'
                    THEN 1 ELSE 0 END
            ) as missing_assets
        FROM mpsm_cache_devices
        WHERE customer_code IS NOT NULL
          AND customer_code != ''
          AND is_uninstalled = 0
        GROUP BY customer_code
        ORDER BY total_devices DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $customerNameMap = fetchCustomerNameMap();

    $portfolio = [];

    foreach ($rows as $row) {
        $customerCode = $row['customer_code'];
        $customerName = $row['customer_name'] ?: $customerCode;
        if ($customerName === $customerCode && isset($customerNameMap[$customerCode])) {
            $customerName = $customerNameMap[$customerCode];
        }

        $totalDevices = (int)$row['total_devices'];
        $offlineDevices = (int)$row['offline_devices'];
        $ghostDevices = (int)$row['ghost_devices'];
        $noContactDataDevices = (int)$row['no_contact_data_devices'];
        $missingAssets = (int)$row['missing_assets'];

        // Duplicate IPs within customer
        $dupStmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM (
                SELECT JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) as ip
                FROM mpsm_cache_devices
                WHERE customer_code = :customerCode
                  AND is_uninstalled = 0
                  AND JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) IS NOT NULL
                  AND JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) != ''
                  AND JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) != 'null'
                GROUP BY JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress'))
                HAVING COUNT(*) > 1
            ) as duplicates
        ");
        $dupStmt->execute(['customerCode' => $customerCode]);
        $duplicateIPs = (int)($dupStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        $hasPanelMessagesTable = $hasPanelMessagesTable ?? checkTableExists($pdo, 'mpsm_panel_messages');
        $panelErrors24h = 0;
        if ($hasPanelMessagesTable) {
            // Panel errors in last 24h
            $panelStmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM mpsm_panel_messages pm
                INNER JOIN mpsm_cache_devices cd ON pm.device_serial = cd.serial_number
                WHERE cd.customer_code = :customerCode
                  AND pm.received_at >= NOW() - INTERVAL 24 HOUR
            ");
            $panelStmt->execute(['customerCode' => $customerCode]);
            $panelErrors24h = (int)($panelStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
        }

        $connectorCount = 0;
        $connectorsActive = 0;

        $healthScore = calculateHealthScore([
            'totalDevices' => $totalDevices,
            'offlineDevices' => $offlineDevices,
            'alertCount' => 0,
            'connectorCount' => $connectorCount,
            'connectorsActive' => $connectorsActive
        ], [
            'ghostDevices' => $ghostDevices,
            'missingAssets' => $missingAssets,
            'duplicateIPs' => $duplicateIPs,
            'panelErrors24h' => $panelErrors24h
        ]);

        $portfolio[] = [
            'code' => $customerCode,
            'name' => $customerName,
            'totalDevices' => $totalDevices,
            'onlineDevices' => max(0, $totalDevices - $offlineDevices),
            'offlineDevices' => $offlineDevices,
            'alertCount' => 0,
            'connectorCount' => $connectorCount,
            'connectorsActive' => $connectorsActive,
            'connectorsOffline' => max(0, $connectorCount - $connectorsActive),
            'ghostDevices' => $ghostDevices,
            'noContactDataDevices' => $noContactDataDevices,
            'missingAssets' => $missingAssets,
            'duplicateIPs' => $duplicateIPs,
            'panelErrors24h' => $panelErrors24h,
            'lastContact' => null,
            'healthScore' => $healthScore,
            'healthStatus' => getHealthStatus($healthScore)
        ];
    }

    usort($portfolio, function($a, $b) {
        return $a['healthScore'] - $b['healthScore'];
    });

    return $portfolio;
}

function fetchAllCustomers() {
    $dealerCode = DEFAULT_DEALER_CODE;

    // Use callMPSQuery to fetch customers directly from MPS API
    $customers = callMPSQuery('Customer/GetCustomers', [
        'DealerCode' => $dealerCode,
        'FilterDealerCodes' => [$dealerCode],
        'PageNumber' => 1,
        'PageRows' => 1000,
        'SortColumn' => 'Code'
    ]);

    if (!$customers || !is_array($customers)) {
        return [];
    }

    return $customers;
}

function fetchCustomerDashboardMetrics($customerCode) {
    $dealerCode = DEFAULT_DEALER_CODE;

    // Use callMPSQuery to fetch dashboard directly from MPS API
    $dashboard = callMPSQuery('CustomerDashboard/Get', [
        'Code' => $customerCode,
        'DealerCode' => $dealerCode
    ]);

    if (!$dashboard || !is_array($dashboard)) {
        return [
            'totalDevices' => 0,
            'offlineDevices' => 0,
            'alertCount' => 0,
            'connectorCount' => 0,
            'connectorsActive' => 0
        ];
    }

    $totalDevices = (int)($dashboard['TotalManagedDevices'] ?? 0);
    $offlineRaw = $dashboard['OfflineDevices'] ?? null;
    $offlineDevices = is_numeric($offlineRaw) ? (int)$offlineRaw : null;

    // Sum all supply alert categories
    $alertCount = 0;
    $supplyAlerts = $dashboard['SupplyAlerts'] ?? [];
    foreach ($supplyAlerts as $alert) {
        $alertCount += (int)($alert['Value'] ?? 0);
    }

    $connectorCount = $dashboard['TotalConnectors'] ?? 0;
    // ContactedDevices shows activity by day
    $contacted = $dashboard['ContactedDevices'] ?? [];
    $contactedTodayOrYesterday = 0;
    $contactedBeforeYesterday = 0;
    foreach ($contacted as $day) {
        if (!isset($day['Key'])) {
            continue;
        }
        $key = (string)$day['Key'];
        $value = (int)($day['Value'] ?? 0);
        if ($key === 'Today' || $key === 'Yesterday') {
            $contactedTodayOrYesterday += $value;
        } elseif ($key === 'BeforeYesterday') {
            $contactedBeforeYesterday += $value;
        }
    }

    // Current upstream payload does not expose active connector count directly in this endpoint.
    // Keep connector fields bounded and avoid deriving connector activity from device-contact counts.
    $connectorsActive = (int)$connectorCount;

    // If OfflineDevices is not returned, estimate offline from recent contact windows.
    // "BeforeYesterday" devices are stale for a 48-hour freshness threshold.
    if ($offlineDevices === null) {
        // Fallback from ContactedDevices buckets:
        // devices contacted today/yesterday are "online";
        // "BeforeYesterday" devices are stale for a 48-hour threshold.
        $offlineDevices = max(0, $totalDevices - $contactedTodayOrYesterday);
    }

    // CustomerDashboard/Get does not provide a reliable 7-day no-contact bucket.
    // Keep this metric sourced from cache-device contact timestamps when available.
    $ghostDevices = 0;

    return [
        'totalDevices' => $totalDevices,
        'offlineDevices' => $offlineDevices,
        'alertCount' => $alertCount,
        'connectorCount' => $connectorCount,
        'connectorsActive' => $connectorsActive,
        'ghostDevices' => $ghostDevices
    ];
}

function fetchCustomerDatabaseMetrics($pdo, $customerCode) {
    $hasPanelMessagesTable = checkTableExists($pdo, 'mpsm_panel_messages');

    $lastContactExpr = "STR_TO_DATE(
        REPLACE(
            REPLACE(
                SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.LastContact')), '.', 1),
                'T',
                ' '
            ),
            'Z',
            ''
        ),
        '%Y-%m-%d %H:%i:%s'
    )";
    $isOfflineExpr = "LOWER(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IsOffline')), ''))";
    $offlineFlagExpr = "({$isOfflineExpr} IN ('1','true','yes'))";

    // Contact health metrics sourced from per-device cache payload fields.
    $stmt = $pdo->prepare("
        SELECT
            SUM(
                CASE
                    WHEN {$offlineFlagExpr} THEN 1
                    WHEN {$lastContactExpr} IS NOT NULL
                     AND {$lastContactExpr} <= DATE_SUB(NOW(), INTERVAL 2 DAY)
                    THEN 1
                    ELSE 0
                END
            ) AS offline_devices,
            SUM(
                CASE
                    WHEN {$lastContactExpr} IS NOT NULL
                     AND {$lastContactExpr} <= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    THEN 1
                    ELSE 0
                END
            ) AS ghost_devices,
            SUM(CASE WHEN {$lastContactExpr} IS NULL THEN 1 ELSE 0 END) AS no_contact_data_devices
        FROM mpsm_cache_devices
        WHERE customer_code = :customerCode
        AND is_uninstalled = 0
    ");
    $stmt->execute(['customerCode' => $customerCode]);
    $contactMetrics = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $offlineDevices = (int)($contactMetrics['offline_devices'] ?? 0);
    $ghostDevices = (int)($contactMetrics['ghost_devices'] ?? 0);
    $noContactDataDevices = (int)($contactMetrics['no_contact_data_devices'] ?? 0);

    // Missing asset numbers
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM mpsm_cache_devices
        WHERE customer_code = :customerCode
        AND is_uninstalled = 0
        AND (JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.AssetNumber')) IS NULL
            OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.AssetNumber')) = ''
            OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.AssetNumber')) = 'null')
    ");
    $stmt->execute(['customerCode' => $customerCode]);
    $missingAssets = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

    // Duplicate IPs within customer
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM (
            SELECT JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) as ip
            FROM mpsm_cache_devices
            WHERE customer_code = :customerCode
            AND is_uninstalled = 0
            AND JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) IS NOT NULL
            AND JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) != ''
            AND JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) != 'null'
            GROUP BY JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress'))
            HAVING COUNT(*) > 1
        ) as duplicates
    ");
    $stmt->execute(['customerCode' => $customerCode]);
    $duplicateIPs = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

    $hasPanelMessagesTable = $hasPanelMessagesTable ?? checkTableExists($pdo, 'mpsm_panel_messages');
    $panelErrors24h = 0;
    if ($hasPanelMessagesTable) {
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
    }

    // Last contact (most recent device update)
    $stmt = $pdo->prepare("
        SELECT MAX(
            COALESCE(
                STR_TO_DATE(
                    REPLACE(
                        REPLACE(
                            SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.LastContact')), '.', 1),
                            'T',
                            ' '
                        ),
                        'Z',
                        ''
                    ),
                    '%Y-%m-%d %H:%i:%s'
                ),
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
        'offlineDevices' => $offlineDevices,
        'ghostDevices' => $ghostDevices,
        'noContactDataDevices' => $noContactDataDevices,
        'missingAssets' => $missingAssets,
        'duplicateIPs' => $duplicateIPs,
        'panelErrors24h' => $panelErrors24h,
        'lastContact' => $lastContact
    ];
}

function fetchCustomerNameMap() {
    $map = [];
    try {
        $customers = fetchAllCustomers();
        foreach ($customers as $customer) {
            $code = trim((string)($customer['Code'] ?? ''));
            $description = trim((string)($customer['Description'] ?? ''));
            if ($code !== '' && $description !== '') {
                $map[$code] = $description;
            }
        }
    } catch (Exception $e) {
        error_log('fetchCustomerNameMap error: ' . $e->getMessage());
    }
    return $map;
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

function checkTableExists($pdo, $tableName) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$tableName}'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

function getCachedDeviceCount($pdo) {
    try {
        $stats = $pdo->query("SELECT COUNT(*) as count FROM mpsm_cache_devices WHERE is_uninstalled = 0")->fetch(PDO::FETCH_ASSOC);
        return (int)($stats['count'] ?? 0);
    } catch (Exception $e) {
        error_log('getCachedDeviceCount error (portfolio): ' . $e->getMessage());
        return 0;
    }
}

/*
CHANGELOG
2025-12-02 Claude
- Initial implementation: Customer portfolio API with health scoring
- Calculates comprehensive health scores based on offline devices, alerts, connectors, ghost devices, data quality
- Includes customer-specific metrics: ghost devices, missing assets, duplicate IPs, panel errors
- Uses cached endpoints for performance (30-minute TTL)
- Returns sorted list (lowest health score first = needs attention)
2025-12-03 Codex
- Added cache-first path to serve portfolio from mpsm_cache_devices when populated, falling back to live API only when empty or forced; cached payload now records its source.
- Derived portfolio metrics from cache (offline/ghost/missing assets, duplicate IPs, panel errors) while defaulting connector/alert counts to zero when absent from cache to avoid blocking the UI.
- Improved resiliency of cached responses by including cache_source metadata and keeping the TTL guard.
*/
