<?php
/**
 * Command Center API
 * Endpoints for managing notification rules and viewing dashboard notifications
 */

require '../config.php';
require '../functions.php';

requireAuth();

define('MPS_ENGINE_ACCESS', true);
require_once __DIR__ . '/../../mps-api/callbacks/panel-message-common.php';  // Provides getNYTimestamp()
require_once __DIR__ . '/../../mps-api/callbacks/command-center-schema.php';
require_once __DIR__ . '/../../mps-api/callbacks/command-center-engine.php';
require_once __DIR__ . '/device-drilldown-enrichment.php';

// Get action from GET, POST, or JSON body
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// If no action in GET/POST, check JSON body
if (empty($action) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonInput = file_get_contents('php://input');
    $jsonData = json_decode($jsonInput, true);
    if (isset($jsonData['action'])) {
        $action = $jsonData['action'];
    }
}

$pdo = getDatabase();

// Ensure tables exist
ensureCommandCenterTables($pdo);

header('Content-Type: application/json');

function getAlertCodeMap(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }

    $map = [];
    $file = dirname(__DIR__, 2) . '/docs/MPSM_Code_Descriptions.md';

    if (!is_readable($file)) {
        return $map;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trim = trim($line);
        if ($trim === '' || stripos($trim, 'code') === 0) {
            continue;
        }
        $parts = preg_split('/\s+/', $trim, 2);
        if (count($parts) === 2) {
            $map[$parts[0]] = $parts[1];
        }
    }

    return $map;
}

function applyAlertMappingFallback(array &$notification): void
{
    $alertCode = (string)($notification['alert_code'] ?? '');
    if ($alertCode === '') {
        return;
    }

    $map = getAlertCodeMap();
    if (isset($map[$alertCode])) {
        if (empty($notification['alert_display_name'])) {
            $notification['alert_display_name'] = $map[$alertCode];
        }
        if (empty($notification['alert_description'])) {
            $notification['alert_description'] = $map[$alertCode];
        }
    } elseif (empty($notification['alert_display_name'])) {
        $notification['alert_display_name'] = "Alert {$alertCode}";
    }
}

function isTruthyFlag($value): bool
{
    if ($value === null) {
        return false;
    }
    $normalized = strtolower(trim((string)$value));
    return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
}

function tableExists(PDO $pdo, string $table): bool
{
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
        return (bool)$stmt->fetchColumn();
    } catch (Throwable $error) {
        error_log('Command center table existence check failed: ' . $error->getMessage());
        return false;
    }
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
        error_log("Command center index check/create failed for {$table}.{$indexName}: " . $error->getMessage());
    }
}

function ensureCommandCenterIndexes(PDO $pdo): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }
    $ensured = true;

    $notifTable = DB_PREFIX . 'dashboard_notifications';
    ensureIndexIfMissing(
        $pdo,
        $notifTable,
        'idx_dashboard_notifications_customer_code',
        "CREATE INDEX `idx_dashboard_notifications_customer_code` ON `{$notifTable}` (`customer_code`)"
    );
    ensureIndexIfMissing(
        $pdo,
        $notifTable,
        'idx_dashboard_notifications_status_customer_priority_created',
        "CREATE INDEX `idx_dashboard_notifications_status_customer_priority_created` ON `{$notifTable}` (`status`, `customer_code`, `priority`, `created_at_ny`)"
    );

    $panelTable = DB_PREFIX . 'panel_messages';
    ensureIndexIfMissing(
        $pdo,
        $panelTable,
        'idx_panel_messages_customer_received',
        "CREATE INDEX `idx_panel_messages_customer_received` ON `{$panelTable}` (`customer_code`, `received_at`)"
    );
}

function fetchDeviceMetadataForSerials(PDO $pdo, array $serials): array
{
    $devicesTable = DB_PREFIX . 'cache_devices';
    if (empty($serials) || !tableExists($pdo, $devicesTable)) {
        return [];
    }

    $serials = array_values(array_unique(array_filter(array_map(static function ($serial) {
        return trim((string)$serial);
    }, $serials))));
    if (empty($serials)) {
        return [];
    }

    $placeholders = [];
    $params = [];
    foreach ($serials as $index => $serial) {
        $token = ':serial_' . $index;
        $placeholders[] = $token;
        $params[$token] = $serial;
    }

    $sql = "SELECT serial_number,
                   customer_code,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Department')) AS department_1,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.department')) AS department_2,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.OfficeDescription')) AS department_3,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Note')) AS department_4,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Product.Model')) AS model_1,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Model')) AS model_2,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.ProductName')) AS model_3,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.EquipmentId')) AS equipment_id_1,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.EquipmentID')) AS equipment_id_2,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.DeviceIdentifier')) AS equipment_id_3,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.DeviceId')) AS device_id_1,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Id')) AS device_id_2,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IPAddress')) AS ip_1,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) AS ip_2,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IP')) AS ip_3,
                   JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Ip')) AS ip_4
            FROM {$devicesTable}
            WHERE serial_number IN (" . implode(',', $placeholders) . ")";

    $metadata = [];
    try {
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $serial = trim((string)($row['serial_number'] ?? ''));
            if ($serial === '') {
                continue;
            }
            $metadata[$serial] = [
                'customer_code' => $row['customer_code'] ?: null,
                'department' => $row['department_1'] ?: ($row['department_2'] ?: ($row['department_3'] ?: ($row['department_4'] ?: null))),
                'model' => $row['model_1'] ?: ($row['model_2'] ?: ($row['model_3'] ?: null)),
                'equipment_id' => $row['equipment_id_1'] ?: ($row['equipment_id_2'] ?: ($row['equipment_id_3'] ?: null)),
                'device_id' => $row['device_id_1'] ?: ($row['device_id_2'] ?: null),
                'ip_address' => $row['ip_1'] ?: ($row['ip_2'] ?: ($row['ip_3'] ?: ($row['ip_4'] ?: null)))
            ];
        }
    } catch (Throwable $error) {
        error_log('Command center device metadata query failed: ' . $error->getMessage());
    }

    return $metadata;
}

