<?php
/**
 * Test Dealer Summary API - Debug endpoint
 * Tests both get-dealer-summary.php and get-dealer-summary-hybrid.php
 */

require '../config.php';
require '../functions.php';

// Allow access without auth for testing
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Dealer Summary API Debug</h1>";
echo "<style>body{font-family:monospace;padding:20px;} pre{background:#f5f5f5;padding:10px;overflow:auto;} h2{color:#1f6feb;}</style>";

// Test 1: Check cache table
echo "<h2>Test 1: Cache Table Status</h2>";
try {
    $pdo = getDatabase();
    $cacheTableExists = checkTableExists($pdo, 'mpsm_cache_devices');
    echo "<p>Cache table exists: <strong>" . ($cacheTableExists ? 'YES' : 'NO') . "</strong></p>";

    if ($cacheTableExists) {
        $stats = $pdo->query("SELECT
            COUNT(*) as total,
            SUM(CASE WHEN is_uninstalled = 0 THEN 1 ELSE 0 END) as installed,
            SUM(CASE WHEN is_uninstalled = 1 THEN 1 ELSE 0 END) as uninstalled
            FROM mpsm_cache_devices")->fetch(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($stats, true) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 2: Call get-dealer-summary.php
echo "<h2>Test 2: get-dealer-summary.php Response</h2>";
try {
    $url = 'https://mpsm.resolutionsbydesign.us/cms/api/get-dealer-summary.php';

    // Use internal request with session
    session_start();
    $_SESSION['user_id'] = 1; // Fake session for test

    ob_start();
    include 'get-dealer-summary.php';
    $response = ob_get_clean();

    echo "<p>Response:</p><pre>" . htmlspecialchars($response) . "</pre>";

    $data = json_decode($response, true);
    if ($data && isset($data['summary'])) {
        echo "<p>Summary data:</p><pre>" . print_r($data['summary'], true) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 3: Call get-dealer-summary-hybrid.php
echo "<h2>Test 3: get-dealer-summary-hybrid.php Response</h2>";
try {
    ob_start();
    include 'get-dealer-summary-hybrid.php';
    $response = ob_get_clean();

    echo "<p>Response:</p><pre>" . htmlspecialchars($response) . "</pre>";

    $data = json_decode($response, true);
    if ($data && isset($data['summary'])) {
        echo "<p>Summary data:</p><pre>" . print_r($data['summary'], true) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 4: Check functions.php for required functions
echo "<h2>Test 4: Required Functions Check</h2>";
$requiredFunctions = ['callMPSQuery', 'checkTableExists', 'cacheGet', 'cacheStore', 'getDatabase'];
foreach ($requiredFunctions as $func) {
    $exists = function_exists($func);
    echo "<p>$func: <strong>" . ($exists ? 'FOUND' : '<span style="color:red;">MISSING</span>') . "</strong></p>";
}

echo "<h2>Test Complete</h2>";
echo "<p><a href='test-dealer-summary.php'>Refresh Test</a> | <a href='../dealer.php'>Back to Dashboard</a></p>";
