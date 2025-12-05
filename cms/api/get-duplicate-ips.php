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
$summaryOnly = isset($_GET['summaryOnly']) && $_GET['summaryOnly'] === '1';

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
                    $payload = [
                        'summary' => $data['summary'],
                        'cached' => true,
                        'cache_age_seconds' => $age
                    ];
                    if (!$summaryOnly) {
                        $payload['duplicates'] = $data['duplicates'];
                    }
                    jsonSuccess($payload);
                }
            }
        }
    }

    // Build fresh analysis (cache-first, live fallback)
    $result = analyzeDuplicateIPs($forceRefresh);

    // Cache result
    cacheStore($cacheKey, json_encode([
        'timestamp' => time(),
        'duplicates' => $result['duplicates'],
        'summary' => $result['summary']
    ]));

    $payload = [
        'summary' => $result['summary'],
        'cached' => false,
        'cache_age_seconds' => $result['summary']['cache_age_seconds']
    ];

    if (!$summaryOnly) {
        $payload['duplicates'] = $result['duplicates'];
    }

    jsonSuccess($payload);

} catch (Exception $e) {
    error_log("Duplicate IPs API Error: " . $e->getMessage());
    jsonError("Failed to analyze duplicate IPs: " . $e->getMessage());
}

function analyzeDuplicateIPs(bool $forceRefresh = false) {
    $cacheAgeSeconds = null;
    $source = 'cache';

    // Prefer cache table for dealership-wide accuracy and speed
    $devices = fetchDevicesFromCache($cacheAgeSeconds);
    error_log("[DUP-IP-DIAG] Cache returned " . count($devices) . " devices");

    // Force or fallback to live API if cache empty/stale
    if ($forceRefresh || empty($devices)) {
        error_log("[DUP-IP-DIAG] Falling back to live vendor API (force={$forceRefresh}, empty=" . (empty($devices) ? 'true' : 'false') . ")");
        $source = 'live-vendor-api';
        $devices = fetchDevicesViaQuery();
        error_log("[DUP-IP-DIAG] live-vendor-api returned " . count($devices) . " devices");
    }

    if (empty($devices)) {
        throw new Exception('No devices returned from cache or API');
    }

    error_log("[DUP-IP-DIAG] Building report from {$source} with " . count($devices) . " devices");
    $report = buildDuplicateReport($devices);
    $report['summary']['source'] = $source;
    $report['summary']['cache_age_seconds'] = $cacheAgeSeconds;

    return $report;
}

function fetchDevicesFromCache(?int &$cacheAgeSeconds = null): array {
    $pdo = getDatabase();

    // Check cache presence
    try {
        $ageRow = $pdo->query("SELECT TIMESTAMPDIFF(SECOND, MAX(cached_at), NOW()) AS newest_age FROM mpsm_cache_devices")->fetch(PDO::FETCH_ASSOC);
        $cacheAgeSeconds = isset($ageRow['newest_age']) ? (int)$ageRow['newest_age'] : null;
    } catch (Exception $e) {
        $cacheAgeSeconds = null;
    }

    try {
        $stmt = $pdo->query("
            SELECT serial_number, customer_code, device_data, cached_at
            FROM mpsm_cache_devices
            WHERE is_uninstalled = 0
              AND JSON_EXTRACT(device_data, '$.IpAddress') IS NOT NULL
              AND JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) NOT IN ('', '0.0.0.0', 'N/A')
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }

    // If cache has fewer than 1000 devices, it's incomplete - return empty to trigger live API
    if (count($rows) < 1000) {
        return [];
    }

    $devices = [];
    foreach ($rows as $row) {
        $data = json_decode($row['device_data'] ?? '', true) ?: [];
        $devices[] = [
            'SerialNumber' => $data['SerialNumber'] ?? $row['serial_number'],
            'CustomerCode' => $row['customer_code'] ?? ($data['CustomerCode'] ?? 'UNKNOWN'),
            'CustomerName' => $data['CustomerName'] ?? $data['CustomerDescription'] ?? ($row['customer_code'] ?? 'UNKNOWN'),
            'IpAddress' => $data['IpAddress'] ?? null,
            'Model' => $data['Model'] ?? ($data['Product']['Model'] ?? 'Unknown'),
            'Location' => $data['Location'] ?? ($data['OfficeName'] ?? ($data['Department'] ?? 'N/A')),
            'Department' => $data['Department'] ?? null,
            'AssetNumber' => $data['AssetNumber'] ?? null,
            'InstallStatus' => $data['InstallStatus'] ?? null,
            'Install' => $data['Install'] ?? null,
            'LastContact' => $data['LastContact'] ?? null
        ];
    }

    return $devices;
}

