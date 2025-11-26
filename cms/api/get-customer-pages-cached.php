<?php
/**
 * Get Cached Customer Pages/Volume API
 * Returns pre-cached page volume data from MySQL cache
 *
 * Phase 2 Cache - Version 1.0.0:
 * - Uses mpsm_cache_page_volume table for instant response
 * - No API calls required (data pre-fetched by background refresh)
 * - Response time: <50ms (vs 2-5 seconds with live API)
 *
 * Related: refresh-cache-enhanced.php (background population)
 */

require '../config.php';
require '../functions.php';

requireAuth();

set_time_limit(30);

$customerCode = $_GET['customerCode'] ?? null;

if (!$customerCode) {
    jsonError("customerCode is required");
    exit;
}

try {
    $pdo = getDatabase();
    $prefix = DB_PREFIX;

    // Check if cache table exists
    $stmt = $pdo->query("SHOW TABLES LIKE '{$prefix}cache_page_volume'");
    $cacheTableExists = $stmt->rowCount() > 0;

    if (!$cacheTableExists) {
        jsonError("Page volume cache not initialized. Please run init-phase2-cache-tables.php first.", 503);
        exit;
    }

    // Fetch page volume data from cache
    $stmt = $pdo->prepare("
        SELECT
            monthly_mono_managed,
            monthly_color_managed,
            monthly_mono_unmanaged,
            monthly_color_unmanaged,
            pages_data,
            cached_at
        FROM {$prefix}cache_page_volume
        WHERE customer_code = :customerCode
        LIMIT 1
    ");
    $stmt->execute([':customerCode' => $customerCode]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        jsonError("No cached page volume data for customer {$customerCode}. Cache may still be populating.", 404);
        exit;
    }

    $cachedAt = $row['cached_at'];
    $cacheAge = strtotime($cachedAt);
    $ageSeconds = $cacheAge > 0 ? time() - $cacheAge : 999999;

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

    // Build response matching get-customer-pages.php structure
    $pages = [
        'MonthlyMonoManaged' => (int)$row['monthly_mono_managed'],
        'MonthlyColorManaged' => (int)$row['monthly_color_managed'],
        'MonthlyMonoUnManaged' => (int)$row['monthly_mono_unmanaged'],
        'MonthlyColorUnManaged' => (int)$row['monthly_color_unmanaged']
    ];

    // Include full pages_data if available
    if ($row['pages_data']) {
        $fullData = json_decode($row['pages_data'], true);
        if (is_array($fullData)) {
            $pages = array_merge($pages, $fullData);
        }
    }

    jsonSuccess([
        'pages' => $pages,
        'cached' => true,
        'cache_age_seconds' => $ageSeconds,
        'cache_age_human' => $cacheAgeHuman,
        'cached_at' => $cachedAt,
        'source' => 'mysql_cache',
        'performance_note' => 'Served from MySQL cache - no API calls required'
    ]);

} catch (Exception $e) {
    error_log("[ERROR] get-customer-pages-cached.php: " . $e->getMessage());
    jsonError("Failed to fetch cached page volume: " . $e->getMessage());
}
