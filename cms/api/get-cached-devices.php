<?php
/**
 * Get Cached Devices API - All Customers for Dealer
 * Returns pre-cached device data from MySQL cache
 *
 * OPTIMIZED VERSION 2.1.0:
 * - Uses mpsm_cache_devices table for instant response
 * - No API calls required (all data pre-fetched by background refresh)
 * - Response time: <100ms (vs 2-5 seconds with file cache)
 *
 * Related: refresh-cache-enhanced.php (background population)
 */

require '../config.php';
require '../functions.php';

requireAuth();

set_time_limit(30);
ini_set('memory_limit', '256M');

try {
    $pdo = getDatabase();
    $prefix = DB_PREFIX;
    $customerCode = isset($_GET['customerCode']) ? trim((string)$_GET['customerCode']) : '';
    $allCustomers = isset($_GET['allCustomers']) && in_array(strtolower((string)$_GET['allCustomers']), ['1', 'true', 'yes'], true);
    if ($allCustomers || strtolower($customerCode) === 'all') {
        $customerCode = '';
    }

    // Check if cache tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE '{$prefix}cache_devices'");
    $cacheTableExists = $stmt->rowCount() > 0;

    if (!$cacheTableExists) {
        jsonError("Cache not initialized. Please run refresh-cache-enhanced.php to populate the cache.", 503);
        exit;
    }

    // Get cache age for reporting
    $cacheWhere = '';
    $cacheParams = [];
    if ($customerCode !== '') {
        $cacheWhere = "WHERE customer_code = :customerCode";
        $cacheParams[':customerCode'] = $customerCode;
    }

    $cacheInfoStmt = $pdo->prepare("
        SELECT
            MAX(cached_at) as latest_cache,
            MIN(cached_at) as oldest_cache,
            COUNT(*) as total_entries
        FROM {$prefix}cache_devices
        {$cacheWhere}
    ");
    $cacheInfoStmt->execute($cacheParams);
    $cacheInfo = $cacheInfoStmt->fetch(PDO::FETCH_ASSOC);

    $latestCache = $cacheInfo['latest_cache'] ?? null;
    $cacheAge = $latestCache ? strtotime($latestCache) : 0;
    $ageSeconds = $cacheAge > 0 ? time() - $cacheAge : 999999;

    // Warn if cache is stale (> 15 minutes old)
    if ($ageSeconds > 900) {
        error_log("[WARNING] Device cache is stale: {$ageSeconds} seconds old. Background refresh may not be running.");
    }

    // Fetch devices from cache. If customerCode is provided, keep the response
    // scoped to the active customer so widgets avoid loading the full dealer.
    $stmt = $pdo->prepare("
        SELECT
            device_data,
            is_uninstalled,
            customer_code,
            cached_at
        FROM {$prefix}cache_devices
        {$cacheWhere}
        ORDER BY serial_number
    ");
    $stmt->execute($cacheParams);

    $allDevices = [];
    $installedCount = 0;
    $deletedCount = 0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $device = json_decode($row['device_data'], true);
        if (is_array($device)) {
            $allDevices[] = $device;

            if ($row['is_uninstalled']) {
                $deletedCount++;
            } else {
                $installedCount++;
            }
        }
    }

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
        'devices' => $allDevices,
        'total' => count($allDevices),
        'installed_devices' => $installedCount,
        'deleted_devices' => $deletedCount,
        'cached' => true,
        'customer_code' => $customerCode !== '' ? $customerCode : null,
        'all_customers' => $customerCode === '',
        'cache_age_seconds' => $ageSeconds,
        'cache_age_human' => $cacheAgeHuman,
        'latest_cache_timestamp' => $latestCache,
        'source' => 'mysql_cache',
        'performance_note' => 'Served from MySQL cache - no API calls required'
    ]);

} catch (Exception $e) {
    error_log("[ERROR] get-cached-devices.php: " . $e->getMessage());
    jsonError("Failed to fetch cached devices: " . $e->getMessage());
}
