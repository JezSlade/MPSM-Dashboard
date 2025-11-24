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

    // Preload alert display names from alert_definitions (enabled only)
    $alertDisplay = [];
    try {
        $defsTable = DB_PREFIX . 'alert_definitions';
        $defsStmt = $pdo->query("SELECT alert_code, display_name, description FROM {$defsTable} WHERE enabled = 1");
        foreach ($defsStmt->fetchAll(PDO::FETCH_ASSOC) as $def) {
            $code = $def['alert_code'] ?? '';
            if ($code !== '') {
                $alertDisplay[$code] = $def['display_name'] ?: ($def['description'] ?: $code);
            }
        }
    } catch (Throwable $e) {
        error_log('[get-panel-messages] Failed to load alert definitions: ' . $e->getMessage());
    }

    $table = DB_PREFIX . 'panel_messages';
    $sql = "SELECT id, received_at, customer_code, customer_description, device_serial, maintenance_alert_code, maintenance_alert_id, panel_configuration, processed, payload
            FROM {$table}";

    $params = [];
    if ($hours !== null) {
        // Calculate cutoff timestamp in PHP (MySQL doesn't support binding inside INTERVAL)
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        $sql .= " WHERE received_at >= :cutoff";
        $params[':cutoff'] = $cutoff;
    }

    $sql .= " ORDER BY received_at DESC LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        // Bind cutoff as string timestamp, not integer
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $messages = [];

    foreach ($rows as $row) {
        $decodedPayload = json_decode($row['payload'], true);
        $payloadDescription = null;
        if (is_array($decodedPayload)) {
            $payloadDescription = $decodedPayload['maintenanceAlert']['description']
                ?? $decodedPayload['MaintenanceAlert_Description']
                ?? $decodedPayload['alert_description']
                ?? $decodedPayload['description']
                ?? null;
        }
        $code = $row['maintenance_alert_code'] ?? '';
        $displayName = null;
        if ($code !== '' && isset($alertDisplay[$code])) {
            $displayName = $alertDisplay[$code];
        } elseif ($payloadDescription) {
            $displayName = $payloadDescription;
        } elseif (!empty($row['panel_configuration'])) {
            $displayName = $row['panel_configuration'];
        } elseif ($code !== '') {
            $displayName = $code;
        }

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
            'display_name' => $displayName,
            'payload' => $decodedPayload ?? $row['payload'],
        ];
    }

    jsonSuccess(['messages' => $messages]);
} catch (Exception $e) {
    jsonError($e->getMessage());
}