try {
    switch ($action) {
        case 'get_notifications':
            getNotifications($pdo);
            break;

        case 'get_customers':
            getCustomers($pdo);
            break;

        case 'get_rules':
            getRules($pdo);
            break;

        case 'create_rule':
            createRule($pdo);
            break;

        case 'update_rule':
            updateRule($pdo);
            break;

        case 'delete_rule':
            deleteRule($pdo);
            break;

        case 'toggle_rule':
            toggleRule($pdo);
            break;

        case 'acknowledge_notification':
            acknowledgeNotification($pdo);
            break;

        case 'dismiss_notification':
            dismissNotification($pdo);
            break;

        case 'get_aggregations':
            getAggregations($pdo);
            break;

        case 'get_alerts_feed':
            getAlertsFeed($pdo);
            break;

        case 'get_rule_history':
            getRuleHistory($pdo);
            break;

        // Alert Definitions CRUD
        case 'get_alert_definitions':
            getAlertDefinitions($pdo);
            break;

        case 'get_alert_definition':
            getAlertDefinition($pdo);
            break;

        case 'create_alert_definition':
            createAlertDefinition($pdo);
            break;

        case 'update_alert_definition':
            updateAlertDefinition($pdo);
            break;

        case 'delete_alert_definition':
            deleteAlertDefinition($pdo);
            break;

        case 'import_alert_definitions':
            importAlertDefinitions($pdo);
            break;

        case 'get_unmapped_alerts':
            getUnmappedAlerts($pdo);
            break;

        case 'lookup_alert_description':
            lookupAlertDescription($pdo);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function getNotifications(PDO $pdo): void
{
    ensureCommandCenterIndexes($pdo);

    $status = trim((string)($_GET['status'] ?? 'active'));
    $severity = $_GET['severity'] ?? null;
    $customerCode = isset($_GET['customerCode']) ? trim((string)$_GET['customerCode']) : null;
    if ($customerCode === '') {
        $customerCode = null;
    }
    $includeGlobal = isTruthyFlag($_GET['include_global'] ?? $_POST['include_global'] ?? null);
    $limit = min((int)($_GET['limit'] ?? 50), 100);
    $offset = max(0, (int)($_GET['offset'] ?? 0));

    $notifTable = DB_PREFIX . 'dashboard_notifications';
    $defsTable = DB_PREFIX . 'alert_definitions';
    $devicesTable = DB_PREFIX . 'cache_devices';

    $where = [];
    $params = [];
    if ($status !== '' && strtolower($status) !== 'all') {
        $where[] = "dn.status = :status";
        $params[':status'] = $status;
    }
    if ($severity) {
        $where[] = "dn.severity = :severity";
        $params[':severity'] = $severity;
    }
    if ($customerCode !== null) {
        if ($includeGlobal) {
            $where[] = "(dn.customer_code = :customer_code OR dn.customer_code IS NULL OR dn.customer_code = '')";
        } else {
            $where[] = "dn.customer_code = :customer_code";
        }
        $params[':customer_code'] = $customerCode;
    }

    $notificationsSql = "SELECT dn.*,
                                ad.display_name AS alert_display_name,
                                ad.description AS alert_description,
                                ad.category AS alert_category
                         FROM {$notifTable} dn
                         LEFT JOIN {$defsTable} ad
                                ON dn.alert_code = ad.alert_code
                               AND ad.enabled = 1
                         " . (!empty($where) ? "WHERE " . implode(' AND ', $where) : "") . "
                         ORDER BY dn.priority DESC, dn.created_at_ny DESC
                         LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($notificationsSql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $devicesBySerial = [];
    if (!empty($notifications) && tableExists($pdo, $devicesTable)) {
        $serials = array_values(array_unique(array_filter(array_map(static function ($notif) {
            return isset($notif['device_serial']) ? trim((string)$notif['device_serial']) : '';
        }, $notifications))));

        if (!empty($serials)) {
            $placeholders = [];
            $serialParams = [];
            foreach ($serials as $index => $serial) {
                $token = ':serial_' . $index;
                $placeholders[] = $token;
                $serialParams[$token] = $serial;
            }

            $deviceSql = "SELECT serial_number,
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
                $deviceStmt = $pdo->prepare($deviceSql);
                foreach ($serialParams as $key => $value) {
                    $deviceStmt->bindValue($key, $value);
                }
                $deviceStmt->execute();
                foreach ($deviceStmt->fetchAll(PDO::FETCH_ASSOC) as $deviceRow) {
                    $serial = trim((string)($deviceRow['serial_number'] ?? ''));
                    if ($serial === '') {
                        continue;
                    }
                    $devicesBySerial[$serial] = [
                        'department' => $deviceRow['department_1'] ?: ($deviceRow['department_2'] ?: ($deviceRow['department_3'] ?: ($deviceRow['department_4'] ?: null))),
                        'model' => $deviceRow['model'] ?: null,
                        'equipment_id' => $deviceRow['equipment_id_1'] ?: ($deviceRow['equipment_id_2'] ?: null),
                        'ip_address' => $deviceRow['ip_1'] ?: ($deviceRow['ip_2'] ?: ($deviceRow['ip_3'] ?: ($deviceRow['ip_4'] ?: null)))
                    ];
                }
            } catch (Throwable $deviceError) {
                error_log('Command center device enrichment query failed: ' . $deviceError->getMessage());
            }
        }
    }

    $totalCount = count($notifications);
    try {
        $countSql = "SELECT COUNT(*) FROM {$notifTable} dn " . (!empty($where) ? "WHERE " . implode(' AND ', $where) : "");
        $countStmt = $pdo->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $resolvedCount = $countStmt->fetchColumn();
        if ($resolvedCount !== false) {
            $totalCount = (int)$resolvedCount;
        }
    } catch (Throwable $countError) {
        error_log('Command center total count query failed: ' . $countError->getMessage());
    }

    foreach ($notifications as &$notif) {
        $displayName = $notif['alert_display_name'] ?? $notif['alert_code'];
        if ($displayName && $notif['alert_code']) {
            $notif['title'] = str_replace($notif['alert_code'], $displayName, $notif['title']);
            $notif['message'] = str_replace($notif['alert_code'], $displayName, $notif['message']);
        }
        $notif['alert_display_name'] = $displayName;
        applyAlertMappingFallback($notif);

        $serial = trim((string)($notif['device_serial'] ?? ''));
        $deviceMeta = ($serial !== '' && isset($devicesBySerial[$serial])) ? $devicesBySerial[$serial] : [];
        $notif['equipment_id'] = $deviceMeta['equipment_id'] ?? (($notif['device_identifier'] ?? null) ?: ($notif['device_serial'] ?? null));
        $notif['department'] = $deviceMeta['department'] ?? null;
        $notif['model'] = $deviceMeta['model'] ?? null;
        $notif['ip_address'] = $deviceMeta['ip_address'] ?? null;
        $notif['customer_description'] = $notif['customer_code'] ? ($notif['customer_description'] ?? $notif['customer_code']) : ($notif['customer_description'] ?? null);
    }
    unset($notif);

    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'count' => count($notifications),
        'total_count' => $totalCount,
        'filters' => [
            'status' => $status,
            'severity' => $severity,
            'customerCode' => $customerCode,
            'include_global' => $includeGlobal
        ]
    ]);
}

function getCustomers(PDO $pdo): void
{
    $customers = [];
    $seen = [];
    $addCustomer = static function ($code, $description = null) use (&$customers, &$seen): void {
        $code = trim((string)$code);
        if ($code === '' || isset($seen[$code])) {
            return;
        }
        $description = trim((string)($description ?? ''));
        $seen[$code] = true;
        $customers[] = [
            'customer_code' => $code,
            'customer_description' => $description !== '' ? $description : $code
        ];
    };

    $devicesTable = DB_PREFIX . 'cache_devices';
    if (tableExists($pdo, $devicesTable)) {
        try {
            $sql = "SELECT DISTINCT customer_code,
                           COALESCE(
                               JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Customer.Description')),
                               JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.CustomerDescription')),
                               JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.CustomerName'))
                           ) AS customer_description
                    FROM {$devicesTable}
                    WHERE customer_code IS NOT NULL AND customer_code <> ''
                    ORDER BY customer_code ASC";
            $stmt = $pdo->query($sql);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $addCustomer($row['customer_code'] ?? '', $row['customer_description'] ?? '');
            }
        } catch (Throwable $error) {
            error_log('Command center customer list query failed: ' . $error->getMessage());
        }
    }

    $panelMessagesTable = DB_PREFIX . 'panel_messages';
    if (tableExists($pdo, $panelMessagesTable)) {
        try {
            $sql = "SELECT customer_code, MAX(customer_description) AS customer_description
                    FROM {$panelMessagesTable}
                    WHERE customer_code IS NOT NULL AND customer_code <> ''
                    GROUP BY customer_code";
            $stmt = $pdo->query($sql);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $addCustomer($row['customer_code'] ?? '', $row['customer_description'] ?? '');
            }
        } catch (Throwable $error) {
            error_log('Command center panel customer list query failed: ' . $error->getMessage());
        }
    }

    $aggregationTable = DB_PREFIX . 'alert_aggregations';
    if (tableExists($pdo, $aggregationTable)) {
        try {
            $sql = "SELECT DISTINCT customer_code
                    FROM {$aggregationTable}
                    WHERE customer_code IS NOT NULL AND customer_code <> ''";
            $stmt = $pdo->query($sql);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $addCustomer($row['customer_code'] ?? '');
            }
        } catch (Throwable $error) {
            error_log('Command center aggregation customer list query failed: ' . $error->getMessage());
        }
    }

    $notificationsTable = DB_PREFIX . 'dashboard_notifications';
    if (tableExists($pdo, $notificationsTable)) {
        try {
            $sql = "SELECT DISTINCT customer_code
                    FROM {$notificationsTable}
                    WHERE customer_code IS NOT NULL AND customer_code <> ''";
            $stmt = $pdo->query($sql);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $addCustomer($row['customer_code'] ?? '');
            }
        } catch (Throwable $error) {
            error_log('Command center notification customer list query failed: ' . $error->getMessage());
        }
    }

    if (empty($customers)) {
        try {
            $apiResponse = callMPSQuery('Customer/GetCustomers', []);
            if (!empty($apiResponse['Result']) && is_array($apiResponse['Result'])) {
                foreach ($apiResponse['Result'] as $customer) {
                    $addCustomer($customer['Code'] ?? $customer['code'] ?? '', $customer['Description'] ?? $customer['description'] ?? '');
                }
            }
        } catch (Throwable $error) {
            error_log('Command center customer fallback API failed: ' . $error->getMessage());
        }
    }

    usort($customers, static function (array $a, array $b): int {
        return strcasecmp($a['customer_description'], $b['customer_description']);
    });

    echo json_encode([
        'success' => true,
        'customers' => $customers
    ]);
}

