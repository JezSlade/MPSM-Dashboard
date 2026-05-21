<?php
/**
 * Device Deep Dive API
 *
 * Cache-first device detail endpoint. Live MPS calls are made only when
 * refresh=1 is requested, so opening a device modal does not block on vendor
 * endpoint fan-out.
 */

require '../config.php';
require '../functions.php';
require_once __DIR__ . '/device-drilldown-enrichment.php';

requireAuth();

set_time_limit(60);
ini_set('max_execution_time', '60');

function mpsm_deep_bool_param(string $name): bool
{
    $value = strtolower(trim((string)($_GET[$name] ?? '')));
    return in_array($value, ['1', 'true', 'yes', 'on'], true);
}

function mpsm_deep_int_param(string $name, int $default, int $min, int $max): int
{
    $value = isset($_GET[$name]) ? (int)$_GET[$name] : $default;
    return max($min, min($max, $value));
}

function mpsm_deep_table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
    return $stmt && $stmt->rowCount() > 0;
}

function mpsm_deep_load_cached_device(PDO $pdo, string $prefix, string $deviceId, string $serialNumber, string $customerCode): ?array
{
    $params = [];
    $where = '';

    if ($serialNumber !== '' && $deviceId !== '') {
        $where = "(serial_number = :serial
               OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Id')) = :id1
               OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.DeviceId')) = :id2
               OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IdInstalledProduct')) = :id3)";
        $params[':serial'] = $serialNumber;
        $params[':id1'] = $deviceId;
        $params[':id2'] = $deviceId;
        $params[':id3'] = $deviceId;
    } elseif ($serialNumber !== '') {
        $where = 'serial_number = :serial';
        $params[':serial'] = $serialNumber;
    } elseif ($deviceId !== '') {
        $where = "(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Id')) = :id1
               OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.DeviceId')) = :id2
               OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IdInstalledProduct')) = :id3)";
        $params[':id1'] = $deviceId;
        $params[':id2'] = $deviceId;
        $params[':id3'] = $deviceId;
    } else {
        return null;
    }

    if ($customerCode !== '') {
        $where .= ' AND customer_code = :customer';
        $params[':customer'] = $customerCode;
    }

    $stmt = $pdo->prepare("
        SELECT serial_number, customer_code, device_data, cached_at
        FROM {$prefix}cache_devices
        WHERE {$where}
        LIMIT 1
    ");
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || empty($row['device_data'])) {
        return null;
    }

    $device = json_decode((string)$row['device_data'], true);
    if (!is_array($device)) {
        return null;
    }

    return [
        'device' => $device,
        'serial_number' => $row['serial_number'] ?? '',
        'customer_code' => $row['customer_code'] ?? '',
        'cached_at' => $row['cached_at'] ?? null
    ];
}

function mpsm_deep_load_cached_drilldown(PDO $pdo, string $prefix, string $serialNumber): ?array
{
    if ($serialNumber === '') {
        return null;
    }

    $stmt = $pdo->prepare("
        SELECT drilldown_data, cached_at, has_alerts, has_supplies
        FROM {$prefix}cache_device_drilldown
        WHERE serial_number = :serial
        LIMIT 1
    ");
    $stmt->execute([':serial' => $serialNumber]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || empty($row['drilldown_data'])) {
        return null;
    }

    $drilldown = json_decode((string)$row['drilldown_data'], true);
    if (!is_array($drilldown)) {
        return null;
    }

    return [
        'drilldown' => $drilldown,
        'cached_at' => $row['cached_at'] ?? null,
        'has_alerts' => (bool)($row['has_alerts'] ?? false),
        'has_supplies' => (bool)($row['has_supplies'] ?? false)
    ];
}

function mpsm_deep_alert_definitions(PDO $pdo, string $prefix): array
{
    $definitions = [];
    $table = $prefix . 'alert_definitions';

    if (!mpsm_deep_table_exists($pdo, $table)) {
        return $definitions;
    }

    try {
        $stmt = $pdo->query("SELECT * FROM {$table} WHERE enabled = 1");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $definition) {
            $code = (string)($definition['alert_code'] ?? '');
            if ($code === '') {
                continue;
            }
            $definitions[$code] = $definition;
        }
    } catch (Throwable $e) {
        error_log('[device-deep-dive] Failed to load alert definitions: ' . $e->getMessage());
    }

    return $definitions;
}

