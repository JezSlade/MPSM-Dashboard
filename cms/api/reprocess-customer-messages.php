<?php
/**
 * Reprocess Panel Messages for a Specific Customer
 * Creates dashboard notifications from historical messages that were received
 * before the Command Center engine was integrated.
 */

require '../config.php';
require '../functions.php';

requireAuth();

define('MPS_ENGINE_ACCESS', true);
require_once __DIR__ . '/../../mps-api/callbacks/panel-message-common.php';
require_once __DIR__ . '/../../mps-api/callbacks/command-center-engine.php';

header('Content-Type: application/json');

$customerCode = $_GET['customerCode'] ?? $_POST['customerCode'] ?? null;
$limit = min((int)($_GET['limit'] ?? 100), 500);
$dryRun = isset($_GET['dryRun']) && $_GET['dryRun'] === 'true';

if (!$customerCode) {
    echo json_encode([
        'success' => false,
        'error' => 'customerCode parameter required'
    ]);
    exit;
}

$pdo = getDatabase();

try {
    $result = [
        'customer_code' => $customerCode,
        'dry_run' => $dryRun,
        'limit' => $limit
    ];

    // Get panel messages for this customer with alert codes
    $pmTable = DB_PREFIX . 'panel_messages';
    $stmt = $pdo->prepare("
        SELECT id, device_serial,
               maintenance_alert_code, maintenance_alert_id,
               customer_code, customer_description,
               ny_received_at
        FROM {$pmTable}
        WHERE customer_code = :customer
          AND maintenance_alert_code IS NOT NULL
          AND maintenance_alert_code != ''
        ORDER BY ny_received_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':customer', $customerCode);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result['messages_found'] = count($messages);

    if (count($messages) === 0) {
        $result['success'] = true;
        $result['message'] = 'No messages with alert codes found for this customer';
        $result['notifications_created'] = 0;
        echo json_encode($result, JSON_PRETTY_PRINT);
        exit;
    }

    // Count notifications before
    $notifTable = DB_PREFIX . 'dashboard_notifications';
    $stmt = $pdo->query("SELECT COUNT(*) FROM {$notifTable} WHERE status = 'active'");
    $beforeCount = (int)$stmt->fetchColumn();

    $processed = 0;
    $errors = [];

    if (!$dryRun) {
        foreach ($messages as $msg) {
            try {
                $messageData = [
                    'device_serial' => $msg['device_serial'],
                    'device_identifier' => $msg['device_serial'],
                    'maintenance_alert_code' => $msg['maintenance_alert_code'],
                    'maintenance_alert_id' => $msg['maintenance_alert_id'] ?? null,
                    'customer_code' => $msg['customer_code'],
                    'customer_description' => $msg['customer_description'] ?? ''
                ];

                processNotificationRules($pdo, (int)$msg['id'], $messageData);
                $processed++;
            } catch (Exception $e) {
                $errors[] = "Message {$msg['id']}: {$e->getMessage()}";
            }
        }
    }

    // Count notifications after
    $stmt = $pdo->query("SELECT COUNT(*) FROM {$notifTable} WHERE status = 'active'");
    $afterCount = (int)$stmt->fetchColumn();

    // Get notifications created for this customer
    $stmt = $pdo->prepare("
        SELECT id, title, severity, device_serial, alert_code, trigger_count, created_at_ny
        FROM {$notifTable}
        WHERE customer_code = :customer AND status = 'active'
        ORDER BY created_at_ny DESC
        LIMIT 20
    ");
    $stmt->execute([':customer' => $customerCode]);
    $customerNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result['success'] = true;
    $result['processed'] = $processed;
    $result['notifications_before'] = $beforeCount;
    $result['notifications_after'] = $afterCount;
    $result['notifications_created'] = $afterCount - $beforeCount;
    $result['customer_active_notifications'] = count($customerNotifications);
    $result['notifications'] = $customerNotifications;
    $result['errors'] = $errors;

    if ($dryRun) {
        $result['message'] = "DRY RUN: Would process {$result['messages_found']} messages. Add &dryRun=false to execute.";
    } else {
        $result['message'] = "Processed {$processed} messages, created " . ($afterCount - $beforeCount) . " notifications";
    }

    echo json_encode($result, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