function fetchDevicesViaQuery(): array {
    $dealerId = DEFAULT_DEALER_ID;
    $dealerCode = DEFAULT_DEALER_CODE;
    $pageRows = 100;   // vendor hard-limit
    $maxPages = 600;   // ~60k devices
    $maxEmptyPages = 3;

    $allDevices = [];
    $seenSerials = [];
    $consecutiveEmptyPages = 0;
    $expectedRows = null;
    $expectedPages = null;

    error_log("[DUP-IP-DIAG] fetchDevicesViaQuery starting with direct vendor API calls");
    error_log("[DUP-IP-DIAG] FilterDealerId: {$dealerId}, FilterDealerCodes: [{$dealerCode}]");

    // Fetch installed devices using Device/List with dealer filters
    $installedParams = [
        'FilterDealerId' => $dealerId,
        'FilterDealerCodes' => [$dealerCode],
        'PageRows' => $pageRows,
        'SortColumn' => 'Id',
        'SortOrder' => 0,
    ];

    for ($page = 1; $page <= $maxPages; $page++) {
        $params = $installedParams;
        $params['PageNumber'] = $page;

        // Filter out null values to avoid API rejection
        $params = array_filter($params, function($value) {
            return $value !== null;
        });

        try {
            $response = callMpsApiWithRetry('Device/List', $params);
        } catch (Exception $e) {
            error_log("[DUP-IP-DIAG] Device/List page {$page} API error: " . $e->getMessage());
            break;
        }

        if (!$response || !is_array($response)) {
            error_log("[DUP-IP-DIAG] Device/List page {$page} returned empty/invalid response");
            $consecutiveEmptyPages++;
            if ($consecutiveEmptyPages >= $maxEmptyPages) {
                break;
            }
            continue;
        }

        $pageDevices = extractDevicesFromResponse($response);
        $deviceCount = count($pageDevices);

        if ($deviceCount === 0) {
            $consecutiveEmptyPages++;
            error_log("[DUP-IP-DIAG] Device/List page {$page}: EMPTY (streak {$consecutiveEmptyPages}/{$maxEmptyPages})");
            if ($consecutiveEmptyPages >= $maxEmptyPages) {
                error_log("[DUP-IP-DIAG] Stopping after {$maxEmptyPages} consecutive empty pages");
                break;
            }
            continue;
        }

        $consecutiveEmptyPages = 0;
        $uniqueAdded = 0;
        $duplicatesThisPage = 0;

        foreach ($pageDevices as $device) {
            $serial = $device['SerialNumber'] ?? $device['serialNumber'] ?? null;
            if ($serial === null) {
                continue;
            }

            if (isset($seenSerials[$serial])) {
                $duplicatesThisPage++;
                continue;
            }

            $seenSerials[$serial] = true;
            $allDevices[] = $device;
            $uniqueAdded++;
        }

        if ($page <= 5 || $page % 10 === 0) {
            error_log("[DUP-IP-DIAG] Device/List page {$page}: {$deviceCount} fetched ({$uniqueAdded} new, {$duplicatesThisPage} dupes). Total unique: " . count($allDevices));
        }

        // Detect expected pages from TotalRows
        if ($expectedPages === null && isset($response['TotalRows'])) {
            $expectedRows = (int)$response['TotalRows'];
            $expectedPages = max(1, (int)ceil($expectedRows / $pageRows));
            error_log("[DUP-IP-DIAG] Detected TotalRows={$expectedRows}, expectedPages={$expectedPages}");
        }

        // Stop if we've reached expected page count
        if ($expectedPages !== null && $page >= $expectedPages) {
            error_log("[DUP-IP-DIAG] Stopping at page {$page} (reached expected pages)");
            break;
        }

        // Stop if partial page (indicates last page)
        if ($deviceCount < $pageRows) {
            error_log("[DUP-IP-DIAG] Partial page detected ({$deviceCount} devices), stopping pagination");
            break;
        }
    }

    $installedCount = count($allDevices);
    error_log("[DUP-IP-DIAG] Installed devices complete: {$installedCount} unique devices");

    // Fetch deleted devices using Device/Deleted/ListByDealer
    $deletedParams = [
        'DealerCode' => $dealerCode,
        'PageRows' => 200,
        'SortColumn' => 'AssetNumber',
        'SortOrder' => 'Asc'
    ];

    $consecutiveEmptyPages = 0;
    $maxDeletedPages = 20;

    error_log("[DUP-IP-DIAG] Fetching deleted devices");

    for ($page = 1; $page <= $maxDeletedPages; $page++) {
        $params = $deletedParams;
        $params['PageNumber'] = $page;

        try {
            $response = callMpsApiWithRetry('Device/Deleted/ListByDealer', $params);
        } catch (Exception $e) {
            error_log("[DUP-IP-DIAG] Device/Deleted page {$page} API error: " . $e->getMessage());
            break;
        }

        if (!$response || !is_array($response)) {
            error_log("[DUP-IP-DIAG] Device/Deleted page {$page} returned empty/invalid response");
            break;
        }

        $pageDevices = extractDevicesFromResponse($response);
        $deviceCount = count($pageDevices);

        if ($deviceCount === 0) {
            error_log("[DUP-IP-DIAG] Device/Deleted page {$page}: EMPTY, stopping deleted pagination");
            break;
        }

        $uniqueAdded = 0;
        $duplicatesThisPage = 0;

        foreach ($pageDevices as $device) {
            $serial = $device['SerialNumber'] ?? $device['serialNumber'] ?? null;
            if ($serial === null) {
                continue;
            }

            if (isset($seenSerials[$serial])) {
                $duplicatesThisPage++;
                continue;
            }

            $seenSerials[$serial] = true;
            $device['IsUninstalled'] = true;
            $allDevices[] = $device;
            $uniqueAdded++;
        }

        error_log("[DUP-IP-DIAG] Device/Deleted page {$page}: {$deviceCount} fetched ({$uniqueAdded} new, {$duplicatesThisPage} dupes)");

        // Stop if partial page
        if ($deviceCount < 200) {
            error_log("[DUP-IP-DIAG] Partial deleted page detected ({$deviceCount} devices), stopping pagination");
            break;
        }
    }

    $deletedCount = count($allDevices) - $installedCount;
    error_log("[DUP-IP-DIAG] Deleted devices complete: {$deletedCount} unique deleted devices");
    error_log("[DUP-IP-DIAG] fetchDevicesViaQuery complete - total devices: " . count($allDevices) . " ({$installedCount} installed + {$deletedCount} deleted)");

    return $allDevices;
}

