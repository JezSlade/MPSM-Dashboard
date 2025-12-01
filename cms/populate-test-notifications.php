<?php
/**
 * POPULATE: Create 10 test notifications from actual repeated alerts
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();

echo "=== POPULATE TEST NOTIFICATIONS ===\n\n";

// Get rule
$stmt = $pdo->query("SELECT * FROM " . DB_PREFIX . "notification_rules WHERE name = 'Repeated JAM Alerts' LIMIT 1");
$rule = $stmt->fetch(PDO::FETCH_ASSOC);

// Get devices with repeated 808 alerts
$stmt = $pdo->query("
    SELECT device_serial, maintenance_alert_code, customer_code, COUNT(*) as count
    FROM " . DB_PREFIX . "panel_messages
    WHERE maintenance_alert_code = '808'
    AND received_at >= DATE_SUB(NOW(), INTERVAL 48 HOUR)
    GROUP BY device_serial, maintenance_alert_code, customer_code
    HAVING count >= 3
    ORDER BY count DESC
    LIMIT 10
");

$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($devices) . " devices with repeated 808 alerts\n\n";

$table = DB_PREFIX . 'dashboard_notifications';
$created = 0;

foreach ($devices as $device) {
    // Check if notification already exists
    $checkStmt = $pdo->prepare("SELECT id FROM {$table} WHERE device_serial = :device AND alert_code = '808' AND status = 'active' LIMIT 1");
    $checkStmt->execute([':device' => $device['device_serial']]);

    if ($checkStmt->fetch()) {
        echo "SKIP: {$device['device_serial']} (already has notification)\n";
        continue;
    }

    $sql = "INSERT INTO {$table}
            (title, message, severity, rule_id, device_serial, alert_code, customer_code,
             trigger_count, time_window_hours, created_at_ny, status, icon, color, priority)
            VALUES (:title, :message, :severity, :rule_id, :device, :alert, :customer,
                    :count, :window, NOW(), 'active', 'exclamation-circle', 'orange', 75)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title' => 'Recurring 808 Alert',
            ':message' => "Device reporting repeated 808 alerts ({$device['count']} times in 48h). Inspect for system issues or mechanical blockages.",
            ':severity' => 'high',
            ':rule_id' => $rule['id'],
            ':device' => $device['device_serial'],
            ':alert' => '808',
            ':customer' => $device['customer_code'],
            ':count' => $device['count'],
            ':window' => 48
        ]);

        $notifId = $pdo->lastInsertId();
        echo "✓ {$device['device_serial']}: Created notification ID {$notifId} (count: {$device['count']})\n";
        $created++;

    } catch (Exception $e) {
        echo "✗ {$device['device_serial']}: ERROR - {$e->getMessage()}\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Created: {$created} notifications\n";

// Show current active count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM {$table} WHERE status = 'active'");
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "Total active notifications: {$total}\n\n";

echo "=== VIEW NOTIFICATIONS ===\n";
echo "Desktop: https://mpsm.resolutionsbydesign.us/cms/index.php\n";
echo "Mobile: https://mpsm.resolutionsbydesign.us/cms/mobile.php\n";
echo "Command Center: https://mpsm.resolutionsbydesign.us/cms/command-center.php?tab=notifications\n";
