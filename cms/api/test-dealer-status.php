<?php
/**
 * Dealer Dashboard Status Test
 * Checks cache health and API readiness (NO AUTH for testing)
 */

require '../config.php';
require '../functions.php';

header('Content-Type: application/json');

$status = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => []
];

// Test 1: Database connection
try {
    $pdo = getDatabase();
    $status['tests']['database'] = '✅ Connected';
} catch (Exception $e) {
    $status['tests']['database'] = '❌ Failed: ' . $e->getMessage();
}

// Test 2: Cache devices table exists and row count
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM mpsm_cache_devices WHERE is_uninstalled = 0");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $deviceCount = (int)$result['count'];
    $status['tests']['cache_devices'] = "✅ Table exists with {$deviceCount} devices";
    $status['device_count'] = $deviceCount;
} catch (Exception $e) {
    $status['tests']['cache_devices'] = '❌ Error: ' . $e->getMessage();
    $status['device_count'] = 0;
}

// Test 3: Check if hybrid API would use cache or live fallback
if ($status['device_count'] > 0) {
    $status['tests']['api_strategy'] = "✅ Will use CACHE (fast, {$status['device_count']} devices)";
} else {
    $status['tests']['api_strategy'] = '⚠️ Will use LIVE API FALLBACK (slower, samples 10 customers)';
}

// Test 4: Check dealer.php exists
if (file_exists('../dealer.php')) {
    $status['tests']['dealer_page'] = '✅ dealer.php exists';
} else {
    $status['tests']['dealer_page'] = '❌ dealer.php missing';
}

// Test 5: Check dealer.js exists
if (file_exists('../assets/dealer.js')) {
    $status['tests']['dealer_js'] = '✅ dealer.js exists';
} else {
    $status['tests']['dealer_js'] = '❌ dealer.js missing';
}

// Test 6: Check if get-dealer-summary.php is hybrid version
$summaryFile = __DIR__ . '/get-dealer-summary.php';
if (file_exists($summaryFile)) {
    $content = file_get_contents($summaryFile);
    if (strpos($content, 'Hybrid') !== false) {
        $status['tests']['api_version'] = '✅ Hybrid version deployed';
    } else {
        $status['tests']['api_version'] = '⚠️ V2 (database-only) version';
    }
} else {
    $status['tests']['api_version'] = '❌ API file missing';
}

// Test 7: Check customers table
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM mpsm_cache_customers");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $customerCount = (int)$result['count'];
    $status['tests']['cache_customers'] = "✅ {$customerCount} customers cached";
} catch (Exception $e) {
    $status['tests']['cache_customers'] = '⚠️ Table may not exist';
}

// Summary
$allPassed = true;
foreach ($status['tests'] as $test => $result) {
    if (strpos($result, '❌') !== false) {
        $allPassed = false;
        break;
    }
}

$status['overall'] = $allPassed ? '✅ ALL TESTS PASSED' : '⚠️ SOME TESTS FAILED';
$status['recommendation'] = $status['device_count'] === 0
    ? 'Cache is empty. Dashboard will use live API (slower). Run refresh-cache-enhanced.php to populate cache.'
    : 'Cache is populated. Dashboard should load quickly from cache.';

echo json_encode($status, JSON_PRETTY_PRINT);
?>