function mpsm_deep_normalize_serial(string $serial): string
{
    return strtolower(preg_replace('/[^a-z0-9]/i', '', $serial));
}

function mpsm_deep_panel_summary(array $row, ?array $payload, array $definitions): array
{
    $code = (string)($row['maintenance_alert_code'] ?? '');
    $definition = $code !== '' ? ($definitions[$code] ?? null) : null;
    $displayName = $definition['display_name'] ?? null;
    $description = $definition['description'] ?? null;

    if (!$displayName && is_array($payload)) {
        $displayName = $payload['maintenanceAlert']['description']
            ?? $payload['MaintenanceAlert_Description']
            ?? $payload['alert_description']
            ?? $payload['message']
            ?? null;
    }

    if (!$displayName) {
        $displayName = $row['panel_configuration'] ?? $code ?: 'Alert';
    }

    $summary = $description ?: $displayName;

    if (is_array($payload)) {
        $summary = $payload['maintenanceAlert']['message']
            ?? $payload['panel_message']
            ?? $payload['runtime_message']
            ?? $summary;
    }

    return [
        'display_name' => $displayName,
        'summary' => $summary,
        'category' => $definition['category'] ?? null,
        'severity' => $definition['severity'] ?? null
    ];
}

function mpsm_deep_format_panel_rows(array $rows, array $definitions, bool $includeRaw): array
{
    $messages = [];

    foreach ($rows as $row) {
        $payload = null;
        if (!empty($row['payload'])) {
            $decoded = json_decode((string)$row['payload'], true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        $summary = mpsm_deep_panel_summary($row, $payload, $definitions);
        $message = [
            'id' => (int)($row['id'] ?? 0),
            'received_at' => $row['received_at'] ?? null,
            'customer_code' => $row['customer_code'] ?? null,
            'customer_description' => $row['customer_description'] ?? null,
            'device_serial' => $row['device_serial'] ?? null,
            'maintenance_alert_code' => $row['maintenance_alert_code'] ?? null,
            'maintenance_alert_id' => $row['maintenance_alert_id'] ?? null,
            'panel_configuration' => $row['panel_configuration'] ?? null,
            'display_name' => $summary['display_name'],
            'summary' => $summary['summary'],
            'category' => $summary['category'],
            'severity' => $summary['severity'],
        ];

        if ($includeRaw) {
            $message['payload'] = $payload ?? ($row['payload'] ?? null);
        }

        $messages[] = $message;
    }

    return $messages;
}

function mpsm_deep_payload_matches(?array $payload, string $normalizedSerial, string $deviceId): bool
{
    if (!$payload) {
        return false;
    }

    foreach (['device_serial', 'serialNumber', 'DeviceSerialNumber', 'serial_number'] as $key) {
        if (!empty($payload[$key]) && mpsm_deep_normalize_serial((string)$payload[$key]) === $normalizedSerial) {
            return true;
        }
    }

    if ($deviceId !== '') {
        foreach (['device_id', 'DeviceId', 'IdInstalledProduct'] as $key) {
            if (!empty($payload[$key]) && (string)$payload[$key] === $deviceId) {
                return true;
            }
        }
    }

    return false;
}

function mpsm_deep_panel_history(PDO $pdo, string $prefix, string $serialNumber, string $deviceId, string $customerCode, int $limit, int $offset, bool $includeRaw): array
{
    $table = $prefix . 'panel_messages';
    if ($serialNumber === '' || !mpsm_deep_table_exists($pdo, $table)) {
        return [
            'total' => 0,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => false,
            'source' => 'none',
            'messages' => []
        ];
    }

    $definitions = mpsm_deep_alert_definitions($pdo, $prefix);
    $where = 'device_serial = :serial';
    $params = [':serial' => $serialNumber];

    if ($customerCode !== '') {
        $where .= ' AND customer_code = :customer';
        $params[':customer'] = $customerCode;
    }

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE {$where}");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    if ($total > 0) {
        $stmt = $pdo->prepare("
            SELECT id, received_at, customer_code, customer_description, device_serial,
                   maintenance_alert_code, maintenance_alert_id, panel_configuration, payload
            FROM {$table}
            WHERE {$where}
            ORDER BY received_at DESC
            LIMIT :limit OFFSET :offset
        ");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $total,
            'source' => 'indexed',
            'messages' => mpsm_deep_format_panel_rows($rows, $definitions, $includeRaw)
        ];
    }

    $fallbackParams = [];
    $fallbackWhere = '1=1';
    if ($customerCode !== '') {
        $fallbackWhere .= ' AND customer_code = :customer';
        $fallbackParams[':customer'] = $customerCode;
    }

    $fallbackWindow = min(2000, max(300, ($limit + $offset) * 4));
    $fallbackStmt = $pdo->prepare("
        SELECT id, received_at, customer_code, customer_description, device_serial,
               maintenance_alert_code, maintenance_alert_id, panel_configuration, payload
        FROM {$table}
        WHERE {$fallbackWhere}
        ORDER BY received_at DESC
        LIMIT :fallback_limit
    ");
    foreach ($fallbackParams as $key => $value) {
        $fallbackStmt->bindValue($key, $value);
    }
    $fallbackStmt->bindValue(':fallback_limit', $fallbackWindow, PDO::PARAM_INT);
    $fallbackStmt->execute();

    $normalizedSerial = mpsm_deep_normalize_serial($serialNumber);
    $matches = [];
    foreach ($fallbackStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $rowSerial = mpsm_deep_normalize_serial((string)($row['device_serial'] ?? ''));
        $payload = !empty($row['payload']) ? json_decode((string)$row['payload'], true) : null;
        if ($rowSerial === $normalizedSerial || mpsm_deep_payload_matches(is_array($payload) ? $payload : null, $normalizedSerial, $deviceId)) {
            $matches[] = $row;
        }
    }

    $slice = array_slice($matches, $offset, $limit);

    return [
        'total' => count($matches),
        'limit' => $limit,
        'offset' => $offset,
        'has_more' => ($offset + $limit) < count($matches),
        'source' => 'bounded_payload_fallback',
        'messages' => mpsm_deep_format_panel_rows($slice, $definitions, $includeRaw)
    ];
}

$deviceId = trim((string)($_GET['deviceId'] ?? ''));
$serialNumber = trim((string)($_GET['serialNumber'] ?? ''));
$customerCode = trim((string)($_GET['customerCode'] ?? ''));
$refresh = mpsm_deep_bool_param('refresh');
$includeRaw = mpsm_deep_bool_param('includeRaw');
$historyLimit = mpsm_deep_int_param('historyLimit', 150, 1, 500);
$historyOffset = mpsm_deep_int_param('historyOffset', 0, 0, 1000000);

if ($deviceId === '' && $serialNumber === '') {
    jsonError('Device ID or Serial Number required', 400);
}

try {
    $pdo = getDatabase();
    $prefix = DB_PREFIX;

    if (!mpsm_deep_table_exists($pdo, $prefix . 'cache_devices')) {
        jsonError('Device cache is not initialized', 503);
    }

    $drilldownTableExists = mpsm_deep_table_exists($pdo, $prefix . 'cache_device_drilldown');
    $cacheDevice = mpsm_deep_load_cached_device($pdo, $prefix, $deviceId, $serialNumber, $customerCode);
    $device = $cacheDevice['device'] ?? null;
    $deviceCachedAt = $cacheDevice['cached_at'] ?? null;

    if ($device) {
        $serialNumber = mpsm_dd_device_serial($device, $serialNumber);
        $customerCode = mpsm_dd_customer_code($device, $customerCode);
        $deviceId = mpsm_dd_device_id($device, $deviceId);
    }

    $cacheDrilldown = $drilldownTableExists ? mpsm_deep_load_cached_drilldown($pdo, $prefix, $serialNumber) : null;
    $drilldown = $cacheDrilldown['drilldown'] ?? null;

    if (!$device && $drilldown) {
        $deviceCandidate = mpsm_dd_extract_device_from_drilldown($drilldown);
        if ($deviceCandidate) {
            $device = $deviceCandidate;
            $serialNumber = mpsm_dd_device_serial($device, $serialNumber);
            $customerCode = mpsm_dd_customer_code($device, $customerCode);
            $deviceId = mpsm_dd_device_id($device, $deviceId);
        }
    }

    $refreshErrors = [];
    if ($refresh) {
        $seed = $device ?: [
            'Id' => $deviceId,
            'SerialNumber' => $serialNumber,
            'CustomerCode' => $customerCode
        ];
        $enriched = mpsm_dd_enrich_device_payload($seed, [
            'deviceId' => $deviceId,
            'serialNumber' => $serialNumber,
            'customerCode' => $customerCode
        ]);
        $drilldown = $enriched['drilldown'];
        $refreshErrors = $enriched['sectionErrors'] ?? [];

        $deviceCandidate = mpsm_dd_extract_device_from_drilldown($drilldown);
        if ($deviceCandidate) {
            $device = $deviceCandidate;
            $serialNumber = mpsm_dd_device_serial($device, $serialNumber);
            $customerCode = mpsm_dd_customer_code($device, $customerCode);
            $deviceId = mpsm_dd_device_id($device, $deviceId);
        }

        if ($serialNumber !== '' && $drilldownTableExists) {
            mpsm_dd_save_drilldown($pdo, $prefix . 'cache_device_drilldown', 'serial_number', $serialNumber, $drilldown);
            $cacheDrilldown = mpsm_deep_load_cached_drilldown($pdo, $prefix, $serialNumber);
        }
    }

    if (!$device) {
        jsonError($refresh ? 'Device not found' : 'Device not found in local cache', 404);
    }

    if (!$drilldown && $serialNumber !== '' && $drilldownTableExists) {
        $cacheDrilldown = mpsm_deep_load_cached_drilldown($pdo, $prefix, $serialNumber);
        $drilldown = $cacheDrilldown['drilldown'] ?? null;
    }

    $normalized = mpsm_dd_normalize_payload($device, $drilldown);
    $panelHistory = mpsm_deep_panel_history($pdo, $prefix, $serialNumber, $deviceId, $customerCode, $historyLimit, $historyOffset, $includeRaw);
    $sectionErrors = array_merge($normalized['sectionErrors'] ?? [], $refreshErrors);
    $errors = [];
    foreach ($sectionErrors as $section => $message) {
        $errors[] = "{$section}: {$message}";
    }

    $response = [
        'device' => $device,
        'counterDetails' => $normalized['counterDetails'],
        'deviceHealth' => $normalized['deviceHealth'],
        'supplyAlerts' => $normalized['supplyAlerts'],
        'panelHistory' => $panelHistory,
        'errors' => $errors,
        'counters' => $normalized['counters'],
        'supplies' => $normalized['supplies'],
        'maintenance' => $normalized['maintenance'],
        'alerts' => array_merge($normalized['alerts'], [
            'panel' => $panelHistory['messages']
        ]),
        'sectionErrors' => $sectionErrors,
        'cache' => [
            'device_cached_at' => $deviceCachedAt,
            'drilldown_cached_at' => $cacheDrilldown['cached_at'] ?? null,
            'drilldown_schema_version' => is_array($drilldown) ? ($drilldown['_mpsm']['schemaVersion'] ?? 1) : null,
            'drilldown_has_alerts' => $cacheDrilldown['has_alerts'] ?? null,
            'drilldown_has_supplies' => $cacheDrilldown['has_supplies'] ?? null,
            'refreshed' => $refresh
        ]
    ];

    if ($includeRaw && $drilldown) {
        $response['drilldownCache'] = $drilldown;
    }

    jsonSuccess($response);
} catch (Throwable $e) {
    error_log('[device-deep-dive] ' . $e->getMessage());
    jsonError('Failed to fetch device data: ' . $e->getMessage());
}

/*
CHANGELOG
2026-05-21 Codex
- Reworked device drill-down as cache-first by default, added targeted refresh,
  normalized maintenance/supply/counter/alert sections, and replaced the global
  latest-300 panel scan with indexed device history plus bounded fallback.
*/
