<?php
/**
 * Command Center API
 * Endpoints for managing notification rules and viewing dashboard notifications
 */

require '../config.php';
require '../functions.php';

requireAuth();

define('MPS_ENGINE_ACCESS', true);
require_once __DIR__ . '/../../mps-api/callbacks/command-center-schema.php';
require_once __DIR__ . '/../../mps-api/callbacks/command-center-engine.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$pdo = getDatabase();

// Ensure tables exist
ensureCommandCenterTables($pdo);

header('Content-Type: application/json');

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
    $limit = min((int)($_GET['limit'] ?? 50), 100);

    $table = DB_PREFIX . 'dashboard_notifications';
    $sql = "SELECT * FROM {$table}
            WHERE status = :status
            ORDER BY priority DESC, created_at_ny DESC
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':status', $status);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'count' => count($notifications)
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
    $stmt = $pdo->prepare("UPDATE {$table} SET enabled = NOT enabled WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Rule toggled successfully'
    ]);
}

function acknowledgeNotification(PDO $pdo): void
{
    $id = (int)($_POST['id'] ?? 0);

    if (!$id) {
        throw new Exception('Notification ID required');
    }

    $nyTime = getNYTimestamp();
    $table = DB_PREFIX . 'dashboard_notifications';

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

    $nyTime = getNYTimestamp();
    $table = DB_PREFIX . 'dashboard_notifications';

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
    $limit = min((int)($_GET['limit'] ?? 50), 100);

    $table = DB_PREFIX . 'alert_aggregations';
    $sql = "SELECT * FROM {$table}
            ORDER BY last_occurrence_ny DESC
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $aggregations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'aggregations' => $aggregations,
        'count' => count($aggregations)
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
