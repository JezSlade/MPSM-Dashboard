<?php
/**
 * DIAGNOSTIC: Why no notifications? (NO AUTH for emergency diagnosis)
 * DELETE THIS FILE AFTER DIAGNOSIS
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

define('MPS_ENGINE_ACCESS', true);
require __DIR__ . '/../mps-api/callbacks/command-center-engine.php';

// NO AUTH - FOR DIAGNOSIS ONLY
header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();

echo "=== EMERGENCY DIAGNOSTIC ===\n\n";

// 1. Check if rules exist
$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "notification_rules WHERE enabled = 1");
$ruleCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "1. Active Rules: {$ruleCount}\n";

if ($ruleCount === 0) {
    echo "   ❌ NO RULES FOUND - Must run insert_rules first!\n\n";
    exit;
}

// 2. List rules
echo "\n2. Rule Details:\n";
$stmt = $pdo->query("SELECT id, name, alert_code_pattern, frequency_count, frequency_window_hours, frequency_type FROM " . DB_PREFIX . "notification_rules WHERE enabled = 1");
while ($rule = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "   Rule {$rule['id']}: {$rule['name']}\n";
    echo "     Pattern: {$rule['alert_code_pattern']}\n";
    echo "     Threshold: {$rule['frequency_count']} in {$rule['frequency_window_hours']}h ({$rule['frequency_type']})\n";
}

// 3. Check panel messages
echo "\n3. Panel Messages:\n";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "panel_messages");
$msgCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "   Total messages: {$msgCount}\n";

// 4. Check date range
$stmt = $pdo->query("SELECT MIN(received_at) as oldest, MAX(received_at) as newest FROM " . DB_PREFIX . "panel_messages");
$dates = $stmt->fetch(PDO::FETCH_ASSOC);
echo "   Date range: {$dates['oldest']} to {$dates['newest']}\n";

// 5. Recent messages
$stmt = $pdo->query("SELECT COUNT(*) as count FROM " . DB_PREFIX . "panel_messages WHERE received_at >= DATE_SUB(NOW(), INTERVAL 48 HOUR)");
$recent = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "   Last 48 hours: {$recent} messages\n";

// 6. Top alert codes
echo "\n4. Top 10 Alert Codes:\n";
$stmt = $pdo->query("
    SELECT maintenance_alert_code, COUNT(*) as count
    FROM " . DB_PREFIX . "panel_messages
    WHERE maintenance_alert_code IS NOT NULL
    GROUP BY maintenance_alert_code
    ORDER BY count DESC
    LIMIT 10
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "   {$row['maintenance_alert_code']}: {$row['count']}\n";
}

// 7. Check for repeated alerts in last 48h
echo "\n5. Devices with Repeated Alerts (last 48h):\n";
$stmt = $pdo->query("
    SELECT device_serial, maintenance_alert_code, COUNT(*) as count
    FROM " . DB_PREFIX . "panel_messages
    WHERE received_at >= DATE_SUB(NOW(), INTERVAL 48 HOUR)
    AND maintenance_alert_code IS NOT NULL
    GROUP BY device_serial, maintenance_alert_code
    HAVING count >= 2
    ORDER BY count DESC
    LIMIT 10
");
$repeated = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($repeated)) {
    echo "   ❌ NO REPEATED ALERTS in last 48 hours!\n";
    echo "   This is why no notifications are being created.\n";
    echo "   All rules require multiple occurrences of same alert.\n";
} else {
    foreach ($repeated as $row) {
        echo "   {$row['device_serial']} / {$row['maintenance_alert_code']}: {$row['count']} times\n";
    }
}

// 8. Test pattern matching on top alerts
echo "\n6. Pattern Matching Test:\n";
$stmt = $pdo->query("SELECT id, alert_code_pattern FROM " . DB_PREFIX . "notification_rules WHERE enabled = 1");
$rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $pdo->query("SELECT DISTINCT maintenance_alert_code FROM " . DB_PREFIX . "panel_messages WHERE maintenance_alert_code IS NOT NULL LIMIT 20");
$alerts = $stmt2->fetchAll(PDO::FETCH_ASSOC);

foreach ($rules as $rule) {
    $matchCount = 0;
    $examples = [];
    foreach ($alerts as $alert) {
        if (matchesPattern($alert['maintenance_alert_code'], $rule['alert_code_pattern'])) {
            $matchCount++;
            if (count($examples) < 3) {
                $examples[] = $alert['maintenance_alert_code'];
            }
        }
    }
    echo "   Pattern '{$rule['alert_code_pattern']}': {$matchCount} matches\n";
    if (!empty($examples)) {
        echo "     Examples: " . implode(', ', $examples) . "\n";
    }
}

// 9. Check existing notifications
echo "\n7. Existing Notifications:\n";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications");
$notifCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "   Total: {$notifCount}\n";

$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications WHERE status = 'active'");
$activeCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "   Active: {$activeCount}\n";

// 10. RCA
echo "\n=== ROOT CAUSE ANALYSIS ===\n\n";

if ($ruleCount === 0) {
    echo "❌ PROBLEM: No active rules\n";
    echo "   SOLUTION: Run https://mpsm.resolutionsbydesign.us/cms/setup-alerts.php?action=insert_rules\n";
} elseif ($msgCount === 0) {
    echo "❌ PROBLEM: No panel messages in database\n";
    echo "   SOLUTION: Check panel message ingestion\n";
} elseif ($recent === 0) {
    echo "⚠️  WARNING: No messages in last 48 hours\n";
    echo "   All historical data is old. Rules won't trigger on old data.\n";
    echo "   SOLUTION: Wait for new panel messages, or adjust time windows\n";
} elseif (empty($repeated)) {
    echo "❌ PROBLEM: No devices have repeated alerts in last 48 hours\n";
    echo "   Rules require frequency thresholds (e.g., 3 in 24h)\n";
    echo "   If all alerts are single occurrences, no notifications will be created.\n";
    echo "   SOLUTION OPTIONS:\n";
    echo "   1. Lower frequency_count thresholds (e.g., from 3 to 1)\n";
    echo "   2. Increase frequency_window_hours (e.g., from 24h to 168h/7days)\n";
    echo "   3. Wait for devices to have repeat issues\n";
} else {
    echo "✓ Data looks good. Let's test notification creation...\n\n";

    // Test creating a notification for the first repeated alert
    $test = $repeated[0];
    echo "Testing with: {$test['device_serial']} / {$test['maintenance_alert_code']} ({$test['count']} occurrences)\n";

    // Get a message ID
    $stmt = $pdo->prepare("SELECT id, customer_code, panel_configuration FROM " . DB_PREFIX . "panel_messages WHERE device_serial = :device AND maintenance_alert_code = :alert LIMIT 1");
    $stmt->execute([':device' => $test['device_serial'], ':alert' => $test['maintenance_alert_code']]);
    $msg = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($msg) {
        $messageData = [
            'device_serial' => $test['device_serial'],
            'maintenance_alert_code' => $test['maintenance_alert_code'],
            'customer_code' => $msg['customer_code'],
            'panel_configuration' => $msg['panel_configuration']
        ];

        try {
            processNotificationRules($pdo, $msg['id'], $messageData);
            echo "✓ processNotificationRules executed\n";

            // Check if notification was created
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications");
            $newCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            if ($newCount > $notifCount) {
                echo "✓ Notification created! Total now: {$newCount}\n";
            } else {
                echo "❌ No notification created. Rules may not match or threshold not met.\n";
            }
        } catch (Exception $e) {
            echo "❌ ERROR: {$e->getMessage()}\n";
        }
    }
}

echo "\n=== END DIAGNOSTIC ===\n";
