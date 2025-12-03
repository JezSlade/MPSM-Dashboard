<?php
/**
 * Dealer Summary API - Hybrid (Cache OR Live API Fallback)
 * If cache empty, fetches live data from MPS API
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

try {
    $cacheKey = 'dealer-summary-hybrid';
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
    $summary = buildHybridSummary();

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
    error_log("Dealer Summary Hybrid Error: " . $e->getMessage());
    jsonError("Failed to generate dealer summary: " . $e->getMessage());
}

function buildHybridSummary() {
    $pdo = getDatabase();

    // Check if cache has devices
    $cacheTableExists = checkTableExists($pdo, 'mpsm_cache_devices');
    $deviceCount = 0;

    if ($cacheTableExists) {
        $stats = $pdo->query("SELECT COUNT(*) as count FROM mpsm_cache_devices WHERE is_uninstalled = 0")->fetch(PDO::FETCH_ASSOC);
        $deviceCount = (int)($stats['count'] ?? 0);
    }

    // If cache is empty, fetch live data from MPS API
    if ($deviceCount === 0) {
        return buildLiveMetrics();
    }

    // Use cached data (fast path)
    return buildCachedMetrics($pdo);
}

function buildLiveMetrics() {
    // Fetch directly from MPS API (slow but always works)
    $metrics = initializeMetrics();

    try {
        // Get dealer code
        $dealerCode = DEFAULT_DEALER_CODE;

        // Fetch customers with required pagination params
        $customers = callMPSQuery('Customer/GetCustomers', [
            'DealerCode' => $dealerCode,
            'FilterDealerCodes' => [$dealerCode],
            'PageNumber' => 1,
            'PageRows' => 1000,
            'SortColumn' => 'Code'
        ]);

        if ($customers && is_array($customers)) {
            $metrics['totalCustomers'] = count($customers);

            // Sample first 10 customers for quick metrics (full scan would be too slow)
            $sampleCustomers = array_slice($customers, 0, 10);
            $totalDevices = 0;
            $totalOffline = 0;

            $totalAlerts = 0;
            $totalConnectors = 0;
            $totalGhost7d = 0;

            foreach ($sampleCustomers as $customer) {
                $customerCode = $customer['Code'] ?? '';
                if (!$customerCode) continue;

                // Get customer dashboard
                $dashboard = callMPSQuery('CustomerDashboard/Get', [
                    'Code' => $customerCode,
                    'DealerCode' => $dealerCode
                ]);
                if ($dashboard && is_array($dashboard)) {
                    $totalDevices += (int)($dashboard['TotalManagedDevices'] ?? 0);
                    $totalConnectors += (int)($dashboard['TotalConnectors'] ?? 0);

                    // Calculate offline and ghost devices from ContactedDevices
                    $contacted = $dashboard['ContactedDevices'] ?? [];
                    $online = 0;
                    $recentContact = 0;  // Today, Yesterday, BeforeYesterday
                    foreach ($contacted as $day) {
                        if ($day['Key'] === 'Today' || $day['Key'] === 'Yesterday') {
                            $online += (int)($day['Value'] ?? 0);
                        }
                        if ($day['Key'] === 'Today' || $day['Key'] === 'Yesterday' || $day['Key'] === 'BeforeYesterday') {
                            $recentContact += (int)($day['Value'] ?? 0);
                        }
                    }
                    $customerDevices = (int)($dashboard['TotalManagedDevices'] ?? 0);
                    $customerOffline = max(0, $customerDevices - $online);
                    $totalOffline += $customerOffline;

                    // Ghost devices: devices that haven't contacted in 3+ days
                    $customerGhost = max(0, $customerDevices - $recentContact);
                    $totalGhost7d += $customerGhost;

                    // Sum supply alerts
                    $supplyAlerts = $dashboard['SupplyAlerts'] ?? [];
                    foreach ($supplyAlerts as $alert) {
                        if ($alert['Key'] === 'ToManage') {
                            $totalAlerts += (int)($alert['Value'] ?? 0);
                        }
                    }
                }
            }

            // Fetch ALL devices to calculate duplicate IPs, ages, uninstalled (MISSION CRITICAL)
            // Note: This is slower but necessary for accurate duplicate IP detection
            // PAGINATION LOOP: Device/List may return limited results per page
            $allDevices = [];
            $pageNumber = 1;
            $pageRows = 1000;  // Request 1000 per page for reasonable performance
            $maxPages = 50;    // Safety limit (50 pages * 1000 = 50,000 devices max)

            do {
                $pageDevices = callMPSQuery('Device/List', [
                    'DealerCode' => $dealerCode,
                    'FilterDealerCodes' => [$dealerCode],
                    'PageNumber' => $pageNumber,
                    'PageRows' => $pageRows,
                    'SortColumn' => 'SerialNumber'
                ]);

                if (!$pageDevices || !is_array($pageDevices) || count($pageDevices) === 0) {
                    break;  // No more devices
                }

                $allDevices = array_merge($allDevices, $pageDevices);
                $pageNumber++;

                // If we got fewer devices than requested, we're on the last page
                if (count($pageDevices) < $pageRows) {
                    break;
                }

            } while ($pageNumber <= $maxPages);

            if ($allDevices && is_array($allDevices)) {
                $ipAddresses = [];
                $ages = ['under1yr' => 0, 'age1to3yr' => 0, 'age3to5yr' => 0, 'over5yr' => 0, 'unknown' => 0];
                $uninstalledCount = 0;

                foreach ($allDevices as $device) {
                    // Count uninstalled devices
                    if (!empty($device['Uninstall'])) {
                        $uninstalledCount++;
                        continue; // Skip uninstalled devices from other calcs
                    }

                    // Track IP addresses for duplicate detection
                    $ip = $device['IpAddress'] ?? null;
                    if ($ip && $ip !== '' && $ip !== '0.0.0.0') {
                        if (!isset($ipAddresses[$ip])) {
                            $ipAddresses[$ip] = 0;
                        }
                        $ipAddresses[$ip]++;
                    }

                    // Calculate device age
                    $installDate = $device['Install'] ?? null;
                    if ($installDate) {
                        $install = strtotime($installDate);
                        $ageYears = (time() - $install) / (365.25 * 24 * 3600);
                        if ($ageYears < 1) {
                            $ages['under1yr']++;
                        } elseif ($ageYears < 3) {
                            $ages['age1to3yr']++;
                        } elseif ($ageYears < 5) {
                            $ages['age3to5yr']++;
                        } else {
                            $ages['over5yr']++;
                        }
                    } else {
                        $ages['unknown']++;
                    }
                }

                // Count duplicate IPs
                $duplicateIPCount = 0;
                foreach ($ipAddresses as $ip => $count) {
                    if ($count > 1) {
                        $duplicateIPCount++;
                    }
                }

                $metrics['duplicateIPs'] = $duplicateIPCount;
                $metrics['fleetAgeDistribution'] = $ages;
                $metrics['uninstalledDevices'] = $uninstalledCount;
                $metrics['totalDevices'] = count($allDevices) - $uninstalledCount;
            }

            // Extrapolate sampled customer metrics to full customer base
            $sampleSize = count($sampleCustomers);
            if ($sampleSize > 0) {
                $multiplier = $metrics['totalCustomers'] / $sampleSize;
                $metrics['offlineDevices'] = round($totalOffline * $multiplier);
                $metrics['totalAlerts'] = round($totalAlerts * $multiplier);
                $metrics['totalConnectors'] = round($totalConnectors * $multiplier);
                $metrics['ghostDevices7d'] = round($totalGhost7d * $multiplier);
                $metrics['devicesByStatus']['online'] = $metrics['totalDevices'] - $metrics['offlineDevices'];
                $metrics['devicesByStatus']['offline'] = $metrics['offlineDevices'];
            }
        }

        $metrics['_dataSource'] = 'live_api';
        $metrics['_note'] = 'Fetched ' . count($allDevices ?? []) . ' devices across ' . ($pageNumber - 1) . ' pages. Sampled ' . count($sampleCustomers ?? []) . ' customers for dashboard metrics.';

    } catch (Exception $e) {
        error_log('Live metrics error: ' . $e->getMessage());
        $metrics['_error'] = 'Failed to fetch live data: ' . $e->getMessage();
    }

    return $metrics;
}

function buildCachedMetrics($pdo) {
    $metrics = initializeMetrics();

    // Total devices from cache
    $deviceStats = $pdo->query("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN is_uninstalled = 1 THEN 1 ELSE 0 END) as uninstalled
        FROM mpsm_cache_devices
    ")->fetch(PDO::FETCH_ASSOC);

    $metrics['totalDevices'] = (int)($deviceStats['total'] ?? 0) - (int)($deviceStats['uninstalled'] ?? 0);
    $metrics['uninstalledDevices'] = (int)($deviceStats['uninstalled'] ?? 0);

    // Total customers
    try {
        $customerCount = $pdo->query("
            SELECT COUNT(DISTINCT customer_code) as count
            FROM mpsm_cache_devices
            WHERE customer_code IS NOT NULL AND customer_code != ''
        ")->fetch(PDO::FETCH_ASSOC);
        $metrics['totalCustomers'] = (int)($customerCount['count'] ?? 0);
    } catch (Exception $e) {
        // customer_code column might not exist
    }

    // Duplicate IPs
    try {
        $dupIPs = $pdo->query("
            SELECT COUNT(*) as count
            FROM (
                SELECT JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) as ip
                FROM mpsm_cache_devices
                WHERE is_uninstalled = 0
                  AND JSON_EXTRACT(device_data, '$.IpAddress') IS NOT NULL
                GROUP BY JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress'))
                HAVING COUNT(*) > 1
            ) as dups
        ")->fetch(PDO::FETCH_ASSOC);
        $metrics['duplicateIPs'] = (int)($dupIPs['count'] ?? 0);
    } catch (Exception $e) {
        // Ignore JSON errors
    }

    // Panel messages
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
        } catch (Exception $e) {
            // Ignore
        }
    }

    // Cache health
    try {
        $cacheStats = $pdo->query("
            SELECT AVG(TIMESTAMPDIFF(SECOND, cached_at, NOW())) as avg_age_seconds
            FROM mpsm_cache_devices
            WHERE is_uninstalled = 0
        ")->fetch(PDO::FETCH_ASSOC);

        $metrics['cacheFreshnessAvg'] = round((float)($cacheStats['avg_age_seconds'] ?? 0));

        if (checkTableExists($pdo, 'mpsm_cache_device_drilldown')) {
            $drillDownCount = $pdo->query("SELECT COUNT(*) as count FROM mpsm_cache_device_drilldown")->fetch(PDO::FETCH_ASSOC);
            $metrics['drillDownCoverage'] = $metrics['totalDevices'] > 0
                ? round(((int)($drillDownCount['count'] ?? 0) / $metrics['totalDevices']) * 100, 1)
                : 0;
        }

        $freshnessScore = min(100, max(0, 100 - ($metrics['cacheFreshnessAvg'] / 3600 * 10)));
        $metrics['cacheHealthScore'] = round(($freshnessScore + $metrics['drillDownCoverage']) / 2, 1);
    } catch (Exception $e) {
        // Ignore
    }

    $metrics['_dataSource'] = 'cache';

    return $metrics;
}

function initializeMetrics() {
    return [
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
        'connectorHealthScore' => 95,
        'connectorsOffline' => 0,
        'cacheHealthScore' => 0,
        'cacheFreshnessAvg' => 0,
        'drillDownCoverage' => 0,
        'alertDefinitionCoverage' => 0,
        'unmappedAlertCodes' => 0,
        'topCustomersByDevices' => [],
        'topCustomersByAlerts' => []
    ];
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
- Hybrid implementation: uses cache if available, falls back to live API if empty
- Samples first 10 customers for quick live metrics (extrapolates to full base)
- Always returns data (never shows zeros if live API works)
- Indicates data source in response (_dataSource field)
2025-12-06 Codex
- Rebranded error logging from Executive to Dealer naming to match new summary endpoints.
2025-12-03 Codex
- Removed duplicate callMPSAPI helper and now use shared callMPSQuery() for live customer/dashboard fetches to avoid redeclaration fatals.
*/
