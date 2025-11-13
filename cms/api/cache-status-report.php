<?php
/**
 * Cache Status Report
 * Detailed report on cache coverage and system health
 */

header('Content-Type: text/plain; charset=utf-8');

require '../config.php';
require '../functions.php';

set_time_limit(60);

$pdo = getDatabase();
$prefix = DB_PREFIX;

echo "=== MPSM DASHBOARD CACHE STATUS REPORT ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . " (America/New_York)\n";
echo str_repeat("=", 60) . "\n\n";

// 1. Device Cache Status
echo "1. DEVICE LIST CACHE\n";
echo str_repeat("-", 60) . "\n";

$stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}cache_devices");
$deviceCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}cache_devices WHERE is_uninstalled = 0");
$installedCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}cache_devices WHERE is_uninstalled = 1");
$uninstalledCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT MIN(cached_at) as oldest, MAX(cached_at) as newest FROM {$prefix}cache_devices");
$cacheAge = $stmt->fetch();

echo "Total Devices Cached: $deviceCount\n";
echo "  - Installed: $installedCount\n";
echo "  - Uninstalled: $uninstalledCount\n";
echo "Cache Age:\n";
echo "  - Oldest entry: " . ($cacheAge['oldest'] ?? 'N/A') . "\n";
echo "  - Newest entry: " . ($cacheAge['newest'] ?? 'N/A') . "\n";

if ($cacheAge['newest']) {
    $ageMinutes = floor((strtotime('now') - strtotime($cacheAge['newest'])) / 60);
    $ageHours = floor($ageMinutes / 60);
    echo "  - Freshness: $ageMinutes minutes ($ageHours hours) old\n";
}
echo "\n";

// 2. Drill-Down Cache Status
echo "2. DRILL-DOWN CACHE (Full Endpoint Data)\n";
echo str_repeat("-", 60) . "\n";

$stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}cache_device_drilldown");
$drilldownCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}cache_device_drilldown WHERE has_alerts = 1");
$withAlerts = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}cache_device_drilldown WHERE has_supplies = 1");
$withSupplies = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT MIN(cached_at) as oldest, MAX(cached_at) as newest FROM {$prefix}cache_device_drilldown");
$drilldownAge = $stmt->fetch();

echo "Devices with Full Drill-Down Data: $drilldownCount\n";
echo "  - With supply alerts: $withAlerts\n";
echo "  - With supplies data: $withSupplies\n";

if ($deviceCount > 0) {
    $coverage = round(($drilldownCount / $deviceCount) * 100, 1);
    echo "Coverage: $coverage% of cached devices\n";
}

echo "Drill-Down Cache Age:\n";
echo "  - Oldest entry: " . ($drilldownAge['oldest'] ?? 'N/A') . "\n";
echo "  - Newest entry: " . ($drilldownAge['newest'] ?? 'N/A') . "\n";
echo "\n";

// 3. Panel Message Callbacks
echo "3. PANEL MESSAGE CALLBACKS\n";
echo str_repeat("-", 60) . "\n";

$stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}panel_messages");
$messageCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(DISTINCT device_serial) FROM {$prefix}panel_messages");
$devicesWithMessages = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(DISTINCT maintenance_alert_code) FROM {$prefix}panel_messages WHERE maintenance_alert_code IS NOT NULL");
$uniqueAlertCodes = $stmt->fetchColumn();

$stmt = $pdo->query("
    SELECT MIN(received_at) as first, MAX(received_at) as last
    FROM {$prefix}panel_messages
");
$messageRange = $stmt->fetch();

$stmt = $pdo->query("
    SELECT maintenance_alert_code, COUNT(*) as count
    FROM {$prefix}panel_messages
    WHERE maintenance_alert_code IS NOT NULL
    GROUP BY maintenance_alert_code
    ORDER BY count DESC
    LIMIT 5
");
$topAlerts = $stmt->fetchAll();

echo "Total Callbacks Received: $messageCount\n";
echo "Devices with Messages: $devicesWithMessages\n";
echo "Unique Alert Codes: $uniqueAlertCodes\n";
echo "Message Timeline:\n";
echo "  - First message: " . ($messageRange['first'] ?? 'N/A') . "\n";
echo "  - Latest message: " . ($messageRange['last'] ?? 'N/A') . "\n";

echo "\nTop 5 Alert Codes:\n";
foreach ($topAlerts as $alert) {
    echo "  - {$alert['maintenance_alert_code']}: {$alert['count']} occurrences\n";
}
echo "\n";

// 4. Integration Status
echo "4. INTEGRATION STATUS\n";
echo str_repeat("-", 60) . "\n";

// Devices with cache but no panel messages
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT cd.serial_number)
    FROM {$prefix}cache_devices cd
    LEFT JOIN {$prefix}panel_messages pm ON cd.serial_number = pm.device_serial
    WHERE pm.device_serial IS NULL
");
$devicesWithoutMessages = $stmt->fetchColumn();

// Devices with panel messages but no drill-down cache
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT pm.device_serial)
    FROM {$prefix}panel_messages pm
    LEFT JOIN {$prefix}cache_device_drilldown cdd ON pm.device_serial = cdd.serial_number
    WHERE cdd.serial_number IS NULL
");
$messagesWithoutDrilldown = $stmt->fetchColumn();

// Devices with drill-down but no base cache
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT cdd.serial_number)
    FROM {$prefix}cache_device_drilldown cdd
    LEFT JOIN {$prefix}cache_devices cd ON cdd.serial_number = cd.serial_number
    WHERE cd.serial_number IS NULL
");
$drilldownWithoutBase = $stmt->fetchColumn();

