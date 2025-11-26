<?php
/**
 * Fetch a single panel message by ID with resolved fields.
 */

require '../config.php';
require '../functions.php';

requireAuth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'id required']);
    exit;
}

try {
    $pdo = getDatabase();

    // Preload alert display names
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
        error_log('[get-panel-message] defs load failed: ' . $e->getMessage());
    }

    $table = DB_PREFIX . 'panel_messages';
    $devicesTable = DB_PREFIX . 'cache_devices';
    $sql = "SELECT pm.id,
                   pm.received_at,
                   pm.customer_code,
                   pm.customer_description,
                   pm.device_serial,
                   pm.maintenance_alert_code,
                   pm.maintenance_alert_id,
                   pm.panel_configuration,
                   pm.processed,
                   pm.payload,
                   COALESCE(
                       JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.Department')),
                       JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.department')),
                       JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.OfficeDescription')),
                       JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.Note'))
                   ) AS department
            FROM {$table} pm
            LEFT JOIN {$devicesTable} cd ON cd.serial_number = pm.device_serial
            WHERE pm.id = :id
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['success' => false, 'error' => 'Message not found']);
        exit;
    }

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

    $row['display_name'] = $displayName;
    if (is_array($decodedPayload)) {
        $row['payload'] = $decodedPayload;
    }

    echo json_encode(['success' => true, 'message' => $row]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/*
CHANGELOG
2025-11-26 Codex
- Added get-panel-message.php to fetch a single panel message by id for efficient payload modal rendering.
*/