function getRules(PDO $pdo): void
{
    $table = DB_PREFIX . 'notification_rules';
    $sql = "SELECT * FROM {$table} ORDER BY severity DESC, enabled DESC, name ASC";

    $stmt = $pdo->query($sql);
    $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'rules' => $rules,
        'count' => count($rules)
    ]);
}

function createRule(PDO $pdo): void
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['name'])) {
        throw new Exception('Invalid rule data');
    }

    // Validate severity
    $validSeverities = ['info', 'warning', 'high', 'critical'];
    $severity = $data['severity'] ?? 'warning';
    if (!in_array($severity, $validSeverities)) {
        throw new Exception('Invalid severity level');
    }

    // Validate frequency type
    $validFreqTypes = ['same_device', 'same_alert', 'same_customer', 'any'];
    $freqType = $data['frequency_type'] ?? 'same_device';
    if (!in_array($freqType, $validFreqTypes)) {
        throw new Exception('Invalid frequency type');
    }

    // Validate numeric fields
    if (isset($data['frequency_count']) && (int)$data['frequency_count'] < 1) {
        throw new Exception('Frequency count must be at least 1');
    }

    if (isset($data['frequency_window_hours']) && (int)$data['frequency_window_hours'] < 1) {
        throw new Exception('Frequency window must be at least 1 hour');
    }

    if (isset($data['auto_dismiss_hours']) && (int)$data['auto_dismiss_hours'] < 1) {
        throw new Exception('Auto-dismiss hours must be at least 1');
    }

    $table = DB_PREFIX . 'notification_rules';
    $sql = "INSERT INTO {$table}
            (name, description, severity, enabled, alert_code_pattern,
             device_serial_pattern, customer_code_pattern, frequency_count,
             frequency_window_hours, frequency_type, show_dashboard,
             send_email, email_recipients, auto_dismiss_hours,
             notification_title, notification_message, created_by)
            VALUES (:name, :description, :severity, :enabled, :alert_pattern,
                    :device_pattern, :customer_pattern, :freq_count,
                    :freq_window, :freq_type, :show_dash, :send_email,
                    :email_recipients, :auto_dismiss, :notif_title,
                    :notif_message, :created_by)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $data['name'],
        ':description' => $data['description'] ?? null,
        ':severity' => $data['severity'] ?? 'warning',
        ':enabled' => $data['enabled'] ?? 1,
        ':alert_pattern' => $data['alert_code_pattern'] ?? null,
        ':device_pattern' => $data['device_serial_pattern'] ?? null,
        ':customer_pattern' => $data['customer_code_pattern'] ?? null,
        ':freq_count' => $data['frequency_count'] ?? null,
        ':freq_window' => $data['frequency_window_hours'] ?? null,
        ':freq_type' => $data['frequency_type'] ?? 'same_device',
        ':show_dash' => $data['show_dashboard'] ?? 1,
        ':send_email' => $data['send_email'] ?? 0,
        ':email_recipients' => $data['email_recipients'] ?? null,
        ':auto_dismiss' => $data['auto_dismiss_hours'] ?? null,
        ':notif_title' => $data['notification_title'] ?? null,
        ':notif_message' => $data['notification_message'] ?? null,
        ':created_by' => $_SESSION['user_id']
    ]);

    echo json_encode([
        'success' => true,
        'rule_id' => (int)$pdo->lastInsertId(),
        'message' => 'Rule created successfully'
    ]);
}

function updateRule(PDO $pdo): void
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['id'])) {
        throw new Exception('Rule ID required');
    }

    // Validate severity
    $validSeverities = ['info', 'warning', 'high', 'critical'];
    if (isset($data['severity']) && !in_array($data['severity'], $validSeverities)) {
        throw new Exception('Invalid severity level');
    }

    // Validate frequency type
    $validFreqTypes = ['same_device', 'same_alert', 'same_customer', 'any'];
    if (isset($data['frequency_type']) && !in_array($data['frequency_type'], $validFreqTypes)) {
        throw new Exception('Invalid frequency type');
    }

    // Validate numeric fields
    if (isset($data['frequency_count']) && (int)$data['frequency_count'] < 1) {
        throw new Exception('Frequency count must be at least 1');
    }

    if (isset($data['frequency_window_hours']) && (int)$data['frequency_window_hours'] < 1) {
        throw new Exception('Frequency window must be at least 1 hour');
    }

    if (isset($data['auto_dismiss_hours']) && (int)$data['auto_dismiss_hours'] < 1) {
        throw new Exception('Auto-dismiss hours must be at least 1');
    }

    $table = DB_PREFIX . 'notification_rules';
    $sql = "UPDATE {$table} SET
            name = :name,
            description = :description,
            severity = :severity,
            enabled = :enabled,
            alert_code_pattern = :alert_pattern,
            device_serial_pattern = :device_pattern,
            customer_code_pattern = :customer_pattern,
            frequency_count = :freq_count,
            frequency_window_hours = :freq_window,
            frequency_type = :freq_type,
            show_dashboard = :show_dash,
            send_email = :send_email,
            email_recipients = :email_recipients,
            auto_dismiss_hours = :auto_dismiss,
            notification_title = :notif_title,
            notification_message = :notif_message
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $data['id'],
        ':name' => $data['name'],
        ':description' => $data['description'] ?? null,
        ':severity' => $data['severity'],
        ':enabled' => $data['enabled'],
        ':alert_pattern' => $data['alert_code_pattern'] ?? null,
        ':device_pattern' => $data['device_serial_pattern'] ?? null,
        ':customer_pattern' => $data['customer_code_pattern'] ?? null,
        ':freq_count' => $data['frequency_count'] ?? null,
        ':freq_window' => $data['frequency_window_hours'] ?? null,
        ':freq_type' => $data['frequency_type'],
        ':show_dash' => $data['show_dashboard'],
        ':send_email' => $data['send_email'],
        ':email_recipients' => $data['email_recipients'] ?? null,
        ':auto_dismiss' => $data['auto_dismiss_hours'] ?? null,
        ':notif_title' => $data['notification_title'] ?? null,
        ':notif_message' => $data['notification_message'] ?? null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Rule updated successfully'
    ]);
}

function deleteRule(PDO $pdo): void
{
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

    if (!$id) {
        throw new Exception('Rule ID required');
    }

    $table = DB_PREFIX . 'notification_rules';

    // Check if rule exists
    $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE id = :id");
    $stmt->execute([':id' => $id]);
    if (!$stmt->fetch()) {
        throw new Exception('Rule not found');
    }

    $stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Rule deleted successfully'
    ]);
}

function toggleRule(PDO $pdo): void
{
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

    if (!$id) {
        throw new Exception('Rule ID required');
    }

    $table = DB_PREFIX . 'notification_rules';

    // Check if rule exists and get current state
    $stmt = $pdo->prepare("SELECT enabled FROM {$table} WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $rule = $stmt->fetch();
    if (!$rule) {
        throw new Exception('Rule not found');
    }

    $stmt = $pdo->prepare("UPDATE {$table} SET enabled = NOT enabled WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Rule toggled successfully',
        'new_state' => !$rule['enabled']
    ]);
}

