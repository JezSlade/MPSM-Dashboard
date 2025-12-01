<?php
/**
 * Deep analysis of why only 2 notifications exist when there should be many more
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();

echo "=== DEEP RULE ANALYSIS ===\n\n";

// Check active rules
echo "STEP 1: Active Notification Rules\n";
echo str_repeat("-", 70) . "\n";

$stmt = $pdo->query("
    SELECT id, name, alert_code_pattern, frequency_count, frequency_window_hours,
           frequency_type, enabled, severity
    FROM " . DB_PREFIX . "notification_rules
    WHERE enabled = 1
    ORDER BY id
");

$rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($rules) . " active rules:\n\n";

foreach ($rules as $rule) {
    echo "Rule #{$rule['id']}: {$rule['name']}\n";
    echo "  Pattern: {$rule['alert_code_pattern']}\n";
    echo "  Threshold: {$rule['frequency_count']} occurrences in {$rule['frequency_window_hours']} hours\n";
    echo "  Type: {$rule['frequency_type']}\n";
    echo "  Severity: {$rule['severity']}\n\n";
}

// Check recent panel messages that match patterns
echo "\nSTEP 2: Recent Panel Messages Matching Patterns\n";
echo str_repeat("-", 70) . "\n";

foreach ($rules as $rule) {
    $pattern = $rule['alert_code_pattern'];

    // Convert pattern to SQL LIKE
    $likePattern = str_replace('*', '%', $pattern);

    echo "\nRule: {$rule['name']} (Pattern: {$pattern})\n";

    // Count messages in the time window
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total,
               COUNT(DISTINCT device_serial) as unique_devices,
               maintenance_alert_code as alert_code
        FROM " . DB_PREFIX . "panel_messages
        WHERE maintenance_alert_code LIKE :pattern
        AND received_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)
        GROUP BY maintenance_alert_code
    ");

    $stmt->execute([
        ':pattern' => $likePattern,
        ':hours' => $rule['frequency_window_hours']
    ]);

    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($matches)) {
        echo "  ❌ No messages match this pattern in last {$rule['frequency_window_hours']}h\n";
        continue;
    }

    foreach ($matches as $match) {
        echo "  ✓ Alert {$match['alert_code']}: {$match['total']} messages from {$match['unique_devices']} devices\n";
    }

    // Now check which devices exceed threshold
    $stmt = $pdo->prepare("
        SELECT device_serial, maintenance_alert_code, customer_code,
               COUNT(*) as occurrence_count,
               MIN(received_at) as first_occurrence,
               MAX(received_at) as last_occurrence
        FROM " . DB_PREFIX . "panel_messages
        WHERE maintenance_alert_code LIKE :pattern
        AND received_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)
        GROUP BY device_serial, maintenance_alert_code, customer_code
        HAVING COUNT(*) >= :threshold
        ORDER BY occurrence_count DESC
        LIMIT 20
    ");

    $stmt->execute([
        ':pattern' => $likePattern,
        ':hours' => $rule['frequency_window_hours'],
        ':threshold' => $rule['frequency_count']
    ]);

    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "  Devices exceeding threshold ({$rule['frequency_count']}x):\n";

    if (empty($candidates)) {
        echo "    ⚠️  NONE! No device has {$rule['frequency_count']}+ occurrences\n";
        echo "    This explains why no notifications were created!\n";
    } else {
        echo "    Found " . count($candidates) . " devices (showing top 20):\n";
        foreach ($candidates as $cand) {
            echo "      - {$cand['device_serial']} ({$cand['customer_code']}): ";
            echo "{$cand['occurrence_count']}x alert {$cand['maintenance_alert_code']}\n";
        }
    }
}

// Check existing notifications
echo "\n\nSTEP 3: Existing Notifications\n";
echo str_repeat("-", 70) . "\n";

$stmt = $pdo->query("
    SELECT id, device_serial, alert_code, customer_code, trigger_count,
           time_window_hours, created_at_ny, status, rule_id
    FROM " . DB_PREFIX . "dashboard_notifications
    WHERE status = 'active'
    ORDER BY created_at_ny DESC
");

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Active notifications: " . count($notifications) . "\n\n";

foreach ($notifications as $notif) {
    echo "ID {$notif['id']}: {$notif['device_serial']} / {$notif['alert_code']}\n";
    echo "  Customer: {$notif['customer_code']}\n";
    echo "  Trigger Count: {$notif['trigger_count']}x in {$notif['time_window_hours']}h\n";
    echo "  Created: {$notif['created_at_ny']}\n";
    echo "  Rule ID: {$notif['rule_id']}\n\n";
}

// Check if metrics are accurate
echo "\nSTEP 4: Verify Notification Metrics\n";
echo str_repeat("-", 70) . "\n";

foreach ($notifications as $notif) {
    // Count actual occurrences for this device/alert in the claimed time window
    $createdTime = strtotime($notif['created_at_ny']);
    $windowStart = date('Y-m-d H:i:s', $createdTime - ($notif['time_window_hours'] * 3600));

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as actual_count
        FROM " . DB_PREFIX . "panel_messages
        WHERE device_serial = :device
        AND maintenance_alert_code = :alert
        AND received_at BETWEEN :start AND :end
    ");

    $stmt->execute([
        ':device' => $notif['device_serial'],
        ':alert' => $notif['alert_code'],
        ':start' => $windowStart,
        ':end' => $notif['created_at_ny']
    ]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $actualCount = $result['actual_count'];
    $claimedCount = $notif['trigger_count'];

    $match = ($actualCount == $claimedCount) ? '✓' : '❌';

    echo "{$match} Notification #{$notif['id']} ({$notif['device_serial']} / {$notif['alert_code']})\n";
    echo "    Claimed: {$claimedCount}x\n";
    echo "    Actual:  {$actualCount}x\n";

    if ($actualCount != $claimedCount) {
        echo "    ⚠️  MISMATCH! Metric is incorrect\n";
    }
    echo "\n";
}

// DIAGNOSIS
echo "\n" . str_repeat("=", 70) . "\n";
echo "DIAGNOSIS\n";
echo str_repeat("=", 70) . "\n\n";

echo "Possible reasons for only 2 notifications:\n\n";

echo "1. THRESHOLDS TOO HIGH\n";
echo "   Current rules require 3+ occurrences within time window\n";
echo "   Most devices may have 1-2 occurrences, not reaching threshold\n\n";

echo "2. TIME WINDOWS TOO SHORT\n";
echo "   24-48 hour windows may miss patterns that occur over longer periods\n\n";

echo "3. PATTERN MATCHING TOO SPECIFIC\n";
echo "   Patterns like '808' only match exact code\n";
echo "   May need wildcard patterns like '80%' to catch 808, 807, 806, etc.\n\n";

echo "4. NOTIFICATION CREATION STOPPED\n";
echo "   If auto-reprocess isn't running, only manual notifications exist\n";
echo "   Need to check if processNotificationRules() is being called\n\n";

echo "5. DEDUPLICATION PREVENTING NEW NOTIFICATIONS\n";
echo "   UNIQUE KEY prevents duplicate device+alert+rule combinations\n";
echo "   Old notifications may be blocking new ones\n\n";
