<?php
/**
 * Bulk Reprocess All Panel Messages
 * Processes ALL panel messages through notification rules in batches
 * Use: https://mpsm.resolutionsbydesign.us/cms/bulk-reprocess.php
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

define('MPS_ENGINE_ACCESS', true);
require __DIR__ . '/../mps-api/callbacks/command-center-engine.php';

requireAuth();

set_time_limit(600); // 10 minutes max
ini_set('memory_limit', '512M');

header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();

echo "=== BULK PANEL MESSAGE REPROCESSING ===\n\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n\n";

// Check for active rules
$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "notification_rules WHERE enabled = 1");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$activeRules = $result['total'];

if ($activeRules === 0) {
    echo "ERROR: No active notification rules found.\n";
    echo "Please insert rules first via setup-alerts.php?action=insert_rules\n";
    exit(1);
}

echo "Active rules: {$activeRules}\n";

// Get total message count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "panel_messages");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalMessages = $result['total'];

echo "Total panel messages: {$totalMessages}\n\n";

// Get batch settings
$batchSize = isset($_GET['batch']) ? (int)$_GET['batch'] : 1000;
$maxMessages = isset($_GET['limit']) ? (int)$_GET['limit'] : $totalMessages;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

echo "Batch size: {$batchSize}\n";
echo "Processing limit: {$maxMessages} messages\n";
echo "Starting offset: {$offset}\n\n";

$processed = 0;
$notificationsCreated = 0;
$errors = 0;
$batches = 0;

// Count notifications before
$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications");
$beforeCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

echo "Existing notifications: {$beforeCount}\n\n";
echo "--- PROCESSING ---\n\n";

$currentOffset = $offset;
while ($processed < $maxMessages) {
    $batches++;
    $remaining = min($batchSize, $maxMessages - $processed);

    echo "Batch {$batches}: Processing {$remaining} messages (offset {$currentOffset})...\n";

    // Fetch batch
    $sql = "SELECT id, device_serial, maintenance_alert_code,
                   customer_code, customer_description, panel_configuration
            FROM " . DB_PREFIX . "panel_messages
            ORDER BY received_at DESC
            LIMIT {$remaining} OFFSET {$currentOffset}";

    $stmt = $pdo->query($sql);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($messages)) {
        echo "  No more messages found.\n";
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
            $errors++;
            if ($errors <= 5) {
                echo "  ERROR on message {$message['id']}: {$e->getMessage()}\n";
            }
        }
    }

    $currentOffset += $remaining;

    // Show progress every batch
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications");
    $currentCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $created = $currentCount - $beforeCount;

    echo "  Processed: {$processed}/{$maxMessages} | Notifications created so far: {$created}\n";

    // Prevent timeout
    flush();
    if (connection_aborted()) {
        echo "\n\nConnection aborted by client.\n";
        break;
    }
}

// Count notifications after
$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications");
$afterCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$notificationsCreated = $afterCount - $beforeCount;

echo "\n--- COMPLETE ---\n\n";
echo "Messages processed: {$processed}\n";
echo "Notifications created: {$notificationsCreated}\n";
echo "Errors: {$errors}\n";
echo "Batches: {$batches}\n";
echo "Finished: " . date('Y-m-d H:i:s') . "\n\n";

// Show notification breakdown
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
    echo "No active notifications created.\n";
    echo "This means no devices met the frequency thresholds in the rules.\n";
}

echo "\nView notifications:\n";
echo "Desktop: https://mpsm.resolutionsbydesign.us/cms/index.php\n";
echo "Command Center: https://mpsm.resolutionsbydesign.us/cms/command-center.php?tab=notifications\n";
echo "Mobile: https://mpsm.resolutionsbydesign.us/cms/mobile.php\n";