function acknowledgeNotification(PDO $pdo): void
{
    $id = (int)($_POST['id'] ?? 0);

    if (!$id) {
        throw new Exception('Notification ID required');
    }

    $table = DB_PREFIX . 'dashboard_notifications';

    // Check if notification exists and is still active
    $stmt = $pdo->prepare("SELECT status FROM {$table} WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $notification = $stmt->fetch();

    if (!$notification) {
        throw new Exception('Notification not found');
    }

    if ($notification['status'] !== 'active') {
        throw new Exception('Notification already ' . $notification['status']);
    }

    $nyTime = getNYTimestamp();

    $stmt = $pdo->prepare("UPDATE {$table}
                           SET status = 'acknowledged',
                               acknowledged_by = :user_id,
                               acknowledged_at = :ny_time
                           WHERE id = :id");

    $stmt->execute([
        ':id' => $id,
        ':user_id' => $_SESSION['user_id'],
        ':ny_time' => $nyTime
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Notification acknowledged'
    ]);
}

function dismissNotification(PDO $pdo): void
{
    $id = (int)($_POST['id'] ?? 0);

    if (!$id) {
        throw new Exception('Notification ID required');
    }

    $table = DB_PREFIX . 'dashboard_notifications';

    // Check if notification exists and is still active
    $stmt = $pdo->prepare("SELECT status FROM {$table} WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $notification = $stmt->fetch();

    if (!$notification) {
        throw new Exception('Notification not found');
    }

    if ($notification['status'] !== 'active') {
        throw new Exception('Notification already ' . $notification['status']);
    }

    $nyTime = getNYTimestamp();

    $stmt = $pdo->prepare("UPDATE {$table}
                           SET status = 'dismissed',
                               dismissed_by = :user_id,
                               dismissed_at = :ny_time
                           WHERE id = :id");

    $stmt->execute([
        ':id' => $id,
        ':user_id' => $_SESSION['user_id'],
        ':ny_time' => $nyTime
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Notification dismissed'
    ]);
}

function commandCenterDecodeJson($value): array
{
    if (is_array($value)) {
        return $value;
    }
    if (!is_string($value) || trim($value) === '') {
        return [];
    }
    $decoded = json_decode($value, true);
    return is_array($decoded) ? $decoded : [];
}

function commandCenterFirstText(array $row, array $keys): string
{
    foreach ($keys as $key) {
        if (!array_key_exists($key, $row)) {
            continue;
        }
        $value = $row[$key];
        if (is_scalar($value)) {
            $text = trim((string)$value);
            if ($text !== '') {
                return $text;
            }
        }
        if (is_array($value)) {
            $nested = commandCenterFirstText($value, ['description', 'Description', 'name', 'Name', 'title', 'Title', 'value', 'Value']);
            if ($nested !== '') {
                return $nested;
            }
        }
    }
    return '';
}

function commandCenterNestedText(array $payload, array $paths): string
{
    foreach ($paths as $path) {
        $cursor = $payload;
        foreach ($path as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                $cursor = null;
                break;
            }
            $cursor = $cursor[$segment];
        }
        if (is_scalar($cursor)) {
            $text = trim((string)$cursor);
            if ($text !== '') {
                return $text;
            }
        }
    }
    return '';
}

function commandCenterAlertDefinitions(PDO $pdo): array
{
    static $definitions = null;
    if ($definitions !== null) {
        return $definitions;
    }

    $definitions = [];
    $table = DB_PREFIX . 'alert_definitions';
    if (!tableExists($pdo, $table)) {
        return $definitions;
    }

    try {
        $stmt = $pdo->query("SELECT alert_code, display_name, description, severity_override FROM {$table} WHERE enabled = 1");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $code = trim((string)($row['alert_code'] ?? ''));
            if ($code === '') {
                continue;
            }
            $definitions[$code] = [
                'display_name' => trim((string)($row['display_name'] ?? '')),
                'description' => trim((string)($row['description'] ?? '')),
                'severity' => trim((string)($row['severity_override'] ?? '')),
            ];
        }
    } catch (Throwable $error) {
        error_log('Command center alert definition load failed: ' . $error->getMessage());
    }

    return $definitions;
}

function commandCenterExtractAlertCode(array $row, string $fallback = ''): string
{
    $code = commandCenterFirstText($row, [
        'alert_code',
        'maintenance_alert_code',
        'MaintenanceAlertCode',
        'MaintenanceAlert_Code',
        'AlertCode',
        'Code',
        'code',
        'ErrorCode',
        'MaintenanceAlertId',
        'maintenance_alert_id',
        'Id',
        'id',
    ]);
    return $code !== '' ? $code : $fallback;
}

function commandCenterExtractPayloadDescription(array $payload): string
{
    $nested = commandCenterNestedText($payload, [
        ['maintenanceAlert', 'description'],
        ['maintenanceAlert', 'Description'],
        ['MaintenanceAlert', 'description'],
        ['MaintenanceAlert', 'Description'],
        ['alert', 'description'],
        ['Alert', 'Description'],
    ]);
    if ($nested !== '') {
        return $nested;
    }

    return commandCenterFirstText($payload, [
        'maintenanceAlertDescription',
        'MaintenanceAlert_Description',
        'alert_description',
        'AlertDescription',
        'Description',
        'description',
        'Message',
        'message',
        'Name',
        'name',
        'PanelConfiguration',
        'panel_configuration',
    ]);
}

function commandCenterResolveAlertText(PDO $pdo, string $code, array $context = []): array
{
    $code = trim($code);
    $definitions = commandCenterAlertDefinitions($pdo);
    if ($code !== '' && isset($definitions[$code])) {
        $definition = $definitions[$code];
        $text = $definition['display_name'] ?: ($definition['description'] ?: $code);
        return [
            'display_name' => $text,
            'description' => $definition['description'] ?: $text,
            'severity' => $definition['severity'] ?: null,
            'translation_source' => 'alert_definitions',
        ];
    }

    $payload = commandCenterDecodeJson($context['payload'] ?? []);
    $payloadDescription = commandCenterExtractPayloadDescription($payload);
    if ($payloadDescription !== '') {
        return [
            'display_name' => $payloadDescription,
            'description' => $payloadDescription,
            'severity' => null,
            'translation_source' => 'maintenance_payload',
        ];
    }

    $panelConfiguration = trim((string)($context['panel_configuration'] ?? ''));
    if ($panelConfiguration !== '') {
        return [
            'display_name' => $panelConfiguration,
            'description' => $panelConfiguration,
            'severity' => null,
            'translation_source' => 'panel_configuration',
        ];
    }

    $docsMap = getAlertCodeMap();
    if ($code !== '' && isset($docsMap[$code])) {
        return [
            'display_name' => $docsMap[$code],
            'description' => $docsMap[$code],
            'severity' => null,
            'translation_source' => 'docs',
        ];
    }

    $fallback = $code !== '' ? $code : 'Alert';
    return [
        'display_name' => $fallback,
        'description' => $fallback,
        'severity' => null,
        'translation_source' => 'raw',
    ];
}

function commandCenterNormalizeSeverity(?string $severity): string
{
    $value = strtolower(trim((string)$severity));
    if (in_array($value, ['critical', 'high', 'warning', 'info'], true)) {
        return $value;
    }
    if (in_array($value, ['error', 'danger', 'severe'], true)) {
        return 'critical';
    }
    if (in_array($value, ['warn', 'medium'], true)) {
        return 'warning';
    }
    return 'warning';
}

function commandCenterTimestampValue($value): int
{
    if (!$value) {
        return 0;
    }
    if (is_numeric($value)) {
        return (int)$value;
    }
    $timestamp = strtotime((string)$value);
    return $timestamp === false ? 0 : $timestamp;
}

function commandCenterAlertFeedKey(array $row, bool $includeSource = true): string
{
    $parts = [
        strtolower(trim((string)($row['customer_code'] ?? ''))),
        strtolower(trim((string)($row['device_serial'] ?? ''))),
        strtolower(trim((string)($row['alert_code'] ?? ''))),
    ];
    if ($includeSource) {
        $parts[] = strtolower(trim((string)($row['source'] ?? '')));
    }
    return implode('|', $parts);
}

function commandCenterMergeFeedRow(array &$rows, array &$baseKeys, array $row, bool $skipIfBaseExists = false): void
{
    $key = commandCenterAlertFeedKey($row, true);
    $baseKey = commandCenterAlertFeedKey($row, false);
    if ($skipIfBaseExists && isset($baseKeys[$baseKey])) {
        return;
    }

    if (!isset($rows[$key])) {
        $row['sources'] = array_values(array_unique(array_filter([$row['source'] ?? ''])));
        $rows[$key] = $row;
        $baseKeys[$baseKey] = true;
        return;
    }

    $existing = $rows[$key];
    $existingCount = (int)($existing['occurrence_count'] ?? 0);
    $rowCount = (int)($row['occurrence_count'] ?? 0);
    $existing['occurrence_count'] = max(1, $existingCount) + max(1, $rowCount);

    if (commandCenterTimestampValue($row['last_seen'] ?? null) >= commandCenterTimestampValue($existing['last_seen'] ?? null)) {
        foreach (['last_seen', 'alert_display_name', 'alert_description', 'translation_source', 'severity'] as $field) {
            if (!empty($row[$field])) {
                $existing[$field] = $row[$field];
            }
        }
    }

    foreach (['device_id', 'equipment_id', 'model', 'department', 'ip_address', 'customer_description'] as $field) {
        if (empty($existing[$field]) && !empty($row[$field])) {
            $existing[$field] = $row[$field];
        }
    }

    $sources = array_merge($existing['sources'] ?? [], [$row['source'] ?? '']);
    $existing['sources'] = array_values(array_unique(array_filter($sources)));
    $rows[$key] = $existing;
    $baseKeys[$baseKey] = true;
}

function commandCenterFeedRow(PDO $pdo, array $row, array $deviceMeta = [], array $context = []): array
{
    $code = commandCenterExtractAlertCode($row, trim((string)($context['alert_code'] ?? '')));
    $text = commandCenterResolveAlertText($pdo, $code, $context);
    $severity = commandCenterNormalizeSeverity($row['severity'] ?? $row['severity_override'] ?? $text['severity'] ?? null);
    $serial = trim((string)($row['device_serial'] ?? $row['serial_number'] ?? $deviceMeta['serial_number'] ?? ''));
    $customerCode = trim((string)($row['customer_code'] ?? $deviceMeta['customer_code'] ?? ''));
    $rowDisplayName = trim((string)($row['alert_display_name'] ?? ''));
    $rowDescription = trim((string)($row['alert_description'] ?? ''));
    $displayName = $text['translation_source'] === 'alert_definitions'
        ? $text['display_name']
        : ($rowDisplayName !== '' ? $rowDisplayName : $text['display_name']);
    $description = $text['translation_source'] === 'alert_definitions'
        ? $text['description']
        : ($rowDescription !== '' ? $rowDescription : $text['description']);

    return [
        'id' => $row['id'] ?? md5($customerCode . '|' . $serial . '|' . $code . '|' . ($context['source'] ?? 'alert')),
        'severity' => $severity,
        'alert_code' => $code,
        'alert_display_name' => $displayName,
        'alert_description' => $description,
        'translation_source' => $text['translation_source'],
        'model' => $row['model'] ?? $deviceMeta['model'] ?? '',
        'equipment_id' => $row['equipment_id'] ?? $deviceMeta['equipment_id'] ?? '',
        'device_serial' => $serial,
        'department' => $row['department'] ?? $deviceMeta['department'] ?? '',
        'ip_address' => $row['ip_address'] ?? $deviceMeta['ip_address'] ?? '',
        'occurrence_count' => (int)($row['occurrence_count'] ?? $row['trigger_count'] ?? $row['count_24h'] ?? 1),
        'last_seen' => $row['last_seen'] ?? $row['last_occurrence_ny'] ?? $row['created_at_ny'] ?? $row['cached_at'] ?? '',
        'source' => $context['source'] ?? ($row['source'] ?? 'alert'),
        'customer_code' => $customerCode,
        'customer_description' => $row['customer_description'] ?? $deviceMeta['customer_description'] ?? '',
        'device_id' => $row['device_id'] ?? $deviceMeta['device_id'] ?? null,
    ];
}

function appendPanelAggregationFeedRows(PDO $pdo, array &$rows, array &$baseKeys, string $customerCode, int $limit): void
{
    $aggTable = DB_PREFIX . 'alert_aggregations';
    $defsTable = DB_PREFIX . 'alert_definitions';
    $panelTable = DB_PREFIX . 'panel_messages';
    if (!tableExists($pdo, $aggTable)) {
        return;
    }

    $hasPanel = tableExists($pdo, $panelTable);
    $where = [];
    $params = [];
    if ($customerCode !== '') {
        $where[] = 'a.customer_code = :customer_code';
        $params[':customer_code'] = $customerCode;
    }
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $panelJoin = $hasPanel ? "LEFT JOIN {$panelTable} pm ON pm.id = a.latest_message_id" : '';
    $panelSelect = $hasPanel
        ? "pm.panel_configuration, pm.payload AS panel_payload, pm.customer_description"
        : "NULL AS panel_configuration, NULL AS panel_payload, NULL AS customer_description";

    $sql = "SELECT a.*,
                   ad.display_name AS alert_display_name,
                   ad.description AS alert_description,
                   ad.severity_override,
                   {$panelSelect}
            FROM {$aggTable} a
            LEFT JOIN {$defsTable} ad ON a.alert_code = ad.alert_code AND ad.enabled = 1
            {$panelJoin}
            {$whereSql}
            ORDER BY a.last_occurrence_ny DESC
            LIMIT :limit";

    try {
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', max($limit, 250), PDO::PARAM_INT);
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $error) {
        error_log('Command center alert feed aggregation query failed: ' . $error->getMessage());
        return;
    }

    $devicesBySerial = fetchDeviceMetadataForSerials($pdo, array_map(static function ($record) {
        return $record['device_serial'] ?? '';
    }, $records));

    foreach ($records as $record) {
        $serial = trim((string)($record['device_serial'] ?? ''));
        $deviceMeta = ($serial !== '' && isset($devicesBySerial[$serial])) ? $devicesBySerial[$serial] : [];
        if (empty($record['customer_code']) && !empty($deviceMeta['customer_code'])) {
            $record['customer_code'] = $deviceMeta['customer_code'];
        }
        $payload = commandCenterDecodeJson($record['latest_payload'] ?? []);
        if ($payload === []) {
            $payload = commandCenterDecodeJson($record['panel_payload'] ?? []);
        }
        $feedRow = commandCenterFeedRow($pdo, $record, $deviceMeta, [
            'source' => 'panel',
            'payload' => $payload,
            'panel_configuration' => $record['panel_configuration'] ?? '',
        ]);
        commandCenterMergeFeedRow($rows, $baseKeys, $feedRow);
    }
}

function appendMaintenanceFeedRows(PDO $pdo, array &$rows, array &$baseKeys, string $customerCode, int $limit): void
{
    $drilldownTable = DB_PREFIX . 'cache_device_drilldown';
    $devicesTable = DB_PREFIX . 'cache_devices';
    if (!tableExists($pdo, $drilldownTable) || !tableExists($pdo, $devicesTable)) {
        return;
    }

    $where = ['cd.is_uninstalled = 0'];
    $params = [];
    if ($customerCode !== '') {
        $where[] = 'cd.customer_code = :customer_code';
        $params[':customer_code'] = $customerCode;
    }
    $whereSql = 'WHERE ' . implode(' AND ', $where);

    $sql = "SELECT cdd.serial_number,
                   cdd.drilldown_data,
                   cdd.cached_at,
                   cd.customer_code,
                   cd.device_data
            FROM {$drilldownTable} cdd
            INNER JOIN {$devicesTable} cd ON cd.serial_number = cdd.serial_number
            {$whereSql}
            ORDER BY cdd.cached_at DESC
            LIMIT :limit";

    try {
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', max($limit, 500), PDO::PARAM_INT);
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $error) {
        error_log('Command center maintenance feed query failed: ' . $error->getMessage());
        return;
    }

    $serials = array_map(static function ($record) {
        return $record['serial_number'] ?? '';
    }, $records);
    $devicesBySerial = fetchDeviceMetadataForSerials($pdo, $serials);

    foreach ($records as $record) {
        $serial = trim((string)($record['serial_number'] ?? ''));
        $device = commandCenterDecodeJson($record['device_data'] ?? []);
        $drilldown = commandCenterDecodeJson($record['drilldown_data'] ?? []);
        if ($device === [] && $drilldown !== []) {
            $device = $drilldown;
        }
        if ($serial !== '') {
            $device['SerialNumber'] = $device['SerialNumber'] ?? $serial;
        }
        if (!empty($record['customer_code'])) {
            $device['CustomerCode'] = $device['CustomerCode'] ?? $record['customer_code'];
        }

        $normalized = mpsm_dd_normalize_payload($device, $drilldown);
        $alerts = $normalized['maintenance']['alerts'] ?? [];
        if (!is_array($alerts)) {
            continue;
        }

        $deviceMeta = ($serial !== '' && isset($devicesBySerial[$serial])) ? $devicesBySerial[$serial] : [];
        foreach ($alerts as $alert) {
            if (!is_array($alert)) {
                continue;
            }
            $code = commandCenterExtractAlertCode($alert);
            $description = commandCenterFirstText($alert, [
                'Description',
                'description',
                'MaintenanceAlertDescription',
                'MaintenanceAlert_Description',
                'Message',
                'message',
                'Name',
                'name',
            ]);
            $lastSeen = commandCenterFirstText($alert, [
                'DateUTC',
                'dateUTC',
                'CreatedAt',
                'created_at',
                'Date',
                'date',
                'LastSeen',
                'last_seen',
            ]);

            $row = [
                'id' => 'maintenance-' . md5($serial . '|' . $code . '|' . json_encode($alert)),
                'alert_code' => $code,
                'device_serial' => $serial,
                'customer_code' => $record['customer_code'] ?? ($deviceMeta['customer_code'] ?? ''),
                'occurrence_count' => 1,
                'last_seen' => $lastSeen ?: ($record['cached_at'] ?? ''),
            ];
            if ($description !== '') {
                $row['alert_display_name'] = $description;
                $row['alert_description'] = $description;
            }
            $feedRow = commandCenterFeedRow($pdo, $row, $deviceMeta, [
                'source' => 'maintenance',
                'payload' => $alert,
                'alert_code' => $code,
            ]);
            commandCenterMergeFeedRow($rows, $baseKeys, $feedRow);
        }
    }
}

function appendDashboardFallbackFeedRows(PDO $pdo, array &$rows, array &$baseKeys, string $customerCode, int $limit): void
{
    $notifTable = DB_PREFIX . 'dashboard_notifications';
    if (!tableExists($pdo, $notifTable)) {
        return;
    }

    $where = ["dn.status = 'active'"];
    $params = [];
    if ($customerCode !== '') {
        $where[] = 'dn.customer_code = :customer_code';
        $params[':customer_code'] = $customerCode;
    }

    $sql = "SELECT dn.*
            FROM {$notifTable} dn
            WHERE " . implode(' AND ', $where) . "
            ORDER BY dn.created_at_ny DESC
            LIMIT :limit";

    try {
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', max($limit, 250), PDO::PARAM_INT);
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $error) {
        error_log('Command center dashboard fallback feed query failed: ' . $error->getMessage());
        return;
    }

    $devicesBySerial = fetchDeviceMetadataForSerials($pdo, array_map(static function ($record) {
        return $record['device_serial'] ?? '';
    }, $records));

    foreach ($records as $record) {
        $serial = trim((string)($record['device_serial'] ?? ''));
        $deviceMeta = ($serial !== '' && isset($devicesBySerial[$serial])) ? $devicesBySerial[$serial] : [];
        $feedRow = commandCenterFeedRow($pdo, $record, $deviceMeta, [
            'source' => 'dashboard',
            'payload' => commandCenterDecodeJson($record['metadata'] ?? []),
        ]);
        commandCenterMergeFeedRow($rows, $baseKeys, $feedRow, true);
    }
}

function getAlertsFeed(PDO $pdo): void
{
    ensureCommandCenterIndexes($pdo);

    $customerCode = isset($_GET['customerCode']) ? trim((string)$_GET['customerCode']) : '';
    if (strtolower($customerCode) === 'all') {
        $customerCode = '';
    }
    $limit = min(max((int)($_GET['limit'] ?? 50), 1), 500);
    $offset = max(0, (int)($_GET['offset'] ?? 0));
    $sort = trim((string)($_GET['sort'] ?? 'last_seen'));
    $direction = strtolower(trim((string)($_GET['direction'] ?? 'desc'))) === 'asc' ? 'asc' : 'desc';
    $severityParam = isset($_GET['severity']) ? trim((string)$_GET['severity']) : '';
    $severity = ($severityParam !== '' && strtolower($severityParam) !== 'all')
        ? commandCenterNormalizeSeverity($severityParam)
        : '';

    $rows = [];
    $baseKeys = [];
    $sourceBudget = max($limit + $offset + 500, 10000);

    appendPanelAggregationFeedRows($pdo, $rows, $baseKeys, $customerCode, $sourceBudget);
    appendMaintenanceFeedRows($pdo, $rows, $baseKeys, $customerCode, $sourceBudget);
    appendDashboardFallbackFeedRows($pdo, $rows, $baseKeys, $customerCode, $sourceBudget);

    $alerts = array_values($rows);
    if ($severity !== '' && in_array($severity, ['critical', 'high', 'warning', 'info'], true)) {
        $alerts = array_values(array_filter($alerts, static function (array $alert) use ($severity): bool {
            return ($alert['severity'] ?? '') === $severity;
        }));
    }
    $allowedSorts = [
        'severity',
        'alert_code',
        'alert_display_name',
        'model',
        'equipment_id',
        'device_serial',
        'department',
        'ip_address',
        'occurrence_count',
        'last_seen',
        'source',
    ];
    if (!in_array($sort, $allowedSorts, true)) {
        $sort = 'last_seen';
    }

    usort($alerts, static function (array $a, array $b) use ($sort, $direction) {
        $multiplier = $direction === 'asc' ? 1 : -1;
        if ($sort === 'last_seen') {
            $av = commandCenterTimestampValue($a['last_seen'] ?? null);
            $bv = commandCenterTimestampValue($b['last_seen'] ?? null);
        } elseif ($sort === 'occurrence_count') {
            $av = (int)($a['occurrence_count'] ?? 0);
            $bv = (int)($b['occurrence_count'] ?? 0);
        } elseif ($sort === 'severity') {
            $rank = ['critical' => 4, 'high' => 3, 'warning' => 2, 'info' => 1];
            $av = $rank[$a['severity'] ?? ''] ?? 0;
            $bv = $rank[$b['severity'] ?? ''] ?? 0;
        } else {
            $av = strtolower((string)($a[$sort] ?? ''));
            $bv = strtolower((string)($b[$sort] ?? ''));
        }
        if ($av === $bv) {
            $av = commandCenterTimestampValue($a['last_seen'] ?? null);
            $bv = commandCenterTimestampValue($b['last_seen'] ?? null);
        }
        $comparison = $av <=> $bv;
        return $comparison === 0 ? 0 : $comparison * $multiplier;
    });

    $total = count($alerts);
    $paged = array_slice($alerts, $offset, $limit);
    $sources = [];
    foreach ($alerts as $alert) {
        foreach (($alert['sources'] ?? [$alert['source'] ?? '']) as $source) {
            if ($source !== '') {
                $sources[$source] = true;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'alerts' => $paged,
        'count' => count($paged),
        'total_count' => $total,
        'limit' => $limit,
        'offset' => $offset,
        'sort' => $sort,
        'direction' => $direction,
        'sources' => array_keys($sources),
        'cache' => [
            'source' => 'local',
            'generated_at' => date('c'),
        ],
    ]);
}

function getAggregations(PDO $pdo): void
{
    ensureCommandCenterIndexes($pdo);

    $limit = min((int)($_GET['limit'] ?? 50), 1000);
    $offset = max(0, (int)($_GET['offset'] ?? 0));
    $groupBy = $_GET['group_by'] ?? 'device_alert'; // 'device_alert' or 'alert_only'
    $severity = isset($_GET['severity']) ? trim((string)$_GET['severity']) : '';
    $customerCode = isset($_GET['customerCode']) ? trim((string)$_GET['customerCode']) : '';

    $aggTable = DB_PREFIX . 'alert_aggregations';
    $defsTable = DB_PREFIX . 'alert_definitions';
    if (!tableExists($pdo, $aggTable)) {
        echo json_encode([
            'success' => true,
            'aggregations' => [],
            'count' => 0,
            'total_count' => 0,
            'group_by' => $groupBy
        ]);
        return;
    }

    $where = [];
    $params = [];
    if ($customerCode !== '') {
        $where[] = 'a.customer_code = :customer_code';
        $params[':customer_code'] = $customerCode;
    }
    if ($severity !== '') {
        if ($severity === 'warning') {
            $where[] = "(ad.severity_override = :severity OR ad.severity_override IS NULL OR ad.severity_override = '')";
        } else {
            $where[] = 'ad.severity_override = :severity';
        }
        $params[':severity'] = $severity;
    }
    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    if ($groupBy === 'alert_only') {
        $sql = "SELECT
                    a.alert_code,
                    ad.display_name as alert_display_name,
                    ad.description as alert_description,
                    ad.category as alert_category,
                    ad.severity_override as severity_override,
                    COUNT(DISTINCT a.device_serial) as device_count,
                    SUM(a.count_1h) as count_1h,
                    SUM(a.count_24h) as count_24h,
                    SUM(a.count_7d) as count_7d,
                    SUM(a.count_30d) as count_30d,
                    SUM(a.occurrence_count) as occurrence_count,
                    MAX(a.last_occurrence_ny) as last_occurrence_ny
                FROM {$aggTable} a
                LEFT JOIN {$defsTable} ad ON a.alert_code = ad.alert_code AND ad.enabled = 1
                {$whereSql}
                GROUP BY a.alert_code, ad.display_name, ad.description, ad.category, ad.severity_override
                ORDER BY last_occurrence_ny DESC
                LIMIT :limit OFFSET :offset";
    } else {
        $sql = "SELECT a.*,
                       ad.display_name as alert_display_name,
                       ad.description as alert_description,
                       ad.category as alert_category,
                       ad.severity_override as severity_override
                FROM {$aggTable} a
                LEFT JOIN {$defsTable} ad ON a.alert_code = ad.alert_code AND ad.enabled = 1
                {$whereSql}
                ORDER BY a.last_occurrence_ny DESC
                LIMIT :limit OFFSET :offset";
    }

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $aggregations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $devicesBySerial = [];
    if ($groupBy !== 'alert_only' && !empty($aggregations)) {
        $devicesBySerial = fetchDeviceMetadataForSerials($pdo, array_map(static function ($row) {
            return $row['device_serial'] ?? '';
        }, $aggregations));
    }

    foreach ($aggregations as &$agg) {
        $agg['severity'] = $agg['severity_override'] ?: 'warning';
        $agg['alert_display_name'] = $agg['alert_display_name'] ?: ($agg['alert_code'] ?? 'Alert');
        applyAlertMappingFallback($agg);

        if ($groupBy !== 'alert_only') {
            $serial = trim((string)($agg['device_serial'] ?? ''));
            $deviceMeta = ($serial !== '' && isset($devicesBySerial[$serial])) ? $devicesBySerial[$serial] : [];
            $agg['device_id'] = $deviceMeta['device_id'] ?? null;
            $agg['equipment_id'] = $deviceMeta['equipment_id'] ?? ($serial ?: null);
            $agg['department'] = $deviceMeta['department'] ?? null;
            $agg['model'] = $deviceMeta['model'] ?? null;
            $agg['ip_address'] = $deviceMeta['ip_address'] ?? null;
            if (empty($agg['customer_code']) && !empty($deviceMeta['customer_code'])) {
                $agg['customer_code'] = $deviceMeta['customer_code'];
            }
        }
    }
    unset($agg);

    $totalCount = count($aggregations);
    try {
        if ($groupBy === 'alert_only') {
            $countSql = "SELECT COUNT(DISTINCT a.alert_code)
                         FROM {$aggTable} a
                         LEFT JOIN {$defsTable} ad ON a.alert_code = ad.alert_code AND ad.enabled = 1
                         {$whereSql}";
        } else {
            $countSql = "SELECT COUNT(*)
                         FROM {$aggTable} a
                         LEFT JOIN {$defsTable} ad ON a.alert_code = ad.alert_code AND ad.enabled = 1
                         {$whereSql}";
        }
        $countStmt = $pdo->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $resolvedCount = $countStmt->fetchColumn();
        if ($resolvedCount !== false) {
            $totalCount = (int)$resolvedCount;
        }
    } catch (Throwable $error) {
        error_log('Command center aggregation count query failed: ' . $error->getMessage());
    }

    echo json_encode([
        'success' => true,
        'aggregations' => $aggregations,
        'count' => count($aggregations),
        'total_count' => $totalCount,
        'group_by' => $groupBy
    ]);
}

function getRuleHistory(PDO $pdo): void
{
    $ruleId = (int)($_GET['rule_id'] ?? 0);
    $limit = min((int)($_GET['limit'] ?? 50), 100);

    $table = DB_PREFIX . 'rule_match_history';
    $sql = "SELECT * FROM {$table}";

    if ($ruleId) {
        $sql .= " WHERE rule_id = :rule_id";
    }

    $sql .= " ORDER BY matched_at_ny DESC LIMIT :limit";

    $stmt = $pdo->prepare($sql);

    if ($ruleId) {
        $stmt->bindValue(':rule_id', $ruleId, PDO::PARAM_INT);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'history' => $history,
        'count' => count($history)
    ]);
}

// ============================================================================
// Alert Definitions Functions
// ============================================================================

function getAlertDefinitions(PDO $pdo): void
{
    $category = $_GET['category'] ?? null;
    $search = $_GET['search'] ?? null;
    $limit = min((int)($_GET['limit'] ?? 100), 500);
    $offset = max(0, (int)($_GET['offset'] ?? 0));

    $table = DB_PREFIX . 'alert_definitions';
    $sql = "SELECT * FROM {$table} WHERE 1=1";
    $params = [];

    if ($category) {
        $sql .= " AND category = :category";
        $params[':category'] = $category;
    }

    if ($search) {
        $sql .= " AND (alert_code LIKE :search OR display_name LIKE :search OR description LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    $sql .= " ORDER BY category ASC, display_name ASC LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $definitions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get categories for filter dropdown
    $catStmt = $pdo->query("SELECT DISTINCT category FROM {$table} WHERE category IS NOT NULL ORDER BY category");
    $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'definitions' => $definitions,
        'count' => count($definitions),
        'categories' => $categories
    ]);
}

function getAlertDefinition(PDO $pdo): void
{
    $id = (int)($_GET['id'] ?? 0);
    $alertCode = $_GET['alert_code'] ?? null;

    if (!$id && !$alertCode) {
        throw new Exception('ID or alert_code required');
    }

    $table = DB_PREFIX . 'alert_definitions';

    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE alert_code = :code");
        $stmt->execute([':code' => $alertCode]);
    }

    $definition = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$definition) {
        throw new Exception('Alert definition not found');
    }

    echo json_encode([
        'success' => true,
        'definition' => $definition
    ]);
}

