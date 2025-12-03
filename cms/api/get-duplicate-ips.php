<?php
/**
 * Duplicate IPs API - Comprehensive Analysis
 * Returns all devices with duplicate IP addresses, grouped by IP
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
    $cacheKey = 'duplicate-ips-analysis';
    $cacheTTL = 900; // 15 minutes

    // Try cache first unless force refresh
    if (!$forceRefresh) {
        $cached = cacheGet($cacheKey);
        if ($cached !== null) {
            $data = json_decode($cached, true);
            if ($data && isset($data['timestamp'])) {
                $age = time() - $data['timestamp'];
                if ($age < $cacheTTL) {
                    jsonSuccess([
                        'duplicates' => $data['duplicates'],
                        'summary' => $data['summary'],
                        'cached' => true,
                        'cache_age_seconds' => $age
                    ]);
                }
            }
        }
    }

    // Build fresh analysis
    $result = analyzeDuplicateIPs();

    // Cache result
    cacheStore($cacheKey, json_encode([
        'timestamp' => time(),
        'duplicates' => $result['duplicates'],
        'summary' => $result['summary']
    ]));

    jsonSuccess([
        'duplicates' => $result['duplicates'],
        'summary' => $result['summary'],
        'cached' => false,
        'cache_age_seconds' => 0
    ]);

} catch (Exception $e) {
    error_log("Duplicate IPs API Error: " . $e->getMessage());
    jsonError("Failed to analyze duplicate IPs: " . $e->getMessage());
}

function analyzeDuplicateIPs() {
    $dealerCode = DEFAULT_DEALER_CODE;

    // Fetch all devices with pagination
    $allDevices = [];
    $pageNumber = 1;
    $pageRows = 1000;
    $maxPages = 50;

    do {
        $pageDevices = callMPSQuery('Device/List', [
            'DealerCode' => $dealerCode,
            'FilterDealerCodes' => [$dealerCode],
            'PageNumber' => $pageNumber,
            'PageRows' => $pageRows,
            'SortColumn' => 'SerialNumber'
        ]);

        if (!$pageDevices || !is_array($pageDevices) || count($pageDevices) === 0) {
            break;
        }

        $allDevices = array_merge($allDevices, $pageDevices);
        $pageNumber++;

        if (count($pageDevices) < $pageRows) {
            break;
        }

    } while ($pageNumber <= $maxPages);

    // Group devices by IP address
    $ipGroups = [];
    $validDevices = [];
    $customerCodes = [];

    foreach ($allDevices as $device) {
        // Skip uninstalled devices
        if (!empty($device['Uninstall'])) {
            continue;
        }

        $ip = $device['IpAddress'] ?? null;

        // Skip invalid/empty IPs
        if (!$ip || $ip === '' || $ip === '0.0.0.0' || $ip === 'N/A') {
            continue;
        }

        // Track valid devices
        $validDevices[] = $device;

        // Track customer codes
        $customerCode = $device['CustomerCode'] ?? 'Unknown';
        $customerCodes[$customerCode] = true;

        // Group by IP
        if (!isset($ipGroups[$ip])) {
            $ipGroups[$ip] = [];
        }

        $ipGroups[$ip][] = [
            'serialNumber' => $device['SerialNumber'] ?? 'Unknown',
            'model' => $device['Model'] ?? 'Unknown',
            'customerCode' => $customerCode,
            'customerName' => $device['CustomerName'] ?? 'Unknown',
            'location' => $device['Location'] ?? 'Unknown',
            'department' => $device['Department'] ?? null,
            'assetNumber' => $device['AssetNumber'] ?? null,
            'install' => $device['Install'] ?? null,
            'lastContact' => $device['LastContact'] ?? null,
            'status' => determineDeviceStatus($device)
        ];
    }

    // Filter to only duplicate IPs (2+ devices)
    $duplicates = [];
    foreach ($ipGroups as $ip => $devices) {
        if (count($devices) > 1) {
            $duplicates[] = [
                'ipAddress' => $ip,
                'deviceCount' => count($devices),
                'devices' => $devices,
                'customerCount' => count(array_unique(array_column($devices, 'customerCode'))),
                'severity' => calculateSeverity(count($devices)),
                'affectedCustomers' => array_values(array_unique(array_column($devices, 'customerName')))
            ];
        }
    }

    // Sort by device count (most problematic first)
    usort($duplicates, function($a, $b) {
        return $b['deviceCount'] - $a['deviceCount'];
    });

    // Calculate summary statistics
    $summary = [
        'totalDuplicateIPs' => count($duplicates),
        'totalDevicesAffected' => array_sum(array_column($duplicates, 'deviceCount')),
        'totalValidDevices' => count($validDevices),
        'percentageAffected' => count($validDevices) > 0
            ? round((array_sum(array_column($duplicates, 'deviceCount')) / count($validDevices)) * 100, 1)
            : 0,
        'customersAffected' => count($customerCodes),
        'severityBreakdown' => [
            'critical' => count(array_filter($duplicates, fn($d) => $d['severity'] === 'critical')),
            'high' => count(array_filter($duplicates, fn($d) => $d['severity'] === 'high')),
            'medium' => count(array_filter($duplicates, fn($d) => $d['severity'] === 'medium')),
            'low' => count(array_filter($duplicates, fn($d) => $d['severity'] === 'low'))
        ],
        'topOffenders' => array_slice($duplicates, 0, 5) // Top 5 worst IPs
    ];

    return [
        'duplicates' => $duplicates,
        'summary' => $summary
    ];
}

function determineDeviceStatus($device) {
    $lastContact = $device['LastContact'] ?? null;
    if (!$lastContact) {
        return 'unknown';
    }

    $contactTime = strtotime($lastContact);
    $hoursSince = (time() - $contactTime) / 3600;

    if ($hoursSince <= 24) {
        return 'online';
    } elseif ($hoursSince <= 168) { // 7 days
        return 'recent';
    } else {
        return 'ghost';
    }
}

function calculateSeverity($deviceCount) {
    if ($deviceCount >= 10) {
        return 'critical';
    } elseif ($deviceCount >= 5) {
        return 'high';
    } elseif ($deviceCount >= 3) {
        return 'medium';
    } else {
        return 'low';
    }
}

/*
CHANGELOG
2025-12-03 Claude
- Initial implementation: Duplicate IP analysis API
- Fetches all devices via paginated Device/List
- Groups by IP address, filters to duplicates only
- Calculates severity based on device count
- Returns summary statistics and detailed device listings
- 15-minute cache for performance
*/
