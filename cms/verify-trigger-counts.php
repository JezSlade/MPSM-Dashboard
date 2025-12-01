<?php
/**
 * Verify the accuracy of trigger counts shown on notification cards
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();

echo "=== TRIGGER COUNT VERIFICATION ===\n\n";

// Get the 6 notifications currently showing on dashboard for W9OPXL0YDK
$stmt = $pdo->prepare("
    SELECT id, device_serial, alert_code, customer_code,
           trigger_count, time_window_hours, created_at_ny,
           rule_id
    FROM " . DB_PREFIX . "dashboard_notifications
    WHERE customer_code = 'W9OPXL0YDK'
    AND status = 'active'
    ORDER BY priority DESC, created_at_ny DESC
    LIMIT 6
");

$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Checking top 6 notifications for W9OPXL0YDK:\n\n";

foreach ($notifications as $notif) {
    echo "═══════════════════════════════════════════════════════════════════\n";
    echo "Notification ID: {$notif['id']}\n";
    echo "Device: {$notif['device_serial']}\n";
    echo "Alert Code: {$notif['alert_code']}\n";
    echo "Created: {$notif['created_at_ny']}\n";
    echo "Time Window: {$notif['time_window_hours']} hours\n";
    echo "Rule ID: {$notif['rule_id']}\n\n";

    echo "CLAIMED Trigger Count: {$notif['trigger_count']}x\n\n";

    // Get the rule details to understand the time window
    $ruleStmt = $pdo->prepare("
        SELECT name, frequency_window_hours, frequency_type
        FROM " . DB_PREFIX . "notification_rules
        WHERE id = :rule_id
    ");
    $ruleStmt->execute([':rule_id' => $notif['rule_id']]);
    $rule = $ruleStmt->fetch(PDO::FETCH_ASSOC);

    echo "Rule: {$rule['name']}\n";
    echo "Rule Window: {$rule['frequency_window_hours']} hours\n";
    echo "Rule Type: {$rule['frequency_type']}\n\n";

    // Calculate the time window for verification
    // The notification was created at created_at_ny
    // It counted messages in the X hours BEFORE creation
    $createdTimestamp = strtotime($notif['created_at_ny']);
    $windowStart = date('Y-m-d H:i:s', $createdTimestamp - ($notif['time_window_hours'] * 3600));
    $windowEnd = $notif['created_at_ny'];

    echo "Counting messages between:\n";
    echo "  Start: {$windowStart}\n";
    echo "  End:   {$windowEnd}\n\n";

    // Count ACTUAL occurrences in that window
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as actual_count
        FROM " . DB_PREFIX . "panel_messages
        WHERE device_serial = :device
        AND maintenance_alert_code = :alert
        AND received_at BETWEEN :start AND :end
    ");

    $countStmt->execute([
        ':device' => $notif['device_serial'],
        ':alert' => $notif['alert_code'],
        ':start' => $windowStart,
        ':end' => $windowEnd
    ]);

    $result = $countStmt->fetch(PDO::FETCH_ASSOC);
    $actualCount = $result['actual_count'];

    echo "ACTUAL Trigger Count: {$actualCount}x\n\n";

    // Compare
    $difference = abs($notif['trigger_count'] - $actualCount);
    $percentDiff = $actualCount > 0 ? round(($difference / $actualCount) * 100, 1) : 0;

    if ($notif['trigger_count'] == $actualCount) {
        echo "✓ ACCURATE - Claimed matches actual\n";
    } else {
        echo "❌ MISMATCH - Difference: {$difference} ({$percentDiff}%)\n";
        echo "   Claimed: {$notif['trigger_count']}x\n";
        echo "   Actual:  {$actualCount}x\n";

        if ($notif['trigger_count'] > $actualCount) {
            echo "   → Count is INFLATED\n";
        } else {
            echo "   → Count is UNDERREPORTED\n";
        }
    }

    // Also check current activity (last 1 hour)
    $nowStmt = $pdo->prepare("
        SELECT COUNT(*) as recent_count
        FROM " . DB_PREFIX . "panel_messages
        WHERE device_serial = :device
        AND maintenance_alert_code = :alert
        AND received_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");

    $nowStmt->execute([
        ':device' => $notif['device_serial'],
        ':alert' => $notif['alert_code']
    ]);

    $recentCount = $nowStmt->fetch(PDO::FETCH_ASSOC)['recent_count'];

    echo "\nCurrent Activity (last 1 hour): {$recentCount}x\n";

    // Get sample messages to see timestamps
    $sampleStmt = $pdo->prepare("
        SELECT received_at, maintenance_alert_code
        FROM " . DB_PREFIX . "panel_messages
        WHERE device_serial = :device
        AND maintenance_alert_code = :alert
        AND received_at BETWEEN :start AND :end
        ORDER BY received_at DESC
        LIMIT 5
    ");

    $sampleStmt->execute([
        ':device' => $notif['device_serial'],
        ':alert' => $notif['alert_code'],
        ':start' => $windowStart,
        ':end' => $windowEnd
    ]);

    $samples = $sampleStmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($samples)) {
        echo "\nSample Messages (most recent 5):\n";
        foreach ($samples as $sample) {
            echo "  - {$sample['received_at']} (alert {$sample['maintenance_alert_code']})\n";
        }
    }

    echo "\n";
}

// Summary
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$accurate = 0;
$inaccurate = 0;

foreach ($notifications as $notif) {
    $createdTimestamp = strtotime($notif['created_at_ny']);
    $windowStart = date('Y-m-d H:i:s', $createdTimestamp - ($notif['time_window_hours'] * 3600));
    $windowEnd = $notif['created_at_ny'];

    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as actual_count
        FROM " . DB_PREFIX . "panel_messages
        WHERE device_serial = :device
        AND maintenance_alert_code = :alert
        AND received_at BETWEEN :start AND :end
    ");

    $countStmt->execute([
        ':device' => $notif['device_serial'],
        ':alert' => $notif['alert_code'],
        ':start' => $windowStart,
        ':end' => $windowEnd
    ]);

    $actualCount = $countStmt->fetch(PDO::FETCH_ASSOC)['actual_count'];

    if ($notif['trigger_count'] == $actualCount) {
        $accurate++;
    } else {
        $inaccurate++;
        echo "ID {$notif['id']} ({$notif['device_serial']}): Claimed {$notif['trigger_count']}x vs Actual {$actualCount}x\n";
    }
}

echo "\nAccurate counts: {$accurate}\n";
echo "Inaccurate counts: {$inaccurate}\n";

if ($inaccurate > 0) {
    echo "\n❌ CONCLUSION: Trigger counts are NOT accurate\n";
    echo "Possible causes:\n";
    echo "1. Notification creation logic counting messages incorrectly\n";
    echo "2. Time window calculation using wrong timezone or dates\n";
    echo "3. Aggregation logic in processNotificationRules() has bugs\n";
    echo "4. Messages being counted multiple times or from wrong devices\n";
} else {
    echo "\n✓ CONCLUSION: All trigger counts are accurate\n";
}
