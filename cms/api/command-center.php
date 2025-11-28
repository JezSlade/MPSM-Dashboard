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

try {
    switch ($action) {
        case 'get_notifications':
            getNotifications($pdo);
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
    $status = $_GET['status'] ?? 'active';
    $severity = $_GET['severity'] ?? null;
    $customerCode = $_GET['customerCode'] ?? null;
    $limit = min((int)($_GET['limit'] ?? 50), 100);

    $notifTable = DB_PREFIX . 'dashboard_notifications';
    $defsTable = DB_PREFIX . 'alert_definitions';

    // Join with alert_definitions to get display names for alert codes
    // Also support customer code filtering (weekend work)
    $devicesTable = DB_PREFIX . 'cache_devices';
    $sql = "SELECT dn.*,
                   ad.display_name as alert_display_name,
                   ad.description as alert_description,
                   ad.category as alert_category,
                   COALESCE(
                       JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.Department')),
                       JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.department')),
                       JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.OfficeDescription')),
                       JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.Note'))
                   ) AS device_department,
                   JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.Product.Model')) AS device_model,
                   JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.EquipmentId')) AS device_equipment_id,
                   JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.EquipmentID')) AS device_equipment_id_alt
            FROM {$notifTable} dn
            LEFT JOIN {$defsTable} ad ON dn.alert_code = ad.alert_code AND ad.enabled = 1
            LEFT JOIN {$devicesTable} cd ON cd.serial_number = dn.device_serial
            WHERE dn.status = :status";

    // Add severity filter if provided
    if ($severity) {
        $sql .= " AND dn.severity = :severity";
    }

    // Restrict to current customer if provided
    // Show notifications that either:
    // 1. Match the customer code exactly, OR
    // 2. Have no customer code (legacy/global notifications)
    if ($customerCode) {
        $sql .= " AND (
            LOWER(TRIM(dn.customer_code)) = LOWER(TRIM(:customer_code))
            OR dn.customer_code IS NULL
            OR dn.customer_code = ''
        )";
    }

    $sql .= " ORDER BY dn.priority DESC, dn.created_at_ny DESC LIMIT :limit";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':status', $status);

        if ($severity) {
            $stmt->bindValue(':severity', $severity);
        }

        if ($customerCode) {
            $stmt->bindValue(':customer_code', $customerCode);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // If cache_devices table doesn't exist or query fails, fall back to notifications without device metadata
        error_log("Command center query failed (possibly missing cache_devices table): " . $e->getMessage());

        // Fallback query without device metadata
        $sql = "SELECT dn.*,
                       ad.display_name as alert_display_name,
                       ad.description as alert_description,
                       ad.category as alert_category
                FROM {$notifTable} dn
                LEFT JOIN {$defsTable} ad ON dn.alert_code = ad.alert_code AND ad.enabled = 1
                WHERE dn.status = :status";

        if ($severity) {
            $sql .= " AND dn.severity = :severity";
        }

        if ($customerCode) {
            $sql .= " AND (
                LOWER(TRIM(dn.customer_code)) = LOWER(TRIM(:customer_code))
                OR dn.customer_code IS NULL
                OR dn.customer_code = ''
            )";
        }

        $sql .= " ORDER BY dn.priority DESC, dn.created_at_ny DESC LIMIT :limit";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':status', $status);

        if ($severity) {
            $stmt->bindValue(':severity', $severity);
        }

        if ($customerCode) {
            $stmt->bindValue(':customer_code', $customerCode);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Process notifications to replace alert_code with display_name in title/message and hydrate device metadata
    foreach ($notifications as &$notif) {
        // If we have a display name, use it; otherwise keep the original alert_code
        $displayName = $notif['alert_display_name'] ?? $notif['alert_code'];

        // Replace {alert} placeholder with display name in title and message
        if ($displayName && $notif['alert_code']) {
            $notif['title'] = str_replace($notif['alert_code'], $displayName, $notif['title']);
            $notif['message'] = str_replace($notif['alert_code'], $displayName, $notif['message']);
        }

        // Add resolved display name as separate field
        $notif['alert_display_name'] = $displayName;
        applyAlertMappingFallback($notif);
        // Normalize equipment_id and department for UI (may be null if cache_devices lookup failed)
        $notif['equipment_id'] = ($notif['device_equipment_id'] ?? null) ?: ($notif['device_equipment_id_alt'] ?? null) ?: ($notif['device_identifier'] ?? null) ?: ($notif['device_serial'] ?? null);
        $notif['department'] = $notif['device_department'] ?? null;
        $notif['model'] = $notif['device_model'] ?? null;
        // Customer description for subtitle
        $notif['customer_description'] = $notif['customer_code'] ? ($notif['customer_description'] ?? $notif['customer_code']) : ($notif['customer_description'] ?? null);
    }

    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'count' => count($notifications),
        'filters' => [
            'status' => $status,
            'severity' => $severity,
            'customerCode' => $customerCode
        ]
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

function getAggregations(PDO $pdo): void
{
    $limit = min((int)($_GET['limit'] ?? 50), 1000);
    $groupBy = $_GET['group_by'] ?? 'device_alert'; // 'device_alert' or 'alert_only'

    $aggTable = DB_PREFIX . 'alert_aggregations';
    $defsTable = DB_PREFIX . 'alert_definitions';

    if ($groupBy === 'alert_only') {
        // Group by alert code only - aggregate across all devices
        $sql = "SELECT
                    a.alert_code,
                    ad.display_name as alert_display_name,
                    ad.category as alert_category,
                    COUNT(DISTINCT a.device_serial) as device_count,
                    SUM(a.count_1h) as count_1h,
                    SUM(a.count_24h) as count_24h,
                    SUM(a.count_7d) as count_7d,
                    SUM(a.count_30d) as count_30d,
                    SUM(a.occurrence_count) as occurrence_count,
                    MAX(a.last_occurrence_ny) as last_occurrence_ny
                FROM {$aggTable} a
                LEFT JOIN {$defsTable} ad ON a.alert_code = ad.alert_code AND ad.enabled = 1
                GROUP BY a.alert_code, ad.display_name, ad.category
                ORDER BY last_occurrence_ny DESC
                LIMIT :limit";
    } else {
        // Default: Group by device + alert (current behavior)
        $sql = "SELECT a.*,
                       ad.display_name as alert_display_name,
                       ad.category as alert_category
                FROM {$aggTable} a
                LEFT JOIN {$defsTable} ad ON a.alert_code = ad.alert_code AND ad.enabled = 1
                ORDER BY a.last_occurrence_ny DESC
                LIMIT :limit";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $aggregations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add display_name field (use definition or fallback to code)
    foreach ($aggregations as &$agg) {
        $agg['alert_display_name'] ??= $agg['alert_code'];
    }

    echo json_encode([
        'success' => true,
        'aggregations' => $aggregations,
        'count' => count($aggregations),
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

    $sql .= " ORDER BY category ASC, display_name ASC LIMIT {$limit}";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
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