function createAlertDefinition(PDO $pdo): void
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['alert_code']) || !isset($data['display_name'])) {
        throw new Exception('alert_code and display_name are required');
    }

    $table = DB_PREFIX . 'alert_definitions';

    // Check if alert_code already exists
    $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE alert_code = :code");
    $stmt->execute([':code' => $data['alert_code']]);
    if ($stmt->fetch()) {
        throw new Exception('Alert code already has a definition');
    }

    $validSeverities = ['info', 'warning', 'high', 'critical'];
    if (isset($data['severity_override']) && $data['severity_override'] && !in_array($data['severity_override'], $validSeverities)) {
        throw new Exception('Invalid severity level');
    }

    $sql = "INSERT INTO {$table}
            (alert_code, display_name, description, category, severity_override,
             icon, color, source, original_description, enabled, created_by)
            VALUES (:code, :display_name, :description, :category, :severity,
                    :icon, :color, :source, :original, :enabled, :created_by)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':code' => $data['alert_code'],
        ':display_name' => $data['display_name'],
        ':description' => $data['description'] ?? null,
        ':category' => $data['category'] ?? null,
        ':severity' => $data['severity_override'] ?? null,
        ':icon' => $data['icon'] ?? null,
        ':color' => $data['color'] ?? null,
        ':source' => $data['source'] ?? 'manual',
        ':original' => $data['original_description'] ?? null,
        ':enabled' => $data['enabled'] ?? 1,
        ':created_by' => $_SESSION['user_id']
    ]);

    echo json_encode([
        'success' => true,
        'id' => (int)$pdo->lastInsertId(),
        'message' => 'Alert definition created successfully'
    ]);
}