/**
 * Call MPS API with retry logic and rate limit handling
 */
function callMpsApiWithRetry(string $action, array $params = [], int $maxAttempts = 5): ?array {
    $baseDelaySeconds = 2;
    $lastError = null;

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        try {
            return callMPSAPI($action, $params);
        } catch (Exception $e) {
            $lastError = $e->getMessage();
            $isRateLimit = stripos($lastError, '429') !== false || stripos($lastError, 'rate') !== false;
            $delay = min(60, (int)($baseDelaySeconds * $attempt));

            if ($isRateLimit) {
                error_log("[DUP-IP-DIAG] Rate limit on {$action} (attempt {$attempt}/{$maxAttempts}): {$lastError}. Sleeping {$delay}s");
            } else {
                error_log("[DUP-IP-DIAG] API error on {$action} (attempt {$attempt}/{$maxAttempts}): {$lastError}. Sleeping {$delay}s");
            }

            if ($attempt === $maxAttempts) {
                throw $e;
            }

            sleep($delay);
        }
    }

    error_log("[DUP-IP-DIAG] API {$action} failed after {$maxAttempts} attempts: {$lastError}");
    return null;
}


function buildDuplicateReport(array $allDevices): array {
    // CRITICAL: Group by CUSTOMER first, then by IP within each customer
    // Duplicate IPs are only problematic WITHIN the same customer
    $customerGroups = [];
    $validDevices = [];
    $seen = [];

    foreach ($allDevices as $device) {
        // Skip uninstalled devices
        $installStatus = strtolower($device['InstallStatus'] ?? '');
        if (!empty($device['Uninstall']) || $installStatus === 'uninstalled') {
            continue;
        }

        $ip = $device['IpAddress'] ?? null;

        // Skip invalid/empty IPs
        if (!$ip || $ip === '' || $ip === '0.0.0.0' || $ip === 'N/A') {
            continue;
        }

        $customerCode = $device['CustomerCode'] ?? 'Unknown';
        $customerName = $device['CustomerName'] ?? 'Unknown';
        $serial = $device['SerialNumber'] ?? 'Unknown';
        $dedupeKey = "{$customerCode}|{$serial}|{$ip}";
        if (isset($seen[$dedupeKey])) {
            continue; // de-dup same device repeated in feed
        }
        $seen[$dedupeKey] = true;

        // Track valid devices count
        $validDevices[] = true;

        // Group by customer, then by IP
        if (!isset($customerGroups[$customerCode])) {
            $customerGroups[$customerCode] = [
                'customerCode' => $customerCode,
                'customerName' => $customerName,
                'ipGroups' => []
            ];
        }

        if (!isset($customerGroups[$customerCode]['ipGroups'][$ip])) {
            $customerGroups[$customerCode]['ipGroups'][$ip] = [];
        }

        $customerGroups[$customerCode]['ipGroups'][$ip][] = [
            'serialNumber' => $serial,
            'model' => $device['Model'] ?? 'Unknown',
            'customerCode' => $customerCode,
            'customerName' => $customerName,
            'location' => $device['Location'] ?? 'Unknown',
            'department' => $device['Department'] ?? null,
            'assetNumber' => $device['AssetNumber'] ?? null,
            'install' => $device['Install'] ?? null,
            'lastContact' => $device['LastContact'] ?? null,
            'status' => determineDeviceStatusFromContact($device['LastContact'] ?? null)
        ];
    }

    // Find duplicates WITHIN each customer
    $duplicates = [];
    $customersAffected = 0;
    $totalDevicesAffected = 0;

    foreach ($customerGroups as $customerData) {
        $customerHasDuplicates = false;

        foreach ($customerData['ipGroups'] as $ip => $devices) {
            // Duplicate only if 2+ devices share IP WITHIN this customer
            if (count($devices) > 1) {
                $customerHasDuplicates = true;
                $totalDevicesAffected += count($devices);

                $duplicates[] = [
                    'ipAddress' => $ip,
                    'deviceCount' => count($devices),
                    'devices' => $devices,
                    'customerCode' => $customerData['customerCode'],
                    'customerName' => $customerData['customerName'],
                    'customerCount' => 1,
                    'severity' => calculateSeverity(count($devices)),
                    'affectedCustomers' => [$customerData['customerName']] // Always single customer
                ];
            }
        }

        if ($customerHasDuplicates) {
            $customersAffected++;
        }
    }

    // Sort by device count (most problematic first)
    usort($duplicates, function($a, $b) {
        return $b['deviceCount'] - $a['deviceCount'];
    });

    // Calculate summary statistics
    $summary = [
        'totalDuplicateIPs' => count($duplicates),
        'totalDevicesAffected' => $totalDevicesAffected,
        'totalValidDevices' => count($validDevices),
        'percentageAffected' => count($validDevices) > 0
            ? round(($totalDevicesAffected / count($validDevices)) * 100, 1)
            : 0,
        'customersAffected' => $customersAffected,
        'severityBreakdown' => [
            'critical' => count(array_filter($duplicates, fn($d) => $d['severity'] === 'critical')),
            'high' => count(array_filter($duplicates, fn($d) => $d['severity'] === 'high')),
            'medium' => count(array_filter($duplicates, fn($d) => $d['severity'] === 'medium')),
            'low' => count(array_filter($duplicates, fn($d) => $d['severity'] === 'low'))
        ],
        'topOffenders' => array_slice($duplicates, 0, 5), // Top 5 worst IPs
        'source' => null,
        'cache_age_seconds' => null
    ];

    return [
        'duplicates' => $duplicates,
        'summary' => $summary
    ];
}

