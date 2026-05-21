<?php
/**
 * Fetch recent panel message callback payloads.
 */

require '../config.php';
require '../functions.php';

requireAuth();

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 200;
$limit = max(1, min($limit, 500));
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$offset = max(0, $offset);

$hours = isset($_GET['hours']) ? (int)$_GET['hours'] : null;
if ($hours !== null) {
    $hours = max(1, min($hours, 168));
}
$customerCode = isset($_GET['customerCode']) ? trim((string)$_GET['customerCode']) : null;
if ($customerCode !== null && $customerCode === '') {
    $customerCode = null;
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
    $devicesTable = DB_PREFIX . 'cache_devices';
    $tableExists = (bool)$pdo->query("SHOW TABLES LIKE '{$table}'")->fetchColumn();
    if (!$tableExists) {
        jsonSuccess([
            'messages' => [],
            'warning' => "Panel message table '{$table}' is not initialized."
        ]);
        return;
    }

    $devicesTableExists = (bool)$pdo->query("SHOW TABLES LIKE '{$devicesTable}'")->fetchColumn();
    $departmentSelect = $devicesTableExists
        ? "COALESCE(
               JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.Department')),
               JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.department')),
               JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.OfficeDescription')),
               JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.Note'))
           ) AS department"
        : "NULL AS department";

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
                   {$departmentSelect}
            FROM {$table} pm";

    if ($devicesTableExists) {
        $sql .= " LEFT JOIN {$devicesTable} cd ON cd.serial_number = pm.device_serial";
    }

    $params = [];
    $conditions = [];

    if ($hours !== null) {
        // Calculate cutoff timestamp in PHP (MySQL doesn't support binding inside INTERVAL)
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        $conditions[] = "received_at >= :cutoff";
        $params[':cutoff'] = $cutoff;
    }

    if ($customerCode !== null) {
        $conditions[] = "pm.customer_code = :customerCode";
        $params[':customerCode'] = $customerCode;
    }

    if ($conditions) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $sql .= " ORDER BY received_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        // Bind cutoff as string timestamp, not integer
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $messages = [];

    foreach ($rows as $row) {
        $decodedPayload = json_decode($row['payload'], true);
        $payloadDescription = null;
        $payloadDepartment = null;
        if (is_array($decodedPayload)) {
            $payloadDescription = $decodedPayload['maintenanceAlert']['description']
                ?? $decodedPayload['MaintenanceAlert_Description']
                ?? $decodedPayload['alert_description']
                ?? $decodedPayload['description']
                ?? null;
            $payloadDepartment = $decodedPayload['Department']
                ?? $decodedPayload['department']
                ?? $decodedPayload['OfficeDescription']
                ?? $decodedPayload['Note']
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
            'department' => $row['department'] ?? $payloadDepartment,
            'display_name' => $displayName,
            'payload' => $decodedPayload ?? $row['payload'],
        ];
    }

    jsonSuccess(['messages' => $messages]);
} catch (Exception $e) {
    jsonError($e->getMessage());
}

/*
CHANGELOG
2025-11-28 Codex
- Added department (physical location) to panel message responses via cache join/payload fallback and ensured human-readable alert display names take precedence over raw codes.
2025-11-24 Codex
- Added optional customerCode filter support and wired bindings for combined hours/customer queries to keep monitor views scoped.
*/
