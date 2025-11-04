<?php
/**
 * Fetch recent panel message callback payloads.
 */

require '../config.php';
require '../functions.php';

requireAuth();

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 200;
$limit = max(1, min($limit, 500));

$hours = isset($_GET['hours']) ? (int)$_GET['hours'] : null;
if ($hours !== null) {
    $hours = max(1, min($hours, 168));
}

try {
    $pdo = getDatabase();

    $table = DB_PREFIX . 'panel_messages';
    $sql = "SELECT id, received_at, customer_code, customer_description, device_serial, maintenance_alert_code, maintenance_alert_id, panel_configuration, processed, payload
            FROM {$table}";

    $params = [];
    if ($hours !== null) {
        $sql .= " WHERE received_at >= (NOW() - INTERVAL :hours HOUR)";
        $params[':hours'] = $hours;
    }

    $sql .= " ORDER BY received_at DESC LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    }
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

    jsonSuccess(['messages' => $messages]);
} catch (Exception $e) {
    jsonError($e->getMessage());
}
