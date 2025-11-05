<?php
/**
 * Get Device Panel Message History
 * Returns the most recent 100 panel messages for a specific device
 *
 * Parameters:
 * - serialNumber: Device serial number (required)
 * - limit: Number of messages (default: 100, max: 100)
 */

require '../config.php';
require '../functions.php';

requireAuth();

$serialNumber = $_GET['serialNumber'] ?? '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$limit = max(1, min($limit, 100)); // Enforce max 100

if (empty($serialNumber)) {
    jsonError("Serial number required");
    exit;
}

try {
    $pdo = getDatabase();
    $table = DB_PREFIX . 'panel_messages';

    // Get most recent messages for this device
    $sql = "SELECT
                id,
                received_at,
                customer_code,
                customer_description,
                device_serial,
                maintenance_alert_code,
                maintenance_alert_id,
                panel_configuration,
                payload,
                processed
            FROM {$table}
            WHERE device_serial = :serialNumber
            ORDER BY received_at DESC
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':serialNumber', $serialNumber, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $messages = [];

    foreach ($rows as $row) {
        $decodedPayload = json_decode($row['payload'], true);
        $messages[] = [
            'id' => (int)$row['id'],
            'received_at' => $row['received_at'],
            'customer_code' => $row['customer_code'],
            'customer_description' => $row['customer_description'],
            'device_serial' => $row['device_serial'],
            'maintenance_alert_code' => $row['maintenance_alert_code'],
            'maintenance_alert_id' => $row['maintenance_alert_id'],
            'panel_configuration' => $row['panel_configuration'],
            'processed' => (bool)$row['processed'],
            'payload' => $decodedPayload ?? $row['payload'],
        ];
    }

    jsonSuccess([
        'messages' => $messages,
        'total' => count($messages),
        'serialNumber' => $serialNumber,
        'limit' => $limit
    ]);

} catch (Exception $e) {
    jsonError("Failed to retrieve panel history: " . $e->getMessage());
}
