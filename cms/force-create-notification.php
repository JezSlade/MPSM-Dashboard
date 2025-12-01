<?php
/**
 * FORCE: Directly create a test notification bypassing all logic
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();

echo "=== FORCE CREATE NOTIFICATION ===\n\n";

// Get rule
$stmt = $pdo->query("SELECT * FROM " . DB_PREFIX . "notification_rules WHERE name = 'Repeated JAM Alerts' LIMIT 1");
$rule = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rule) {
    echo "❌ Rule not found\n";
    exit;
}

echo "Rule ID: {$rule['id']}\n";
echo "Pattern: {$rule['alert_code_pattern']}\n\n";

// Get a device with 808 alerts
$stmt = $pdo->query("
    SELECT device_serial, maintenance_alert_code, customer_code
    FROM " . DB_PREFIX . "panel_messages
    WHERE maintenance_alert_code = '808'
    LIMIT 1
");
$msg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$msg) {
    echo "❌ No 808 messages found\n";
    exit;
}

echo "Device: {$msg['device_serial']}\n";
echo "Alert: {$msg['maintenance_alert_code']}\n";
echo "Customer: {$msg['customer_code']}\n\n";

// Force insert notification
$table = DB_PREFIX . 'dashboard_notifications';

$sql = "INSERT INTO {$table}
        (title, message, severity, rule_id, device_serial, alert_code, customer_code,
         trigger_count, time_window_hours, created_at_ny, status, icon, color, priority)
        VALUES (:title, :message, :severity, :rule_id, :device, :alert, :customer,
                :count, :window, NOW(), 'active', 'fire', 'red', 75)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => 'TEST: Repeated 808 Alert',
        ':message' => 'This is a forced test notification to verify display.',
        ':severity' => 'high',
        ':rule_id' => $rule['id'],
        ':device' => $msg['device_serial'],
        ':alert' => $msg['maintenance_alert_code'],
        ':customer' => $msg['customer_code'],
        ':count' => 3,
        ':window' => 24
    ]);

    $notifId = $pdo->lastInsertId();
    echo "✅ SUCCESS! Created notification ID: {$notifId}\n\n";

    // Verify it exists
    $stmt = $pdo->query("SELECT * FROM {$table} WHERE id = {$notifId}");
    $notif = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Notification details:\n";
    echo "  Title: {$notif['title']}\n";
    echo "  Severity: {$notif['severity']}\n";
    echo "  Status: {$notif['status']}\n";
    echo "  Device: {$notif['device_serial']}\n";
    echo "  Alert: {$notif['alert_code']}\n";
    echo "  Customer: {$notif['customer_code']}\n\n";

    echo "=== VERIFY DISPLAY ===\n";
    echo "Desktop: https://mpsm.resolutionsbydesign.us/cms/index.php\n";
    echo "Mobile: https://mpsm.resolutionsbydesign.us/cms/mobile.php\n";
    echo "API: https://mpsm.resolutionsbydesign.us/cms/api/command-center.php?action=get_notifications&status=active\n";

} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
    exit(1);
}
