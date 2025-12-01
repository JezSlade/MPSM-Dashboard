<?php
/**
 * AUTO-REPROCESS: Process all messages (NO AUTH for automation)
 * SECURITY: DELETE THIS FILE AFTER USE
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

define('MPS_ENGINE_ACCESS', true);
require __DIR__ . '/../mps-api/callbacks/command-center-engine.php';

set_time_limit(600);
ini_set('memory_limit', '512M');

header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();

echo "=== AUTO-REPROCESS ALL MESSAGES ===\n\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n\n";

// Check rules
$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "notification_rules WHERE enabled = 1");
$activeRules = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

if ($activeRules === 0) {
    echo "❌ ERROR: No active rules\n";
    exit(1);
}

echo "Active rules: {$activeRules}\n";

// Get message count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "panel_messages");
$totalMessages = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "Total messages: {$totalMessages}\n";

// Count before
$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications");
$beforeCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "Existing notifications: {$beforeCount}\n\n";

// Process in batches
$batchSize = 1000;
$limit = min(10000, $totalMessages); // Cap at 10K for first run
$processed = 0;
$offset = 0;
$batches = 0;

echo "Processing {$limit} messages in batches of {$batchSize}...\n\n";

while ($processed < $limit) {
    $batches++;
    $remaining = min($batchSize, $limit - $processed);

    echo "Batch {$batches}: Processing {$remaining} messages (offset {$offset})...\n";

    $sql = "SELECT id, device_serial, maintenance_alert_code,
                   customer_code, customer_description, panel_configuration
            FROM " . DB_PREFIX . "panel_messages
            ORDER BY received_at DESC
            LIMIT {$remaining} OFFSET {$offset}";

    $stmt = $pdo->query($sql);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($messages)) {
        echo "  No more messages\n";
        break;
    }

    foreach ($messages as $message) {
        try {
            $messageData = [
                'device_serial' => $message['device_serial'],
                'maintenance_alert_code' => $message['maintenance_alert_code'],
                'customer_code' => $message['customer_code'],
                'customer_description' => $message['customer_description'],
                'panel_configuration' => $message['panel_configuration']
            ];

            processNotificationRules($pdo, (int)$message['id'], $messageData);
            $processed++;

        } catch (Exception $e) {
            // Skip errors
        }
    }

    $offset += $remaining;

    // Show progress
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications");
    $currentCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $created = $currentCount - $beforeCount;

    echo "  Processed: {$processed}/{$limit} | Notifications created: {$created}\n";
    flush();

    if (connection_aborted()) {
        break;
    }
}

// Final count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications");
$afterCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$notificationsCreated = $afterCount - $beforeCount;

echo "\n=== COMPLETE ===\n\n";
echo "Messages processed: {$processed}\n";
echo "Notifications created: {$notificationsCreated}\n";
echo "Batches: {$batches}\n";
echo "Finished: " . date('Y-m-d H:i:s') . "\n\n";

// Show breakdown
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
        echo "  {$row['severity']}: {$row['count']}\n";
    }
} else {
    echo "No active notifications.\n";
}

echo "\n=== NEXT: VERIFY ===\n";
echo "Desktop: https://mpsm.resolutionsbydesign.us/cms/index.php\n";
echo "Mobile: https://mpsm.resolutionsbydesign.us/cms/mobile.php\n";
