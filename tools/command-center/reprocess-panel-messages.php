<?php
/**
 * Reprocess Existing Panel Messages Against New Notification Rules
 *
 * This script processes all existing panel messages through the notification
 * rules engine to generate notifications for historical data.
 */

$repoRoot = dirname(__DIR__, 2);
require $repoRoot . '/cms/config.php';
require $repoRoot . '/cms/functions.php';

define('MPS_ENGINE_ACCESS', true);
require $repoRoot . '/mps-api/callbacks/command-center-engine.php';

$pdo = getDatabase();

echo "=== Reprocessing Panel Messages Against Notification Rules ===\n\n";

// Get count of panel messages
$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "panel_messages");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalMessages = $result['total'];

echo "Total panel messages: {$totalMessages}\n";

// Get active rules count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "notification_rules WHERE enabled = 1");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$activeRules = $result['total'];

echo "Active notification rules: {$activeRules}\n\n";

if ($activeRules === 0) {
    echo "ERROR: No active notification rules found.\n";
    echo "Run tools/command-center/create-live-rules.php first to create rules.\n";
    exit(1);
}

// Fetch all panel messages (most recent first)
$sql = "SELECT id, device_serial, maintenance_alert_code,
               customer_code, customer_description, panel_configuration,
               received_at
        FROM " . DB_PREFIX . "panel_messages
        ORDER BY received_at DESC
        LIMIT 100";  // Process last 100 messages to avoid timeout

$stmt = $pdo->query($sql);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Processing last " . count($messages) . " panel messages...\n\n";

$processed = 0;
$notificationsCreated = 0;
$errors = 0;

foreach ($messages as $message) {
    try {
        $messageData = [
            'device_serial' => $message['device_serial'],
            'maintenance_alert_code' => $message['maintenance_alert_code'],
            'customer_code' => $message['customer_code'],
            'customer_description' => $message['customer_description'],
            'panel_configuration' => $message['panel_configuration']
        ];

        // Count notifications before processing
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications");
        $beforeCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Process message through rules engine
        processNotificationRules($pdo, (int)$message['id'], $messageData);

        // Count notifications after processing
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications");
        $afterCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $created = $afterCount - $beforeCount;
        $notificationsCreated += $created;

        $processed++;

        if ($created > 0) {
            echo "✓ Message {$message['id']}: {$message['device_serial']} / {$message['maintenance_alert_code']} → {$created} notification(s)\n";
        }

    } catch (Exception $e) {
        $errors++;
        echo "✗ Message {$message['id']}: ERROR - {$e->getMessage()}\n";
    }
}

echo "\n=== Reprocessing Complete ===\n";
echo "Messages processed: {$processed}\n";
echo "Notifications created: {$notificationsCreated}\n";
echo "Errors: {$errors}\n\n";

// Show current notification counts
$stmt = $pdo->query("
    SELECT severity, COUNT(*) as count
    FROM " . DB_PREFIX . "dashboard_notifications
    WHERE status = 'active'
    GROUP BY severity
    ORDER BY FIELD(severity, 'critical', 'high', 'warning', 'info')
");
$severityCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($severityCounts)) {
    echo "Active Notifications by Severity:\n";
    foreach ($severityCounts as $row) {
        echo "  - {$row['severity']}: {$row['count']}\n";
    }
} else {
    echo "No active notifications found.\n";
}

echo "\nVisit Command Center: https://mpsm.resolutionsbydesign.us/cms/command-center.php\n";