echo "Devices with cache but no panel messages: $devicesWithoutMessages\n";
echo "Devices with panel messages but no drill-down: $messagesWithoutDrilldown\n";
echo "Drill-downs without base cache: $drilldownWithoutBase\n";

if ($messagesWithoutDrilldown > 0) {
    echo "  ⚠ Warning: Some devices have panel messages but missing drill-down cache\n";
}
if ($drilldownWithoutBase > 0) {
    echo "  ⚠ Warning: Orphaned drill-down entries detected\n";
}
echo "\n";

// 5. Command Center Status
echo "5. COMMAND CENTER STATUS\n";
echo str_repeat("-", 60) . "\n";

$stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}notification_rules WHERE enabled = 1");
$activeRules = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}notification_rules WHERE enabled = 0");
$inactiveRules = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}dashboard_notifications WHERE status = 'active'");
$activeNotifications = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}dashboard_notifications WHERE status = 'acknowledged'");
$acknowledgedNotifications = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}dashboard_notifications WHERE status = 'dismissed'");
$dismissedNotifications = $stmt->fetchColumn();

echo "Notification Rules:\n";
echo "  - Active rules: $activeRules\n";
echo "  - Inactive rules: $inactiveRules\n";
echo "Dashboard Notifications:\n";
echo "  - Active: $activeNotifications\n";
echo "  - Acknowledged: $acknowledgedNotifications\n";
echo "  - Dismissed: $dismissedNotifications\n";

if ($activeRules === 0 && $messageCount > 0) {
    echo "  ⚠ Note: No active rules - notifications won't be generated\n";
}
echo "\n";

// 6. Sample Drill-Down Data
echo "6. SAMPLE DRILL-DOWN DATA\n";
echo str_repeat("-", 60) . "\n";

$stmt = $pdo->query("
    SELECT
        cd.serial_number,
        cd.customer_code,
        cdd.serial_number IS NOT NULL as has_drilldown,
        (SELECT COUNT(*) FROM {$prefix}panel_messages pm WHERE pm.device_serial = cd.serial_number) as message_count,
        cd.cached_at
    FROM {$prefix}cache_devices cd
    LEFT JOIN {$prefix}cache_device_drilldown cdd ON cd.serial_number = cdd.serial_number
    WHERE cd.is_uninstalled = 0
    ORDER BY cd.cached_at DESC
    LIMIT 10
");
$samples = $stmt->fetchAll();

echo "Latest 10 Cached Devices:\n";
foreach ($samples as $sample) {
    $drilldown = $sample['has_drilldown'] ? '✓' : '✗';
    $messages = $sample['message_count'];
    echo "  {$sample['serial_number']} (Customer: {$sample['customer_code']})\n";
    echo "    Drill-down: $drilldown | Messages: $messages | Cached: {$sample['cached_at']}\n";
}
echo "\n";

// 7. Overall Health Score
echo "7. OVERALL HEALTH SCORE\n";
echo str_repeat("-", 60) . "\n";

$score = 100;
$issues = [];

if ($deviceCount === 0) {
    $score -= 50;
    $issues[] = "No devices cached";
}

if ($deviceCount > 0 && $drilldownCount / $deviceCount < 0.5) {
    $score -= 20;
    $issues[] = "Low drill-down coverage (< 50%)";
}

if ($cacheAge['newest']) {
    $ageMinutes = floor((strtotime('now') - strtotime($cacheAge['newest'])) / 60);
    if ($ageMinutes > 360) {
        $score -= 15;
        $issues[] = "Stale cache (> 6 hours old)";
    }
}

if ($messagesWithoutDrilldown > 10) {
    $score -= 10;
    $issues[] = "Many devices missing drill-down data";
}

if ($activeRules === 0 && $messageCount > 100) {
    $score -= 5;
    $issues[] = "No notification rules despite active callbacks";
}

$healthStatus = $score >= 90 ? "EXCELLENT" : ($score >= 70 ? "GOOD" : ($score >= 50 ? "FAIR" : "POOR"));

echo "Health Score: $score/100 ($healthStatus)\n";

if (empty($issues)) {
    echo "Status: ✓ All systems operational\n";
} else {
    echo "Issues Found:\n";
    foreach ($issues as $issue) {
        echo "  ⚠ $issue\n";
    }
}

echo "\n";

// 8. Cache Refresh Logs
echo "8. RECENT CACHE REFRESH ACTIVITY\n";
echo str_repeat("-", 60) . "\n";

$logFile = dirname(__DIR__) . '/logs/cache-refresh-' . date('Y-m-d') . '.log';
$yesterdayLog = dirname(__DIR__) . '/logs/cache-refresh-' . date('Y-m-d', strtotime('-1 day')) . '.log';

if (file_exists($logFile)) {
    $logLines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $recentLines = array_slice($logLines, -20);

    echo "Today's Log (last 20 lines):\n";
    foreach ($recentLines as $line) {
        echo "  $line\n";
    }
} else {
    echo "No log file found for today ($logFile)\n";
}

if (file_exists($yesterdayLog)) {
    echo "\nYesterday's log available: $yesterdayLog\n";
}

echo "\n";

// 9. Error Detection
echo "9. ERROR DETECTION\n";
echo str_repeat("-", 60) . "\n";

$errorCount = 0;
$warnings = 0;

if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $errorCount = substr_count($logContent, 'ERROR');
    $warnings = substr_count($logContent, 'WARNING');
}

echo "Errors in today's log: $errorCount\n";
echo "Warnings in today's log: $warnings\n";

if ($errorCount > 0 || $warnings > 10) {
    echo "  ⚠ ATTENTION: Check logs for details\n";
}

echo "\n";
echo str_repeat("=", 60) . "\n";
echo "End of Report\n";
