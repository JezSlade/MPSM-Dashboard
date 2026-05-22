<?php
/**
 * Fetch recent panel message callback payloads.
 */

require '../config.php';
require '../functions.php';

requireAuth();

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
    return (bool)$stmt->fetchColumn();
}

function ensureIndexIfMissing(PDO $pdo, string $table, string $indexName, string $ddl): void
{
    try {
        if (!tableExists($pdo, $table)) {
            return;
        }
        $stmt = $pdo->prepare("SHOW INDEX FROM `{$table}` WHERE Key_name = :key_name");
        $stmt->execute([':key_name' => $indexName]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            $pdo->exec($ddl);
        }
    } catch (Throwable $error) {
        error_log("[get-panel-messages] Index ensure failed for {$table}.{$indexName}: " . $error->getMessage());
    }
}

function ensurePanelMessageIndexes(PDO $pdo): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }
    $ensured = true;

    $table = DB_PREFIX . 'panel_messages';
    ensureIndexIfMissing(
        $pdo,
        $table,
        'idx_panel_messages_customer_received',
        "CREATE INDEX `idx_panel_messages_customer_received` ON `{$table}` (`customer_code`, `received_at`)"
    );
}

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
    ensurePanelMessageIndexes($pdo);

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
    if (!tableExists($pdo, $table)) {
        jsonSuccess([
            'messages' => [],
            'warning' => "Panel message table '{$table}' is not initialized."
        ]);
        return;
    }

    $sql = "SELECT pm.id,
                   pm.received_at,
                   pm.customer_code,
                   pm.customer_description,
                   pm.device_serial,
                   pm.maintenance_alert_code,
                   pm.maintenance_alert_id,
                   pm.panel_configuration,
                   pm.processed,
                   pm.payload
            FROM {$table} pm";

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
    $deviceMetaBySerial = [];
    if (!empty($rows) && tableExists($pdo, $devicesTable)) {
        $serials = array_values(array_unique(array_filter(array_map(static function ($row) {
            return trim((string)($row['device_serial'] ?? ''));
        }, $rows))));

        if (!empty($serials)) {
            $placeholders = [];
            $metaParams = [];
            foreach ($serials as $index => $serial) {
                $token = ':serial_' . $index;
                $placeholders[] = $token;
                $metaParams[$token] = $serial;
            }

            $metaSql = "SELECT serial_number,
                               JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Department')) AS department_1,
                               JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.department')) AS department_2,
                               JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.OfficeDescription')) AS department_3,
                               JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Note')) AS department_4,
                               JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Product.Model')) AS model,
                               JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.EquipmentId')) AS equipment_id_1,
                               JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.EquipmentID')) AS equipment_id_2,
                               JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IPAddress')) AS ip_1,
                               JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) AS ip_2,
                               JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IP')) AS ip_3,
                               JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Ip')) AS ip_4
                        FROM {$devicesTable}
                        WHERE serial_number IN (" . implode(',', $placeholders) . ")";

            try {
                $metaStmt = $pdo->prepare($metaSql);
                foreach ($metaParams as $token => $value) {
                    $metaStmt->bindValue($token, $value);
                }
                $metaStmt->execute();
                foreach ($metaStmt->fetchAll(PDO::FETCH_ASSOC) as $metaRow) {
                    $serial = trim((string)($metaRow['serial_number'] ?? ''));
                    if ($serial === '') {
                        continue;
                    }
                    $deviceMetaBySerial[$serial] = [
                        'department' => $metaRow['department_1'] ?: ($metaRow['department_2'] ?: ($metaRow['department_3'] ?: ($metaRow['department_4'] ?: null))),
                        'model' => $metaRow['model'] ?: null,
                        'equipment_id' => $metaRow['equipment_id_1'] ?: ($metaRow['equipment_id_2'] ?: null),
                        'ip_address' => $metaRow['ip_1'] ?: ($metaRow['ip_2'] ?: ($metaRow['ip_3'] ?: ($metaRow['ip_4'] ?: null)))
                    ];
                }
            } catch (Throwable $metaError) {
                error_log('[get-panel-messages] Device metadata enrichment failed: ' . $metaError->getMessage());
            }
        }
    }

    $messages = [];

    foreach ($rows as $row) {
        $serial = trim((string)($row['device_serial'] ?? ''));
        $meta = ($serial !== '' && isset($deviceMetaBySerial[$serial])) ? $deviceMetaBySerial[$serial] : [];
        $decodedPayload = json_decode($row['payload'], true);
        $payloadDescription = null;
        $payloadDepartment = null;
        $payloadIp = null;
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
            $payloadIp = $decodedPayload['IPAddress']
                ?? $decodedPayload['IpAddress']
                ?? $decodedPayload['IP']
                ?? $decodedPayload['Ip']
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
            'department' => $meta['department'] ?? $payloadDepartment,
            'model' => $meta['model'] ?? null,
            'equipment_id' => $meta['equipment_id'] ?? null,
            'ip_address' => $meta['ip_address'] ?? $payloadIp,
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
