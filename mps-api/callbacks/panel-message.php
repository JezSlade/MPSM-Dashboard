<?php
declare(strict_types=1);

/**
 * MPSM Panel Message Callback Receiver
 *
 * Accepts POSTed JSON payloads from the MPS Monitor callback system,
 * persists the raw payload for later analysis, and provides a simple
 * acknowledgement response.
 */

define('MPS_ENGINE_ACCESS', true);

require_once dirname(__DIR__, 1) . '/config.php';     // Loads .env-backed engine config
require_once dirname(__DIR__, 2) . '/cms/config.php'; // Provides DB constants/session settings
require_once dirname(__DIR__, 2) . '/cms/functions.php';
require_once __DIR__ . '/panel-message-common.php';

$debugLogId = createPanelCallbackDebugLog();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', 'Method Not Allowed', 405, null);
    respondError('Method Not Allowed', 405);
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') === false) {
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', 'Invalid Content-Type', 415, null);
    respondError('Content-Type must be application/json', 415);
}

$rawBody = file_get_contents('php://input');
if ($rawBody === false || trim($rawBody) === '') {
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', 'Empty request body', 400, $rawBody ?: null);
    respondError('Empty request body');
}

$decoded = json_decode($rawBody, true);
if (!is_array($decoded)) {
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', 'Invalid JSON payload', 400, $rawBody);
    respondError('Invalid JSON payload');
}

// Lightweight shared-secret validation since the upstream cannot set headers.
$providedSecret = $decoded['callbackSecret'] ?? $decoded['secret'] ?? null;
$expectedSecret = 'mpsm-panel-message-v1';
if ($providedSecret !== $expectedSecret) {
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', 'Unauthorized - invalid secret', 401, $rawBody);
    respondError('Unauthorized', 401);
}

try {
    $pdo = getDatabase();
    ensurePanelMessageTable($pdo);

    $nyTimestamp = getNYTimestamp(); // Get NY local time

    $insertSql = sprintf(
        'INSERT INTO %s (ny_received_at, customer_code, customer_description, device_serial, maintenance_alert_code, maintenance_alert_id, panel_configuration, payload, source_ip)
         VALUES (:ny_received_at, :customer_code, :customer_description, :device_serial, :maintenance_alert_code, :maintenance_alert_id, :panel_configuration, :payload, :source_ip)',
        DB_PREFIX . 'panel_messages'
    );

    $stmt = $pdo->prepare($insertSql);
    $stmt->execute([
        ':ny_received_at' => $nyTimestamp, // MISSION CRITICAL: NY local time
        ':customer_code' => truncateField($decoded['customer']['code'] ?? $decoded['Customer_Code'] ?? null, 100),
        ':customer_description' => truncateField($decoded['customer']['description'] ?? $decoded['Customer_Description'] ?? null, 255),
        ':device_serial' => truncateField(extractDeviceSerial($decoded), 150),
        ':maintenance_alert_code' => truncateField($decoded['maintenanceAlert']['code'] ?? $decoded['MaintenanceAlert_Code'] ?? null, 150),
        ':maintenance_alert_id' => truncateField($decoded['maintenanceAlert']['id'] ?? $decoded['MaintenanceAlert_Id'] ?? null, 150),
        ':panel_configuration' => truncateField($decoded['maintenanceAlert']['panelConfiguration'] ?? $decoded['PanelMessageConfiguration_Description'] ?? null, 255),
        ':payload' => json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION),
        ':source_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    $messageId = $pdo->lastInsertId();

    updatePanelCallbackDebugLog($debugLogId, 'SUCCESS', "Message stored with ID {$messageId} at {$nyTimestamp} ET", 200, $rawBody);
    logPanelMessage($decoded);
} catch (Throwable $exception) {
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', 'Database error: ' . $exception->getMessage(), 500, $rawBody);
    respondError('Internal Server Error: ' . $exception->getMessage(), 500);
}

respondSuccess(['stored' => true]);

function respondSuccess(array $data = []): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => true], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

function respondError(string $message, int $status = 400): void
{
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

