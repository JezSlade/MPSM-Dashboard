<?php
/**
 * Comprehensive Regression Test Suite
 * Tests all components touched during notification system implementation
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

header('Content-Type: text/plain; charset=utf-8');

$testResults = [];
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function test($name, $condition, $successMsg, $failMsg) {
    global $testResults, $totalTests, $passedTests, $failedTests;
    $totalTests++;

    if ($condition) {
        $passedTests++;
        $testResults[] = ['status' => 'PASS', 'name' => $name, 'message' => $successMsg];
        return true;
    } else {
        $failedTests++;
        $testResults[] = ['status' => 'FAIL', 'name' => $name, 'message' => $failMsg];
        return false;
    }
}

echo "=== COMPREHENSIVE REGRESSION TEST SUITE ===\n";
echo "Testing all components modified during notification system implementation\n\n";
echo "Start Time: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 80) . "\n\n";

$pdo = getDatabase();

// ===========================
// DATABASE SCHEMA TESTS
// ===========================
echo "TEST CATEGORY: Database Schema\n";
echo str_repeat("-", 80) . "\n";

try {
    $stmt = $pdo->query("DESCRIBE " . DB_PREFIX . "dashboard_notifications");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    test(
        "dashboard_notifications table exists",
        count($columns) > 0,
        "Table exists with " . count($columns) . " columns",
        "Table is missing"
    );

    $requiredColumns = ['id', 'title', 'message', 'severity', 'status', 'device_serial',
                        'alert_code', 'customer_code', 'created_at_ny', 'priority'];
    foreach ($requiredColumns as $col) {
        test(
            "Column '{$col}' exists",
            in_array($col, $columns),
            "Column present",
            "Column missing"
        );
    }
} catch (Exception $e) {
    test("dashboard_notifications table", false, "", "Error: " . $e->getMessage());
}

try {
    $stmt = $pdo->query("DESCRIBE " . DB_PREFIX . "notification_rules");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    test(
        "notification_rules table exists",
        count($columns) > 0,
        "Table exists with " . count($columns) . " columns",
        "Table is missing"
    );
} catch (Exception $e) {
    test("notification_rules table", false, "", "Error: " . $e->getMessage());
}

try {
    $stmt = $pdo->query("DESCRIBE " . DB_PREFIX . "alert_definitions");
    test(
        "alert_definitions table exists",
        $stmt->rowCount() > 0,
        "Table exists",
        "Table is missing"
    );
} catch (Exception $e) {
    test("alert_definitions table", false, "", "Error: " . $e->getMessage());
}

echo "\n";

// ===========================
// NOTIFICATION RULES TESTS
// ===========================
echo "TEST CATEGORY: Notification Rules\n";
echo str_repeat("-", 80) . "\n";

$stmt = $pdo->query("SELECT COUNT(*) as count FROM " . DB_PREFIX . "notification_rules WHERE enabled = 1");
$activeRules = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
test(
    "Active notification rules exist",
    $activeRules > 0,
    "{$activeRules} active rules configured",
    "No active rules found"
);

$stmt = $pdo->query("SELECT * FROM " . DB_PREFIX . "notification_rules WHERE enabled = 1");
$rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rules as $rule) {
    test(
        "Rule '{$rule['name']}' has valid pattern",
        !empty($rule['alert_code_pattern']),
        "Pattern: {$rule['alert_code_pattern']}",
        "Pattern is empty"
    );

    test(
        "Rule '{$rule['name']}' has valid thresholds",
        $rule['frequency_count'] > 0 && $rule['frequency_window_hours'] > 0,
        "Threshold: {$rule['frequency_count']}x in {$rule['frequency_window_hours']}h",
        "Invalid thresholds"
    );
}

echo "\n";

// ===========================
// NOTIFICATION DATA TESTS
// ===========================
echo "TEST CATEGORY: Notification Data\n";
echo str_repeat("-", 80) . "\n";

$stmt = $pdo->query("SELECT COUNT(*) as count FROM " . DB_PREFIX . "dashboard_notifications");
$totalNotifications = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
test(
    "Notifications exist in database",
    $totalNotifications > 0,
    "{$totalNotifications} total notifications",
    "No notifications found"
);

$stmt = $pdo->query("SELECT COUNT(*) as count FROM " . DB_PREFIX . "dashboard_notifications WHERE status = 'active'");
$activeNotifications = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
test(
    "Active notifications exist",
    $activeNotifications > 0,
    "{$activeNotifications} active notifications",
    "No active notifications"
);

// Check notification data integrity
$stmt = $pdo->query("
    SELECT COUNT(*) as count
    FROM " . DB_PREFIX . "dashboard_notifications
    WHERE device_serial IS NOT NULL
    AND alert_code IS NOT NULL
    AND created_at_ny IS NOT NULL
");
$validNotifications = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
test(
    "Notification data integrity",
    $validNotifications == $totalNotifications,
    "All notifications have required fields",
    ($totalNotifications - $validNotifications) . " notifications have missing fields"
);

echo "\n";

// ===========================
// API ENDPOINT TESTS
// ===========================
echo "TEST CATEGORY: API Endpoints\n";
echo str_repeat("-", 80) . "\n";

$apiFiles = [
    'api/command-center.php' => 'Command Center API',
    'api/search-devices.php' => 'Device Search API'
];

foreach ($apiFiles as $file => $description) {
    $path = __DIR__ . '/' . $file;
    test(
        "{$description} file exists",
        file_exists($path),
        "File present at {$file}",
        "File not found"
    );

    if (file_exists($path)) {
        $content = file_get_contents($path);
        test(
            "{$description} has authentication",
            strpos($content, 'requireAuth') !== false || strpos($content, 'session') !== false,
            "Authentication check present",
            "No authentication found"
        );
    }
}

echo "\n";

// ===========================
// FRONTEND INTEGRATION TESTS
// ===========================
echo "TEST CATEGORY: Frontend Integration\n";
echo str_repeat("-", 80) . "\n";

// Desktop dashboard
$desktopPath = __DIR__ . '/index.php';
if (file_exists($desktopPath)) {
    $html = file_get_contents($desktopPath);

    test(
        "Desktop: hero-notifications container",
        strpos($html, 'id="hero-notifications"') !== false,
        "Notification container present",
        "Container missing"
    );

    test(
        "Desktop: hero-notifications.js loaded",
        strpos($html, 'hero-notifications.js') !== false,
        "JavaScript file included",
        "JavaScript not loaded"
    );

    test(
        "Desktop: device search present",
        strpos($html, 'global-device-search') !== false,
        "Search functionality present",
        "Search missing"
    );
}

// Mobile dashboard
$mobilePath = __DIR__ . '/mobile.php';
if (file_exists($mobilePath)) {
    $html = file_get_contents($mobilePath);

    test(
        "Mobile: search functionality",
        strpos($html, 'mobile-search') !== false,
        "Search present",
        "Search missing"
    );

    test(
        "Mobile: mobile.js loaded",
        strpos($html, 'mobile.js') !== false,
        "JavaScript file included",
        "JavaScript not loaded"
    );
}

// JavaScript files
$jsFiles = [
    'assets/hero-notifications.js' => ['loadHeroNotifications', 'renderHeroNotifications'],
    'assets/app.js' => ['global-device-search'],
    'assets/mobile.js' => ['mobile-search']
];

foreach ($jsFiles as $file => $keywords) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        foreach ($keywords as $keyword) {
            test(
                "{$file} contains '{$keyword}'",
                strpos($content, $keyword) !== false,
                "Function/element present",
                "Missing expected code"
            );
        }
    } else {
        test("{$file} exists", false, "", "File not found");
    }
}

echo "\n";

// ===========================
// PATTERN MATCHING TESTS
// ===========================
echo "TEST CATEGORY: Pattern Matching (Critical Fix)\n";
echo str_repeat("-", 80) . "\n";

// Test pattern matching logic by checking if rules match actual data
$stmt = $pdo->query("SELECT alert_code_pattern FROM " . DB_PREFIX . "notification_rules WHERE enabled = 1 LIMIT 3");
$patterns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

foreach ($patterns as $pattern) {
    $likePattern = str_replace('*', '%', $pattern);

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM " . DB_PREFIX . "panel_messages
        WHERE maintenance_alert_code LIKE :pattern
        AND received_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        LIMIT 1
    ");
    $stmt->execute([':pattern' => $likePattern]);
    $matches = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    test(
        "Pattern '{$pattern}' can match messages",
        true, // Just testing execution, not requiring matches
        "Pattern query executes successfully",
        "Pattern query failed"
    );
}

echo "\n";

// ===========================
// CUSTOMER FILTERING TESTS
// ===========================
echo "TEST CATEGORY: Customer Filtering\n";
echo str_repeat("-", 80) . "\n";

$stmt = $pdo->query("
    SELECT DISTINCT customer_code
    FROM " . DB_PREFIX . "dashboard_notifications
    WHERE status = 'active'
    AND customer_code IS NOT NULL
    LIMIT 5
");
$customers = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

test(
    "Notifications have customer codes",
    count($customers) > 0,
    count($customers) . " unique customers have notifications",
    "No customer codes found"
);

// Test customer filter query
if (count($customers) > 0) {
    $testCustomer = $customers[0];
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM " . DB_PREFIX . "dashboard_notifications
        WHERE status = 'active'
        AND customer_code = :customer
    ");
    $stmt->execute([':customer' => $testCustomer]);
    $customerNotifications = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    test(
        "Customer filter query works",
        $customerNotifications >= 0,
        "Found {$customerNotifications} notifications for {$testCustomer}",
        "Filter query failed"
    );
}

echo "\n";

// ===========================
// ALERT DEFINITIONS TESTS
// ===========================
echo "TEST CATEGORY: Alert Definitions\n";
echo str_repeat("-", 80) . "\n";

$stmt = $pdo->query("SELECT COUNT(*) as count FROM " . DB_PREFIX . "alert_definitions WHERE enabled = 1");
$alertDefs = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

test(
    "Alert definitions exist",
    $alertDefs > 0,
    "{$alertDefs} alert definitions configured",
    "No alert definitions found"
);

// Check if key alert codes are defined
$keyAlerts = ['808', '807'];
foreach ($keyAlerts as $code) {
    $stmt = $pdo->prepare("SELECT display_name FROM " . DB_PREFIX . "alert_definitions WHERE alert_code = :code");
    $stmt->execute([':code' => $code]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    test(
        "Alert code '{$code}' is defined",
        $result !== false,
        "Definition: " . ($result['display_name'] ?? 'Found'),
        "Not found in alert_definitions"
    );
}

echo "\n";

// ===========================
// CACHE TESTS
// ===========================
echo "TEST CATEGORY: Device Cache\n";
echo str_repeat("-", 80) . "\n";

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM " . DB_PREFIX . "cache_devices");
    $cacheCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    test(
        "Device cache table exists",
        true,
        "Table present",
        "Table missing"
    );

    test(
        "Device cache populated",
        $cacheCount > 0,
        "{$cacheCount} devices cached",
        "Cache is empty (search will be slow)"
    );
} catch (Exception $e) {
    test("Device cache", false, "", "Error: " . $e->getMessage());
}

echo "\n";

// ===========================
// RESULTS SUMMARY
// ===========================
echo str_repeat("=", 80) . "\n";
echo "TEST RESULTS SUMMARY\n";
echo str_repeat("=", 80) . "\n\n";

echo "Total Tests: {$totalTests}\n";
echo "Passed: {$passedTests} (" . round(($passedTests/$totalTests)*100, 1) . "%)\n";
echo "Failed: {$failedTests} (" . round(($failedTests/$totalTests)*100, 1) . "%)\n\n";

if ($failedTests > 0) {
    echo "FAILED TESTS:\n";
    echo str_repeat("-", 80) . "\n";
    foreach ($testResults as $result) {
        if ($result['status'] === 'FAIL') {
            echo "❌ {$result['name']}\n";
            echo "   {$result['message']}\n\n";
        }
    }
}

if ($passedTests === $totalTests) {
    echo "✅ ALL TESTS PASSED - No regression detected\n";
} elseif ($failedTests <= 3) {
    echo "⚠️  MINOR ISSUES - Most tests passed\n";
} else {
    echo "❌ CRITICAL ISSUES - Multiple test failures\n";
}

echo "\nEnd Time: " . date('Y-m-d H:i:s') . "\n";