function updateAlertDefinition(PDO $pdo): void
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['id'])) {
        throw new Exception('ID required');
    }

    $table = DB_PREFIX . 'alert_definitions';

    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE id = :id");
    $stmt->execute([':id' => $data['id']]);
    if (!$stmt->fetch()) {
        throw new Exception('Alert definition not found');
    }

    $validSeverities = ['info', 'warning', 'high', 'critical'];
    if (isset($data['severity_override']) && $data['severity_override'] && !in_array($data['severity_override'], $validSeverities)) {
        throw new Exception('Invalid severity level');
    }

    $sql = "UPDATE {$table} SET
            display_name = :display_name,
            description = :description,
            category = :category,
            severity_override = :severity,
            icon = :icon,
            color = :color,
            enabled = :enabled,
            updated_by = :updated_by
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $data['id'],
        ':display_name' => $data['display_name'],
        ':description' => $data['description'] ?? null,
        ':category' => $data['category'] ?? null,
        ':severity' => $data['severity_override'] ?? null,
        ':icon' => $data['icon'] ?? null,
        ':color' => $data['color'] ?? null,
        ':enabled' => $data['enabled'] ?? 1,
        ':updated_by' => $_SESSION['user_id']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Alert definition updated successfully'
    ]);
}

function deleteAlertDefinition(PDO $pdo): void
{
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

    if (!$id) {
        throw new Exception('ID required');
    }

    $table = DB_PREFIX . 'alert_definitions';

    $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE id = :id");
    $stmt->execute([':id' => $id]);
    if (!$stmt->fetch()) {
        throw new Exception('Alert definition not found');
    }

    $stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Alert definition deleted successfully'
    ]);
}