function determineDeviceStatusFromContact(?string $lastContact): string {
    if (!$lastContact) {
        return 'unknown';
    }

    $contactTime = strtotime($lastContact);
    if ($contactTime === false) {
        return 'unknown';
    }

    $hoursSince = (time() - $contactTime) / 3600;

    if ($hoursSince <= 24) {
        return 'online';
    } elseif ($hoursSince <= 168) { // 7 days
        return 'recent';
    }

    return 'ghost';
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
- HOTFIX: Corrected PageRows to 100 (vendor hard-limit, was 1000 causing incomplete data)
- CRITICAL FIX: Changed logic to find duplicates WITHIN each customer only
  * Multiple customers can have same IP (e.g., 192.168.1.1) - this is normal
  * Only flag as duplicate when 2+ devices share IP at SAME customer location
  * Grouped by customer first, then by IP within each customer
2025-12-03 Claude
- Added cache completeness check: if cache has <1000 devices, fall back to live API
- This ensures all customers and all devices are analyzed, not just incomplete cache subset
- Matches same fix applied to device-age-report.php to resolve systemic cache issue
2025-12-03 Codex
- Switched to cache-first analysis with live query/API fallback, added cache age/source metadata, and kept dealership-wide scope while filtering out uninstalled/invalid-IP devices.
- Normalized Device/List parsing to the mps-api/query shape and hardened pagination to vendor limits.
- Added customerCount field for UI and improved status calculation based on LastContact.
2025-12-04 Codex
- Only apply DealerCode/FilterDealerCodes when a real code is configured (not TEST) so live queries include all customers and devices, strip null params before calling Device/List to avoid upstream rejection, and drive pagination using meta.total_rows from mps-api/query.
2025-12-04 Claude
- CRITICAL FIX: Replaced broken mps-api/query endpoint with direct vendor API calls
  * Removed callMpsQueryWithMeta() function (was calling broken mps-api/query)
  * Removed fetchDevicesViaApi() and normalizeDeviceListResponse() functions (now redundant)
  * Rewrote fetchDevicesViaQuery() to use callMPSAPI() for direct vendor calls
  * Follows exact pagination pattern from refresh-cache-enhanced.php (lines 361-536)
  * Fetches installed devices via Device/List with FilterDealerId + FilterDealerCodes
  * Fetches deleted devices via Device/Deleted/ListByDealer
  * Uses extractDevicesFromResponse() helper from cms/functions.php
  * Implements proper pagination: PageRows=100, TotalRows detection, 3 empty page limit
  * Deduplicates by serial number across all pages
  * Added callMpsApiWithRetry() with exponential backoff and rate limit handling
  * Enhanced diagnostic logging with [DUP-IP-DIAG] prefix
  * Expected outcome: 5000+ devices instead of 100
*/