/*
CHANGELOG
2025-11-26 Codex
- get_aggregations: added support for alert-only grouping via `group_by=alert_only` with device_count and summed window counts.
- get_notifications: minor filter wiring retained for customerCode compatibility with UI filter.
*/

function importAlertDefinitions(PDO $pdo): void
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['definitions']) || !is_array($data['definitions'])) {
        throw new Exception('definitions array required');
    }

    $table = DB_PREFIX . 'alert_definitions';
    $imported = 0;
    $skipped = 0;
    $errors = [];

    foreach ($data['definitions'] as $def) {
        if (!isset($def['alert_code']) || !isset($def['display_name'])) {
            $errors[] = "Missing alert_code or display_name";
            continue;
        }

        // Check if exists
        $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE alert_code = :code");
        $stmt->execute([':code' => $def['alert_code']]);

        if ($stmt->fetch()) {
            if ($data['skip_existing'] ?? true) {
                $skipped++;
                continue;
            }
            // Update existing
            $sql = "UPDATE {$table} SET
                    display_name = :display_name,
                    description = :description,
                    category = :category,
                    original_description = :original,
                    source = :source,
                    updated_by = :updated_by
                    WHERE alert_code = :code";
        } else {
            // Insert new
            $sql = "INSERT INTO {$table}
                    (alert_code, display_name, description, category, original_description, source, created_by)
                    VALUES (:code, :display_name, :description, :category, :original, :source, :updated_by)";
        }

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':code' => $def['alert_code'],
                ':display_name' => $def['display_name'],
                ':description' => $def['description'] ?? null,
                ':category' => $def['category'] ?? null,
                ':original' => $def['original_description'] ?? null,
                ':source' => $data['source'] ?? 'import',
                ':updated_by' => $_SESSION['user_id']
            ]);
            $imported++;
        } catch (Exception $e) {
            $errors[] = "Error importing {$def['alert_code']}: {$e->getMessage()}";
        }
    }

    echo json_encode([
        'success' => true,
        'imported' => $imported,
        'skipped' => $skipped,
        'errors' => $errors
    ]);
}

function getUnmappedAlerts(PDO $pdo): void
{
    $limit = min((int)($_GET['limit'] ?? 50), 200);

    $messagesTable = DB_PREFIX . 'panel_messages';
    $defsTable = DB_PREFIX . 'alert_definitions';

    // Find alert codes in panel_messages that don't have definitions
    $sql = "SELECT pm.maintenance_alert_code as alert_code,
                   pm.panel_configuration as original_description,
                   COUNT(*) as occurrence_count,
                   MAX(pm.ny_received_at) as last_seen
            FROM {$messagesTable} pm
            LEFT JOIN {$defsTable} ad ON pm.maintenance_alert_code = ad.alert_code
            WHERE pm.maintenance_alert_code IS NOT NULL
              AND pm.maintenance_alert_code != ''
              AND ad.id IS NULL
            GROUP BY pm.maintenance_alert_code, pm.panel_configuration
            ORDER BY occurrence_count DESC
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $unmapped = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'unmapped' => $unmapped,
        'count' => count($unmapped)
    ]);
}

function lookupAlertDescription(PDO $pdo): void
{
    $alertCode = $_GET['alert_code'] ?? null;

    if (!$alertCode) {
        throw new Exception('alert_code required');
    }

    $table = DB_PREFIX . 'alert_definitions';

    $stmt = $pdo->prepare("SELECT display_name, description, category, severity_override, icon, color
                           FROM {$table}
                           WHERE alert_code = :code AND enabled = 1");
    $stmt->execute([':code' => $alertCode]);
    $definition = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($definition) {
        echo json_encode([
            'success' => true,
            'found' => true,
            'display_name' => $definition['display_name'],
            'description' => $definition['description'],
            'category' => $definition['category'],
            'severity_override' => $definition['severity_override'],
            'icon' => $definition['icon'],
            'color' => $definition['color']
        ]);
        return;
    }

    $map = getAlertCodeMap();
    if (isset($map[$alertCode])) {
        echo json_encode([
            'success' => true,
            'found' => true,
            'display_name' => $map[$alertCode],
            'description' => $map[$alertCode],
            'category' => null,
            'severity_override' => null,
            'icon' => null,
            'color' => null
        ]);
        return;
    } else {
        echo json_encode([
            'success' => true,
            'found' => false,
            'display_name' => $alertCode,
            'description' => null
        ]);
    }
}

/*
CHANGELOG
2025-11-22 Codex
- Added optional customerCode filter to dashboard notifications to scope alerts to the currently viewed customer.
2025-11-23 Codex
- Relaxed customer scoping to include legacy notifications missing customer_code.
- Include notifications with NULL/empty customer_code (legacy/global alerts) alongside customer-specific ones.
2025-11-24 Codex
- Added alert definitions CRUD endpoints for managing alert code to description mappings
- Added get_alert_definitions, get_alert_definition, create_alert_definition, update_alert_definition, delete_alert_definition
- Added import_alert_definitions for bulk import from spreadsheet data
- Added get_unmapped_alerts to find alert codes without definitions
- Added lookup_alert_description for real-time alert code to display name resolution
- Merged getNotifications to include both customer filtering AND alert definitions JOIN
2025-11-25 Codex
- Added fallback mapping from docs/MPSM_Code_Descriptions.md so alert display names/descriptions always resolve for system alerts and lookup endpoints.
2025-11-27 Codex
- CRITICAL FIX: Added JSON body parsing for action parameter to fix "Invalid action" error when saving rules.
- Rule create/update requests send action in JSON body, but API was only checking GET/POST.
- Now checks JSON body if action not found in GET/POST parameters.
*/
